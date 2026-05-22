# INFORME DE ANÁLISIS — SISDOC MODERNIZADO
**Fecha:** 22 de mayo 2026 | **Rama:** main | **Analista:** Claude Code

---

## A. RESUMEN EJECUTIVO

SISDOC Modernizado es una plataforma SaaS de gestión documental del HUAP (Hospital Universitario Asociado de Puebla), construida como reemplazo del sistema legacy (Windows Server 2003 / ASP clásico / SQL Server 2005).

**Estado actual:** Sistema funcional en desarrollo. Todos los módulos core están implementados. El sistema gestiona el ciclo de vida completo de documentos institucionales: ingreso, despacho, recepción, derivación, cierre y trazabilidad.

**Tecnología:** Node.js 20 + Express 4 (backend) · React 18 + Vite 6 (frontend) · SQL Server 2022 (DB) · Docker Compose (infraestructura).

**Cobertura:** 15 módulos funcionales, 47 endpoints REST, 15 rutas frontend, 12 tablas principales en BD.

**Usuarios activos en BD:** 1 (admin). Los 477 usuarios legacy fueron migrados a backup.

---

## B. ARQUITECTURA GENERAL

```
┌─────────────────────────────────────────────────────────────┐
│  CLIENTE                                                     │
│  Browser → React 18 + Vite (puerto 5173 dev / 80 prod)      │
│  ├─ TanStack Query v5  (fetching + cache)                    │
│  ├─ Zustand v5         (estado global: auth)                 │
│  ├─ React Router v6    (SPA routing)                         │
│  └─ Axios              (HTTP + auto-refresh JWT)             │
└───────────────────────┬─────────────────────────────────────┘
                        │ HTTP / JSON
                        │ Authorization: Bearer <JWT>
                        ▼
┌─────────────────────────────────────────────────────────────┐
│  BACKEND (puerto 3001)                                       │
│  Express 4 + TypeScript 5.7                                  │
│  ├─ Middleware: CORS, Helmet, Rate-limit, Compression        │
│  ├─ Auth: JWT (15m access) + httpOnly cookie (7d refresh)    │
│  ├─ Validación: Zod schemas                                  │
│  ├─ Upload: Multer → ./uploads/                              │
│  ├─ Logs: Winston + daily rotate                             │
│  └─ Docs: Swagger UI → /api-docs                             │
└───────────────────────┬─────────────────────────────────────┘
                        │ mssql v12 (pool max:20)
                        ▼
┌─────────────────────────────────────────────────────────────┐
│  BASE DE DATOS                                               │
│  SQL Server 2022 (contenedor Docker — puerto 1433)           │
│  Base: SISDOC · 12+ tablas · 19,373 expedientes legacy       │
└─────────────────────────────────────────────────────────────┘

PRODUCCIÓN (docker compose --profile prod):
  nginx:80 → /api/v1 → backend:3001 → sqlserver:1433
             /uploads → volumen persistente
```

### Flujo de autenticación

```
Login → POST /auth/login → { accessToken, user } + cookie (refreshToken)
      → Axios adjunta Bearer en cada request
      → 401 → interceptor → POST /auth/refresh → nuevo accessToken
      → fallo → logout() → redirect /login
```

---

## C. MAPA DE MÓDULOS

| Módulo Frontend | Backend Módulo | Tablas Principales |
|----------------|----------------|-------------------|
| Login / Auth | auth | usuario, funcionario, refresh_token |
| Dashboard | reportes | documento, tramite, expediente |
| Documentos | documentos | documento, tramite, tipo_documento |
| Bandeja entrada | tramites | tramite, documento, dependencia |
| Enviados | tramites | tramite, documento, dependencia |
| Mis Trámites | tramites | tramite, documento |
| Trazabilidad | documentos | documento, tramite |
| Búsqueda | busqueda | documento, tramite, funcionario |
| Archivos | archivos | archivo_digital, documento |
| Expedientes | expedientes | expediente, documento |
| Usuarios | usuarios | usuario, funcionario, usuario_rol, rol |
| Roles | roles | rol, rol_modulo, usuario_rol |
| Configuración | configuracion | sistema.json (archivo) |
| Reportes | reportes | documento, tramite, expediente |
| Recuperar contraseña | auth | usuario (reset_token) |

---

## D. TABLA DE RUTAS FRONTEND

