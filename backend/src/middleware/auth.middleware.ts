import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { env } from '../config/env';
import { JwtPayload, AuthenticatedRequest } from '../shared/types/api.types';
import { sendUnauthorized, sendForbidden } from '../shared/utils/response';

export function requireAuth(req: Request, res: Response, next: NextFunction): void {
  const authHeader = req.headers.authorization;
  const token = authHeader?.startsWith('Bearer ') ? authHeader.slice(7) : null;

  if (!token) {
    sendUnauthorized(res, 'Token de autenticación requerido');
    return;
  }

  try {
    const payload = jwt.verify(token, env.JWT_SECRET) as unknown as JwtPayload;
    (req as AuthenticatedRequest).user = {
      idUsuario:      payload.sub,
      usuario:        payload.usuario,
      idFuncionario:  payload.idFuncionario,
      idDependencia:  payload.idDependencia ?? null,
      todosServicios: payload.todosServicios ?? true,
      roles:          payload.roles ?? [],
      modulos:        payload.modulos ?? [],
    };
    next();
  } catch (error) {
    if (error instanceof jwt.TokenExpiredError) {
      sendUnauthorized(res, 'Token expirado');
    } else {
      sendUnauthorized(res, 'Token inválido');
    }
  }
}

export function requireRole(...roles: string[]) {
  return (req: Request, res: Response, next: NextFunction): void => {
    const user = (req as AuthenticatedRequest).user;
    if (!user) { sendUnauthorized(res); return; }
    if (!roles.some((role) => user.roles.includes(role))) {
      sendForbidden(res, `Requiere rol: ${roles.join(' o ')}`);
      return;
    }
    next();
  };
}

// Verifica que el JWT del usuario incluya acceso al módulo dado.
// Admin siempre pasa. Otros roles deben tener el módulo en su lista.
export function requireModule(modulo: string) {
  return (req: Request, res: Response, next: NextFunction): void => {
    const user = (req as AuthenticatedRequest).user;
    if (!user) { sendUnauthorized(res); return; }
    if (user.roles.includes('admin') || user.modulos.includes(modulo)) {
      next();
    } else {
      sendForbidden(res, `Acceso denegado al módulo: ${modulo}`);
    }
  };
}
