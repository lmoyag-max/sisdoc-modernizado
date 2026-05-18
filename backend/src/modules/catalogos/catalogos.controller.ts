import { Request, Response, NextFunction } from 'express';
import * as service from './catalogos.service';
import { sendSuccess } from '../../shared/utils/response';

export async function tiposDocumento(_req: Request, res: Response, next: NextFunction): Promise<void> {
  try { sendSuccess(res, await service.getTiposDocumento()); } catch (e) { next(e); }
}
export async function estados(_req: Request, res: Response, next: NextFunction): Promise<void> {
  try { sendSuccess(res, await service.getEstadosDocumento()); } catch (e) { next(e); }
}
export async function prioridades(_req: Request, res: Response, next: NextFunction): Promise<void> {
  try { sendSuccess(res, await service.getPrioridades()); } catch (e) { next(e); }
}
export async function dependencias(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const soloActivas = req.query.todas !== 'true';
    sendSuccess(res, await service.getDependencias(soloActivas));
  } catch (e) { next(e); }
}
export async function descriptores(_req: Request, res: Response, next: NextFunction): Promise<void> {
  try { sendSuccess(res, await service.getDescriptores()); } catch (e) { next(e); }
}
export async function funcionariosPorDependencia(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    sendSuccess(res, await service.getFuncionariosPorDependencia(Number(req.params.idDep)));
  } catch (e) { next(e); }
}
export async function dependenciasExternas(_req: Request, res: Response, next: NextFunction): Promise<void> {
  try { sendSuccess(res, await service.getDependenciasExternas()); } catch (e) { next(e); }
}