| URL | Componente | Módulo Guard | Descripción | Auth | Roles |
|-----|-----------|-------------|-------------|------|-------|
| `/login` | `LoginPage` | — | Autenticación | No | — |
| `/forgot-password` | `ForgotPasswordPage` | — | Solicitar reset de contraseña | No | — |
| `/reset-password` | `ResetPasswordPage` | — | Nueva contraseña con token | No | — |
| `/` | `Navigate` | — | Redirige a `/dashboard` | Sí | — |
| `/dashboard` | `DashboardPage` | `dashboard` | Métricas, gráficos, actividad reciente | Sí | Todos |
| `/documentos` | `DocumentosPage` | `documentos` | Listado paginado con filtros | Sí | Todos |
| `/documentos/nuevo` | `NuevoDocumentoPage` | `documentos` | Crear documento + adjuntar archivo | Sí | Todos |
| `/documentos/:id` | `DocumentoDetallePage` | `documentos` | Detalle + historial + archivos + acciones | Sí | Todos |
| `/bandeja` | `BandejaPage` | `bandeja` | Trámites pendientes de recepción | Sí | Todos |
| `/enviados` | `EnviadosPage` | `enviados` | Documentos enviados por mi dependencia | Sí | Todos |
| `/tramites` | `TramitesPage` | `tramites` | Mis trámites asignados | Sí | Todos |
| `/trazabilidad` | `TrazabilidadPage` | `trazabilidad` | Timeline documental (seguimiento histórico) | Sí | Todos |
| `/busqueda` | `BusquedaPage` | `busqueda` | Búsqueda global en docs/trámites/funcionarios | Sí | Todos |
| `/archivos` | `ArchivosPage` | `archivos` | Gestión de archivos digitales adjuntos | Sí | Todos |
| `/expedientes` | `ExpedientesPage` | `expedientes` | CRUD expedientes + vincular documentos | Sí | Admin+ |
| `/admin/usuarios` | `UsuariosPage` | `usuarios` | CRUD usuarios + asignación de roles | Sí | Admin |
| `/admin/roles` | `RolesPage` | `roles` | CRUD roles + asignación de módulos | Sí | Admin |
| `/admin/configuracion` | `ConfiguracionPage` | `configuracion` | Logo, fondo login, nombres del sistema | Sí | Admin |
| `/reportes` | `ReportesPage` | `reportes` | Métricas + gráficos + exportar CSV | Sí | Admin+ |
| `*` | `NotFoundPage` | — | Página 404 | No | — |

**Mecanismo de protección:**
- `ProtectedRoute` — verifica `isAuthenticated` en store Zustand; redirige a `/login` si no hay sesión
- `ModuleGuard` — verifica `puede(modulo)` via `useModulos()`; admin siempre pasa; redirige a `/dashboard` con toast de error si sin acceso

---

## E. TABLA DE ENDPOINTS BACKEND

### Auth — `/api/v1/auth`

| Endpoint | Método | Archivo | Auth | Permisos | Función |
|----------|--------|---------|------|----------|---------|
| `/auth/login` | POST | `auth.routes.ts` | No | — | Login con bcrypt/legacy, retorna JWT + cookie refresh |
| `/auth/refresh` | POST | `auth.routes.ts` | Cookie | — | Renueva accessToken con httpOnly cookie |
| `/auth/logout` | POST | `auth.routes.ts` | JWT | — | Revoca refresh token |
| `/auth/me` | GET | `auth.routes.ts` | JWT | — | Retorna sesión actual del usuario |
| `/auth/request-reset` | POST | `auth.routes.ts` | No | — | Envía email con token de reset |
| `/auth/reset-password` | POST | `auth.routes.ts` | No | — | Cambia contraseña con token |

### Documentos — `/api/v1/documentos`

| Endpoint | Método | Auth | Roles | Función |
|----------|--------|------|-------|---------|
| `/documentos` | GET | JWT | Todos | Lista paginada con filtros: q, idTipo, idEstado, idDependencia, fechas |
| `/documentos/buscar-por-numero` | GET | JWT | Todos | Búsqueda por num_interno / num_oficial |
| `/documentos/:id` | GET | JWT | Todos | Detalle documento con metadatos |
| `/documentos/:id/historial` | GET | JWT | Todos | Trámites del documento |
| `/documentos/:id/trazabilidad` | GET | JWT | Todos | Timeline completo del documento |
| `/documentos` | POST | JWT | Todos | Crear documento + primer trámite |
| `/documentos/:id/despachar` | POST | JWT | admin, of.partes, supervisores | Despachar a destino |
| `/documentos/:id/recepcionar` | POST | JWT | Todos | Recepcionar trámite entrante |
| `/documentos/:id/derivar` | POST | JWT | admin, of.partes, supervisores | Re-derivar a otro destino |
| `/documentos/:id/terminar` | POST | JWT | admin, of.partes, supervisores, funcionario | Cerrar documento |
| `/documentos/:id/reabrir` | POST | JWT | admin, supervisores | Re-abrir documento cerrado |
| `/documentos/:id` | DELETE | JWT | admin | Eliminar documento (solo admin) |

