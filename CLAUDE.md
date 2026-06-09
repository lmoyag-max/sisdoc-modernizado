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
- **Credenciales dev:** `sa` / `<DB_PASSWORD>`
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

**Usuarios del sistema (contraseña actualizada 2026-05-22):**

| Usuario | Contraseña | Rol | id_usuario |
|---------|-----------|-----|-----------|
| `admin` | `Huap.2025` | Administrador | 532 |
| `ti` | `Huap.2025` | Funcionario | 1535 |
| `aba` | `Huap.2025` | Funcionario | 1536 |
| `contrato` | `Huap.2025` | Funcionario | 1537 |
| `ofparte` | `Huap.2025` | Of. de Partes | 2535 |

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
│   │   │   ├── auth/                 # Login, refresh, logout, /me, forgot/reset password
│   │   │   ├── documentos/           # CRUD documentos + derivar + historial
│   │   │   ├── tramites/             # Bandeja + recibir + cerrar
│   │   │   ├── archivos/             # Upload multer + listado + delete
│   │   │   ├── usuarios/             # CRUD usuarios + roles
│   │   │   ├── catalogos/            # Tipos de doc, estados, dependencias
│   │   │   ├── busqueda/             # Búsqueda global docs/tramites/funcionarios
│   │   │   ├── reportes/             # Dashboard + actividad + exportar CSV
│   │   │   ├── configuracion/        # Logo, background login, nombres sistema, upload rules
│   │   │   ├── memorandum/           # Correlativos + generación PDF con firma/timbre
│   │   │   ├── jefaturas/            # Titular/subrogante + imagen firma por dependencia
│   │   │   ├── firma-gob/            # Integración FirmaGOB (ambientes TEST/PRODUCCION)
│   │   │   ├── alertas/              # Configuración SMTP + horarios + envío manual
│   │   │   └── roles/                # Gestión de módulos por rol
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
│   │   │   ├── auth/ForgotPasswordPage.tsx
│   │   │   ├── auth/ResetPasswordPage.tsx
│   │   │   ├── dashboard/DashboardPage.tsx
│   │   │   ├── documentos/
│   │   │   │   ├── DocumentosPage.tsx       # Listado paginado con filtros
│   │   │   │   ├── DocumentoDetallePage.tsx # Detalle individual (/documentos/:id)
│   │   │   │   └── NuevoDocumentoPage.tsx   # Formulario creación + upload múltiple
│   │   │   ├── bandeja/BandejaPage.tsx
│   │   │   ├── enviados/EnviadosPage.tsx
│   │   │   ├── tramites/TramitesPage.tsx
│   │   │   ├── trazabilidad/TrazabilidadPage.tsx
│   │   │   ├── busqueda/BusquedaPage.tsx
│   │   │   ├── archivos/ArchivosPage.tsx
│   │   │   ├── reportes/ReportesPage.tsx
│   │   │   ├── admin/
│   │   │   │   ├── UsuariosPage.tsx
│   │   │   │   ├── RolesPage.tsx
│   │   │   │   ├── JefaturasPage.tsx
│   │   │   │   ├── FirmaGobPage.tsx
│   │   │   │   └── AlertasPage.tsx
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
| `/reportes` | `ReportesPage` | Dashboard métricas + exportar CSV |
| `/admin/usuarios` | `UsuariosPage` | CRUD usuarios |
| `/admin/roles` | `RolesPage` | Gestión de módulos por rol |
| `/admin/jefaturas` | `JefaturasPage` | Titular/subrogante + imagen firma/timbre |
| `/admin/firma-gob` | `FirmaGobPage` | Configuración integración FirmaGOB |
| `/admin/alertas` | `AlertasPage` | Configuración alertas y logs SMTP |
| `/admin/configuracion` | `ConfiguracionPage` | Logo, fondo, nombres, reglas upload |

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
GET    /               → pública — { nombreSistema, nombreInstitucion, logoUrl, backgroundUrl, version, uploadRules }
PATCH  /               → requireAuth + requireRole('admin') — { nombreSistema?, nombreInstitucion?, textos login... }
POST   /logo           → requireAuth + requireRole('admin') — multipart/form-data: archivo
POST   /background     → requireAuth + requireRole('admin') — multipart/form-data: archivo
PATCH  /upload-rules   → requireAuth + requireRole('admin') — { extensionesPermitidas, maxFileMB, maxTotalMB }
GET    /tipos-documento         → lista con campo vigencia
PATCH  /tipos-documento/:id/vigencia
GET    /dependencias            → lista con campo vigencia
PATCH  /dependencias/:id/vigencia
```

### Búsqueda (`/api/v1/busqueda`) — requireAuth
```
GET    /?q=&tipo=documentos|tramites|funcionarios|todos&pagina=
```

### Catálogos (`/api/v1/catalogos`) — requireAuth
```
GET    /tipos-documento
GET    /estados              ← nombre real (NO /estados-documento)
GET    /prioridades
GET    /dependencias
GET    /tipos-distribucion
GET    /tipos-compromiso
GET    /estados-compromiso
```

### Memorándum (`/api/v1/memorandum`) — requireAuth
```
GET    /firmante-activo        → { disponible, firmante? } — firmante de la dependencia del usuario
GET    /firmantes-disponibles  → titular + subrogante con estado de imagen
POST   /confirmar              → { idDocumento, materia?, referencia?, cuerpo?, idFirmante? }
                                 → asigna correlativo MEMO-YYYY-NNNNNN y genera PDF si hay firma/timbre
