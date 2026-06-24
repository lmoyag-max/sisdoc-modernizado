# DOC360 — Guía de despliegue en Preproducción (Docker)

Esta guía es para el equipo de Operaciones que levantará DOC360 en un servidor local mediante Docker. No requiere conocer el código fuente.

---

## ⚠️ Léeme primero: sobre la base de datos

DOC360 es una modernización que **corre sobre el esquema de la base de datos legacy SISDOC** (documentos, trámites, usuarios, funcionarios, dependencias, etc. — cientos de tablas heredadas del sistema anterior en ASP clásico). Los scripts en `database/scripts/` **no crean ese esquema desde cero** — solo agregan tablas nuevas (roles, memorándum, firma electrónica, auditoría) **sobre una base SISDOC que ya debe existir**.

Esto significa que, para tener un ambiente 100% funcional, Operaciones necesita **una de estas dos cosas** (a decidir con el equipo de desarrollo, no asumido aquí):
1. Un backup `.bak` de SISDOC ya sanitizado (sin datos reales de pacientes/personal), restaurado primero, o
2. Un script de creación de esquema desde cero (no existe hoy en este repositorio — habría que generarlo aparte).

Sin uno de los dos, el sistema **no tendrá tablas base** (`documento`, `usuario`, `funcionario`, `dependencia`, `tipo_documento`, etc.) y los scripts de `database/scripts/` fallarán al ejecutarse.

---

## Requisitos del servidor

- Docker Engine 24+ y Docker Compose v2 (`docker compose version`)
- 4 GB RAM mínimo para SQL Server (recomendado 8 GB)
- 10 GB de disco libre (sin contar el tamaño de la base de datos real)
- Puertos libres en el host: **80**, **3001**, **11433** (ver tabla de puertos)

## Puertos utilizados

| Puerto (host) | Servicio | Acceso |
|---|---|---|
| `80` | Nginx (frontend + proxy `/api`) | Público — esta es la URL que usan los usuarios |
| `3001` | Backend API directo | Solo para diagnóstico/healthcheck — no es necesario exponerlo a usuarios |
| `127.0.0.1:11433` | SQL Server | Solo localhost — para administración con herramientas externas (Azure Data Studio, sqlcmd) |

## Variables de entorno necesarias

**1. Raíz del proyecto — copiar `.env.example` → `.env`:**
```
MSSQL_SA_PASSWORD=<contraseña fuerte para el usuario sa>
```

**2. `backend/.env.example` → `backend/.env`** — completar:
- `DB_USER`, `DB_PASSWORD` — usuario de aplicación (no usar `sa`; crear un login `doc360_app` con `db_owner` en SISDOC, sin `sysadmin`)
- `JWT_SECRET`, `JWT_REFRESH_SECRET` — generar con `openssl rand -base64 64` (o `[Convert]::ToBase64String([byte[]](1..64|%{Get-Random -Max 256}))` en PowerShell)
- `CORS_ORIGIN` — dominio/IP real del servidor (ej. `http://10.0.0.5`)
- `SMTP_*` — opcional; si se deja vacío, el sistema funciona pero la recuperación de contraseña por email no enviará correos
- El resto de valores por defecto del `.env.example` son seguros para preprod

> El **frontend no tiene variables de entorno** — usa rutas relativas (`/api/v1`) servidas por el proxy de nginx, así que no hay nada que configurar ahí.

## Comandos Docker

```bash
# Levantar todo (build + arranque)
./scripts/start-preprod.sh              # Linux/Mac
.\scripts\start-preprod.ps1             # Windows

# Equivalente manual
docker compose -f docker-compose.preprod.yml up -d --build

# Ver estado
docker compose -f docker-compose.preprod.yml ps

# Ver logs
docker compose -f docker-compose.preprod.yml logs -f            # todos
docker compose -f docker-compose.preprod.yml logs -f backend    # solo backend

# Detener (conserva datos)
docker compose -f docker-compose.preprod.yml down

# Detener y borrar también los volúmenes (¡borra la base de datos!)
docker compose -f docker-compose.preprod.yml down -v
```

