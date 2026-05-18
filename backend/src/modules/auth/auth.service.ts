import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import { v4 as uuidv4 } from 'uuid';
import { getPool, sql } from '../../config/database';
import { env } from '../../config/env';
import { logger } from '../../shared/utils/logger';
import { JwtPayload } from '../../shared/types/api.types';
import { LoginDto } from './auth.schema';

const BCRYPT_ROUNDS = 12;

export interface AuthTokens {
  accessToken: string;
  refreshToken: string;
  expiresIn: number;
}

export interface UserSession {
  idUsuario: number;
  usuario: string;
  idFuncionario: number | null;
  nombres: string | null;
  apPat: string | null;
  apMat: string | null;
  email: string | null;
  idDependencia: number | null;
  descDependencia: string | null;
  roles: string[];
}

export async function login(dto: LoginDto): Promise<{ user: UserSession; tokens: AuthTokens }> {
  const pool = await getPool();

  const result = await pool
    .request()
    .input('usuario', sql.VarChar(50), dto.usuario)
    .query<{
      id_usuario: number;
      usuario: string;
      clave: string | null;
      clave_hash: string | null;
      id_funcionario: number | null;
      nombres_fun: string | null;
      ap_pat_fun: string | null;
      ap_mat_fun: string | null;
      email_fun: string | null;
      id_dependencia: number | null;
      desc_dependencia: string | null;
      activo: boolean | null;
    }>(`
      SELECT
        u.id_usuario, u.usuario, u.clave, u.clave_hash,
        u.id_funcionario,
        f.nombres_fun, f.ap_pat_fun, f.ap_mat_fun, f.email_fun, f.id_dependencia,
        d.desc_dependencia,
        ISNULL(u.activo, 1) AS activo
      FROM usuario u
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      LEFT JOIN dependencia d ON f.id_dependencia = d.id_dependencia
      WHERE u.usuario = @usuario
    `);

  const row = result.recordset[0];

  if (!row) throw createAuthError('Credenciales inválidas');
  if (row.activo === false) throw createAuthError('Usuario deshabilitado');

  const isValid = await verifyPassword(dto.clave, row.clave, row.clave_hash);
  if (!isValid) throw createAuthError('Credenciales inválidas');

  // Migración gradual: si no tiene hash, generarlo y guardarlo
  if (!row.clave_hash) {
    const hash = await bcrypt.hash(dto.clave, BCRYPT_ROUNDS);
    await saveClavHash(pool, row.id_usuario, hash);
  }

  const roles = await getUserRoles(pool, row.id_usuario);

  const user: UserSession = {
    idUsuario: row.id_usuario,
    usuario: row.usuario,
    idFuncionario: row.id_funcionario,
    nombres: row.nombres_fun,
    apPat: row.ap_pat_fun,
    apMat: row.ap_mat_fun,
    email: row.email_fun,
    idDependencia: row.id_dependencia,
    descDependencia: row.desc_dependencia,
    roles,
  };

  const tokens = generateTokens(user);
  await saveRefreshToken(pool, row.id_usuario, tokens.refreshToken);

  logger.info(`Login exitoso: ${user.usuario} (id: ${user.idUsuario})`);
  return { user, tokens };
}

export async function refreshAccessToken(
  refreshToken: string,
): Promise<{ accessToken: string; expiresIn: number }> {
  let payload: JwtPayload;
  try {
    payload = jwt.verify(refreshToken, env.JWT_REFRESH_SECRET) as JwtPayload;
  } catch {
    throw createAuthError('Refresh token inválido o expirado', 401);
  }

  const pool = await getPool();
  const result = await pool
    .request()
    .input('token', sql.VarChar(500), refreshToken)
    .input('idUsuario', sql.Int, payload.sub)
    .query<{ id: string }>(`
      SELECT id FROM refresh_token
      WHERE token = @token
        AND id_usuario = @idUsuario
        AND expires_at > GETDATE()
        AND revoked_at IS NULL
    `);

  if (!result.recordset[0]) throw createAuthError('Refresh token revocado o expirado', 401);

  const accessToken = jwt.sign(
    { sub: payload.sub, usuario: payload.usuario, idFuncionario: payload.idFuncionario, roles: payload.roles },
    env.JWT_SECRET,
    { expiresIn: env.JWT_EXPIRES_IN } as jwt.SignOptions,
  );

  return { accessToken, expiresIn: 900 };
}

export async function logout(idUsuario: number, refreshToken: string): Promise<void> {
  const pool = await getPool();
  await pool
    .request()
    .input('token', sql.VarChar(500), refreshToken)
    .input('idUsuario', sql.Int, idUsuario)
    .query(`
      UPDATE refresh_token
      SET revoked_at = GETDATE()
      WHERE token = @token AND id_usuario = @idUsuario
    `);
}

