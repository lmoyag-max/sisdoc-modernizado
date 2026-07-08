# DOC360 — Sistema de Gestión Documental
### Hospital de Urgencia Asistencia Pública (HUAP)

Plataforma documental institucional moderna que reemplaza el sistema legacy SISDOC (Windows Server 2003 / ASP clásico / SQL Server 2005), manteniendo compatibilidad total con la base de datos y los datos históricos.

**Estado:** Operativo en producción · Versión 2.0.0 · Auditado 2026-06-09 · Paquete de preproducción disponible (2026-06-24) · Rediseño visual completo (2026-06-25)

---

## Arquitectura

```
┌─────────────────────────────────────────────────┐
│  Browser / Cliente                              │
│  React 18 + Vite + TypeScript + Tailwind        │
└────────────────────┬────────────────────────────┘
                     │ HTTPS / JWT Bearer
┌────────────────────▼────────────────────────────┐
│  API Backend                                    │
│  Node.js 20 + Express + TypeScript              │
│  Puerto 3001 (dev) · Nginx proxy (prod)         │
└────────────────────┬────────────────────────────┘
                     │ mssql driver (doc360_app)
┌────────────────────▼────────────────────────────┐
│  SQL Server 2022                                │
│  Docker · Puerto 11433 (host) · BD: SISDOC     │
└─────────────────────────────────────────────────┘
```

---

## Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Frontend | React + Vite + TypeScript | 18 / 6 / 5.7 |
| UI | Tailwind CSS + shadcn/ui (Radix) | 3 |
| Estado / Data | Zustand + TanStack Query | 5 |
| Formularios | react-hook-form + Zod | — |
| Backend | Node.js + Express + TypeScript | 20 LTS |
| ORM/Driver | mssql (queries directas) | 12.5.4 |
| Auth | JWT (15 min) + Refresh Token (7 días, httpOnly) | — |
| Base de datos | SQL Server 2022 | Docker |
| Contenedor | Docker + Docker Compose | 27+ |
| Proxy (prod) | Nginx | Alpine |
| UI visual | Glassmorphism + iconos 3D (paleta unificada, `globals.css`) | — |

---

## Módulos operativos

| Módulo | Ruta | Roles con acceso | Estado |
|---|---|---|---|
| Dashboard | `/dashboard` | Todos | ✅ Operativo |
| Documentos | `/documentos` | Todos | ✅ Operativo |
| Bandeja de entrada | `/bandeja` | Todos | ✅ Operativo |
| Documentos enviados | `/enviados` | Todos | ✅ Operativo |
| Mis trámites | `/tramites` | Todos | ✅ Operativo |
| Trazabilidad | `/trazabilidad` | Todos | ✅ Operativo |
| Búsqueda global | `/busqueda` | Todos + of.partes | ✅ Operativo |
| Gestión de archivos | `/archivos` | admin | ✅ Operativo |
| Reportes + CSV | `/reportes` | admin | ✅ Operativo |
| Memorándum | (integrado en documentos) | Todos | ✅ Operativo* |
| Usuarios | `/admin/usuarios` | admin | ✅ Operativo |
| Roles | `/admin/roles` | admin | ✅ Operativo |
| Jefaturas | `/admin/jefaturas` | admin | ✅ Operativo |
| Alertas | `/admin/alertas` | admin | ✅ Operativo |
| Firma electrónica | `/admin/firma-gob` | admin | ⚙️ Sin configurar |
| Configuración | `/admin/configuracion` | admin | ✅ Operativo |

> *Memorándum requiere imagen de firma/timbre configurada en Jefaturas para generar PDF.

---

## Requisitos

### Desarrollo
- Node.js 20 LTS
- Docker Desktop (corriendo)
- Git

### Producción (servidor Linux)
- Docker Engine 24+
- Docker Compose v2+
- 4 GB RAM mínimo · 20 GB disco
- Puertos 80, 3001 y 11433 (o 1433 en red interna)

---

## Instalación y puesta en marcha

### 1. Clonar el repositorio