PATCH  /vincular-archivo       → { idMemo, idArchivoDigital }
GET    /firmantes              → requireRole('admin') — lista todos los firmantes
GET    /firmantes/:id          → requireRole('admin')
POST   /firmantes              → requireRole('admin') — crear firmante
POST   /firmantes/:id/imagen   → requireRole('admin') — subir imagen firma+timbre
```
**Requisito operacional:** Para generar PDF de memorándum, el firmante de la jefatura debe tener imagen
subida vía `/admin/jefaturas` → botón "Subir firma y timbre". Sin imagen: correlativo se asigna pero PDF no se genera.
Correlativo actual: MEMO-2026-000010 (próximo: MEMO-2026-000011).

### Jefaturas (`/api/v1/jefaturas`) — requireAuth
```
GET    /                       → lista de jefaturas con titular y subrogante por dependencia
```
Administración completa en `/admin/jefaturas` (UI). Fuente de verdad para firmantes de memorándum.

### Firma GOB (`/api/v1/firma-gob`) — requireAuth
```
GET    /config                 → requireRole('admin') — configuración ambientes TEST/PRODUCCION
PATCH  /config/:ambiente       → requireRole('admin') — { url_api, entity, purpose, api_token_key, jwt_secret }
POST   /config/:ambiente/limpiar-secreto → requireRole('admin')
GET    /historial              → requireRole('admin') — historial de firmas electrónicas
POST   /test-conexion          → requireRole('admin') — prueba conexión con FirmaGOB
POST   /solicitar              → requireAuth — solicitar firma electrónica para un documento
```
**Estado actual:** Integración no configurada. Ambientes TEST y PRODUCCION sin credenciales.
Configurar en `/admin/firma-gob` antes de usar firma electrónica institucional.

### Alertas (`/api/v1/alertas`) — requireAuth + requireRole('admin')
```
GET    /configuracion          → { activo, horarios }
PUT    /configuracion          → { activo, horarios[] }
GET    /pendientes             → alertas pendientes de envío
GET    /destinatarios          → destinatarios configurados
GET    /logs                   → historial de envíos
POST   /enviar-manual          → disparo manual inmediato
POST   /enviar-todos           → enviar todas las alertas pendientes
POST   /probar-servicio/:id    → prueba de conectividad SMTP
```

### Roles (`/api/v1/roles`) — requireAuth
```
GET    /                       → lista de roles con módulos asignados
```
Administración de módulos por rol en `/admin/roles` (UI).

### Reportes (`/api/v1/reportes`) — requireAuth
```
GET    /dashboard              → requireModule('dashboard') — totales + porEstado + porMes + porTipo
                                 Funcionario ve sus documentos; admin ve todos
GET    /actividad-reciente     → requireModule('dashboard') — últimas 15 acciones
GET    /exportar               → requireModule('reportes') — CSV con BOM para Excel
                                 Solo accesible a usuarios con módulo 'reportes' (admin por defecto)
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
- `password_reset_tokens` — id, id_usuario, token_hash (SHA256, VARCHAR 64), fecha_creacion, fecha_expiracion, usado (BIT), fecha_uso, ip_solicitud, user_agent
- `auditoria_reset` — id, evento, id_usuario, email, ip, user_agent, detalle, fecha

