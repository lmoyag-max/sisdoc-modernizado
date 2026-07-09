import os from 'os';
import { env } from './config/env';
import { logger } from './shared/utils/logger';
import { getPool, closePool, ensureIndexes } from './config/database';
import app from './app';
import { startAlertaScheduler, stopAlertaScheduler } from './shared/services/alertas.scheduler';

function getLocalIP(): string {
  const ifaces = os.networkInterfaces();
  for (const name of Object.keys(ifaces)) {
    for (const iface of ifaces[name] ?? []) {
      if (iface.family === 'IPv4' && !iface.internal) return iface.address;
    }
  }
  return 'localhost';
}

async function bootstrap(): Promise<void> {
  try {
    await getPool();
    ensureIndexes(); // non-blocking — crea índices si no existen

    // Sin SMTP configurado, email.service.ts usa jsonTransport (los correos
    // "se envían" y quedan como estado='ok' en alerta_log sin llegar a nadie)
    // — en producción esto oculta una falla real de entrega. Solo advertir
    // aquí, no bloquear el arranque: alertas por correo es una función
    // secundaria del sistema.
    if (env.NODE_ENV === 'production' && (!env.SMTP_HOST || !env.SMTP_USER)) {
      logger.warn('SMTP no configurado en producción — las alertas se registrarán como enviadas sin llegar a ningún destinatario real. Configura SMTP_HOST/SMTP_USER en .env.');
    }

    startAlertaScheduler();

    const server = app.listen(env.PORT, '0.0.0.0', () => {
      const ip = getLocalIP();
      logger.info('═══════════════════════════════════════════════════');
      logger.info(`  DOC360 API v2 — ${env.NODE_ENV.toUpperCase()}`);
      logger.info(`  Local:   http://localhost:${env.PORT}/api/v1`);
      logger.info(`  Red:     http://${ip}:${env.PORT}/api/v1`);
      logger.info(`  Docs:    http://localhost:${env.PORT}/api-docs`);
      logger.info(`  Health:  http://localhost:${env.PORT}/api/health`);
      logger.info('═══════════════════════════════════════════════════');
    });

    const shutdown = async (signal: string) => {
      logger.info(`${signal} — cerrando servidor...`);
      stopAlertaScheduler();
      server.close(async () => {
        await closePool();
        process.exit(0);
      });
    };

    process.on('SIGTERM', () => shutdown('SIGTERM'));
    process.on('SIGINT', () => shutdown('SIGINT'));

  } catch (error) {
    logger.error('Error al iniciar servidor:', error);
    process.exit(1);
  }
}

bootstrap();
