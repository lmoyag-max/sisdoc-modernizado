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
    const payload = jwt.verify(token, env.JWT_SECRET) as JwtPayload;
    (req as AuthenticatedRequest).user = {
      idUsuario: payload.sub,
      usuario: payload.usuario,
      idFuncionario: payload.idFuncionario,
      roles: payload.roles ?? [],
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
    if (!user) {
      sendUnauthorized(res);
      return;
    }
    const hasRole = roles.some((role) => user.roles.includes(role));
    if (!hasRole) {
      sendForbidden(res, `Requiere rol: ${roles.join(' o ')}`);
      return;
    }
    next();
  };
}
