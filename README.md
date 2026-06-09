# DOC360 — Sistema de Gestión Documental
### Hospital de Asistencia Pública Alejandro Del Río (HUAP)

Sistema de gestión documental institucional moderno. Plataforma SaaS enterprise que reemplaza el sistema legacy SISDOC (Windows Server 2003 / ASP clásico / SQL Server 2005), manteniendo compatibilidad total con la base de datos y los datos históricos.

---

## Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | Node.js + Express + TypeScript | 20 LTS |
| Base de datos | SQL Server 2022 | Docker |
| ORM/Driver | mssql (queries directas) | 12.5.4 |
| Frontend | React + Vite + TypeScript | 18 / 6 |
| UI | Tailwind CSS + shadcn/ui (Radix) | 3 |
| Estado global | Zustand + TanStack Query | 5 |
| Auth | JWT access 15 min + refresh 7 días (httpOnly cookie) | — |
| Formularios | react-hook-form + Zod | — |
| Contenedor | Docker + Docker Compose | 27+ |

---

## Inicio rápido

### Prerrequisitos
- Node.js 20+
- Docker Desktop (corriendo)
- Git

### Setup inicial (una sola vez)

```powershell
# 1. Ubicarse en el proyecto
cd c:\sisdoc-modernizado

# 2. Levantar SQL Server
docker compose up -d sqlserver

# 3. Restaurar base de datos (si es primera vez)
.\scripts\restore-db.ps1

# 4. Instalar dependencias
cd backend  && npm install && cd ..
cd frontend && npm install && cd ..
```

### Desarrollo diario (3 terminales)

```powershell
# Terminal 1: SQL Server
docker compose up -d sqlserver

# Terminal 2: Backend — hot reload en :3001
cd backend && npm run dev

# Terminal 3: Frontend — hot reload en :5173
cd frontend && npm run dev
```

---

## URLs de acceso

| Servicio | Local | Red local |
|---|---|---|
| Frontend | http://localhost:5173 | http://TU-IP:5173 |
| Backend API | http://localhost:3001/api/v1 | http://TU-IP:3001/api/v1 |
| Health check | http://localhost:3001/api/health | — |
| API Docs (Swagger) | http://localhost:3001/api-docs | — |

> **¿Cuál es tu IP?** `ipconfig` en Windows → IPv4 de tu adaptador de red.

---

## Usuarios del sistema

| Usuario | Contraseña | Rol |
|---------|-----------|-----|
| `admin` | `Huap.2025` | Administrador |
| `ti` | `Huap.2025` | Funcionario |
| `aba` | `Huap.2025` | Funcionario |
| `contrato` | `Huap.2025` | Funcionario |
| `ofparte` | `Huap.2025` | Of. de Partes |

---

## Módulos operativos

| Módulo | Ruta | Estado |
|---|---|---|
| Dashboard | `/dashboard` | ✅ Operativo |
| Documentos (listado + detalle + crear) | `/documentos` | ✅ Operativo |
| Bandeja de entrada | `/bandeja` | ✅ Operativo |
| Documentos enviados | `/enviados` | ✅ Operativo |
| Mis trámites | `/tramites` | ✅ Operativo |
| Trazabilidad documental | `/trazabilidad` | ✅ Operativo |
| Búsqueda global | `/busqueda` | ✅ Operativo |
| Gestión de archivos | `/archivos` | ✅ Operativo |
| Expedientes | `/expedientes` | ✅ Operativo |
| Administración de usuarios | `/admin/usuarios` | ✅ Operativo |
| Reportes y exportación CSV | `/reportes` | ✅ Operativo |
| Configuración del sistema | `/admin/configuracion` | ✅ Operativo |

---

## API REST

**Base URL:** `http://localhost:3001/api/v1`

Todos los endpoints (excepto `/auth/login`, `/auth/refresh` y `/configuracion` GET) requieren `Authorization: Bearer <token>`.

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

# Expedientes
GET    /expedientes             (q, pagina)
POST   /expedientes
GET    /expedientes/:id/documentos
PATCH  /expedientes/vincular

# Usuarios
GET    /usuarios                (q, pagina)
GET    /usuarios/:id
POST   /usuarios
PATCH  /usuarios/:id
DELETE /usuarios/:id
GET    /usuarios/meta/roles

# Reportes
GET    /reportes/dashboard
GET    /reportes/actividad-reciente
GET    /reportes/exportar       (→ CSV con BOM para Excel)

# Búsqueda
GET    /busqueda                (q, tipo: documentos|tramites|funcionarios|todos, pagina)

# Catálogos
GET    /catalogos/tipos-documento
GET    /catalogos/estados-documento
GET    /catalogos/dependencias

# Configuración
GET    /configuracion           (pública)
PATCH  /configuracion
POST   /configuracion/logo      (multipart/form-data)
POST   /configuracion/background (multipart/form-data)
PATCH  /configuracion/upload-rules
```

---

## Docker Compose

```powershell
# Solo SQL Server (desarrollo)
docker compose up -d sqlserver

# Producción completa (build + arranque)
docker compose --profile prod up -d --build

# Ver estado de contenedores
docker compose ps

# Logs en tiempo real
docker compose logs -f backend
docker compose logs -f nginx

# Detener todo
docker compose down

# Detener y eliminar volúmenes — ¡BORRA LA BD!
docker compose down -v

