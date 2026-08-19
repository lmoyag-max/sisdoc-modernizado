import { getPool, sql } from '../../config/database';
import { FiltrosReferenciaDto, CrearReferenciaDto, EditarReferenciaDto } from './libro-referencias.schema';

export interface ReferenciaRow {
  id_referencia:          number;
  anio:                   number;
  numero:                 number;
  codigo:                 string;
  id_usuario_creador:     number;
  nombre_usuario_creador: string;
  nombre_interesado:      string;
  tipo_tramite:           string;
  fecha_documento:        Date;
  fecha_recepcion:        Date;
  observaciones:          string | null;
  condicion:               'VIGENTE' | 'ELIMINADO';
  id_usuario_eliminacion: number | null;
  usuario_eliminacion:    string | null;
  fecha_eliminacion:      Date | null;
  motivo_eliminacion:     string | null;
  fecha_creacion:         Date;
  fecha_actualizacion:    Date;
  total?:                 number;
}

const ORDEN_SQL: Record<FiltrosReferenciaDto['orden'], string> = {
  correlativo_desc: 'r.anio DESC, r.numero DESC',
  correlativo_asc:  'r.anio ASC, r.numero ASC',
  fecha_desc:       'r.fecha_documento DESC',
  fecha_asc:        'r.fecha_documento ASC',
};

// Lista de columnas separada de FROM/JOIN para poder insertar columnas
// calculadas adicionales (COUNT(*) OVER()) en el lugar correcto del SELECT.
const CAMPOS = `
  r.id_referencia, r.anio, r.numero, r.codigo,
  r.id_usuario_creador, r.nombre_usuario_creador,
  r.nombre_interesado, r.tipo_tramite,
  r.fecha_documento, r.fecha_recepcion, r.observaciones,
  r.condicion, r.id_usuario_eliminacion, ue.usuario AS usuario_eliminacion,
  r.fecha_eliminacion, r.motivo_eliminacion,
  r.fecha_creacion, r.fecha_actualizacion
`;
const FROM_JOIN = `
  FROM libro_referencia r
  LEFT JOIN usuario ue ON ue.id_usuario = r.id_usuario_eliminacion
`;

// ── findMany ─────────────────────────────────────────────────
// soloEliminados=false -> solo condicion='VIGENTE' (vista normal)
// soloEliminados=true  -> solo condicion='ELIMINADO' (exclusivo Superadmin)
export async function findMany(filtros: FiltrosReferenciaDto, soloEliminados: boolean): Promise<ReferenciaRow[]> {
  const pool   = await getPool();
  const offset = (filtros.pagina - 1) * filtros.porPagina;
  const request = pool.request()
    .input('offset',    sql.Int, offset)
    .input('porPagina', sql.Int, filtros.porPagina);

  let where = soloEliminados ? "r.condicion = 'ELIMINADO'" : "r.condicion = 'VIGENTE'";

  if (filtros.q) {
    request.input('q', sql.NVarChar(200), `%${filtros.q}%`);
    where += ' AND (r.codigo LIKE @q OR r.nombre_interesado LIKE @q OR r.tipo_tramite LIKE @q)';
  }
  if (filtros.anio)          { request.input('anio',       sql.Int,  filtros.anio);                     where += ' AND r.anio = @anio'; }
  if (filtros.fechaDocDesde) { request.input('fechaDocDesde', sql.Date, new Date(filtros.fechaDocDesde)); where += ' AND r.fecha_documento >= @fechaDocDesde'; }
  if (filtros.fechaDocHasta) { request.input('fechaDocHasta', sql.Date, new Date(filtros.fechaDocHasta)); where += ' AND r.fecha_documento <= @fechaDocHasta'; }
  if (filtros.fechaRecDesde) { request.input('fechaRecDesde', sql.Date, new Date(filtros.fechaRecDesde)); where += ' AND r.fecha_recepcion >= @fechaRecDesde'; }
  if (filtros.fechaRecHasta) { request.input('fechaRecHasta', sql.Date, new Date(filtros.fechaRecHasta)); where += ' AND r.fecha_recepcion <= @fechaRecHasta'; }

  const result = await request.query<ReferenciaRow>(`
    SELECT ${CAMPOS}, COUNT(*) OVER() AS total
    ${FROM_JOIN}
    WHERE ${where}
    ORDER BY ${ORDEN_SQL[filtros.orden]}
    OFFSET @offset ROWS FETCH NEXT @porPagina ROWS ONLY
  `);
  return result.recordset;
}

