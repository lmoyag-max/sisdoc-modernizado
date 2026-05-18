import { Request, Response, NextFunction } from 'express';
import { ZodError } from 'zod';
import { logger } from '../shared/utils/logger';
import { env } from '../config/env';

export function errorHandler(
  err: unknown,
  req: Request,
  res: Response,
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  _next: NextFunction,
): void {
  if (err instanceof ZodError) {
    res.status(400).json({
      ok: false,
      error: 'Datos de entrada inválidos',
      details: err.flatten().fieldErrors,
    });
    return;
  }

  if (isHttpError(err)) {
    res.status(err.statusCode).json({ ok: false, error: err.message });
    return;
  }

  logger.error('Error no manejado:', { error: err, path: req.path, method: req.method });

  res.status(500).json({
    ok: false,
    error: 'Error interno del servidor',
    ...(env.NODE_ENV === 'development' && { details: String(err) }),
  });
}

export function notFoundHandler(req: Request, res: Response): void {
  res.status(404).json({ ok: false, error: `Ruta ${req.method} ${req.path} no encontrada` });
}

function isHttpError(err: unknown): err is { statusCode: number; message: string } {
  return typeof err === 'object' && err !== null && 'statusCode' in err && 'message' in err;
}

export function createHttpError(statusCode: number, message: string): { statusCode: number; message: string } {
  return { statusCode, message };
}
