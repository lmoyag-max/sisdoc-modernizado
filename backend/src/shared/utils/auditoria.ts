import { sql } from '../../config/database';
import { logger } from './logger';

type Pool = Awaited<ReturnType<typeof import('../../config/database').getPool>>;

interface AuditoriaOpts {
  idUsuario?: number | null;
  accion: string;
  recurso?: string | null;
  detalle?: string | null;
  ip?: string | null;
}

export async function logAuditoria(pool: Pool, opts: AuditoriaOpts): Promise<void> {
  try {
    await pool
      .request()
      .input('idUsuario', sql.Int,           opts.idUsuario ?? null)
      .input('accion',    sql.NVarChar(50),  opts.accion)
      .input('recurso',   sql.NVarChar(100), opts.recurso ?? null)
      .input('detalle',   sql.NVarChar(500), opts.detalle ?? null)
      .input('ip',        sql.NVarChar(45),  opts.ip ?? null)
      .query(`
        INSERT INTO auditoria (id_usuario, accion, recurso, detalle, ip)
        VALUES (@idUsuario, @accion, @recurso, @detalle, @ip)
      `);
  } catch (e) {
    logger.warn('logAuditoria: no se pudo registrar entrada — ' + String(e));
  }
}
