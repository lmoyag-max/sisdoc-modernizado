import { getPool, sql } from '../../config/database';
import { FiltrosDocumentoDto } from './documento.schema';

export interface DocumentoRow {
  id_documento: number;
  num_documento: string | null;
  asunto: string | null;
  id_tipo_documento: number | null;
  desc_tipo_documento: string | null;
  id_estado_documento: number | null;
  desc_estado_documento: string | null;
  id_prioridad: number | null;
  desc_prioridad: string | null;
  color_prioridad: string | null;
  id_procedencia: number | null;
  desc_procedencia: string | null;
  id_destino: number | null;
  desc_destino: string | null;
  id_usuario: number | null;
  usuario: string | null;
  nombres_fun: string | null;
  ap_pat_fun: string | null;
  fecha_documento: Date | null;
  fecha_ingreso: Date | null;
  fecha_cierre: Date | null;
  observacion: string | null;
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
    where += ' AND (d.asunto LIKE @q OR d.num_documento LIKE @q)';
  }
  if (filtros.idTipo) {
    request.input('idTipo', sql.Int, filtros.idTipo);
    where += ' AND d.id_tipo_documento = @idTipo';
  }
  if (filtros.idEstado) {
    request.input('idEstado', sql.Int, filtros.idEstado);
    where += ' AND d.id_estado_documento = @idEstado';
  }
  if (filtros.idDependencia) {
    request.input('idDependencia', sql.Int, filtros.idDependencia);
    where += ' AND d.id_destino = @idDependencia';
  }
  if (filtros.idPrioridad) {
    request.input('idPrioridad', sql.Int, filtros.idPrioridad);
    where += ' AND d.id_prioridad = @idPrioridad';
  }
  if (filtros.fechaDesde) {
    request.input('fechaDesde', sql.Date, new Date(filtros.fechaDesde));
    where += ' AND d.fecha_ingreso >= @fechaDesde';
  }
  if (filtros.fechaHasta) {
    request.input('fechaHasta', sql.Date, new Date(filtros.fechaHasta));
    where += ' AND d.fecha_ingreso <= @fechaHasta';
  }

  const result = await request.query<DocumentoRow>(`
    SELECT
      d.id_documento, d.num_documento, d.asunto,
      d.id_tipo_documento, td.desc_tipo_documento,
      d.id_estado_documento, ed.desc_estado_documento,
      d.id_prioridad, p.desc_prioridad, p.color AS color_prioridad,
      d.id_procedencia, dep_orig.desc_dependencia AS desc_procedencia,
      d.id_destino, dep_dest.desc_dependencia AS desc_destino,
      d.id_usuario, u.usuario,
      f.nombres_fun, f.ap_pat_fun,
      d.fecha_documento, d.fecha_ingreso, d.fecha_cierre, d.observacion,
      COUNT(*) OVER() AS total
    FROM documento d
    LEFT JOIN tipo_documento td   ON d.id_tipo_documento = td.id_tipo_documento
    LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
    LEFT JOIN prioridad p          ON d.id_prioridad = p.id_prioridad
    LEFT JOIN dependencia dep_orig ON d.id_procedencia = dep_orig.id_dependencia
    LEFT JOIN dependencia dep_dest ON d.id_destino = dep_dest.id_dependencia
    LEFT JOIN usuario u            ON d.id_usuario = u.id_usuario
    LEFT JOIN funcionario f        ON u.id_funcionario = f.id_funcionario
    WHERE ${where}
    ORDER BY d.fecha_ingreso DESC
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
        d.id_documento, d.num_documento, d.asunto,
        d.id_tipo_documento, td.desc_tipo_documento,
        d.id_estado_documento, ed.desc_estado_documento,
        d.id_prioridad, p.desc_prioridad, p.color AS color_prioridad,
        d.id_procedencia, dep_orig.desc_dependencia AS desc_procedencia,
        d.id_destino, dep_dest.desc_dependencia AS desc_destino,
        d.id_usuario, u.usuario,
        f.nombres_fun, f.ap_pat_fun,
        d.fecha_documento, d.fecha_ingreso, d.fecha_cierre, d.observacion,
        0 AS total
      FROM documento d
      LEFT JOIN tipo_documento td   ON d.id_tipo_documento = td.id_tipo_documento
      LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
      LEFT JOIN prioridad p          ON d.id_prioridad = p.id_prioridad
      LEFT JOIN dependencia dep_orig ON d.id_procedencia = dep_orig.id_dependencia
      LEFT JOIN dependencia dep_dest ON d.id_destino = dep_dest.id_dependencia
      LEFT JOIN usuario u            ON d.id_usuario = u.id_usuario
      LEFT JOIN funcionario f        ON u.id_funcionario = f.id_funcionario
      WHERE d.id_documento = @id
    `);
  return result.recordset[0] ?? null;
}

