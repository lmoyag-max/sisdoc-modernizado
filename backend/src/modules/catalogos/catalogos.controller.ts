import { Request, Response, NextFunction } from 'express';
import * as service from './catalogos.service';
import { sendSuccess } from '../../shared/utils/response';

const h = (fn: () => Promise<unknown>) =>
  async (_req: Request, res: Response, next: NextFunction): Promise<void> => {
    try { sendSuccess(res, await fn()); } catch (e) { next(e); }
  };

export const tiposDocumento       = h(service.getTiposDocumento);
export const estados              = h(service.getEstadosDocumento);
export const estadosTramite       = h(service.getEstadosTramite);
export const prioridades          = h(service.getPrioridades);
export const descriptores         = h(service.getDescriptores);
export const dependenciasExternas = h(service.getDependenciasExternas);
export const tiposDistribucion    = h(service.getTiposDistribucion);
export const tiposCompromiso      = h(service.getTiposCompromiso);
export const estadosCompromiso    = h(service.getEstadosCompromiso);
export const todosFuncionarios    = h(service.getTodosFuncionarios);

export async function dependencias(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    sendSuccess(res, await service.getDependencias(req.query.todas !== 'true'));
  } catch (e) { next(e); }
}

export async function funcionariosPorDependencia(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    sendSuccess(res, await service.getFuncionariosPorDependencia(Number(req.params.idDep)));
  } catch (e) { next(e); }
}
