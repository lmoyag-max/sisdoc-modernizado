# Auditoría de factibilidad — Migración DOC360 de SQL Server a PostgreSQL

**Fecha:** 2026-08-12
**Alcance:** Auditoría de solo lectura. No se modificó código, configuración, datos ni infraestructura durante esta revisión.
**Autor:** Auditoría técnica asistida (Claude Code), a solicitud del equipo DOC360.

> **Nota de corrección de premisa (importante):** la solicitud original de esta auditoría asumía que DOC360 usa **MySQL** como motor de origen. Se verificó directamente en el repositorio que esto es incorrecto: el proyecto usa **SQL Server 2022** (`mcr.microsoft.com/mssql/server:2022-latest`, ver `docker-compose.yml:13`) accedido mediante el driver nativo `mssql` v12.5.4 (`backend/package.json:24`), sin ORM. No existe absolutamente ninguna dependencia de MySQL en el repositorio (paquete, imagen Docker, dump, script). Esta auditoría se realizó con el alcance corregido: **SQL Server 2022 → PostgreSQL**, conservando el mismo rigor, estructura y checklist de incompatibilidades solicitado originalmente, adaptado al motor real. Por esta razón, dos de los seis entregables se renombraron de `..._MYSQL_...` a `..._SQLSERVER_...` para no introducir un documento formal con un nombre técnicamente falso.

---

## 1. Resumen ejecutivo

DOC360 es un sistema de gestión documental institucional (HUAP) que reemplaza un sistema legacy en ASP clásico / SQL Server 2005. La aplicación moderna (Node.js 20 + TypeScript + Express, React + Vite) mantiene el mismo motor de base de datos que el sistema legacy — **SQL Server**, ahora en su versión 2022, corriendo en Docker con persistencia por volumen — y accede a él con **queries SQL directas** (sin ORM), a través del driver `mssql`.

La evaluación de factibilidad concluye:

> ## Factibilidad: **MEDIA**
> *Migración posible, pero requiere modificaciones relevantes — principalmente el rediseño del mecanismo de generación de correlativos (actualmente basado en locking pesimista `TABLOCKX`/`HOLDLOCK`/`UPDLOCK` de SQL Server) y la reescritura de la búsqueda de texto completo (`CONTAINS()` → `tsvector`/`tsquery`), sumado a un volumen mecánico considerable de reescritura de sintaxis T-SQL en ~21 archivos de módulos, sin ninguna red de pruebas automatizadas que valide la equivalencia de comportamiento.*

No se encontraron bloqueadores estructurales duros (no hay stored procedures activos, no hay ORM que migrar, no hay datos binarios en la base de datos, no hay inyección SQL por concatenación de input). El riesgo principal no es de "portabilidad de sintaxis" sino de **preservar exactamente la semántica transaccional y de concurrencia** que protege la numeración correlativa de documentos y memorándums — una regla de negocio no negociable según `CLAUDE.md`.

---

## 2. Arquitectura actual

| Capa | Tecnología | Evidencia |
|---|---|---|
| Backend | Node.js 20 + TypeScript 5.7 + Express 4 | `backend/package.json` |
| Acceso a datos | Driver nativo `mssql` v12.5.4, SQL crudo, sin ORM | `backend/package.json:24`; cero coincidencias de `prisma`/`typeorm`/`sequelize`/`knex`/`drizzle` en `package.json` de backend y frontend |
| Base de datos | SQL Server 2022, contenedor `sisdoc_sqlserver` | `docker-compose.yml:13` |
| Persistencia | Volumen nombrado `sisdoc_sqlserver_data` + bind mount `./database` para backups | `docker-compose.yml` (servicio `sqlserver`) |
| Pool de conexiones | `sql.ConnectionPool`, singleton de módulo, `max:20 min:2 idleTimeoutMillis:30000` | `backend/src/config/database.ts:5-39` |
| Transacciones | **Ninguna vía `sql.Transaction` del driver** — todas son texto T-SQL (`BEGIN TRANSACTION...COMMIT`) dentro de un único `.query()` | confirmado por grep, 0 resultados de `sql.Transaction`/`new Transaction` en `backend/src` |
| Tipos del driver | Declaración manual (`mssql` v12 no trae `@types/mssql`) | `backend/src/types/mssql.d.ts` |
| Módulos de negocio | 15 módulos bajo `backend/src/modules/`, cada uno con SQL inline (solo `documentos` separa un `*.repository.ts`) | estructura de `backend/src/modules/` |
| Frontend | React 18 + Vite 6, sin dependencia de BD | `frontend/package.json` |
| Orquestación | Docker Compose, 3 servicios (`sqlserver`, `backend` perfil `prod`, `nginx` perfil `prod`) + variante `docker-compose.preprod.yml` autocontenida | `docker-compose.yml`, `docker-compose.preprod.yml` |
| CI/CD | **No existe** (sin `.github/workflows`, sin pipeline de ningún tipo) | búsqueda exhaustiva sin resultados |
| Pruebas automatizadas | **No existen** (backend ni frontend) | búsqueda exhaustiva sin resultados fuera de `node_modules` |
| Stored procedures activos | **Ninguno invocado** por la app moderna | grep de `EXEC`/`.execute(`/`sp_` en `backend/src` sin resultados reales (el único match es un comentario) |