export async function findById(id: number): Promise<ReferenciaRow | null> {
  const pool = await getPool();
  const result = await pool.request()
    .input('id', sql.Int, id)
    .query<ReferenciaRow>(`SELECT ${CAMPOS} ${FROM_JOIN} WHERE r.id_referencia = @id`);
  return result.recordset[0] ?? null;
}

// ── Métricas de las tarjetas de resumen ─────────────────────
export interface Metricas {
  referenciasAnioActual: number;
  registradasHoy:        number;
  registradasMes:        number;
  eliminadas:             number;
}

export async function obtenerMetricas(): Promise<Metricas> {
  const pool = await getPool();
  const result = await pool.request().query<{
    referencias_anio_actual: number; registradas_hoy: number; registradas_mes: number; eliminadas: number;
  }>(`
    SELECT
      SUM(CASE WHEN condicion = 'VIGENTE' AND anio = YEAR(GETDATE()) THEN 1 ELSE 0 END) AS referencias_anio_actual,
      SUM(CASE WHEN condicion = 'VIGENTE' AND CAST(fecha_creacion AS DATE) = CAST(GETDATE() AS DATE) THEN 1 ELSE 0 END) AS registradas_hoy,
      SUM(CASE WHEN condicion = 'VIGENTE' AND YEAR(fecha_creacion) = YEAR(GETDATE()) AND MONTH(fecha_creacion) = MONTH(GETDATE()) THEN 1 ELSE 0 END) AS registradas_mes,
      SUM(CASE WHEN condicion = 'ELIMINADO' THEN 1 ELSE 0 END) AS eliminadas
    FROM libro_referencia
  `);
  const r = result.recordset[0];
  return {
    referenciasAnioActual: r?.referencias_anio_actual ?? 0,
    registradasHoy:        r?.registradas_hoy ?? 0,
    registradasMes:        r?.registradas_mes ?? 0,
    eliminadas:             r?.eliminadas ?? 0,
  };
}

// ── resolverNombreCreador — nombre a mostrar del usuario autenticado ──
// Prioriza nombres+apellidos del funcionario vinculado (igual criterio que
// documento.repository.ts para "ingresadoPor"); si no hay funcionario
// vinculado, usa el nombre de usuario de login como respaldo.
export async function resolverNombreCreador(idUsuario: number): Promise<string> {
  const pool = await getPool();
  const result = await pool.request()
    .input('id', sql.Int, idUsuario)
    .query<{ usuario: string; nombres: string | null; apellidos: string | null }>(`
      SELECT u.usuario, f.nombres, f.apellidos
      FROM usuario u
      LEFT JOIN funcionario f ON f.id_funcionario = u.id_funcionario
      WHERE u.id_usuario = @id
    `);
  const r = result.recordset[0];
  if (!r) return 'Desconocido';
  const nombreCompleto = [r.nombres, r.apellidos].filter(Boolean).join(' ').trim();
  return nombreCompleto || r.usuario;
}

