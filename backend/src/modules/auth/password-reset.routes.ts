import { Router, Request, Response, NextFunction } from 'express';
import crypto from 'crypto';
import bcrypt from 'bcrypt';
import rateLimit from 'express-rate-limit';
import { z } from 'zod';
import { getPool, sql } from '../../config/database';
import { env } from '../../config/env';
import { logger } from '../../shared/utils/logger';
import { sendMail, buildPasswordResetEmail } from '../../shared/services/email.service';
import { sendSuccess, sendError } from '../../shared/utils/response';

const router = Router();

// Rate limit para forgot-password: 5 solicitudes por IP cada 15 minutos
const forgotLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: env.NODE_ENV === 'development' ? 20 : 5,
  message: { ok: false, error: 'Demasiadas solicitudes de recuperación. Espera 15 minutos.' },
  standardHeaders: true,
  legacyHeaders: false,
});

// Rate limit para reset-password: 10 intentos por IP cada 15 minutos (token ya fue validado)
const resetLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: env.NODE_ENV === 'development' ? 20 : 10,
  message: { ok: false, error: 'Demasiados intentos. Espera 15 minutos.' },
  standardHeaders: true,
  legacyHeaders: false,
});

// ── Schemas de validación ─────────────────────────────────────

const forgotSchema = z.object({
  email: z.string().email('Correo electrónico inválido').max(100),
});

const resetSchema = z.object({
  token:     z.string().min(10, 'Token inválido'),
  nuevaClave: z.string().min(4, 'La contraseña debe tener al menos 4 caracteres').max(10, 'Máximo 10 caracteres'),
});

// ── Helper: auditoría ─────────────────────────────────────────

async function audit(
  pool: Awaited<ReturnType<typeof getPool>>,
  evento: string,
  opts: { idUsuario?: number | null; email?: string; ip?: string; userAgent?: string; detalle?: string },
): Promise<void> {
  try {
    await pool.request()
      .input('evento',     sql.VarChar(50),  evento)
      .input('idUsuario',  sql.Int,          opts.idUsuario ?? null)
      .input('email',      sql.VarChar(100), opts.email ?? null)
      .input('ip',         sql.VarChar(45),  opts.ip ?? null)
      .input('userAgent',  sql.VarChar(500), (opts.userAgent ?? '').substring(0, 500))
      .input('detalle',    sql.VarChar(500), opts.detalle ?? null)
      .query(`
        INSERT INTO auditoria_reset (evento, id_usuario, email, ip, user_agent, detalle)
        VALUES (@evento, @idUsuario, @email, @ip, @userAgent, @detalle)
      `);
  } catch (e) {
    logger.warn('No se pudo registrar auditoría de reset: ' + String(e));
  }
}

// ── POST /auth/forgot-password ────────────────────────────────
// Genera token seguro y envía correo. Siempre responde igual sin revelar
// si el correo existe o no.
router.post('/forgot-password', forgotLimiter, async (req: Request, res: Response, next: NextFunction) => {
  const GENERIC_MSG = 'Si el correo existe en el sistema, recibirás un enlace de recuperación en breve.';
  const ip        = req.ip ?? req.socket.remoteAddress ?? '';
  const userAgent = req.headers['user-agent'] ?? '';

  try {
    const parsed = forgotSchema.safeParse(req.body);
    if (!parsed.success) {
      sendError(res, parsed.error.errors[0]?.message ?? 'Datos inválidos', 400);
      return;
    }
    const { email } = parsed.data;

    const pool = await getPool();

    // Buscar usuario por email (sin revelar si existe)
    const userRes = await pool.request()
      .input('email', sql.VarChar(100), email)
      .query<{ id_usuario: number; nombres: string | null; apellidos: string | null; usuario: string }>(`
        SELECT u.id_usuario,
               ISNULL(f.nombres, u.usuario) AS nombres,
               f.apellidos,
               u.usuario
        FROM usuario u
        LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
        WHERE u.email = @email
      `);

    const user = userRes.recordset[0];

    if (!user) {
      // No revelar que el correo no existe — igual responder genérico
      await audit(pool, 'FORGOT_EMAIL_NO_ENCONTRADO', { email, ip, userAgent });
      logger.info(`forgot-password: email no encontrado (${email}) — respuesta genérica`);
      sendSuccess(res, null, GENERIC_MSG);
      return;
    }

    // Invalidar tokens anteriores activos del mismo usuario
    await pool.request()
      .input('idUsr', sql.Int, user.id_usuario)
      .query(`
        UPDATE password_reset_tokens
        SET usado = 1, fecha_uso = GETDATE()
        WHERE id_usuario = @idUsr AND usado = 0
      `);

    // Generar token seguro (32 bytes = 64 hex chars)
    const rawToken   = crypto.randomBytes(32).toString('hex');
    const tokenHash  = crypto.createHash('sha256').update(rawToken).digest('hex');
    const expiraEn   = new Date(Date.now() + env.RESET_TOKEN_EXPIRES_MINUTES * 60 * 1000);

    await pool.request()
      .input('idUsr',    sql.Int,       user.id_usuario)
      .input('hash',     sql.VarChar(64), tokenHash)
      .input('expira',   sql.DateTime,  expiraEn)
      .input('ip',       sql.VarChar(45), ip)
      .input('ua',       sql.VarChar(500), userAgent.substring(0, 500))
      .query(`
        INSERT INTO password_reset_tokens
          (id_usuario, token_hash, fecha_expiracion, ip_solicitud, user_agent)
        VALUES (@idUsr, @hash, @expira, @ip, @ua)
      `);

    const resetUrl   = `${env.FRONTEND_URL}/reset-password?token=${rawToken}`;
    const nombre     = [user.nombres, user.apellidos].filter(Boolean).join(' ') || user.usuario;
    const { html, text } = buildPasswordResetEmail(nombre, resetUrl, env.RESET_TOKEN_EXPIRES_MINUTES);

    try {
      await sendMail({ to: email, subject: 'Recupera tu contraseña — SISDOC', html, text });
      await audit(pool, 'FORGOT_CORREO_ENVIADO', { idUsuario: user.id_usuario, email, ip, userAgent });
      logger.info(`forgot-password: correo enviado a ${email} (usuario ${user.usuario})`);
    } catch (mailErr) {
      logger.error(`forgot-password: fallo al enviar correo a ${email}: ${String(mailErr)}`);
      await audit(pool, 'FORGOT_CORREO_FALLO', { idUsuario: user.id_usuario, email, ip, userAgent, detalle: String(mailErr) });
    }

    sendSuccess(res, null, GENERIC_MSG);
  } catch (e) { next(e); }
});

