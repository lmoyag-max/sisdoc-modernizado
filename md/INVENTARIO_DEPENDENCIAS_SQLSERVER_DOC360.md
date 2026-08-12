# Inventario de dependencias de SQL Server — DOC360

**Fecha:** 2026-08-12
**Alcance:** Solo lectura. Inventario exhaustivo de todo elemento del repositorio acoplado específicamente a SQL Server, con evidencia de archivo y línea, como insumo para la migración a PostgreSQL.

> Este documento reemplaza al `INVENTARIO_DEPENDENCIAS_MYSQL_DOC360.md` solicitado originalmente: el proyecto no usa MySQL en ningún punto (ver corrección de premisa en `AUDITORIA_FACTIBILIDAD_POSTGRESQL_DOC360.md`).

---

## 1. Driver y dependencias de paquete

| Elemento | Ubicación | Detalle |
|---|---|---|
| Driver de base de datos | `backend/package.json:24` | `"mssql": "^12.5.4"` — única dependencia de acceso a datos |
| Tipos manuales del driver | `backend/src/types/mssql.d.ts` | Declaración manual de `sql.config`, `ConnectionPool`, `Request` (`.input()`, `.query()`, `.execute()`), tipos `Int, VarChar, NVarChar, Char, Bit, DateTime, Date, Float, MAX`. **No cubre** `Transaction`, `PreparedStatement`, `ISOLATION_LEVEL`, `Decimal`, `BigInt`, `UniqueIdentifier`, `Binary`, `VarBinary` — porque el código de la app tampoco los usa |
| ORM | — | **Ninguno.** Cero coincidencias de `prisma`/`typeorm`/`sequelize`/`knex`/`drizzle` en `backend/package.json` ni `frontend/package.json` |
| Imagen Docker de BD | `docker-compose.yml:13` | `mcr.microsoft.com/mssql/server:2022-latest` |

---

## 2. Configuración de conexión y pool

**`backend/src/config/database.ts`**
```
pool: { max: 20, min: 2, idleTimeoutMillis: 30000 },
connectionTimeout: 15000,
requestTimeout: 30000,
options: { encrypt: env.DB_ENCRYPT, trustServerCertificate: env.DB_TRUST_CERT, enableArithAbort: true },
```
(líneas 5-23). Patrón singleton de módulo (`let pool: sql.ConnectionPool | null = null`, líneas 25-39), sin retry/backoff explícito. `closePool()` (líneas 41-47).

**`ensureIndexes()`** (líneas 49-76) — ejecuta DDL condicional contra el catálogo de sistema `sys.indexes`/`OBJECT_ID()` al boot del servidor Node. Específico de T-SQL.

**Variables de entorno** (`backend/src/config/env.ts:6-33`, validadas con Zod):
```
DB_USER, DB_PASSWORD, DB_SERVER, DB_PORT (default 1433), DB_DATABASE,
DB_TRUST_CERT (default true), DB_ENCRYPT (default false),
DATABASE_URL (opcional, declarado pero NUNCA usado en el código — 0 referencias fuera de env.ts)
```

---

## 3. Sintaxis T-SQL por categoría (con ubicación exacta)

### 3.1 `TOP N` (SQL Server-only)
| Archivo | Línea(s) |
|---|---|
| `busqueda.routes.ts` | 149, 211 |
| `archivos.routes.ts` | 113, 248 |
| `usuarios.routes.ts` | 150 |
| `reportes.routes.ts` | 94, 156, 235 (`TOP (@maxRows)`, parámetro dinámico) |
| `documento.repository.ts` | 352, 647 |
| `documento.service.ts` | 36 |
| `firma-gob.routes.ts` | 453, 717 |

### 3.2 `OFFSET ... FETCH NEXT` (portable, sintaxis casi idéntica en PG 9+)
| Archivo | Línea(s) |
|---|---|
| `busqueda.routes.ts` | 120, 139, 184, 202 |
| `usuarios.routes.ts` | 50 |
| `documento.repository.ts` | 119 |
| `tramite.routes.ts` | 111, 179 |
| `alertas.service.ts` | 311 |
| `jefatura.routes.ts` | 197 |

Patrón de total de páginas: `COUNT(*) OVER()` como window function en la misma query (`documento.repository.ts:110`, `busqueda.routes.ts`, `alertas.service.ts:307`) — cálculo final en TypeScript vía `Math.ceil()`. Portable sin cambios a PG.

