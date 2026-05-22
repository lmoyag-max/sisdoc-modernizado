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

export { sql };
