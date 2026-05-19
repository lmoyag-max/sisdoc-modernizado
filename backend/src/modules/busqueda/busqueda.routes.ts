import { Router, Request, Response, NextFunction } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess } from '../../shared/utils/response';

const router = Router();
router.use(requireAuth);

// GET /api/v1/busqueda?q=texto&tipo=documentos|tramites|funcionarios
router.get('/', async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    const q = String(req.query.q ?? '').trim();
    const tipo = String(req.query.tipo ?? 'documentos');
    const pagina = Math.max(1, Number(req.query.pagina ?? 1));
    const porPagina = Math.min(50, Math.max(1, Number(req.query.porPagina ?? 20)));
    const offset = (pagina - 1) * porPagina;

    if (!q || q.length < 2) {
      sendSuccess(res, { documentos: [], tramites: [], funcionarios: [], total: 0 });
      return;
    }

    const pool = await getPool();
    const like = `%${q}%`;

    const [docs, trams, funcs] = await Promise.all([
      // Documentos
      tipo === 'documentos' || tipo === 'todos'
        ? pool.request()
            .input('q', sql.NVarChar(200), like)
            .input('offset', sql.Int, offset)
            .input('n', sql.Int, porPagina)
            .query<{
              id_documento: number; materia: string | null;
              num_interno: string | null; desc_tipo_documento: string | null;
              desc_estado_documento: string | null; fecha_sistema: Date | null; total: number;
            }>(`
              SELECT d.id_documento, d.materia, d.num_interno,
                     td.desc_tipo_documento, ed.desc_estado_documento,
                     d.fecha_sistema,
                     COUNT(*) OVER() AS total
              FROM documento d
              LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento
              LEFT JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
              WHERE d.materia LIKE @q OR d.num_interno LIKE @q OR d.num_oficial LIKE @q
              ORDER BY d.fecha_sistema DESC
              OFFSET @offset ROWS FETCH NEXT @n ROWS ONLY
            `)
        : Promise.resolve({ recordset: [] as never[] }),

      // Trámites
      tipo === 'tramites' || tipo === 'todos'
        ? pool.request()
            .input('q', sql.NVarChar(200), like)
            .input('offset', sql.Int, offset)
            .input('n', sql.Int, porPagina)
            .query<{
              id_seguimiento: number; id_documento: number | null;
              materia: string | null; observaciones: string | null;
              fecha_sistema: Date | null; total: number;
            }>(`
              SELECT t.id_seguimiento, t.id_documento, d.materia, t.observaciones,
                     t.fecha_sistema, COUNT(*) OVER() AS total
              FROM tramite t
              LEFT JOIN documento d ON t.id_documento = d.id_documento
              WHERE d.materia LIKE @q OR t.observaciones LIKE @q
              ORDER BY t.fecha_sistema DESC
              OFFSET @offset ROWS FETCH NEXT @n ROWS ONLY
            `)
        : Promise.resolve({ recordset: [] as never[] }),

      // Funcionarios
      tipo === 'funcionarios' || tipo === 'todos'
        ? pool.request()
            .input('q', sql.NVarChar(200), like)
            .query<{
              id_funcionario: number; nombres: string | null;
              apellidos: string | null; rut: string | null; desc_dependencia: string | null;
            }>(`
              SELECT TOP 20 f.id_funcionario, f.nombres, f.apellidos, f.rut,
                     d.desc_dependencia
              FROM funcionario f
              LEFT JOIN dependencia d ON f.id_dependencia = d.id_dependencia
              WHERE f.nombres LIKE @q OR f.apellidos LIKE @q OR f.rut LIKE @q
              ORDER BY f.apellidos, f.nombres
            `)
        : Promise.resolve({ recordset: [] as never[] }),
    ]);

    const totalDocs = (docs.recordset[0] as { total?: number } | undefined)?.total ?? 0;
    sendSuccess(res, {
      documentos: docs.recordset,
      tramites: trams.recordset,
      funcionarios: funcs.recordset,
      total: totalDocs,
      pagina,
      porPagina,
      totalPaginas: Math.ceil(totalDocs / porPagina),
    });
  } catch (e) { next(e); }
});

export default router;
