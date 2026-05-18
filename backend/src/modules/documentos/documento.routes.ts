import { Router } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { validate } from '../../middleware/validate.middleware';
import { crearDocumentoSchema, derivarDocumentoSchema, filtrosDocumentoSchema } from './documento.schema';
import * as ctrl from './documento.controller';

const router = Router();

router.use(requireAuth);

router.get('/', validate(filtrosDocumentoSchema, 'query'), ctrl.listar);
router.post('/', validate(crearDocumentoSchema), ctrl.crear);
router.get('/:id', ctrl.obtener);
router.get('/:id/historial', ctrl.historial);
router.post('/:id/derivar', validate(derivarDocumentoSchema), ctrl.derivar);

export default router;
