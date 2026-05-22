import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import compression from 'compression';
import cookieParser from 'cookie-parser';
import rateLimit from 'express-rate-limit';
import swaggerUi from 'swagger-ui-express';
import path from 'path';

import { env } from './config/env';
import { swaggerSpec } from './config/swagger';
import { requestLogger } from './middleware/logger.middleware';
import { errorHandler, notFoundHandler } from './middleware/error.middleware';

import { requireAuth } from './middleware/auth.middleware';
import authRoutes from './modules/auth/auth.routes';
import documentosRoutes from './modules/documentos/documento.routes';
import tramitesRoutes from './modules/tramites/tramite.routes';
import catalogosRoutes from './modules/catalogos/catalogos.routes';
import reportesRoutes from './modules/reportes/reportes.routes';
import archivosRoutes from './modules/archivos/archivos.routes';
import busquedaRoutes from './modules/busqueda/busqueda.routes';
import configuracionRoutes from './modules/configuracion/configuracion.routes';
import usuariosRoutes from './modules/usuarios/usuarios.routes';
import expedientesRoutes from './modules/expedientes/expedientes.routes';
import rolesRoutes       from './modules/roles/roles.routes';

const app = express();

// ── CORS: lista explícita de orígenes en todos los entornos ───────────────
app.use(cors({
  origin: (origin, callback) => {
    // Requests sin origin (curl, Postman, server-to-server) se permiten siempre
    if (!origin) return callback(null, true);
    const allowed = env.CORS_ORIGIN.split(',').map((s) => s.trim());
    if (allowed.includes('*') || allowed.some((o) => origin.startsWith(o))) {
      return callback(null, true);
    }
    callback(new Error(`CORS bloqueado: ${origin}`));
  },
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization'],
}));

// ── Seguridad ──────────────────────────────────────────────
app.use(helmet({
  crossOriginResourcePolicy: { policy: 'cross-origin' },
  // CSP desactivado para rutas API puras; se activa selectivamente abajo para UI
  contentSecurityPolicy: false,
}));

// CSP básico solo para rutas de UI (Swagger, health) — la API JSON no lo necesita
const uiCsp = helmet.contentSecurityPolicy({
  directives: {
    defaultSrc:  ["'self'"],
    scriptSrc:   ["'self'", "'unsafe-inline'"], // Swagger UI requiere inline scripts
    styleSrc:    ["'self'", "'unsafe-inline'"],
    imgSrc:      ["'self'", 'data:'],
    connectSrc:  ["'self'"],
    frameAncestors: ["'none'"],
  },
});

// Rate limiting — autenticación
const authLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: env.NODE_ENV === 'production' ? 20 : 100,
  message: { ok: false, error: 'Demasiados intentos. Intente en 15 minutos.' },
  standardHeaders: true,
  legacyHeaders: false,
});

// ── Parsers ────────────────────────────────────────────────
app.use(compression());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));
app.use(cookieParser());

// ── Logging ────────────────────────────────────────────────
app.use(requestLogger);

// ── Config files: público — usados en la página de login (logo, fondo) ────
app.use('/uploads/config', express.static(path.resolve(env.UPLOAD_DIR, 'config')));

// ── Archivos de documentos: requieren autenticación ────────────────────────
// path.basename() previene path traversal (ej. ../../etc/passwd)
app.get('/uploads/:filename', requireAuth, (req, res) => {
  const filename = path.basename(req.params.filename);
  const filePath = path.resolve(env.UPLOAD_DIR, filename);
  res.sendFile(filePath, (err) => {
    if (err) res.status(404).json({ ok: false, error: 'Archivo no encontrado' });
  });
});

// ── Health checks ─────────────────────────────────────────
// En producción solo devuelve { ok: true } — sin version ni entorno (fingerprinting)
const healthResponse = () =>
  env.NODE_ENV === 'production'
    ? { ok: true }
    : { ok: true, sistema: 'SISDOC API v2', version: '2.0.0', entorno: env.NODE_ENV, timestamp: new Date().toISOString() };
app.get('/health', uiCsp, (_req, res) => res.json(healthResponse()));
app.get('/api/health', uiCsp, (_req, res) => res.json(healthResponse())); // alias

// ── API Docs — solo en entornos no productivos ─────────────
if (env.NODE_ENV !== 'production') {
  app.use('/api-docs', uiCsp, swaggerUi.serve, swaggerUi.setup(swaggerSpec, {
    customCss: '.swagger-ui .topbar { display: none }',
    customSiteTitle: 'SISDOC API Docs',
  }));
}

// ── Rutas ──────────────────────────────────────────────────
const API = '/api/v1';

app.use(`${API}/auth`, authLimiter, authRoutes);
app.use(`${API}/documentos`, documentosRoutes);
app.use(`${API}/tramites`, tramitesRoutes);
app.use(`${API}/catalogos`, catalogosRoutes);
app.use(`${API}/reportes`, reportesRoutes);
app.use(`${API}/archivos`, archivosRoutes);
app.use(`${API}/busqueda`, busquedaRoutes);
app.use(`${API}/configuracion`, configuracionRoutes);
app.use(`${API}/usuarios`, usuariosRoutes);
app.use(`${API}/expedientes`, expedientesRoutes);
app.use(`${API}/roles`,       rolesRoutes);

app.get(`${API}`, (_req, res) => {
  res.json({ ok: true, api: 'SISDOC v2', docs: '/api-docs' });
});

// ── Error handlers ─────────────────────────────────────────
app.use(notFoundHandler);
app.use(errorHandler);

export default app;