### Trámites — `/api/v1/tramites`

| Endpoint | Método | Auth | Función |
|----------|--------|------|---------|
| `/tramites` | GET | JWT | Bandeja entrada: trámites pendientes de mi dependencia |
| `/tramites/enviados` | GET | JWT | Documentos enviados desde mi dependencia |
| `/tramites/:id/recibir` | PATCH | JWT | Recepcionar (estado → 3, registra fecha + usuario) |
| `/tramites/:id/cerrar` | PATCH | JWT | Cerrar trámite (estado → 5) |

### Archivos — `/api/v1/archivos`

| Endpoint | Método | Auth | Función |
|----------|--------|------|---------|
| `/archivos/upload` | POST | JWT | Upload multipart/form-data + registro en BD |
| `/archivos` | GET | JWT | Lista archivos (filtro por idDocumento) |
| `/archivos/:id/preview` | GET | JWT | Sirve archivo inline para preview |
| `/archivos/:id/download` | GET | JWT | Descarga forzada (Content-Disposition: attachment) |
| `/archivos/:id` | DELETE | JWT | Elimina registro BD + archivo físico |

### Expedientes — `/api/v1/expedientes`

| Endpoint | Método | Auth | Permisos | Función |
|----------|--------|------|----------|---------|
| `/expedientes` | GET | JWT | módulo: expedientes | Lista paginada + búsqueda |
| `/expedientes` | POST | JWT | módulo: expedientes | Crear expediente |
| `/expedientes/:id/documentos` | GET | JWT | módulo: expedientes | Docs vinculados al expediente |
| `/expedientes/vincular` | PATCH | JWT | módulo: expedientes | Vincular documento a expediente |

### Usuarios — `/api/v1/usuarios`

| Endpoint | Método | Auth | Permisos | Función |
|----------|--------|------|----------|---------|
| `/usuarios` | GET | JWT | módulo: usuarios | Lista paginada + búsqueda |
| `/usuarios/:id` | GET | JWT | módulo: usuarios | Detalle usuario |
| `/usuarios` | POST | JWT | módulo: usuarios | Crear usuario + funcionario + roles |
| `/usuarios/:id` | PATCH | JWT | módulo: usuarios | Actualizar nombres, clave, roles, email |
| `/usuarios/:id` | DELETE | JWT | módulo: usuarios | Eliminar usuario + registros usuario_rol |
| `/usuarios/meta/roles` | GET | JWT | módulo: usuarios | Roles disponibles para asignar |

### Catálogos — `/api/v1/catalogos`

| Endpoint | Método | Auth | Función |
|----------|--------|------|---------|
| `/catalogos/tipos-documento` | GET | JWT | Tipos de documento |
| `/catalogos/estados` | GET | JWT | Estados de documento |
| `/catalogos/estados-tramite` | GET | JWT | Estados de trámite (1–5) |
| `/catalogos/prioridades` | GET | JWT | Prioridades |
| `/catalogos/dependencias` | GET | JWT | Dependencias internas |
| `/catalogos/dependencias-externas` | GET | JWT | Dependencias externas |
| `/catalogos/funcionarios` | GET | JWT | Todos los funcionarios |
| `/catalogos/dependencias/:id/funcionarios` | GET | JWT | Funcionarios de una dependencia |
| `/catalogos/tipos-distribucion` | GET | JWT | Tipos de distribución |
| `/catalogos/tipos-compromiso` | GET | JWT | Tipos de compromiso |
| `/catalogos/estados-compromiso` | GET | JWT | Estados de compromiso |
| `/catalogos/descriptores` | GET | JWT | Descriptores |

### Búsqueda — `/api/v1/busqueda`

| Endpoint | Método | Auth | Función |
|----------|--------|------|---------|
| `/busqueda?q=&tipo=` | GET | JWT | Búsqueda global: documentos, trámites, funcionarios |

### Reportes — `/api/v1/reportes`

| Endpoint | Método | Auth | Permisos | Función |
|----------|--------|------|----------|---------|
| `/reportes/dashboard` | GET | JWT | módulo: dashboard | Totales + por estado + por mes + por tipo |
| `/reportes/actividad-reciente` | GET | JWT | módulo: dashboard | Últimos 15 trámites del sistema |
| `/reportes/exportar` | GET | JWT | módulo: reportes | Descarga CSV con BOM (UTF-8 + Excel) |

