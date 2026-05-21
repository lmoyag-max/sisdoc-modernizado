import { Request } from 'express';

export interface ApiResponse<T = unknown> {
  ok: boolean;
  data?: T;
  message?: string;
  error?: string;
  details?: unknown;
}

export interface PaginatedResponse<T> extends ApiResponse<T[]> {
  meta: PaginationMeta;
}

export interface PaginationMeta {
  total: number;
  pagina: number;
  porPagina: number;
  totalPaginas: number;
}

export interface PaginationQuery {
  pagina?: number;
  porPagina?: number;
}

export interface AuthenticatedRequest extends Request {
  user: {
    idUsuario: number;
    usuario: string;
    idFuncionario: number | null;
    idDependencia: number | null;
    todosServicios: boolean;
    roles: string[];
    modulos: string[];
  };
}

export interface JwtPayload {
  sub: number;
  usuario: string;
  idFuncionario: number | null;
  idDependencia: number | null;
  todosServicios: boolean;
  roles: string[];
  modulos: string[];
  iat?: number;
  exp?: number;
}

export type AppError = {
  statusCode: number;
  message: string;
  details?: unknown;
};
