import { Router } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool } from '../../config/database';
import { sendSuccess } from '../../shared/utils/response';

const router = Router();
router.use(requireAuth);

router.get('/dashboard', async (_req, res, next) => {
  try {
    const pool = await getPool();
    const [totales, extras, porEstado, porMes, porTipo] = await Promise.all([
      // Totales documentos
      pool.request().query<{ total: number; pendientes: number; cerradosHoy: number; urgentes: number }>(`
        SELECT
          COUNT(*) AS total,
          SUM(CASE WHEN id_estado_documento NOT IN (5) THEN 1 ELSE 0 END) AS pendientes,
          SUM(CASE WHEN CAST(fecha_sistema AS DATE) = CAST(GETDATE() AS DATE) THEN 1 ELSE 0 END) AS cerradosHoy,
          0 AS urgentes
        FROM documento
      `),
      // Extras: expedientes, archivos, usuarios, tramites
      pool.request().query<{ expedientes: number; archivos: number; usuarios: number; tramites: number }>(`
        SELECT
          (SELECT COUNT(*) FROM expediente) AS expedientes,
          (SELECT COUNT(*) FROM archivo_digital) AS archivos,
          (SELECT COUNT(*) FROM usuario) AS usuarios,
          (SELECT COUNT(*) FROM tramite) AS tramites
      `),
      // Por estado
      pool.request().query<{ id_estado_documento: number; desc_estado_documento: string; cantidad: number }>(`
        SELECT d.id_estado_documento, ed.desc_estado_documento, COUNT(*) AS cantidad
        FROM documento d
        LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
        GROUP BY d.id_estado_documento, ed.desc_estado_documento
        ORDER BY cantidad DESC
      `),
      // Por mes (últimos 6 meses)
      pool.request().query<{ mes: string; cantidad: number }>(`
        SELECT FORMAT(fecha_sistema, 'yyyy-MM') AS mes, COUNT(*) AS cantidad
        FROM documento
        WHERE fecha_sistema >= DATEADD(MONTH, -6, GETDATE())
        GROUP BY FORMAT(fecha_sistema, 'yyyy-MM')
        ORDER BY mes
      `),
      // Por tipo (top 8)
      pool.request().query<{ desc_tipo_documento: string; cantidad: number }>(`
        SELECT TOP 8 td.desc_tipo_documento, COUNT(*) AS cantidad
        FROM documento d
        LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento
        GROUP BY td.desc_tipo_documento
        ORDER BY cantidad DESC
      `),
    ]);

    sendSuccess(res, {
      totales: {
        ...(totales.recordset[0] ?? { total: 0, pendientes: 0, cerradosHoy: 0, urgentes: 0 }),
        ...(extras.recordset[0] ?? { expedientes: 0, archivos: 0, usuarios: 0, tramites: 0 }),
      },
      porEstado: porEstado.recordset,
      porMes: porMes.recordset,
      porTipo: porTipo.recordset,
    });
  } catch (e) { next(e); }
});

router.get('/actividad-reciente', async (_req, res, next) => {
  try {
    const pool = await getPool();
    // Actividad desde tramites (historial real)
    const result = await pool.request().query<{
      id_seguimiento: number; id_estado_tramite: number | null;
      fecha_sistema: Date | null; materia: string | null;
      num_interno: number | null; usuario: string | null; nombres: string | null;
    }>(`
      SELECT TOP 15
        t.id_seguimiento, t.id_estado_tramite, t.fecha_sistema,
        d.materia, d.num_interno,
        u.usuario, f.nombres
      FROM tramite t
      JOIN documento d ON t.id_documento = d.id_documento
      LEFT JOIN usuario u ON t.id_usuario = u.id_usuario
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      ORDER BY t.fecha_sistema DESC
    `);

    const ACCION: Record<number, string> = { 1: 'DERIVADO', 2: 'RECEPCIONADO', 3: 'CERRADO' };
    sendSuccess(res, result.recordset.map((r) => ({
      id_historial: r.id_seguimiento,
      accion: ACCION[r.id_estado_tramite ?? 0] ?? 'MOVIMIENTO',
      fecha: r.fecha_sistema,
      asunto: r.materia,
      num_documento: r.num_interno ? String(r.num_interno) : null,
      usuario: r.usuario,
      nombres_fun: r.nombres,
    })));
  } catch (e) { next(e); }
});

// GET /reportes/exportar — exportar documentos a CSV
router.get('/exportar', async (_req, res, next) => {
  try {
    const pool = await getPool();
    const result = await pool.request().query<{
      id_documento: number; materia: string | null;
      num_interno: number | null; num_oficial: number | null;
      desc_tipo_documento: string | null; desc_estado_documento: string | null;
      usuario: string | null; fecha_sistema: Date | null;
    }>(`
      SELECT d.id_documento, d.materia, d.num_interno, d.num_oficial,
             td.desc_tipo_documento, ed.desc_estado_documento,
             u.usuario, d.fecha_sistema
      FROM documento d
      LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento
      LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
      LEFT JOIN usuario u ON d.id_usuario = u.id_usuario
      ORDER BY d.fecha_sistema DESC
    `);

    const headers = ['ID','Materia','N° Interno','N° Oficial','Tipo','Estado','Usuario','Fecha'];
    const rows = result.recordset.map((r) => [
      r.id_documento,
      `"${(r.materia ?? '').replace(/"/g, '""')}"`,
      r.num_interno ?? '',
      r.num_oficial ?? '',
      `"${(r.desc_tipo_documento ?? '').replace(/"/g, '""')}"`,
      `"${(r.desc_estado_documento ?? '').replace(/"/g, '""')}"`,
      r.usuario ?? '',
      r.fecha_sistema ? new Date(r.fecha_sistema).toLocaleDateString('es-CL') : '',
    ].join(','));

    const csv = [headers.join(','), ...rows].join('\n');
    res.setHeader('Content-Type', 'text/csv; charset=utf-8');
    res.setHeader('Content-Disposition', `attachment; filename="documentos_${Date.now()}.csv"`);
    res.send('﻿' + csv); // BOM para Excel
  } catch (e) { next(e); }
});

export default router;
