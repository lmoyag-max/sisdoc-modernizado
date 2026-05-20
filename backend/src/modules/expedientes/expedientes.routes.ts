import { Router, Request, Response, NextFunction } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendCreated, sendError, sendPaginated, buildPaginationMeta } from '../../shared/utils/response';

const router = Router();
router.use(requireAuth);

// Verificar si existe tabla expediente
async function expedienteTableExists(): Promise<boolean> {
  const pool = await getPool();
  const r = await pool.request().query(
    "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'expediente'"
  );
  return r.recordset.length > 0;
}

// ── GET /expedientes ────────────────────────────────────────
router.get('/', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const exists = await expedienteTableExists();
    if (!exists) {
      sendPaginated(res, [], buildPaginationMeta(0, 1, 20));
      return;
    }

    const pagina = Math.max(1, Number(req.query.pagina ?? 1));
    const porPagina = Math.min(100, Number(req.query.porPagina ?? 20));
    const offset = (pagina - 1) * porPagina;
    const q = String(req.query.q ?? '').trim();

    const pool = await getPool();
    const request = pool.request().input('offset', sql.Int, offset).input('n', sql.Int, porPagina);
    let where = '1=1';
    if (q) {
      request.input('q', sql.VarChar(200), `%${q}%`);
      where += ' AND (e.descripcion LIKE @q OR CAST(e.id_expediente AS VARCHAR) LIKE @q)';
    }

    const result = await request.query<{
      id_expediente: number; descripcion: string | null;
      fecha_sistema: Date | null; total_documentos: number; total: number;
    }>(`
      SELECT e.id_expediente, e.descripcion, e.fecha_sistema,
             (SELECT COUNT(*) FROM documento d WHERE d.id_expediente = e.id_expediente) AS total_documentos,
             COUNT(*) OVER() AS total
      FROM expediente e
      WHERE ${where}
      ORDER BY e.fecha_sistema DESC
      OFFSET @offset ROWS FETCH NEXT @n ROWS ONLY
    `);

    const total = result.recordset[0]?.total ?? 0;
    sendPaginated(res, result.recordset, buildPaginationMeta(total, pagina, porPagina));
  } catch (e) { next(e); }
});

// ── POST /expedientes ───────────────────────────────────────
router.post('/', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { descripcion } = req.body as { descripcion: string };
    if (!descripcion) { sendError(res, 'La descripción es requerida', 400); return; }

    const exists = await expedienteTableExists();
    if (!exists) {
      sendError(res, 'La tabla de expedientes no existe en esta base de datos', 503); return;
    }

    const pool = await getPool();
    const result = await pool.request()
      .input('descripcion', sql.VarChar(500), descripcion)
      .query<{ id: number }>(`
        INSERT INTO expediente (descripcion, fecha_sistema)
        OUTPUT INSERTED.id_expediente AS id
        VALUES (@descripcion, GETDATE())
      `);

    sendCreated(res, { idExpediente: result.recordset[0].id, descripcion });
  } catch (e) { next(e); }
});

// ── GET /expedientes/:id/documentos ────────────────────────
router.get('/:id/documentos', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const result = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<{
        id_documento: number; materia: string | null;
        num_interno: number | null; desc_tipo_documento: string | null;
        desc_estado_documento: string | null; fecha_sistema: Date | null;
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

export default router;
