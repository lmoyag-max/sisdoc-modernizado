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
  id_usuario_titular:              number | null;
  usuario_titular:                 string | null;
  usuario_titular_activo:          boolean | null;
  id_usuario_subrogante:           number | null;
  usuario_subrogante:              string | null;
  usuario_subrogante_activo:       boolean | null;
  id_usuario_subrogante_2:         number | null;
  usuario_subrogante_2:            string | null;
  usuario_subrogante_2_activo:     boolean | null;
  desc_dependencia:                string | null;
  total:                           number;
}

// Estado de habilitación de Firma Simple para un slot de firmante.
function estadoFirmaSimple(
  activoSlot: boolean,
  idUsuarioVinculado: number | null,
  usuarioActivo: boolean | null,
  firmaTimbreRuta: string | null,
): { estado: 'disponible' | 'sin_vincular' | 'usuario_inactivo' | 'sin_firma_timbre' | 'slot_inactivo'; motivo: string | null } {
  if (!activoSlot)              return { estado: 'slot_inactivo',     motivo: 'El cargo no está activo' };
  if (!idUsuarioVinculado)      return { estado: 'sin_vincular',      motivo: 'Sin usuario DOC360 vinculado' };
  if (usuarioActivo === false)  return { estado: 'usuario_inactivo',  motivo: 'El usuario vinculado está inactivo' };
  if (!firmaTimbreRuta)         return { estado: 'sin_firma_timbre',  motivo: 'Sin firma/timbre cargado' };
  return { estado: 'disponible', motivo: null };
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
      usuarioVinculado: r.id_usuario_titular
        ? { idUsuario: r.id_usuario_titular, usuario: r.usuario_titular, activo: !!r.usuario_titular_activo }
        : null,
      ...estadoFirmaSimple(!!r.activo_titular, r.id_usuario_titular, r.usuario_titular_activo, r.firma_timbre_titular_ruta),
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
      usuarioVinculado: r.id_usuario_subrogante
        ? { idUsuario: r.id_usuario_subrogante, usuario: r.usuario_subrogante, activo: !!r.usuario_subrogante_activo }
        : null,
      ...estadoFirmaSimple(!!r.activo_subrogante, r.id_usuario_subrogante, r.usuario_subrogante_activo, r.firma_timbre_subrogante_ruta),
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
      usuarioVinculado: r.id_usuario_subrogante_2
        ? { idUsuario: r.id_usuario_subrogante_2, usuario: r.usuario_subrogante_2, activo: !!r.usuario_subrogante_2_activo }
        : null,
      ...estadoFirmaSimple(!!r.activo_subrogante_2, r.id_usuario_subrogante_2, r.usuario_subrogante_2_activo, r.firma_timbre_subrogante_2_ruta),
    },
  };
}

