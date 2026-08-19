import { describe, it, expect, afterAll, afterEach } from 'vitest';
import request from 'supertest';
import jwt from 'jsonwebtoken';
import app from '../../app';
import { env } from '../../config/env';
import { getPool, closePool, sql } from '../../config/database';

// ── Helpers de identidad ─────────────────────────────────────
// Usuarios reales del entorno de desarrollo (ver CLAUDE.md) — se firman tokens
// directamente con el mismo secreto/shape que auth.service.ts, sin depender
// de POST /auth/login ni de contraseñas hardcodeadas en el test.
function tokenPara(opts: { idUsuario: number; usuario: string; roles: string[]; modulos: string[] }): string {
  return jwt.sign(
    {
      sub:            opts.idUsuario,
      usuario:        opts.usuario,
      idFuncionario:  null,
      idDependencia:  null,
      todosServicios: false,
      roles:          opts.roles,
      modulos:        opts.modulos,
    },
    env.JWT_SECRET,
    { expiresIn: '15m' },
  );
}

const ADMIN_TOKEN = tokenPara({ idUsuario: 532, usuario: 'admin', roles: ['admin'], modulos: [] });
const OFPARTES_TOKEN = tokenPara({ idUsuario: 2535, usuario: 'ofparte', roles: ['of.partes'], modulos: ['libro-referencias'] });
const SIN_PERMISO_TOKEN = tokenPara({ idUsuario: 1537, usuario: 'contrato', roles: ['funcionario'], modulos: [] });

const authAdmin      = () => ({ Authorization: `Bearer ${ADMIN_TOKEN}` });
const authOfPartes   = () => ({ Authorization: `Bearer ${OFPARTES_TOKEN}` });
const authSinPermiso = () => ({ Authorization: `Bearer ${SIN_PERMISO_TOKEN}` });

const dtoBase = {
  nombreInteresado: 'Persona De Prueba Vitest',
  tipoTramite:       'Solicitud de prueba automatizada',
  fechaDocumento:    '2026-01-15',
  fechaRecepcion:    '2026-01-16',
  observaciones:     'Registro creado por la suite de pruebas del módulo Libro de Referencias.',
};

const idsCreadosParaLimpiar: number[] = [];
const crearYRegistrar = async () => {
  const res = await request(app).post('/api/v1/libro-referencias').set(authOfPartes()).send(dtoBase);
  idsCreadosParaLimpiar.push(res.body.data.id);
  return res;
};

afterAll(async () => {
  if (idsCreadosParaLimpiar.length > 0) {
    const pool = await getPool();
    for (const id of idsCreadosParaLimpiar) {
      await pool.request().input('id', sql.Int, id).query('DELETE FROM libro_referencia WHERE id_referencia = @id');
    }
  }
  await closePool();
});

describe('Libro de Referencias — permisos y acceso', () => {
  it('8. un usuario sin el módulo asignado no puede acceder (403)', async () => {
    const res = await request(app).get('/api/v1/libro-referencias').set(authSinPermiso());
    expect(res.status).toBe(403);
  });

  it('rechaza sin token de autenticación (401)', async () => {
    const res = await request(app).get('/api/v1/libro-referencias');
    expect(res.status).toBe(401);
  });

  it('10. un rol autorizado (of.partes) puede crear y consultar', async () => {
    const crearRes = await crearYRegistrar();
    expect(crearRes.status).toBe(201);

    const listarRes = await request(app).get('/api/v1/libro-referencias').set(authOfPartes());
    expect(listarRes.status).toBe(200);
    expect(listarRes.body.ok).toBe(true);
  });

  it('el Superadministrador (admin) siempre puede acceder aunque no tenga el módulo listado', async () => {
    const res = await request(app).get('/api/v1/libro-referencias').set(authAdmin());
    expect(res.status).toBe(200);
  });
});

