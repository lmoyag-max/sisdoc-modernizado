import { env } from './config/env';
import { logger } from './shared/utils/logger';
import { getPool, closePool } from './config/database';
import app from './app';

async function bootstrap(): Promise<void> {
  try {
    // Verificar conexión a base de datos antes de arrancar
    await getPool();

    const server = app.listen(env.PORT, () => {
      logger.info('═══════════════════════════════════════════════');
      logger.info(`  SISDOC API v2 — ${env.NODE_ENV.toUpperCase()}`);
      logger.info(`  Puerto: ${env.PORT}`);
      logger.info(`  API:    http://localhost:${env.PORT}/api/v1`);
      logger.info(`  Docs:   http://localhost:${env.PORT}/api-docs`);
      logger.info('═══════════════════════════════════════════════');
    });

    const shutdown = async (signal: string) => {
      logger.info(`${signal} recibido — cerrando servidor...`);
      server.close(async () => {
        await closePool();
        logger.info('Servidor cerrado correctamente');
        process.exit(0);
      });
    };

    process.on('SIGTERM', () => shutdown('SIGTERM'));
    process.on('SIGINT', () => shutdown('SIGINT'));

  } catch (error) {
    logger.error('Error al iniciar el servidor:', error);
    process.exit(1);
  }
}

bootstrap();
