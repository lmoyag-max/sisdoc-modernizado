import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import compression from 'compression';
import cookieParser from 'cookie-parser';
import rateLimit from 'express-rate-limit';
import swaggerUi from 'swagger-ui-express';

import { env } from './config/env';
import { swaggerSpec } from './config/swagger';
import { requestLogger } from './middleware/logger.middleware';
import { errorHandler, notFoundHandler } from './middleware/error.middleware';

import authRoutes from './modules/auth/auth.routes';
import documentosRoutes from './modules/documentos/documento.routes';
import tramitesRoutes from './modules/tramites/tramite.routes';
import catalogosRoutes from './modules/catalogos/catalogos.routes';
import reportesRoutes from './modules/reportes/reportes.routes';

const app = express();

// ── Seguridad ──────────────────────────────────────────────
app.use(helmet({ crossOriginResourcePolicy: { policy: 'cross-origin' } }));
app.use(cors({
  origin: env.CORS_ORIGIN,
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization'],
}));

// Rate limiting en autenticación
const authLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 20,
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

// ── Health check ───────────────────────────────────────────
app.get('/health', (_req, res) => {
  res.json({
    ok: true,
    sistema: 'SISDOC API v2',
    version: '2.0.0',
    entorno: env.NODE_ENV,
    timestamp: new Date().toISOString(),
  });
});

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

// Ruta raíz de la API para compatibilidad backward
app.get(`${API}`, (_req, res) => {
  res.json({ ok: true, api: 'SISDOC v2', docs: '/api-docs' });
});

// ── Error handlers ─────────────────────────────────────────
app.use(notFoundHandler);
app.use(errorHandler);

export default app;
