# 06 — Arquitectura de Modernización

**Fecha de análisis:** 2026-05-18  
**Estrategia:** Migración gradual (Strangler Fig Pattern)

---

## 1. Estrategia general: Strangler Fig

No se reescribe todo de una vez. Se reemplaza funcionalidad legacy módulo a módulo, mientras el sistema original sigue operando.

```
                    ┌─────────────────────────────────────┐
                    │           FASE ACTUAL               │
                    │                                     │
                    │  Legacy PHP ─── SQL Server 2005     │
                    │       │              │              │
                    │       └──── Backup ──┘              │
                    │                │                    │
                    │         SQL Server 2022             │
                    │         (Docker - Moderno)          │
                    └─────────────────────────────────────┘

                    ┌─────────────────────────────────────┐
                    │         ARQUITECTURA OBJETIVO       │
                    │                                     │
                    │  React SPA                          │
                    │     │                               │
                    │     ▼                               │
                    │  API Gateway (Node.js/Express)      │
                    │     │                               │
                    │     ├──► Auth Service (JWT)         │
                    │     ├──► Documentos Service         │
                    │     ├──► Tramites Service           │
                    │     ├──► Expedientes Service        │
                    │     ├──► Usuarios Service           │
                    │     └──► Notificaciones Service     │
                    │                │                    │
                    │         SQL Server 2022             │
                    └─────────────────────────────────────┘
```

---

## 2. Stack tecnológico objetivo

### 2.1 Backend

| Capa | Tecnología | Justificación |
|---|---|---|
| Runtime | Node.js 20 LTS | Moderno, alta concurrencia, JS unificado |
| Framework | Express.js | Ligero, extensible, compatible con middleware |
| ORM/Query | mssql + SQL nativo | Control fino de queries, compatible con SP legados |
| Autenticación | JWT + bcrypt | Estándar de industria, sin estado |
| Validación | Zod o Joi | Validación de schemas en entrada |
| Upload archivos | Multer | Manejo de multipart/form-data |
| Logging | Winston | Logs estructurados para producción |
| Tests | Jest + Supertest | Unit y integration tests |
| Documentación API | Swagger/OpenAPI | Documentación automática |

### 2.2 Frontend

| Capa | Tecnología | Justificación |
|---|---|---|
| Framework | React 18 | Ecosistema maduro, SPA moderna |
| Build | Vite | Rapidísimo en dev y build |
| Router | React Router v6 | Navegación SPA declarativa |
| Estado global | Zustand | Ligero, sin boilerplate |
| Servidor de datos | TanStack Query | Cache, re-fetch, loading states automáticos |
| UI Components | shadcn/ui + Tailwind CSS | Moderno, accesible, altamente personalizable |
| Iconos | Lucide React | Iconos SVG livianos y modernos |
| Formularios | React Hook Form + Zod | Validación de alto rendimiento |
| Gráficos | Recharts | Dashboards y métricas |
| Tablas | TanStack Table | Tablas potentes con filtros y paginación |
| Fechas | date-fns | Ligero, sin moment.js |
| Notificaciones | Sonner | Toast moderno y elegante |
| Temas | next-themes | Modo oscuro/claro |

### 2.3 Infraestructura

| Capa | Tecnología |
|---|---|
| Base de datos | SQL Server 2022 (Docker) |
| Contenedores | Docker + Docker Compose |
| Variables de entorno | dotenv (dev) / secretos de entorno (prod) |

---

## 3. Estructura de directorios propuesta

### 3.1 Backend (`/backend/`)

```
backend/
├── src/
│   ├── app.js               ← Express app factory
│   ├── server.js            ← Entry point (listen)
│   ├── config/
│   │   ├── database.js      ← Pool de conexiones mssql
│   │   └── env.js           ← Validación de variables de entorno
│   ├── middleware/
│   │   ├── auth.js          ← requireAuth middleware (JWT)
│   │   ├── validate.js      ← Validación de schemas Zod
│   │   ├── errorHandler.js  ← Manejador global de errores
│   │   └── logger.js        ← Request logging
│   ├── routes/
│   │   ├── auth.routes.js
│   │   ├── documentos.routes.js
│   │   ├── tramites.routes.js
│   │   ├── expedientes.routes.js
│   │   ├── usuarios.routes.js
│   │   ├── catalogos.routes.js
│   │   └── reportes.routes.js
│   ├── controllers/
│   │   ├── auth.controller.js
│   │   ├── documentos.controller.js
│   │   ├── tramites.controller.js
│   │   ├── expedientes.controller.js
│   │   └── usuarios.controller.js
│   ├── services/
│   │   ├── auth.service.js
│   │   ├── documentos.service.js
│   │   └── archivos.service.js
│   ├── schemas/             ← Validaciones Zod
│   │   ├── documento.schema.js
│   │   └── usuario.schema.js
│   └── utils/
│       ├── paginacion.js
│       └── respuesta.js     ← Formato estándar de respuesta API
├── uploads/                 ← Archivos subidos (en prod: S3/Azure Blob)
├── tests/
├── .env
└── package.json
```