---

## 3. Evidencias encontradas — hallazgos priorizados

Cada hallazgo incluye archivo, componente, evidencia textual, impacto, nivel de riesgo y acción recomendada. El detalle exhaustivo de cada patrón de sintaxis (con todas sus ocurrencias) está en `INVENTARIO_DEPENDENCIAS_SQLSERVER_DOC360.md`.

### 3.1 — Crítico

| # | Hallazgo | Archivo / línea | Evidencia | Impacto | Riesgo | Acción recomendada |
|---|---|---|---|---|---|---|
| C1 | Generación de correlativos con locking pesimista exclusivo de SQL Server | `backend/src/modules/memorandum/memorandum.routes.ts:504-532`, `:860-885`; `backend/src/modules/documentos/documento.repository.ts:262-301` | `FROM memo_generado mg WITH (TABLOCKX, HOLDLOCK)` / `FROM documento WITH (UPDLOCK, HOLDLOCK)` — cálculo `MAX(numero)+1` bajo lock exclusivo de tabla, dentro de `BEGIN TRANSACTION...COMMIT` en texto plano | Es el mecanismo que garantiza unicidad y no-duplicación de `num_interno`, `num_oficial` y del correlativo `MEMO-AÑO-COD-NNNNNN` — regla de negocio no negociable (ver `CLAUDE.md`, sección "Correlativos de memorándum"). `TABLOCKX`/`HOLDLOCK`/`UPDLOCK` no existen en PostgreSQL. | **Crítico** | Rediseñar explícitamente (no traducir 1:1). Opciones evaluadas en `PLAN_MIGRACION_POSTGRESQL_DOC360.md` Fase 1: `SELECT ... FOR UPDATE` sobre fila de contador dedicada, o tabla de contadores con `INSERT ... ON CONFLICT DO UPDATE ... RETURNING`. **No usar `SEQUENCE` nativa de PG** sin modificación, porque el negocio exige reutilización automática de números liberados al eliminar un documento (`CLAUDE.md`), comportamiento que las secuencias de PG no ofrecen de forma nativa. |
| C2 | Ausencia total de pruebas automatizadas | Todo el repo | Búsqueda de `*.test.ts`/`*.spec.ts`/`vitest.config.*` sin resultados fuera de `node_modules` | No existe forma de validar automáticamente que el comportamiento post-migración sea idéntico al actual, especialmente para C1 y para las 4 funciones transaccionales de `documento.repository.ts` (`recepcionarDestinoAtomic`, `terminarDestinoAtomic`, `recepcionarDocumentoAtomic`, `terminarDocumentoAtomic`, líneas 476-615) | **Crítico** (bloqueante para autorizar producción, no para el análisis) | Construir suite de pruebas de integración *antes* de migrar (ver `PLAN_PRUEBAS_MIGRACION_POSTGRESQL_DOC360.md`), ejecutable contra ambos motores en paralelo durante la Fase 6 (piloto). |

### 3.2 — Alto

| # | Hallazgo | Archivo / línea | Evidencia | Impacto | Riesgo | Acción recomendada |
|---|---|---|---|---|---|---|
| A1 | Búsqueda de texto completo nativa de SQL Server | `backend/src/modules/busqueda/busqueda.routes.ts:115,136,153,171-221`; `database/scripts/05-full-text-index.sql` | `CONTAINS(columna, @ftsQ)` con fallback a `LIKE` vía detección de errores 7601/7603/7613 (`esFTSError()`, líneas 11-15) | `CONTAINS()` no existe en PostgreSQL. Afecta el módulo de Búsqueda global (documentos, funcionarios) usado en producción. | **Alto** | Reescribir a `tsvector`/`tsquery` con `to_tsquery()`/`plainto_tsquery()` español + índice GIN. Reescribir también la sanitización de queries de usuario (líneas 70-75), que hoy remueve sintaxis booleana específica de FTS de SQL Server (`AND/OR/NOT/NEAR/FORMSOF/ISABOUT`) — la sintaxis de `tsquery` de PG es distinta y requiere su propia sanitización. |
| A2 | Volumen mecánico de reescritura de sintaxis T-SQL | 21 archivos de módulos bajo `backend/src/modules/**` | Decenas/cientos de ocurrencias de `GETDATE()`, `ISNULL()`, `TOP N`, `OFFSET...FETCH NEXT`, `CONVERT(VARCHAR, fecha, 120)`, `DATEADD`/`DATEDIFF`, `OUTPUT INSERTED.*`, parámetros nombrados `@x` (596 usos de `.input()`) | Sin capa ORM que centralice la traducción, cada ocurrencia debe reescribirse y probarse manualmente. Alto volumen, bajo riesgo individual por ocurrencia. | **Alto** (por volumen, no por complejidad) | Ver matriz de equivalencias completa en `MATRIZ_COMPATIBILIDAD_SQLSERVER_POSTGRESQL.md`. Recomendable adoptar un query builder ligero (ej. Kysely o similar, no un ORM completo) para reducir el riesgo de reescritura manual masiva y unificar parámetros nombrados/posicionales. |
| A3 | `.bak` de SQL Server es formato binario propietario, sin equivalente en PG | `scripts/backup-db.ps1/.sh`, `scripts/restore-db.ps1/.sh`, `docker/sqlserver/init.sh`, `database/scripts/demo/00_respaldo_pre_demo.sql:52-54` | `BACKUP DATABASE [SISDOC] TO DISK=... WITH FORMAT, COMPRESSION, CHECKSUM` | Toda la estrategia operacional de backup/restore (incluida la tarea programada semanal documentada en `README.md`) debe reconstruirse desde cero con herramientas de PostgreSQL (`pg_dump`/`pg_basebackup`/WAL archiving) | **Alto** (operacional, no de datos) | Ver Fase 3 y Fase 0 del plan de migración. |

