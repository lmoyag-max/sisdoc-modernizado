import * as repo from './documento.repository';
import { getPool, sql } from '../../config/database';
import {
  CrearDocumentoDto, DespacharDto, RecepcionarDto,
  DerivarDto, TerminarDto, FiltrosDocumentoDto,
} from './documento.schema';
import { buildPaginationMeta } from '../../shared/utils/response';

// ── Listar ───────────────────────────────────────────────────

interface FiltroServicio {
  idDependencia: number | null;
  verExternos:   boolean;
}

export async function listarDocumentos(filtros: FiltrosDocumentoDto, filtroServicio?: FiltroServicio | null) {
  const rows = await repo.findMany(filtros, filtroServicio ?? null);
  const total = rows[0]?.total ?? 0;
  return { data: rows.map(mapDocumento), meta: buildPaginationMeta(total, filtros.pagina, filtros.porPagina) };
}

// ── Control de acceso a documento individual ──────────────────
// Retorna true si el usuario tiene relación con el documento (origen o destino).
export async function usuarioTieneAccesoDocumento(
  idDocumento:    number,
  idDependencia:  number | null,
  verExternos:    boolean,
): Promise<boolean> {
  if (!idDependencia) return false;
  const pool = await getPool();
  const r = await pool.request()
    .input('idDoc', sql.Int, idDocumento)
    .input('idDep', sql.Int, idDependencia)
    .query<{ ok: number }>(`
      SELECT TOP 1 1 AS ok
      FROM tramite t
      WHERE t.id_documento = @idDoc
        AND (
          (t.id_destino     = @idDep AND t.tipo_destinatario = 'D')
          OR (t.id_procedencia = @idDep AND t.tipo_procedencia  = 'D')
          ${verExternos ? "OR t.tipo_destinatario = 'E'" : ''}
        )
    `);
  return !!r.recordset[0];
}

// ── Obtener uno ───────────────────────────────────────────────

export async function obtenerDocumento(idDocumento: number) {
  const row = await repo.findById(idDocumento);
  if (!row) throw { statusCode: 404, message: 'Documento no encontrado' };

  const trazabilidad = await repo.findTrazabilidad(idDocumento);
  const tramiteActual = trazabilidad[trazabilidad.length - 1] ?? null;

  return {
    ...mapDocumento(row),
    tramiteActual: tramiteActual ? mapTramite(tramiteActual) : null,
  };
}

// ── Trazabilidad ──────────────────────────────────────────────

export async function obtenerTrazabilidad(idDocumento: number) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };
  const rows = await repo.findTrazabilidad(idDocumento);
  return rows.map(mapTramite);
}

// ── Crear ─────────────────────────────────────────────────────
// Regla de negocio: el origen se asigna SIEMPRE desde la dependencia del
// usuario autenticado. El estado inicial es siempre DESPACHADO (2).
// El frontend no puede modificar estas reglas.
export async function crearDocumento(
  dto: CrearDocumentoDto,
  idUsuario: number,
  idDependenciaUsuario: number | null,
) {
  // Origen siempre = dependencia del usuario autenticado
  const idProcedencia = idDependenciaUsuario ?? 1;
  // Destino desde el formulario (si no se provee, mismo origen como fallback)
  const idDestino = dto.idDestino ?? idProcedencia;

  const { idDocumento, idSeguimiento } = await repo.insert({
    idTipoDocumento:    dto.idTipoDocumento,
    materia:            dto.materia,
    idEstadoDocumento:  2,          // Siempre DESPACHADO al crear
    idUsuario,
    fechaDocumento:     dto.fechaDocumento ? new Date(dto.fechaDocumento) : undefined,
    idExpediente:       dto.idExpediente,
    original:           dto.original ?? 'S',
    medio:              dto.medio,
    tipoProcedencia:    'D',        // Origen siempre interno (dependencia del usuario)
    idProcedencia,                  // Dependencia del usuario autenticado
    tipoDestinatario:   dto.tipoDestinatario,
    idDestino,
    idTipoDistribucion: dto.idTipoDistribucion,
    idTipoCompromiso:   dto.idTipoCompromiso,
    idEstadoCompromiso: dto.idEstadoCompromiso,
    diasCompromiso:     dto.diasCompromiso,
    observaciones:      dto.observaciones,
  });

  // Siempre marcar el trámite inicial como despachado con fecha actual
  await repo.updateTramiteEstado(idSeguimiento, 2, { fechaDespacho: new Date() });

  return obtenerDocumento(idDocumento);
}