### 3.2 Frontend (`/frontend/`)

```
frontend/
├── src/
│   ├── main.jsx
│   ├── App.jsx
│   ├── assets/
│   ├── components/
│   │   ├── ui/              ← Componentes base (shadcn/ui)
│   │   │   ├── Button.jsx
│   │   │   ├── Card.jsx
│   │   │   ├── Input.jsx
│   │   │   ├── Table.jsx
│   │   │   ├── Badge.jsx
│   │   │   ├── Modal.jsx
│   │   │   └── ...
│   │   ├── layout/
│   │   │   ├── Sidebar.jsx
│   │   │   ├── Header.jsx
│   │   │   ├── Footer.jsx
│   │   │   └── Layout.jsx
│   │   ├── documentos/
│   │   │   ├── DocumentoCard.jsx
│   │   │   ├── DocumentoTable.jsx
│   │   │   ├── DocumentoForm.jsx
│   │   │   └── DocumentoEstado.jsx
│   │   ├── tramites/
│   │   │   ├── TramiteItem.jsx
│   │   │   └── TramiteTimeline.jsx
│   │   ├── shared/
│   │   │   ├── SearchBar.jsx
│   │   │   ├── FilterPanel.jsx
│   │   │   ├── Pagination.jsx
│   │   │   ├── LoadingSpinner.jsx
│   │   │   └── EmptyState.jsx
│   │   └── dashboard/
│   │       ├── MetricCard.jsx
│   │       ├── DocumentosChart.jsx
│   │       └── ActividadReciente.jsx
│   ├── pages/
│   │   ├── Login.jsx
│   │   ├── Dashboard.jsx
│   │   ├── Documentos/
│   │   │   ├── ListaDocumentos.jsx
│   │   │   ├── NuevoDocumento.jsx
│   │   │   └── DetalleDocumento.jsx
│   │   ├── Tramites/
│   │   │   ├── MisTramites.jsx
│   │   │   └── DetalleTramite.jsx
│   │   ├── Expedientes/
│   │   │   ├── ListaExpedientes.jsx
│   │   │   └── DetalleExpediente.jsx
│   │   ├── Reportes.jsx
│   │   └── Admin/
│   │       ├── Usuarios.jsx
│   │       └── Configuracion.jsx
│   ├── hooks/
│   │   ├── useDocumentos.js    ← TanStack Query hooks
│   │   ├── useAuth.js
│   │   └── useTramites.js
│   ├── stores/
│   │   └── authStore.js        ← Zustand store
│   ├── api/
│   │   ├── client.js           ← Axios instance con interceptors
│   │   ├── documentos.api.js
│   │   ├── auth.api.js
│   │   └── tramites.api.js
│   ├── lib/
│   │   └── utils.js
│   └── styles/
│       └── globals.css
├── public/
├── index.html
├── vite.config.js
├── tailwind.config.js
└── package.json
```

---

## 4. Contrato de API REST

### 4.1 Formato de respuesta estándar

```json
{
  "ok": true,
  "data": { ... },
  "meta": {
    "pagina": 1,
    "total": 245,
    "por_pagina": 20
  }
}
```

### 4.2 Endpoints completos

