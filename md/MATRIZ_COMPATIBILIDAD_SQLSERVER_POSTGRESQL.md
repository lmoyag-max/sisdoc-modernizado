# Matriz de compatibilidad SQL Server ↔ PostgreSQL — DOC360

**Fecha:** 2026-08-12. Ver corrección de premisa (motor real = SQL Server, no MySQL) en `AUDITORIA_FACTIBILIDAD_POSTGRESQL_DOC360.md`.

Cada fila indica: construcción de SQL Server encontrada en el repo, su equivalente en PostgreSQL, y el nivel de esfuerzo de traducción (Mecánico = búsqueda/reemplazo seguro; Semántico = requiere entender el comportamiento y decidir; Rediseño = no hay traducción directa, requiere nueva arquitectura).

## 1. Tipos de datos

| SQL Server | PostgreSQL | Esfuerzo | Nota / evidencia en DOC360 |
|---|---|---|---|
| `INT IDENTITY(1,1)` | `INTEGER GENERATED ALWAYS AS IDENTITY` o `SERIAL` | Mecánico | Usado en casi toda tabla nueva (`04-create-admin-user.sql:14`, etc.) |
| `BIT` | `BOOLEAN` | Mecánico | `activo`, `alerta_config.activo` — mapeo directo 1:1 |
| `CHAR(1)` como pseudo-booleano (`'S'`/`'D'`/`'F'`) | `CHAR(1)` con `CHECK` o `BOOLEAN` si es binario puro | Semántico | `original`, `resuelto`, `medio` (3 estados, no booleano), `vigencia` — decidir caso por caso (ver hallazgo M1 del informe principal) |
| `VARCHAR(n)` | `VARCHAR(n)` | Mecánico | Directo, ambos motores soportan longitud variable con límite |
| `NVARCHAR(n)` / `NVARCHAR(MAX)` | `VARCHAR(n)` / `TEXT` | Mecánico | PG es UTF-8 uniforme, no hay distinción Unicode/no-Unicode — se colapsa a un solo tipo de texto |
| `DATETIME` | `TIMESTAMP` o `TIMESTAMPTZ` | Semántico | Decisión de zona horaria pendiente (ver hallazgo M3) — DOC360 usa `DATETIME` "naive" en todas partes |
| `DATETIME2` | `TIMESTAMP(n)` | Mecánico | Solo `auditoria.timestamp` (`06-auditoria-table.sql:13`) |
| `MONEY` | — | N/A | No usado en DOC360 |
| `TEXT`/`IMAGE` (deprecados en SQL Server) | `TEXT`/`BYTEA` | N/A | No usados en DOC360 |
| `VARBINARY` | `BYTEA` | N/A | No usado — DOC360 no guarda binarios en BD |
| `UNIQUEIDENTIFIER` | `UUID` | N/A | No usado en DOC360 |
| `VARCHAR(MAX)` | `TEXT` | Mecánico | `memo_generado.cuerpo` (`05-memorandum-setup.sql:90`) |

## 2. Autoincrementales e identidad

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `IDENTITY(seed,increment)` | `GENERATED ALWAYS AS IDENTITY (START seed INCREMENT increment)` | Mecánico | — |
| `SCOPE_IDENTITY()` | `RETURNING id` en el mismo `INSERT` | Mecánico | Único uso: `memorandum.routes.ts:885` |
| `OUTPUT INSERTED.*` | `RETURNING *` (o columnas específicas) | Mecánico | 12+ ocurrencias, ver inventario |
| `@@IDENTITY` | (no aplica, no usado) | — | 0 ocurrencias confirmadas |
| `DBCC CHECKIDENT ('tabla', RESEED, n)` | `ALTER SEQUENCE tabla_col_seq RESTART WITH n+1` | Mecánico | `update-dependencias.sql:16` |
| Tabla variable como intermediario de `OUTPUT` (`OUTPUT INSERTED.id INTO @docOut`) | `RETURNING id` directo, sin variable intermedia | Mecánico (simplifica) | `documento.repository.ts:276,290,400` |

## 3. Booleanos

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `TINYINT(1)` (no usado en DOC360, incluido por completitud del checklist original) | `BOOLEAN` / `SMALLINT` | N/A | No aplica — DOC360 no usa `TINYINT`, usa `BIT` |
| `BIT` (0/1) | `BOOLEAN` (true/false) | Mecánico | Driver `mssql` ya mapea `BIT`↔JS `boolean`; `pg` mapea `BOOLEAN`↔JS `boolean` igual — sin cambio de código de aplicación más allá del tipo de columna declarado |