// ── Despachar ─────────────────────────────────────────────────

export async function despacharDocumento(idDocumento: number, dto: DespacharDto, idUsuario: number) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };
  if (doc.id_estado_documento === 4) throw { statusCode: 400, message: 'El documento ya está terminado' };

  const tramiteActual = await repo.getLastTramite(idDocumento);

  // Solo marcar como Despachado si el tramite aún está en Generado (estado 1).
  // Para estados avanzados (Recepcionado, Derivado…) el registro histórico NO se toca —
  // cada re-despacho inserta un nuevo tramite, preservando la trazabilidad íntegra.
  if (tramiteActual && tramiteActual.id_estado_tramite === 1) {
    await repo.updateTramiteEstado(tramiteActual.id_seguimiento, 2, { fechaDespacho: new Date() });
  }

  // Insertar nuevo tramite representando el despacho/reasignación
  const pool = await getPool();
  await pool.request()
    .input('idDoc',    sql.Int,          idDocumento)
    .input('idUsr',    sql.Int,          idUsuario)
    .input('idProc',   sql.Int,          tramiteActual?.id_destino ?? 1)
    .input('idDest',   sql.Int,          dto.idDestino)
    .input('tipProc',  sql.Char(1),      tramiteActual?.tipo_destinatario ?? 'D')
    .input('tipDest',  sql.Char(1),      dto.tipoDestinatario)
    .input('idTipDis', sql.Int,          dto.idTipoDistribucion)
    .input('idTipCom', sql.Int,          dto.idTipoCompromiso)
    .input('idEstCom', sql.Int,          dto.idEstadoCompromiso)
    .input('dias',     sql.Int,          dto.diasCompromiso)
    .input('obs',      sql.VarChar(250), (dto.observaciones ?? '').substring(0, 250))
    .query(`
      INSERT INTO tramite
        (id_documento, id_usuario, id_procedencia, id_destino,
         tipo_procedencia, tipo_destinatario,
         id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
         id_estado_tramite, dias_compromiso, observaciones,
         fecha_sistema, fecha_update, fecha_despacho)
      VALUES
        (@idDoc, @idUsr, @idProc, @idDest,
         @tipProc, @tipDest,
         @idTipDis, @idTipCom, @idEstCom,
         2, @dias, @obs,
         GETDATE(), GETDATE(), GETDATE())
    `);

  await repo.updateEstado(idDocumento, 2);
  return obtenerDocumento(idDocumento);
}

// ── Recepcionar ───────────────────────────────────────────────

export async function recepcionarDocumento(idDocumento: number, dto: RecepcionarDto, idUsuario: number) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };

  const tramiteActual = await repo.getLastTramite(idDocumento);
  if (tramiteActual) {
    await repo.updateTramiteEstado(tramiteActual.id_seguimiento, 3, {
      fechaRecepcion: new Date(),
      usuarioRecepcion: idUsuario,
    });
    // Actualizar observaciones si se enviaron
    if (dto.observaciones) {
      const pool = await getPool();
      await pool.request()
        .input('obs', sql.VarChar(250), dto.observaciones.substring(0, 250))
        .input('id',  sql.Int,          tramiteActual.id_seguimiento)
        .query('UPDATE tramite SET observaciones = @obs WHERE id_seguimiento = @id');
    }
  }

  await repo.updateEstado(idDocumento, 3);
  return obtenerDocumento(idDocumento);
}

// ── Derivar ───────────────────────────────────────────────────