// ── crear — genera el correlativo REF-AAAA-NNNNNN de forma transaccional ──
// Regla de negocio: el correlativo es único solo entre los registros VIGENTES
// del mismo año. Al eliminar lógicamente una referencia, su número queda
// libre y debe asignarse al SIGUIENTE registro que se cree — se usa el menor
// entero positivo no ocupado por una referencia vigente de ese año (no
// MAX(numero)+1, que ignoraría los huecos dejados por eliminaciones).
//
// Algoritmo (patrón estándar de "primer hueco libre" en SQL):
// candidatos = {1} ∪ {numero+1 de cada vigente del año} — el candidato
// correcto es el menor de ellos que NO coincide con el numero de ningún
// vigente. Ejemplo: vigentes {1,3} → candidatos {1,2,4} → 1 está ocupado,
// 2 no lo está → resultado 2.
//
// El cálculo y el INSERT ocurren en una única transacción bajo
// TABLOCKX+HOLDLOCK sobre libro_referencia — el lock exclusivo de tabla se
// mantiene durante toda la transacción, así dos requests concurrentes jamás
// calculan ni asignan el mismo número (ver también el índice único filtrado
// uq_libro_referencia_anio_numero_vigente, que actúa como segunda barrera).
// El año se calcula con GETDATE() en el servidor (contenedor SQL Server
// configurado con TZ=America/Santiago, ver docker-compose.yml) — nunca se
// acepta el año desde el cliente.
export async function crear(dto: CrearReferenciaDto, idUsuario: number, nombreUsuario: string): Promise<ReferenciaRow> {
  const pool = await getPool();
  const result = await pool.request()
    .input('idUsr',      sql.Int,          idUsuario)
    .input('nombreUsr',  sql.VarChar(100), nombreUsuario.substring(0, 100))
    .input('nombreInt',  sql.VarChar(150), dto.nombreInteresado.substring(0, 150))
    .input('tipoTram',   sql.VarChar(150), dto.tipoTramite.substring(0, 150))
    .input('fechaDoc',   sql.Date,         new Date(dto.fechaDocumento))
    .input('fechaRec',   sql.Date,         new Date(dto.fechaRecepcion))
    .input('obs',        sql.VarChar(1000), dto.observaciones?.substring(0, 1000) || null)
    .query<ReferenciaRow>(`
      BEGIN TRANSACTION;

      DECLARE @anio INT = YEAR(GETDATE());
      DECLARE @sigNumero INT;

      SELECT @sigNumero = MIN(candidato)
      FROM (
        SELECT 1 AS candidato
        UNION ALL
        SELECT numero + 1 AS candidato
        FROM libro_referencia WITH (TABLOCKX, HOLDLOCK)
        WHERE anio = @anio AND condicion = 'VIGENTE'
      ) c
      WHERE NOT EXISTS (
        SELECT 1 FROM libro_referencia r WITH (TABLOCKX, HOLDLOCK)
        WHERE r.anio = @anio AND r.condicion = 'VIGENTE' AND r.numero = c.candidato
      );

      DECLARE @codigo VARCHAR(20) = 'REF-' + CAST(@anio AS VARCHAR(4)) + '-'
        + RIGHT('000000' + CAST(@sigNumero AS VARCHAR(6)), 6);

      INSERT INTO libro_referencia
        (anio, numero, codigo, id_usuario_creador, nombre_usuario_creador,
         nombre_interesado, tipo_tramite, fecha_documento, fecha_recepcion, observaciones,
         condicion, fecha_creacion, fecha_actualizacion)
      OUTPUT INSERTED.id_referencia, INSERTED.anio, INSERTED.numero, INSERTED.codigo,
             INSERTED.id_usuario_creador, INSERTED.nombre_usuario_creador,
             INSERTED.nombre_interesado, INSERTED.tipo_tramite,
             INSERTED.fecha_documento, INSERTED.fecha_recepcion, INSERTED.observaciones,
             INSERTED.condicion, INSERTED.fecha_creacion, INSERTED.fecha_actualizacion
      VALUES
        (@anio, @sigNumero, @codigo, @idUsr, @nombreUsr,
         @nombreInt, @tipoTram, @fechaDoc, @fechaRec, @obs,
         'VIGENTE', GETDATE(), GETDATE());

      COMMIT TRANSACTION;
    `);
  return result.recordset[0];
}

