import { Router } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { requireAuth, requireRole } from '../../middleware/auth.middleware';
import { env } from '../../config/env';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendCreated, sendError } from '../../shared/utils/response';

const router = Router();

// ── Paths ───────────────────────────────────────────────────
const CONFIG_DIR = path.resolve(env.UPLOAD_DIR, 'config');
const CONFIG_FILE = path.join(CONFIG_DIR, 'sistema.json');

function ensureConfigDir() {
  if (!fs.existsSync(CONFIG_DIR)) fs.mkdirSync(CONFIG_DIR, { recursive: true });
}

function readConfig(): Record<string, unknown> {
  ensureConfigDir();
  if (!fs.existsSync(CONFIG_FILE)) return {};
  try { return JSON.parse(fs.readFileSync(CONFIG_FILE, 'utf-8')); } catch { return {}; }
}

function writeConfig(data: Record<string, unknown>) {
  ensureConfigDir();
  fs.writeFileSync(CONFIG_FILE, JSON.stringify(data, null, 2), 'utf-8');
}

// ── Multer para imágenes de config ─────────────────────────
const storage = multer.diskStorage({
  destination: (_req, _file, cb) => { ensureConfigDir(); cb(null, CONFIG_DIR); },
  filename: (_req, file, cb) => {
    const ext = path.extname(file.originalname).toLowerCase();
    const key = (file.fieldname === 'archivo' && _req.path.includes('logo')) ? 'logo' : 'background';
    cb(null, `${key}${ext}`);
  },
});

const upload = multer({
  storage,
  limits: { fileSize: 5 * 1024 * 1024 },
  fileFilter: (_req, file, cb) => {
    // SVG eliminado: puede contener <script> → riesgo de XSS almacenado
    const allowed = /\.(png|jpg|jpeg|webp)$/i;
    cb(null, allowed.test(file.originalname));
  },
});

// ── Reglas de carga de archivos ────────────────────────────
const UPLOAD_RULES_DEFAULTS = {
  extensionesPermitidas: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'webp'],
  maxFileMB:  20,
  maxTotalMB: 60,
};

// Extensiones que el sistema físicamente puede aceptar (límite duro de multer en archivos.routes.ts).
// La configuración solo puede restringir dentro de este conjunto, no ampliar.
const EXTENSIONES_POSIBLES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'webp', 'txt'];

// Textos del login con sus valores por defecto
const LOGIN_DEFAULTS = {
  loginNombreSistema:   'DOC360',
  loginSubtitulo:       'Sistema de Gestión Documental',
  loginTituloPrincipal: 'Gestión documental moderna',
  loginDescripcion:     'Plataforma enterprise para la gestión, seguimiento y trazabilidad de documentos institucionales.',
  loginCard1:           'Gestión documental',
  loginCard2:           'Flujo de derivaciones',
  loginCard3:           'Trazabilidad completa',
  loginCard4:           'Historial documental',
  loginFooter:          '© 2026 DOC360 — Sistema institucional de gestión documental',
};

