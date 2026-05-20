import { getPool, sql } from '../../config/database';
import { FiltrosDocumentoDto } from './documento.schema';

export interface DocumentoRow {
  id_documento: number;
  num_interno: string | null;
  num_oficial: string | null;
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
  total: number;
}

export async function findMany(filtros: FiltrosDocumentoDto): Promise<DocumentoRow[]> {
  const pool = await getPool();
  const offset = (filtros.pagina - 1) * filtros.porPagina;

  const request = pool.request();
  request.input('offset', sql.Int, offset);
  request.input('porPagina', sql.Int, filtros.porPagina);

  let where = '1=1';

  if (filtros.q) {
    request.input('q', sql.NVarChar(200), `%${filtros.q}%`);
    where += ' AND (d.materia LIKE @q OR d.num_interno LIKE @q OR d.num_oficial LIKE @q)';
  }
  if (filtros.idTipo) {
    request.input('idTipo', sql.Int, filtros.idTipo);
    where += ' AND d.id_tipo_documento = @idTipo';
  }
  if (filtros.idEstado) {
    request.input('idEstado', sql.Int, filtros.idEstado);
    where += ' AND d.id_estado_documento = @idEstado';
  }
  if (filtros.fechaDesde) {
    request.input('fechaDesde', sql.Date, new Date(filtros.fechaDesde));
    where += ' AND d.fecha_sistema >= @fechaDesde';
  }
  if (filtros.fechaHasta) {
    request.input('fechaHasta', sql.Date, new Date(filtros.fechaHasta));
    where += ' AND d.fecha_sistema <= @fechaHasta';
  }

  const result = await request.query<DocumentoRow>(`
    SELECT
      d.id_documento, d.num_interno, d.num_oficial, d.materia,
      d.id_tipo_documento, td.desc_tipo_documento,
      d.id_estado_documento, ed.desc_estado_documento,
      d.id_usuario, u.usuario,
      f.nombres, f.apellidos,
      d.fecha_documento, d.fecha_sistema,
      COUNT(*) OVER() AS total
    FROM documento d
    LEFT JOIN tipo_documento td   ON d.id_tipo_documento = td.id_tipo_documento
    LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
    LEFT JOIN usuario u            ON d.id_usuario = u.id_usuario
    LEFT JOIN funcionario f        ON u.id_funcionario = f.id_funcionario
    WHERE ${where}
    ORDER BY d.fecha_sistema DESC
    OFFSET @offset ROWS FETCH NEXT @porPagina ROWS ONLY
  `);

  return result.recordset;
}

export async function findById(idDocumento: number): Promise<DocumentoRow | null> {
  const pool = await getPool();
  const result = await pool
    .request()
    .input('id', sql.Int, idDocumento)
    .query<DocumentoRow>(`
      SELECT
        d.id_documento, d.num_interno, d.num_oficial, d.materia,
        d.id_tipo_documento, td.desc_tipo_documento,
        d.id_estado_documento, ed.desc_estado_documento,
        d.id_usuario, u.usuario,
        f.nombres, f.apellidos,
        d.fecha_documento, d.fecha_sistema,
        0 AS total
      FROM documento d
      LEFT JOIN tipo_documento td   ON d.id_tipo_documento = td.id_tipo_documento
      LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
      LEFT JOIN usuario u            ON d.id_usuario = u.id_usuario
      LEFT JOIN funcionario f        ON u.id_funcionario = f.id_funcionario
      WHERE d.id_documento = @id
    `);
  return result.recordset[0] ?? null;
}

export async function findHistorial(idDocumento: number) {
  const pool = await getPool();
  // SISDOC usa 'tramite' como historial de derivaciones
  const result = await pool
    .request()
    .input('id', sql.Int, idDocumento)
    .query<{
      id_seguimiento: number;
      id_estado_tramite: number | null;
      observaciones: string | null;
      fecha_sistema: Date | null;
      usuario: string | null;
      nombres: string | null;
    }>(`
      SELECT t.id_seguimiento, t.id_estado_tramite, t.observaciones, t.fecha_sistema,
             u.usuario, f.nombres
      FROM tramite t
      LEFT JOIN usuario u ON t.id_usuario = u.id_usuario
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      WHERE t.id_documento = @id
      ORDER BY t.fecha_sistema DESC
    `);
  return result.recordset;
}

export async function insert(data: {
  idTipoDocumento?: number;
  materia: string;
  idEstadoDocumento: number;
  idUsuario: number;
  fechaDocumento?: Date;
  observaciones?: string;
}): Promise<number> {
  const pool = await getPool();

  // num_interno y num_oficial son INT NOT NULL — generamos el siguiente correlativo
  const maxResult = await pool.request().query<{ maxInterno: number; maxOficial: number }>(`
    SELECT ISNULL(MAX(num_interno), 0) AS maxInterno,
           ISNULL(MAX(num_oficial), 0) AS maxOficial
    FROM documento
  `);
  const nextInterno = (maxResult.recordset[0]?.maxInterno ?? 0) + 1;
  const nextOficial = (maxResult.recordset[0]?.maxOficial ?? 0) + 1;

  const fechaDoc = data.fechaDocumento ?? new Date();

  const result = await pool
    .request()
    .input('idTipoDocumento', sql.Int, data.idTipoDocumento ?? 1)
    .input('idEstadoDocumento', sql.Int, data.idEstadoDocumento)
    .input('idUsuario', sql.Int, data.idUsuario)
    .input('numInterno', sql.Int, nextInterno)
    .input('numOficial', sql.Int, nextOficial)
    .input('materia', sql.VarChar(250), data.materia.substring(0, 250))
    .input('fechaDocumento', sql.DateTime, fechaDoc)
    .query<{ id_documento: number }>(`
      INSERT INTO documento (
        id_tipo_documento, id_estado_documento, id_usuario,
        num_interno, num_oficial, num_externo,
        original, materia,
        fecha_documento, fecha_sistema, fecha_update
      )
      OUTPUT INSERTED.id_documento
      VALUES (
        @idTipoDocumento, @idEstadoDocumento, @idUsuario,
        @numInterno, @numOficial, 0,
        'S', @materia,
        @fechaDocumento, GETDATE(), GETDATE()
      )
    `);
  return result.recordset[0].id_documento;
}

export async function updateEstado(idDocumento: number, idEstado: number): Promise<void> {
  const pool = await getPool();
  await pool
    .request()
    .input('idEstado', sql.Int, idEstado)
    .input('idDocumento', sql.Int, idDocumento)
    .query(`UPDATE documento SET id_estado_documento = @idEstado, fecha_update = GETDATE() WHERE id_documento = @idDocumento`);
}
