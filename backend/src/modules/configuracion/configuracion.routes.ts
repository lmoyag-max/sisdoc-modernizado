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
      nombreSistema: cfg.nombreSistema ?? 'SISDOC',
      nombreInstitucion: cfg.nombreInstitucion ?? 'HUAP',
      logoUrl: logoFile ? `/uploads/config/${logoFile}` : null,
      backgroundUrl: bgFile ? `/uploads/config/${bgFile}` : null,
      version: '2.0.0',
    },
  });
});

// ── PATCH /configuracion — actualizar nombres ───────────────
router.patch('/', requireAuth, (req, res) => {
  const { nombreSistema, nombreInstitucion } = req.body as { nombreSistema?: string; nombreInstitucion?: string };
  const cfg = readConfig();
  if (nombreSistema !== undefined) cfg.nombreSistema = nombreSistema;
  if (nombreInstitucion !== undefined) cfg.nombreInstitucion = nombreInstitucion;
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