### Usuarios en BD
| Usuario | id_usuario | Email configurado |
|---------|-----------|-------------------|
| `admin` | 532 | arturo.moya@redsalud.gov.cl |
| `ti` | 1535 | lmoyag@gmail.com |
| `aba` | 1536 | operaciones@huap.online |
| `ofparte` | 2535 | operaciones.huap@redsalud.gob.cl |
| `contrato` | 1537 | (sin email — no puede usar recuperación) |

- Los 477 usuarios legacy fueron eliminados (backup en `usuario_backup_2026`)
- La columna `email` en `usuario` es `VARCHAR(100) NULL` — si está vacía, el flujo de recuperación no puede enviar correo a ese usuario

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
- **Reglas configurables:** extensiones permitidas, `maxFileMB` y `maxTotalMB` se guardan en `uploads/config/sistema.json`; el hook `useUploadRules()` los expone al frontend con defaults seguros si faltan
- **Hardcap absoluto:** `MAX_FILE_SIZE` en `.env` es el límite máximo de multer — la configuración de UI solo puede restringir por debajo de ese valor, nunca superarlo

---

## Variables de entorno (`backend/.env`)

```env
NODE_ENV=development
PORT=3001

DB_USER=doc360_app              # Usuario de aplicación — NOT sa (db_owner en SISDOC, sin sysadmin)
DB_PASSWORD=<DB_PASSWORD>
DB_SERVER=localhost
DB_PORT=11433                   # Puerto 1433 reservado por Windows/Hyper-V → mapeado como 127.0.0.1:11433:1433
DB_DATABASE=SISDOC
DB_TRUST_CERT=true
DB_ENCRYPT=false                # En producción: DB_ENCRYPT=true, DB_TRUST_CERT=false + cert válido

JWT_SECRET=<512-bit random>     # Generar: openssl rand -base64 64
JWT_REFRESH_SECRET=<512-bit random>
JWT_EXPIRES_IN=15m
JWT_REFRESH_EXPIRES_IN=7d

CORS_ORIGIN=http://localhost:5173,http://10.6.15.182:5173

UPLOAD_DIR=./uploads
MAX_FILE_SIZE=20971520          # Hardcap multer — UI solo puede restringir por debajo

# Recuperación de contraseña por email
SMTP_HOST=mail.huap.online
SMTP_PORT=465
SMTP_SECURE=true
SMTP_USER=operaciones@huap.online
SMTP_PASS=<SMTP_PASSWORD>
SMTP_FROM=DOC360 HUAP <operaciones@huap.online>
FRONTEND_URL=http://localhost:5173
RESET_TOKEN_EXPIRES_MINUTES=30
```

---

## Comandos útiles

```powershell
# Ver contenedor SQL Server
docker ps

# Ejecutar query SQL directa (usar -U sa solo para tareas de admin; app usa doc360_app)
docker exec sisdoc_sqlserver /opt/mssql-tools18/bin/sqlcmd `
  -S localhost -U sa -P "<SA_PASSWORD>" -C -d SISDOC -Q "SELECT TOP 5 * FROM documento"

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
| `ESOCKET` al conectar BD | Docker no corriendo, BD iniciando, o puerto no expuesto al host | `docker compose up -d sqlserver` + esperar 15s. **Nota:** el puerto del host es `11433` (no 1433 — reservado por Windows) |
| Login falla con "Error al iniciar sesión" sin detalle | Backend no corriendo — el frontend no distingue `ECONNREFUSED` de `401` | Verificar que backend está up: `curl http://localhost:3001/api/health` |
| `"datos inválidos"` al crear doc | Schema backend esperaba campos distintos | Ya corregido: schema usa `materia` + `idEstadoDocumento` |
| `Cannot read properties of undefined (reading 'descripcion')` | `mapDocumento` no retornaba `destino`/`prioridad` | Ya corregido: incluir esos campos con `null` |
| Archivos subidos no aparecen en BD | `ruta` varchar(50) overflow con filename largo | Ya corregido: filenames cortos de 12 chars |
| `App.jsx` carga en lugar de `App.tsx` | Vite resuelve .jsx antes | `main.tsx` importa `from './App.tsx'` explícitamente |
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

## Estado actual del sistema (Junio 2026)

Auditado funcionalmente el 2026-06-09. Correcciones de seguridad aplicadas.

