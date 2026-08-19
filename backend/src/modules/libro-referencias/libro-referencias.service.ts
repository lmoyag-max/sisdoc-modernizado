import { getPool } from '../../config/database';
import { logAuditoria } from '../../shared/utils/auditoria';
import { buildPaginationMeta } from '../../shared/utils/response';
import * as repo from './libro-referencias.repository';
import { CrearReferenciaDto, EditarReferenciaDto, FiltrosReferenciaDto } from './libro-referencias.schema';

function mapReferencia(r: repo.ReferenciaRow) {
  return {
    id:                r.id_referencia,
    codigo:            r.codigo,
    anio:              r.anio,
    numero:            r.numero,
    usuarioCreador: {
      id:     r.id_usuario_creador,
      nombre: r.nombre_usuario_creador,
    },
    nombreInteresado:  r.nombre_interesado,
    tipoTramite:       r.tipo_tramite,
    fechaDocumento:    r.fecha_documento,
    fechaRecepcion:    r.fecha_recepcion,
    observaciones:     r.observaciones,
    condicion:         r.condicion,
    eliminacion: r.condicion === 'ELIMINADO' ? {
      usuario: r.usuario_eliminacion,
      fecha:   r.fecha_eliminacion,
      motivo:  r.motivo_eliminacion,
    } : null,
    fechaCreacion:     r.fecha_creacion,
    fechaActualizacion: r.fecha_actualizacion,
  };
}

export async function listar(filtros: FiltrosReferenciaDto, soloEliminados = false) {
  const rows  = await repo.findMany(filtros, soloEliminados);
  const total = rows[0]?.total ?? 0;
  return { data: rows.map(mapReferencia), meta: buildPaginationMeta(total, filtros.pagina, filtros.porPagina) };
}

export async function obtener(id: number) {
  const row = await repo.findById(id);
  if (!row) throw { statusCode: 404, message: 'Referencia no encontrada' };
  return mapReferencia(row);
}

export async function metricas() {
  return repo.obtenerMetricas();
}

export async function crear(
  dto: CrearReferenciaDto,
  actor: { idUsuario: number; nombre: string },
  ip: string | null,
) {
  const row  = await repo.crear(dto, actor.idUsuario, actor.nombre);
  const pool = await getPool();
  // recurso = ID técnico, no el código visible: el código puede reutilizarse
  // entre un registro eliminado y uno nuevo vigente (ver sección 8 del
  // pliego de corrección de correlativos) — usar el código como referencia
  // haría ambigua la auditoría en cuanto ese código se reasigne.
  await logAuditoria(pool, {
    idUsuario: actor.idUsuario,
    accion:    'LIBRO_REFERENCIA_CREADO',
    recurso:   String(row.id_referencia),
    detalle:   `código: ${row.codigo} — interesado: ${dto.nombreInteresado} — trámite: ${dto.tipoTramite}`,
    ip,
  });
  return mapReferencia(row);
}

const CAMPOS_EDITABLES: Array<{ key: keyof EditarReferenciaDto; label: string; get: (r: repo.ReferenciaRow) => string }> = [
  { key: 'nombreInteresado', label: 'Nombre interesado', get: (r) => r.nombre_interesado },
  { key: 'tipoTramite',      label: 'Tipo de trámite',   get: (r) => r.tipo_tramite },
  { key: 'fechaDocumento',   label: 'Fecha documento',   get: (r) => new Date(r.fecha_documento).toISOString().slice(0, 10) },
  { key: 'fechaRecepcion',   label: 'Fecha recepción',   get: (r) => new Date(r.fecha_recepcion).toISOString().slice(0, 10) },
  { key: 'observaciones',    label: 'Observaciones',     get: (r) => r.observaciones ?? '' },
];

export async function actualizar(
  id: number,
  dto: EditarReferenciaDto,
  actor: { idUsuario: number },
  ip: string | null,
) {
  const previo = await repo.findById(id);
  if (!previo) throw { statusCode: 404, message: 'Referencia no encontrada' };
  if (previo.condicion === 'ELIMINADO') throw { statusCode: 409, message: 'No se puede editar una referencia eliminada' };

  // Diff campo por campo para la auditoría — se registra solo lo que realmente cambió.
  const cambios: string[] = [];
  for (const campo of CAMPOS_EDITABLES) {
    const anterior = campo.get(previo);
    const nuevo    = String(dto[campo.key] ?? '');
    if (anterior !== nuevo) cambios.push(`${campo.label}: "${anterior}" -> "${nuevo}"`);
  }

  await repo.actualizar(id, dto);

  if (cambios.length > 0) {
    const pool = await getPool();
    await logAuditoria(pool, {
      idUsuario: actor.idUsuario,
      accion:    'LIBRO_REFERENCIA_EDITADO',
      recurso:   String(previo.id_referencia),
      detalle:   `código: ${previo.codigo} — ${cambios.join('; ')}`.substring(0, 500),
      ip,
    });
  }

  return obtener(id);
}

