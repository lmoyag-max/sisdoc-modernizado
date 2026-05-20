# SISDOC Modernizado — Guía completa para Claude Code

## Descripción del proyecto

Modernización del sistema legacy SISDOC (Sistema de Gestión Documental) del HUAP (Hospital Universitario Asociado de Puebla). El sistema original corría en Windows Server 2003 / ASP clásico / SQL Server 2005. La nueva versión es una plataforma SaaS enterprise moderna.

**Regla absoluta:** NUNCA modificar nada dentro de `/legacy`. Solo crear código nuevo en `/backend` y `/frontend`.

---

## Stack tecnológico

### Backend
- **Runtime:** Node.js 20 + TypeScript 5.7
- **Framework:** Express 4
- **ORM/DB driver:** mssql v12.5.4 (queries directas — NO Prisma activo)
- **Autenticación:** JWT (access 15 min) + refresh token (7 días, httpOnly cookie)
- **Passwords:** bcrypt 12 rounds — migración gradual desde texto plano legacy
- **Validación:** Zod
- **Upload archivos:** multer (diskStorage)
- **Logs:** Winston + daily rotate
- **Dev hot-reload:** `tsx watch`

### Frontend
- **Framework:** React 18 + Vite 6
- **Lenguaje:** TypeScript 5.7
- **Estilos:** TailwindCSS 3 + shadcn/ui (Radix UI primitives)
- **Data fetching:** TanStack Query v5 (useQuery, useMutation, keepPreviousData)
- **Estado global:** Zustand v5
- **Router:** React Router v6
- **Forms:** react-hook-form + zodResolver
- **Gráficos:** Recharts
- **Toasts:** Sonner
- **HTTP:** Axios con interceptores de auto-refresh JWT
- **Iconos:** Lucide React

### Base de datos
- **Motor:** SQL Server 2022 (contenedor Docker)
- **Contenedor:** `sisdoc_sqlserver`
- **Volumen persistente:** `sisdoc_sqlserver_data` → `/var/opt/mssql/data`
- **Credenciales dev:** `sa` / `Adminhuap2026!`
- **Base de datos:** `SISDOC`

### Infraestructura
- **Docker Compose** con perfil `prod` para backend + nginx
- **Desarrollo:** solo `docker compose up -d sqlserver`
- **Producción:** `docker compose --profile prod up -d --build`

---

## Levantar el sistema en desarrollo

```powershell
# 1. SQL Server (Docker)
docker compose up -d sqlserver

# 2. Backend (terminal separada)
cd backend
npm run dev        # tsx watch — hot reload en puerto 3001

# 3. Frontend (terminal separada)
cd frontend
npm run dev        # Vite — hot reload en puerto 5173
```

**URLs de desarrollo:**
- Frontend: http://localhost:5173 (o http://10.6.15.182:5173 en red local)
- Backend API: http://localhost:3001/api/v1
- API Docs (Swagger): http://localhost:3001/api-docs
- Health check: http://localhost:3001/api/health

**Usuario de prueba:**
- Usuario: `admin`
- Contraseña: `admin`
- Rol: `admin` (Administrador del sistema)
- id_usuario: 532

---

## Estructura de carpetas

