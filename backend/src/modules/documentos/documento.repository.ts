import { getPool, sql } from '../../config/database';
import { FiltrosDocumentoDto } from './documento.schema';
import { logger } from '../../shared/utils/logger';

// ── Types ────────────────────────────────────────────────────

export interface DocumentoRow {
  id_documento: number;
  num_interno: number | null;
  num_oficial: number | null;
  materia: string | null;
  id_tipo_documento: number | null;
  desc_tipo_documento: string | null;
  id_estado_documento: number | null;
  desc_estado_documento: string | null;
  id_usuario: number | null;
  usuario: string | null;
  nombres: string | null;
  apellidos: string | null;
  fecha_documento: Date | null;
  fecha_sistema: Date | null;
  // Campos Oficina de Partes: 'F'=Físico, null/'D'=Digital (columna medio legacy)
  medio: string | null;
  // Reservado: 'S'=sí (columna resuelto legacy reutilizada)
  resuelto: string | null;
  total: number;
  // Correlativo de memorándum (poblado en findMany y findById; ausente en findByNumero)
  correlativo_memo?: string | null;
}

export interface TramiteRow {
  id_seguimiento: number;
  id_documento: number;
  id_estado_tramite: number | null;
  desc_estado_tramite: string | null;
  id_procedencia: number | null;
  id_destino: number | null;
  tipo_procedencia: string | null;
  tipo_destinatario: string | null;
  desc_procedencia: string | null;
  desc_destino: string | null;
  id_tipo_distribucion: number | null;
  desc_tipo_distribucion: string | null;
  id_tipo_compromiso: number | null;
  desc_tipo_compromiso: string | null;
  dias_compromiso: number | null;
  observaciones: string | null;
  fecha_sistema: Date | null;
  fecha_despacho: Date | null;
  fecha_recepcion: Date | null;
  usuario: string | null;
  nombres_usuario: string | null;
  usuario_recepcion: number | null;
}

// ── findMany ─────────────────────────────────────────────────

interface FiltroServicioRepo {
  idDependencia: number | null;
  verExternos:   boolean;
}

export async function findMany(filtros: FiltrosDocumentoDto, filtroServicio?: FiltroServicioRepo | null): Promise<DocumentoRow[]> {
  const pool = await getPool();
  const offset = (filtros.pagina - 1) * filtros.porPagina;
  const request = pool.request()
    .input('offset', sql.Int, offset)
    .input('porPagina', sql.Int, filtros.porPagina);

  let where = '1=1';
  if (filtros.q) {
    request.input('q', sql.NVarChar(200), `%${filtros.q}%`);
    where += ' AND (d.materia LIKE @q OR CAST(d.num_interno AS VARCHAR) LIKE @q OR CAST(d.num_oficial AS VARCHAR) LIKE @q)';
  }
  if (filtros.idTipo)    { request.input('idTipo',    sql.Int,  filtros.idTipo);                    where += ' AND d.id_tipo_documento = @idTipo'; }
  if (filtros.idEstado)  { request.input('idEstado',  sql.Int,  filtros.idEstado);                  where += ' AND d.id_estado_documento = @idEstado'; }
  if (filtros.fechaDesde){ request.input('fechaDesde',sql.Date, new Date(filtros.fechaDesde));       where += ' AND d.fecha_sistema >= @fechaDesde'; }
  if (filtros.fechaHasta){ request.input('fechaHasta',sql.Date, new Date(filtros.fechaHasta));       where += ' AND d.fecha_sistema <= @fechaHasta'; }

  // Filtro por servicio
  if (filtroServicio !== null && filtroServicio !== undefined) {
    if (filtroServicio.idDependencia) {
      // Usuario con servicio asignado → solo docs donde su dep participa
      request.input('idDepFiltro', sql.Int, filtroServicio.idDependencia);
      const externalClause = filtroServicio.verExternos ? " OR t_f.tipo_destinatario = 'E'" : '';
      where += ` AND EXISTS (
        SELECT 1 FROM tramite t_f WHERE t_f.id_documento = d.id_documento
        AND (
          (t_f.id_destino     = @idDepFiltro AND t_f.tipo_destinatario = 'D')
          OR (t_f.id_procedencia = @idDepFiltro AND t_f.tipo_procedencia  = 'D')
          ${externalClause}
        )
      )`;
    } else {
      // Usuario sin servicio asignado y sin acceso total → bloquear todo
      where += ' AND 1=0';
    }
  }

  const result = await request.query<DocumentoRow>(`
    SELECT
      d.id_documento, d.num_interno, d.num_oficial, d.materia,
      d.id_tipo_documento, td.desc_tipo_documento,
      d.id_estado_documento, ed.desc_estado_documento,
      d.id_usuario, u.usuario,
      f.nombres, f.apellidos,
      d.fecha_documento, d.fecha_sistema,
      d.medio, d.resuelto,
      mg.correlativo AS correlativo_memo,
      COUNT(*) OVER() AS total
    FROM documento d
    LEFT JOIN tipo_documento td   ON d.id_tipo_documento  = td.id_tipo_documento
    LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
    LEFT JOIN usuario u           ON d.id_usuario          = u.id_usuario
    LEFT JOIN funcionario f       ON u.id_funcionario      = f.id_funcionario
    LEFT JOIN memo_generado mg    ON mg.id_documento       = d.id_documento
    WHERE ${where}
    ORDER BY d.fecha_sistema DESC
    OFFSET @offset ROWS FETCH NEXT @porPagina ROWS ONLY
  `);
  return result.recordset;
}