// ── actualizar — solo campos autorizados; correlativo/año/creador intactos ──
export async function actualizar(id: number, dto: EditarReferenciaDto): Promise<void> {
  const pool = await getPool();
  await pool.request()
    .input('id',        sql.Int,          id)
    .input('nombreInt', sql.VarChar(150), dto.nombreInteresado.substring(0, 150))
    .input('tipoTram',  sql.VarChar(150), dto.tipoTramite.substring(0, 150))
    .input('fechaDoc',  sql.Date,         new Date(dto.fechaDocumento))
    .input('fechaRec',  sql.Date,         new Date(dto.fechaRecepcion))
    .input('obs',       sql.VarChar(1000), dto.observaciones?.substring(0, 1000) || null)
    .query(`
      UPDATE libro_referencia SET
        nombre_interesado   = @nombreInt,
        tipo_tramite        = @tipoTram,
        fecha_documento     = @fechaDoc,
        fecha_recepcion     = @fechaRec,
        observaciones       = @obs,
        fecha_actualizacion = GETDATE()
      WHERE id_referencia = @id
    `);
}

// ── eliminarLogico — transaccional, reversible ante fallo ──────
// Solo afecta la fila propia; no toca otros correlativos ni otras tablas.
export async function eliminarLogico(id: number, idUsuario: number, motivo: string): Promise<void> {
  const pool = await getPool();
  await pool.request()
    .input('id',      sql.Int,          id)
    .input('idUsr',   sql.Int,          idUsuario)
    .input('motivo',  sql.VarChar(500), motivo.substring(0, 500))
    .query(`
      BEGIN TRANSACTION;

      UPDATE libro_referencia SET
        condicion              = 'ELIMINADO',
        id_usuario_eliminacion = @idUsr,
        fecha_eliminacion      = GETDATE(),
        motivo_eliminacion     = @motivo,
        fecha_actualizacion    = GETDATE()
      WHERE id_referencia = @id AND condicion = 'VIGENTE';

      COMMIT TRANSACTION;
    `);
}

// ── eliminarDefinitivo — nivel 2, eliminación física del registro ──
// Solo procede sobre un registro que YA está en condición ELIMINADO — el
// guard "AND condicion = 'ELIMINADO'" va dentro del propio DELETE (no en un
// SELECT previo separado), así queda protegido ante condiciones de carrera:
// si dos solicitudes de eliminación definitiva llegan casi al mismo tiempo,
// como máximo una de ellas borra la fila (la otra recibe 0 filas afectadas
// y el servicio la trata como conflicto, sin duplicar ni fallar a medias).
//
// libro_referencia no tiene ninguna tabla que la referencie por FK (0 claves
// foráneas entrantes, verificado antes de implementar esto) y `auditoria`
// no depende de la fila por FK — es texto libre (`recurso`) — por lo que la
// eliminación física no puede dejar referencias rotas en ningún otro punto
// del sistema. OUTPUT DELETED.* captura exactamente la fila borrada dentro
// de la misma transacción, para que el llamador pueda construir la
// evidencia de auditoría con los datos reales que existían al momento del
// borrado (la auditoría se escribe después, ya con la fila ya no existente).
export async function eliminarDefinitivo(id: number): Promise<ReferenciaRow | null> {
  const pool = await getPool();
  const result = await pool.request()
    .input('id', sql.Int, id)
    .query<ReferenciaRow>(`
      BEGIN TRANSACTION;

      DELETE FROM libro_referencia
      OUTPUT DELETED.id_referencia, DELETED.anio, DELETED.numero, DELETED.codigo,
             DELETED.id_usuario_creador, DELETED.nombre_usuario_creador,
             DELETED.nombre_interesado, DELETED.tipo_tramite,
             DELETED.fecha_documento, DELETED.fecha_recepcion, DELETED.observaciones,
             DELETED.condicion, DELETED.id_usuario_eliminacion,
             DELETED.fecha_eliminacion, DELETED.motivo_eliminacion,
             DELETED.fecha_creacion, DELETED.fecha_actualizacion
      WHERE id_referencia = @id AND condicion = 'ELIMINADO';

      COMMIT TRANSACTION;
    `);
  return result.recordset[0] ?? null;
}
