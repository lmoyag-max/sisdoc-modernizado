import { Router, Request } from 'express';
import { z } from 'zod';
import { requireAuth, requireModule } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendError } from '../../shared/utils/response';
import { logAuditoria } from '../../shared/utils/auditoria';
import { AuthenticatedRequest } from '../../shared/types/api.types';

const router = Router();
router.use(requireAuth);

function getUser(req: Request) {
  return (req as unknown as AuthenticatedRequest).user;
}

function hasFullAccess(user: AuthenticatedRequest['user']): boolean {
  return user.roles.includes('admin') || user.todosServicios === true;
}

// ── GET /reportes/dashboard ───────────────────────────────────

router.get('/dashboard', requireModule('dashboard'), async (req, res, next) => {
  try {
    const user = getUser(req);
    const pool = await getPool();
    const full = hasFullAccess(user);
    const idDep = user.idDependencia;

    const docFilter = full
      ? ''
      : idDep
        ? `AND EXISTS (
            SELECT 1 FROM tramite t_f
            WHERE t_f.id_documento = d.id_documento
            AND (
              (t_f.id_destino     = @idDep AND t_f.tipo_destinatario = 'D')
              OR (t_f.id_procedencia = @idDep AND t_f.tipo_procedencia  = 'D')
            )
          )`
        : 'AND 1=0';

    const tramiteFilter = full
      ? ''
      : idDep
        ? `AND (
            (t.id_destino     = @idDep AND t.tipo_destinatario = 'D')
            OR (t.id_procedencia = @idDep AND t.tipo_procedencia  = 'D')
          )`
        : 'AND 1=0';

    // Crea un request con @idDep pre-registrado cuando aplica filtro por servicio
    const makeDepReq = () => {
      const r = pool.request();
      if (!full && idDep) r.input('idDep', sql.Int, idDep);
      return r;
    };

    const [totales, porEstado, porMes, porTipo] = await Promise.all([
      makeDepReq().query<{ total: number; pendientes: number; cerradosHoy: number; urgentes: number; tramites: number }>(`
        SELECT
          (SELECT COUNT(*) FROM documento d WHERE 1=1 ${docFilter}) AS total,
          (SELECT COUNT(*) FROM documento d WHERE id_estado_documento NOT IN (4,5) ${docFilter}) AS pendientes,
          (SELECT COUNT(*) FROM documento d WHERE CAST(d.fecha_sistema AS DATE) = CAST(GETDATE() AS DATE) ${docFilter}) AS cerradosHoy,
          0 AS urgentes,
          (SELECT COUNT(*) FROM tramite t WHERE 1=1 ${tramiteFilter}) AS tramites
      `),

      makeDepReq().query<{ id_estado_documento: number; desc_estado_documento: string; cantidad: number }>(`
        SELECT d.id_estado_documento, ed.desc_estado_documento, COUNT(*) AS cantidad
        FROM documento d
        LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
        WHERE 1=1 ${docFilter}
        GROUP BY d.id_estado_documento, ed.desc_estado_documento
        ORDER BY cantidad DESC
      `),

      makeDepReq().query<{ mes: string; cantidad: number }>(`
        SELECT FORMAT(d.fecha_sistema, 'yyyy-MM') AS mes, COUNT(*) AS cantidad
        FROM documento d
        WHERE d.fecha_sistema >= DATEADD(MONTH, -6, GETDATE()) ${docFilter}
        GROUP BY FORMAT(d.fecha_sistema, 'yyyy-MM')
        ORDER BY mes
      `),

      makeDepReq().query<{ desc_tipo_documento: string; cantidad: number }>(`
        SELECT TOP 8 td.desc_tipo_documento, COUNT(*) AS cantidad
        FROM documento d
        LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento
        WHERE 1=1 ${docFilter}
        GROUP BY td.desc_tipo_documento
        ORDER BY cantidad DESC
      `),
    ]);

    let extras = { expedientes: 0, archivos: 0, usuarios: 0 };
    if (full) {
      const extRes = await pool.request().query<{ expedientes: number; archivos: number; usuarios: number }>(`
        SELECT
          (SELECT COUNT(*) FROM expediente) AS expedientes,
          (SELECT COUNT(*) FROM archivo_digital) AS archivos,
          (SELECT COUNT(*) FROM usuario) AS usuarios
      `);
      extras = extRes.recordset[0] ?? extras;
    }

    sendSuccess(res, {
      totales: {
        ...(totales.recordset[0] ?? { total: 0, pendientes: 0, cerradosHoy: 0, urgentes: 0, tramites: 0 }),
        ...extras,
      },
      porEstado: porEstado.recordset,
      porMes:    porMes.recordset,
      porTipo:   porTipo.recordset,
      servicio:  full ? null : user.idDependencia,
    });
  } catch (e) { next(e); }
});