```
sisdoc-modernizado/
├── backend/
│   ├── src/
│   │   ├── app.ts                    # Express app, CORS, middlewares, rutas
│   │   ├── server.ts                 # Entry point, bind 0.0.0.0:3001
│   │   ├── config/
│   │   │   ├── database.ts           # Pool mssql, getPool()
│   │   │   ├── env.ts                # Variables de entorno validadas con Zod
│   │   │   └── swagger.ts
│   │   ├── middleware/
│   │   │   ├── auth.middleware.ts    # requireAuth (JWT verify)
│   │   │   ├── validate.middleware.ts # Zod schema validation
│   │   │   ├── error.middleware.ts
│   │   │   └── logger.middleware.ts
│   │   ├── modules/
│   │   │   ├── auth/                 # Login, refresh, logout, /me
│   │   │   ├── documentos/           # CRUD documentos + derivar + historial
│   │   │   ├── tramites/             # Bandeja + recibir + cerrar
│   │   │   ├── archivos/             # Upload multer + listado + delete
│   │   │   ├── expedientes/          # CRUD expedientes legacy
│   │   │   ├── usuarios/             # CRUD usuarios + roles
│   │   │   ├── catalogos/            # Tipos de doc, estados, dependencias
│   │   │   ├── busqueda/             # Búsqueda global docs/tramites/funcionarios
│   │   │   ├── reportes/             # Dashboard + actividad + exportar CSV
│   │   │   └── configuracion/        # Logo, background login, nombres sistema
│   │   ├── shared/
│   │   │   ├── types/api.types.ts    # AuthenticatedRequest, JwtPayload, etc.
│   │   │   └── utils/response.ts     # sendSuccess, sendError, sendPaginated
│   │   └── types/mssql.d.ts         # Declaraciones manuales (mssql v12 sin types)
│   ├── uploads/                      # Archivos subidos (NO en git)
│   │   └── config/                   # Logo y fondo login
│   ├── .env                          # Variables de entorno (NO en git)
│   └── package.json
├── frontend/
│   ├── src/
│   │   ├── App.tsx                   # Root con QueryClientProvider + RouterProvider
│   │   ├── main.tsx                  # Entry point (importa App.tsx explícitamente)
│   │   ├── App.jsx                   # Alias legacy → re-exporta App.tsx
│   │   ├── app/
│   │   │   ├── router.tsx            # createBrowserRouter con todas las rutas
│   │   │   └── providers.tsx
│   │   ├── components/
│   │   │   ├── layout/
│   │   │   │   ├── Layout.tsx        # Shell: Sidebar + Header + <Outlet>
│   │   │   │   ├── Sidebar.tsx       # Nav colapsable con sidebar-gradient
│   │   │   │   └── Header.tsx        # Barra superior con búsqueda y avatar
│   │   │   ├── ui/                   # shadcn/ui: Button, Card, Badge, Input...
│   │   │   ├── shared/
│   │   │   │   ├── ProtectedRoute.tsx # Redirige a /login si no hay token
│   │   │   │   └── EmptyState.tsx
│   │   │   └── dashboard/
│   │   │       └── MetricCard.tsx
│   │   ├── pages/
│   │   │   ├── auth/LoginPage.tsx
│   │   │   ├── dashboard/DashboardPage.tsx
│   │   │   ├── documentos/
│   │   │   │   ├── DocumentosPage.tsx       # Listado paginado con filtros
│   │   │   │   ├── DocumentoDetallePage.tsx # Detalle individual (/documentos/:id)
│   │   │   │   └── NuevoDocumentoPage.tsx   # Formulario creación
│   │   │   ├── bandeja/BandejaPage.tsx
│   │   │   ├── enviados/EnviadosPage.tsx
│   │   │   ├── tramites/TramitesPage.tsx
│   │   │   ├── trazabilidad/TrazabilidadPage.tsx
│   │   │   ├── busqueda/BusquedaPage.tsx
│   │   │   ├── archivos/ArchivosPage.tsx
│   │   │   ├── expedientes/ExpedientesPage.tsx
│   │   │   ├── reportes/ReportesPage.tsx
│   │   │   ├── admin/
│   │   │   │   └── UsuariosPage.tsx
│   │   │   └── configuracion/ConfiguracionPage.tsx
│   │   ├── lib/
│   │   │   ├── api/
│   │   │   │   ├── client.ts         # Axios instance + interceptores JWT refresh
│   │   │   │   ├── auth.api.ts
│   │   │   │   ├── documentos.api.ts
│   │   │   │   ├── catalogos.api.ts
│   │   │   │   └── reportes.api.ts
│   │   │   └── utils.ts             # cn(), formatFechaHora(), formatRelativo(), iniciales()
│   │   ├── stores/
│   │   │   └── auth.store.ts        # Zustand: user, accessToken, setAuth, logout
│   │   ├── hooks/
│   │   │   └── useDebounce.ts
│   │   └── styles/
│   │       └── globals.css          # Paleta CSS vars + sidebar-gradient + glass + skeleton
│   └── package.json
├── database/
│   └── scripts/
│       ├── 01-backup-docs.sql
│       ├── 02-clean-and-seed.sql
│       ├── 03-optimize-indexes.sql
│       └── 04-create-admin-user.sql
├── legacy/                           # NUNCA MODIFICAR
├── docker-compose.yml
└── CLAUDE.md
```