// ── Algoritmo de "menor número libre entre vigentes" ──────────
// Se prueba de forma aislada contra años sintéticos (nunca usados por datos
// reales) manipulando filas directamente vía SQL, para poder verificar los
// 5 casos obligatorios de forma determinística sin depender de qué otros
// datos existan hoy en la base de desarrollo compartida. La query replica
// exactamente la lógica de libro-referencias.repository.ts#crear() (sin el
// hint TABLOCKX, que aquí no aporta nada fuera de una transacción real) —
// la seguridad de concurrencia se prueba aparte, con la API real, más abajo.
async function siguienteNumeroLibre(anio: number): Promise<number> {
  const pool = await getPool();
  const result = await pool.request()
    .input('anio', sql.Int, anio)
    .query<{ candidato: number }>(`
      SELECT MIN(candidato) AS candidato
      FROM (
        SELECT 1 AS candidato
        UNION ALL
        SELECT numero + 1 AS candidato FROM libro_referencia WHERE anio = @anio AND condicion = 'VIGENTE'
      ) c
      WHERE NOT EXISTS (
        SELECT 1 FROM libro_referencia r WHERE r.anio = @anio AND r.condicion = 'VIGENTE' AND r.numero = c.candidato
      )
    `);
  return result.recordset[0].candidato;
}

async function insertarFila(anio: number, numero: number, condicion: 'VIGENTE' | 'ELIMINADO') {
  const pool = await getPool();
  const codigo = `REF-${anio}-${String(numero).padStart(6, '0')}`;
  const result = await pool.request()
    .input('anio', sql.Int, anio)
    .input('numero', sql.Int, numero)
    .input('codigo', sql.VarChar(20), codigo)
    .input('condicion', sql.VarChar(10), condicion)
    .query<{ id: number }>(`
      INSERT INTO libro_referencia
        (anio, numero, codigo, id_usuario_creador, nombre_usuario_creador,
         nombre_interesado, tipo_tramite, fecha_documento, fecha_recepcion, condicion)
      OUTPUT INSERTED.id_referencia AS id
      VALUES (@anio, @numero, @codigo, 532, 'Test', 'Test algoritmo', 'Test', GETDATE(), GETDATE(), @condicion)
    `);
  return result.recordset[0].id;
}

const idsSinteticosParaLimpiar: number[] = [];
afterEach(async () => {
  if (idsSinteticosParaLimpiar.length > 0) {
    const pool = await getPool();
    for (const id of idsSinteticosParaLimpiar.splice(0)) {
      await pool.request().input('id', sql.Int, id).query('DELETE FROM libro_referencia WHERE id_referencia = @id');
    }
  }
});

describe('Libro de Referencias — algoritmo de correlativo (menor número libre)', () => {
  // Años reservados exclusivamente para estas pruebas — nunca usados por la app real.
  const ANIO_A = 2091;
  const ANIO_B = 2092;

  it('14.1 sin registros, la primera referencia es 000001', async () => {
    expect(await siguienteNumeroLibre(ANIO_A)).toBe(1);
  });

  it('caso 1 / 14.2 — 1, 2 y 3 eliminados: la siguiente es 000001', async () => {
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 1, 'ELIMINADO'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 2, 'ELIMINADO'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 3, 'ELIMINADO'));
    expect(await siguienteNumeroLibre(ANIO_A)).toBe(1);
  });

  it('caso 2 / 14.3 — 1 vigente, 2 eliminado, 3 vigente: la siguiente es 000002', async () => {
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 1, 'VIGENTE'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 2, 'ELIMINADO'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 3, 'VIGENTE'));
    expect(await siguienteNumeroLibre(ANIO_A)).toBe(2);
  });

  it('caso 3 / 14.4 — 1, 2 y 3 vigentes: la siguiente es 000004', async () => {
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 1, 'VIGENTE'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 2, 'VIGENTE'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 3, 'VIGENTE'));
    expect(await siguienteNumeroLibre(ANIO_A)).toBe(4);
  });

  it('caso 4 — 1 eliminado, 2 y 3 vigentes: la siguiente es 000001', async () => {
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 1, 'ELIMINADO'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 2, 'VIGENTE'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 3, 'VIGENTE'));
    expect(await siguienteNumeroLibre(ANIO_A)).toBe(1);
  });

  it('caso 5 / 14.10 — un año no interfiere con otro (cambio de año reinicia en 1)', async () => {
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 1, 'VIGENTE'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 2, 'VIGENTE'));
    idsSinteticosParaLimpiar.push(await insertarFila(ANIO_A, 3, 'VIGENTE'));
    // ANIO_B no tiene ninguna fila propia — su siguiente número es 1,
    // sin importar cuántas vigentes tenga ANIO_A.
    expect(await siguienteNumeroLibre(ANIO_B)).toBe(1);
  });
});