### 3.3 — Medio

| # | Hallazgo | Archivo / línea | Evidencia | Impacto | Riesgo | Acción recomendada |
|---|---|---|---|---|---|---|
| M1 | Convención de booleanos mixta en el esquema | `documento.repository.ts` (comentario línea 25), `catalogos.service.ts:8` | `BIT` real (`activo`, `alertas.activo`) conviviendo con flags `CHAR(1)` legacy: `original` (`'S'`), `resuelto` (`'S'`/otro), `medio` (`'F'`/`'D'`/null), `vigencia` (`'S'`/otro) | Riesgo de mapeo inconsistente si se traduce automáticamente `CHAR(1)` a `BOOLEAN` de PG sin revisar caso por caso | **Medio** | Mapear explícitamente cada columna en la Fase 1 (diseño de esquema PG), documentando cuáles se normalizan a `BOOLEAN` real y cuáles se preservan como `CHAR(1)`/`VARCHAR` con `CHECK` por compatibilidad con lógica legacy que las lee con múltiples valores (`medio` tiene 3 estados, no es booleano puro). |
| M2 | Collation no documentado ni verificado | Ningún script contiene `COLLATE` explícito | Confirmado por búsqueda exhaustiva | El comportamiento de comparación/orden de texto en español (tildes, mayúsculas) depende del collation del servidor SQL Server, no capturado en el repo | **Medio** | Ejecutar `SELECT DATABASEPROPERTYEX('SISDOC','Collation')` contra la BD viva **antes** de migrar (paso obligatorio de Fase 0) y definir el collation/locale PG equivalente (`es_CL.UTF-8` o `ICU es-CL`), validando `ORDER BY`/`LIKE` con datos reales con tildes y ñ. |
| M3 | Zona horaria no confirmada; `GETDATE()` usa reloj del servidor | Uso masivo de `GETDATE()` en 13+ archivos backend | Ningún uso de `DATETIMEOFFSET`; todas las columnas de fecha son "naive" | Si el servidor SQL Server no corre en UTC, la migración a `TIMESTAMPTZ` de PG (recomendado) requiere una decisión explícita de conversión, no automática | **Medio** | Confirmar zona horaria del contenedor SQL Server actual (`docker-compose.yml:20` fija `TZ=America/Santiago` para el contenedor — dato ya disponible, usar como base) y decidir en Fase 1 si se migra a `TIMESTAMPTZ` (recomendado, con conversión explícita) o se preserva `TIMESTAMP WITHOUT TIME ZONE` para minimizar riesgo de reinterpretación de fechas históricas. |
| M4 | `LIKE` case-sensitivity difiere entre motores | `documento.repository.ts:73`, `busqueda.routes.ts` (fallback), `catalogos.service.ts`, `alertas.service.ts:213-214,233` | `LIKE '%...%'` parametrizado, sin `COLLATE` explícito | `LIKE` es case-insensitive en SQL Server (collation CI por defecto) pero case-sensitive en PostgreSQL por defecto | **Medio** | Usar `ILIKE` en PG, o definir columnas relevantes con `citext`, validado contra M2. |
| M5 | Inconsistencia transaccional en rollback de FirmaGOB | `backend/src/modules/firma-gob/firma-gob.utils.ts:255-303` (`revertirDocumentoSinFirmar`) | 8 llamadas `.request().query()` independientes, **sin** `BEGIN TRANSACTION`, a diferencia de `revertirMemorandumSinFirmar()` y `softDelete()` que sí son transaccionales | Riesgo de documento huérfano si el proceso Node falla a mitad de la secuencia de borrado | **Medio** (hallazgo de calidad, no exclusivo de la migración, pero debe corregirse *antes* o *durante* la migración para no propagar el defecto a PostgreSQL) | Envolver en transacción explícita, igual patrón que `softDelete()`. Recomendado como fix independiente, no condicionado a la migración. |
| M6 | Discrepancia de puertos documentados | `docker-compose.yml:24` usa `127.0.0.1:15433:1433`; `CLAUDE.md`, `README.md` y `backend/.env.example` documentan `11433` (que sí coincide con `docker-compose.preprod.yml`) | Confirmado por lectura directa de ambos archivos | Riesgo de confusión operacional al preparar el entorno de migración, no un riesgo de portabilidad de datos | **Medio** (higiene documental) | Unificar antes de iniciar cualquier trabajo de migración, para evitar apuntar herramientas de comparación (Fase 6) al puerto equivocado. |