// ── GET /reportes/actividad-reciente ─────────────────────────

router.get('/actividad-reciente', requireModule('dashboard'), async (req, res, next) => {
  try {
    const user = getUser(req);
    const pool = await getPool();
    const full = hasFullAccess(user);
    const idDep = user.idDependencia;

    const tramiteFilter = full
      ? ''
      : idDep
        ? `AND (
            (t.id_destino     = @idDep AND t.tipo_destinatario = 'D')
            OR (t.id_procedencia = @idDep AND t.tipo_procedencia  = 'D')
          )`
        : 'AND 1=0';

    const request = pool.request();
    if (!full && idDep) request.input('idDep', sql.Int, idDep);

    const result = await request.query<{
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
      WHERE 1=1 ${tramiteFilter}
      ORDER BY t.fecha_sistema DESC
    `);

    const ACCION: Record<number, string> = { 1: 'INGRESADO', 2: 'DESPACHADO', 3: 'RECEPCIONADO', 4: 'DERIVADO', 5: 'CERRADO' };
    sendSuccess(res, result.recordset.map((r) => ({
      id_historial:   r.id_seguimiento,
      accion:         ACCION[r.id_estado_tramite ?? 0] ?? 'MOVIMIENTO',
      fecha:          r.fecha_sistema,
      asunto:         r.materia,
      num_documento:  r.num_interno ? String(r.num_interno) : null,
      usuario:        r.usuario,
      nombres_fun:    r.nombres,
    })));
  } catch (e) { next(e); }
});

// ── GET /reportes/exportar — CSV filtrado por servicio ───────

const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
const exportQuerySchema = z.object({
  fechaDesde: z.string().regex(dateRegex, 'Formato inválido, se espera YYYY-MM-DD').optional(),
  fechaHasta: z.string().regex(dateRegex, 'Formato inválido, se espera YYYY-MM-DD').optional(),
});

router.get('/exportar', requireModule('reportes'), async (req, res, next) => {
  try {
    const parsed = exportQuerySchema.safeParse(req.query);
    if (!parsed.success) {
      sendError(res, 'Parámetros de fecha inválidos', 400, parsed.error.flatten().fieldErrors);
      return;
    }
    const { fechaDesde, fechaHasta } = parsed.data;

    const user = getUser(req);
    const pool = await getPool();
    const full = hasFullAccess(user);
    const idDep = user.idDependencia;

    const docFilter = full
      ? ''
      : idDep
        ? `AND EXISTS (
            SELECT 1 FROM tramite t_f
            WHERE t_f.id_documento = d.id_documento
            AND (
              (t_f.id_destino     = @idDep AND t_f.tipo_destinatario = 'D')
              OR (t_f.id_procedencia = @idDep AND t_f.tipo_procedencia  = 'D')
            )
          )`
        : 'AND 1=0';

    const dateFilter = [
      fechaDesde ? 'AND d.fecha_sistema >= @fechaDesde' : '',
      fechaHasta ? 'AND d.fecha_sistema < DATEADD(DAY, 1, @fechaHasta)' : '',
    ].join(' ');

    const request = pool.request();
    if (!full && idDep) request.input('idDep', sql.Int, idDep);
    if (fechaDesde) request.input('fechaDesde', sql.Date, new Date(fechaDesde));
    if (fechaHasta) request.input('fechaHasta', sql.Date, new Date(fechaHasta));

    const result = await request.query<{
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
      WHERE 1=1 ${docFilter} ${dateFilter}
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
    await logAuditoria(pool, {
      idUsuario: user.idUsuario,
      accion: 'EXPORTAR_CSV',
      detalle: `registros: ${result.recordset.length}${fechaDesde ? ` desde ${fechaDesde}` : ''}${fechaHasta ? ` hasta ${fechaHasta}` : ''}`,
      ip: (req as import('express').Request).ip ?? null,
    });
    res.setHeader('Content-Type', 'text/csv; charset=utf-8');
    res.setHeader('Content-Disposition', `attachment; filename="documentos_${Date.now()}.csv"`);
    res.send('﻿' + csv);
  } catch (e) { next(e); }
});

export default router;
