# SISDOC v2 — Sistema de Gestión Documental Moderno
### Hospital Universitario Asociado de Puebla (HUAP)

Sistema de gestión documental institucional modernizado. Migración gradual del sistema legacy SISDOC a arquitectura enterprise moderna, manteniendo compatibilidad con la base de datos original.

---

## Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | Node.js + Express + TypeScript | 20 LTS |
| Base de datos | SQL Server 2022 | Docker |
| Frontend | React + Vite + TypeScript | 18 / 6 |
| UI | Tailwind CSS + shadcn/ui | 3 |
| Estado | Zustand + TanStack Query | 5 |
| Auth | JWT (access 15m + refresh 7d) | — |
| Contenedor | Docker + Docker Compose | 27+ |

---

## Inicio rápido

### Prerrequisitos
- Node.js 20+
- Docker Desktop (corriendo)
- Git

### Setup inicial (una sola vez)

```powershell
# 1. Clonar / ubicarse en el proyecto
cd c:\sisdoc-modernizado

# 2. Levantar SQL Server
docker compose up -d sqlserver

# 3. Restaurar base de datos SISDOC (si es primera vez o se perdió)
.\scripts\restore-db.ps1

# 4. Instalar dependencias
cd backend  && npm install && cd ..
cd frontend && npm install && cd ..
```

### Desarrollo diario

**Opción A — Script automático:**
```powershell
.\scripts\dev.ps1
```

**Opción B — Manual (3 terminales):**
```powershell
# Terminal 1: SQL Server
docker compose up -d sqlserver

# Terminal 2: Backend (con hot reload)
cd backend && npm run dev

# Terminal 3: Frontend (con hot reload)
cd frontend && npm run dev
```

---

## URLs de acceso

| Servicio | Local | Red local |
|---|---|---|
| Frontend | http://localhost:5173 | http://TU-IP:5173 |
| Backend API | http://localhost:3001/api/v1 | http://TU-IP:3001/api/v1 |
| Health check | http://localhost:3001/api/health | — |
| API Docs | http://localhost:3001/api-docs | — |

> **¿Cuál es tu IP?** Ejecuta `ipconfig` en Windows → busca la IPv4 de tu adaptador de red.
> El frontend está configurado con `host: 0.0.0.0` — ya acepta conexiones desde la red local.

---

## Módulos disponibles

| Módulo | Ruta | Estado |
|---|---|---|
| Dashboard | `/dashboard` | ✅ Operativo |
| Documentos | `/documentos` | ✅ Operativo |
| Bandeja de entrada | `/bandeja` | ✅ Operativo |
| Documentos enviados | `/enviados` | ✅ Operativo |
| Mis trámites | `/tramites` | ✅ Operativo |
| Trazabilidad | `/trazabilidad` | ✅ Operativo |
| Búsqueda avanzada | `/busqueda` | ✅ Operativo |
| Gestión de archivos | `/archivos` | ✅ Operativo |
| Expedientes | `/expedientes` | 🚧 Próximamente |
| Usuarios | `/admin/usuarios` | 🚧 Próximamente |
| Reportes | `/reportes` | 🚧 Próximamente |

---

## API REST

**Base URL:** `http://localhost:3001/api/v1`

```
# Autenticación
POST   /auth/login
POST   /auth/refresh
POST   /auth/logout
GET    /auth/me

# Documentos
GET    /documentos?q=&pagina=&porPagina=&idTipo=&idEstado=
GET    /documentos/:id
GET    /documentos/:id/historial
POST   /documentos

# Trámites
GET    /tramites
PATCH  /tramites/:id/recibir
PATCH  /tramites/:id/cerrar

# Catálogos (cacheable)
GET    /catalogos/tipos-documento
GET    /catalogos/estados
GET    /catalogos/dependencias
GET    /catalogos/descriptores
GET    /catalogos/prioridades

# Búsqueda
GET    /busqueda?q=texto&tipo=todos|documentos|tramites|funcionarios

# Archivos
GET    /archivos
POST   /archivos/upload          (multipart/form-data)
DELETE /archivos/:id

# Reportes
GET    /reportes/dashboard
GET    /reportes/actividad-reciente
```

---

## Docker Compose — comandos

```powershell
# Solo SQL Server (desarrollo)
docker compose up -d sqlserver

# Producción completa (build + arranque)
docker compose --profile prod up -d --build

# Ver estado
docker compose ps

# Logs en tiempo real
docker compose logs -f backend
docker compose logs -f nginx

# Detener todo
docker compose down

# Detener y eliminar volúmenes (¡CUIDADO! borra la BD)
docker compose down -v

# Reconstruir imagen del backend
docker compose --profile prod build backend --no-cache
```