### Configuración — `/api/v1/configuracion`

| Endpoint | Método | Auth | Función |
|----------|--------|------|---------|
| `/configuracion` | GET | **No** | Config pública: nombre, logo, fondo, textos login |
| `/configuracion` | PATCH | JWT | Actualizar nombreSistema, textos login |
| `/configuracion/logo` | POST | JWT | Upload logo (PNG/JPG/SVG/WEBP — 5MB) |
| `/configuracion/background` | POST | JWT | Upload fondo de login |

### Roles — `/api/v1/roles`

| Endpoint | Método | Auth | Permisos | Función |
|----------|--------|------|----------|---------|
| `/roles` | GET | JWT | rol: admin | Lista roles con módulos |
| `/roles/:id` | GET | JWT | rol: admin | Detalle rol |
| `/roles` | POST | JWT | rol: admin | Crear rol + asignar módulos |
| `/roles/:id` | PATCH | JWT | rol: admin | Actualizar nombre, módulos, activo |
| `/roles/:id` | DELETE | JWT | rol: admin | Desactiva si tiene usuarios, sino borra |
| `/roles/meta/modulos` | GET | JWT | rol: admin | Módulos disponibles para asignar |

---

## F. MAPA DE BASE DE DATOS

### Diagrama de relaciones

```
funcionario (id_funcionario PK)
  ├── rut VARCHAR(8), dig VARCHAR(1)
  ├── nombres VARCHAR(30), apellidos VARCHAR(30)
  ├── id_dependencia INT NOT NULL → dependencia
  └── vigencia CHAR(1)

usuario (id_usuario PK)
  ├── usuario VARCHAR(10) UNIQUE NOT NULL
  ├── clave VARCHAR(10)            ← texto plano legacy
  ├── clave_hash VARCHAR(255)      ← bcrypt (nuevo)
  ├── id_funcionario → funcionario
  ├── email VARCHAR(100)
  ├── todos_servicios BIT
  └── tipo_alertas CHAR(1)

rol (id_rol PK)
  ├── codigo VARCHAR(50) UNIQUE
  ├── nombre VARCHAR(100)
  └── activo BIT

usuario_rol (PK compuesta)
  ├── id_usuario → usuario
  └── id_rol → rol

rol_modulo
  ├── id_rol → rol
  └── modulo VARCHAR(50)

refresh_token
  ├── id, token, id_usuario → usuario
  └── expires_at, revoked_at, created_at

documento (id_documento PK)
  ├── id_tipo_documento → tipo_documento
  ├── id_estado_documento → estado_documento
  ├── id_usuario → usuario
  ├── id_expediente → expediente (nullable)
  ├── num_interno INT (MAX()+1)
  ├── num_oficial INT (MAX()+1)
  ├── num_externo INT (default 0)
  ├── original CHAR(1) (default 'S')
  ├── materia VARCHAR(250) NOT NULL
  └── fecha_documento, fecha_sistema, fecha_update DATETIME

tramite (id_seguimiento PK)
  ├── id_documento → documento
  ├── id_usuario → usuario
  ├── id_destino → dependencia (o external)
  ├── id_procedencia → dependencia (o external)
  ├── tipo_destinatario CHAR(1) (D=Dependencia, E=Externa)
  ├── tipo_procedencia CHAR(1) (D|E)
  ├── id_estado_tramite INT
  │     1=INGRESADO, 2=DESPACHADO, 3=RECEPCIONADO, 4=DERIVADO, 5=CERRADO
  ├── observaciones VARCHAR(500)
  ├── fecha_sistema, fecha_recepcion, fecha_update DATETIME
  └── usuario_recepcion (id_usuario)

archivo_digital (id_archivo_digital PK)
  ├── id_documento → documento
  ├── archivo VARCHAR(50)  ← nombre truncado a 50 chars
  └── ruta VARCHAR(50)     ← filename corto ej: 87328552.pdf (12 chars)

expediente (id_expediente PK)
  ├── desc_expediente CHAR(100) NOT NULL
  ├── fecha_expediente DATETIME NOT NULL
  └── tipo_expediente (nullable)
```

### Tablas legacy heredadas (sin modificar)

- `tipo_documento` — catálogo de tipos
- `estado_documento` — catálogo de estados
- `dependencia` — unidades/servicios del hospital
- `estado_tramite` — catálogo estados trámite (1–5)
- `tipo_distribucion`, `tipo_compromiso` — catálogos de trámite

### Tablas nuevas creadas en este proyecto

