import { getPool, sql } from '../../config/database';

export async function getTiposDocumento() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_tipo_documento AS id, desc_tipo_documento AS descripcion
    FROM tipo_documento ORDER BY desc_tipo_documento
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

export async function getEstadosTramite() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_estado_tramite AS id, desc_estado_tramite AS descripcion
    FROM estado_tramite ORDER BY id_estado_tramite
  `);
  return r.recordset;
}

export async function getPrioridades() {
  return [
    { id: 1, descripcion: 'Normal', color: '#6b7280' },
    { id: 2, descripcion: 'Urgente', color: '#f59e0b' },
    { id: 3, descripcion: 'Muy Urgente', color: '#ef4444' },
  ];
}

export async function getDependencias(_soloActivas = true) {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_dependencia AS id, LTRIM(RTRIM(desc_dependencia)) AS descripcion
    FROM dependencia
    WHERE LTRIM(RTRIM(ISNULL(desc_dependencia,''))) <> ''
    ORDER BY desc_dependencia
  `);
  return r.recordset;
}

export async function getDependenciasExternas() {
  try {
    const pool = await getPool();
    const r = await pool.request().query(`
      SELECT id_dependencia_externa AS id, desc_dependencia_externa AS descripcion
      FROM dependencia_externa ORDER BY desc_dependencia_externa
    `);
    return r.recordset;
  } catch { return []; }
}

export async function getDescriptores() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_descriptor AS id, desc_descriptor AS descripcion
    FROM descriptor ORDER BY desc_descriptor
  `);
  return r.recordset;
}

export async function getFuncionariosPorDependencia(idDependencia: number) {
  const pool = await getPool();
  const r = await pool.request()
    .input('idDep', sql.Int, idDependencia)
    .query(`
      SELECT f.id_funcionario AS id,
             LTRIM(RTRIM(f.nombres)) + ' ' + LTRIM(RTRIM(f.apellidos)) AS nombre
      FROM funcionario f
      WHERE f.id_dependencia = @idDep
        AND LTRIM(RTRIM(ISNULL(f.nombres,''))) <> ''
      ORDER BY f.apellidos, f.nombres
    `);
  return r.recordset;
}

export async function getTodosFuncionarios() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT f.id_funcionario AS id,
           LTRIM(RTRIM(f.nombres)) + ' ' + LTRIM(RTRIM(f.apellidos)) AS nombre,
           f.id_dependencia,
           LTRIM(RTRIM(d.desc_dependencia)) AS dependencia
    FROM funcionario f
    LEFT JOIN dependencia d ON f.id_dependencia = d.id_dependencia
    WHERE LTRIM(RTRIM(ISNULL(f.nombres,''))) <> ''
    ORDER BY f.apellidos, f.nombres
  `);
  return r.recordset;
}

export async function getTiposDistribucion() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_tipo_distribucion AS id, desc_tipo_distribucion AS descripcion
    FROM tipo_distribucion ORDER BY id_tipo_distribucion
  `);
  return r.recordset;
}

export async function getTiposCompromiso() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_tipo_compromiso AS id, desc_tipo_compromiso AS descripcion
    FROM tipo_compromiso ORDER BY id_tipo_compromiso
  `);
  return r.recordset;
}

export async function getEstadosCompromiso() {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_estado_compromiso AS id, desc_estado_compromiso AS descripcion
    FROM estado_compromiso ORDER BY id_estado_compromiso
  `);
  return r.recordset;
}