# Reconstruir imagen del backend
docker compose --profile prod build backend --no-cache
```

### Si la base de datos se pierde

```powershell
# Restaurar desde backup automáticamente
.\scripts\restore-db.ps1

# O manualmente
docker exec sisdoc_sqlserver /opt/mssql-tools18/bin/sqlcmd `
  -S localhost -U sa -P "<DB_PASSWORD>" -C `
  -Q "RESTORE DATABASE [SISDOC] FROM DISK='/var/opt/mssql/backup/respaldo anterior.bak' WITH MOVE 'sisdoc_Data' TO '/var/opt/mssql/data/SISDOC.mdf', MOVE 'sisdoc_Log' TO '/var/opt/mssql/data/SISDOC_log.ldf', REPLACE"
```

> **Nota de puerto:** SQL Server corre en el puerto `11433` del host (no 1433 — reservado por Windows/Hyper-V). Se mapea como `127.0.0.1:11433:1433` en docker-compose.

---

## Estructura del proyecto

```
sisdoc-modernizado/
├── legacy/                  ← Sistema original ASP clásico (NUNCA MODIFICAR)
├── database/
│   ├── scripts/             ← Scripts SQL de migración y setup
│   └── respaldo anterior.bak
├── backend/                 ← API Node.js + TypeScript
│   ├── src/
│   │   ├── config/          # env.ts, database.ts, swagger.ts
│   │   ├── middleware/      # auth, errores, validación, logs
│   │   ├── modules/         # auth | documentos | tramites | archivos
│   │   │                      expedientes | usuarios | catalogos
│   │   │                      busqueda | reportes | configuracion
│   │   ├── shared/          # types, utils (sendSuccess/sendError)
│   │   └── types/           # mssql.d.ts (declaraciones manuales)
│   ├── uploads/             # Archivos subidos + config/ (logo, fondo)
│   ├── .env                 # Variables de entorno (no commitear)
│   └── package.json
├── frontend/                ← React 18 + TypeScript + Vite
│   └── src/
│       ├── app/             # router.tsx + providers.tsx
│       ├── components/      # ui | layout | shared | dashboard | documentos
│       ├── pages/           # auth | dashboard | documentos | tramites
│       │                      bandeja | enviados | trazabilidad | busqueda
│       │                      archivos | expedientes | reportes | admin
│       ├── lib/api/         # Axios client + módulos de API
│       ├── stores/          # Zustand (auth)
│       └── hooks/           # useDebounce, useUploadRules
├── docker-compose.yml
├── CLAUDE.md                ← Guía completa para desarrollo
└── README.md
```

---

## Variables de entorno

**`backend/.env`:**

```env
NODE_ENV=development
PORT=3001

# Base de datos
DB_USER=sa
DB_PASSWORD=CAMBIAR_EN_PRODUCCION
DB_SERVER=localhost
DB_PORT=11433
DB_DATABASE=SISDOC
DB_TRUST_CERT=true
DB_ENCRYPT=false

# JWT (cambiar en producción — mínimo 32 chars aleatorios)
JWT_SECRET=clave-secreta-minimo-32-caracteres
JWT_REFRESH_SECRET=clave-refresh-secreta-minimo-32-chars
JWT_EXPIRES_IN=15m
JWT_REFRESH_EXPIRES_IN=7d

# CORS
CORS_ORIGIN=http://localhost:5173

# Archivos
UPLOAD_DIR=./uploads
MAX_FILE_SIZE=20971520       # 20 MB — hardcap absoluto de multer

# SMTP — recuperación de contraseña
SMTP_HOST=mail.dominio.cl
SMTP_PORT=465
SMTP_SECURE=true
SMTP_USER=usuario@dominio.cl
SMTP_PASS=CAMBIAR_EN_PRODUCCION
SMTP_FROM=DOC360 <noreply@dominio.cl>
FRONTEND_URL=http://localhost:5173
RESET_TOKEN_EXPIRES_MINUTES=30
```

---

## Seguridad

- Cambiar `JWT_SECRET` y `JWT_REFRESH_SECRET` antes de producción (mínimo 32 chars)
- Cambiar contraseña SA de SQL Server antes de exponer en red
- El `.env` **nunca** debe subirse a Git (está en `.gitignore`)
- En producción usar `DB_ENCRYPT=true` y `DB_TRUST_CERT=false` con certificado válido
- Restringir `CORS_ORIGIN` a dominios específicos en producción

---

## Reglas del proyecto

1. **NUNCA modificar** ningún archivo dentro de `/legacy`
2. **NUNCA eliminar** archivos sin respaldo previo
3. Código nuevo solo en `/backend` y `/frontend`
4. No exponer datos productivos sin anonimizar

---

## Roadmap

- [x] Fase 0: Infraestructura (Docker, SQL Server, backup, JWT)
- [x] Fase 1: Auth + Dashboard + Documentos
- [x] Fase 2: Bandeja, Enviados, Trámites, Trazabilidad, Búsqueda, Archivos
- [x] Fase 3: Expedientes, Usuarios CRUD, Reportes + CSV, Configuración
- [ ] Fase 4: Derivación desde UI, notificaciones en tiempo real (WebSocket)
- [ ] Fase 5: Firma digital, módulos OIRS / Gabinete, modo oscuro
- [ ] Fase 6: CI/CD, monitoreo, tests automatizados (Vitest)