// ── findByNumero — búsqueda exacta por num_interno o id_documento ──
// Usa comparación INT directa (no LIKE) — aprovecha índices existentes.
export async function findByNumero(numero: number): Promise<DocumentoRow[]> {
  const pool = await getPool();
  const result = await pool.request()
    .input('num', sql.Int, numero)
    .query<DocumentoRow>(`
      SELECT
        d.id_documento, d.num_interno, d.num_oficial, d.materia,
        d.id_tipo_documento, td.desc_tipo_documento,
        d.id_estado_documento, ed.desc_estado_documento,
        d.id_usuario, u.usuario,
        f.nombres, f.apellidos,
        d.fecha_documento, d.fecha_sistema,
        d.medio, d.resuelto,
        0 AS total
      FROM documento d
      LEFT JOIN tipo_documento td   ON d.id_tipo_documento  = td.id_tipo_documento
      LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
      LEFT JOIN usuario u           ON d.id_usuario          = u.id_usuario
      LEFT JOIN funcionario f       ON u.id_funcionario      = f.id_funcionario
      WHERE d.num_interno = @num OR d.id_documento = @num
      ORDER BY d.fecha_sistema DESC
    `);
  return result.recordset;
}

// ── findById ─────────────────────────────────────────────────

export async function findById(idDocumento: number): Promise<DocumentoRow | null> {
  const pool = await getPool();
  const result = await pool.request().input('id', sql.Int, idDocumento).query<DocumentoRow>(`
    SELECT
      d.id_documento, d.num_interno, d.num_oficial, d.materia,
      d.id_tipo_documento, td.desc_tipo_documento,
      d.id_estado_documento, ed.desc_estado_documento,
      d.id_usuario, u.usuario,
      f.nombres, f.apellidos,
      d.fecha_documento, d.fecha_sistema,
      d.medio, d.resuelto,
      mg.correlativo AS correlativo_memo,
      0 AS total
    FROM documento d
    LEFT JOIN tipo_documento td   ON d.id_tipo_documento  = td.id_tipo_documento
    LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
    LEFT JOIN usuario u           ON d.id_usuario          = u.id_usuario
    LEFT JOIN funcionario f       ON u.id_funcionario      = f.id_funcionario
    LEFT JOIN memo_generado mg    ON mg.id_documento       = d.id_documento
    WHERE d.id_documento = @id
  `);
  return result.recordset[0] ?? null;
}

// ── findTrazabilidad — historial completo con tramites ───────

