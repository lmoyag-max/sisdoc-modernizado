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

// Antes solo `obtener`/`trazabilidad` verificaban pertenencia al documento —
// las transiciones de flujo (despachar/recepcionar/derivar/terminar/reabrir)
// solo validaban rol y estado, permitiendo que cualquier usuario con rol
// genérico operara documentos de cualquier servicio (incluidos reservados,
// que solo restringen quién puede crearlos, no quién puede operarlos después).
async function verificarAccesoDocumento(req: Request, res: Response, idDoc: number): Promise<boolean> {
  const u = user(req);
  if (hasFullAccess(u)) return true;
  const tieneAcceso = await service.usuarioTieneAccesoDocumento(idDoc, u.idDependencia, canSeeExternals(u));
  if (!tieneAcceso) {
    sendForbidden(res, 'No tienes acceso a este documento');
    return false;
  }
  return true;
}

// ── Listar documentos (filtrado por servicio) ─────────────────

export async function listar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const u = user(req);
    const filtroServicio = hasFullAccess(u)
      ? null
      : { idDependencia: u.idDependencia, verExternos: canSeeExternals(u) };

    const { data, meta, resumen } = await service.listarDocumentos(req.query as never, filtroServicio);
    sendPaginated(res, data, meta, { resumen });
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
    const u     = user(req);
    const idDoc = Number(req.params.id);

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

    sendSuccess(res, await service.obtenerTrazabilidad(idDoc));
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

    // Sin esto, cualquier usuario autenticado podía iterar números correlativos
    // y leer documentos (incluidos reservados) de servicios ajenos al suyo.
    const u = user(req);
    if (hasFullAccess(u)) { sendSuccess(res, rows); return; }
    const visibles = [];
    for (const row of rows) {
      const tieneAcceso = await service.usuarioTieneAccesoDocumento(row.idDocumento, u.idDependencia, canSeeExternals(u));
      if (tieneAcceso) visibles.push(row);
    }
    sendSuccess(res, visibles);
  } catch (e) { next(e); }
}

// ── Crear ─────────────────────────────────────────────────────

function esOficinaPartesOAdmin(u: AuthenticatedRequest['user']): boolean {
  return u.roles.includes('admin') || u.roles.includes('of.partes');
}

export async function crear(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const u = user(req);

    // Solo admin y of.partes pueden crear documentos con destino externo.
    if (req.body.tipoDestinatario === 'E' && !canSeeExternals(u)) {
      sendForbidden(res, 'No tienes permisos para crear documentos con destino externo');
      return;
    }

    // Solo admin y of.partes pueden usar tipo soporte Físico o marcar como Reservado.
    if ((req.body.tipoSoporte === 'F' || req.body.reservado === true) && !esOficinaPartesOAdmin(u)) {
      sendForbidden(res, 'Solo Oficina de Partes puede crear documentos físicos o reservados');
      return;
    }

    const doc = await service.crearDocumento(req.body, u.idUsuario, u.idDependencia);
    sendCreated(res, doc, 'Documento creado exitosamente');
  } catch (e) { next(e); }
}

// ── Flujo documental ──────────────────────────────────────────

export async function despachar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const idDoc = Number(req.params.id);
    if (!(await verificarAccesoDocumento(req, res, idDoc))) return;
    const doc = await service.despacharDocumento(idDoc, req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento despachado');
  } catch (e) { next(e); }
}

export async function recepcionar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const idDoc = Number(req.params.id);
    if (!(await verificarAccesoDocumento(req, res, idDoc))) return;
    const doc = await service.recepcionarDocumento(idDoc, req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento recepcionado');
  } catch (e) { next(e); }
}

export async function derivar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const idDoc = Number(req.params.id);
    if (!(await verificarAccesoDocumento(req, res, idDoc))) return;
    const doc = await service.derivarDocumento(idDoc, req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento derivado');
  } catch (e) { next(e); }
}

export async function terminar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const idDoc = Number(req.params.id);
    if (!(await verificarAccesoDocumento(req, res, idDoc))) return;
    const doc = await service.terminarDocumento(idDoc, req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento terminado');
  } catch (e) { next(e); }
}

export async function reabrir(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const idDoc = Number(req.params.id);
    if (!(await verificarAccesoDocumento(req, res, idDoc))) return;
    const doc = await service.reabrirDocumento(idDoc, req.body, user(req).idUsuario);
    sendSuccess(res, doc, 'Documento reabierto y devuelto a estado Recepcionado');
  } catch (e) { next(e); }
}

export async function eliminar(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    await service.eliminarDocumento(Number(req.params.id), user(req).idUsuario);
    sendSuccess(res, null, 'Documento eliminado');
  } catch (e) { next(e); }
}

// ── Multi-destino ─────────────────────────────────────────────

export async function listarDestinos(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    sendSuccess(res, await service.obtenerDestinos(Number(req.params.id)));
  } catch (e) { next(e); }
}

export async function recepcionarDestino(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const idDoc = Number(req.params.id);
    if (!(await verificarAccesoDocumento(req, res, idDoc))) return;
    const idDocDestinoBody = Number(req.body.idDocumentoDestino);
    if (!idDocDestinoBody) { sendError(res, 'idDocumentoDestino es requerido', 400); return; }
    const doc = await service.recepcionarDestino(
      idDoc, idDocDestinoBody, req.body.observaciones, user(req).idUsuario,
    );
    sendSuccess(res, doc, 'Destino recepcionado');
  } catch (e) { next(e); }
}

export async function terminarDestino(req: Request, res: Response, next: NextFunction): Promise<void> {
  try {
    const idDoc = Number(req.params.id);
    if (!(await verificarAccesoDocumento(req, res, idDoc))) return;
    const idDocDestinoBody = Number(req.body.idDocumentoDestino);
    if (!idDocDestinoBody) { sendError(res, 'idDocumentoDestino es requerido', 400); return; }
    const doc = await service.terminarDestino(
      idDoc, idDocDestinoBody, req.body.observaciones, user(req).idUsuario,
    );
    sendSuccess(res, doc, 'Destino cerrado');
  } catch (e) { next(e); }
}