- `rol` — roles del sistema
- `usuario_rol` — relación usuario ↔ rol
- `rol_modulo` — qué módulos puede ver cada rol
- `refresh_token` — tokens de renovación JWT

### Qué tablas usa cada módulo

| Módulo | Tablas principales |
|--------|-------------------|
| Auth | usuario, funcionario, refresh_token, usuario_rol, rol, rol_modulo |
| Dashboard / Reportes | documento, tramite, expediente, usuario |
| Documentos | documento, tramite, tipo_documento, estado_documento, dependencia |
| Bandeja / Enviados | tramite, documento, dependencia |
| Archivos | archivo_digital, documento |
| Expedientes | expediente, documento |
| Usuarios | usuario, funcionario, usuario_rol, rol, dependencia |
| Roles | rol, rol_modulo, usuario_rol |
| Búsqueda | documento, tramite, funcionario |
| Configuración | uploads/config/sistema.json (archivo, no BD) |

---

## G. FUNCIONALIDADES POR MÓDULO

### 1. Login / Autenticación

**¿Qué hace?** Autentica usuarios con soporte dual: texto plano legacy + bcrypt moderno. En cada login exitoso migra la contraseña a bcrypt silenciosamente.

**Rutas frontend:** `/login`, `/forgot-password`, `/reset-password`

**APIs:** `POST /auth/login`, `POST /auth/refresh`, `POST /auth/logout`, `GET /auth/me`, `POST /auth/request-reset`, `POST /auth/reset-password`

**Tablas:** `usuario`, `funcionario`, `refresh_token`, `usuario_rol`, `rol`, `rol_modulo`

**Permisos:** Pública

**Riesgos:** Contraseña admin `admin` en texto plano. Migración a bcrypt ocurre solo al hacer login. Rate limit: 20 req/15min en producción.

---

### 2. Dashboard

**¿Qué hace?** Muestra métricas del sistema: total documentos, pendientes, cerrados hoy, urgentes. Gráficos: por estado, por mes (últimos 6), por tipo (top 8). Actividad reciente (últimos 15 trámites).

**Rutas frontend:** `/dashboard`

**APIs:** `GET /reportes/dashboard`, `GET /reportes/actividad-reciente`

**Tablas:** `documento`, `tramite`, `expediente`, `usuario`

**Permisos:** módulo `dashboard`

**Filtrado de datos:** Si el usuario no tiene `todosServicios`, solo ve documentos donde su dependencia participó en algún trámite. Admin ve todo.

---

### 3. Documentos

**¿Qué hace?** Ciclo completo de documentos: listar con filtros, crear, ver detalle, historial de trámites, acciones de despacho/recepción/derivación/cierre.

**Rutas frontend:** `/documentos`, `/documentos/nuevo`, `/documentos/:id`

**APIs:** `GET /documentos`, `POST /documentos`, `GET /documentos/:id`, `GET /documentos/:id/historial`, `GET /documentos/:id/trazabilidad`, `POST /documentos/:id/despachar|recepcionar|derivar|terminar|reabrir`, `DELETE /documentos/:id`

**Tablas:** `documento`, `tramite`, `tipo_documento`, `estado_documento`

**Permisos:**
- Crear: todos
- Despachar/derivar: admin, of.partes, supervisores
- Terminar: todos
- Reabrir: admin, supervisores (NO of.partes)
- Eliminar: admin únicamente

---

### 4. Bandeja de Entrada

**¿Qué hace?** Muestra trámites pendientes de recepción dirigidos a la dependencia del usuario. Permite recepcionar.

**Rutas frontend:** `/bandeja`

**APIs:** `GET /tramites`, `PATCH /tramites/:id/recibir`

**Tablas:** `tramite`, `documento`, `dependencia`

**Filtrado:** `id_destino = idDependencia AND tipo_destinatario = 'D'` (o 'E' con permisos).

---

### 5. Enviados

**¿Qué hace?** Muestra documentos cuyo último trámite fue enviado desde la dependencia del usuario.

**Rutas frontend:** `/enviados`

**APIs:** `GET /tramites/enviados`

**Tablas:** `tramite`, `documento`, `dependencia`

---

### 6. Mis Trámites

**¿Qué hace?** Trámites asignados al usuario específico (no a la dependencia general).

**Rutas frontend:** `/tramites`

**APIs:** `GET /tramites`, `PATCH /tramites/:id/recibir`, `PATCH /tramites/:id/cerrar`

**Tablas:** `tramite`, `documento`

---

### 7. Trazabilidad

**¿Qué hace?** Muestra el timeline completo del ciclo de vida de un documento: cada estado, fecha, usuario, dependencias de origen y destino.