## 4. Fechas y horas

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `GETDATE()` | `NOW()` / `CURRENT_TIMESTAMP` | Mecánico | 40+ ocurrencias, 13 archivos |
| `DATEADD(unit, n, fecha)` | `fecha + INTERVAL 'n unit'` | Mecánico con reescritura | `reportes.routes.ts:88,220`, `firma-gob.routes.ts:812,891` |
| `DATEDIFF(unit, a, b)` | `EXTRACT(EPOCH FROM (b - a))` (segundos) o `AGE(b, a)` | Semántico | `alertas.service.ts:152`, `memorandum.routes.ts:940` — verificar unidades (DÍAS vs MINUTOS) al traducir |
| `CONVERT(VARCHAR, fecha, 120)` | `TO_CHAR(fecha, 'YYYY-MM-DD HH24:MI:SS')` | Mecánico | `firma-gob.routes.ts` (7 ocurrencias) |
| `CONVERT(VARCHAR, fecha, 23)` | `TO_CHAR(fecha, 'YYYY-MM-DD')` | Mecánico | `03_validar_datos_demo_doc360.sql:106` |
| `DATETIMEOFFSET` | `TIMESTAMPTZ` | N/A | No usado en DOC360 (todas las fechas son "naive") |
| `ON UPDATE CURRENT_TIMESTAMP` (MySQL, no aplica a SQL Server) | Trigger `BEFORE UPDATE` en PG | N/A | SQL Server no tiene este mecanismo tampoco — DOC360 actualiza `fecha_update` manualmente desde la app en cada UPDATE, patrón que se preserva igual en PG sin cambios |

## 5. Paginación y límites

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `TOP N` | `LIMIT N` | Mecánico | 10 ocurrencias, 8 archivos |
| `TOP (@param)` | `LIMIT $1` | Mecánico | `reportes.routes.ts:235` |
| `OFFSET @o ROWS FETCH NEXT @n ROWS ONLY` | `LIMIT @n OFFSET @o` (o `OFFSET...FETCH` — PG también soporta esta sintaxis ANSI desde 8.4) | Mecánico | Puede preservarse literal si se prefiere sintaxis ANSI |
| `COUNT(*) OVER()` | `COUNT(*) OVER()` | **Sin cambio** | Idéntico en ambos motores (window function estándar SQL) |

## 6. Funciones de texto y null-handling

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `ISNULL(a, b)` | `COALESCE(a, b)` | Mecánico | 30+ ocurrencias — cuidado: `ISNULL` fuerza el tipo del primer argumento, `COALESCE` usa el tipo de mayor precedencia entre todos los argumentos; validar en columnas con tipos mixtos |
| `+` (concatenación de strings) | `\|\|` | Mecánico | `04-create-admin-user.sql:83` y construcción de correlativos (`memorandum.routes.ts:513`) |
| `LTRIM(RTRIM(x))` | `TRIM(x)` (o mantener `LTRIM`/`RTRIM`, ambos existen en PG) | Mecánico (opcional) | Usado en `alertas.service.ts` para limpiar columnas `CHAR` legacy con padding |
| `LIKE '%x%'` (case-insensitive por collation CI por defecto) | `ILIKE '%x%'` o `LIKE` con columna `citext` | Semántico | Ver hallazgo M4 — riesgo funcional silencioso si no se ajusta |
| `CONCAT()` | `CONCAT()` | **Sin cambio** | Estándar ANSI, usado en `06-jefaturas-firma-gob.sql:106` |

## 7. Locking, transacciones y concurrencia

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `WITH (TABLOCKX, HOLDLOCK)` | `LOCK TABLE tabla IN ACCESS EXCLUSIVE MODE` (dentro de transacción) | **Rediseño** | Hallazgo C1 — funcionalmente similar pero no recomendado como solución final; preferir secuencia por año/dependencia o `SELECT ... FOR UPDATE` sobre fila de contador |
| `WITH (UPDLOCK, HOLDLOCK)` | `SELECT ... FOR UPDATE` sobre la fila/tabla relevante | **Rediseño** | `documento.repository.ts:270` — mismo patrón de riesgo que C1 |
| `WITH (NOLOCK)` | (no usado en DOC360) | N/A | 0 ocurrencias confirmadas |
| `BEGIN TRANSACTION...COMMIT TRANSACTION` (texto plano) | `BEGIN;...COMMIT;` | Mecánico | Sintaxis casi idéntica; recomendable migrar a `sql.Transaction`/`pg` transacción nativa del driver en vez de texto plano, aprovechando la migración para mejorar manejo de errores |
| Ausencia de `TRY/CATCH...ROLLBACK` explícito | Manejo de errores en la capa de aplicación (`try/catch` de Node + rollback automático de PG ante error dentro de una transacción) | Mecánico (mejora natural) | PG aborta automáticamente la transacción completa ante cualquier error dentro de un bloque — comportamiento más seguro por defecto que el T-SQL actual |
| Niveles de aislamiento | Ambos soportan `READ COMMITTED` (default), `REPEATABLE READ`, `SERIALIZABLE` | Semántico | DOC360 no fija explícitamente el nivel de aislamiento en ningún punto — usa el default de cada motor (`READ COMMITTED` en ambos) salvo por los hints de locking explícitos (fila anterior) |