---

## Rutas del router (frontend)

| Path | Componente | Descripción |
|------|-----------|-------------|
| `/login` | `LoginPage` | Pública |
| `/dashboard` | `DashboardPage` | Métricas, gráficos, actividad |
| `/documentos` | `DocumentosPage` | Listado paginado con filtros |
| `/documentos/nuevo` | `NuevoDocumentoPage` | Formulario + drag-drop archivo |
| `/documentos/:id` | `DocumentoDetallePage` | Detalle + historial + archivos |
| `/bandeja` | `BandejaPage` | Trámites pendientes |
| `/enviados` | `EnviadosPage` | Documentos enviados |
| `/tramites` | `TramitesPage` | Mis trámites asignados |
| `/trazabilidad` | `TrazabilidadPage` | Timeline documental |
| `/busqueda` | `BusquedaPage` | Búsqueda global |
| `/archivos` | `ArchivosPage` | Gestión de archivos digitales |
| `/expedientes` | `ExpedientesPage` | Expedientes + vinculación docs |
| `/reportes` | `ReportesPage` | Dashboard métricas + exportar CSV |
| `/admin/usuarios` | `UsuariosPage` | CRUD usuarios + roles |
| `/admin/configuracion` | `ConfiguracionPage` | Logo, fondo, nombres |

---

## API REST — endpoints

### Autenticación (`/api/v1/auth`)
```
POST   /login          → { user, accessToken }  — clave en texto plano (legacy) o bcrypt
POST   /refresh        → { accessToken }         — usa httpOnly cookie
POST   /logout
GET    /me             → UserSession
```

### Documentos (`/api/v1/documentos`) — requireAuth
```
GET    /               → lista paginada (q, idTipo, idEstado, fechaDesde, fechaHasta)
POST   /               → { materia, idTipoDocumento, idEstadoDocumento?, fechaDocumento?, observaciones? }
GET    /:id            → detalle documento
GET    /:id/historial  → tramites del documento
POST   /:id/derivar    → { idDependenciaDestino, observacion? }
```

### Trámites (`/api/v1/tramites`) — requireAuth
```
GET    /               → lista paginada (idEstado, pagina)
PATCH  /:id/recibir    → cambia estado a 2
PATCH  /:id/cerrar     → cambia estado a 3
```

### Archivos (`/api/v1/archivos`) — requireAuth
```
POST   /upload         → multipart/form-data: archivo + idDocumento
GET    /               → lista (idDocumento?)
DELETE /:id            → elimina registro y archivo físico
```

### Expedientes (`/api/v1/expedientes`) — requireAuth
```
GET    /               → lista paginada (q)
POST   /               → { descripcion }
GET    /:id/documentos → docs del expediente
PATCH  /vincular       → { idDocumento, idExpediente }
```

### Usuarios (`/api/v1/usuarios`) — requireAuth
```
GET    /               → lista paginada (q)
GET    /:id
POST   /               → { usuario, clave, nombres, apellidos, idDependencia?, roles? }
PATCH  /:id            → { nombres?, apellidos?, clave?, roles?, idDependencia? }
DELETE /:id
GET    /meta/roles     → roles disponibles
```

### Reportes (`/api/v1/reportes`) — requireAuth
```
GET    /dashboard      → totales + porEstado + porMes + porTipo
GET    /actividad-reciente
GET    /exportar       → CSV download con BOM para Excel
```

### Configuración (`/api/v1/configuracion`)
```
GET    /               → pública — { nombreSistema, nombreInstitucion, logoUrl, backgroundUrl, version }
PATCH  /               → requireAuth — { nombreSistema?, nombreInstitucion? }
POST   /logo           → requireAuth — multipart/form-data: archivo
POST   /background     → requireAuth — multipart/form-data: archivo
```

### Búsqueda (`/api/v1/busqueda`) — requireAuth
```
GET    /?q=&tipo=documentos|tramites|funcionarios|todos&pagina=
```

### Catálogos (`/api/v1/catalogos`) — requireAuth
```
GET    /tipos-documento
GET    /estados-documento
GET    /dependencias
```