## Backup y restauración de la base de datos

```bash
./scripts/backup-db.sh                                    # genera database/backups/SISDOC_backup_<fecha>.bak
./scripts/restore-db.sh /var/opt/mssql/backup/archivo.bak  # restaura (pide --force si SISDOC ya existe)
```
En Windows: `scripts\backup-db.ps1` / `scripts\restore-db.ps1` (mismo comportamiento).

## Inicialización del esquema (una vez restaurada la base legacy)

Ejecutar en este orden contra la base `SISDOC` (todos son idempotentes — verifican antes de crear):

```
database/scripts/04-create-admin-user.sql
database/scripts/05-memorandum-setup.sql
database/scripts/06-auditoria-table.sql
database/scripts/06-jefaturas-firma-gob.sql
database/scripts/07-segundo-subrogante.sql
database/scripts/08-rut-firmantes.sql
database/scripts/10-firma-gob-logs.sql
database/scripts/11-fix-tipo-firmante-length.sql
database/scripts/12-correlativo-por-servicio.sql
```

**Opcionales:**
- `03-optimize-indexes.sql` — mejora de rendimiento, no bloqueante
- `05-full-text-index.sql` — solo si SQL Server tiene Full-Text Search instalado (`SELECT FULLTEXTSERVICEPROPERTY('IsFullTextInstalled')` debe devolver `1`)

**⚠️ NO ejecutar en preprod** (datos reales de HUAP o utilidades de limpieza de un ambiente de desarrollo específico):
- `01-backup-docs.sql`, `02-clean-and-seed.sql`, `clean-documentos-completo.sql`, `clean-documentos-tramites.sql` — limpieza/seed de un ambiente de desarrollo puntual
- `09-ruts-firmantes.sql` — **contiene RUTs reales del personal directivo de HUAP** hardcodeados; configura tus propias jefaturas desde `/admin/jefaturas` en vez de ejecutar este script
- `update-dependencias.sql`, `update-roles-usuarios.sql`, `migrate-jefaturas.ps1` — parches históricos de una migración puntual ya aplicada; re-ejecutarlos sobre una base nueva no es necesario y algunos son destructivos (reemplazan tablas completas)

Después de los scripts: el primer usuario admin queda creado por `04-create-admin-user.sql` — revisar ese script para la contraseña inicial y **cambiarla en el primer login**.

## Validaciones post-despliegue

```bash
curl http://localhost/api/health                 # { "status": "ok" } esperado
curl http://localhost:3001/api/health             # backend directo
docker compose -f docker-compose.preprod.yml ps   # los 3 servicios deben decir "healthy" o "running"
```

## Checklist de pruebas funcionales

- [ ] Login con usuario admin
- [ ] Dashboard carga métricas sin error
- [ ] Crear documento normal
- [ ] Crear memorándum y confirmar correlativo (`MEMO-AAAA-COD-NNNNNN`)
- [ ] Adjuntar archivo a un documento
- [ ] Visualizar PDF del memorándum generado
- [ ] Ver trazabilidad completa del documento
- [ ] Bandeja de entrada muestra trámites pendientes
- [ ] Enviados muestra documentos despachados
- [ ] Administración → Usuarios, Roles, Configuración cargan sin error
- [ ] Logs del backend sin errores críticos (`docker compose -f docker-compose.preprod.yml logs backend | grep -i error`)

## Notas de seguridad para este paquete

- No hay credenciales reales en ningún archivo `.example` ni en los Dockerfiles/compose.
- `database/scripts/09-ruts-firmantes.sql` contiene RUTs reales — excluido de la inicialización recomendada (ver arriba).
- Los backups `.bak` existentes en `database/` (con datos reales de HUAP) y la carpeta `evidencia-firmagov/` (tokens/PDFs reales de pruebas de FirmaGob) están en `.gitignore` y **no deben copiarse** al servidor de Operaciones bajo ningún escenario.
