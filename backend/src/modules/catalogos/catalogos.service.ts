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

export async function getPrioridades() {
  // La tabla prioridad puede no existir en SISDOC legacy
  return [
    { id: 1, descripcion: 'Normal', color: '#6b7280' },
    { id: 2, descripcion: 'Urgente', color: '#f59e0b' },
    { id: 3, descripcion: 'Muy Urgente', color: '#ef4444' },
  ];
}

export async function getDependencias(_soloActivas = true) {
  const pool = await getPool();
  const r = await pool.request().query(`
    SELECT id_dependencia AS id, desc_dependencia AS descripcion,
           cod_dependencia AS sigla
    FROM dependencia ORDER BY desc_dependencia
  `);
  return r.recordset;
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
  const r = await pool
    .request()
    .input('idDep', sql.Int, idDependencia)
    .query(`
      SELECT f.id_funcionario AS id,
             f.nombres + ' ' + f.apellidos AS nombre
      FROM funcionario f
      WHERE f.id_dependencia = @idDep
      ORDER BY f.apellidos, f.nombres
    `);
  return r.recordset;
}

export async function getDependenciasExternas() {
  try {
    const pool = await getPool();
    const r = await pool.request().query(`
      SELECT id_dependencia_externa AS id, desc_dependencia_externa AS descripcion, tipo
      FROM dependencia_externa ORDER BY desc_dependencia_externa
    `);
    return r.recordset;
  } catch {
    return [];
  }
}