describe('Libro de Referencias — reutilización real vía API (1/2/6/7 y formato)', () => {
  it('genera el código con el formato correcto y toma el usuario desde la sesión (no del body)', async () => {
    const anioActual = new Date().getFullYear();
    const r = await request(app).post('/api/v1/libro-referencias').set(authOfPartes())
      .send({ ...dtoBase, codigo: 'REF-9999-999999', anio: 1900, numero: 999999, idUsuarioCreador: 1 }); // campos ajenos — deben ser ignorados
    idsCreadosParaLimpiar.push(r.body.data.id);
    expect(r.status).toBe(201);
    expect(r.body.data.codigo).toMatch(new RegExp(`^REF-${anioActual}-\\d{6}$`));
    expect(r.body.data.usuarioCreador.id).toBe(2535); // viene del token, no del body
  });

  it('5/6/7. al eliminar una referencia, su número vuelve a estar disponible para la siguiente creación', async () => {
    const r1 = await crearYRegistrar();
    const numeroLiberado = r1.body.data.numero as number;
    const codigoOriginal = r1.body.data.codigo as string;
    const idOriginal = r1.body.data.id as number;

    const eliminar = await request(app).delete(`/api/v1/libro-referencias/${idOriginal}`).set(authAdmin())
      .send({ motivo: 'Prueba de reutilización de correlativo' });
    expect(eliminar.status).toBe(200);

    // Como nada más pudo haber tomado ese número entremedio (test secuencial,
    // un solo proceso), la siguiente creación debe recibir exactamente el
    // mismo número que se acaba de liberar.
    const r2 = await crearYRegistrar();
    expect(r2.status).toBe(201);
    expect(r2.body.data.numero).toBe(numeroLiberado);
    expect(r2.body.data.codigo).toBe(codigoOriginal); // 6. mismo código reutilizado

    // 7. puede existir un eliminado y un vigente con el mismo código a la vez
    const eliminado = await request(app).get(`/api/v1/libro-referencias/${idOriginal}`).set(authAdmin());
    expect(eliminado.body.data.condicion).toBe('ELIMINADO');
    expect(eliminado.body.data.codigo).toBe(codigoOriginal);
    const vigente = await request(app).get(`/api/v1/libro-referencias/${r2.body.data.id}`).set(authAdmin());
    expect(vigente.body.data.condicion).toBe('VIGENTE');
    expect(vigente.body.data.codigo).toBe(codigoOriginal);
    expect(vigente.body.data.id).not.toBe(idOriginal); // 8 (sección 8) — identificados por ID técnico, no por código
  });

  it('8 (sección 7) — la base de datos impide dos VIGENTE con el mismo año+número', async () => {
    const pool = await getPool();
    const r = await crearYRegistrar();
    const { anio, numero } = r.body.data;

    // Intento directo a nivel de BD de duplicar (anio, numero) como VIGENTE —
    // debe fallar por el índice único filtrado, sin pasar por la app.
    await expect(
      pool.request()
        .input('anio', sql.Int, anio)
        .input('numero', sql.Int, numero)
        .query(`
          INSERT INTO libro_referencia
            (anio, numero, codigo, id_usuario_creador, nombre_usuario_creador,
             nombre_interesado, tipo_tramite, fecha_documento, fecha_recepcion, condicion)
          VALUES (@anio, @numero, 'REF-DUP-000000', 532, 'Test', 'Duplicado', 'Test', GETDATE(), GETDATE(), 'VIGENTE')
        `),
    ).rejects.toThrow();
  });

  it('4/9. dos creaciones concurrentes reciben números distintos (sin duplicados)', async () => {
    const N = 8;
    const respuestas = await Promise.all(Array.from({ length: N }, () => crearYRegistrar()));
    respuestas.forEach((r) => expect(r.status).toBe(201));
    const numeros = respuestas.map((r) => r.body.data.numero as number);
    expect(new Set(numeros).size).toBe(N);
  });
});

