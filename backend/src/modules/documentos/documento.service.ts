import * as repo from './documento.repository';
import { getPool, sql } from '../../config/database';
import {
  CrearDocumentoDto, DespacharDto, RecepcionarDto,
  DerivarDto, TerminarDto, ReabrirDto, FiltrosDocumentoDto,
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

// ── Buscar por número exacto ──────────────────────────────────
export async function buscarDocumentosPorNumero(numero: number) {
  const rows = await repo.findByNumero(numero);
  return rows.map(mapDocumento);
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

// ID de la dependencia "Dirección" (forzado cuando reservado=true)
const ID_DIRECCION = 32;

// ── Crear ─────────────────────────────────────────────────────
// Regla de negocio: el origen se asigna SIEMPRE desde la dependencia del
// usuario autenticado. El estado inicial es siempre DESPACHADO (2).
// Soporta 1 o N destinos. Si destinos[] tiene >1 elemento, crea un tramite
// y un documento_destino por cada servicio. Backward compat: si solo
// llega idDestino, crea exactamente 1 destino (flujo previo intacto).
// Extensión Oficina de Partes:
//   tipoSoporte='F' → documento físico/papel (almacenado en medio='F')
//   reservado=true  → destino forzado a Dirección (id=32), almacenado en resuelto='S'
export async function crearDocumento(
  dto: CrearDocumentoDto,
  idUsuario: number,
  idDependenciaUsuario: number | null,
) {
  const idProcedencia = idDependenciaUsuario ?? 1;
  const esReservado   = dto.reservado === true;
  const esFisico      = dto.tipoSoporte === 'F';

  // Si es reservado, el destino siempre es Dirección — ignorar lo que envíe el frontend
  const listaDestinos: number[] = esReservado
    ? [ID_DIRECCION]
    : dto.destinos && dto.destinos.length > 0
      ? dto.destinos
      : [dto.idDestino ?? idProcedencia];

  // Prefijo de trazabilidad en observaciones del trámite inicial
  const prefijos: string[] = [];
  if (esFisico)    prefijos.push('[SOPORTE:FÍSICO]');
  if (esReservado) prefijos.push('[RESERVADO→DIRECCIÓN]');
  const obsConPrefijo = prefijos.length > 0
    ? `${prefijos.join(' ')} ${dto.observaciones ?? ''}`.trim().substring(0, 250)
    : dto.observaciones;

  // Crear el documento principal (solo 1 vez)
  const { idDocumento, idSeguimiento } = await repo.insert({
    idTipoDocumento:    dto.idTipoDocumento,
    materia:            dto.materia,
    idEstadoDocumento:  2,
    idUsuario,
    fechaDocumento:     dto.fechaDocumento ? new Date(dto.fechaDocumento) : undefined,
    original:           dto.original ?? 'S',
    medio:              esFisico ? 'F' : (dto.medio ?? null) ?? undefined,
    resuelto:           esReservado ? 'S' : undefined,
    tipoProcedencia:    'D',
    idProcedencia,
    tipoDestinatario:   esReservado ? 'D' : (listaDestinos.length === 1 ? dto.tipoDestinatario : 'D'),
    idDestino:          listaDestinos[0],
    idTipoDistribucion: dto.idTipoDistribucion,
    idTipoCompromiso:   dto.idTipoCompromiso,
    idEstadoCompromiso: dto.idEstadoCompromiso,
    diasCompromiso:     dto.diasCompromiso,
    observaciones:      obsConPrefijo,
  });
  await repo.updateTramiteEstado(idSeguimiento, 2, { fechaDespacho: new Date() });

  // Registrar el primer destino en documento_destino (primer elemento de la lista)
  await repo.insertDocumentoDestino({
    idDocumento,
    idDestino:         listaDestinos[0],
    tipoDestinatario:  listaDestinos.length === 1 ? dto.tipoDestinatario : 'D',
    idTipoCompromiso:  dto.idTipoCompromiso,
    diasCompromiso:    dto.diasCompromiso,
    idUsuarioCreacion: idUsuario,
  });

  // Si hay destinos adicionales, crear un tramite + documento_destino por cada uno
  if (listaDestinos.length > 1) {
    const pool = await getPool();
    for (const idDest of listaDestinos.slice(1)) {
      const tramRes = await pool.request()
        .input('idDoc',    sql.Int,          idDocumento)
        .input('idUsr',    sql.Int,          idUsuario)
        .input('idProc',   sql.Int,          idProcedencia)
        .input('idDest',   sql.Int,          idDest)
        .input('idTipDis', sql.Int,          dto.idTipoDistribucion)
        .input('idTipCom', sql.Int,          dto.idTipoCompromiso)
        .input('idEstCom', sql.Int,          dto.idEstadoCompromiso)
        .input('dias',     sql.Int,          dto.diasCompromiso)
        .input('obs',      sql.VarChar(250), (dto.observaciones ?? '').substring(0, 250))
        .query<{ id_seguimiento: number }>(`
          INSERT INTO tramite
            (id_documento, id_usuario, id_procedencia, id_destino,
             tipo_procedencia, tipo_destinatario,
             id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
             id_estado_tramite, dias_compromiso, observaciones,
             fecha_sistema, fecha_update, fecha_despacho)
          OUTPUT INSERTED.id_seguimiento
          VALUES
            (@idDoc, @idUsr, @idProc, @idDest,
             'D', 'D',
             @idTipDis, @idTipCom, @idEstCom,
             2, @dias, @obs,
             GETDATE(), GETDATE(), GETDATE())
        `);
      void tramRes; // tramite adicional insertado

      await repo.insertDocumentoDestino({
        idDocumento,
        idDestino:         idDest,
        tipoDestinatario:  'D',
        idTipoCompromiso:  dto.idTipoCompromiso,
        diasCompromiso:    dto.diasCompromiso,
        idUsuarioCreacion: idUsuario,
      });
    }
  }

  return obtenerDocumento(idDocumento);
}

// ── Obtener destinos de un documento ─────────────────────────
export async function obtenerDestinos(idDocumento: number) {
  const rows = await repo.findDestinosByDocumento(idDocumento);
  return rows.map((r) => ({
    id:               r.id,
    idDocumento:      r.id_documento,
    idDestino:        r.id_destino,
    tipoDestinatario: r.tipo_destinatario,
    estadoTramite:    { id: r.id_estado_tramite, descripcion: r.desc_estado_tramite },
    tipoCompromiso:   { id: r.id_tipo_compromiso, descripcion: r.desc_tipo_compromiso },
    diasCompromiso:   r.dias_compromiso,
    servicio:         r.desc_dependencia,
    fechaCreacion:    r.fecha_creacion,
    fechaRecepcion:   r.fecha_recepcion,
    fechaCierre:      r.fecha_cierre,
    idUsuarioRecepcion: r.id_usuario_recepcion,
    idUsuarioCierre:    r.id_usuario_cierre,
    observaciones:    r.observaciones,
  }));
}

// ── Recepcionar por destino específico (multi-destino) ────────
export async function recepcionarDestino(
  idDocumento: number,
  idDocumentoDestino: number,
  observaciones: string | undefined,
  idUsuario: number,
) {
  const destinos = await repo.findDestinosByDocumento(idDocumento);
  const dest     = destinos.find((d) => d.id === idDocumentoDestino);
  if (!dest) throw { statusCode: 404, message: 'Destino no encontrado en este documento' };
  if (dest.id_estado_tramite === 5) throw { statusCode: 400, message: 'Este destino ya está cerrado' };

  await repo.updateDocumentoDestinoEstado(idDocumentoDestino, 3, {
    idUsuarioRecepcion: idUsuario,
    fechaRecepcion: new Date(),
    observaciones,
  });

  // Insertar tramite de recepción para este destino
  const pool = await getPool();
  await pool.request()
    .input('idDoc',  sql.Int, idDocumento)
    .input('idUsr',  sql.Int, idUsuario)
    .input('idProc', sql.Int, dest.id_destino)
    .input('obs',    sql.VarChar(250), (observaciones ?? '').substring(0, 250))
    .query(`
      INSERT INTO tramite
        (id_documento, id_usuario, id_procedencia, id_destino,
         tipo_procedencia, tipo_destinatario,
         id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
         id_estado_tramite, dias_compromiso, observaciones,
         fecha_sistema, fecha_update, fecha_recepcion, usuario_recepcion)
      VALUES
        (@idDoc, @idUsr, @idProc, @idProc,
         'D', 'D', 5, 1, 2,
         3, 0, @obs,
         GETDATE(), GETDATE(), GETDATE(), @idUsr)
    `);

  // Si al menos 1 destino está recepcionado, marcar documento en estado 3
  await repo.updateEstado(idDocumento, 3);
  return obtenerDocumento(idDocumento);
}

// ── Terminar por destino específico (multi-destino) ───────────
export async function terminarDestino(
  idDocumento: number,
  idDocumentoDestino: number,
  observaciones: string | undefined,
  idUsuario: number,
) {
  const destinos = await repo.findDestinosByDocumento(idDocumento);
  const dest     = destinos.find((d) => d.id === idDocumentoDestino);
  if (!dest) throw { statusCode: 404, message: 'Destino no encontrado en este documento' };
  if (dest.id_estado_tramite === 5) throw { statusCode: 400, message: 'Este destino ya está cerrado' };

  await repo.updateDocumentoDestinoEstado(idDocumentoDestino, 5, {
    idUsuarioCierre: idUsuario,
    fechaCierre: new Date(),
    observaciones,
  });

  const pool = await getPool();
  await pool.request()
    .input('idDoc',  sql.Int, idDocumento)
    .input('idUsr',  sql.Int, idUsuario)
    .input('idProc', sql.Int, dest.id_destino)
    .input('obs',    sql.VarChar(250), (observaciones ?? '').substring(0, 250))
    .query(`
      INSERT INTO tramite
        (id_documento, id_usuario, id_procedencia, id_destino,
         tipo_procedencia, tipo_destinatario,
         id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
         id_estado_tramite, dias_compromiso, observaciones,
         fecha_sistema, fecha_update)
      VALUES
        (@idDoc, @idUsr, @idProc, @idProc,
         'D', 'D', 5, 1, 2,
         5, 0, @obs,
         GETDATE(), GETDATE())
    `);

  // Cierre global: solo si TODOS los destinos están cerrados
  const todosCerrados = await repo.allDestinosCerrados(idDocumento);
  if (todosCerrados) await repo.updateEstado(idDocumento, 4);

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
// Inserta un nuevo tramite (estado 3=Recepcionado) en lugar de actualizar
// el tramite de despacho. Preserva la trazabilidad íntegra.

export async function recepcionarDocumento(idDocumento: number, dto: RecepcionarDto, idUsuario: number) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };

  const ultimoTramite = await repo.getLastTramite(idDocumento);
  const idProc   = ultimoTramite?.id_destino ?? 1;
  const tipProc  = ultimoTramite?.tipo_destinatario ?? 'D';

  const pool = await getPool();
  await pool.request()
    .input('idDoc',  sql.Int,          idDocumento)
    .input('idUsr',  sql.Int,          idUsuario)
    .input('idProc', sql.Int,          idProc)
    .input('tipProc',sql.Char(1),      tipProc)
    .input('obs',    sql.VarChar(250), (dto.observaciones ?? '').substring(0, 250))
    .query(`
      INSERT INTO tramite
        (id_documento, id_usuario, id_procedencia, id_destino,
         tipo_procedencia, tipo_destinatario,
         id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
         id_estado_tramite, dias_compromiso, observaciones,
         fecha_sistema, fecha_update, fecha_recepcion, usuario_recepcion)
      VALUES
        (@idDoc, @idUsr, @idProc, @idProc,
         @tipProc, @tipProc,
         5, 1, 2,
         3, 0, @obs,
         GETDATE(), GETDATE(), GETDATE(), @idUsr)
    `);

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
// Inserta nuevo tramite (estado 5=Cerrado) para preservar trazabilidad.

export async function terminarDocumento(idDocumento: number, dto: TerminarDto, idUsuario: number) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };
  if (doc.id_estado_documento === 4) throw { statusCode: 400, message: 'El documento ya está terminado' };
  if (doc.id_estado_documento !== 3) throw { statusCode: 400, message: 'Solo se puede terminar un documento en estado Recepcionado' };

  const ultimoTramite = await repo.getLastTramite(idDocumento);
  const idProc  = ultimoTramite?.id_destino ?? 1;
  const tipProc = ultimoTramite?.tipo_destinatario ?? 'D';

  const pool = await getPool();
  await pool.request()
    .input('idDoc',  sql.Int,          idDocumento)
    .input('idUsr',  sql.Int,          idUsuario)
    .input('idProc', sql.Int,          idProc)
    .input('tipProc',sql.Char(1),      tipProc)
    .input('obs',    sql.VarChar(250), (dto.observaciones ?? '').substring(0, 250))
    .query(`
      INSERT INTO tramite
        (id_documento, id_usuario, id_procedencia, id_destino,
         tipo_procedencia, tipo_destinatario,
         id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
         id_estado_tramite, dias_compromiso, observaciones,
         fecha_sistema, fecha_update)
      VALUES
        (@idDoc, @idUsr, @idProc, @idProc,
         @tipProc, @tipProc,
         5, 1, 2,
         5, 0, @obs,
         GETDATE(), GETDATE())
    `);

  await repo.updateEstado(idDocumento, 4); // 4=Terminado
  return obtenerDocumento(idDocumento);
}

