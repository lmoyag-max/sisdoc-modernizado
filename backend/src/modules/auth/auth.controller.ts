import { Request, Response, NextFunction } from 'express';
import * as authService from './auth.service';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { sendSuccess, sendUnauthorized } from '../../shared/utils/response';
import { env } from '../../config/env';

const COOKIE_OPTIONS = {
  httpOnly: true,
  secure: env.NODE_ENV === 'production',
  sameSite: 'strict' as const,
  maxAge: 7 * 24 * 60 * 60 * 1000,
  path: '/',
};

export async function login(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { user, tokens } = await authService.login(req.body);
    res.cookie('refreshToken', tokens.refreshToken, COOKIE_OPTIONS);
    sendSuccess(res, {
      user,
      accessToken: tokens.accessToken,
      expiresIn: tokens.expiresIn,
    });
  } catch (error) {
    next(error);
  }
}

export async function refresh(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const token = req.cookies?.refreshToken ?? req.body?.refreshToken;
    if (!token) {
      sendUnauthorized(res, 'Refresh token requerido');
      return;
    }
    const result = await authService.refreshAccessToken(token);
    sendSuccess(res, result);
  } catch (error) {
    next(error);
  }
}

export async function logout(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const user = (req as AuthenticatedRequest).user;
    const token = req.cookies?.refreshToken ?? req.body?.refreshToken;
    if (token && user) {
      await authService.logout(user.idUsuario, token);
    }
    res.clearCookie('refreshToken', { path: '/' });
    sendSuccess(res, null, 'Sesión cerrada');
  } catch (error) {
    next(error);
  }
}

export async function me(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { idUsuario } = (req as AuthenticatedRequest).user;
    const user = await authService.getMe(idUsuario);
    sendSuccess(res, user);
  } catch (error) {
    next(error);
  }
}