describe('Libro de Referencias — edición', () => {
  it('el correlativo, año y usuario creador no pueden modificarse vía PATCH', async () => {
    const crear = await crearYRegistrar();
    const id = crear.body.data.id;
    const codigoOriginal = crear.body.data.codigo;

    const editar = await request(app).patch(`/api/v1/libro-referencias/${id}`).set(authOfPartes())
      .send({ ...dtoBase, nombreInteresado: 'Nombre Editado', codigo: 'REF-0000-000000', anio: 1, numero: 1 });

    expect(editar.status).toBe(200);
    expect(editar.body.data.codigo).toBe(codigoOriginal);
    expect(editar.body.data.nombreInteresado).toBe('Nombre Editado');
  });
});

describe('Libro de Referencias — eliminación exclusiva del Superadministrador', () => {
  it('11. un rol no-admin (of.partes) no puede eliminar (403)', async () => {
    const crear = await crearYRegistrar();
    const id = crear.body.data.id;

    const eliminar = await request(app).delete(`/api/v1/libro-referencias/${id}`).set(authOfPartes())
      .send({ motivo: 'Intento no autorizado' });
    expect(eliminar.status).toBe(403);

    const consulta = await request(app).get(`/api/v1/libro-referencias/${id}`).set(authOfPartes());
    expect(consulta.body.data.condicion).toBe('VIGENTE');
  });

  it('12/13/14/15. el Superadministrador elimina lógicamente con motivo; queda oculta del listado normal pero disponible para auditoría; otros registros no se ven afectados', async () => {
    const otro = await crearYRegistrar();
    const otroCodigoAntes = otro.body.data.codigo;

    const crear = await crearYRegistrar();
    const id = crear.body.data.id;
    const codigo = crear.body.data.codigo;

    const sinMotivo = await request(app).delete(`/api/v1/libro-referencias/${id}`).set(authAdmin()).send({});
    expect(sinMotivo.status).toBe(400);

    const eliminar = await request(app).delete(`/api/v1/libro-referencias/${id}`).set(authAdmin())
      .send({ motivo: 'Eliminado por prueba automatizada de regresión' });
    expect(eliminar.status).toBe(200);
    expect(eliminar.body.data.condicion).toBe('ELIMINADO');

    const listado = await request(app).get('/api/v1/libro-referencias').set(authAdmin()).query({ q: codigo });
    expect(listado.body.data.some((r: { id: number }) => r.id === id)).toBe(false);

    const detalle = await request(app).get(`/api/v1/libro-referencias/${id}`).set(authAdmin());
    expect(detalle.body.data.condicion).toBe('ELIMINADO');
    expect(detalle.body.data.eliminacion.motivo).toBe('Eliminado por prueba automatizada de regresión');

    const listadoEliminados = await request(app).get('/api/v1/libro-referencias/eliminados').set(authAdmin()).query({ q: codigo });
    expect(listadoEliminados.body.data.some((r: { id: number }) => r.id === id)).toBe(true);

    const otroDespues = await request(app).get(`/api/v1/libro-referencias/${otro.body.data.id}`).set(authAdmin());
    expect(otroDespues.body.data.condicion).toBe('VIGENTE');
    expect(otroDespues.body.data.codigo).toBe(otroCodigoAntes);
  });

  it('la lista de eliminados es exclusiva del Superadministrador', async () => {
    const res = await request(app).get('/api/v1/libro-referencias/eliminados').set(authOfPartes());
    expect(res.status).toBe(403);
  });

  it('13. la auditoría registra el ID técnico (no el código visible) para poder distinguir códigos reutilizados', async () => {
    const crear = await crearYRegistrar();
    const id = crear.body.data.id;

    await request(app).delete(`/api/v1/libro-referencias/${id}`).set(authAdmin())
      .send({ motivo: 'Prueba de auditoría con ID técnico' });

    const pool = await getPool();
    const auditRes = await pool.request()
      .input('recurso', sql.NVarChar(100), String(id))
      .query<{ accion: string; recurso: string; detalle: string }>(`
        SELECT TOP 1 accion, recurso, detalle FROM auditoria
        WHERE accion = 'LIBRO_REFERENCIA_ELIMINADO' AND recurso = @recurso
        ORDER BY id DESC
      `);
    expect(auditRes.recordset[0]).toBeDefined();
    expect(auditRes.recordset[0].recurso).toBe(String(id));
    expect(auditRes.recordset[0].detalle).toContain('Prueba de auditoría con ID técnico');
  });
});

