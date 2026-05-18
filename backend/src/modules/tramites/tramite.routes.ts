import { Router } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendPaginated, buildPaginationMeta } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';

const router = Router();
router.use(requireAuth);

router.get('/', async (req, res, next) => {
  try {
    const user = (req as AuthenticatedRequest).user;
    const pagina = Number(req.query.pagina ?? 1);
    const porPagina = Number(req.query.porPagina ?? 20);
    const offset = (pagina - 1) * porPagina;

    const pool = await getPool();
    const result = await pool
      .request()
      .input('idUsuario', sql.Int, user.idUsuario)
      .input('offset', sql.Int, offset)
      .input('porPagina', sql.Int, porPagina)
      .query<{
        id_tramite: number; id_documento: number; asunto: string | null;
        num_documento: string | null; desc_tipo_documento: string | null;
        desc_estado_tramite: string | null; id_estado_tramite: number | null;
        desc_procedencia: string | null; fecha_derivacion: Date | null;
        fecha_cierre: Date | null; observacion: string | null; total: number;
      }>(`
        SELECT
          t.id_tramite, t.id_documento, d.asunto, d.num_documento,
          td.desc_tipo_documento,
          et.id_estado_tramite, et.desc_estado_tramite,
          dep_orig.desc_dependencia AS desc_procedencia,
          t.fecha_derivacion, t.fecha_cierre, t.observacion,
          COUNT(*) OVER() AS total
        FROM tramite t
        JOIN documento d ON t.id_documento = d.id_documento
        LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento
        LEFT JOIN estado_tramite et ON t.id_estado_tramite = et.id_estado_tramite
        LEFT JOIN dependencia dep_orig ON d.id_procedencia = dep_orig.id_dependencia
        WHERE t.id_usuario_origen = @idUsuario
           OR t.id_dependencia_destino IN (
              SELECT id_dependencia FROM acceso WHERE id_usuario = @idUsuario
           )
        ORDER BY t.fecha_derivacion DESC
        OFFSET @offset ROWS FETCH NEXT @porPagina ROWS ONLY
      `);

    const total = result.recordset[0]?.total ?? 0;
    sendPaginated(res, result.recordset, buildPaginationMeta(total, pagina, porPagina));
  } catch (e) { next(e); }
});

router.patch('/:id/recibir', async (req, res, next) => {
  try {
    const { idUsuario } = (req as AuthenticatedRequest).user;
    const pool = await getPool();
    await pool.request()
      .input('idTramite', sql.Int, Number(req.params.id))
      .query(`UPDATE tramite SET id_estado_tramite = 2 WHERE id_tramite = @idTramite`);
    sendSuccess(res, null, 'Trámite recibido');
  } catch (e) { next(e); }
});

router.patch('/:id/cerrar', async (req, res, next) => {
  try {
    const pool = await getPool();
    await pool.request()
      .input('idTramite', sql.Int, Number(req.params.id))
      .query(`UPDATE tramite SET id_estado_tramite = 3, fecha_cierre = GETDATE() WHERE id_tramite = @idTramite`);
    sendSuccess(res, null, 'Trámite cerrado');
  } catch (e) { next(e); }
});

export default router;