// ── GET /configuracion — leer config completa (pública) ────
router.get('/', (req, res) => {
  const cfg = readConfig();
  const logoFile = ['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp']
    .find((f) => fs.existsSync(path.join(CONFIG_DIR, f)));
  const bgFile = ['background.jpg', 'background.jpeg', 'background.png', 'background.webp']
    .find((f) => fs.existsSync(path.join(CONFIG_DIR, f)));

  res.json({
    ok: true,
    data: {
      nombreSistema:     cfg.nombreSistema     ?? 'DOC360',
      nombreInstitucion: cfg.nombreInstitucion ?? 'HUAP',
      logoUrl:           logoFile ? `/uploads/config/${logoFile}` : null,
      backgroundUrl:     bgFile   ? `/uploads/config/${bgFile}`   : null,
      version:           '2.0.0',
      // Textos configurables del login
      loginNombreSistema:   cfg.loginNombreSistema   ?? LOGIN_DEFAULTS.loginNombreSistema,
      loginSubtitulo:       cfg.loginSubtitulo       ?? LOGIN_DEFAULTS.loginSubtitulo,
      loginTituloPrincipal: cfg.loginTituloPrincipal ?? LOGIN_DEFAULTS.loginTituloPrincipal,
      loginDescripcion:     cfg.loginDescripcion     ?? LOGIN_DEFAULTS.loginDescripcion,
      loginCard1:           cfg.loginCard1           ?? LOGIN_DEFAULTS.loginCard1,
      loginCard2:           cfg.loginCard2           ?? LOGIN_DEFAULTS.loginCard2,
      loginCard3:           cfg.loginCard3           ?? LOGIN_DEFAULTS.loginCard3,
      loginCard4:           cfg.loginCard4           ?? LOGIN_DEFAULTS.loginCard4,
      loginFooter:          cfg.loginFooter          ?? LOGIN_DEFAULTS.loginFooter,
      // Reglas de carga configurables
      uploadRules: {
        extensionesPermitidas: Array.isArray(cfg.uploadExtensionesPermitidas)
          ? (cfg.uploadExtensionesPermitidas as string[])
          : UPLOAD_RULES_DEFAULTS.extensionesPermitidas,
        maxFileMB:  typeof cfg.uploadMaxFileMB  === 'number' ? cfg.uploadMaxFileMB  : UPLOAD_RULES_DEFAULTS.maxFileMB,
        maxTotalMB: typeof cfg.uploadMaxTotalMB === 'number' ? cfg.uploadMaxTotalMB : UPLOAD_RULES_DEFAULTS.maxTotalMB,
      },
    },
  });
});

// ── PATCH /configuracion — actualizar nombres y textos ─────
router.patch('/', requireAuth, (req, res) => {
  const body = req.body as Record<string, string | undefined>;
  const cfg  = readConfig();

  // Campos de identidad
  if (body.nombreSistema     !== undefined) cfg.nombreSistema     = body.nombreSistema;
  if (body.nombreInstitucion !== undefined) cfg.nombreInstitucion = body.nombreInstitucion;

  // Textos configurables del login
  const loginFields: (keyof typeof LOGIN_DEFAULTS)[] = [
    'loginNombreSistema', 'loginSubtitulo', 'loginTituloPrincipal',
    'loginDescripcion', 'loginCard1', 'loginCard2', 'loginCard3',
    'loginCard4', 'loginFooter',
  ];
  for (const field of loginFields) {
    if (body[field] !== undefined) cfg[field] = body[field];
  }

  writeConfig(cfg);
  res.json({ ok: true, data: cfg });
});

// ── PATCH /configuracion/upload-rules — actualizar reglas de carga ──
router.patch('/upload-rules', requireAuth, (req, res) => {
  const body = req.body as {
    extensionesPermitidas?: unknown;
    maxFileMB?:             unknown;
    maxTotalMB?:            unknown;
  };
  const cfg = readConfig();

  if (body.extensionesPermitidas !== undefined) {
    if (!Array.isArray(body.extensionesPermitidas)) {
      res.status(400).json({ ok: false, error: 'extensionesPermitidas debe ser un array' });
      return;
    }
    const valid = (body.extensionesPermitidas as unknown[])
      .filter((e): e is string => typeof e === 'string' && EXTENSIONES_POSIBLES.includes(e));
    if (valid.length === 0) {
      res.status(400).json({ ok: false, error: 'Debe permitirse al menos una extensión válida' });
      return;
    }
    cfg.uploadExtensionesPermitidas = valid;
  }

  if (body.maxFileMB !== undefined) {
    const v = Number(body.maxFileMB);
    if (isNaN(v) || v < 1 || v > 100) {
      res.status(400).json({ ok: false, error: 'maxFileMB debe estar entre 1 y 100 MB' });
      return;
    }
    cfg.uploadMaxFileMB = v;
  }

  if (body.maxTotalMB !== undefined) {
    const v    = Number(body.maxTotalMB);
    const fMB  = typeof cfg.uploadMaxFileMB === 'number' ? cfg.uploadMaxFileMB : UPLOAD_RULES_DEFAULTS.maxFileMB;
    if (isNaN(v) || v < fMB) {
      res.status(400).json({ ok: false, error: `maxTotalMB debe ser mayor o igual a maxFileMB (${fMB} MB)` });
      return;
    }
    cfg.uploadMaxTotalMB = v;
  }

  writeConfig(cfg);
  res.json({
    ok: true,
    data: {
      uploadRules: {
        extensionesPermitidas: Array.isArray(cfg.uploadExtensionesPermitidas)
          ? cfg.uploadExtensionesPermitidas
          : UPLOAD_RULES_DEFAULTS.extensionesPermitidas,
        maxFileMB:  typeof cfg.uploadMaxFileMB  === 'number' ? cfg.uploadMaxFileMB  : UPLOAD_RULES_DEFAULTS.maxFileMB,
        maxTotalMB: typeof cfg.uploadMaxTotalMB === 'number' ? cfg.uploadMaxTotalMB : UPLOAD_RULES_DEFAULTS.maxTotalMB,
      },
    },
  });
});