export async function findTrazabilidad(idDocumento: number): Promise<TramiteRow[]> {
  const pool = await getPool();
  const result = await pool.request().input('id', sql.Int, idDocumento).query<TramiteRow>(`
    SELECT
      t.id_seguimiento, t.id_documento,
      t.id_estado_tramite, et.desc_estado_tramite,
      t.id_procedencia, t.id_destino,
      t.tipo_procedencia, t.tipo_destinatario,
      CASE t.tipo_procedencia
        WHEN 'D' THEN (SELECT LTRIM(RTRIM(desc_dependencia)) FROM dependencia WHERE id_dependencia = t.id_procedencia)
        WHEN 'E' THEN (SELECT desc_dependencia_externa FROM dependencia_externa WHERE id_dependencia_externa = t.id_procedencia)
        ELSE 'Interno'
      END AS desc_procedencia,
      CASE t.tipo_destinatario
        WHEN 'D' THEN (SELECT LTRIM(RTRIM(desc_dependencia)) FROM dependencia WHERE id_dependencia = t.id_destino)
        WHEN 'E' THEN (SELECT desc_dependencia_externa FROM dependencia_externa WHERE id_dependencia_externa = t.id_destino)
        ELSE 'Interno'
      END AS desc_destino,
      t.id_tipo_distribucion, td.desc_tipo_distribucion,
      t.id_tipo_compromiso, tc.desc_tipo_compromiso,
      t.dias_compromiso, t.observaciones,
      t.fecha_sistema, t.fecha_despacho, t.fecha_recepcion,
      u.usuario, f.nombres AS nombres_usuario,
      t.usuario_recepcion
    FROM tramite t
    LEFT JOIN estado_tramite et       ON t.id_estado_tramite   = et.id_estado_tramite
    LEFT JOIN tipo_distribucion td    ON t.id_tipo_distribucion = td.id_tipo_distribucion
    LEFT JOIN tipo_compromiso tc      ON t.id_tipo_compromiso   = tc.id_tipo_compromiso
    LEFT JOIN usuario u               ON t.id_usuario           = u.id_usuario
    LEFT JOIN funcionario f           ON u.id_funcionario       = f.id_funcionario
    WHERE t.id_documento = @id
    ORDER BY t.fecha_sistema ASC
  `);
  return result.recordset;
}

// ── insert documento + tramite inicial ───────────────────────

export async function insert(data: {
  idTipoDocumento: number;
  materia: string;
  idEstadoDocumento: number;
  idUsuario: number;
  fechaDocumento?: Date;
  medio?: string;
  original?: string;
  resuelto?: string;
  // Trámite
  tipoProcedencia: string;
  idProcedencia: number;
  tipoDestinatario: string;
  idDestino: number;
  idTipoDistribucion: number;
  idTipoCompromiso: number;
  idEstadoCompromiso: number;
  diasCompromiso: number;
  observaciones?: string;
}): Promise<{ idDocumento: number; idSeguimiento: number }> {
  const pool    = await getPool();
  const fechaDoc = data.fechaDocumento ?? new Date();

  // Batch atómico: bloquea la tabla con UPDLOCK+HOLDLOCK para que dos requests
  // concurrentes no lean el mismo MAX y generen num_interno duplicado.
  // Los tres pasos (SELECT MAX, INSERT documento, INSERT tramite) se ejecutan
  // en un único sp_executesql, eliminando la race condition.
  const result = await pool.request()
    .input('idTipo',    sql.Int,          data.idTipoDocumento)
    .input('idEstado',  sql.Int,          data.idEstadoDocumento)
    .input('idUsr',     sql.Int,          data.idUsuario)
    .input('materia',   sql.VarChar(250), data.materia.substring(0, 250))
    .input('fechaDoc',  sql.DateTime,     fechaDoc)
    .input('medio',     sql.VarChar(1),   data.medio ?? null)
    .input('original',  sql.VarChar(1),   data.original ?? 'S')
    .input('resuelto',  sql.Char(1),      data.resuelto ?? null)
    .input('idProc',    sql.Int,          data.idProcedencia)
    .input('idDest',    sql.Int,          data.idDestino)
    .input('tipProc',   sql.Char(1),      data.tipoProcedencia)
    .input('tipDest',   sql.Char(1),      data.tipoDestinatario)
    .input('idTipDis',  sql.Int,          data.idTipoDistribucion)
    .input('idTipCom',  sql.Int,          data.idTipoCompromiso)
    .input('idEstCom',  sql.Int,          data.idEstadoCompromiso)
    .input('dias',      sql.Int,          data.diasCompromiso)
    .input('obs',       sql.VarChar(250), (data.observaciones ?? '').substring(0, 250))
    .query<{ id_documento: number; id_seguimiento: number }>(`
      DECLARE @nextInt INT, @nextOf  INT;
      DECLARE @idDoc   INT, @idSeg   INT;
      DECLARE @docOut  TABLE (id_documento   INT);
      DECLARE @tramOut TABLE (id_seguimiento INT);

      SELECT @nextInt = ISNULL(MAX(num_interno), 0) + 1,
             @nextOf  = ISNULL(MAX(num_oficial), 0) + 1
      FROM documento WITH (UPDLOCK, HOLDLOCK);

      INSERT INTO documento
        (id_tipo_documento, id_estado_documento, id_usuario,
         num_interno, num_oficial, num_externo, original, medio, resuelto,
         materia, fecha_documento, fecha_sistema, fecha_update)
      OUTPUT INSERTED.id_documento INTO @docOut
      VALUES
        (@idTipo, @idEstado, @idUsr,
         @nextInt, @nextOf, 0, @original, @medio, @resuelto,
         @materia, @fechaDoc, GETDATE(), GETDATE());

      SET @idDoc = (SELECT id_documento FROM @docOut);

      INSERT INTO tramite
        (id_documento, id_usuario, id_procedencia, id_destino,
         tipo_procedencia, tipo_destinatario,
         id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
         id_estado_tramite, dias_compromiso, observaciones,
         fecha_sistema, fecha_update)
      OUTPUT INSERTED.id_seguimiento INTO @tramOut
      VALUES
        (@idDoc, @idUsr, @idProc, @idDest,
         @tipProc, @tipDest,
         @idTipDis, @idTipCom, @idEstCom,
         1, @dias, @obs,
         GETDATE(), GETDATE());

      SET @idSeg = (SELECT id_seguimiento FROM @tramOut);

      SELECT @idDoc AS id_documento, @idSeg AS id_seguimiento;
    `);

  const row = result.recordset[0];
  return { idDocumento: row.id_documento, idSeguimiento: row.id_seguimiento };
}