## 8. Búsqueda de texto

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `CREATE FULLTEXT CATALOG` / `CREATE FULLTEXT INDEX ... LANGUAGE 'Spanish'` | `CREATE INDEX ... USING GIN (to_tsvector('spanish', columna))` | **Rediseño** | Hallazgo A1 |
| `CONTAINS(columna, @query)` | `to_tsvector('spanish', columna) @@ to_tsquery('spanish', @query)` o `plainto_tsquery` | **Rediseño** | Sanitización de query de usuario debe reescribirse completa (sintaxis de operadores distinta) |
| `FREETEXT()` (no usado en DOC360) | `plainto_tsquery()` | N/A | No aplica |
| Detección de error FTS por código (7601/7603/7613) | Detección por `SQLSTATE` de PG (códigos de 5 caracteres, distintos) | Semántico | `busqueda.routes.ts:11-15` — reescribir `esFTSError()` completo |

## 9. Índices

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `CREATE INDEX ... INCLUDE (col1, col2)` | `CREATE INDEX ... INCLUDE (col1, col2)` | **Sin cambio** | Soportado en PG 11+, sintaxis casi idéntica (`03-optimize-indexes.sql:11`) |
| `CREATE UNIQUE INDEX ... WHERE condicion` (filtered index) | `CREATE UNIQUE INDEX ... WHERE condicion` (partial index) | **Sin cambio** | `12-correlativo-por-servicio.sql:53-56` — sintaxis prácticamente idéntica |
| `CLUSTERED` / `NONCLUSTERED` | (no hay equivalente continuo; usar `CLUSTER` puntualmente si se necesita, no recomendado como estrategia continua) | Semántico | Diferencia arquitectónica, no solo sintáctica — revisar diseño de PK/índices primarios |
| Consultas a `sys.indexes`, `sys.key_constraints`, `OBJECT_ID()` | `pg_indexes`, `pg_constraint`, `to_regclass()` | Mecánico con reescritura | `database.ts:49-76` (`ensureIndexes()`), varios scripts con `IF NOT EXISTS (SELECT...)` |

## 10. DDL condicional y scripting

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `IF OBJECT_ID('x','U') IS NOT NULL` | `IF EXISTS (SELECT FROM information_schema.tables WHERE table_name='x')` o `CREATE TABLE IF NOT EXISTS` | Mecánico | Patrón repetido en casi todos los scripts de `database/scripts/` |
| `GO` (separador de lote de `sqlcmd`) | No existe — cada sentencia termina en `;`, no requiere separador de lote | Mecánico (eliminar) | — |
| `DECLARE @x TYPE` + `SET`/`SELECT @x = ...` | Variables dentro de `DO $$ DECLARE x TYPE; BEGIN ... END $$;` o funciones PL/pgSQL | Semántico | Reescritura de estructura, no solo sintaxis |
| `PRINT 'mensaje'` | `RAISE NOTICE 'mensaje'` | Mecánico | `06-jefaturas-firma-gob.sql:106` |
| `RAISERROR('msg', 16, 1)` + `SET NOEXEC ON` | `RAISE EXCEPTION 'msg'` dentro de bloque `DO`/función | Semántico | Patrón de scripts de demo (`01_limpiar_datos_demo_doc360.sql`) |
| `SET XACT_ABORT ON` | Comportamiento por defecto en PG (siempre abortará la transacción ante error) | Mecánico (eliminar, ya es el default) | — |
| `SELECT * INTO nueva_tabla FROM origen` | `CREATE TABLE nueva_tabla AS SELECT * FROM origen` | Mecánico | Backups ad-hoc, 5 scripts |
| `DELETE alias FROM tabla alias JOIN ...` | `DELETE FROM tabla alias USING otra_tabla b WHERE ...` | Semántico | `clean-documentos-tramites.sql:35-37` |
| `@@ROWCOUNT` | `GET DIAGNOSTICS var = ROW_COUNT` (en PL/pgSQL) o revisar `rowCount` desde el driver `pg` en Node | Mecánico | `clean-documentos-completo.sql:27` |
| Cursores T-SQL (`DECLARE cursor CURSOR ... FETCH NEXT`) | Cursores PL/pgSQL (`FOR row IN SELECT ... LOOP`) o resolver con SQL de conjuntos (preferible) | Semántico | `00_respaldo_pre_demo.sql:80-106` — recomendable eliminar el cursor y usar SQL basado en conjuntos al portar |