// ── POST /configuracion/logo — subir logo ──────────────────
router.post('/logo', requireAuth, upload.single('archivo'), (req, res) => {
  if (!req.file) { res.status(400).json({ ok: false, error: 'No se recibió archivo' }); return; }
  // rename to logo.<ext> replacing any previous logo
  const ext = path.extname(req.file.originalname).toLowerCase() || path.extname(req.file.filename);
  const finalName = `logo${ext}`;
  const finalPath = path.join(CONFIG_DIR, finalName);
  if (req.file.path !== finalPath) fs.renameSync(req.file.path, finalPath);
  res.json({ ok: true, data: { url: `/uploads/config/${finalName}` } });
});

// ── POST /configuracion/background — subir fondo ───────────
router.post('/background', requireAuth, upload.single('archivo'), (req, res) => {
  if (!req.file) { res.status(400).json({ ok: false, error: 'No se recibió archivo' }); return; }
  const ext = path.extname(req.file.originalname).toLowerCase() || path.extname(req.file.filename);
  const finalName = `background${ext}`;
  const finalPath = path.join(CONFIG_DIR, finalName);
  if (req.file.path !== finalPath) fs.renameSync(req.file.path, finalPath);
  res.json({ ok: true, data: { url: `/uploads/config/${finalName}` } });
});

// ═══════════════════════════════════════════════════════════════════════════
// MANTENEDORES ADMINISTRATIVOS — solo admin y of.partes
// ═══════════════════════════════════════════════════════════════════════════

const soloPropietarios = [requireAuth, requireRole('admin', 'of.partes')];

// ── Tipos de documento ────────────────────────────────────────────────────

router.get('/tipos-documento', ...soloPropietarios, async (req, res, next) => {
  try {
    const q    = String(req.query.q ?? '').trim();
    const pool = await getPool();
    const req_ = pool.request();
    let where  = '1=1';
    if (q) {
      req_.input('q', sql.VarChar(100), `%${q}%`);
      where += ' AND desc_tipo_documento LIKE @q';
    }
    const result = await req_.query<{ id_tipo_documento: number; desc_tipo_documento: string; vigencia: string | null }>(`
      SELECT id_tipo_documento, desc_tipo_documento, vigencia
      FROM tipo_documento
      WHERE ${where}
      ORDER BY desc_tipo_documento
    `);
    sendSuccess(res, result.recordset.map((r) => ({
      id:          r.id_tipo_documento,
      descripcion: r.desc_tipo_documento,
      vigencia:    r.vigencia?.trim() ?? 'S',
    })));
  } catch (e) { next(e); }
});

router.post('/tipos-documento', ...soloPropietarios, async (req, res, next) => {
  try {
    const desc = String(req.body?.descripcion ?? '').trim();
    if (!desc)       { sendError(res, 'La descripción es requerida', 400); return; }
    if (desc.length > 60) { sendError(res, 'La descripción no puede superar 60 caracteres', 400); return; }

    const pool = await getPool();
    const dup  = await pool.request()
      .input('desc', sql.VarChar(60), desc)
      .query('SELECT 1 FROM tipo_documento WHERE LTRIM(RTRIM(desc_tipo_documento)) = LTRIM(RTRIM(@desc))');
    if (dup.recordset.length > 0) { sendError(res, 'Ya existe un tipo de documento con esa descripción', 409); return; }

    const ins = await pool.request()
      .input('desc', sql.VarChar(60), desc)
      .query<{ id: number }>(`
        INSERT INTO tipo_documento (desc_tipo_documento)
        OUTPUT INSERTED.id_tipo_documento AS id
        VALUES (@desc)
      `);
    sendCreated(res, { id: ins.recordset[0].id, descripcion: desc }, 'Tipo de documento creado');
  } catch (e) { next(e); }
});

