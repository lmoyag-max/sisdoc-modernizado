import { z } from 'zod';
import dotenv from 'dotenv';

dotenv.config();

const envSchema = z.object({
  NODE_ENV: z.enum(['development', 'production', 'test']).default('development'),
  PORT: z.string().default('3001').transform(Number),
  DB_USER: z.string().min(1),
  DB_PASSWORD: z.string().min(1),
  DB_SERVER: z.string().min(1),
  DB_PORT: z.string().default('1433').transform(Number),
  DB_DATABASE: z.string().min(1),
  DB_TRUST_CERT: z.string().default('true').transform((v) => v === 'true'),
  DB_ENCRYPT: z.string().default('false').transform((v) => v === 'true'),
  DATABASE_URL: z.string().optional(),
  JWT_SECRET: z.string().min(32),
  JWT_REFRESH_SECRET: z.string().min(32),
  JWT_EXPIRES_IN: z.string().default('15m'),
  JWT_REFRESH_EXPIRES_IN: z.string().default('7d'),
  CORS_ORIGIN: z.string().default('http://localhost:5173'),
  UPLOAD_DIR: z.string().default('./uploads'),
  MAX_FILE_SIZE: z.string().default('20971520').transform(Number),
  // SMTP — recuperación de contraseña
  SMTP_HOST:    z.string().default(''),
  SMTP_PORT:    z.string().default('587').transform(Number),
  SMTP_SECURE:  z.string().default('false').transform((v) => v === 'true'),
  SMTP_USER:    z.string().default(''),
  SMTP_PASS:    z.string().default(''),
  SMTP_FROM:    z.string().default('SISDOC <noreply@sisdoc.cl>'),
  FRONTEND_URL: z.string().default('http://localhost:5173'),
  RESET_TOKEN_EXPIRES_MINUTES: z.string().default('30').transform(Number),
});

const parsed = envSchema.safeParse(process.env);

if (!parsed.success) {
  console.error('Variables de entorno inválidas:');
  console.error(parsed.error.flatten().fieldErrors);
  process.exit(1);
}

export const env = parsed.data;
export type Env = typeof env;