### 3.4 — Bajo / observación

| # | Hallazgo | Archivo / línea | Evidencia | Impacto | Riesgo | Acción recomendada |
|---|---|---|---|---|---|---|
| B1 | ~118-140 stored procedures legacy no invocados por el backend moderno | `database/sp_legacy_fase2_backup_20260609.sql` (920 líneas, export truncado/corrupto); `PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md` | Confirmado que el backend TypeScript no los invoca (grep sin resultados reales de `EXEC`/`.execute(`) | Estos SPs pueden contener lógica de negocio legacy relevante para módulos aún no migrados del sistema ASP clásico, pero no están en el camino crítico de DOC360 moderno | **Bajo** (para DOC360 moderno), **brecha de evidencia** (para decisiones legacy) | Si se planea descontinuar por completo el ASP clásico legacy, extraer y archivar la definición completa de estos SPs desde la BD viva (el backup actual está corrupto) antes de apagar SQL Server, por completitud histórica — no bloquea la migración de DOC360. |
| B2 | `DATABASE_URL` declarado en `env.ts` pero nunca usado | `backend/src/config/env.ts:16` | `DATABASE_URL: z.string().optional()` sin ninguna referencia fuera de `env.ts` | Ninguno funcional; posible vestigio de una intención previa de usar connection string estilo URL (común en Postgres/Prisma) | **Bajo** | Eliminar o, alternativamente, aprovechar como el mecanismo real de configuración de conexión PG en la Fase 2 (`postgres://user:pass@host:port/db`). |
| B3 | Credencial en texto plano en script operacional | `database/scripts/migrate-jefaturas.ps1:15` | Password de SQL Server hardcodeado en connection string | Hallazgo de higiene de seguridad, tangencial a la migración pero cualquier plan la tocará | **Bajo/seguridad** | Rotar la credencial y mover a variable de entorno antes de archivar o reutilizar el script. |
| B4 | Datos personales (RUT) en script versionado | `database/scripts/09-ruts-firmantes.sql` (118 sentencias UPDATE, líneas 15-265) | RUTs reales de funcionarios del hospital | Cualquier copia de este repo (o de un entorno de prueba de migración) expone PII si no se trata como dato sensible | **Bajo/cumplimiento** | Confirmar que este script está excluido de cualquier entorno de prueba compartido fuera del personal autorizado; no usar como fixture de pruebas de migración sin anonimizar. |
| B5 | `ensureIndexes()` ejecuta DDL condicional contra catálogo de sistema al boot | `backend/src/config/database.ts:49-76` | Consulta `sys.indexes`/`OBJECT_ID()` | No portable literalmente a PG (`pg_indexes` es el catálogo equivalente) | **Bajo** | Reescribir usando `information_schema`/`pg_indexes` en Fase 2. |

**Hallazgos positivos (reducen el riesgo de migración):**
- Sin inyección SQL por concatenación: 100% de los valores pasan por parámetros (`.input()`); el único patrón de "columna dinámica" (`SET ${col} = ...`) usa siempre una whitelist fija en código, nunca input directo del usuario.
- Sin stored procedures, triggers ni vistas activos en el camino de ejecución de la app moderna (`PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md` confirma explícitamente "no hay triggers ni vistas").
- Sin datos binarios en la base de datos — los archivos viven en filesystem (`backend/uploads/`), la BD solo referencia metadata (`archivo_digital.ruta` VARCHAR(50)). La migración de archivos adjuntos es una copia de filesystem, no un problema de tipos de datos SQL.
- Sin ORM que además haya que migrar — la lógica está en SQL crudo, auditable directamente línea por línea.
- Identificadores de tabla/columna sin corchetes `[...]` en ningún query del backend — no hay que reescribir delimitadores.

---

## 4. Evaluación de rendimiento

**No se ejecutó ninguna prueba de carga ni benchmark como parte de esta auditoría** (fuera del alcance de una revisión de solo código). No debe asumirse que PostgreSQL será más rápido — la evidencia del repositorio sugiere que el rendimiento actual depende principalmente de:

- **Diseño de índices ad-hoc, no sistemático**: los índices existentes (`database/scripts/03-optimize-indexes.sql`, `13-alerta-log-index-fecha.sql`) fueron agregados reactivamente sobre columnas específicas (`fecha_sistema`, `fecha_envio`), no como resultado de un análisis sistemático de plan de ejecución.
- **Ausencia de pool tuning documentado**: `max:20 min:2` (`database/database.ts:16`) son valores por defecto razonables pero no se encontró evidencia de que hayan sido ajustados en base a medición real de concurrencia.
- **Paginación correcta**: usa `OFFSET...FETCH` + `COUNT(*) OVER()` de forma consistente (no hay antipatrón de traer todo el dataset a memoria para paginar en la app), salvo la exportación CSV que según `CLAUDE.md` mantiene hasta 50.000 filas en memoria (mejora pendiente, no bloqueante).
- **Lock pesado deliberado en correlativos** (hallazgo C1): es un cuello de botella de concurrencia *por diseño* (documentado como decisión consciente de simplicidad sobre performance en el código), no un problema del motor SQL Server en sí.
- **Full-Text Search con fallback a `LIKE`**: si el índice FTS no existe o fallara, el sistema degrada a `LIKE '%...%'` sin índice — esto sería lento en ambos motores por igual ante volumen alto, no es un diferencial MySQL/PostgreSQL/SQL Server.

**Métricas y pruebas que deben ejecutarse antes de decidir por rendimiento** (no se hicieron, quedan como precondición de cualquier autorización):
1. Latencia p50/p95/p99 de los endpoints más usados (`GET /documentos`, `GET /busqueda`, `GET /reportes/dashboard`) bajo carga realista, en SQL Server actual vs. PostgreSQL en el piloto (Fase 6).
2. Throughput de creación concurrente de documentos y memorándums (10-50 requests concurrentes) — mide directamente el impacto del rediseño del hallazgo C1.
3. Tiempo de generación de reportes/exportación CSV con volumen simulado de crecimiento a 2-3 años (no solo el volumen actual de desarrollo, que es bajo — ver sección 10 de infraestructura, sin dato de tamaño de producción real capturado en el repo).
4. Uso de CPU/memoria del contenedor de BD bajo la misma carga en ambos motores, con los mismos límites de recursos Docker.
5. Comparación de plan de ejecución (`EXPLAIN`/`EXPLAIN ANALYZE` en PG vs. `SET STATSTICS IO/TIME` en SQL Server) para las 5-10 queries más frecuentes.
6. Prueba específica de concurrencia en generación de correlativos: N clientes simultáneos creando memorándums del mismo año/dependencia, verificando cero duplicados y midiendo tiempo de espera por lock.

---

## 5. Dependencias específicas del motor de origen (resumen)

Ver inventario exhaustivo en `INVENTARIO_DEPENDENCIAS_SQLSERVER_DOC360.md`. Resumen cuantitativo:

| Categoría | Cantidad aproximada | Portabilidad |
|---|---|---|
| `GETDATE()` | 40+ ocurrencias en 13 archivos | Mecánica (`NOW()`/`CURRENT_TIMESTAMP`) |
| `ISNULL()` | 30+ ocurrencias en 10+ archivos | Mecánica (`COALESCE()`) |
| `TOP N` | 10 ocurrencias en 8 archivos | Mecánica (`LIMIT`) |
| `OFFSET...FETCH NEXT` | 10+ ocurrencias en 7 archivos | Prácticamente idéntica en PG 9+ |
| `CONVERT(VARCHAR, fecha, 120)` | 6 ocurrencias en 1 archivo (`firma-gob.routes.ts`) | Mecánica (`TO_CHAR()`) |
| `DATEADD`/`DATEDIFF` | 5 ocurrencias en 4 archivos | Mecánica con reescritura (`+ INTERVAL`, `EXTRACT(EPOCH...)`) |
| `OUTPUT INSERTED.*` | 12+ ocurrencias en 9 archivos | Mecánica (`RETURNING`) |
| `WITH (TABLOCKX\|HOLDLOCK\|UPDLOCK)` | 3 bloques críticos en 2 archivos | **Sin equivalente directo — requiere rediseño** (hallazgo C1) |
| `SCOPE_IDENTITY()` | 1 ocurrencia | Mecánica (`RETURNING id`) |
| `CREATE FULLTEXT INDEX`/`CONTAINS()` | 1 índice + 1 módulo de búsqueda | **Sin equivalente directo — requiere reescritura** (hallazgo A1) |
| `BACKUP DATABASE`/`.bak` | 4 scripts operacionales | **Formato propietario — sin equivalente** (hallazgo A3) |
| `DBCC CHECKIDENT` | 1 ocurrencia | Mecánica (`ALTER SEQUENCE ... RESTART WITH`) |
| Parámetros nombrados `@x` (driver) | 596 usos de `.input()` en 21 archivos | Mecánica pero de alto volumen (`$1,$2,...` posicional en `node-postgres`) |
| Stored procedures legacy | ~118-140, **no invocados** por la app moderna | No aplica a la migración de DOC360 |
| Triggers, vistas | **Ninguno** confirmado activo | No aplica |

---

## 6. Riesgos

### Matriz de riesgos