```bash
git clone https://github.com/lmoyag-max/sisdoc-modernizado.git
cd sisdoc-modernizado
```

### 2. Configurar variables de entorno

```bash
cp backend/.env.example backend/.env
# Editar backend/.env con los valores reales
```

Ver [backend/.env.example](backend/.env.example) para referencia completa.

### 3. Levantar SQL Server

```powershell
docker compose up -d sqlserver
# Esperar ~15 segundos para que SQL Server termine de iniciar
```

### 4. Restaurar base de datos (primera vez)

```powershell
.\scripts\restore-db.ps1
```

### 5. Instalar dependencias

```powershell
cd backend  ; npm install ; cd ..
cd frontend ; npm install ; cd ..
```

---

## Ejecución en desarrollo

Requiere 2 terminales además de Docker:

```powershell
# Terminal 1: Backend — hot reload en :3001
cd backend
npm run dev

# Terminal 2: Frontend — hot reload en :5173
cd frontend
npm run dev
```

---

## Despliegue en producción

```bash
# Build y arranque completo con Docker Compose
docker compose --profile prod up -d --build

# Verificar estado
docker compose ps
docker compose logs -f backend

# Health check
curl http://localhost:3001/api/health
```

---

## Despliegue en preproducción (paquete autocontenido)

Stack Docker aislado (`docker-compose.preprod.yml`, project name `sisdoc_preprod`) pensado para que Operaciones lo levante sin conocer el código fuente. No colisiona con el stack de desarrollo.

```bash
# Levantar todo (build + arranque)
./scripts/start-preprod.sh              # Linux/Mac
.\scripts\start-preprod.ps1             # Windows

# Equivalente manual
docker compose -f docker-compose.preprod.yml up -d --build

# Backup / restore
./scripts/backup-db.sh
./scripts/restore-db.sh /var/opt/mssql/backup/archivo.bak
```

Guía completa (requisitos, puertos, variables de entorno, orden de scripts SQL, checklist funcional): [README_PREPROD.md](README_PREPROD.md).

> **Importante:** este paquete no crea el esquema legacy SISDOC desde cero — requiere restaurar primero un `.bak` sanitizado de la base existente.

---

## URLs de acceso

| Servicio | Desarrollo | Producción |
|---|---|---|
| Frontend | http://localhost:5173 | https://dominio.hospital.cl |
| Backend API | http://localhost:3001/api/v1 | https://dominio.hospital.cl/api/v1 |
| Health check | http://localhost:3001/api/health | — |
| API Docs | http://localhost:3001/api-docs | (deshabilitado en prod) |

> **Puerto SQL Server:** `11433` en el host (no 1433 — reservado por Windows/Hyper-V). Mapeado como `127.0.0.1:11433:1433` — solo accesible desde localhost.

---

## Usuarios del sistema

Las contraseñas reales se gestionan internamente. Contactar al administrador del sistema para credenciales de acceso.

| Usuario | Rol | Descripción |
|---------|-----|-------------|
| `admin` | Administrador | Acceso total al sistema |
| `ofparte` | Of. de Partes | Búsqueda y gestión de entrada de documentos |
| Funcionarios | Funcionario | Bandeja, documentos, trazabilidad |

---

## API REST

**Base URL:** `http://localhost:3001/api/v1`

Todos los endpoints (excepto `/auth/login`, `/auth/refresh` y `GET /configuracion`) requieren `Authorization: Bearer <token>`.

