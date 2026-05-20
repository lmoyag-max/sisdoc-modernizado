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

const app = express();

// ── CORS: permitir localhost + red local ───────────────────
app.use(cors({
  origin: (origin, callback) => {
    // En desarrollo: permitir cualquier origen (red local incluida)
    if (env.NODE_ENV !== 'production') return callback(null, true);
    // En producción: validar contra lista de orígenes permitidos
    const allowed = env.CORS_ORIGIN.split(',').map((s) => s.trim());
    if (!origin || allowed.includes('*') || allowed.some((o) => origin.startsWith(o))) {
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
  contentSecurityPolicy: false, // Desactivado para API
}));

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

// ── Archivos estáticos (uploads públicos) ──────────────────
app.use('/uploads', express.static(path.resolve(env.UPLOAD_DIR)));

// ── Health checks ─────────────────────────────────────────
const healthResponse = () => ({
  ok: true,
  sistema: 'SISDOC API v2',
  version: '2.0.0',
  entorno: env.NODE_ENV,
  timestamp: new Date().toISOString(),
});
app.get('/health', (_req, res) => res.json(healthResponse()));
app.get('/api/health', (_req, res) => res.json(healthResponse())); // alias

// ── API Docs ───────────────────────────────────────────────
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerSpec, {
  customCss: '.swagger-ui .topbar { display: none }',
  customSiteTitle: 'SISDOC API Docs',
}));

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

app.get(`${API}`, (_req, res) => {
  res.json({ ok: true, api: 'SISDOC v2', docs: '/api-docs' });
});

// ── Error handlers ─────────────────────────────────────────
app.use(notFoundHandler);
app.use(errorHandler);

export default app;