// ── updateEstado ─────────────────────────────────────────────

export async function updateEstado(idDocumento: number, idEstado: number): Promise<void> {
  const pool = await getPool();
  await pool.request()
    .input('idEstado', sql.Int, idEstado)
    .input('idDoc',    sql.Int, idDocumento)
    .query(`UPDATE documento SET id_estado_documento = @idEstado, fecha_update = GETDATE() WHERE id_documento = @idDoc`);
}

// ── updateTramite estado ──────────────────────────────────────

export async function updateTramiteEstado(idSeguimiento: number, idEstadoTramite: number, extra?: {
  fechaDespacho?: Date;
  fechaRecepcion?: Date;
  usuarioRecepcion?: number;
}): Promise<void> {
  const pool = await getPool();
  let setClause = 'id_estado_tramite = @est, fecha_update = GETDATE()';
  const req = pool.request()
    .input('est', sql.Int, idEstadoTramite)
    .input('id',  sql.Int, idSeguimiento);

  if (extra?.fechaDespacho) { req.input('fd', sql.DateTime, extra.fechaDespacho); setClause += ', fecha_despacho = @fd'; }
  if (extra?.fechaRecepcion) { req.input('fr', sql.DateTime, extra.fechaRecepcion); setClause += ', fecha_recepcion = @fr'; }
  if (extra?.usuarioRecepcion) { req.input('ur', sql.Int, extra.usuarioRecepcion); setClause += ', usuario_recepcion = @ur'; }

  await req.query(`UPDATE tramite SET ${setClause} WHERE id_seguimiento = @id`);
}

// ── getLastTramite ────────────────────────────────────────────

export async function getLastTramite(idDocumento: number): Promise<{
  id_seguimiento: number;
  id_destino: number;
  tipo_destinatario: string;
  id_estado_tramite: number | null;
} | null> {
  const pool = await getPool();
  const r = await pool.request().input('id', sql.Int, idDocumento).query<{
    id_seguimiento: number;
    id_destino: number;
    tipo_destinatario: string;
    id_estado_tramite: number | null;
  }>(`
    SELECT TOP 1 id_seguimiento, id_destino, tipo_destinatario, id_estado_tramite
    FROM tramite WHERE id_documento = @id ORDER BY fecha_sistema DESC
  `);
  return r.recordset[0] ?? null;
}

// ── documento_destino — CRUD ──────────────────────────────────

