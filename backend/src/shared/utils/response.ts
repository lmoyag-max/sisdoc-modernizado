import { Response } from 'express';
import { ApiResponse, PaginatedResponse, PaginationMeta } from '../types/api.types';

export function sendSuccess<T>(res: Response, data: T, message?: string, status = 200): Response {
  const body: ApiResponse<T> = { ok: true, data, message };
  return res.status(status).json(body);
}

export function sendCreated<T>(res: Response, data: T, message?: string): Response {
  return sendSuccess(res, data, message, 201);
}

export function sendPaginated<T>(
  res: Response,
  data: T[],
  meta: PaginationMeta,
): Response {
  const body: PaginatedResponse<T> = { ok: true, data, meta };
  return res.status(200).json(body);
}

export function sendError(
  res: Response,
  message: string,
  status = 500,
  details?: unknown,
): Response {
  const body: ApiResponse = { ok: false, error: message, details };
  return res.status(status).json(body);
}

export function sendNotFound(res: Response, resource = 'Recurso'): Response {
  return sendError(res, `${resource} no encontrado`, 404);
}

export function sendUnauthorized(res: Response, message = 'No autorizado'): Response {
  return sendError(res, message, 401);
}

export function sendForbidden(res: Response, message = 'Acceso denegado'): Response {
  return sendError(res, message, 403);
}

export function sendBadRequest(res: Response, message: string, details?: unknown): Response {
  return sendError(res, message, 400, details);
}

export function buildPaginationMeta(
  total: number,
  pagina: number,
  porPagina: number,
): PaginationMeta {
  return {
    total,
    pagina,
    porPagina,
    totalPaginas: Math.ceil(total / porPagina),
  };
}