| Riesgo | Probabilidad | Impacto | Severidad | Mitigación |
|---|---|---|---|---|
| Duplicación de correlativos durante/después de la migración por rediseño incorrecto del locking (C1) | Media | Muy alto (viola regla de negocio no negociable, ver `CLAUDE.md`) | **Crítica** | Diseño explícito + pruebas de concurrencia dedicadas antes de producción (Fase 5/6) |
| Regresión silenciosa por ausencia de pruebas automatizadas (C2) | Alta | Alto | **Crítica** | Construir suite de pruebas de integración antes de tocar código de acceso a datos |
| Pérdida de funcionalidad de búsqueda (A1) durante ventana de transición | Media | Medio (degrada UX, no integridad de datos) | **Alta** | Implementar y validar `tsvector`/GIN en paralelo antes de cortar sobre SQL Server |
| Reescritura mecánica incompleta u omitida en algún archivo (A2) | Media | Medio-alto (bug funcional puntual) | **Alta** | Checklist exhaustivo por archivo (ver Fase 2) + code review dedicado |
| Corrupción de texto en tildes/ñ por collation mal mapeado (M2, M4) | Media | Alto (dato institucional en español) | **Alta** | Validar collation/locale con datos reales antes de migrar (Fase 0/1) |
| Reinterpretación incorrecta de fechas históricas por zona horaria (M3) | Baja-media | Alto (afecta trazabilidad/auditoría) | **Media** | Confirmar TZ del servidor origen antes de decidir estrategia de `TIMESTAMPTZ` |
| Pérdida de capacidad operacional de backup/restore durante la transición (A3) | Media | Alto (sistema institucional crítico) | **Alta** | No cortar producción hasta tener backup/restore de PG probado y documentado (Fase 0/3) |
| Documento/registro huérfano por rollback no transaccional preexistente (M5) | Baja | Medio | **Media** | Corregir independientemente de la migración |

---

## 7. Beneficios esperados de migrar a PostgreSQL

Con evidencia y sin asumir superioridad de rendimiento no probada:

- **Costo de licenciamiento**: SQL Server tiene licenciamiento comercial (aunque en Docker se use la imagen sin costo para desarrollo, producción con SQL Server Standard/Enterprise tiene costo; PostgreSQL es libre de licencia bajo cualquier escala). Relevante si el HUAP no tiene ya licenciamiento SQL Server institucional cubierto — **no confirmado en el repo, verificar con TI/administración antes de ponderar este beneficio**.
- **`JSONB` nativo con indexación**: mejora directa para columnas hoy serializadas como texto en `NVARCHAR` (`alerta_log.destinatarios`, `firma_gob_logs.request_payload/response_payload`) — no bloqueante hoy, pero una mejora real disponible post-migración.
- **Índices únicos parciales** (`CREATE UNIQUE INDEX ... WHERE ...`) ya usados hoy en SQL Server (`12-correlativo-por-servicio.sql`) tienen equivalente directo y igual de expresivo en PG — sin pérdida de capacidad.
- **Simplificación del workaround de cascadas múltiples**: el hallazgo documentado en `14-firma-simple.sql` (líneas 24-29) sobre la limitación de SQL Server de no permitir múltiples `ON DELETE SET NULL` desde la misma tabla no aplica en PostgreSQL — podría simplificarse la lógica manual de desvinculación actualmente hecha a mano en `documento.repository.ts`.
- **Ecosistema de extensiones** (`pg_trgm` para búsqueda difusa, `pgcrypto`, etc.) relevante si el módulo de Búsqueda evoluciona más allá de FTS básico.
- **Sin cambio de dualidad `VARCHAR`/`NVARCHAR`**: PostgreSQL usa UTF-8 uniforme, eliminando la distinción confusa que hoy convive de forma inconsistente en el esquema (hallazgo de la sección 3, collation).

**Beneficio NO confirmado (no asumir):** rendimiento superior. No hay evidencia en este repo de que el cuello de botella actual sea el motor de base de datos — la evidencia apunta a diseño de índices, y sobre todo, al locking pesado deliberado del hallazgo C1, que en PostgreSQL requeriría un rediseño equivalente para no ser igual o peor.

---

## 8. Comparación técnica MySQL/PostgreSQL — ver `MATRIZ_COMPATIBILIDAD_SQLSERVER_POSTGRESQL.md`

*(Nota: se entrega como matriz SQL Server vs. PostgreSQL, motor real del proyecto — ver corrección de premisa al inicio de este documento.)*

Tabla comparativa resumida (detalle completo en el documento dedicado):