describe('Libro de Referencias — métricas', () => {
  it('14. distingue vigentes y eliminados', async () => {
    const antes = await request(app).get('/api/v1/libro-referencias/metricas').set(authAdmin());
    const eliminadosAntes = antes.body.data.eliminadas as number;

    const crear = await crearYRegistrar();
    await request(app).delete(`/api/v1/libro-referencias/${crear.body.data.id}`).set(authAdmin())
      .send({ motivo: 'Prueba de métricas' });

    const despues = await request(app).get('/api/v1/libro-referencias/metricas').set(authAdmin());
    expect(despues.body.data.eliminadas).toBe(eliminadosAntes + 1);
  });
});

describe('Libro de Referencias — filtros y paginación', () => {
  it('19. busca por término y pagina correctamente', async () => {
    const nombreUnico = `Buscable-${Date.now()}`;
    await request(app).post('/api/v1/libro-referencias').set(authOfPartes())
      .send({ ...dtoBase, nombreInteresado: nombreUnico })
      .then((r) => idsCreadosParaLimpiar.push(r.body.data.id));

    const busqueda = await request(app).get('/api/v1/libro-referencias').set(authOfPartes()).query({ q: nombreUnico });
    expect(busqueda.status).toBe(200);
    expect(busqueda.body.data.length).toBe(1);
    expect(busqueda.body.data[0].nombreInteresado).toBe(nombreUnico);

    const pagina1 = await request(app).get('/api/v1/libro-referencias').set(authOfPartes()).query({ pagina: 1, porPagina: 1 });
    expect(pagina1.body.data.length).toBeLessThanOrEqual(1);
    expect(pagina1.body.meta.porPagina).toBe(1);
  });
});