export interface DocumentoDestinoRow {
  id:                   number;
  id_documento:         number;
  id_destino:           number;
  tipo_destinatario:    string;
  id_estado_tramite:    number;
  id_tipo_compromiso:   number;
  dias_compromiso:      number;
  id_usuario_creacion:  number;
  id_usuario_recepcion: number | null;
  id_usuario_cierre:    number | null;
  observaciones:        string | null;
  fecha_creacion:       Date;
  fecha_recepcion:      Date | null;
  fecha_cierre:         Date | null;
  activo:               boolean;
  desc_dependencia:     string | null;
  desc_estado_tramite:  string | null;
  desc_tipo_compromiso: string | null;
}

export async function insertDocumentoDestino(data: {
  idDocumento:       number;
  idDestino:         number;
  tipoDestinatario:  string;
  idTipoCompromiso:  number;
  diasCompromiso:    number;
  idUsuarioCreacion: number;
}): Promise<number> {
  const pool = await getPool();
  const r = await pool.request()
    .input('idDoc',    sql.Int,    data.idDocumento)
    .input('idDest',   sql.Int,    data.idDestino)
    .input('tipDest',  sql.Char(1), data.tipoDestinatario)
    .input('idTipCom', sql.Int,    data.idTipoCompromiso)
    .input('dias',     sql.Int,    data.diasCompromiso)
    .input('idUsr',    sql.Int,    data.idUsuarioCreacion)
    .query<{ id: number }>(`
      INSERT INTO documento_destino
        (id_documento, id_destino, tipo_destinatario, id_tipo_compromiso, dias_compromiso, id_usuario_creacion)
      OUTPUT INSERTED.id
      VALUES (@idDoc, @idDest, @tipDest, @idTipCom, @dias, @idUsr)
    `);
  return r.recordset[0].id;
}

export async function findDestinosByDocumento(idDocumento: number): Promise<DocumentoDestinoRow[]> {
  const pool = await getPool();
  const r = await pool.request().input('idDoc', sql.Int, idDocumento).query<DocumentoDestinoRow>(`
    SELECT
      dd.*,
      LTRIM(RTRIM(dep.desc_dependencia)) AS desc_dependencia,
      et.desc_estado_tramite,
      tc.desc_tipo_compromiso
    FROM documento_destino dd
    LEFT JOIN dependencia dep    ON dd.id_destino           = dep.id_dependencia
    LEFT JOIN estado_tramite et  ON dd.id_estado_tramite    = et.id_estado_tramite
    LEFT JOIN tipo_compromiso tc ON dd.id_tipo_compromiso   = tc.id_tipo_compromiso
    WHERE dd.id_documento = @idDoc AND dd.activo = 1
    ORDER BY dd.id_tipo_compromiso DESC, dd.fecha_creacion
  `);
  return r.recordset;
}

export async function updateDocumentoDestinoEstado(
  idDocumentoDestino: number,
  idEstadoTramite: number,
  extra?: {
    idUsuarioRecepcion?: number;
    idUsuarioCierre?: number;
    fechaRecepcion?: Date;
    fechaCierre?: Date;
    observaciones?: string;
  },
): Promise<void> {
  const pool = await getPool();
  let set    = 'id_estado_tramite = @est, fecha_creacion = fecha_creacion';
  const req  = pool.request().input('est', sql.Int, idEstadoTramite).input('id', sql.Int, idDocumentoDestino);
  if (extra?.idUsuarioRecepcion) { req.input('urec', sql.Int, extra.idUsuarioRecepcion); set += ', id_usuario_recepcion = @urec'; }
  if (extra?.idUsuarioCierre)    { req.input('ucl',  sql.Int, extra.idUsuarioCierre);    set += ', id_usuario_cierre = @ucl'; }
  if (extra?.fechaRecepcion)     { req.input('frec', sql.DateTime, extra.fechaRecepcion); set += ', fecha_recepcion = @frec'; }
  if (extra?.fechaCierre)        { req.input('fcl',  sql.DateTime, extra.fechaCierre);    set += ', fecha_cierre = @fcl'; }
  if (extra?.observaciones)      { req.input('obs',  sql.VarChar(500), extra.observaciones.substring(0, 500)); set += ', observaciones = @obs'; }
  await req.query(`UPDATE documento_destino SET ${set} WHERE id = @id`);
}

