import * as sql from 'mssql';
import { env } from './env';
import { logger } from '../shared/utils/logger';

const dbConfig: sql.config = {
  user: env.DB_USER,
  password: env.DB_PASSWORD,
  server: env.DB_SERVER,
  port: env.DB_PORT,
  database: env.DB_DATABASE,
  options: {
    encrypt: env.DB_ENCRYPT,
    trustServerCertificate: env.DB_TRUST_CERT,
    enableArithAbort: true,
  },
  pool: {
    max: 20,
    min: 2,
    idleTimeoutMillis: 30000,
  },
  connectionTimeout: 15000,
  requestTimeout: 30000,
};

let pool: sql.ConnectionPool | null = null;

export async function getPool(): Promise<sql.ConnectionPool> {
  if (pool && pool.connected) return pool;
  if (pool && !pool.connected) { pool = null; }

  try {
    pool = await sql.connect(dbConfig);
    logger.info(`Conectado a SQL Server — ${env.DB_SERVER}:${env.DB_PORT}/${env.DB_DATABASE}`);
    return pool;
  } catch (error) {
    logger.error('Error conectando a SQL Server:', error);
    throw error;
  }
}

export async function closePool(): Promise<void> {
  if (pool) {
    await pool.close();
    pool = null;
    logger.info('Pool de conexiones SQL Server cerrado');
  }
}

export async function ensureIndexes(): Promise<void> {
  try {
    const p = await getPool();
    await p.request().query(`
      IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_tramite_doc_dest' AND object_id = OBJECT_ID('tramite'))
        CREATE INDEX IX_tramite_doc_dest
          ON tramite (id_documento, id_destino, tipo_destinatario)
          INCLUDE (id_procedencia, tipo_procedencia);

      IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_tramite_doc_comp' AND object_id = OBJECT_ID('tramite'))
        CREATE INDEX IX_tramite_doc_comp
          ON tramite (id_documento, id_tipo_compromiso);

      IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_tramite_fecha' AND object_id = OBJECT_ID('tramite'))
        CREATE INDEX IX_tramite_fecha
          ON tramite (fecha_sistema DESC)
          INCLUDE (id_documento, id_estado_tramite, id_usuario);

      IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_documento_estado_fecha' AND object_id = OBJECT_ID('documento'))
        CREATE INDEX IX_documento_estado_fecha
          ON documento (id_estado_documento, fecha_sistema, fecha_update)
          INCLUDE (resuelto, id_tipo_documento);
    `);
    logger.info('Índices de rendimiento verificados');
  } catch (err) {
    logger.warn('ensureIndexes: error no fatal —', err);
  }
}

export { sql };