### Módulos funcionales ✅
- Login + JWT + refresh automático + recuperación de contraseña por email
- Dashboard con métricas reales, gráficos y actividad reciente (filtrado por servicio)
- Documentos: listado paginado, detalle, crear, derivar, historial
- Bandeja de entrada con paginación y estado
- Enviados
- Trámites
- Trazabilidad documental
- Búsqueda global (documentos, trámites, funcionarios)
- Archivos: upload múltiple + listado + descarga + asociar a documento
- Memorándum: correlativos MEMO-YYYY-NNNNNN, generación PDF con firma/timbre
- Jefaturas: titular + subrogante + imagen firma/timbre por dependencia
- Usuarios: CRUD + asignación de roles
- Roles: gestión de módulos por rol
- Alertas: configuración SMTP, horarios, envío manual
- Reportes: métricas, gráficos, exportar CSV (filtrado por servicio)
- Configuración: logo, fondo login, nombres del sistema, reglas de carga configurables
- FirmaGOB: módulo implementado, pendiente de configuración de credenciales

### Requiere configuración operacional
- **Memorándum PDF:** subir imagen de firma+timbre en `/admin/jefaturas`
- **FirmaGOB:** configurar URL y credenciales en `/admin/firma-gob`

### Pendiente / mejoras futuras
- Notificaciones en tiempo real (WebSocket)
- Modo oscuro
- Export a PDF en reportes
- Tests automatizados (Vitest + Supertest)
- CI/CD pipeline

---

## Cambios técnicos — registro de modificaciones

### [2026-05-27] Carga múltiple de archivos en creación de documento

**Problema detectado:** El formulario "Nuevo documento" solo permitía adjuntar un archivo por creación. El `input[type=file]` carecía del atributo `multiple`, el estado era `File | null`, y el drag-and-drop tomaba solo el primer archivo (`files[0]`).

**Causa raíz:** Implementación inicial conservadora con estado `File | null` y `upload.single`. No había validación de cuota total ni lista de archivos seleccionados.

**Archivos modificados:**
- `frontend/src/pages/documentos/NuevoDocumentoPage.tsx` — único archivo modificado

**Cambios realizados:**

| Elemento | Antes | Después |
|----------|-------|---------|
| Estado | `File \| null` | `File[]` |
| Input | sin `multiple` | `multiple` |
| Drag-drop | `files[0]` | `agregarArchivos(files)` |
| Validación | ninguna | por archivo + cuota total |
| Lista UI | archivo único o zona vacía | lista con remove por item + barra de progreso |
| Upload | un `FormData` | loop secuencial, un `FormData` por archivo |
| Error manejo | bloquea todo | por archivo: continúa y avisa con `toast.warning` |

**Regla de cuota:**
- Límite individual: `MAX_FILE_MB = 20` MB — rechaza al intentar agregar; no se añade a la lista.
- Cuota total: `CUOTA_TOTAL_MB = 60` MB — se muestra inline en la lista; deshabilita el botón "Registrar y Despachar".
- Constantes definidas fuera del componente para edición fácil.

**Compatibilidad:**
- El backend `/archivos/upload` no se modificó — sigue usando `upload.single('archivo')`. El campo enviado sigue siendo `'archivo'`, idéntico al flujo de `AdjuntarArchivoModal`.
- Documentos creados anteriormente con un solo archivo no se ven afectados.
- Si la creación del documento es exitosa pero algún archivo falla al subir, se muestra `toast.warning` y se navega al documento; los archivos pueden re-adjuntarse desde el detalle.

**Pruebas funcionales a validar:**
1. Crear documento sin archivo — flujo normal, sin cambios
2. Crear documento con 1 archivo — comportamiento igual que antes
3. Crear documento con 2+ archivos — todos quedan asociados
4. Seleccionar PDF + Word + Excel + imagen — todos se suben
5. Intentar agregar archivo > 20 MB — toast de error, no se agrega a la lista
6. Agregar archivos hasta superar 60 MB total — barra roja + mensaje + botón deshabilitado
7. Quitar archivo de la lista antes de registrar — se elimina de `archivos[]`, no se sube
8. Verificar descarga posterior de cada adjunto desde el detalle
9. Verificar trazabilidad: cada archivo genera un evento estado 7 ("Archivo adjuntado")
10. Verificar que despachar / recepcionar / terminar siguen funcionando

---

### [2026-05-27] Reglas de carga configurables desde módulo Configuración

**Motivación:** Los límites de tamaño y extensiones de archivos estaban hardcodeados en múltiples componentes y en `MAX_FILE_SIZE` del `.env`. Se necesitaba un control centralizado desde la UI de administración sin modificar código ni reiniciar el servidor.

