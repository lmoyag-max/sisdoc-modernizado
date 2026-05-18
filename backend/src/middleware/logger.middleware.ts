import morgan from 'morgan';
import { logger } from '../shared/utils/logger';
import { env } from '../config/env';

const stream = {
  write: (message: string) => logger.http(message.trim()),
};

export const requestLogger = morgan(
  env.NODE_ENV === 'development' ? 'dev' : 'combined',
  { stream },
);