### 3.3 `ISNULL()` (SQL Server-only; PG usa `COALESCE()`)
Ocurrencias representativas:
- `usuarios.routes.ts:42,84,250-252`
- `documento.repository.ts:268-269` (correlativos `num_interno`/`num_oficial`), `638-644` (6 usos en un INSERT de backup)
- `memorandum.routes.ts:484,511` (comparación null-safe para el cálculo de correlativo por dependencia)
- `catalogos.service.ts:8,45,101,102,117` (filtros de vigencia)
- `auth.service.ts:58,188`
- `password-reset.routes.ts:90`

### 3.4 `GETDATE()` (SQL Server-only; PG usa `NOW()`/`CURRENT_TIMESTAMP`)
Uso masivo (40+ ocurrencias, 13 archivos):
`documento.repository.ts` (280,296,314,479,494,497,521,536,542,575,577,610,612,642,644), `memorandum.routes.ts` (526,1004,1012,1060,1067,1172), `firma-gob.routes.ts` (81,140,536,581,604,641,679,700,749,756,768), `alertas.service.ts` (107,152), `auth.service.ts` (129,165,304,309), `tramite.routes.ts` (215,244), `jefatura.routes.ts` (172,173,337,414,531), `reportes.routes.ts` (67,68,88), `usuarios.routes.ts` (297), `password-reset.routes.ts` (168,204,240,247), `documento.service.ts` (184,309,366,422).

Usos incluyen tanto timestamps de auditoría como comparaciones de vigencia (`jefatura.routes.ts:172-173`: `j.vigencia_desde_titular <= CAST(GETDATE() AS DATE)`) y cálculo de expiración de tokens (`auth.service.ts:129`: `expires_at > GETDATE()`).

### 3.5 `CONVERT()` con código de estilo (SQL Server-only; PG usa `TO_CHAR()`)
- `firma-gob.routes.ts:37,180,181,843,912,970,974` — `CONVERT(VARCHAR, fecha, 120)` (estilo 120 = ISO 8601 sin milisegundos)
- `database/scripts/demo/03_validar_datos_demo_doc360.sql:106` — `CONVERT(VARCHAR, MIN(fecha_documento), 23)` (estilo 23 = `yyyy-mm-dd`)

### 3.6 `DATEADD` / `DATEDIFF`
- `firma-gob.routes.ts:812,891` — `DATEADD(DAY, 1, @fechaHasta)`
- `reportes.routes.ts:88,220` — `DATEADD(MONTH, -6, GETDATE())`, `DATEADD(DAY, 1, @fechaHasta)`
- `alertas.service.ts:152` — `DATEDIFF(DAY, t.fecha_sistema, GETDATE())`
- `memorandum.routes.ts:940,955` — `DATEDIFF(MINUTE, fecha_creacion, GETDATE())`, con comentario explícito de que el cálculo de expiración de código de Firma Simple se hace server-side

### 3.7 `SCOPE_IDENTITY()` / `OUTPUT INSERTED.*`
- `SCOPE_IDENTITY()`: único uso en `memorandum.routes.ts:885`
- `OUTPUT INSERTED.*` (patrón dominante, equivalente a `RETURNING` de PG):
  `archivos.routes.ts:273,338`; `configuracion.routes.ts:265,366`; `usuarios.routes.ts:162,182`; `documento.repository.ts:276,290,400` (con tabla variable `@docOut`/`@tramOut` como intermediario); `firma-gob.routes.ts:533`; `firma-gob.utils.ts:169`; `jefatura.routes.ts:449`; `roles.routes.ts:81`; `documento.service.ts:178`; `memorandum.routes.ts:1195`.
  No se encontró ningún `@@IDENTITY`.

### 3.8 Identificadores entre corchetes `[nombre]`
**Ninguno** en el código del backend — favorece la portabilidad (no hay que reescribir `[...]` → `"..."`).

---

## 4. Transacciones y hints de bloqueo

**No se usa `sql.Transaction` del driver en ningún punto** (0 resultados de `sql.Transaction`/`new Transaction`). Todas las transacciones son texto `BEGIN TRANSACTION...COMMIT` dentro de un único `.query()`, sin `TRY/CATCH...ROLLBACK` explícito en T-SQL (si el batch falla, SQL Server hace rollback implícito).

### 4.1 Hallazgo crítico — locking pesimista para correlativos

