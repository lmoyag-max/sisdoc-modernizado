import { Router, Request, Response, NextFunction } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendCreated, sendError, sendPaginated, buildPaginationMeta } from '../../shared/utils/response';

const router = Router();
router.use(requireAuth);

// ── GET /expedientes ────────────────────────────────────────
router.get('/', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pagina = Math.max(1, Number(req.query.pagina ?? 1));
    const porPagina = Math.min(100, Number(req.query.porPagina ?? 20));
    const offset = (pagina - 1) * porPagina;
    const q = String(req.query.q ?? '').trim();

    const pool = await getPool();
    const request = pool.request().input('offset', sql.Int, offset).input('n', sql.Int, porPagina);
    let where = '1=1';
    if (q) {
      request.input('q', sql.VarChar(200), `%${q}%`);
      where += ' AND (e.desc_expediente LIKE @q OR CAST(e.id_expediente AS VARCHAR) LIKE @q)';
    }

    const result = await request.query<{
      id_expediente: number;
      desc_expediente: string | null;
      fecha_expediente: Date | null;
      tipo_expediente: number | null;
      total_documentos: number;
      total: number;
    }>(`
      SELECT e.id_expediente, e.desc_expediente, e.fecha_expediente, e.tipo_expediente,
             (SELECT COUNT(*) FROM documento d WHERE d.id_expediente = e.id_expediente) AS total_documentos,
             COUNT(*) OVER() AS total
      FROM expediente e
      WHERE ${where}
      ORDER BY e.fecha_expediente DESC
      OFFSET @offset ROWS FETCH NEXT @n ROWS ONLY
    `);

    const total = result.recordset[0]?.total ?? 0;
    const data = result.recordset.map((r) => ({
      id_expediente: r.id_expediente,
      descripcion: (r.desc_expediente ?? '').trim() || `Expediente #${r.id_expediente}`,
      fecha_sistema: r.fecha_expediente,
      tipo_expediente: r.tipo_expediente,
      total_documentos: r.total_documentos,
    }));

    sendPaginated(res, data, buildPaginationMeta(total, pagina, porPagina));
  } catch (e) { next(e); }
});

// ── POST /expedientes ───────────────────────────────────────
router.post('/', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { descripcion } = req.body as { descripcion: string };
    if (!descripcion?.trim()) { sendError(res, 'La descripción es requerida', 400); return; }

    // desc_expediente es char(100) NOT NULL — truncar a 100
    const descCorta = descripcion.trim().substring(0, 100);

    const pool = await getPool();
    const result = await pool.request()
      .input('desc', sql.Char(100), descCorta)
      .query<{ id: number }>(`
        INSERT INTO expediente (desc_expediente, fecha_expediente)
        OUTPUT INSERTED.id_expediente AS id
        VALUES (@desc, GETDATE())
      `);

    sendCreated(res, {
      idExpediente: result.recordset[0].id,
      descripcion: descCorta,
    }, 'Expediente creado correctamente');
  } catch (e) { next(e); }
});

// ── GET /expedientes/:id/documentos ────────────────────────
router.get('/:id/documentos', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const result = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<{
        id_documento: number;
        materia: string | null;
        num_interno: number | null;
        desc_tipo_documento: string | null;
        desc_estado_documento: string | null;
        fecha_sistema: Date | null;
      }>(`
        SELECT d.id_documento, d.materia, d.num_interno,
               td.desc_tipo_documento, ed.desc_estado_documento, d.fecha_sistema
        FROM documento d
        LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento
        LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
        WHERE d.id_expediente = @id
        ORDER BY d.fecha_sistema DESC
      `);

    sendSuccess(res, result.recordset);
  } catch (e) { next(e); }
});

// ── PATCH /expedientes/:docId/vincular/:expId ───────────────
router.patch('/vincular', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { idDocumento, idExpediente } = req.body as { idDocumento: number; idExpediente: number };
    if (!idDocumento || !idExpediente) { sendError(res, 'idDocumento e idExpediente son requeridos', 400); return; }

    const pool = await getPool();
    await pool.request()
      .input('idDoc', sql.Int, idDocumento)
      .input('idExp', sql.Int, idExpediente)
      .query('UPDATE documento SET id_expediente = @idExp WHERE id_documento = @idDoc');

    sendSuccess(res, null, 'Documento vinculado al expediente');
  } catch (e) { next(e); }
});

export default router;