**Rutas frontend:** `/trazabilidad`

**APIs:** `GET /documentos/:id/trazabilidad`

**Tablas:** `tramite`, `documento`, `dependencia`

---

### 8. Búsqueda Global

**¿Qué hace?** Busca en paralelo en documentos (materia, números), trámites (observaciones) y funcionarios (nombre, rut). Mínimo 2 caracteres.

**Rutas frontend:** `/busqueda`

**APIs:** `GET /busqueda?q=...&tipo=todos`

**Tablas:** `documento`, `tramite`, `funcionario`

**Límite funcionarios:** TOP 20 resultados.

---

### 9. Archivos

**¿Qué hace?** Gestión de archivos digitales adjuntos: upload (hasta 20MB), listar, preview inline, descarga, eliminar.

**Rutas frontend:** `/archivos`

**APIs:** `POST /archivos/upload`, `GET /archivos`, `GET /archivos/:id/preview`, `GET /archivos/:id/download`, `DELETE /archivos/:id`

**Tablas:** `archivo_digital`, `documento`

**Tipos permitidos:** PDF, DOC/DOCX, XLS/XLSX, PNG, JPG/JPEG, WEBP, TXT, ZIP

**Almacenamiento:** `backend/uploads/` — en prod: volumen Docker compartido con nginx.

---

### 10. Expedientes

**¿Qué hace?** Agrupa documentos bajo un expediente institucional. Permite listar los 19,373 expedientes legacy, crear nuevos y vincular documentos.

**Rutas frontend:** `/expedientes`

**APIs:** `GET /expedientes`, `POST /expedientes`, `GET /expedientes/:id/documentos`, `PATCH /expedientes/vincular`

**Tablas:** `expediente`, `documento`

**Permisos:** módulo `expedientes`

---

### 11. Usuarios

**¿Qué hace?** CRUD completo: crear (automáticamente crea registro funcionario), editar, asignar roles, eliminar.

**Rutas frontend:** `/admin/usuarios`

**APIs:** `GET|POST /usuarios`, `GET|PATCH|DELETE /usuarios/:id`, `GET /usuarios/meta/roles`

**Tablas:** `usuario`, `funcionario`, `usuario_rol`, `rol`, `dependencia`

**Permisos:** módulo `usuarios`

**Restricción:** `usuario` máx 10 chars, `clave` máx 10 chars (legacy).

---

### 12. Roles

**¿Qué hace?** Define roles institucionales y qué módulos puede ver/usar cada rol.

**Rutas frontend:** `/admin/roles`

**APIs:** `GET|POST /roles`, `GET|PATCH|DELETE /roles/:id`, `GET /roles/meta/modulos`

**Tablas:** `rol`, `rol_modulo`, `usuario_rol`

**Permisos:** rol `admin` únicamente

**Módulos asignables:**
- Operativos: `dashboard`, `documentos`, `bandeja`, `enviados`, `tramites`, `trazabilidad`, `busqueda`, `archivos`
- Admin: `expedientes`, `usuarios`, `reportes`, `roles`, `configuracion`

**Lógica de borrado:** Si el rol tiene usuarios → se desactiva (`activo=0`). Si no tiene → se borra físicamente.

---

### 13. Configuración del Sistema

**¿Qué hace?** Personalización institucional: nombre del sistema, institución, logo, fondo de login, textos de login.

**Rutas frontend:** `/admin/configuracion`

**APIs:** `GET /configuracion` (pública), `PATCH /configuracion`, `POST /configuracion/logo`, `POST /configuracion/background`

**Persistencia:** `uploads/config/sistema.json` (no en BD)

**Permisos:** Lectura: pública. Escritura: JWT requerido.

---

### 14. Reportes

**¿Qué hace?** Métricas detalladas con gráficos (Recharts): documentos por estado, por mes, por tipo. Exportación a CSV compatible con Excel (UTF-8 + BOM).

**Rutas frontend:** `/reportes`

**APIs:** `GET /reportes/dashboard`, `GET /reportes/actividad-reciente`, `GET /reportes/exportar`

**Tablas:** `documento`, `tramite`, `expediente`, `usuario`

**Permisos:** módulo `reportes`

---

### 15. Recuperación de Contraseña

**¿Qué hace?** Envía email con token de reset al usuario para establecer nueva contraseña.

**Rutas frontend:** `/forgot-password`, `/reset-password`

**APIs:** `POST /auth/request-reset`, `POST /auth/reset-password`

**Dependencia externa:** SMTP configurado en `.env` (`SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`)

---

## H. RIESGOS DETECTADOS

### Seguridad

