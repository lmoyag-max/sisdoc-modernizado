import { Request, Response, NextFunction } from 'express';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { sendSuccess, sendCreated, sendPaginated } from '../../shared/utils/response';
import * as service from './libro-referencias.service';
import * as repo from './libro-referencias.repository';
import {
  FiltrosReferenciaDto, CrearReferenciaDto, EditarReferenciaDto, EliminarReferenciaDto, EliminarDefinitivoDto,
} from './libro-referencias.schema';

const user = (req: Request) => (req as AuthenticatedRequest).user;

export async function listar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { data, meta } = await service.listar(req.query as unknown as FiltrosReferenciaDto, false);
    sendPaginated(res, data, meta);
  } catch (e) { next(e); }
}

// Exclusivo Superadministrador — gateado además por requireRole('admin') en las rutas.
export async function listarEliminados(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { data, meta } = await service.listar(req.query as unknown as FiltrosReferenciaDto, true);
    sendPaginated(res, data, meta);
  } catch (e) { next(e); }
}

export async function obtener(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    sendSuccess(res, await service.obtener(Number(req.params.id)));
  } catch (e) { next(e); }
}

export async function metricas(_req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    sendSuccess(res, await service.metricas());
  } catch (e) { next(e); }
}

export async function crear(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const u = user(req);
    const nombre = await repo.resolverNombreCreador(u.idUsuario);
    const referencia = await service.crear(
      req.body as CrearReferenciaDto,
      { idUsuario: u.idUsuario, nombre },
      req.ip ?? null,
    );
    sendCreated(res, referencia, `Referencia ${referencia.codigo} creada correctamente`);
  } catch (e) { next(e); }
}

export async function actualizar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const referencia = await service.actualizar(
      Number(req.params.id),
      req.body as EditarReferenciaDto,
      { idUsuario: user(req).idUsuario },
      req.ip ?? null,
    );
    sendSuccess(res, referencia, 'Referencia actualizada correctamente');
  } catch (e) { next(e); }
}

// Exclusivo Superadministrador — gateado además por requireRole('admin') en las rutas.
export async function eliminar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { motivo } = req.body as EliminarReferenciaDto;
    const referencia = await service.eliminar(
      Number(req.params.id),
      motivo,
      { idUsuario: user(req).idUsuario },
      req.ip ?? null,
    );
    sendSuccess(res, referencia, 'Referencia eliminada correctamente');
  } catch (e) { next(e); }
}

// Nivel 2 — eliminación física, exclusiva Superadministrador, solo sobre un
// registro ya en condición ELIMINADO. Endpoint separado de `eliminar` a
// propósito (ver libro-referencias.routes.ts).
export async function eliminarDefinitivo(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { motivo } = req.body as EliminarDefinitivoDto;
    const referencia = await service.eliminarDefinitivo(
      Number(req.params.id),
      motivo,
      { idUsuario: user(req).idUsuario },
      req.ip ?? null,
    );
    sendSuccess(res, referencia, `Referencia ${referencia.codigo} eliminada definitivamente`);
  } catch (e) { next(e); }
}