// ── Reabrir (Terminado → Recepcionado) ───────────────────────
// Inserta un nuevo tramite (estado 3=Recepcionado) dejando intacta toda la
// trazabilidad previa. El documento vuelve a estado Recepcionado.
export async function reabrirDocumento(idDocumento: number, dto: ReabrirDto, idUsuario: number) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };
  if (doc.id_estado_documento !== 4) throw { statusCode: 400, message: 'Solo se puede reabrir un documento en estado Terminado' };

  const ultimoTramite = await repo.getLastTramite(idDocumento);
  const idProc  = ultimoTramite?.id_destino ?? 1;
  const tipProc = ultimoTramite?.tipo_destinatario ?? 'D';
  const obsTexto = `Reabierto desde estado Terminado: ${dto.observaciones}`.substring(0, 250);

  const pool = await getPool();
  await pool.request()
    .input('idDoc',  sql.Int,          idDocumento)
    .input('idUsr',  sql.Int,          idUsuario)
    .input('idProc', sql.Int,          idProc)
    .input('tipProc',sql.Char(1),      tipProc)
    .input('obs',    sql.VarChar(250), obsTexto)
    .query(`
      INSERT INTO tramite
        (id_documento, id_usuario, id_procedencia, id_destino,
         tipo_procedencia, tipo_destinatario,
         id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
         id_estado_tramite, dias_compromiso, observaciones,
         fecha_sistema, fecha_update, fecha_recepcion, usuario_recepcion)
      VALUES
        (@idDoc, @idUsr, @idProc, @idProc,
         @tipProc, @tipProc,
         5, 1, 2,
         3, 0, @obs,
         GETDATE(), GETDATE(), GETDATE(), @idUsr)
    `);

  await repo.updateEstado(idDocumento, 3); // Vuelve a Recepcionado
  return obtenerDocumento(idDocumento);
}

// ── Eliminar (solo admin) ──────────────────────────────────────

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
    fechaDocumento: row.fecha_documento,
    fechaIngreso:   row.fecha_sistema,
    fechaCierre:    null,
    observacion:    null,
    // Campos Oficina de Partes (null-safe: docs legados no tienen estos campos)
    tipoSoporte: row.medio === 'F' ? 'F' : 'D',
    reservado:   row.resuelto === 'S',
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
