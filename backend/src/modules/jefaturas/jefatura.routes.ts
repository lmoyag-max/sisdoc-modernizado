import { Router, Request, Response, NextFunction } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { requireAuth, requireRole } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendCreated, sendError } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { env } from '../../config/env';

const router = Router();
router.use(requireAuth);

const MEMO_IMG_DIR = path.resolve(env.UPLOAD_DIR, 'config', 'memo');
fs.mkdirSync(MEMO_IMG_DIR, { recursive: true });

const imgStorage = multer.diskStorage({
  destination: (_req, _file, cb) => { cb(null, MEMO_IMG_DIR); },
  filename: (_req, file, cb) => {
    const ext = path.extname(file.originalname).toLowerCase();
    const ts  = Date.now().toString().slice(-8);
    cb(null, `${ts}${ext}`);
  },
});

const uploadImg = multer({
  storage: imgStorage,
  limits:  { fileSize: 5 * 1024 * 1024 },
  fileFilter: (_req, file, cb) => {
    const allowed = ['.png', '.jpg', '.jpeg', '.webp'];
    cb(null, allowed.includes(path.extname(file.originalname).toLowerCase()));
  },
});

interface JefaturaRow {
  id_jefatura:                     number;
  id_dependencia:                  number;
  nombre_titular:                  string;
  cargo_titular:                   string;
  rut_titular:                     string | null;
  firma_timbre_titular_ruta:       string | null;
  activo_titular:                  boolean;
  vigencia_desde_titular:          string | null;
  vigencia_hasta_titular:          string | null;
  nombre_subrogante:               string | null;
  cargo_subrogante:                string | null;
  rut_subrogante:                  string | null;
  firma_timbre_subrogante_ruta:    string | null;
  activo_subrogante:               boolean;
  vigencia_desde_sub:              string | null;
  vigencia_hasta_sub:              string | null;
  nombre_subrogante_2:             string | null;
  cargo_subrogante_2:              string | null;
  rut_subrogante_2:                string | null;
  firma_timbre_subrogante_2_ruta:  string | null;
  activo_subrogante_2:             boolean;
  vigencia_desde_sub_2:            string | null;
  vigencia_hasta_sub_2:            string | null;
  desc_dependencia:                string | null;
  total:                           number;
}

function mapJefatura(r: JefaturaRow) {
  return {
    id:             r.id_jefatura,
    idDependencia:  r.id_dependencia,
    dependencia:    r.desc_dependencia ?? null,
    titular: {
      nombre:         r.nombre_titular,
      cargo:          r.cargo_titular,
      rut:            r.rut_titular ?? null,
      firmaTimbreUrl: r.firma_timbre_titular_ruta
        ? `/uploads/config/memo/${r.firma_timbre_titular_ruta}`
        : null,
      activo:        !!r.activo_titular,
      vigenciaDesde: r.vigencia_desde_titular ?? null,
      vigenciaHasta: r.vigencia_hasta_titular ?? null,
    },
    subrogante: {
      nombre:         r.nombre_subrogante   ?? null,
      cargo:          r.cargo_subrogante    ?? null,
      rut:            r.rut_subrogante      ?? null,
      firmaTimbreUrl: r.firma_timbre_subrogante_ruta
        ? `/uploads/config/memo/${r.firma_timbre_subrogante_ruta}`
        : null,
      activo:        !!r.activo_subrogante,
      vigenciaDesde: r.vigencia_desde_sub   ?? null,
      vigenciaHasta: r.vigencia_hasta_sub   ?? null,
    },
    subrogante2: {
      nombre:         r.nombre_subrogante_2   ?? null,
      cargo:          r.cargo_subrogante_2    ?? null,
      rut:            r.rut_subrogante_2      ?? null,
      firmaTimbreUrl: r.firma_timbre_subrogante_2_ruta
        ? `/uploads/config/memo/${r.firma_timbre_subrogante_2_ruta}`
        : null,
      activo:        !!r.activo_subrogante_2,
      vigenciaDesde: r.vigencia_desde_sub_2   ?? null,
      vigenciaHasta: r.vigencia_hasta_sub_2   ?? null,
    },
  };
}

const soloAdmin = [requireRole('admin', 'of.partes')];