// JOIN reusado por GET / y GET /:id para traer datos básicos del usuario
// vinculado a cada slot (username + activo), sin exponer clave/clave_hash.
const JOIN_USUARIOS_VINCULADOS = `
  LEFT JOIN usuario ut  ON ut.id_usuario  = j.id_usuario_titular
  LEFT JOIN usuario us  ON us.id_usuario  = j.id_usuario_subrogante
  LEFT JOIN usuario us2 ON us2.id_usuario = j.id_usuario_subrogante_2
`;
const SELECT_USUARIOS_VINCULADOS = `
  ut.usuario  AS usuario_titular,      ut.activo  AS usuario_titular_activo,
  us.usuario  AS usuario_subrogante,   us.activo  AS usuario_subrogante_activo,
  us2.usuario AS usuario_subrogante_2, us2.activo AS usuario_subrogante_2_activo
`;

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
               ${SELECT_USUARIOS_VINCULADOS},
               COUNT(*) OVER() AS total
        FROM   jefatura j
        JOIN   dependencia d ON d.id_dependencia = j.id_dependencia
        ${JOIN_USUARIOS_VINCULADOS}
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
        SELECT j.*, LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia,
               ${SELECT_USUARIOS_VINCULADOS}
        FROM   jefatura j
        JOIN   dependencia d ON d.id_dependencia = j.id_dependencia
        ${JOIN_USUARIOS_VINCULADOS}
        WHERE  j.id_jefatura = @id
      `);
    const row = result.recordset[0];
    if (!row) { sendError(res, 'Jefatura no encontrada', 404); return; }
    sendSuccess(res, mapJefatura(row));
  } catch (e) { next(e); }
});

// ── GET /jefaturas/:id/usuarios-vinculables ────────────────────────
// Candidatos para vincular a un slot de firmante: por defecto usuarios de
// la misma dependencia de la jefatura, filtrables por q (usuario/nombres/apellidos).
// No expone clave/clave_hash.
router.get('/:id/usuarios-vinculables', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const id = Number(req.params.id);
    const q  = String(req.query.q ?? '').trim();

    const pool = await getPool();

    const jef = await pool.request().input('id', sql.Int, id)
      .query<{ id_dependencia: number }>('SELECT id_dependencia FROM jefatura WHERE id_jefatura = @id');
    if (!jef.recordset[0]) { sendError(res, 'Jefatura no encontrada', 404); return; }
    const idDependencia = jef.recordset[0].id_dependencia;

    const request = pool.request()
      .input('idDep', sql.Int, idDependencia)
      .input('idJef', sql.Int, id);

    let where = '(f.id_dependencia = @idDep OR f.id_dependencia IS NULL)';
    if (q) {
      request.input('q', sql.VarChar(100), `%${q}%`);
      where += ' AND (u.usuario LIKE @q OR f.nombres LIKE @q OR f.apellidos LIKE @q)';
    }

    const result = await request.query<{
      id_usuario: number; usuario: string; email: string | null; activo: boolean;
      nombres: string | null; apellidos: string | null;
      id_dependencia: number | null; desc_dependencia: string | null;
      vinculado_como: string | null;
    }>(`
      SELECT u.id_usuario, u.usuario, u.email, u.activo,
             f.nombres, f.apellidos, f.id_dependencia,
             d.desc_dependencia,
             CASE
               WHEN j.id_usuario_titular      = u.id_usuario THEN 'TITULAR'
               WHEN j.id_usuario_subrogante    = u.id_usuario THEN 'SUBROGANTE'
               WHEN j.id_usuario_subrogante_2  = u.id_usuario THEN 'SUBROGANTE_2'
               ELSE NULL
             END AS vinculado_como
      FROM usuario u
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      LEFT JOIN dependencia d ON f.id_dependencia = d.id_dependencia
      LEFT JOIN jefatura j    ON j.id_jefatura = @idJef
      WHERE ${where}
      ORDER BY f.nombres, u.usuario
    `);

    sendSuccess(res, result.recordset.map((r) => ({
      idUsuario:       r.id_usuario,
      usuario:         r.usuario,
      email:           r.email,
      activo:          !!r.activo,
      nombreCompleto:  [r.nombres, r.apellidos].filter(Boolean).join(' ') || null,
      idDependencia:   r.id_dependencia,
      descDependencia: r.desc_dependencia,
      vinculadoComo:   r.vinculado_como,
    })));
  } catch (e) { next(e); }
});

// ── PATCH /jefaturas/:id/vincular-usuario ──────────────────────────
// Ata (o quita, con idUsuario:null) un usuario DOC360 real a un slot de
// firmante. Requiere admin estricto (más restrictivo que el resto del CRUD
// de jefatura) porque esto conecta una identidad de login con autoridad de
// firma — no basta con el rol of.partes que sí puede editar nombre/cargo.
const COL_USUARIO_POR_TIPO: Record<string, string> = {
  TITULAR:      'id_usuario_titular',
  SUBROGANTE:   'id_usuario_subrogante',
  SUBROGANTE_2: 'id_usuario_subrogante_2',
};

router.patch('/:id/vincular-usuario', requireRole('admin'), async (req: Request, res: Response, next: NextFunction) => {
  try {
    const id = Number(req.params.id);
    const { tipo, idUsuario } = req.body as { tipo?: string; idUsuario?: number | null };

    const col = tipo ? COL_USUARIO_POR_TIPO[tipo] : undefined;
    if (!col) {
      sendError(res, 'tipo inválido. Use TITULAR, SUBROGANTE o SUBROGANTE_2', 400);
      return;
    }

    const pool = await getPool();

    const jef = await pool.request().input('id', sql.Int, id)
      .query<{ id_dependencia: number }>('SELECT id_dependencia FROM jefatura WHERE id_jefatura = @id');
    if (!jef.recordset[0]) { sendError(res, 'Jefatura no encontrada', 404); return; }

    if (idUsuario != null) {
      const usr = await pool.request().input('id', sql.Int, idUsuario)
        .query<{ activo: boolean; id_dependencia: number | null }>(`
          SELECT u.activo, f.id_dependencia
          FROM usuario u
          LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
          WHERE u.id_usuario = @id
        `);
      const usrRow = usr.recordset[0];
      if (!usrRow) { sendError(res, 'Usuario no encontrado', 404); return; }
      if (!usrRow.activo) { sendError(res, 'El usuario está inactivo — actívalo antes de vincularlo', 400); return; }
    }

    await pool.request()
      .input('id',  sql.Int, id)
      .input('idU', sql.Int, idUsuario ?? null)
      .query(`UPDATE jefatura SET ${col} = @idU, fecha_update = GETDATE() WHERE id_jefatura = @id`);

    sendSuccess(res, { id, tipo, idUsuarioVinculado: idUsuario ?? null }, idUsuario ? 'Usuario vinculado' : 'Vínculo quitado');
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