**`memorandum.routes.ts:504-532`** (correlativo `MEMO-AÑO-COD-NNNNNN`):
```sql
BEGIN TRANSACTION;
DECLARE @sigNumero INT;
SELECT @sigNumero = ISNULL(MAX(mg.numero), 0) + 1
FROM memo_generado mg WITH (TABLOCKX, HOLDLOCK)
INNER JOIN documento d ON d.id_documento = mg.id_documento
WHERE mg.anio = @anio AND ISNULL(mg.id_dependencia_origen, -1) = ISNULL(@idDep, -1);
-- INSERT INTO memo_generado (...) VALUES (...);
-- SELECT @corr AS correlativo, @sigNumero AS numero;
COMMIT TRANSACTION;
```
`TABLOCKX` (lock exclusivo de tabla completa) + `HOLDLOCK` (equivalente a `SERIALIZABLE` sobre el recurso) — documentado explícitamente en comentario (líneas 462-472) como decisión deliberada de simplicidad: fuerza serialización total de inserciones concurrentes en `memo_generado`, sin importar año/dependencia.

**Segundo uso idéntico — `memorandum.routes.ts:860-885`** (código de verificación Fase A Firma Simple, tabla `memorandum_firma_simple`).

**Tercer patrón — `documento.repository.ts:262-301`** (correlativos `num_interno`/`num_oficial`):
```sql
SELECT @nextInt = ISNULL(MAX(num_interno), 0) + 1,
       @nextOf  = ISNULL(MAX(num_oficial), 0) + 1
FROM documento WITH (UPDLOCK, HOLDLOCK);
```
Comentario explícito (líneas 240-243): *"Batch atómico: bloquea la tabla con UPDLOCK+HOLDLOCK para que dos requests concurrentes no lean el mismo MAX y generen num_interno duplicado."*

No se encontró ningún uso de `NOLOCK` ni `ROWLOCK`.

### 4.2 Transacciones simples (sin hints)
`documento.repository.ts:476-500` (`recepcionarDestinoAtomic`), `517-546` (`terminarDestinoAtomic`), `562-580` (`recepcionarDocumentoAtomic`), `597-615` (`terminarDocumentoAtomic`), `666-678` (`softDelete()`, ver cita completa en el documento principal).

### 4.3 Inconsistencia: `revertirDocumentoSinFirmar()` no transaccional
`backend/src/modules/firma-gob/firma-gob.utils.ts:255-303` — 8 llamadas `.query()` independientes, sin `BEGIN TRANSACTION` (a diferencia de sus pares `revertirMemorandumSinFirmar()` y `softDelete()`).

---

## 5. Búsqueda de texto — Full-Text Search

**Definición del índice:** `database/scripts/05-full-text-index.sql`
```sql
CREATE FULLTEXT CATALOG SisdocFTCatalog AS DEFAULT;                    -- línea 10
CREATE FULLTEXT INDEX ON documento(materia LANGUAGE 'Spanish')
  KEY INDEX PK_documento ON SisdocFTCatalog WITH CHANGE_TRACKING AUTO;  -- líneas 21-24
```
También sobre `funcionario.nombres`/`apellidos`.

**Consumo en runtime:** `backend/src/modules/busqueda/busqueda.routes.ts:115,136,153,171-221` — `CONTAINS(columna, @ftsQ)` con fallback automático a `LIKE` si el índice no existe, detectado por `esFTSError()` (líneas 11-15) inspeccionando el mensaje de error en busca de los códigos SQL Server 7601/7603/7613. Sanitización de query de usuario (líneas 70-75) elimina comillas y operadores booleanos FTS (`AND/OR/NOT/NEAR/FORMSOF/ISABOUT`) y comodines.

**Sin equivalente directo en PostgreSQL** — requiere `tsvector`/`tsquery` + índice GIN, con reescritura completa de la lógica de sanitización (sintaxis de `tsquery` es distinta).

---

## 6. Manejo de fechas, booleanos y JSON

- **Fechas:** `sql.DateTime`/`sql.Date` en `.input()`. Sin manejo explícito de zona horaria en columnas (`DATETIME`/`DATETIME2` "naive"). `firma-gob.utils.ts:188-200` es el único lugar con timezone explícito (`America/Santiago`, vía `Intl.DateTimeFormat`, para el payload JWT de FirmaGOB — no toca la BD).
- **Booleanos:** `sql.Bit` mapeado a `boolean` nativo de JS. Convive con flags legacy `CHAR(1)`: `original` (`'S'`), `resuelto` (`'S'`/otro, comentado como "legacy reutilizada" en `documento.repository.ts:25`), `medio` (`'F'`/`'D'`/null — 3 estados, no booleano puro), `vigencia` (`'S'`/otro, `catalogos.service.ts:8`).
- **JSON:** sin tipo JSON nativo ni `JSON_VALUE()`/`OPENJSON()` en ningún query. JSON serializado como texto en columnas `NVARCHAR`: `alertas.service.ts:454` (`alerta_log.destinatarios`), `firma-gob.utils.ts:158-159` (`firma_gob_logs.request_payload/response_payload`, `NVARCHAR(MAX)`).

