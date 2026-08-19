import { Router } from 'express';
import { requireAuth, requireRole, requireModule } from '../../middleware/auth.middleware';
import { validate } from '../../middleware/validate.middleware';
import {
  crearReferenciaSchema, editarReferenciaSchema, eliminarReferenciaSchema, eliminarDefinitivoSchema,
  filtrosReferenciaSchema,
} from './libro-referencias.schema';
import * as ctrl from './libro-referencias.controller';

const router = Router();
router.use(requireAuth);
// Acceso al módulo completo (ver/crear/editar/consultar) — admin siempre pasa,
// el resto de los roles requiere que el Superadministrador les asigne el
// módulo 'libro-referencias' desde /admin/roles (mismo mecanismo que el resto
// de DOC360, ver requireModule en auth.middleware.ts).
router.use(requireModule('libro-referencias'));

// ── Lectura ──────────────────────────────────────────────────
router.get('/',          validate(filtrosReferenciaSchema, 'query'), ctrl.listar);
router.get('/metricas',  ctrl.metricas);
// Eliminados: exclusivo Superadministrador — debe ir antes de '/:id' para no colisionar.
router.get('/eliminados', requireRole('admin'), validate(filtrosReferenciaSchema, 'query'), ctrl.listarEliminados);
router.get('/:id',       ctrl.obtener);

// ── Escritura ────────────────────────────────────────────────
router.post('/',       validate(crearReferenciaSchema),  ctrl.crear);
router.patch('/:id',   validate(editarReferenciaSchema), ctrl.actualizar);

// Eliminación lógica (nivel 1): exclusiva Superadministrador. No basta con
// ocultar el botón en el frontend — se valida el rol explícitamente en el servidor.
router.delete('/:id',  requireRole('admin'), validate(eliminarReferenciaSchema), ctrl.eliminar);

// Eliminación definitiva (nivel 2): endpoint separado a propósito, para no
// generar ambigüedad con la eliminación lógica. Exclusiva Superadministrador
// y solo aplica sobre un registro que YA está en condición ELIMINADO — esa
// verificación se hace en el servicio (404/409), no basta con requireRole.
router.delete('/:id/permanent', requireRole('admin'), validate(eliminarDefinitivoSchema), ctrl.eliminarDefinitivo);

export default router;