```
# Autenticación
POST   /auth/login
POST   /auth/refresh
POST   /auth/logout
GET    /auth/me

# Documentos
GET    /documentos              (q, idTipo, idEstado, fechaDesde, fechaHasta, pagina)
GET    /documentos/:id
GET    /documentos/:id/historial
POST   /documentos
POST   /documentos/:id/derivar

# Trámites
GET    /tramites                (idEstado, pagina)
PATCH  /tramites/:id/recibir
PATCH  /tramites/:id/cerrar

# Archivos
POST   /archivos/upload         (multipart/form-data: archivo + idDocumento)
GET    /archivos                (idDocumento?)
DELETE /archivos/:id

# Memorándum
GET    /memorandum/firmante-activo
GET    /memorandum/firmantes-disponibles
POST   /memorandum/confirmar    ({ idDocumento, materia?, referencia?, cuerpo? })
PATCH  /memorandum/vincular-archivo
GET    /memorandum/firmantes          (solo admin)
POST   /memorandum/firmantes          (solo admin)
POST   /memorandum/firmantes/:id/imagen (solo admin)

# Usuarios (solo admin)
GET    /usuarios                (q, pagina)
GET    /usuarios/:id
POST   /usuarios
PATCH  /usuarios/:id
DELETE /usuarios/:id
GET    /usuarios/meta/roles

# Roles (solo admin)
GET    /roles

# Jefaturas
GET    /jefaturas               (q, pagina, porPagina — KPIs del strip se calculan sobre el dataset completo)

# Alertas (solo admin)
GET    /alertas/configuracion
PUT    /alertas/configuracion
GET    /alertas/pendientes
GET    /alertas/logs            (paginado — OFFSET/FETCH, 20 por página)
POST   /alertas/enviar-manual

# Firma Electrónica (solo admin)
GET    /firma-gob/config
PATCH  /firma-gob/config/:ambiente
GET    /firma-gob/historial
POST   /firma-gob/test-conexion
POST   /firma-gob/solicitar

# Reportes
GET    /reportes/dashboard      (requireModule 'dashboard')
GET    /reportes/actividad-reciente (requireModule 'dashboard')
GET    /reportes/exportar       (requireModule 'reportes' → CSV con BOM)

# Búsqueda
GET    /busqueda                (q, tipo: documentos|tramites|funcionarios|todos, pagina)

# Catálogos
GET    /catalogos/tipos-documento
GET    /catalogos/estados
GET    /catalogos/prioridades
GET    /catalogos/dependencias
GET    /catalogos/tipos-distribucion
GET    /catalogos/tipos-compromiso

# Configuración
GET    /configuracion           (pública)
PATCH  /configuracion           (solo admin)
POST   /configuracion/logo      (solo admin — multipart/form-data)
POST   /configuracion/background (solo admin — multipart/form-data)
PATCH  /configuracion/upload-rules (solo admin)
```

---

## Docker Compose

```bash
# Desarrollo — solo SQL Server
docker compose up -d sqlserver

# Producción completa
docker compose --profile prod up -d --build

# Estado de contenedores
docker compose ps

# Logs en tiempo real
docker compose logs -f backend
docker compose logs -f nginx

# Detener (conserva datos)
docker compose down

# Detener y eliminar volúmenes — ¡ELIMINA LA BASE DE DATOS!
docker compose down -v

# Reconstruir imagen backend
docker compose --profile prod build backend --no-cache
```

---

## Backup de base de datos

El script `scripts/backup-db.ps1` ejecuta backup semanal automático cada **domingo a las 2:00 AM** (Task Scheduler de Windows). Retención: 30 días. Destino: `database/backups/`.

```powershell
# Backup manual
.\scripts\backup-db.ps1 -SaPassword "CONTRASENA_SA"

# Ver backups disponibles
Get-ChildItem database\backups\
```

### Restaurar base de datos

```powershell
# Restaurar automáticamente desde el backup más reciente
.\scripts\restore-db.ps1

# Restaurar manualmente desde un backup específico
docker exec sisdoc_sqlserver /opt/mssql-tools18/bin/sqlcmd `
  -S localhost -U sa -P "CONTRASENA_SA" -C `
  -Q "RESTORE DATABASE [SISDOC] FROM DISK='/var/opt/mssql/backup/SISDOC_backup_FECHA.bak'
      WITH MOVE 'sisdoc_Data' TO '/var/opt/mssql/data/SISDOC.mdf',
           MOVE 'sisdoc_Log'  TO '/var/opt/mssql/data/SISDOC_log.ldf', REPLACE"
```

### Rollback a commit anterior

```bash
git log --oneline          # identificar el commit objetivo
git checkout <commit_hash> -- backend/src/
git checkout <commit_hash> -- frontend/src/
# Reiniciar backend
```

