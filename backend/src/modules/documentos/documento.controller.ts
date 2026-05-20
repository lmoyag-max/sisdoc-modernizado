import { Request, Response, NextFunction } from 'express';
import * as service from './documento.service';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { sendSuccess, sendCreated, sendPaginated } from '../../shared/utils/response';

const user = (req: Request) => (req as AuthenticatedRequest).user;

export async function listar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const { data, meta } = await service.listarDocumentos(req.query as never);
    sendPaginated(res, data, meta);
  } catch (e) { next(e); }
}

export async function obtener(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    sendSuccess(res, await service.obtenerDocumento(Number(req.params.id)));
  } catch (e) { next(e); }
}

export async function trazabilidad(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    sendSuccess(res, await service.obtenerTrazabilidad(Number(req.params.id)));
  } catch (e) { next(e); }
}

// alias histórico
export async function historial(req: Request, res: Response, next: NextFunction): Promise<void> {
  return trazabilidad(req, res, next);
}

export async function crear(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const doc = await service.crearDocumento(req.body, user(req).idUsuario);
    sendCreated(res, doc, 'Documento creado exitosamente');
  } catch (e) { next(e); }
}

export async function despachar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const doc = await service.despacharDocumento(Number(req.params.id), req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento despachado');
  } catch (e) { next(e); }
}

export async function recepcionar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const doc = await service.recepcionarDocumento(Number(req.params.id), req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento recepcionado');
  } catch (e) { next(e); }
}

export async function derivar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const doc = await service.derivarDocumento(Number(req.params.id), req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento derivado');
  } catch (e) { next(e); }
}

export async function terminar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const doc = await service.terminarDocumento(Number(req.params.id), req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento terminado');
  } catch (e) { next(e); }
}

export async function eliminar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    await service.eliminarDocumento(Number(req.params.id), user(req).idUsuario);
    sendSuccess(res, null, 'Documento eliminado');
  } catch (e) { next(e); }
}
