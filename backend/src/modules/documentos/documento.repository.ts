import { getPool, sql } from '../../config/database';
import { FiltrosDocumentoDto } from './documento.schema';

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
  id_expediente: number | null;
  total: number;
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
      d.fecha_documento, d.fecha_sistema, d.id_expediente,
      COUNT(*) OVER() AS total
    FROM documento d
    LEFT JOIN tipo_documento td   ON d.id_tipo_documento  = td.id_tipo_documento
    LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
    LEFT JOIN usuario u           ON d.id_usuario          = u.id_usuario
    LEFT JOIN funcionario f       ON u.id_funcionario      = f.id_funcionario
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
        d.fecha_documento, d.fecha_sistema, d.id_expediente,
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
      d.fecha_documento, d.fecha_sistema, d.id_expediente,
      0 AS total
    FROM documento d
    LEFT JOIN tipo_documento td   ON d.id_tipo_documento  = td.id_tipo_documento
    LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
    LEFT JOIN usuario u           ON d.id_usuario          = u.id_usuario
    LEFT JOIN funcionario f       ON u.id_funcionario      = f.id_funcionario
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
  idExpediente?: number;
  medio?: string;
  original?: string;
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
  const pool = await getPool();

  // Correlativo siguiente
  const maxRes = await pool.request().query<{ maxInterno: number; maxOficial: number }>(`
    SELECT ISNULL(MAX(num_interno), 0) AS maxInterno, ISNULL(MAX(num_oficial), 0) AS maxOficial
    FROM documento
  `);
  const nextInterno = (maxRes.recordset[0]?.maxInterno ?? 0) + 1;
  const nextOficial = (maxRes.recordset[0]?.maxOficial ?? 0) + 1;
  const fechaDoc    = data.fechaDocumento ?? new Date();

  // INSERT documento
  const docRes = await pool.request()
    .input('idTipo',    sql.Int,        data.idTipoDocumento)
    .input('idEstado',  sql.Int,        data.idEstadoDocumento)
    .input('idUsr',     sql.Int,        data.idUsuario)
    .input('numInt',    sql.Int,        nextInterno)
    .input('numOf',     sql.Int,        nextOficial)
    .input('materia',   sql.VarChar(250), data.materia.substring(0, 250))
    .input('fechaDoc',  sql.DateTime,   fechaDoc)
    .input('idExp',     sql.Int,        data.idExpediente ?? null)
    .input('medio',     sql.VarChar(1), data.medio ?? null)
    .input('original',  sql.VarChar(1), data.original ?? 'S')
    .query<{ id_documento: number }>(`
      INSERT INTO documento
        (id_tipo_documento, id_estado_documento, id_usuario,
         num_interno, num_oficial, num_externo, original, medio,
         materia, fecha_documento, fecha_sistema, fecha_update, id_expediente)
      OUTPUT INSERTED.id_documento
      VALUES
        (@idTipo, @idEstado, @idUsr,
         @numInt, @numOf, 0, @original, @medio,
         @materia, @fechaDoc, GETDATE(), GETDATE(), @idExp)
    `);
  const idDocumento = docRes.recordset[0].id_documento;

  // INSERT tramite inicial (estado 1 = Generado)
  const tramRes = await pool.request()
    .input('idDoc',    sql.Int,        idDocumento)
    .input('idUsr',    sql.Int,        data.idUsuario)
    .input('idProc',   sql.Int,        data.idProcedencia)
    .input('idDest',   sql.Int,        data.idDestino)
    .input('tipProc',  sql.Char(1),    data.tipoProcedencia)
    .input('tipDest',  sql.Char(1),    data.tipoDestinatario)
    .input('idTipDis', sql.Int,        data.idTipoDistribucion)
    .input('idTipCom', sql.Int,        data.idTipoCompromiso)
    .input('idEstCom', sql.Int,        data.idEstadoCompromiso)
    .input('dias',     sql.Int,        data.diasCompromiso)
    .input('obs',      sql.VarChar(250), (data.observaciones ?? '').substring(0, 250))
    .query<{ id_seguimiento: number }>(`
      INSERT INTO tramite
        (id_documento, id_usuario, id_procedencia, id_destino,
         tipo_procedencia, tipo_destinatario,
         id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
         id_estado_tramite, dias_compromiso, observaciones,
         fecha_sistema, fecha_update)
      OUTPUT INSERTED.id_seguimiento
      VALUES
        (@idDoc, @idUsr, @idProc, @idDest,
         @tipProc, @tipDest,
         @idTipDis, @idTipCom, @idEstCom,
         1, @dias, @obs,
         GETDATE(), GETDATE())
    `);
  const idSeguimiento = tramRes.recordset[0].id_seguimiento;

  return { idDocumento, idSeguimiento };
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

  // Eliminar en orden para respetar posibles FK
  await pool.request().input('id', sql.Int, idDocumento)
    .query('DELETE FROM archivo_digital WHERE id_documento = @id');
  await pool.request().input('id', sql.Int, idDocumento)
    .query('DELETE FROM tramite WHERE id_documento = @id');
  await pool.request().input('id', sql.Int, idDocumento)
    .query('DELETE FROM documento WHERE id_documento = @id');
}