// ── GET /jefaturas ─────────────────────────────────────────────────
// Paginado desde BD (OFFSET/FETCH). Los KPIs (total_global/activos_global)
// se calculan en una consulta aparte, sobre la tabla completa sin filtro de
// búsqueda ni de página, para que el strip de indicadores no varíe con la
// búsqueda ni quede en 0 cuando una página/búsqueda no tiene resultados.
router.get('/', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pagina    = Math.max(1, Number(req.query.pagina ?? 1));
    const porPagina = Math.min(100, Math.max(1, Number(req.query.porPagina ?? 20)));
    const offset    = (pagina - 1) * porPagina;
    const q         = String(req.query.q ?? '').trim();

    const pool = await getPool();

    const kpisResult = await pool.request().query<{ total_global: number; activos_global: number }>(`
      SELECT
        COUNT(*) AS total_global,
        SUM(CASE WHEN j.activo_titular = 1
                  AND (j.vigencia_desde_titular IS NULL OR j.vigencia_desde_titular <= CAST(GETDATE() AS DATE))
                  AND (j.vigencia_hasta_titular IS NULL OR j.vigencia_hasta_titular >= CAST(GETDATE() AS DATE))
                 THEN 1 ELSE 0 END) AS activos_global
      FROM jefatura j
    `);
    const totalGlobal   = kpisResult.recordset[0]?.total_global   ?? 0;
    const activosGlobal = kpisResult.recordset[0]?.activos_global ?? 0;

    const result = await pool.request()
      .input('offset',    sql.Int,     offset)
      .input('porPagina', sql.Int,     porPagina)
      .input('like',      sql.VarChar, `%${q}%`)
      .query<JefaturaRow>(`
        SELECT j.*, LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia,
               COUNT(*) OVER() AS total
        FROM   jefatura j
        JOIN   dependencia d ON d.id_dependencia = j.id_dependencia
        WHERE  @like = '%%' OR
               d.desc_dependencia    LIKE @like OR
               j.nombre_titular      LIKE @like OR
               j.nombre_subrogante   LIKE @like OR
               j.nombre_subrogante_2 LIKE @like
        ORDER BY d.desc_dependencia
        OFFSET @offset ROWS FETCH NEXT @porPagina ROWS ONLY
      `);

    const total = result.recordset[0]?.total ?? 0;

    res.status(200).json({
      ok:   true,
      data: result.recordset.map(mapJefatura),
      meta: { total, pagina, porPagina, totalPaginas: Math.ceil(total / porPagina) },
      kpis: { total: totalGlobal, activos: activosGlobal, sinFirmante: totalGlobal - activosGlobal },
    });
  } catch (e) { next(e); }
});

// ── GET /jefaturas/:id ─────────────────────────────────────────────
router.get('/:id', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const result = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<JefaturaRow>(`
        SELECT j.*, LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia
        FROM   jefatura j
        JOIN   dependencia d ON d.id_dependencia = j.id_dependencia
        WHERE  j.id_jefatura = @id
      `);
    const row = result.recordset[0];
    if (!row) { sendError(res, 'Jefatura no encontrada', 404); return; }
    sendSuccess(res, mapJefatura(row));
  } catch (e) { next(e); }
});

