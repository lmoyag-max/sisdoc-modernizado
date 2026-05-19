import { Router } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendPaginated, buildPaginationMeta } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';

const router = Router();
router.use(requireAuth);

router.get('/', async (req, res, next) => {
  try {
    const user = (req as unknown as AuthenticatedRequest).user;
    const pagina = Number(req.query.pagina ?? 1);
    const porPagina = Number(req.query.porPagina ?? 20);
    const offset = (pagina - 1) * porPagina;
    const idEstado = req.query.idEstado ? Number(req.query.idEstado) : null;

    const pool = await getPool();
    const request = pool.request()
      .input('idUsuario', sql.Int, user.idUsuario)
      .input('offset', sql.Int, offset)
      .input('porPagina', sql.Int, porPagina);

    let estadoWhere = '';
    if (idEstado) {
      request.input('idEstado', sql.Int, idEstado);
      estadoWhere = ' AND t.id_estado_tramite = @idEstado';
    }

    const result = await request.query<{
      id_seguimiento: number; id_documento: number | null;
      materia: string | null; num_interno: string | null;
      desc_tipo_documento: string | null;
      desc_estado_tramite: string | null; id_estado_tramite: number | null;
      fecha_sistema: Date | null; observaciones: string | null; total: number;
    }>(`
      SELECT
        t.id_seguimiento, t.id_documento,
        d.materia, d.num_interno,
        td.desc_tipo_documento,
        et.id_estado_tramite, et.desc_estado_tramite,
        t.fecha_sistema, t.observaciones,
        COUNT(*) OVER() AS total
      FROM tramite t
      LEFT JOIN documento d ON t.id_documento = d.id_documento
      LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento
      LEFT JOIN estado_tramite et ON t.id_estado_tramite = et.id_estado_tramite
      WHERE (t.id_usuario = @idUsuario OR t.id_destino IN (
        SELECT id_dependencia FROM acceso WHERE id_usuario = @idUsuario
      ))${estadoWhere}
      ORDER BY t.fecha_sistema DESC
      OFFSET @offset ROWS FETCH NEXT @porPagina ROWS ONLY
    `);

    const total = result.recordset[0]?.total ?? 0;
    sendPaginated(res, result.recordset, buildPaginationMeta(total, pagina, porPagina));
  } catch (e) { next(e); }
});

router.patch('/:id/recibir', async (req, res, next) => {
  try {
    void (req as unknown as AuthenticatedRequest).user?.idUsuario;
    const pool = await getPool();
    await pool.request()
      .input('idTramite', sql.Int, Number(req.params.id))
      .query(`UPDATE tramite SET id_estado_tramite = 2, fecha_update = GETDATE() WHERE id_seguimiento = @idTramite`);
    sendSuccess(res, null, 'Trámite recibido');
  } catch (e) { next(e); }
});

router.patch('/:id/cerrar', async (req, res, next) => {
  try {
    const pool = await getPool();
    await pool.request()
      .input('idTramite', sql.Int, Number(req.params.id))
      .query(`UPDATE tramite SET id_estado_tramite = 3, fecha_update = GETDATE() WHERE id_seguimiento = @idTramite`);
    sendSuccess(res, null, 'Trámite cerrado');
  } catch (e) { next(e); }
});

export default router;