router.patch('/tipos-documento/:id/vigencia', ...soloPropietarios, async (req, res, next) => {
  try {
    const id      = Number(req.params.id);
    const vigencia = String(req.body?.vigencia ?? '').trim().toUpperCase();
    if (vigencia !== 'S' && vigencia !== 'N') { sendError(res, 'vigencia debe ser S o N', 400); return; }

    const pool = await getPool();
    await pool.request()
      .input('vigencia', sql.Char(1), vigencia)
      .input('id',       sql.Int,    id)
      .query('UPDATE tipo_documento SET vigencia = @vigencia WHERE id_tipo_documento = @id');
    sendSuccess(res, { id, vigencia }, vigencia === 'S' ? 'Tipo activado' : 'Tipo desactivado');
  } catch (e) { next(e); }
});

router.put('/tipos-documento/:id', ...soloPropietarios, async (req, res, next) => {
  try {
    const id   = Number(req.params.id);
    const desc = String(req.body?.descripcion ?? '').trim();
    if (!desc)       { sendError(res, 'La descripción es requerida', 400); return; }
    if (desc.length > 60) { sendError(res, 'La descripción no puede superar 60 caracteres', 400); return; }

    const pool = await getPool();
    const dup  = await pool.request()
      .input('desc', sql.VarChar(60), desc)
      .input('id',   sql.Int,        id)
      .query('SELECT 1 FROM tipo_documento WHERE LTRIM(RTRIM(desc_tipo_documento)) = LTRIM(RTRIM(@desc)) AND id_tipo_documento <> @id');
    if (dup.recordset.length > 0) { sendError(res, 'Ya existe un tipo de documento con esa descripción', 409); return; }

    await pool.request()
      .input('desc', sql.VarChar(60), desc)
      .input('id',   sql.Int,        id)
      .query('UPDATE tipo_documento SET desc_tipo_documento = @desc WHERE id_tipo_documento = @id');
    sendSuccess(res, { id, descripcion: desc }, 'Tipo de documento actualizado');
  } catch (e) { next(e); }
});

// ── Dependencias ──────────────────────────────────────────────────────────

router.get('/dependencias', ...soloPropietarios, async (req, res, next) => {
  try {
    const q    = String(req.query.q ?? '').trim();
    const pool = await getPool();
    const req_ = pool.request();
    let where  = "LTRIM(RTRIM(ISNULL(desc_dependencia,''))) <> ''";
    if (q) {
      req_.input('q', sql.VarChar(100), `%${q}%`);
      where += ' AND desc_dependencia LIKE @q';
    }
    const result = await req_.query<{
      id_dependencia: number; desc_dependencia: string;
      cod_dependencia: string | null; cod_numinterno: number | null;
      ofpartes: string | null; vigencia: string | null;
      cod_dependencia_nuevo: string | null;
    }>(`
      SELECT id_dependencia, LTRIM(RTRIM(desc_dependencia)) AS desc_dependencia,
             cod_dependencia, cod_numinterno, ofpartes, vigencia, cod_dependencia_nuevo
      FROM dependencia
      WHERE ${where}
      ORDER BY desc_dependencia
    `);
    sendSuccess(res, result.recordset.map((r) => ({
      id:          r.id_dependencia,
      descripcion: r.desc_dependencia,
      codigo:      r.cod_dependencia?.trim() ?? null,
      codInterno:  r.cod_numinterno ?? null,
      ofpartes:    r.ofpartes?.trim() ?? null,
      vigencia:    r.vigencia?.trim() ?? null,
      codigoNuevo: r.cod_dependencia_nuevo?.trim() ?? null,
    })));
  } catch (e) { next(e); }
});