---

## 7. Datos binarios

**Ninguno en la base de datos.** Archivos en filesystem (`backend/uploads/`, `multer.diskStorage`, `archivos.routes.ts:71-74`). `archivo_digital` solo guarda metadata: `archivo` (VARCHAR(50)), `ruta` (VARCHAR(50), de ahí los filenames cortos generados por `shortFilename()`, líneas 65-69), `tamano` (INT), `tipo_mime` (VARCHAR(100)). Migración de archivos = copia de filesystem, no problema de tipos SQL.

---

## 8. Stored procedures, triggers, vistas

- **Invocados por la app moderna: ninguno.** Grep de `EXEC`/`.execute(`/`sp_` en `backend/src` sin resultados reales (único match es un comentario en `documento.repository.ts:243`).
- **Legacy, no invocados:** `database/sp_legacy_fase2_backup_20260609.sql` — intento de export de ~118 stored procedures legacy vía `sp_helptext`, **truncado/corrupto** (cada bloque solo conserva el comentario de cabecera; el cuerpo real aparece cortado a ~20-40 caracteres). Nomenclatura reconocible: `busca_*`, `derivar_tramite*`, `Ingreso_Documento*`, `Mod_estado_tramite*`, `Mantenedor_Documentos`, `Mantenedor_Tramite`, `ingreso_us_*`, `modifica_u_*`. Creados entre 2009 y 2013 según comentarios de cabecera. `PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md` confirma independientemente que el conteo aproximado es ~140.
- **Triggers, vistas:** ninguno confirmado activo (`PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md` lo confirma explícitamente: "no hay triggers ni vistas").

---

## 9. Índices y catálogo de sistema

- `database/scripts/03-optimize-indexes.sql:11` — `CREATE INDEX IX_doc_fecha_sistema ON documento(fecha_sistema DESC) INCLUDE(...)` — cláusula `INCLUDE` (covering index), migrable a PG 11+ con sintaxis casi idéntica.
- `database/scripts/03-optimize-indexes.sql:10` y `12-correlativo-por-servicio.sql:42` — consultas a `sys.indexes`/`sys.key_constraints`, catálogos propietarios de SQL Server. Equivalente PG: `information_schema`/`pg_indexes`/`pg_constraint`.
- `database/scripts/12-correlativo-por-servicio.sql:53-56` — `CREATE UNIQUE INDEX uq_memo_correlativo_anio_dep ON memo_correlativo(anio, id_dependencia) WHERE id_dependencia IS NOT NULL;` — índice único filtrado, **migrable directo** a PG (índices parciales, sintaxis casi idéntica).
- `database/scripts/13-alerta-log-index-fecha.sql:17-18` — distinción `CLUSTERED`/`NONCLUSTERED` (arquitectura de almacenamiento propia de SQL Server; PG no tiene el concepto de forma equivalente, `CLUSTER` es una operación puntual, no una propiedad continua).
- `backend/src/config/database.ts:49-76` (`ensureIndexes()`) — DDL condicional al boot, contra `sys.indexes`/`OBJECT_ID()`.

---

## 10. Autoincrementales, DBCC y scripting T-SQL

