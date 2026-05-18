import { Request, Response, NextFunction } from 'express';
import * as service from './documento.service';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { sendSuccess, sendCreated, sendPaginated } from '../../shared/utils/response';

export async function listar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { data, meta } = await service.listarDocumentos(req.query as never);
    sendPaginated(res, data, meta);
  } catch (e) { next(e); }
}

export async function obtener(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const doc = await service.obtenerDocumento(Number(req.params.id));
    sendSuccess(res, doc);
  } catch (e) { next(e); }
}

export async function historial(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const data = await service.obtenerHistorial(Number(req.params.id));
    sendSuccess(res, data);
  } catch (e) { next(e); }
}

export async function crear(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { idUsuario } = (req as AuthenticatedRequest).user;
    const doc = await service.crearDocumento(req.body, idUsuario);
    sendCreated(res, doc, 'Documento creado exitosamente');
  } catch (e) { next(e); }
}

export async function derivar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { idUsuario } = (req as AuthenticatedRequest).user;
    const doc = await service.derivarDocumento(Number(req.params.id), req.body, idUsuario);
    sendSuccess(res, doc, 'Documento derivado exitosamente');
  } catch (e) { next(e); }
}
