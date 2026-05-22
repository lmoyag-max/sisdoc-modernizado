import { Router } from 'express';
import { requireAuth, requireRole } from '../../middleware/auth.middleware';
import { validate } from '../../middleware/validate.middleware';
import {
  crearDocumentoSchema, despacharSchema, recepcionarSchema,
  derivarSchema, terminarSchema, reabrirSchema, filtrosDocumentoSchema,
} from './documento.schema';
import * as ctrl from './documento.controller';

const router = Router();
router.use(requireAuth);

// Lectura — todos los roles autenticados
router.get('/',                  validate(filtrosDocumentoSchema, 'query'), ctrl.listar);
// Búsqueda exacta por número — debe estar ANTES de /:id para evitar colisión de rutas
router.get('/buscar-por-numero', ctrl.buscarPorNumero);
router.get('/:id',               ctrl.obtener);
router.get('/:id/historial',     ctrl.historial);
router.get('/:id/trazabilidad',  ctrl.trazabilidad);

// Crear — cualquier usuario autenticado
router.post('/', validate(crearDocumentoSchema), ctrl.crear);

// Flujo documental
// despachar/redespachar: todos los roles pueden redirigir un doc que llegó a su servicio
router.post('/:id/despachar',   requireRole('admin', 'of.partes', 'supervisores', 'funcionario'), validate(despacharSchema),   ctrl.despachar);
router.post('/:id/recepcionar', requireRole('admin', 'of.partes', 'supervisores', 'funcionario'), validate(recepcionarSchema), ctrl.recepcionar);
router.post('/:id/derivar',     requireRole('admin', 'of.partes', 'supervisores'), validate(derivarSchema),     ctrl.derivar);
// Terminar: todos los roles pueden cerrar un doc en estado Recepcionado (3).
// El servicio valida que el estado sea 3 antes de ejecutar.
router.post('/:id/terminar',    requireRole('admin', 'of.partes', 'supervisores', 'funcionario'), validate(terminarSchema), ctrl.terminar);
// Reabrir: admin y supervisores (of.partes NO puede reabrir)
router.post('/:id/reabrir',     requireRole('admin', 'supervisores'),              validate(reabrirSchema),      ctrl.reabrir);

// Eliminar — solo admin
router.delete('/:id', requireRole('admin'), ctrl.eliminar);

export default router;