**Almacenamiento:** Tres campos nuevos opcionales en `backend/uploads/config/sistema.json` (archivo JSON plano ya existente):
- `uploadExtensionesPermitidas` — `string[]` — ej: `["pdf","doc","docx","png"]`
- `uploadMaxFileMB` — `number` — máximo por archivo individual
- `uploadMaxTotalMB` — `number` — cuota total por operación de carga múltiple

Si los campos no existen (sistema nuevo o config sin actualizar) se aplican defaults seguros: `['pdf','doc','docx','xls','xlsx','png','jpg','jpeg','webp']`, 20 MB, 60 MB.

**Nuevo endpoint:**
```
PATCH  /api/v1/configuracion/upload-rules   → requireAuth
Body: { extensionesPermitidas: string[], maxFileMB: number, maxTotalMB: number }
```
Validaciones: array no vacío, `maxFileMB` entre 1–100, `maxTotalMB >= maxFileMB`, extensiones del conjunto permitido. Ruta registrada antes de `PATCH /` para evitar conflicto de rutas en Express.

**Nuevo hook frontend:** `frontend/src/hooks/useUploadRules.ts`
- `useUploadRules()` — retorna `UploadRules` con `extensionesPermitidas`, `maxFileMB`, `maxTotalMB`
- `staleTime: 5 * 60 * 1000`, `retry: false` — tolerante a fallos; jamás bloquea la UI
- En el primer render retorna `UPLOAD_RULES_DEFAULTS` síncronamente antes de que la query resuelva
- Todos los componentes de upload lo consumen en lugar de constantes locales

**Archivos modificados:**

| Archivo | Cambio |
|---------|--------|
| `backend/src/modules/configuracion/configuracion.routes.ts` | GET `/` expone `uploadRules` en la respuesta; nuevo `PATCH /upload-rules` |
| `backend/src/modules/archivos/archivos.routes.ts` | `getUploadRules()` helper + validación ADITIVA en `POST /upload` |
| `frontend/src/hooks/useUploadRules.ts` | **NUEVO** — hook central |
| `frontend/src/pages/configuracion/ConfiguracionPage.tsx` | Sección "Reglas de carga": toggles de extensiones + inputs MB |
| `frontend/src/pages/documentos/NuevoDocumentoPage.tsx` | Constantes derivadas del hook; `agregarArchivos()` valida extensión |
| `frontend/src/components/documentos/AdjuntarArchivoModal.tsx` | Hint text dinámico desde hook |
| `frontend/src/pages/archivos/ArchivosPage.tsx` | Hint text dinámico desde hook |

**Arquitectura de validación (por capas, aditiva):**
1. **multer `fileFilter`** — extensiones permitidas por defecto del servidor (primera barrera, siempre activa)
2. **multer `limits.fileSize`** — `MAX_FILE_SIZE` del `.env` (hardcap absoluto, no configurable desde UI)
3. **`getUploadRules()` en `POST /upload`** — restricciones adicionales desde `sistema.json` (pueden ser más estrictas que el paso 1, nunca más permisivas que él)
4. **`useUploadRules()` en el frontend** — validación client-side; evita round-trips innecesarios; no reemplaza la validación server-side

**Compatibilidad garantizada:**
- Todos los flows de upload preexistentes (crear documento, adjuntar desde detalle, subir desde módulo Archivos) siguen funcionando sin cambios en su lógica de negocio.
- Si `sistema.json` no tiene los campos de upload, el comportamiento es idéntico al anterior.
- El admin puede restringir pero nunca ampliar más allá del hardcap del `.env`.

**Pruebas funcionales a validar:**
1. Acceder a Configuración → sección "Reglas de carga" aparece con valores por defecto
2. Desactivar extensión `.xlsx` → guardar → intentar subir `.xlsx` → debe ser rechazado en todos los puntos de upload
3. Reducir `maxFileMB` a 5 → guardar → intentar subir archivo de 10 MB → rechazado
4. `maxTotalMB` no puede ser menor que `maxFileMB` — botón Guardar deshabilitado si inválido
5. Con todas las extensiones activas y defaults, el comportamiento es igual al pre-feature
6. Corromper `sistema.json` manualmente → el sistema usa defaults y no rompe
7. Verificar que los hint texts en los tres puntos de upload muestran la configuración activa