| Dimensión | SQL Server 2022 (actual) | PostgreSQL (propuesto) |
|---|---|---|
| Compatibilidad con código actual | Nativa (100%, es el motor actual) | Requiere reescritura de ~596 queries parametrizadas + rediseño de 2 mecanismos críticos (correlativos, FTS) |
| Rendimiento esperado | Conocido en producción actual (no cuantificado en este repo) | Desconocido — requiere benchmark antes de decidir (sección 4) |
| Seguridad | Login de aplicación de bajo privilegio ya implementado (`doc360_app`, no `sa`) según `CLAUDE.md` | Equivalente disponible (roles con privilegios mínimos) |
| Integridad transaccional | ACID completo, locking pesimista explícito usado hoy | ACID completo, MVCC con `SERIALIZABLE`/`SELECT FOR UPDATE` — semántica distinta, requiere rediseño validado |
| Concurrencia | Locking pesimista (hallazgo C1) | MVCC — generalmente mejor concurrencia de lectura, pero el patrón actual de escritura exclusiva debe rediseñarse, no solo traducirse |
| Mantenimiento | Personal/documentación ya orientada a T-SQL (`CLAUDE.md` completo en términos SQL Server) | Requiere curva de aprendizaje del equipo en PL/pgSQL y herramientas PG |
| Backups | `BACKUP DATABASE` nativo, ya operacionalizado (script + Task Scheduler documentado) | Requiere reconstruir con `pg_dump`/`pg_basebackup`, sin trayectoria operacional previa en este proyecto |
| Restauración | Probada (scripts `restore-db.ps1/.sh`, `docker/sqlserver/init.sh`) | Sin trayectoria previa — debe probarse desde cero (Fase 0/7) |
| Monitoreo | No hay evidencia de monitoreo especializado en el repo (ni para SQL Server ni para nada) | Mismo estado — no es un diferencial a favor de ninguno de los dos motores hoy |
| Soporte disponible | Imagen oficial Microsoft, comunidad amplia | Imagen oficial `postgres`, comunidad muy amplia, licencia libre |
| Complejidad de migración | N/A (es el estado actual) | Media-alta, concentrada en 2 mecanismos (correlativos, FTS) + volumen de reescritura mecánica |
| Costos operativos | Licencia SQL Server (si aplica en producción; no confirmado desde el repo) | Sin costo de licencia |
| Portabilidad Docker | Ya implementada y probada (`docker-compose.yml`, `docker-compose.preprod.yml`) | Requiere replicar exactamente el mismo patrón (ver `PLAN_MIGRACION_POSTGRESQL_DOC360.md` Fase 3) — PostgreSQL es igualmente "Docker-nativo" |
| Riesgos para producción | Bajos — sistema estable y en operación | Altos durante la ventana de corte si no se sigue el plan de pruebas/piloto/reversa completo |

---

## 9. Estimación de esfuerzo por fase

Estimaciones en días-persona, asumiendo un equipo de 2 personas (1 backend senior + 1 DBA/backend con experiencia dual SQL Server/PostgreSQL) trabajando en paralelo con el desarrollo normal del producto (no full-time exclusivo). Son estimaciones de planificación, no compromisos — deben refinarse una vez ejecutada la Fase 0.

| Fase | Descripción | Esfuerzo estimado |
|---|---|---|
| 0 | Respaldo, inventario, aprobación, congelamiento | 3-5 días |
| 1 | Diseño de esquema PostgreSQL + estrategia de correlativos | 8-12 días (incluye diseño y prueba del rediseño de C1, el ítem de mayor incertidumbre) |
| 2 | Adaptación de la aplicación (capa de datos, 21 módulos) | 20-30 días (volumen alto, complejidad individual baja salvo C1/A1) |
| 3 | Incorporación de PostgreSQL a Docker Compose (dev/prod/preprod) | 3-5 días |
| 4 | Migración de datos (herramienta + validación) | 5-8 días |
| 5 | Diseño y ejecución de pruebas | 10-15 días (mayor incertidumbre por ausencia total de tests previos — construcción desde cero) |
| 6 | Piloto (clon anonimizado, carga, comparación funcional) | 8-12 días |
| 7 | Puesta en producción | 2-3 días (ventana de corte) + 5 días de monitoreo reforzado post-corte |
| 8 | Plan de reversa (preparación, no ejecución) | 2-3 días (debe estar listo *antes* de la Fase 7, no es esfuerzo adicional secuencial) |
| **Total estimado** | | **~60-90 días-persona** (aprox. 3-4.5 meses calendario con 2 personas a tiempo parcial, o 1.5-2 meses con 2 personas a tiempo completo) |

**Factor de mayor incertidumbre en la estimación:** la Fase 5 (pruebas), porque no existe ninguna suite previa sobre la cual apalancarse — se construye desde cero simultáneamente con la validación de la migración misma.

---

## 10. Perfiles profesionales necesarios

- **Backend Senior TypeScript/Node.js** (perfil ya presente en el proyecto, según convenciones de `CLAUDE.md`) — ejecuta Fase 2.
- **DBA o backend senior con experiencia dual SQL Server + PostgreSQL** — indispensable para Fase 1 (diseño de esquema y, en particular, el rediseño del mecanismo de correlativos) y Fase 4 (migración de datos). Es el perfil de mayor riesgo si no está disponible internamente — considerar apoyo externo puntual si el equipo no tiene experiencia previa en PostgreSQL en producción.
- **QA/Test Engineer** — construcción de la suite de pruebas de integración (Fase 5), idealmente con experiencia en pruebas de concurrencia (para validar C1).
- **DevOps/Infraestructura** — Fase 3 (Docker Compose, backups, healthchecks) y Fase 7 (ventana de corte, monitoreo).
- **Product Owner / referente funcional HUAP** — aprobación de criterios de éxito en Fase 0, validación funcional en Fase 5/6/7 (flujos documentales reales, no solo técnicos).
- **Responsable de seguridad/cumplimiento** — revisión de manejo de PII (hallazgo B4) durante la preparación de cualquier dataset de prueba/piloto anonimizado.

