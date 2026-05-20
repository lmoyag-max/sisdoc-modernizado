import { Router } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import * as ctrl from './catalogos.controller';

const router = Router();
router.use(requireAuth);

router.get('/tipos-documento',       ctrl.tiposDocumento);
router.get('/estados',               ctrl.estados);
router.get('/estados-tramite',       ctrl.estadosTramite);
router.get('/prioridades',           ctrl.prioridades);
router.get('/dependencias',          ctrl.dependencias);
router.get('/dependencias-externas', ctrl.dependenciasExternas);
router.get('/funcionarios',          ctrl.todosFuncionarios);
router.get('/dependencias/:idDep/funcionarios', ctrl.funcionariosPorDependencia);
router.get('/tipos-distribucion',    ctrl.tiposDistribucion);
router.get('/tipos-compromiso',      ctrl.tiposCompromiso);
router.get('/estados-compromiso',    ctrl.estadosCompromiso);
router.get('/descriptores',          ctrl.descriptores);

export default router;