// ── GET /auth/validate-reset-token?token= ────────────────────
// Valida si un token es válido (sin consumirlo). Usado por el frontend
// para mostrar/ocultar el formulario de nueva contraseña.
router.get('/validate-reset-token', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const raw = String(req.query.token ?? '').trim();
    if (!raw || raw.length < 10) { sendError(res, 'Token inválido', 400); return; }

    const tokenHash = crypto.createHash('sha256').update(raw).digest('hex');
    const pool = await getPool();

    const result = await pool.request()
      .input('hash', sql.VarChar(64), tokenHash)
      .query<{ id: number }>(`
        SELECT id FROM password_reset_tokens
        WHERE token_hash = @hash
          AND usado = 0
          AND fecha_expiracion > GETDATE()
      `);

    if (!result.recordset[0]) {
      sendError(res, 'El enlace de recuperación es inválido o ha expirado.', 400);
      return;
    }

    sendSuccess(res, { valido: true }, 'Token válido');
  } catch (e) { next(e); }
});

// ── POST /auth/reset-password ─────────────────────────────────
// Consume el token y actualiza la contraseña.
router.post('/reset-password', resetLimiter, async (req: Request, res: Response, next: NextFunction) => {
  const ip        = req.ip ?? req.socket.remoteAddress ?? '';
  const userAgent = req.headers['user-agent'] ?? '';

  try {
    const parsed = resetSchema.safeParse(req.body);
    if (!parsed.success) {
      sendError(res, parsed.error.errors[0]?.message ?? 'Datos inválidos', 400);
      return;
    }
    const { token: rawToken, nuevaClave } = parsed.data;

    const tokenHash = crypto.createHash('sha256').update(rawToken).digest('hex');
    const pool = await getPool();

    // Buscar token válido
    const tokenRes = await pool.request()
      .input('hash', sql.VarChar(64), tokenHash)
      .query<{ id: number; id_usuario: number }>(`
        SELECT id, id_usuario FROM password_reset_tokens
        WHERE token_hash = @hash
          AND usado = 0
          AND fecha_expiracion > GETDATE()
      `);

    const tkRow = tokenRes.recordset[0];

    if (!tkRow) {
      await audit(pool, 'RESET_TOKEN_INVALIDO', { ip, userAgent });
      sendError(res, 'El enlace de recuperación es inválido o ha expirado.', 400);
      return;
    }

    // Hashear nueva contraseña con bcrypt (y guardar texto plano truncado para compat legacy)
    const nuevaHash  = await bcrypt.hash(nuevaClave, 12);
    const claveCorta = nuevaClave.substring(0, 10);

    await pool.request()
      .input('hash',  sql.VarChar(255), nuevaHash)
      .input('clave', sql.VarChar(10),  claveCorta)
      .input('idUsr', sql.Int,          tkRow.id_usuario)
      .query(`
        UPDATE usuario
        SET clave_hash = @hash, clave = @clave
        WHERE id_usuario = @idUsr
      `);

    // Invalidar el token usado + todos los anteriores del mismo usuario
    await pool.request()
      .input('idUsr', sql.Int, tkRow.id_usuario)
      .input('idTok', sql.Int, tkRow.id)
      .query(`
        UPDATE password_reset_tokens
        SET usado = 1, fecha_uso = GETDATE()
        WHERE id_usuario = @idUsr AND usado = 0
      `);

    // Revocar todos los refresh tokens activos — fuerza re-login con la nueva contraseña
    await pool.request()
      .input('idUsr', sql.Int, tkRow.id_usuario)
      .query('UPDATE refresh_token SET revoked_at = GETDATE() WHERE id_usuario = @idUsr AND revoked_at IS NULL');

    await audit(pool, 'RESET_CONTRASENA_CAMBIADA', { idUsuario: tkRow.id_usuario, ip, userAgent });
    logger.info(`reset-password: contraseña cambiada para usuario id=${tkRow.id_usuario}`);

    sendSuccess(res, null, 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
  } catch (e) { next(e); }
});

export default router;