export async function derivarDocumento(idDocumento: number, dto: DerivarDto, idUsuario: number) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };
  if (doc.id_estado_documento === 4) throw { statusCode: 400, message: 'El documento ya está terminado' };

  const tramiteActual = await repo.getLastTramite(idDocumento);

  // Nuevo tramite de derivación (estado 4 = Derivado)
  const pool = await getPool();
  await pool.request()
    .input('idDoc',    sql.Int,        idDocumento)
    .input('idUsr',    sql.Int,        idUsuario)
    .input('idProc',   sql.Int,        tramiteActual?.id_destino ?? 1)
    .input('idDest',   sql.Int,        dto.idDestino)
    .input('tipProc',  sql.Char(1),    tramiteActual?.tipo_destinatario ?? 'D')
    .input('tipDest',  sql.Char(1),    dto.tipoDestinatario)
    .input('idTipDis', sql.Int,        dto.idTipoDistribucion)
    .input('idTipCom', sql.Int,        dto.idTipoCompromiso)
    .input('dias',     sql.Int,        dto.diasCompromiso)
    .input('obs',      sql.VarChar(250), (dto.observaciones ?? '').substring(0, 250))
    .query(`
      INSERT INTO tramite
        (id_documento, id_usuario, id_procedencia, id_destino,
         tipo_procedencia, tipo_destinatario,
         id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
         id_estado_tramite, dias_compromiso, observaciones,
         fecha_sistema, fecha_update)
      VALUES
        (@idDoc, @idUsr, @idProc, @idDest,
         @tipProc, @tipDest,
         @idTipDis, @idTipCom, 2,
         4, @dias, @obs,
         GETDATE(), GETDATE())
    `);

  await repo.updateEstado(idDocumento, 2); // Vuelve a Despachado
  return obtenerDocumento(idDocumento);
}

// ── Terminar ──────────────────────────────────────────────────

export async function terminarDocumento(idDocumento: number, dto: TerminarDto, idUsuario: number) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };
  if (doc.id_estado_documento === 4) throw { statusCode: 400, message: 'El documento ya está terminado' };

  const tramiteActual = await repo.getLastTramite(idDocumento);
  if (tramiteActual) {
    await repo.updateTramiteEstado(tramiteActual.id_seguimiento, 5); // 5=Cerrado
    if (dto.observaciones) {
      const pool = await getPool();
      await pool.request()
        .input('obs', sql.VarChar(250), dto.observaciones.substring(0, 250))
        .input('id',  sql.Int,          tramiteActual.id_seguimiento)
        .query('UPDATE tramite SET observaciones = @obs WHERE id_seguimiento = @id');
    }
  }

  await repo.updateEstado(idDocumento, 4); // 4=Terminado
  return obtenerDocumento(idDocumento);
}

// ── Eliminar (solo coordinador/admin) ─────────────────────────

export async function eliminarDocumento(idDocumento: number, idUsuario: number) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };
  await repo.softDelete(idDocumento, idUsuario);
}

// ── Mappers ───────────────────────────────────────────────────

function mapDocumento(row: repo.DocumentoRow) {
  return {
    idDocumento:    row.id_documento,
    numDocumento:   row.num_oficial ?? row.num_interno,
    numInterno:     row.num_interno,
    numOficial:     row.num_oficial,
    materia:        row.materia,
    asunto:         row.materia,
    tipoDocumento:  { id: row.id_tipo_documento, descripcion: row.desc_tipo_documento },
    estadoDocumento:{ id: row.id_estado_documento, descripcion: row.desc_estado_documento },
    prioridad:      { id: null, descripcion: null, color: null },
    destino:        { id: null, descripcion: null },
    procedencia:    { id: null, descripcion: null },
    ingresadoPor: {
      id: row.id_usuario, usuario: row.usuario,
      nombre: [row.nombres, row.apellidos].filter(Boolean).join(' '),
    },
    idExpediente:   row.id_expediente,
    fechaDocumento: row.fecha_documento,
    fechaIngreso:   row.fecha_sistema,
    fechaCierre:    null,
    observacion:    null,
  };
}

function mapTramite(t: repo.TramiteRow) {
  return {
    idSeguimiento:       t.id_seguimiento,
    idDocumento:         t.id_documento,
    estadoTramite:       { id: t.id_estado_tramite, descripcion: t.desc_estado_tramite },
    procedencia:         { id: t.id_procedencia, descripcion: t.desc_procedencia, tipo: t.tipo_procedencia },
    destino:             { id: t.id_destino, descripcion: t.desc_destino, tipo: t.tipo_destinatario },
    tipoDistribucion:    { id: t.id_tipo_distribucion, descripcion: t.desc_tipo_distribucion },
    tipoCompromiso:      { id: t.id_tipo_compromiso, descripcion: t.desc_tipo_compromiso },
    diasCompromiso:      t.dias_compromiso,
    observaciones:       t.observaciones,
    fechaSistema:        t.fecha_sistema,
    fechaDespacho:       t.fecha_despacho,
    fechaRecepcion:      t.fecha_recepcion,
    usuario:             { usuario: t.usuario, nombre: t.nombres_usuario },
  };
}