// ── Eliminación definitiva (nivel 2) — DELETE /libro-referencias/:id/permanent ──
describe('Libro de Referencias — eliminación definitiva (nivel 2, exclusiva Superadministrador)', () => {
  const eliminarLogico = async (id: number, motivo = 'Eliminación lógica previa para prueba de nivel 2') => {
    const r = await request(app).delete(`/api/v1/libro-referencias/${id}`).set(authAdmin()).send({ motivo });
    expect(r.status).toBe(200);
  };
  const CONFIRMACION = { motivo: 'Eliminación definitiva de prueba automatizada', confirmacion: 'ELIMINAR' };

  it('1/11. el Superadministrador elimina definitivamente un registro ya eliminado, usando el ID técnico', async () => {
    const crear = await crearYRegistrar();
    const id = crear.body.data.id;
    const codigo = crear.body.data.codigo;
    await eliminarLogico(id);

    const res = await request(app).delete(`/api/v1/libro-referencias/${id}/permanent`).set(authAdmin()).send(CONFIRMACION);
    expect(res.status).toBe(200);
    expect(res.body.data.codigo).toBe(codigo);

    // 14. ya no aparece ni siquiera en la vista de eliminados (dejó de existir).
    const consulta = await request(app).get(`/api/v1/libro-referencias/${id}`).set(authAdmin());
    expect(consulta.status).toBe(404);
  });

  it('2/4/6. of.partes y un usuario sin el módulo no pueden eliminar definitivamente (403), y el registro no se toca', async () => {
    const crear = await crearYRegistrar();
    const id = crear.body.data.id;
    await eliminarLogico(id);

    const intento1 = await request(app).delete(`/api/v1/libro-referencias/${id}/permanent`).set(authOfPartes()).send(CONFIRMACION);
    expect(intento1.status).toBe(403);

    const intento2 = await request(app).delete(`/api/v1/libro-referencias/${id}/permanent`).set(authSinPermiso()).send(CONFIRMACION);
    expect(intento2.status).toBe(403);

    // El registro sigue existiendo y sigue eliminado (no se tocó).
    const consulta = await request(app).get(`/api/v1/libro-referencias/${id}`).set(authAdmin());
    expect(consulta.status).toBe(200);
    expect(consulta.body.data.condicion).toBe('ELIMINADO');
  });

  it('7/8. no se puede eliminar definitivamente un registro VIGENTE (409), sin modificarlo', async () => {
    const crear = await crearYRegistrar();
    const id = crear.body.data.id;
    // Nunca se elimina lógicamente — sigue VIGENTE.

    const res = await request(app).delete(`/api/v1/libro-referencias/${id}/permanent`).set(authAdmin()).send(CONFIRMACION);
    expect(res.status).toBe(409);

    const consulta = await request(app).get(`/api/v1/libro-referencias/${id}`).set(authAdmin());
    expect(consulta.body.data.condicion).toBe('VIGENTE');
  });

  it('9. el motivo es obligatorio (400)', async () => {
    const crear = await crearYRegistrar();
    await eliminarLogico(crear.body.data.id);

    const res = await request(app).delete(`/api/v1/libro-referencias/${crear.body.data.id}/permanent`).set(authAdmin())
      .send({ confirmacion: 'ELIMINAR' }); // sin motivo
    expect(res.status).toBe(400);
  });

  it('10. la palabra de confirmación es obligatoria y debe ser exactamente "ELIMINAR" (400)', async () => {
    const crear = await crearYRegistrar();
    await eliminarLogico(crear.body.data.id);

    const sinConfirmacion = await request(app).delete(`/api/v1/libro-referencias/${crear.body.data.id}/permanent`).set(authAdmin())
      .send({ motivo: 'Motivo válido de más de 5 caracteres' });
    expect(sinConfirmacion.status).toBe(400);

    const confirmacionIncorrecta = await request(app).delete(`/api/v1/libro-referencias/${crear.body.data.id}/permanent`).set(authAdmin())
      .send({ motivo: 'Motivo válido de más de 5 caracteres', confirmacion: 'eliminar' }); // minúsculas — no coincide
    expect(confirmacionIncorrecta.status).toBe(400);
  });

  it('12/13. con varios eliminados del mismo correlativo (código repetido), solo se borra el ID técnico seleccionado — el vigente con el mismo código no se toca', async () => {
    // A: se crea, se elimina lógicamente, se elimina definitivamente -> libera su número.
    const a = await crearYRegistrar();
    await eliminarLogico(a.body.data.id);
    await request(app).delete(`/api/v1/libro-referencias/${a.body.data.id}/permanent`).set(authAdmin()).send(CONFIRMACION);

    // B: se crea (reutiliza el mismo número que tuvo A, mismo código visible), se elimina lógicamente.
    const b = await crearYRegistrar();
    expect(b.body.data.codigo).toBe(a.body.data.codigo); // confirma que el código se reutilizó
    await eliminarLogico(b.body.data.id);

    // C: se crea de nuevo (mismo código otra vez), queda VIGENTE.
    const c = await crearYRegistrar();
    expect(c.body.data.codigo).toBe(a.body.data.codigo);

    // Eliminar definitivamente solo B (por su ID técnico) — ni A (ya no existe) ni C (vigente) deben verse afectados.
    const res = await request(app).delete(`/api/v1/libro-referencias/${b.body.data.id}/permanent`).set(authAdmin()).send(CONFIRMACION);
    expect(res.status).toBe(200);

    const consultaB = await request(app).get(`/api/v1/libro-referencias/${b.body.data.id}`).set(authAdmin());
    expect(consultaB.status).toBe(404); // B ya no existe

    const consultaC = await request(app).get(`/api/v1/libro-referencias/${c.body.data.id}`).set(authAdmin());
    expect(consultaC.status).toBe(200);
    expect(consultaC.body.data.condicion).toBe('VIGENTE');
    expect(consultaC.body.data.codigo).toBe(a.body.data.codigo); // C sigue con su código, intacto
  });

  it('15/16. el contador de eliminados baja tras la eliminación definitiva y la auditoría conserva la evidencia', async () => {
    const crear = await crearYRegistrar();
    const id = crear.body.data.id;
    const codigo = crear.body.data.codigo;
    await eliminarLogico(id, 'Motivo de la eliminación lógica original');

    const antes = await request(app).get('/api/v1/libro-referencias/metricas').set(authAdmin());
    const eliminadosAntes = antes.body.data.eliminadas as number;

    const res = await request(app).delete(`/api/v1/libro-referencias/${id}/permanent`).set(authAdmin())
      .send({ motivo: 'Motivo de la eliminación definitiva', confirmacion: 'ELIMINAR' });
    expect(res.status).toBe(200);

    const despues = await request(app).get('/api/v1/libro-referencias/metricas').set(authAdmin());
    expect(despues.body.data.eliminadas).toBe(eliminadosAntes - 1); // 15. baja el contador (ya no está ni eliminado ni vigente)

    // 16. la auditoría conserva la evidencia, indexada por el ID técnico, aunque la fila ya no exista.
    const pool = await getPool();
    const auditRes = await pool.request()
      .input('recurso', sql.NVarChar(100), String(id))
      .query<{ accion: string; recurso: string; detalle: string }>(`
        SELECT TOP 1 accion, recurso, detalle FROM auditoria
        WHERE accion = 'ELIMINACION_DEFINITIVA_LIBRO_REFERENCIAS' AND recurso = @recurso
        ORDER BY id DESC
      `);
    expect(auditRes.recordset[0]).toBeDefined();
    expect(auditRes.recordset[0].detalle).toContain(codigo);
    expect(auditRes.recordset[0].detalle).toContain('Motivo de la eliminación lógica original');
    expect(auditRes.recordset[0].detalle).toContain('Motivo de la eliminación definitiva');
  });

  it('17/18. el correlativo permanece disponible y la siguiente creación usa el menor número libre', async () => {
    const a = await crearYRegistrar();
    const numeroLiberado = a.body.data.numero;
    await eliminarLogico(a.body.data.id);
    await request(app).delete(`/api/v1/libro-referencias/${a.body.data.id}/permanent`).set(authAdmin()).send(CONFIRMACION);

    const siguiente = await crearYRegistrar();
    expect(siguiente.body.data.numero).toBe(numeroLiberado); // mismo número, disponible para reutilizarse
  });

  it('20. dos solicitudes de eliminación definitiva simultáneas sobre el mismo registro: solo una tiene éxito, sin dejar cambios parciales', async () => {
    const crear = await crearYRegistrar();
    const id = crear.body.data.id;
    await eliminarLogico(id);

    const [r1, r2] = await Promise.all([
      request(app).delete(`/api/v1/libro-referencias/${id}/permanent`).set(authAdmin()).send(CONFIRMACION),
      request(app).delete(`/api/v1/libro-referencias/${id}/permanent`).set(authAdmin()).send(CONFIRMACION),
    ]);
    const estados = [r1.status, r2.status].sort();
    expect(estados).toEqual([200, 409]); // una exitosa, la otra en conflicto — ninguna deja el sistema a medias

    const consulta = await request(app).get(`/api/v1/libro-referencias/${id}`).set(authAdmin());
    expect(consulta.status).toBe(404);
  });
});