export async function allDestinosCerrados(idDocumento: number): Promise<boolean> {
  const pool = await getPool();
  const r = await pool.request().input('idDoc', sql.Int, idDocumento).query<{ pendientes: number }>(`
    SELECT COUNT(*) AS pendientes
    FROM documento_destino
    WHERE id_documento = @idDoc AND activo = 1 AND id_estado_tramite NOT IN (5, 6)
  `);
  return (r.recordset[0]?.pendientes ?? 1) === 0;
}

// ── Transiciones atómicas de estado ──────────────────────────
// Cada función consolida UPDATE doc_destino + INSERT tramite + UPDATE documento
// en un único batch T-SQL con BEGIN TRAN/COMMIT, eliminando ventanas de
// inconsistencia entre las llamadas separadas previas.

export async function recepcionarDestinoAtomic(params: {
  idDocumentoDestino: number;
  idDocumento:        number;
  idDestino:          number;
  idUsuario:          number;
  observaciones:      string | undefined;
}): Promise<void> {
  const pool = await getPool();
  await pool.request()
    .input('idDocDest', sql.Int,          params.idDocumentoDestino)
    .input('idDoc',     sql.Int,          params.idDocumento)
    .input('idProc',    sql.Int,          params.idDestino)
    .input('idUsr',     sql.Int,          params.idUsuario)
    .input('obs',       sql.VarChar(500), (params.observaciones ?? '').substring(0, 500))
    .query(`
      BEGIN TRAN;
        UPDATE documento_destino
          SET id_estado_tramite    = 3,
              fecha_recepcion      = GETDATE(),
              id_usuario_recepcion = @idUsr,
              observaciones        = @obs
        WHERE id = @idDocDest;

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
           GETDATE(), GETDATE(), GETDATE(), @idUsr);

        UPDATE documento
          SET id_estado_documento = 3, fecha_update = GETDATE()
        WHERE id_documento = @idDoc;
      COMMIT;
    `);
}

export async function terminarDestinoAtomic(params: {
  idDocumentoDestino: number;
  idDocumento:        number;
  idDestino:          number;
  idUsuario:          number;
  observaciones:      string | undefined;
}): Promise<void> {
  const pool = await getPool();
  await pool.request()
    .input('idDocDest', sql.Int,          params.idDocumentoDestino)
    .input('idDoc',     sql.Int,          params.idDocumento)
    .input('idProc',    sql.Int,          params.idDestino)
    .input('idUsr',     sql.Int,          params.idUsuario)
    .input('obs',       sql.VarChar(500), (params.observaciones ?? '').substring(0, 500))
    .query(`
      BEGIN TRAN;
        UPDATE documento_destino
          SET id_estado_tramite = 5,
              fecha_cierre      = GETDATE(),
              id_usuario_cierre = @idUsr,
              observaciones     = @obs
        WHERE id = @idDocDest;

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
           GETDATE(), GETDATE());

        IF NOT EXISTS (
          SELECT 1 FROM documento_destino
          WHERE id_documento = @idDoc AND activo = 1 AND id_estado_tramite NOT IN (5, 6)
        )
          UPDATE documento SET id_estado_documento = 4, fecha_update = GETDATE()
          WHERE id_documento = @idDoc;
      COMMIT;
    `);
}

export async function recepcionarDocumentoAtomic(params: {
  idDocumento:   number;
  idProc:        number;
  tipProc:       string;
  idUsuario:     number;
  observaciones: string | undefined;
}): Promise<void> {
  const pool = await getPool();
  await pool.request()
    .input('idDoc',  sql.Int,          params.idDocumento)
    .input('idUsr',  sql.Int,          params.idUsuario)
    .input('idProc', sql.Int,          params.idProc)
    .input('tipProc',sql.Char(1),      params.tipProc)
    .input('obs',    sql.VarChar(250), (params.observaciones ?? '').substring(0, 250))
    .query(`
      BEGIN TRAN;
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
           GETDATE(), GETDATE(), GETDATE(), @idUsr);

        UPDATE documento SET id_estado_documento = 3, fecha_update = GETDATE()
        WHERE id_documento = @idDoc;
      COMMIT;
    `);
}