---

## Base de datos — tablas críticas

### Columnas reales (difieren del esquema esperado — IMPORTANTE)

**`documento`** — columnas NOT NULL que el INSERT debe incluir:
- `id_tipo_documento` INT NOT NULL
- `id_estado_documento` INT NOT NULL
- `id_usuario` INT NOT NULL
- `num_interno` INT NOT NULL — se calcula como `MAX(num_interno) + 1`
- `num_oficial` INT NOT NULL — se calcula como `MAX(num_oficial) + 1`
- `num_externo` INT NOT NULL — se inserta con valor 0
- `original` VARCHAR(1) NOT NULL — se inserta como `'S'`
- `materia` VARCHAR(250) NOT NULL
- `fecha_documento` DATETIME NOT NULL
- `fecha_sistema` DATETIME NOT NULL
- `fecha_update` DATETIME NOT NULL

**`funcionario`** — columnas NOT NULL:
- `rut` VARCHAR(8), `dig` VARCHAR(1), `nombres` VARCHAR(30), `apellidos` VARCHAR(30)
- `id_dependencia` INT NOT NULL
- `id_funcionario` es IDENTITY (no especificar en INSERT)

**`usuario`**:
- `usuario` VARCHAR(10) — máx 10 chars
- `clave` VARCHAR(10) — texto plano legacy, máx 10 chars
- `clave_hash` VARCHAR(255) — bcrypt, columna nueva añadida
- `id_funcionario` INT
- `tipo_alertas` CHAR(1)

**`expediente`** — nombres reales de columnas:
- `desc_expediente` CHAR(100) NOT NULL — NO `descripcion`
- `fecha_expediente` DATETIME NOT NULL — NO `fecha_sistema`
- `tipo_expediente` INT nullable

**`archivo_digital`** — varchar(50) en columnas críticas:
- `archivo` VARCHAR(50) — nombre original truncado a 50 chars
- `ruta` VARCHAR(50) — filename corto (ej: `87328552.pdf` = 12 chars)

**`tramite`** — PK es `id_seguimiento` (NO `id_tramite`):
- `id_seguimiento` IDENTITY
- `observaciones` (plural)
- `fecha_sistema`
- `id_usuario` / `id_destino`

### Tablas nuevas creadas en este proyecto
- `rol` — id_rol, codigo, nombre, activo
- `usuario_rol` — id_usuario, id_rol (FK compuesta)
- `refresh_token` — id, token, id_usuario, expires_at, revoked_at, created_at

### Usuarios en BD
- Solo 1 usuario activo: `admin` (id_usuario=532, id_funcionario=675)
- Los 477 usuarios legacy fueron eliminados (backup en `usuario_backup_2026`)

---

## Autenticación — flujo

1. `POST /auth/login` con `{ usuario, clave }`
2. Backend busca usuario, verifica password:
   - Si tiene `clave_hash` → `bcrypt.compare()`
   - Si no → comparación directa texto plano (legacy)
3. En login exitoso: guarda hash bcrypt en `clave_hash` (migración gradual)
4. Retorna `{ accessToken (JWT 15min), user }` + `refreshToken` en httpOnly cookie
5. Frontend guarda `accessToken` en Zustand store
6. Axios interceptor agrega `Authorization: Bearer <token>` a cada request
7. Si 401 → interceptor intenta `POST /auth/refresh` con la cookie
8. Si refresh falla → `logout()` + redirect a `/login`

---

## Upload de archivos — consideraciones

- Directorio: `backend/uploads/` (debe existir, NO está en git)
- Config logo/background: `backend/uploads/config/`
- Multer genera filenames cortos: `${timestamp_8_chars}.${ext}` — ej: `87328552.pdf`
- Razón: `archivo_digital.ruta` y `archivo_digital.archivo` son VARCHAR(50) en BD legacy
- Archivos servidos como estático: `GET /uploads/{filename}`
- En producción: nginx sirve `/uploads` directamente desde volumen Docker

---

## Variables de entorno (`backend/.env`)

