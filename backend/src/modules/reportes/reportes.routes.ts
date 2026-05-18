import { Router } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
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
          SUM(CASE WHEN CAST(fecha_cierre AS DATE) = CAST(GETDATE() AS DATE) THEN 1 ELSE 0 END) AS cerradosHoy,
          SUM(CASE WHEN id_prioridad = (SELECT MAX(id_prioridad) FROM prioridad) THEN 1 ELSE 0 END) AS urgentes
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
        SELECT FORMAT(fecha_ingreso, 'yyyy-MM') AS mes, COUNT(*) AS cantidad
        FROM documento
        WHERE fecha_ingreso >= DATEADD(MONTH, -6, GETDATE())
        GROUP BY FORMAT(fecha_ingreso, 'yyyy-MM')
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
      id_historial: number; accion: string | null; fecha: Date;
      asunto: string | null; num_documento: string | null;
      usuario: string | null; nombres_fun: string | null;
    }>(`
      SELECT TOP 10
        h.id_historial, h.accion, h.fecha,
        d.asunto, d.num_documento,
        u.usuario, f.nombres_fun
      FROM historial_documento h
      JOIN documento d ON h.id_documento = d.id_documento
      LEFT JOIN usuario u ON h.id_usuario = u.id_usuario
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      ORDER BY h.fecha DESC
    `);
    sendSuccess(res, result.recordset);
  } catch (e) { next(e); }
});

export default router;
