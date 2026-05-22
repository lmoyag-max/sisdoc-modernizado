import { Router } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { requireAuth } from '../../middleware/auth.middleware';
import { env } from '../../config/env';

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
    const allowed = /\.(png|jpg|jpeg|svg|webp)$/i;
    cb(null, allowed.test(file.originalname));
  },
});

// Textos del login con sus valores por defecto
const LOGIN_DEFAULTS = {
  loginNombreSistema:   'SISDOC',
  loginSubtitulo:       'Sistema de Gestión Documental',
  loginTituloPrincipal: 'Gestión documental moderna',
  loginDescripcion:     'Plataforma enterprise para la gestión, seguimiento y trazabilidad de documentos institucionales.',
  loginCard1:           'Gestión documental',
  loginCard2:           'Flujo de derivaciones',
  loginCard3:           'Trazabilidad completa',
  loginCard4:           'Historial documental',
  loginFooter:          '© 2026 SISDOC v2.0 — Sistema institucional de gestión documental',
};

// ── GET /configuracion — leer config completa (pública) ────
router.get('/', (req, res) => {
  const cfg = readConfig();
  const logoFile = ['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.svg', 'logo.webp']
    .find((f) => fs.existsSync(path.join(CONFIG_DIR, f)));
  const bgFile = ['background.jpg', 'background.jpeg', 'background.png', 'background.webp']
    .find((f) => fs.existsSync(path.join(CONFIG_DIR, f)));

  res.json({
    ok: true,
    data: {
      nombreSistema:     cfg.nombreSistema     ?? 'SISDOC',
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

export default router;