---

## 11. Conclusión de factibilidad

### Factibilidad: **MEDIA**

La migración es técnicamente viable y no encuentra bloqueadores estructurales duros: no hay ORM que migrar, no hay stored procedures activos, no hay inyección SQL, no hay datos binarios en la base, y la mayoría de la sintaxis T-SQL usada tiene traducción mecánica directa a PostgreSQL. Sin embargo, la clasificación no es "alta" porque:

1. El mecanismo de generación de correlativos (hallazgo C1) — el corazón de la integridad de negocio de DOC360 — depende de semántica de locking exclusiva de SQL Server y **requiere rediseño**, no traducción. Un rediseño incorrecto rompe la regla de negocio no negociable de unicidad de correlativos.
2. **No existe ninguna prueba automatizada** sobre la cual validar que el comportamiento se preserva — la suite de pruebas debe construirse desde cero, en paralelo a la migración misma, lo cual añade incertidumbre real a los tiempos y al riesgo.
3. La búsqueda de texto completo (hallazgo A1) requiere reescritura funcional, no solo sintáctica.
4. El volumen de archivos y queries a tocar (21 módulos, 596 parámetros) es considerable para un equipo pequeño sin experiencia previa documentada en PostgreSQL.

Ninguno de estos puntos hace la migración inviable — todos son manejables con el plan de fases propuesto — pero sí exigen "modificaciones relevantes" (la definición exacta de factibilidad media), no un cambio de configuración de bajo riesgo.

---

## 12. Recomendación final

1. **No migrar de inmediato.** Ejecutar primero la Fase 0 completa (incluye la verificación de collation/timezone del servidor vivo, que este repo no puede responder por sí solo) y las pruebas de rendimiento comparativas de la sección 4, **antes** de comprometer una fecha de migración.
2. **Tratar el rediseño del mecanismo de correlativos (C1) como el primer entregable técnico**, de forma aislada y con pruebas de concurrencia dedicadas, antes de tocar el resto del sistema — es el único punto donde un error se traduce directamente en una violación de regla de negocio institucional (numeración duplicada de memorándums/documentos oficiales).
3. **Construir la suite mínima de pruebas de integración (Fase 5) antes de escribir una sola línea de código de migración de la Fase 2** — sin eso, no hay forma responsable de verificar equivalencia de comportamiento en un sistema que gestiona documentos oficiales de un hospital.
4. Evaluar el beneficio real de costo de licenciamiento con el área de TI/administración del HUAP — el repo no puede confirmar si existe ya licenciamiento SQL Server cubierto institucionalmente, dato que cambia materialmente el análisis costo/beneficio.
5. Si se aprueba avanzar, seguir estrictamente las 9 fases de `PLAN_MIGRACION_POSTGRESQL_DOC360.md`, sin saltarse la Fase 6 (piloto) ni comprometer una ventana de corte de producción sin el plan de reversa de `PLAN_REVERSA_POSTGRESQL_DOC360.md` ya ensayado.

## 13. Condiciones necesarias para autorizar la implementación

La migración **no debe autorizarse a producción** hasta que se cumplan todas las siguientes condiciones:

- [ ] Collation y zona horaria del servidor SQL Server de producción confirmados y su equivalente PostgreSQL definido y documentado.
- [ ] Rediseño del mecanismo de correlativos (C1) implementado, revisado por al menos dos personas, y probado bajo concurrencia real (no solo pruebas unitarias) sin producir duplicados en al menos 3 corridas independientes.
- [ ] Suite de pruebas de integración cubriendo como mínimo los flujos listados en `PLAN_PRUEBAS_MIGRACION_POSTGRESQL_DOC360.md`, ejecutada exitosamente contra el ambiente piloto en PostgreSQL.
- [ ] Backup y restore de PostgreSQL probados de extremo a extremo (no solo diseñados) al menos dos veces, incluyendo restauración completa desde cero.
- [ ] Comparación de conteo de registros y validación por hashes/checksums entre la BD SQL Server origen y la BD PostgreSQL destino, con reconciliación de cualquier discrepancia documentada.
- [ ] Plan de reversa (`PLAN_REVERSA_POSTGRESQL_DOC360.md`) ensayado en el ambiente piloto, no solo escrito.
- [ ] Aprobación funcional explícita del referente HUAP sobre los flujos críticos probados en el piloto (registro, derivación, despacho, cierre, memorándum, Firma Simple).
- [ ] Ventana de mantenimiento acordada y comunicada, con criterios objetivos de cancelación definidos por escrito antes de iniciar el corte.

---

**Confirmación:** esta auditoría fue realizada en modo de solo lectura. No se modificó código, no se alteró configuración de Docker/entorno, no se ejecutaron migraciones ni comandos destructivos, y no se alteraron datos productivos ni de desarrollo durante esta revisión.
