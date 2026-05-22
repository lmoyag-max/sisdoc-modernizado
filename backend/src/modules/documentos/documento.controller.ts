import { Request, Response, NextFunction } from 'express';
import * as service from './documento.service';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { sendSuccess, sendCreated, sendPaginated, sendForbidden, sendError } from '../../shared/utils/response';

const user = (req: Request) => (req as AuthenticatedRequest).user;

// ── Helpers de acceso ─────────────────────────────────────────

function hasFullAccess(u: AuthenticatedRequest['user']): boolean {
  return u.roles.includes('admin') || u.todosServicios === true;
}

function canSeeExternals(u: AuthenticatedRequest['user']): boolean {
  return u.roles.includes('admin') || u.roles.includes('of.partes');
}

// ── Listar documentos (filtrado por servicio) ─────────────────

export async function listar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const u = user(req);
    const filtroServicio = hasFullAccess(u)
      ? null
      : { idDependencia: u.idDependencia, verExternos: canSeeExternals(u) };

    const { data, meta } = await service.listarDocumentos(req.query as never, filtroServicio);
    sendPaginated(res, data, meta);
  } catch (e) { next(e); }
}

// ── Obtener un documento (con control de acceso) ──────────────

export async function obtener(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const u   = user(req);
    const idDoc = Number(req.params.id);

    const doc = await service.obtenerDocumento(idDoc);

    // Control de acceso: si el usuario no tiene acceso total, verificar relación con el doc
    if (!hasFullAccess(u)) {
      const tieneAcceso = await service.usuarioTieneAccesoDocumento(
        idDoc,
        u.idDependencia,
        canSeeExternals(u),
      );
      if (!tieneAcceso) {
        sendForbidden(res, 'No tienes acceso a este documento');
        return;
      }
    }

    sendSuccess(res, doc);
  } catch (e) { next(e); }
}

// ── Trazabilidad / historial ──────────────────────────────────

export async function trazabilidad(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    sendSuccess(res, await service.obtenerTrazabilidad(Number(req.params.id)));
  } catch (e) { next(e); }
}

export async function historial(req: Request, res: Response, next: NextFunction): Promise<void> {
  return trazabilidad(req, res, next);
}

// ── Buscar por número exacto (num_interno o id_documento) ─────
// Endpoint rápido para trazabilidad: sin LIKE, sin paginación.
export async function buscarPorNumero(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const raw = String(req.query.numero ?? '').trim();
    const numero = parseInt(raw, 10);
    if (!raw || isNaN(numero) || numero <= 0) {
      sendError(res, 'El parámetro "numero" debe ser un número entero positivo', 400);
      return;
    }
    const rows = await service.buscarDocumentosPorNumero(numero);
    sendSuccess(res, rows);
  } catch (e) { next(e); }
}

// ── Crear ─────────────────────────────────────────────────────

export async function crear(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const u   = user(req);
    const doc = await service.crearDocumento(req.body, u.idUsuario, u.idDependencia);
    sendCreated(res, doc, 'Documento creado exitosamente');
  } catch (e) { next(e); }
}

// ── Flujo documental ──────────────────────────────────────────

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

export async function reabrir(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const doc = await service.reabrirDocumento(Number(req.params.id), req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento reabierto y devuelto a estado Recepcionado');
  } catch (e) { next(e); }
}

export async function eliminar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    await service.eliminarDocumento(Number(req.params.id), user(req).idUsuario);
    sendSuccess(res, null, 'Documento eliminado');
  } catch (e) { next(e); }
}
