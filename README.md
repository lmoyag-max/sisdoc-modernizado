# SISDOC v2 — Sistema de Gestión Documental Moderno

Modernización gradual del sistema legacy SISDOC usando arquitectura enterprise moderna.

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Node.js 20 + Express + TypeScript |
| ORM | Prisma + mssql (SQL Server) |
| Frontend | React 18 + TypeScript + Vite |
| UI | Tailwind CSS + shadcn/ui |
| Estado | Zustand + TanStack Query |
| Auth | JWT (access 15m + refresh 7d) |
| Base de datos | SQL Server 2022 (Docker) |

## Inicio rápido (desarrollo)

### 1. Prerrequisitos
- Node.js 20+
- Docker Desktop corriendo
- SQL Server SISDOC ya restaurado en Docker

### 2. Setup inicial (una sola vez)
```powershell
.\scripts\setup.ps1
```

### 3. Desarrollo diario
```powershell
.\scripts\dev.ps1
```

O manualmente:
```powershell
# Terminal 1 — SQL Server
docker compose up -d sqlserver

# Terminal 2 — Backend
cd backend && npm run dev

# Terminal 3 — Frontend
cd frontend && npm run dev
```

### 4. URLs
| Servicio | URL |
|---|---|
| Frontend | http://localhost:5173 |
| Backend API | http://localhost:3001/api/v1 |
| API Docs | http://localhost:3001/api-docs |
| Health check | http://localhost:3001/health |

## Estructura del proyecto

```
sisdoc-modernizado/
├── legacy/          ← Sistema original (NO MODIFICAR)
├── backend/         ← API Node.js + TypeScript + Prisma
│   ├── src/
│   │   ├── config/      # Env, DB, Swagger
│   │   ├── middleware/  # Auth, errores, logging
│   │   ├── modules/     # auth, documentos, tramites, catalogos, reportes
│   │   └── shared/      # Types, utils
│   ├── prisma/          # Schema Prisma
│   └── uploads/         # Archivos subidos
├── frontend/        ← React + TypeScript + Tailwind
│   └── src/
│       ├── app/         # Router + Providers
│       ├── components/  # UI, layout, shared, dashboard
│       ├── pages/       # Login, Dashboard, Documentos, Tramites
│       ├── lib/         # API client + endpoints
│       ├── stores/      # Zustand (auth)
│       └── hooks/       # Custom hooks
├── docker/          ← Dockerfiles
├── nginx/           ← Config Nginx (producción)
├── scripts/         ← Scripts PS1 de arranque
├── docs/            ← Documentación técnica
│   └── analisis-claude/ ← Análisis del sistema legacy
└── database/        ← Backups SQL Server
```

## API REST

Base URL: `http://localhost:3001/api/v1`

### Autenticación
```
POST /auth/login          # Iniciar sesión
POST /auth/refresh        # Renovar access token
POST /auth/logout         # Cerrar sesión
GET  /auth/me             # Usuario actual
```

### Documentos
```
GET    /documentos        # Listar (paginado + filtros)
POST   /documentos        # Crear
GET    /documentos/:id    # Detalle
GET    /documentos/:id/historial
POST   /documentos/:id/derivar
```

### Otros
```
GET /tramites             # Mis trámites
GET /catalogos/*          # Catálogos (tipos, estados, dependencias...)
GET /reportes/dashboard   # Métricas para el dashboard
GET /reportes/actividad-reciente
```

## Prisma y base de datos

```bash
# Sincronizar schema con DB existente
cd backend && npm run prisma:pull

# Generar cliente Prisma (después de cambios al schema)
npm run prisma:generate

# Explorar DB visualmente
npm run prisma:studio
```

> **Nota:** El schema en `prisma/schema.prisma` mapea las tablas legacy de SISDOC.
> Ejecuta `prisma db pull` para verificar que los tipos y nombres coincidan exactamente.

## Migración gradual

La estrategia es Strangler Fig — el sistema legacy sigue operando mientras se construye el nuevo:

1. **Fase 1** (actual) — Auth + Dashboard + Documentos básico
2. **Fase 2** — CRUD completo, derivaciones, búsqueda
3. **Fase 3** — Expedientes, archivos digitales, notificaciones
4. **Fase 4** — Módulos especializados (OIRS, Gabinete, Alertas)
5. **Fase 5** — Reportes avanzados, firma digital, CI/CD

## Reglas del proyecto

1. **NUNCA** modificar archivos en `/legacy`
2. **NUNCA** eliminar archivos existentes
3. Código nuevo solo en `/backend` y `/frontend`
4. Documentar antes de implementar módulos críticos
5. No romper el sistema legacy en producción

## Documentación

Ver `/docs/analisis-claude/` para análisis completo del sistema legacy:
- [01-estructura-sistema.md](docs/analisis-claude/01-estructura-sistema.md)
- [02-login-autenticacion.md](docs/analisis-claude/02-login-autenticacion.md)
- [03-flujo-documental.md](docs/analisis-claude/03-flujo-documental.md)
- [04-tablas-principales.md](docs/analisis-claude/04-tablas-principales.md)
- [06-arquitectura-modernizacion.md](docs/analisis-claude/06-arquitectura-modernizacion.md)
