import os from 'os';
import { env } from './config/env';
import { logger } from './shared/utils/logger';
import { getPool, closePool } from './config/database';
import app from './app';

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

    const server = app.listen(env.PORT, '0.0.0.0', () => {
      const ip = getLocalIP();
      logger.info('═══════════════════════════════════════════════════');
      logger.info(`  SISDOC API v2 — ${env.NODE_ENV.toUpperCase()}`);
      logger.info(`  Local:   http://localhost:${env.PORT}/api/v1`);
      logger.info(`  Red:     http://${ip}:${env.PORT}/api/v1`);
      logger.info(`  Docs:    http://localhost:${env.PORT}/api-docs`);
      logger.info(`  Health:  http://localhost:${env.PORT}/api/health`);
      logger.info('═══════════════════════════════════════════════════');
    });

    const shutdown = async (signal: string) => {
      logger.info(`${signal} — cerrando servidor...`);
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
