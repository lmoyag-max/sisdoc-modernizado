import { Router } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool } from '../../config/database';
import { sendSuccess } from '../../shared/utils/response';

const router = Router();
router.use(requireAuth);

router.get('/dashboard', async (_req, res, next) => {
  try {
    const pool = await getPool();
    const [totales, porEstado, porMes] = await Promise.all([
      pool.request().query<{ total: number; pendientes: number; cerradosHoy: number; urgentes: number }>(`
        SELECT
          COUNT(*) AS total,
          SUM(CASE WHEN id_estado_documento IN (1,2,3,4) THEN 1 ELSE 0 END) AS pendientes,
          SUM(CASE WHEN CAST(fecha_sistema AS DATE) = CAST(GETDATE() AS DATE) THEN 1 ELSE 0 END) AS cerradosHoy,
          0 AS urgentes
        FROM documento
      `),
      pool.request().query<{ id_estado_documento: number; desc_estado_documento: string; cantidad: number }>(`
        SELECT d.id_estado_documento, ed.desc_estado_documento,
               COUNT(*) AS cantidad
        FROM documento d
        LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
        GROUP BY d.id_estado_documento, ed.desc_estado_documento
        ORDER BY d.id_estado_documento
      `),
      pool.request().query<{ mes: string; cantidad: number }>(`
        SELECT FORMAT(fecha_sistema, 'yyyy-MM') AS mes, COUNT(*) AS cantidad
        FROM documento
        WHERE fecha_sistema >= DATEADD(MONTH, -6, GETDATE())
        GROUP BY FORMAT(fecha_sistema, 'yyyy-MM')
        ORDER BY mes
      `),
    ]);

    sendSuccess(res, {
      totales: totales.recordset[0] ?? { total: 0, pendientes: 0, cerradosHoy: 0, urgentes: 0 },
      porEstado: porEstado.recordset,
      porMes: porMes.recordset,
    });
  } catch (e) { next(e); }
});

router.get('/actividad-reciente', async (_req, res, next) => {
  try {
    const pool = await getPool();
    const result = await pool.request().query<{
      id_seguimiento: number;
      id_estado_tramite: number | null;
      fecha_sistema: Date | null;
      materia: string | null;
      num_interno: string | null;
      usuario: string | null;
      nombres: string | null;
    }>(`
      SELECT TOP 10
        t.id_seguimiento, t.id_estado_tramite, t.fecha_sistema,
        d.materia, d.num_interno,
        u.usuario, f.nombres
      FROM tramite t
      JOIN documento d ON t.id_documento = d.id_documento
      LEFT JOIN usuario u ON t.id_usuario = u.id_usuario
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      ORDER BY t.fecha_sistema DESC
    `);

    // Mapear al formato que espera el frontend
    const mapped = result.recordset.map(r => ({
      id_historial: r.id_seguimiento,
      accion: r.id_estado_tramite === 1 ? 'DERIVADO' : 'RECEPCIONADO',
      fecha: r.fecha_sistema,
      asunto: r.materia,
      num_documento: r.num_interno,
      usuario: r.usuario,
      nombres_fun: r.nombres,
    }));

    sendSuccess(res, mapped);
  } catch (e) { next(e); }
});

export default router;