export async function eliminar(
  id: number,
  motivo: string,
  actor: { idUsuario: number },
  ip: string | null,
) {
  const previo = await repo.findById(id);
  if (!previo) throw { statusCode: 404, message: 'Referencia no encontrada' };
  if (previo.condicion === 'ELIMINADO') throw { statusCode: 409, message: 'La referencia ya está eliminada' };

  await repo.eliminarLogico(id, actor.idUsuario, motivo);

  const pool = await getPool();
  await logAuditoria(pool, {
    idUsuario: actor.idUsuario,
    accion:    'LIBRO_REFERENCIA_ELIMINADO',
    recurso:   String(previo.id_referencia),
    detalle:   `código: ${previo.codigo} — motivo: ${motivo}`,
    ip,
  });

  return obtener(id);
}

// ── eliminarDefinitivo — Nivel 2: eliminación física, exclusiva del
// Superadministrador, solo sobre un registro que YA está en condición
// ELIMINADO. Distinto del endpoint/acción de eliminación lógica (`eliminar`)
// para no generar ambigüedad — ver libro-referencias.routes.ts.
//
// El registro se identifica exclusivamente por su ID técnico (id_referencia)
// — nunca por el código visible, que puede repetirse entre un histórico
// eliminado y un vigente actual (ver sección 4 del pliego). findById() ya
// resuelve por ID técnico, así que no hay ambigüedad posible aunque existan
// varios registros con el mismo código.
export async function eliminarDefinitivo(
  id: number,
  motivo: string,
  actor: { idUsuario: number },
  ip: string | null,
) {
  const previo = await repo.findById(id);
  if (!previo) throw { statusCode: 404, message: 'Referencia no encontrada' };
  if (previo.condicion !== 'ELIMINADO') {
    throw { statusCode: 409, message: 'Solo se puede eliminar definitivamente una referencia que ya se encuentre eliminada' };
  }

  const borrado = await repo.eliminarDefinitivo(id);
  if (!borrado) {
    // Carrera: la referencia dejó de estar en condición ELIMINADO entre la
    // verificación anterior y el DELETE guardado (ej. otra solicitud de
    // eliminación definitiva ya la borró). No se ejecutó ningún cambio.
    throw { statusCode: 409, message: 'La referencia ya no está disponible para eliminación definitiva' };
  }

  // Evidencia mínima e inalterable en el sistema de auditoría central — no
  // depende de que la fila de libro_referencia siga existiendo (auditoria no
  // tiene FK hacia ella, verificado antes de implementar esto). Se registra
  // ANTES de que el llamador reciba la respuesta, dentro del mismo flujo
  // atómico a nivel de aplicación (si esto fallara, el registro ya fue
  // borrado pero quedaría sin evidencia — logAuditoria() ya maneja sus
  // propios errores sin romper la respuesta, igual que el resto de DOC360).
  const pool = await getPool();
  const detalle = [
    `código: ${previo.codigo} (${previo.anio}/${previo.numero})`,
    `interesado: ${previo.nombre_interesado}`,
    `trámite: ${previo.tipo_tramite}`,
    `creado por: ${previo.nombre_usuario_creador}`,
    `eliminación lógica: ${previo.usuario_eliminacion ?? 'desconocido'} el ${formatFechaAuditoria(previo.fecha_eliminacion)} — motivo: ${previo.motivo_eliminacion ?? ''}`,
    `eliminación definitiva — motivo: ${motivo}`,
  ].join(' | ');

  await logAuditoria(pool, {
    idUsuario: actor.idUsuario,
    accion:    'ELIMINACION_DEFINITIVA_LIBRO_REFERENCIAS',
    recurso:   String(previo.id_referencia),
    detalle:   detalle.substring(0, 500),
    ip,
  });

  // El registro ya no existe en la BD — se devuelve un snapshot de los
  // datos que tenía justo antes de borrarse, útil para que el frontend
  // confirme visualmente qué se eliminó.
  return mapReferencia(previo);
}

function formatFechaAuditoria(fecha: Date | null): string {
  if (!fecha) return 'fecha desconocida';
  return new Date(fecha).toISOString();
}