// ── POST /jefaturas — UPSERT por dependencia ──────────────────────
router.post('/', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const user = (req as unknown as AuthenticatedRequest).user;
    const body = req.body as {
      idDependencia:        number;
      nombreTitular:        string;
      cargoTitular:         string;
      rutTitular?:          string | null;
      activoTitular?:       boolean;
      vigenciaDesde?:       string;
      vigenciaHasta?:       string;
      nombreSubrogante?:    string;
      cargoSubrogante?:     string;
      rutSubrogante?:       string | null;
      activoSubrogante?:    boolean;
      vigenciaDesdeSubr?:   string;
      vigenciaHastaSubr?:   string;
      nombreSubrogante2?:   string;
      cargoSubrogante2?:    string;
      rutSubrogante2?:      string | null;
      activoSubrogante2?:   boolean;
      vigenciaDesdeSubr2?:  string;
      vigenciaHastaSubr2?:  string;
    };

    if (!body.idDependencia || !body.nombreTitular || !body.cargoTitular) {
      sendError(res, 'idDependencia, nombreTitular y cargoTitular son requeridos', 400);
      return;
    }

    const pool = await getPool();

    const exists = await pool.request()
      .input('idDep', sql.Int, body.idDependencia)
      .query<{ id_jefatura: number }>('SELECT id_jefatura FROM jefatura WHERE id_dependencia = @idDep');

    if (exists.recordset[0]) {
      const id = exists.recordset[0].id_jefatura;
      await pool.request()
        .input('id',           sql.Int,          id)
        .input('nomTit',       sql.VarChar(100), body.nombreTitular.trim())
        .input('cargoTit',     sql.VarChar(100), body.cargoTitular.trim())
        .input('rutTit',       sql.VarChar(12),  body.rutTitular   ?? null)
        .input('activoTit',    sql.Bit,          body.activoTitular !== false ? 1 : 0)
        .input('vDesde',       sql.Date,         body.vigenciaDesde     ?? null)
        .input('vHasta',       sql.Date,         body.vigenciaHasta     ?? null)
        .input('nomSubr',      sql.VarChar(100), body.nombreSubrogante  ?? null)
        .input('cargoSubr',    sql.VarChar(100), body.cargoSubrogante   ?? null)
        .input('rutSubr',      sql.VarChar(12),  body.rutSubrogante     ?? null)
        .input('activoSubr',   sql.Bit,          body.activoSubrogante  ? 1 : 0)
        .input('vDesdeSubr',   sql.Date,         body.vigenciaDesdeSubr ?? null)
        .input('vHastaSubr',   sql.Date,         body.vigenciaHastaSubr ?? null)
        .input('nomSubr2',     sql.VarChar(100), body.nombreSubrogante2  ?? null)
        .input('cargoSubr2',   sql.VarChar(100), body.cargoSubrogante2   ?? null)
        .input('rutSubr2',     sql.VarChar(12),  body.rutSubrogante2     ?? null)
        .input('activoSubr2',  sql.Bit,          body.activoSubrogante2  ? 1 : 0)
        .input('vDesdeSubr2',  sql.Date,         body.vigenciaDesdeSubr2 ?? null)
        .input('vHastaSubr2',  sql.Date,         body.vigenciaHastaSubr2 ?? null)
        .input('idUsr',        sql.Int,          user.idUsuario)
        .query(`
          UPDATE jefatura SET
            nombre_titular = @nomTit, cargo_titular = @cargoTit,
            rut_titular = @rutTit, activo_titular = @activoTit,
            vigencia_desde_titular = @vDesde, vigencia_hasta_titular = @vHasta,
            nombre_subrogante = @nomSubr, cargo_subrogante = @cargoSubr,
            rut_subrogante = @rutSubr, activo_subrogante = @activoSubr,
            vigencia_desde_sub = @vDesdeSubr, vigencia_hasta_sub = @vHastaSubr,
            nombre_subrogante_2 = @nomSubr2, cargo_subrogante_2 = @cargoSubr2,
            rut_subrogante_2 = @rutSubr2, activo_subrogante_2 = @activoSubr2,
            vigencia_desde_sub_2 = @vDesdeSubr2, vigencia_hasta_sub_2 = @vHastaSubr2,
            fecha_update = GETDATE(), id_usuario_modificacion = @idUsr
          WHERE id_jefatura = @id
        `);
      sendSuccess(res, { id }, 'Jefatura actualizada');
    } else {
      const ins = await pool.request()
        .input('idDep',        sql.Int,          body.idDependencia)
        .input('nomTit',       sql.VarChar(100), body.nombreTitular.trim())
        .input('cargoTit',     sql.VarChar(100), body.cargoTitular.trim())
        .input('rutTit',       sql.VarChar(12),  body.rutTitular   ?? null)
        .input('activoTit',    sql.Bit,          body.activoTitular !== false ? 1 : 0)
        .input('vDesde',       sql.Date,         body.vigenciaDesde     ?? null)
        .input('vHasta',       sql.Date,         body.vigenciaHasta     ?? null)
        .input('nomSubr',      sql.VarChar(100), body.nombreSubrogante  ?? null)
        .input('cargoSubr',    sql.VarChar(100), body.cargoSubrogante   ?? null)
        .input('rutSubr',      sql.VarChar(12),  body.rutSubrogante     ?? null)
        .input('activoSubr',   sql.Bit,          body.activoSubrogante  ? 1 : 0)
        .input('vDesdeSubr',   sql.Date,         body.vigenciaDesdeSubr ?? null)
        .input('vHastaSubr',   sql.Date,         body.vigenciaHastaSubr ?? null)
        .input('nomSubr2',     sql.VarChar(100), body.nombreSubrogante2  ?? null)
        .input('cargoSubr2',   sql.VarChar(100), body.cargoSubrogante2   ?? null)
        .input('rutSubr2',     sql.VarChar(12),  body.rutSubrogante2     ?? null)
        .input('activoSubr2',  sql.Bit,          body.activoSubrogante2  ? 1 : 0)
        .input('vDesdeSubr2',  sql.Date,         body.vigenciaDesdeSubr2 ?? null)
        .input('vHastaSubr2',  sql.Date,         body.vigenciaHastaSubr2 ?? null)
        .input('idUsr',        sql.Int,          user.idUsuario)
        .query<{ id: number }>(`
          INSERT INTO jefatura
            (id_dependencia, nombre_titular, cargo_titular, rut_titular, activo_titular,
             vigencia_desde_titular, vigencia_hasta_titular,
             nombre_subrogante, cargo_subrogante, rut_subrogante, activo_subrogante,
             vigencia_desde_sub, vigencia_hasta_sub,
             nombre_subrogante_2, cargo_subrogante_2, rut_subrogante_2, activo_subrogante_2,
             vigencia_desde_sub_2, vigencia_hasta_sub_2,
             id_usuario_modificacion)
          OUTPUT INSERTED.id_jefatura AS id
          VALUES
            (@idDep, @nomTit, @cargoTit, @rutTit, @activoTit,
             @vDesde, @vHasta,
             @nomSubr, @cargoSubr, @rutSubr, @activoSubr,
             @vDesdeSubr, @vHastaSubr,
             @nomSubr2, @cargoSubr2, @rutSubr2, @activoSubr2,
             @vDesdeSubr2, @vHastaSubr2,
             @idUsr)
        `);
      sendCreated(res, { id: ins.recordset[0].id }, 'Jefatura creada');
    }
  } catch (e) { next(e); }
});