## 11. Backups y restauración

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `BACKUP DATABASE ... TO DISK` → `.bak` (binario propietario) | `pg_dump -Fc` (formato custom) o `pg_basebackup` (físico) | **Rediseño operacional** | Sin conversión directa entre formatos — se reconstruye el mecanismo, no se traduce el archivo |
| `RESTORE DATABASE ... WITH MOVE ... REPLACE` | `pg_restore` (lógico) o restauración de `pg_basebackup` + WAL (físico) | **Rediseño operacional** | — |
| `sqlcmd -Q "..."` (cliente CLI) | `psql -c "..."` | Mecánico | Scripts de backup/restore (`.ps1`/`.sh`) |

## 12. Multi-schema

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| Schema `sisdoc.tabla` (no `dbo`) | Schema `sisdoc.tabla` | **Sin cambio** | PG soporta schemas de forma directa y equivalente; `memorandum_firma_simple`, `firma_gob_logs` migran sin fricción conceptual |

## 13. Collation y codificación

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| Collation de servidor (ej. `SQL_Latin1_General_CP1_CI_AS`, no confirmado en el repo — ver acción pendiente) | Locale `es_CL.UTF-8` o `ICU es-CL` a nivel de base/columna | Semántico | Debe verificarse contra el servidor vivo antes de migrar (acción de Fase 0) |
| `VARCHAR` (dependiente de code page) vs `NVARCHAR` (UTF-16) | `VARCHAR`/`TEXT` (siempre UTF-8) | Semántico | Colapsa la dualidad, pero requiere verificar que no haya datos legacy con caracteres fuera del code page de origen (riesgo de mojibake) |
| Comparaciones case-insensitive por default (collation CI) | Case-sensitive por default | Semántico | Ver fila `LIKE`/`ILIKE` arriba |

## 14. Comillas invertidas / delimitadores de identificador

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `` `identificador` `` (backticks, sintaxis MySQL — no aplica a SQL Server) | `"identificador"` | N/A | No aplica — SQL Server usa `[corchetes]`, no backticks; y DOC360 no usa ninguno de los dos en su código (identificadores sin delimitar) |
| `[identificador]` (SQL Server) | `"identificador"` | N/A | 0 ocurrencias en el backend — no requiere reescritura |

## 15. ENUM

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| `ENUM` (no existe nativo en SQL Server; DOC360 usa `VARCHAR` + valores controlados en código, ej. roles, estados) | `VARCHAR` + `CHECK`, o tipo `ENUM` nativo de PG | Semántico (mejora opcional) | DOC360 ya modela "enums" como catálogos en tabla (`rol`, `estado_documento`, etc.) más que como tipo enumerado — patrón que se preserva igual en PG sin necesidad de usar `CREATE TYPE ... AS ENUM` |

## 16. Nombres de tabla/columna — sensibilidad a mayúsculas

| SQL Server | PostgreSQL | Esfuerzo | Nota |
|---|---|---|---|
| Nombres no delimitados son case-insensitive en la práctica (collation del servidor) | Nombres no delimitados se pliegan a minúsculas automáticamente; nombres entre comillas dobles son case-sensitive | Bajo riesgo | Todo el esquema de DOC360 usa `snake_case` en minúsculas de forma consistente (`id_documento`, `fecha_sistema`, etc.) — sin conflicto esperado, pero validar en Fase 1 que ningún nombre tenga mayúsculas mixtas que dependan de folding case-insensitive de SQL Server |

---

## Resumen de esfuerzo por categoría

| Nivel de esfuerzo | Categorías | Cantidad de construcciones distintas |
|---|---|---|
| **Sin cambio** | Window functions, índices `INCLUDE`/parciales, `CONCAT()`, multi-schema | 4 |
| **Mecánico** (buscar/reemplazar con validación puntual) | Tipos de datos base, `IDENTITY`→`SERIAL`, `RETURNING`, paginación, `GETDATE`/`CONVERT`/`ISNULL`, DDL condicional | ~20 |
| **Semántico** (requiere entender comportamiento, no solo sintaxis) | `DATEDIFF`, collation/`LIKE`, niveles de aislamiento, cursores, multi-DELETE, tipos `CHAR(1)` legacy | ~10 |
| **Rediseño** (sin traducción directa, nueva arquitectura) | Locking pesimista de correlativos (C1), Full-Text Search (A1), backup/restore nativo (A3) | 3 |