| Riesgo | Severidad | Detalle |
|--------|-----------|---------|
| Contraseña admin en texto plano | **Alta** | `admin/admin` sin bcrypt hasta el primer login. Si la BD se expone antes del primer acceso, es vulnerable. |
| `GET /configuracion` sin auth | Media | Expone nombre del sistema, logo URL y textos del login. Aceptable para la pantalla de login, pero también expone rutas internas de archivos. |
| `GET /uploads/:filename` sin auth | Media | Cualquier archivo subido es accesible sin autenticación si se conoce el nombre (nombres semialeatorizados: timestamp de 8 chars). |
| `clave VARCHAR(10)` texto plano en BD | **Alta** | La columna legacy `clave` persiste en BD en texto plano para usuarios aún no migrados. |
| JWT_SECRET débil en dev | Media | El secret por defecto en `.env` es conocido y hardcodeado. Requiere rotación en producción. |
| Rate limit solo en `/auth` | Media | El resto de endpoints no tiene rate limiting. Un atacante autenticado podría hacer scraping o DoS. |
| SMTP sin configurar = reset falla silencioso | Baja | Si SMTP no está configurado, el endpoint de reset no envía email pero puede no retornar error claro. |

### Funcionalidad

| Riesgo | Severidad | Detalle |
|--------|-----------|---------|
| `sistema.json` no en BD | Media | La config del sistema se guarda en archivo. Si el volumen Docker se pierde, se pierde la config. |
| `@prisma/client` en dependencies | Baja | Prisma está instalado pero no se usa. Agrega peso innecesario al bundle de producción. |
| `App.jsx` alias legacy | Baja | `App.jsx` re-exporta `App.tsx`. Podría causar confusión. |
| Columnas VARCHAR truncadas en BD | Alta | `archivo VARCHAR(50)` y `ruta VARCHAR(50)` — si el filename supera 50 chars, el INSERT falla. El sistema ya genera nombres cortos (12 chars) pero si se cambia el generador, reaparece el problema. |
| `PATCH /expedientes/vincular` sin validación de propiedad | Baja | Si se llama con idDocumento incorrecto no hay validación de que el documento pertenezca a quien llama. |

### Operación

| Riesgo | Severidad | Detalle |
|--------|-----------|---------|
| Solo 1 usuario activo (admin) | Alta | Si el usuario admin se bloquea o se pierde la contraseña, no hay mecanismo de recuperación alternativo. |
| BD sin respaldo automático | Alta | El volumen Docker `sisdoc_sqlserver_data` no tiene backup automático configurado. Solo manual. |
| `uploads/` no persistente en dev | Media | En desarrollo el directorio `uploads/` no está en git. Se pierde al clonar el proyecto. |
| Logs en volumen no configurado en dev | Baja | `backend/logs/` tampoco está en git. |

---

## I. MEJORAS RECOMENDADAS

### Corto plazo (antes de producción real)

1. **Cambiar contraseña admin** — Hacer login con `admin/admin` para migrar a bcrypt, luego cambiar a contraseña fuerte desde la UI de usuarios.
2. **Rotar JWT secrets** — Reemplazar los secrets del `.env` de producción por valores generados con `openssl rand -base64 64`.
3. **Proteger `/uploads/`** — Agregar middleware de auth al servicio de archivos estáticos, o mover a `/api/v1/archivos/:id/serve` que valide JWT.
4. **Persistir `sistema.json` en BD** — Mover la configuración del sistema a una tabla `configuracion_sistema` para no depender del sistema de archivos.
5. **Remover `@prisma/client`** — Limpiar dependencias no usadas del `package.json` del backend.
6. **Crear segundo usuario admin** — Tener un usuario de respaldo con rol admin, diferente usuario/contraseña.
7. **Configurar backup automático** — Script o cron job que ejecute `BACKUP DATABASE SISDOC` periódicamente.

### Mediano plazo

8. **Rate limiting global** — Agregar rate limit a todos los endpoints autenticados (actualmente solo `/auth`).
9. **Notificaciones en tiempo real** — WebSocket o SSE para avisar cuando llega un trámite nuevo a la bandeja.
10. **Modo oscuro** — CSS vars para `.dark` ya están preparados. Solo falta el toggle en UI.
11. **Tests automatizados** — Vitest para frontend, Jest para backend. Al menos smoke tests de los endpoints críticos.
12. **Branding dinámico en sidebar** — El logo configurado en `/configuracion` no se refleja en el sidebar (usa ícono estático `Building2`).
13. **Export PDF en reportes** — Complemento del CSV actual.
14. **Eliminar `App.jsx`** — El alias legacy ya no es necesario si `main.tsx` importa explícitamente `App.tsx`.