// ── DELETE /jefaturas/:id ──────────────────────────────────────────
router.delete('/:id', requireRole('admin'), async (req: Request, res: Response, next: NextFunction) => {
  try {
    const id = Number(req.params.id);
    const pool = await getPool();

    const prev = await pool.request()
      .input('id', sql.Int, id)
      .query<{
        firma_timbre_titular_ruta:     string | null;
        firma_timbre_subrogante_ruta:  string | null;
        firma_timbre_subrogante_2_ruta: string | null;
      }>(
        'SELECT firma_timbre_titular_ruta, firma_timbre_subrogante_ruta, firma_timbre_subrogante_2_ruta FROM jefatura WHERE id_jefatura = @id'
      );
    const row = prev.recordset[0];
    if (!row) { sendError(res, 'Jefatura no encontrada', 404); return; }

    for (const ruta of [row.firma_timbre_titular_ruta, row.firma_timbre_subrogante_ruta, row.firma_timbre_subrogante_2_ruta]) {
      if (ruta) {
        const fp = path.join(MEMO_IMG_DIR, ruta);
        if (fs.existsSync(fp)) fs.unlinkSync(fp);
      }
    }

    await pool.request().input('id', sql.Int, id).query('DELETE FROM jefatura WHERE id_jefatura = @id');
    sendSuccess(res, null, 'Jefatura eliminada');
  } catch (e) { next(e); }
});

// ── POST /jefaturas/:id/imagen ─────────────────────────────────────
// tipo query: 'firma_timbre_titular' | 'firma_timbre_subrogante' | 'firma_timbre_subrogante_2'
router.post('/:id/imagen',
  ...soloAdmin,
  uploadImg.single('imagen'),
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      if (!req.file) { sendError(res, 'No se recibió imagen', 400); return; }

      const id   = Number(req.params.id);
      const tipo = String(req.query.tipo ?? '');
      const cols: Record<string, string> = {
        firma_timbre_titular:      'firma_timbre_titular_ruta',
        firma_timbre_subrogante:   'firma_timbre_subrogante_ruta',
        firma_timbre_subrogante_2: 'firma_timbre_subrogante_2_ruta',
      };
      const col = cols[tipo];
      if (!col) {
        fs.unlinkSync(req.file.path);
        sendError(res, `tipo inválido: ${tipo}. Use firma_timbre_titular, firma_timbre_subrogante o firma_timbre_subrogante_2`, 400);
        return;
      }

      const pool = await getPool();

      const prev = await pool.request()
        .input('id', sql.Int, id)
        .query<Record<string, string | null>>(`SELECT ${col} AS ruta FROM jefatura WHERE id_jefatura = @id`);
      const rutaAnterior = prev.recordset[0]?.ruta;
      if (rutaAnterior) {
        const fp = path.join(MEMO_IMG_DIR, rutaAnterior);
        if (fs.existsSync(fp)) fs.unlinkSync(fp);
      }

      await pool.request()
        .input('ruta', sql.VarChar(100), req.file.filename)
        .input('id',   sql.Int,          id)
        .query(`UPDATE jefatura SET ${col} = @ruta, fecha_update = GETDATE() WHERE id_jefatura = @id`);

      sendSuccess(res, {
        url:      `/uploads/config/memo/${req.file.filename}`,
        filename: req.file.filename,
      }, 'Imagen guardada');
    } catch (e) { next(e); }
  }
);

export default router;