### ⚠️ Si la base de datos se pierde

```powershell
# La BD se restaura automáticamente desde el backup
.\scripts\restore-db.ps1

# O manualmente:
docker exec sisdoc_sqlserver bash -c "/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P '<DB_PASSWORD>' -C -Q \"RESTORE DATABASE [SISDOC] FROM DISK='/var/opt/mssql/backup/respaldo anterior.bak' WITH MOVE 'sisdoc_Data' TO '/var/opt/mssql/data/SISDOC.mdf', MOVE 'sisdoc_Log' TO '/var/opt/mssql/data/SISDOC_log.ldf', REPLACE\""
```

---

## Estructura del proyecto

```
sisdoc-modernizado/
├── legacy/              ← Sistema original (NO MODIFICAR)
├── database/            ← Backups SQL Server (.bak)
│   └── respaldo anterior.bak
├── backend/             ← API Node.js + TypeScript
│   ├── src/
│   │   ├── config/      # env, database, swagger
│   │   ├── middleware/  # auth, errores, validación, logs
│   │   ├── modules/     # auth | documentos | tramites | catalogos
│   │   │                  reportes | archivos | busqueda
│   │   └── shared/      # types, utils
│   ├── prisma/          # Schema Prisma (SQL Server)
│   ├── uploads/         # Archivos subidos
│   ├── .env             # Variables de entorno (no commitear)
│   └── .env.example
├── frontend/            ← React 18 + TypeScript + Vite
│   └── src/
│       ├── app/         # Router + Providers
│       ├── components/  # ui | layout | shared | dashboard
│       ├── pages/       # auth | dashboard | documentos | tramites
│       │                  bandeja | enviados | trazabilidad
│       │                  busqueda | archivos
│       ├── lib/api/     # Axios client + endpoints
│       ├── stores/      # Zustand (auth)
│       └── hooks/
├── docker/              # Dockerfiles
│   ├── backend/Dockerfile
│   ├── frontend/Dockerfile
│   └── sqlserver/init.sh
├── nginx/               # Config nginx (producción)
├── scripts/             # Scripts PowerShell
│   ├── dev.ps1          # Levantar entorno de desarrollo
│   ├── setup.ps1        # Setup inicial
│   └── restore-db.ps1   # Restaurar BD SISDOC
├── docs/
│   └── analisis-claude/ # Análisis del sistema legacy
├── uploads/             # Archivos subidos (gitignored)
├── logs/                # Logs del backend (gitignored)
├── docker-compose.yml
├── .gitignore
└── README.md
```

---

## Variables de entorno

**Backend (`backend/.env`):**

```env
NODE_ENV=development
PORT=3001

# Base de datos
DB_USER=sa
DB_PASSWORD=CAMBIAR_EN_PRODUCCION
DB_SERVER=localhost
DB_PORT=1433
DB_DATABASE=SISDOC
DB_TRUST_CERT=true
DB_ENCRYPT=false

# JWT (cambiar en producción — mínimo 32 chars)
JWT_SECRET=clave-secreta-minimo-32-caracteres
JWT_REFRESH_SECRET=clave-refresh-secreta-minimo-32-chars
JWT_EXPIRES_IN=15m
JWT_REFRESH_EXPIRES_IN=7d

# CORS
CORS_ORIGIN=http://localhost:5173

# Archivos
UPLOAD_DIR=./uploads
MAX_FILE_SIZE=20971520
```

---

## Seguridad importante

- Cambiar `JWT_SECRET` y `JWT_REFRESH_SECRET` en producción (mínimo 32 chars aleatorios)
- Cambiar contraseña SA de SQL Server antes de exponer en red
- El `.env` real **nunca** debe subirse a Git (está en `.gitignore`)
- En producción, restringir `CORS_ORIGIN` a dominios específicos

---

## Reglas del proyecto

1. **NO modificar** ningún archivo dentro de `/legacy`
2. **NO eliminar** archivos sin respaldo previo
3. Código nuevo solo en `/backend` y `/frontend`
4. Documentar cambios en `/docs` antes de modificar módulos críticos
5. No exponer datos productivos sin anonimizar

---

## Roadmap

- [x] Fase 0: Infrastructure (Docker, SQL Server, backup)
- [x] Fase 1: Auth JWT + Dashboard + Documentos básico
- [x] Fase 2: Bandeja, Enviados, Trazabilidad, Búsqueda, Archivos
- [ ] Fase 3: CRUD completo documentos, derivaciones desde UI
- [ ] Fase 4: Expedientes, alertas, notificaciones
- [ ] Fase 5: Firma digital, módulos OIRS/Gabinete
- [ ] Fase 6: CI/CD, monitoreo, reportes avanzados