```env
NODE_ENV=development
PORT=3001
DB_USER=sa
DB_PASSWORD=Adminhuap2026!
DB_SERVER=localhost
DB_PORT=1433
DB_DATABASE=SISDOC
DB_TRUST_CERT=true
DB_ENCRYPT=false
JWT_SECRET=sisdoc-jwt-secret-2026-enterprise-key-cambia-en-produccion
JWT_REFRESH_SECRET=sisdoc-refresh-secret-2026-enterprise-key-cambia-en-produccion
JWT_EXPIRES_IN=15m
JWT_REFRESH_EXPIRES_IN=7d
CORS_ORIGIN=http://localhost:5173
UPLOAD_DIR=./uploads
MAX_FILE_SIZE=20971520
```

---

## Comandos útiles

```powershell
# Ver contenedor SQL Server
docker ps

# Ejecutar query SQL directa
docker exec sisdoc_sqlserver /opt/mssql-tools18/bin/sqlcmd \
  -S localhost -U sa -P "Adminhuap2026!" -C -d SISDOC -Q "SELECT TOP 5 * FROM documento"

# Reiniciar backend (si hay cambios en .env)
# Ctrl+C en la terminal del backend, luego: npm run dev

# Ver logs en tiempo real
# La salida de tsx watch ya muestra logs de winston en consola

# Build frontend para producción
cd frontend && npm run build

# Verificar tipos TypeScript
cd backend && npm run typecheck
cd frontend && npm run typecheck
```

---

## Errores conocidos y sus soluciones

| Error | Causa | Solución |
|-------|-------|---------|
| `ESOCKET` al conectar BD | Docker no corriendo o BD iniciando | `docker compose up -d sqlserver` + esperar 15s |
| `"datos inválidos"` al crear doc | Schema backend esperaba campos distintos | Ya corregido: schema usa `materia` + `idEstadoDocumento` |
| `Cannot read properties of undefined (reading 'descripcion')` | `mapDocumento` no retornaba `destino`/`prioridad` | Ya corregido: incluir esos campos con `null` |
| Archivos subidos no aparecen en BD | `ruta` varchar(50) overflow con filename largo | Ya corregido: filenames cortos de 12 chars |
| `App.jsx` carga en lugar de `App.tsx` | Vite resuelve .jsx antes | `main.tsx` importa `from './App.tsx'` explícitamente |
| Error al crear expediente | Columnas `desc_expediente` / `fecha_expediente` (no `descripcion` / `fecha_sistema`) | Ya corregido en `expedientes.routes.ts` |
| mssql TypeScript sin types | mssql v12 no incluye `.d.ts` | `src/types/mssql.d.ts` con declaraciones manuales |

---

## Convenciones de código

- **Backend:** módulo por feature en `src/modules/{nombre}/`; un archivo `{nombre}.routes.ts` como entry point
- **Frontend:** página por ruta en `src/pages/{seccion}/`; hooks de queries inline en el componente o extraídos si se reusan
- **API responses:** siempre `{ ok: boolean, data?, error?, message? }` vía `sendSuccess()` / `sendError()`
- **Paginación:** siempre `{ data: [], meta: { total, pagina, porPagina, totalPaginas } }` vía `sendPaginated()`
- **Autenticación:** `router.use(requireAuth)` al inicio de cada módulo protegido
- **Validación:** `validate(schema)` middleware antes del handler; schema en archivo `.schema.ts` separado
- **No comentarios obvios:** solo comentar WHY no WHAT

---

## Estado actual del sistema (Mayo 2026)

### Módulos funcionales ✅
- Login + JWT + refresh automático
- Dashboard con métricas reales y gráficos
- Documentos: listado, detalle, crear
- Bandeja de entrada con paginación
- Enviados
- Trámites
- Trazabilidad documental
- Búsqueda global
- Archivos: upload + listado + descarga + asociar a documento
- Expedientes: listado (19,373 registros legacy) + crear + documentos del expediente
- Usuarios: CRUD + asignación de roles
- Reportes: métricas, gráficos, exportar CSV
- Configuración: logo, fondo login, nombres del sistema

### Pendiente / mejoras futuras
- Módulo de derivación de documentos (formulario en detalle documento)
- Notificaciones en tiempo real (WebSocket)
- Modo oscuro
- Branding dinámico (logo en sidebar desde configuración)
- Export a PDF en reportes
- Tests automatizados (Jest/Vitest)
