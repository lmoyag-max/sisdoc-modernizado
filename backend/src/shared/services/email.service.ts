import nodemailer from 'nodemailer';
import { env } from '../../config/env';
import { logger } from '../utils/logger';

let transporter: nodemailer.Transporter | null = null;

function getTransporter(): nodemailer.Transporter {
  if (transporter) return transporter;

  // Si no hay SMTP configurado, usar un transporte que registra el correo en consola
  if (!env.SMTP_HOST || !env.SMTP_USER) {
    logger.warn('SMTP no configurado — los correos se mostrarán solo en consola (modo desarrollo)');
    transporter = nodemailer.createTransport({ jsonTransport: true });
    return transporter;
  }

  transporter = nodemailer.createTransport({
    host:   env.SMTP_HOST,
    port:   env.SMTP_PORT,
    secure: env.SMTP_SECURE,
    auth: {
      user: env.SMTP_USER,
      pass: env.SMTP_PASS,
    },
    tls: { rejectUnauthorized: false },
  });

  return transporter;
}

export interface SendMailOptions {
  to:      string;
  subject: string;
  html:    string;
  text?:   string;
}

export async function sendMail(opts: SendMailOptions): Promise<void> {
  const t = getTransporter();

  const info = await t.sendMail({
    from:    env.SMTP_FROM,
    to:      opts.to,
    subject: opts.subject,
    html:    opts.html,
    text:    opts.text ?? opts.html.replace(/<[^>]*>/g, ''),
  });

  // jsonTransport (modo sin SMTP): imprime el correo en logs para desarrollo
  if ((info as { message?: string }).message) {
    const parsed = JSON.parse((info as { message: string }).message);
    logger.info('=== CORREO (modo consola) ===');
    logger.info(`Para: ${parsed.to?.map((t: { address: string }) => t.address).join(', ')}`);
    logger.info(`Asunto: ${parsed.subject}`);
    logger.info('Contenido HTML disponible en info.message');
  } else {
    logger.info(`Correo enviado a ${opts.to} — messageId: ${info.messageId}`);
  }
}

export function buildPasswordResetEmail(nombre: string, resetUrl: string, minutosExpira: number): { html: string; text: string } {
  const html = `
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar contraseña — SISDOC</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 0;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#1e293b 0%,#312e81 100%);padding:32px 40px;">
            <table cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:#4f46e5;border-radius:10px;width:40px;height:40px;text-align:center;vertical-align:middle;">
                  <span style="color:#fff;font-weight:bold;font-size:14px;">SD</span>
                </td>
                <td style="padding-left:12px;">
                  <p style="margin:0;color:#ffffff;font-weight:700;font-size:16px;">SISDOC</p>
                  <p style="margin:0;color:#94a3b8;font-size:12px;">Sistema de Gestión Documental</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px 40px 32px;">
            <h1 style="margin:0 0 8px;color:#1e293b;font-size:22px;font-weight:700;">Recuperación de contraseña</h1>
            <p style="margin:0 0 24px;color:#64748b;font-size:14px;line-height:1.6;">
              Hola <strong>${nombre}</strong>, recibimos una solicitud para restablecer la contraseña de tu cuenta en SISDOC.
            </p>
            <p style="margin:0 0 24px;color:#64748b;font-size:14px;line-height:1.6;">
              Haz clic en el botón para crear una nueva contraseña. Este enlace es válido por <strong>${minutosExpira} minutos</strong> y solo puede usarse una vez.
            </p>

            <!-- CTA -->
            <table cellpadding="0" cellspacing="0" style="margin:0 0 32px;">
              <tr>
                <td style="background:#4f46e5;border-radius:8px;">
                  <a href="${resetUrl}" target="_blank"
                     style="display:inline-block;padding:14px 28px;color:#ffffff;font-weight:600;font-size:14px;text-decoration:none;letter-spacing:0.3px;">
                    Restablecer contraseña
                  </a>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 8px;color:#94a3b8;font-size:12px;">
              Si el botón no funciona, copia y pega este enlace en tu navegador:
            </p>
            <p style="margin:0 0 24px;word-break:break-all;">
              <a href="${resetUrl}" style="color:#4f46e5;font-size:12px;">${resetUrl}</a>
            </p>

            <!-- Nota red hospital -->
            <div style="background:#fef3c7;border:2px solid #f59e0b;border-radius:8px;padding:16px 20px;margin-bottom:16px;">
              <p style="margin:0 0 4px;color:#92400e;font-size:14px;font-weight:700;letter-spacing:0.3px;">
                ⚠️ NOTA IMPORTANTE
              </p>
              <p style="margin:0;color:#78350f;font-size:13px;line-height:1.6;">
                <strong>Debes estar conectado a la red del Hospital para poder restablecer tu contraseña.</strong>
                Si te encuentras fuera de las instalaciones, conéctate primero a la VPN institucional.
              </p>
            </div>

            <!-- Warning box -->
            <div style="background:#fef9c3;border:1px solid #fef08a;border-radius:8px;padding:16px 20px;">
              <p style="margin:0;color:#713f12;font-size:13px;line-height:1.6;">
                <strong>Aviso de seguridad:</strong> Si no solicitaste este cambio, ignora este correo. Tu contraseña actual seguirá siendo la misma.
              </p>
            </div>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="border-top:1px solid #e2e8f0;padding:20px 40px;background:#f8fafc;">
            <p style="margin:0;color:#94a3b8;font-size:11px;text-align:center;">
              © 2026 SISDOC v2 — Hospital Universitario Asociado de Puebla<br>
              Este es un correo automático, por favor no respondas.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>`;

  const text = `
SISDOC — Recuperación de contraseña
=====================================

Hola ${nombre},

Recibimos una solicitud para restablecer tu contraseña en SISDOC.

Usa el siguiente enlace para crear una nueva contraseña (válido por ${minutosExpira} minutos):
${resetUrl}

⚠️ NOTA IMPORTANTE: Debes estar conectado a la red del Hospital para poder restablecer tu contraseña.

Si no solicitaste este cambio, ignora este correo.

© 2026 SISDOC v2
`.trim();

  return { html, text };
}
