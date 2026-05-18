import { getPool, sql } from '../../config/database';

export async function getTiposDocumento() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_tipo_documento AS id, desc_tipo_documento AS descripcion, activo
    FROM tipo_documento WHERE activo = 1 ORDER BY desc_tipo_documento
  `);
  return r.recordset;
}

export async function getEstadosDocumento() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_estado_documento AS id, desc_estado_documento AS descripcion
    FROM estado_documento ORDER BY id_estado_documento
  `);
  return r.recordset;
}

export async function getPrioridades() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_prioridad AS id, desc_prioridad AS descripcion, color
    FROM prioridad ORDER BY id_prioridad
  `);
  return r.recordset;
}

export async function getDependencias(soloActivas = true) {
  const pool = await getPool();
  const request = pool.request();
  const where = soloActivas ? 'WHERE activa = 1' : '';
  const r = await request.query(`
    SELECT id_dependencia AS id, desc_dependencia AS descripcion, sigla_dependencia AS sigla
    FROM dependencia ${where} ORDER BY desc_dependencia
  `);
  return r.recordset;
}

export async function getDescriptores() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_descriptor AS id, desc_descriptor AS descripcion
    FROM descriptor WHERE activo = 1 ORDER BY desc_descriptor
  `);
  return r.recordset;
}

export async function getFuncionariosPorDependencia(idDependencia: number) {
  const pool = await getPool();
  const r = await pool
    .request()
    .input('idDep', sql.Int, idDependencia)
    .query(`
      SELECT f.id_funcionario AS id,
             f.nombres_fun + ' ' + f.ap_pat_fun AS nombre,
             f.email_fun AS email
      FROM funcionario f
      WHERE f.id_dependencia = @idDep
      ORDER BY f.ap_pat_fun, f.nombres_fun
    `);
  return r.recordset;
}

export async function getDependenciasExternas() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_dependencia_externa AS id, desc_dependencia_externa AS descripcion, tipo
    FROM dependencia_externa ORDER BY desc_dependencia_externa
  `);
  return r.recordset;
}