```
AUTH
  POST   /api/auth/login
  POST   /api/auth/logout
  GET    /api/auth/me
  POST   /api/auth/refresh

DOCUMENTOS
  GET    /api/documentos                     (paginado, filtros)
  POST   /api/documentos                     (crear)
  GET    /api/documentos/:id                 (detalle)
  PATCH  /api/documentos/:id                 (actualizar)
  DELETE /api/documentos/:id                 (archivar)
  GET    /api/documentos/:id/historial
  GET    /api/documentos/:id/tramites
  POST   /api/documentos/:id/derivar

TRÁMITES
  GET    /api/tramites                       (mis trámites)
  GET    /api/tramites/:id
  PATCH  /api/tramites/:id/recibir
  PATCH  /api/tramites/:id/cerrar

EXPEDIENTES
  GET    /api/expedientes
  POST   /api/expedientes
  GET    /api/expedientes/:id
  POST   /api/expedientes/:id/documentos

ARCHIVOS
  POST   /api/archivos                       (multipart upload)
  GET    /api/archivos/:id/download
  DELETE /api/archivos/:id

CATÁLOGOS (cacheable)
  GET    /api/catalogos/tipos-documento
  GET    /api/catalogos/estados
  GET    /api/catalogos/dependencias
  GET    /api/catalogos/descriptores
  GET    /api/catalogos/prioridades

USUARIOS (admin)
  GET    /api/usuarios
  POST   /api/usuarios
  GET    /api/usuarios/:id
  PATCH  /api/usuarios/:id
  PATCH  /api/usuarios/:id/accesos

REPORTES
  GET    /api/reportes/dashboard
  GET    /api/reportes/documentos-por-estado
  GET    /api/reportes/tramites-pendientes
  GET    /api/reportes/actividad-mensual

BÚSQUEDA
  GET    /api/busqueda?q=texto&tipo=...      (full-text search)
```

---

## 5. Plan de migración gradual (fases)

### Fase 0: Base (COMPLETADA)
- [x] Docker con SQL Server 2022
- [x] Base de datos SISDOC restaurada
- [x] Backend Node.js básico
- [x] Frontend React diagnóstico

### Fase 1: Autenticación (2-3 semanas)
- [ ] Endpoint POST /api/auth/login con JWT
- [ ] Página de login moderna en React
- [ ] Contexto de autenticación (AuthContext/Zustand)
- [ ] Middleware requireAuth
- [ ] Migración gradual de contraseñas (legacy → bcrypt)

### Fase 2: Dashboard y lectura de datos (2-3 semanas)
- [ ] Endpoint GET /api/reportes/dashboard
- [ ] Dashboard principal con métricas
- [ ] Listado de documentos con paginación y filtros
- [ ] Detalle de documento
- [ ] Historial del documento

### Fase 3: CRUD completo de documentos (3-4 semanas)
- [ ] Formulario de ingreso de nuevo documento
- [ ] Derivación de documentos
- [ ] Gestión de estados
- [ ] Upload de archivos digitales
- [ ] Búsqueda full-text

### Fase 4: Trámites y expedientes (2-3 semanas)
- [ ] Mis trámites pendientes
- [ ] Aceptar / Cerrar trámites
- [ ] Gestión de expedientes
- [ ] Timeline de historial

### Fase 5: Administración (2-3 semanas)
- [ ] CRUD de usuarios
- [ ] Gestión de accesos y roles
- [ ] Configuración del sistema
- [ ] Logs de auditoría

### Fase 6: Funciones avanzadas (4-6 semanas)
- [ ] Notificaciones en tiempo real (WebSocket o polling)
- [ ] Sistema de alertas
- [ ] Reportes y gráficos avanzados
- [ ] Búsqueda full-text avanzada
- [ ] Módulos especializados (OIRS, Gabinete, Electoral)

### Fase 7: Calidad y producción
- [ ] Tests unitarios e integración
- [ ] Documentación Swagger
- [ ] CI/CD pipeline
- [ ] Hardening de seguridad
- [ ] Optimización de rendimiento

---

## 6. Decisiones arquitectónicas clave

| Decisión | Elección | Alternativa descartada | Razón |
|---|---|---|---|
| Monorepo vs separado | Monorepo (`/backend` `/frontend`) | Repos separados | Más fácil de gestionar en fase inicial |
| REST vs GraphQL | REST | GraphQL | Menor complejidad, más familiar |
| ORM vs SQL nativo | SQL nativo + mssql | Prisma/TypeORM | Compatibilidad total con SP legados |
| Estado global | Zustand | Redux | Menos boilerplate, más simple |
| UI Kit | shadcn/ui | Material UI / Ant Design | Más control visual, sin dependencia de vendor |
| Autenticación | JWT stateless | Sesiones en servidor | Escalable, sin estado compartido |
| Archivos | Filesystem local → migrar a blob | Solo filesystem | Pragmático ahora, escalable después |