export async function getMe(idUsuario: number): Promise<UserSession> {
  const pool = await getPool();
  const result = await pool
    .request()
    .input('idUsuario', sql.Int, idUsuario)
    .query<{
      id_usuario: number;
      usuario: string;
      id_funcionario: number | null;
      nombres_fun: string | null;
      ap_pat_fun: string | null;
      ap_mat_fun: string | null;
      email_fun: string | null;
      id_dependencia: number | null;
      desc_dependencia: string | null;
    }>(`
      SELECT u.id_usuario, u.usuario, u.id_funcionario,
             f.nombres_fun, f.ap_pat_fun, f.ap_mat_fun, f.email_fun, f.id_dependencia,
             d.desc_dependencia
      FROM usuario u
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      LEFT JOIN dependencia d ON f.id_dependencia = d.id_dependencia
      WHERE u.id_usuario = @idUsuario
    `);

  const row = result.recordset[0];
  if (!row) throw createAuthError('Usuario no encontrado', 404);

  const roles = await getUserRoles(pool, idUsuario);

  return {
    idUsuario: row.id_usuario,
    usuario: row.usuario,
    idFuncionario: row.id_funcionario,
    nombres: row.nombres_fun,
    apPat: row.ap_pat_fun,
    apMat: row.ap_mat_fun,
    email: row.email_fun,
    idDependencia: row.id_dependencia,
    descDependencia: row.desc_dependencia,
    roles,
  };
}

// ── Helpers ──────────────────────────────────────────────────

async function verifyPassword(
  clave: string,
  claveRaw: string | null,
  claveHash: string | null,
): Promise<boolean> {
  if (claveHash) return bcrypt.compare(clave, claveHash);
  // Fallback legacy: comparación directa (texto plano)
  return clave === claveRaw;
}

async function saveClavHash(pool: Awaited<ReturnType<typeof getPool>>, idUsuario: number, hash: string): Promise<void> {
  try {
    await pool
      .request()
      .input('hash', sql.VarChar(255), hash)
      .input('idUsuario', sql.Int, idUsuario)
      .query('UPDATE usuario SET clave_hash = @hash WHERE id_usuario = @idUsuario');
  } catch {
    // La columna clave_hash puede no existir aún en el schema legacy — no bloquear el login
    logger.warn('No se pudo guardar clave_hash (columna puede no existir aún)');
  }
}

async function getUserRoles(pool: Awaited<ReturnType<typeof getPool>>, idUsuario: number): Promise<string[]> {
  try {
    const result = await pool
      .request()
      .input('idUsuario', sql.Int, idUsuario)
      .query<{ codigo: string }>(`
        SELECT r.codigo
        FROM usuario_rol ur
        JOIN rol r ON ur.id_rol = r.id_rol
        WHERE ur.id_usuario = @idUsuario AND r.activo = 1
      `);
    return result.recordset.map((r) => r.codigo);
  } catch {
    return ['funcionario']; // Rol por defecto si la tabla no existe aún
  }
}

async function saveRefreshToken(
  pool: Awaited<ReturnType<typeof getPool>>,
  idUsuario: number,
  token: string,
): Promise<void> {
  const id = uuidv4();
  const expiresAt = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000);

  try {
    await pool
      .request()
      .input('id', sql.VarChar(50), id)
      .input('token', sql.VarChar(500), token)
      .input('idUsuario', sql.Int, idUsuario)
      .input('expiresAt', sql.DateTime, expiresAt)
      .query(`
        INSERT INTO refresh_token (id, token, id_usuario, expires_at, created_at)
        VALUES (@id, @token, @idUsuario, @expiresAt, GETDATE())
      `);
  } catch {
    logger.warn('No se pudo guardar refresh token (tabla puede no existir aún)');
  }
}

function generateTokens(user: UserSession): AuthTokens {
  const payload: Omit<JwtPayload, 'iat' | 'exp'> = {
    sub: user.idUsuario,
    usuario: user.usuario,
    idFuncionario: user.idFuncionario,
    roles: user.roles,
  };

  const accessToken = jwt.sign(payload, env.JWT_SECRET, {
    expiresIn: env.JWT_EXPIRES_IN,
  } as jwt.SignOptions);

  const refreshToken = jwt.sign(payload, env.JWT_REFRESH_SECRET, {
    expiresIn: env.JWT_REFRESH_EXPIRES_IN,
  } as jwt.SignOptions);

  return { accessToken, refreshToken, expiresIn: 900 };
}

function createAuthError(message: string, statusCode = 401): { statusCode: number; message: string } {
  return { statusCode, message };
}