---

## J. PENDIENTES CRÍTICOS

| Prioridad | Tarea | Módulo |
|-----------|-------|--------|
| 🔴 Alta | Cambiar contraseña `admin/admin` a algo seguro | Auth |
| 🔴 Alta | Configurar backup automático de BD antes de usar en producción | Infraestructura |
| 🔴 Alta | Configurar SMTP para recuperación de contraseña | Auth |
| 🔴 Alta | Rotar `JWT_SECRET` y `JWT_REFRESH_SECRET` en producción | Auth |
| 🟡 Media | Proteger rutas de archivos `/uploads/` con auth | Archivos |
| 🟡 Media | Migrar `sistema.json` a BD | Configuración |
| 🟡 Media | Crear al menos un segundo usuario admin | Usuarios |
| 🟡 Media | Módulo de derivación en UI del detalle de documento | Documentos |
| 🟡 Media | Crear directorio `uploads/` automáticamente en setup | Infraestructura |
| 🟢 Baja | Remover `@prisma/client` de dependencies | Backend |
| 🟢 Baja | Eliminar `App.jsx` legacy | Frontend |
| 🟢 Baja | Rate limiting en endpoints no-auth | Seguridad |
| 🟢 Baja | Notificaciones tiempo real (WebSocket) | UX |
| 🟢 Baja | Modo oscuro (toggle UI) | UX |
| 🟢 Baja | Logo dinámico en sidebar desde configuración | UI |

---

## K. CHECKLIST PARA PRODUCCIÓN

### Infraestructura
- [ ] `docker compose --profile prod up -d --build` — levanta sin errores
- [ ] Volumen `sisdoc_sqlserver_data` montado y persistente
- [ ] Volumen `uploads` compartido entre backend y nginx
- [ ] Directorio `backend/uploads/config/` existe dentro del volumen
- [ ] Health checks pasan: `/api/health` retorna 200
- [ ] nginx sirve frontend en puerto 80 y proxea `/api/v1` al backend:3001

### Variables de entorno (`.env` producción)
- [ ] `NODE_ENV=production`
- [ ] `JWT_SECRET` con mínimo 64 chars aleatorios (no el valor por defecto del repo)
- [ ] `JWT_REFRESH_SECRET` con mínimo 64 chars aleatorios
- [ ] `CORS_ORIGIN` apunta al dominio real (no localhost)
- [ ] `DB_PASSWORD` fuerte
- [ ] `SMTP_*` configurados para recuperación de contraseña
- [ ] `FRONTEND_URL` apunta al dominio real

### Base de datos
- [ ] BD `SISDOC` accesible desde el backend (health check pasa)
- [ ] Índices creados (`03-optimize-indexes.sql` ejecutado)
- [ ] Usuario admin existe y funciona (`04-create-admin-user.sql`)
- [ ] Tablas nuevas existen: `rol`, `usuario_rol`, `refresh_token`, `rol_modulo`
- [ ] Script de backup automático configurado (cron o task scheduler)
- [ ] Contraseña admin cambiada desde `admin` a algo seguro

### Seguridad
- [ ] Contraseña admin no es `admin`
- [ ] JWT secrets rotados (no los del repo)
- [ ] Rate limiting activo en `/auth` (verificar `NODE_ENV=production`)
- [ ] `X-Powered-By` header eliminado (Helmet lo hace automáticamente)
- [ ] HTTPS configurado (certificado SSL en nginx o upstream)

### Frontend
- [ ] `npm run build` sin errores TypeScript
- [ ] `npm run typecheck` pasa sin errores
- [ ] Build copiado a `/usr/share/nginx/html` en imagen nginx

### Backend
- [ ] `npm run typecheck` pasa sin errores
- [ ] `npm run build` genera `dist/` sin errores
- [ ] `backend/uploads/` existe y tiene permisos de escritura
- [ ] Swagger UI funciona en `/api-docs`
- [ ] Logs rotan correctamente en `backend/logs/`

### Funcional (smoke test)
- [ ] Login con `admin` + contraseña nueva funciona
- [ ] Dashboard carga métricas reales
- [ ] Crear documento → aparece en bandeja del destinatario
- [ ] Upload de archivo → se descarga correctamente
- [ ] Exportar CSV desde reportes → abre en Excel sin problemas de encoding
- [ ] Configuración: subir logo → se refleja en pantalla de login
- [ ] Recuperación de contraseña: llega email con link válido

---

*Documento generado el 22 de mayo de 2026. Refleja el estado del proyecto en la rama `main`.*