---

## Seguridad

- **Login de BD:** `doc360_app` (no `sa`) — permisos limitados a la base SISDOC
- **Contraseñas:** bcrypt $2b$12 — columna legacy `clave` sin texto plano
- **JWT:** Access token 15 min + Refresh token 7 días (httpOnly cookie, revocable)
- **CORS:** Lista explícita de orígenes permitidos
- **Rate limiting:** 20 intentos de login por 15 minutos en producción
- **Uploads:** Validación de extensión y tamaño en servidor + cliente
- **SQL:** `Page Verify = CHECKSUM` activo — detección de corrupción
- **Puerto SQL:** `127.0.0.1:11433` — no accesible desde red externa
- El `.env` **nunca** debe subirse a Git (listado en `.gitignore`)
- En producción: `DB_ENCRYPT=true`, `DB_TRUST_CERT=false`, certificado TLS válido

---

## Estructura del proyecto

```
sisdoc-modernizado/
├── backend/
│   ├── src/
│   │   ├── app.ts                    # Express: CORS, middlewares, rutas
│   │   ├── server.ts                 # Entry point — bind 0.0.0.0:3001
│   │   ├── config/                   # env.ts, database.ts, swagger.ts
│   │   ├── middleware/               # auth, validate, error, logger
│   │   ├── modules/
│   │   │   ├── auth/                 # Login, refresh, logout, /me, reset
│   │   │   ├── documentos/           # CRUD + derivar + historial
│   │   │   ├── tramites/             # Bandeja + recibir + cerrar
│   │   │   ├── archivos/             # Upload multer + lista + delete
│   │   │   ├── memorandum/           # Generación PDF + correlativos + firmantes
│   │   │   ├── jefaturas/            # Titular/subrogante + firma/timbre
│   │   │   ├── firma-gob/            # Integración FirmaGOB (ClaveÚnica)
│   │   │   ├── alertas/              # Alertas SMTP configurables
│   │   │   ├── usuarios/             # CRUD + roles
│   │   │   ├── roles/                # Gestión de módulos por rol
│   │   │   ├── catalogos/            # Tipos, estados, dependencias
│   │   │   ├── busqueda/             # Búsqueda global
│   │   │   ├── reportes/             # Dashboard + actividad + CSV
│   │   │   └── configuracion/        # Logo, fondo, nombres, upload-rules
│   │   ├── shared/
│   │   │   ├── types/api.types.ts
│   │   │   └── utils/response.ts
│   │   └── types/mssql.d.ts
│   ├── uploads/                      # Archivos subidos (NO en git)
│   │   └── config/                   # Logo, fondo, sistema.json
│   ├── .env                          # Variables reales (NO en git)
│   ├── .env.example                  # Plantilla sin secretos
│   └── package.json
├── frontend/
│   └── src/
│       ├── app/                      # router.tsx + providers.tsx
│       ├── components/               # ui | layout | shared | documentos
│       ├── pages/
│       │   ├── auth/                 # Login, ForgotPassword, ResetPassword
│       │   ├── dashboard/
│       │   ├── documentos/           # Listado, Detalle, Nuevo
│       │   ├── bandeja/
│       │   ├── enviados/
│       │   ├── tramites/
│       │   ├── trazabilidad/
│       │   ├── busqueda/
│       │   ├── archivos/
│       │   ├── reportes/
│       │   ├── alertas/
│       │   └── admin/                # Usuarios, Roles, Jefaturas, FirmaGob, Config
│       ├── lib/api/                  # Axios client + módulos API
│       ├── stores/                   # Zustand (auth)
│       └── hooks/                    # useDebounce, useUploadRules
├── database/
│   ├── scripts/                      # SQL de migración y setup
│   ├── backups/                      # Backups automáticos (NO en git)
│   └── sp_legacy_fase2_backup_20260609.sql
├── scripts/
│   ├── backup-db.ps1 / backup-db.sh      # Backup semanal automatizado
│   ├── restore-db.ps1 / restore-db.sh
│   ├── start-preprod.ps1 / start-preprod.sh # Levanta el stack de preproducción
│   ├── setup.ps1
│   └── dev.ps1
├── docker/                           # Dockerfiles backend/frontend + init.sh SQL Server
├── nginx/nginx.conf                  # Proxy prod y preprod (incluye /uploads/)
├── docker-compose.yml                # Dev + perfil "prod"
├── docker-compose.preprod.yml        # Stack aislado "sisdoc_preprod"
├── CLAUDE.md                         # Guía técnica completa para desarrollo
├── README_PREPROD.md                 # Guía de despliegue para Operaciones
└── README.md
```

