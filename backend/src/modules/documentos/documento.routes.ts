import { Router } from 'express';
import { requireAuth, requireRole } from '../../middleware/auth.middleware';
import { validate } from '../../middleware/validate.middleware';
import {
  crearDocumentoSchema, despacharSchema, recepcionarSchema,
  derivarSchema, terminarSchema, filtrosDocumentoSchema,
} from './documento.schema';
import * as ctrl from './documento.controller';

const router = Router();
router.use(requireAuth);

// Lectura — todos los roles autenticados
router.get('/',                  validate(filtrosDocumentoSchema, 'query'), ctrl.listar);
router.get('/:id',               ctrl.obtener);
router.get('/:id/historial',     ctrl.historial);
router.get('/:id/trazabilidad',  ctrl.trazabilidad);

// Crear — cualquier usuario autenticado
router.post('/', validate(crearDocumentoSchema), ctrl.crear);

// Flujo documental — coordinador y admin
router.post('/:id/despachar',   requireRole('admin', 'coordinador'), validate(despacharSchema),   ctrl.despachar);
router.post('/:id/recepcionar', requireRole('admin', 'coordinador', 'funcionario'), validate(recepcionarSchema), ctrl.recepcionar);
router.post('/:id/derivar',     requireRole('admin', 'coordinador'), validate(derivarSchema),     ctrl.derivar);
router.post('/:id/terminar',    requireRole('admin', 'coordinador'), validate(terminarSchema),     ctrl.terminar);

// Eliminar — solo admin y coordinador
router.delete('/:id', requireRole('admin', 'coordinador'), ctrl.eliminar);

export default router;