export async function terminarDocumentoAtomic(params: {
  idDocumento:   number;
  idProc:        number;
  tipProc:       string;
  idUsuario:     number;
  observaciones: string | undefined;
}): Promise<void> {
  const pool = await getPool();
  await pool.request()
    .input('idDoc',  sql.Int,          params.idDocumento)
    .input('idUsr',  sql.Int,          params.idUsuario)
    .input('idProc', sql.Int,          params.idProc)
    .input('tipProc',sql.Char(1),      params.tipProc)
    .input('obs',    sql.VarChar(250), (params.observaciones ?? '').substring(0, 250))
    .query(`
      BEGIN TRAN;
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
           GETDATE(), GETDATE());

        UPDATE documento SET id_estado_documento = 4, fecha_update = GETDATE()
        WHERE id_documento = @idDoc;
      COMMIT;
    `);
}

// ── softDelete ────────────────────────────────────────────────

export async function softDelete(idDocumento: number, _idUsuario: number): Promise<void> {
  const pool = await getPool();

  // Intentar backup en respaldo_documento (schema legacy: documento + ultimo tramite)
  try {
    await pool.request().input('id', sql.Int, idDocumento).query(`
      INSERT INTO respaldo_documento
        (id_documento, id_tipo_documento, id_usuario,
         num_interno, num_oficial, num_externo,
         fecha_documento, fecha_sistema1, materia,
         id_seguimiento, id_procedencia, id_destino,
         tipo_procedencia, tipo_destino,
         rut_procedencia, rut_destino, fecha_sistema2,
         observaciones, tipo_eliminacion, fecha_eliminacion)
      SELECT
        d.id_documento, d.id_tipo_documento, d.id_usuario,
        d.num_interno, d.num_oficial, d.num_externo,
        d.fecha_documento, d.fecha_sistema, d.materia,
        ISNULL(t.id_seguimiento, 0),
        ISNULL(t.id_procedencia, 0), ISNULL(t.id_destino, 0),
        ISNULL(t.tipo_procedencia, 'D'), ISNULL(t.tipo_destinatario, 'D'),
        0, 0,
        ISNULL(t.fecha_sistema, GETDATE()),
        ISNULL(t.observaciones, ''),
        'D', GETDATE()
      FROM documento d
      LEFT JOIN (
        SELECT TOP 1 * FROM tramite WHERE id_documento = @id ORDER BY fecha_sistema DESC
      ) t ON t.id_documento = d.id_documento
      WHERE d.id_documento = @id
    `);
  } catch { /* si falla el backup, continuar igual */ }

  // Eliminar en orden para respetar FK, todo en una sola transacción: si
  // cualquier paso falla (p. ej. una FK nueva no contemplada aquí), nada
  // queda a medio borrar — antes cada DELETE era un request independiente
  // y un fallo intermedio dejaba filas huérfanas.
  // 1. firma_gob_historial.id_documento y memorandum_firma_simple.id_documento
  //    son nullable → se desvinculan (no se borran) para conservar la
  //    auditoría de intentos de firma electrónica.
  // 2. memo_generado.id_documento es NOT NULL → debe borrarse antes que
  //    documento. Además memo_generado.id_archivo_digital referencia
  //    archivo_digital, así que debe borrarse ANTES que archivo_digital.
  // 3. documento_destino.id_documento es NOT NULL → antes que documento.
  // 4. archivo_digital y tramite no tienen FK hacia documento, pero se
  //    borran antes por prolijidad (no quedan huérfanos).
  await pool.request().input('id', sql.Int, idDocumento).query(`
    BEGIN TRANSACTION;

    UPDATE firma_gob_historial SET id_documento = NULL WHERE id_documento = @id;
    UPDATE memorandum_firma_simple SET id_documento = NULL WHERE id_documento = @id;
    DELETE FROM memo_generado WHERE id_documento = @id;
    DELETE FROM archivo_digital WHERE id_documento = @id;
    DELETE FROM tramite WHERE id_documento = @id;
    DELETE FROM documento_destino WHERE id_documento = @id;
    DELETE FROM documento WHERE id_documento = @id;

    COMMIT TRANSACTION;
  `);

  logger.info(`[documentos] Documento ${idDocumento} eliminado por usuario ${_idUsuario} (memo_generado, archivo_digital, tramite y documento_destino asociados también eliminados; firma_gob_historial y memorandum_firma_simple desvinculados) — transaccional.`);
}
