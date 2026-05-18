import { Router } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import * as ctrl from './catalogos.controller';

const router = Router();
router.use(requireAuth);

router.get('/tipos-documento', ctrl.tiposDocumento);
router.get('/estados', ctrl.estados);
router.get('/prioridades', ctrl.prioridades);
router.get('/dependencias', ctrl.dependencias);
router.get('/dependencias-externas', ctrl.dependenciasExternas);
router.get('/descriptores', ctrl.descriptores);
router.get('/dependencias/:idDep/funcionarios', ctrl.funcionariosPorDependencia);

export default router;