- `IDENTITY(1,1)` recurrente en la mayoría de tablas (`04-create-admin-user.sql:14`, `05-memorandum-setup.sql`, etc.) — equivalente PG: `GENERATED ALWAYS AS IDENTITY` o `SERIAL`.
- `database/scripts/update-dependencias.sql:16` — `DBCC CHECKIDENT ('dependencia', RESEED, 0);` — comando propietario para resetear el contador IDENTITY. Equivalente PG: `ALTER SEQUENCE ... RESTART WITH ...`.
- `GO` como separador de lote (múltiples scripts) — comando de cliente `sqlcmd`/SSMS, sin existencia en el motor ni en `psql`.
- Variables `@nombre` + `DECLARE` (T-SQL puro) — en `02-clean-and-seed.sql:21,25` y muchos otros; PG usaría bloques `DO $$ ... $$` o funciones PL/pgSQL.
- Operador `+` para concatenación de strings (`04-create-admin-user.sql:83`) — PG usa `||`.
- `database/scripts/demo/00_respaldo_pre_demo.sql` — variables de scripting `sqlcmd` (`$(Stamp)`, líneas 41,50,96,99,116), cursores explícitos (`DECLARE tabla_cursor CURSOR LOCAL FAST_FORWARD`, línea 80, con `FETCH NEXT`/`@@FETCH_STATUS`).
- `database/scripts/demo/01_limpiar_datos_demo_doc360.sql` — `SET XACT_ABORT ON` (línea 33, comportamiento por defecto en PG), `RAISERROR(...) + SET NOEXEC ON` (líneas 39-69, patrón de scripting `sqlcmd` sin equivalente directo — en PG se resuelve con `RAISE EXCEPTION` en un bloque `DO`).
- `SELECT...INTO` para snapshots (`01-backup-docs.sql:12,17`, y 4 scripts más) — equivalente conceptual PG: `CREATE TABLE ... AS SELECT ...`.
- `DELETE <alias> FROM <tabla> <alias> JOIN ...` (multi-table delete, `clean-documentos-tramites.sql:35-37`) — en PG: `DELETE FROM x a USING y b WHERE ...`.
- `@@ROWCOUNT` (`clean-documentos-completo.sql:27`) — en PG: `GET DIAGNOSTICS ... ROW_COUNT`.
- `SET QUOTED_IDENTIFIER ON` (`12-correlativo-por-servicio.sql:6`) — sin equivalente necesario en PG.

---

## 11. Backups nativos

- `BACKUP DATABASE [SISDOC] TO DISK=...` — `scripts/backup-db.ps1/.sh`, `database/scripts/demo/00_respaldo_pre_demo.sql:52-54`. Genera `.bak` (formato binario propietario). Archivos `.bak` presentes en el repo: `database/backups/SISDOC_backup_20260713_074724.bak`, 3 archivos en `database/demo-doc360-backups/`.
- `RESTORE DATABASE [SISDOC] FROM DISK=... WITH MOVE ... REPLACE` — `scripts/restore-db.ps1/.sh`, `docker/sqlserver/init.sh` (no referenciado automáticamente por ningún `docker-compose*.yml`).
- Retención documentada: 30 días, tarea programada semanal (`README.md`, comentario en `scripts/backup-db.ps1` líneas 87-97).

---

## 12. Multi-schema

`database/scripts/demo/00_respaldo_pre_demo.sql` (comentario líneas 64-69) y `01_limpiar_datos_demo_doc360.sql:91` confirman que `memorandum_firma_simple` y `firma_gob_logs` viven en el **schema `sisdoc`** (no `dbo`). PostgreSQL soporta múltiples schemas de forma directa — migración de este concepto sin complicaciones.

---

## 13. Hallazgos colaterales de higiene (no bloqueantes para la migración, pero a corregir)

- `database/scripts/migrate-jefaturas.ps1:15` — password de SQL Server en texto plano en connection string.
- `database/scripts/09-ruts-firmantes.sql` (líneas 15-265, 118 sentencias) — RUTs reales de funcionarios del hospital (PII), no anonimizar antes de reutilizar como fixture de pruebas.
- Discrepancia de puerto documentado: `docker-compose.yml:24` (`15433`) vs. `CLAUDE.md`/`README.md`/`backend/.env.example` (`11433`, que sí coincide con `docker-compose.preprod.yml`).

---

## 14. Resumen cuantitativo

| Categoría | Ocurrencias | Archivos afectados |
|---|---|---|
| `GETDATE()` | 40+ | 13 |
| `ISNULL()` | 30+ | 10+ |
| `TOP N` | 10 | 8 |
| `OFFSET...FETCH NEXT` | 10+ | 7 |
| `CONVERT(..., 120/23)` | 7 | 2 |
| `DATEADD`/`DATEDIFF` | 5 | 4 |
| `OUTPUT INSERTED.*` | 12+ | 9 |
| Parámetros `.input()` nombrados | 596 | 21 |
| Bloques `WITH (TABLOCKX\|UPDLOCK\|HOLDLOCK)` | 3 | 2 |
| Índices con sintaxis SQL Server-only (`INCLUDE`, `sys.*`) | 4 scripts | — |
| Stored procedures legacy (no invocados) | ~118-140 | 0 (no invocados) |
| Triggers / vistas activos | 0 | — |