---

## Variables de entorno

Ver [backend/.env.example](backend/.env.example) para la lista completa con descripción de cada variable.

Variables críticas que deben configurarse antes del primer arranque:

| Variable | Descripción |
|---|---|
| `DB_PASSWORD` | Contraseña del usuario `doc360_app` en SQL Server |
| `MSSQL_SA_PASSWORD` | Contraseña del administrador SA (usado por Docker y backups) |
| `JWT_SECRET` | Mínimo 64 bytes base64 aleatorios |
| `JWT_REFRESH_SECRET` | Mínimo 64 bytes base64 aleatorios |
| `CORS_ORIGIN` | URL del frontend (ej. `https://doc360.hospital.cl`) |
| `SMTP_HOST` | Servidor de correo para alertas y recuperación de contraseña |
| `SMTP_PASS` | Contraseña del servidor SMTP |

---

## Checklist de despliegue

- [ ] Variables de entorno configuradas en servidor
- [ ] `JWT_SECRET` y `JWT_REFRESH_SECRET` generados con entropía real
- [ ] `DB_ENCRYPT=true` y `DB_TRUST_CERT=false` configurados
- [ ] `CORS_ORIGIN` apunta al dominio real de producción
- [ ] `NODE_ENV=production` en `.env`
- [ ] Backup inicial de BD ejecutado y verificado
- [ ] Task Scheduler de backup semanal configurado
- [ ] Imagen de firma/timbre subida en `/admin/jefaturas` (para memorándum)
- [ ] FirmaGOB configurado en `/admin/firma-gob` (si se usará firma electrónica)
- [ ] SMTP probado con `/alertas/probar-servicio/:id`
- [ ] Health check responde: `GET /api/health`

---

## Roadmap

- [x] Fase 0: Infraestructura (Docker, SQL Server, JWT, backup)
- [x] Fase 1: Auth + Dashboard + Documentos
- [x] Fase 2: Bandeja, Enviados, Trámites, Trazabilidad, Búsqueda, Archivos
- [x] Fase 3: Usuarios, Roles, Reportes, Configuración, Alertas, Jefaturas
- [x] Fase 3b: Memorándum (PDF con firma/timbre), FirmaGOB (integración base)
- [x] Fase 3c: Auditoría funcional y correcciones de seguridad (2026-06-09)
- [x] Fase 3d: Empaquetado de preproducción con Docker (`docker-compose.preprod.yml`, 2026-06-24)
- [x] Fase 3e: Rediseño visual completo — glassmorphism + iconos 3D en las 17 pantallas (2026-06-25)
- [x] Fase 3f: Limpieza de Prisma sin uso y documentación legacy obsoleta (2026-06-25)
- [x] Fase 3g: Paginación real en Historial de Alertas y Jefaturas (2026-06-25)
- [ ] Fase 4: Notificaciones en tiempo real (WebSocket)
- [ ] Fase 5: Tests automatizados (Vitest + Supertest)
- [ ] Fase 6: CI/CD pipeline, modo oscuro, firma digital completa

---

## Reglas del proyecto

1. **NUNCA modificar** archivos dentro de `/legacy` (sistema original — referencia histórica)
2. **NUNCA eliminar** archivos sin respaldo previo
3. Código nuevo solo en `/backend` y `/frontend`
4. No exponer datos productivos sin anonimizar
5. Cambios en BD requieren respaldo previo y autorización explícita
