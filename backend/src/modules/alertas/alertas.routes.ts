import { Router, Request, Response, NextFunction } from 'express';
import { z } from 'zod';
import { requireAuth, requireRole } from '../../middleware/auth.middleware';
import { sendSuccess, sendError } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import * as service from './alertas.service';

const router = Router();
router.use(requireAuth);
router.use(requireRole('admin'));

function uid(req: Request): number {
  return (req as unknown as AuthenticatedRequest).user.idUsuario;
}

// ── GET /alertas/configuracion ───────────────────────────────
router.get('/configuracion', async (_req, res, next) => {
  try { sendSuccess(res, await service.getConfiguracion()); }
  catch (e) { next(e); }
});

// ── PUT /alertas/configuracion ───────────────────────────────
const configSchema = z.object({
  activo:   z.boolean(),
  horarios: z.array(z.string().regex(/^\d{2}:\d{2}$/, 'Formato HH:MM requerido')).min(1).max(4),
});

router.put('/configuracion', async (req, res, next) => {
  try {
    const parsed = configSchema.safeParse(req.body);
    if (!parsed.success) { sendError(res, 'Datos inválidos', 400, parsed.error.flatten().fieldErrors); return; }
    sendSuccess(res, await service.updateConfiguracion(parsed.data.activo, parsed.data.horarios), 'Configuración actualizada');
  } catch (e) { next(e); }
});

// ── GET /alertas/pendientes ───────────────────────────────────
router.get('/pendientes', async (req, res, next) => {
  try {
    const idDep = req.query.idDependencia ? Number(req.query.idDependencia) : undefined;
    sendSuccess(res, await service.getDocumentosPendientes(idDep));
  } catch (e) { next(e); }
});

// ── GET /alertas/destinatarios ────────────────────────────────
// Retorna resumen de destinatarios dinámicos (usuarios con email por servicio)
router.get('/destinatarios', async (req, res, next) => {
  try {
    const idDep = req.query.idDependencia ? Number(req.query.idDependencia) : undefined;
    sendSuccess(res, await service.getResumenDestinatarios(idDep));
  } catch (e) { next(e); }
});

// ── GET /alertas/logs ─────────────────────────────────────────
router.get('/logs', async (req, res, next) => {
  try {
    const pagina    = Math.max(1, Number(req.query.pagina    ?? 1));
    const porPagina = Math.min(100, Math.max(1, Number(req.query.porPagina ?? 30)));
    const result    = await service.getLogs(pagina, porPagina);
    sendSuccess(res, result.data);
  } catch (e) { next(e); }
});

// ── POST /alertas/enviar-manual ───────────────────────────────
// Envía alerta al servicio indicado usando usuarios reales del sistema
const enviarManualSchema = z.object({
  idDependencia: z.number().int().positive(),
});

router.post('/enviar-manual', async (req, res, next) => {
  try {
    const parsed = enviarManualSchema.safeParse(req.body);
    if (!parsed.success) { sendError(res, 'Datos inválidos', 400, parsed.error.flatten().fieldErrors); return; }
    const result = await service.enviarAlertaServicio(parsed.data.idDependencia, 'manual', uid(req));
    sendSuccess(res, result, result.mensaje);
  } catch (e) { next(e); }
});

// ── POST /alertas/enviar-todos ────────────────────────────────
// Envía alertas a todos los servicios con documentos pendientes
router.post('/enviar-todos', async (_req, res, next) => {
  try {
    const result = await service.enviarTodasLasAlertas('manual');
    sendSuccess(res, result, `Proceso completado: ${result.enviados} enviados, ${result.errores} errores`);
  } catch (e) { next(e); }
});

// ── POST /alertas/probar-servicio/:id ────────────────────────
// Envío de prueba inmediato para un servicio (muestra resultado detallado)
router.post('/probar-servicio/:id', async (req, res, next) => {
  try {
    const idDependencia = Number(req.params.id);
    if (!idDependencia || isNaN(idDependencia)) { sendError(res, 'ID de dependencia inválido', 400); return; }
    const [result, destinatarios] = await Promise.all([
      service.enviarAlertaServicio(idDependencia, 'manual', uid(req)),
      service.getDestinatariosServicio(idDependencia),
    ]);
    sendSuccess(res, {
      ...result,
      detalle: destinatarios.map((d) => ({ nombre: d.nombreCompleto, email: d.email })),
    }, result.mensaje);
  } catch (e) { next(e); }
});

export default router;