export async function findHistorial(idDocumento: number) {
  const pool = await getPool();
  const result = await pool
    .request()
    .input('id', sql.Int, idDocumento)
    .query<{ id_historial: number; accion: string | null; observacion: string | null; fecha: Date; usuario: string | null; nombres_fun: string | null }>(`
      SELECT h.id_historial, h.accion, h.observacion, h.fecha,
             u.usuario, f.nombres_fun
      FROM historial_documento h
      LEFT JOIN usuario u ON h.id_usuario = u.id_usuario
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      WHERE h.id_documento = @id
      ORDER BY h.fecha DESC
    `);
  return result.recordset;
}

export async function insert(data: {
  idTipoDocumento?: number;
  numDocumento?: string;
  asunto: string;
  idEstadoDocumento: number;
  idProcedencia?: number;
  idProcedenciaExterna?: number;
  idDestino: number;
  idUsuario: number;
  idPrioridad?: number;
  idExpediente?: number;
  fechaDocumento?: Date;
  observacion?: string;
}): Promise<number> {
  const pool = await getPool();
  const result = await pool
    .request()
    .input('idTipoDocumento', sql.Int, data.idTipoDocumento ?? null)
    .input('numDocumento', sql.VarChar(50), data.numDocumento ?? null)
    .input('asunto', sql.VarChar(300), data.asunto)
    .input('idEstadoDocumento', sql.Int, data.idEstadoDocumento)
    .input('idProcedencia', sql.Int, data.idProcedencia ?? null)
    .input('idProcedenciaExterna', sql.Int, data.idProcedenciaExterna ?? null)
    .input('idDestino', sql.Int, data.idDestino)
    .input('idUsuario', sql.Int, data.idUsuario)
    .input('idPrioridad', sql.Int, data.idPrioridad ?? 1)
    .input('idExpediente', sql.Int, data.idExpediente ?? null)
    .input('fechaDocumento', sql.Date, data.fechaDocumento ?? null)
    .input('observacion', sql.VarChar(500), data.observacion ?? null)
    .query<{ id_documento: number }>(`
      INSERT INTO documento (
        id_tipo_documento, num_documento, asunto, id_estado_documento,
        id_procedencia, id_procedencia_externa, id_destino,
        id_usuario, id_prioridad, id_expediente,
        fecha_documento, fecha_ingreso, observacion
      )
      OUTPUT INSERTED.id_documento
      VALUES (
        @idTipoDocumento, @numDocumento, @asunto, @idEstadoDocumento,
        @idProcedencia, @idProcedenciaExterna, @idDestino,
        @idUsuario, @idPrioridad, @idExpediente,
        @fechaDocumento, GETDATE(), @observacion
      )
    `);
  return result.recordset[0].id_documento;
}

export async function insertHistorial(data: {
  idDocumento: number;
  idUsuario: number;
  accion: string;
  observacion?: string;
}): Promise<void> {
  const pool = await getPool();
  await pool
    .request()
    .input('idDocumento', sql.Int, data.idDocumento)
    .input('idUsuario', sql.Int, data.idUsuario)
    .input('accion', sql.VarChar(50), data.accion)
    .input('observacion', sql.VarChar(500), data.observacion ?? null)
    .query(`
      INSERT INTO historial_documento (id_documento, id_usuario, accion, observacion, fecha)
      VALUES (@idDocumento, @idUsuario, @accion, @observacion, GETDATE())
    `);
}

export async function updateEstado(idDocumento: number, idEstado: number): Promise<void> {
  const pool = await getPool();
  const extra = idEstado === 5 ? ', fecha_cierre = GETDATE()' : '';
  await pool
    .request()
    .input('idEstado', sql.Int, idEstado)
    .input('idDocumento', sql.Int, idDocumento)
    .query(`UPDATE documento SET id_estado_documento = @idEstado${extra} WHERE id_documento = @idDocumento`);
}