router.post('/dependencias', ...soloPropietarios, async (req, res, next) => {
  try {
    const body   = req.body as Record<string, unknown>;
    const desc   = String(body.descripcion ?? '').trim();
    if (!desc)       { sendError(res, 'La descripción es requerida', 400); return; }
    if (desc.length > 60) { sendError(res, 'La descripción no puede superar 60 caracteres', 400); return; }

    const pool = await getPool();
    const dup  = await pool.request()
      .input('desc', sql.VarChar(60), desc)
      .query('SELECT 1 FROM dependencia WHERE LTRIM(RTRIM(desc_dependencia)) = LTRIM(RTRIM(@desc))');
    if (dup.recordset.length > 0) { sendError(res, 'Ya existe una dependencia con esa descripción', 409); return; }

    const ins = await pool.request()
      .input('desc',     sql.VarChar(60), desc)
      .input('cod',      sql.VarChar(6),  body.codigo      ? String(body.codigo).substring(0, 6)      : null)
      .input('codN',     sql.Int,         body.codInterno  ? Number(body.codInterno)                  : null)
      .input('ofp',      sql.Char(1),     body.ofpartes === 'S' ? 'S' : 'N')
      .input('codNuevo', sql.VarChar(6),  body.codigoNuevo ? String(body.codigoNuevo).substring(0, 6) : null)
      .query<{ id: number }>(`
        INSERT INTO dependencia (desc_dependencia, cod_dependencia, cod_numinterno, ofpartes, vigencia, cod_dependencia_nuevo)
        OUTPUT INSERTED.id_dependencia AS id
        VALUES (@desc, @cod, @codN, @ofp, 'S', @codNuevo)
      `);
    sendCreated(res, { id: ins.recordset[0].id, descripcion: desc, vigencia: 'S' }, 'Dependencia creada');
  } catch (e) { next(e); }
});

router.put('/dependencias/:id', ...soloPropietarios, async (req, res, next) => {
  try {
    const id   = Number(req.params.id);
    const body = req.body as Record<string, unknown>;
    const desc = String(body.descripcion ?? '').trim();
    if (!desc)       { sendError(res, 'La descripción es requerida', 400); return; }
    if (desc.length > 60) { sendError(res, 'La descripción no puede superar 60 caracteres', 400); return; }

    const pool = await getPool();
    const dup  = await pool.request()
      .input('desc', sql.VarChar(60), desc)
      .input('id',   sql.Int,        id)
      .query('SELECT 1 FROM dependencia WHERE LTRIM(RTRIM(desc_dependencia)) = LTRIM(RTRIM(@desc)) AND id_dependencia <> @id');
    if (dup.recordset.length > 0) { sendError(res, 'Ya existe una dependencia con esa descripción', 409); return; }

    await pool.request()
      .input('desc',     sql.VarChar(60), desc)
      .input('cod',      sql.VarChar(6),  body.codigo      ? String(body.codigo).substring(0, 6)      : null)
      .input('codN',     sql.Int,         body.codInterno  ? Number(body.codInterno)                  : null)
      .input('ofp',      sql.Char(1),     body.ofpartes === 'S' ? 'S' : 'N')
      .input('codNuevo', sql.VarChar(6),  body.codigoNuevo ? String(body.codigoNuevo).substring(0, 6) : null)
      .input('id',       sql.Int,         id)
      .query(`
        UPDATE dependencia SET
          desc_dependencia      = @desc,
          cod_dependencia       = @cod,
          cod_numinterno        = @codN,
          ofpartes              = @ofp,
          cod_dependencia_nuevo = @codNuevo
        WHERE id_dependencia = @id
      `);
    sendSuccess(res, { id, descripcion: desc }, 'Dependencia actualizada');
  } catch (e) { next(e); }
});

router.patch('/dependencias/:id/vigencia', ...soloPropietarios, async (req, res, next) => {
  try {
    const id      = Number(req.params.id);
    const vigencia = String(req.body?.vigencia ?? '').trim().toUpperCase();
    if (vigencia !== 'S' && vigencia !== 'N') { sendError(res, 'vigencia debe ser S o N', 400); return; }

    const pool = await getPool();
    await pool.request()
      .input('vigencia', sql.Char(1), vigencia)
      .input('id',       sql.Int,    id)
      .query('UPDATE dependencia SET vigencia = @vigencia WHERE id_dependencia = @id');
    sendSuccess(res, { id, vigencia }, vigencia === 'S' ? 'Servicio activado' : 'Servicio desactivado');
  } catch (e) { next(e); }
});

export default router;
