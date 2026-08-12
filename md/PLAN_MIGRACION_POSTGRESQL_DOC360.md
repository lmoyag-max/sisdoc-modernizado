# Plan de migración condicionado — DOC360 SQL Server → PostgreSQL

**Fecha:** 2026-08-12
**Condición de activación:** este plan solo debe ejecutarse tras la aprobación explícita descrita en la sección 13 de `AUDITORIA_FACTIBILIDAD_POSTGRESQL_DOC360.md`. Ningún paso de este documento debe iniciarse sin esa autorización.

---

## Fase 0 — Respaldo y preparación

**Objetivo:** asegurar que no se pueda perder trabajo ni datos, y que exista una línea base verificable antes de tocar nada.

1. **Inventario completo:** confirmar el estado exacto de todos los objetos de BD contra el servidor SQL Server vivo (no solo el repo, que según `INVENTARIO_DEPENDENCIAS_SQLSERVER_DOC360.md` sección 3 es insuficiente por sí solo): `INFORMATION_SCHEMA.TABLES`, `sys.columns`, `sys.foreign_keys`, `sys.indexes`, collation actual (`SELECT DATABASEPROPERTYEX('SISDOC','Collation')`), zona horaria del contenedor.
2. **Respaldo verificable de SQL Server:** ejecutar `scripts/backup-db.ps1`/`.sh` (ya existente y operacional según README) generando un `.bak` fresco, con `CHECKSUM`.
3. **Prueba de restauración:** restaurar ese `.bak` en una instancia SQL Server separada (no la de desarrollo activo) usando `scripts/restore-db.ps1`/`.sh`, y validar conteo de filas de las tablas críticas (`documento`, `tramite`, `memo_generado`, `usuario`, `funcionario`) contra el origen.
4. **Congelamiento controlado de cambios de esquema:** desde el inicio de la Fase 0 hasta el cierre de la Fase 7, ningún cambio de esquema SQL Server debe aplicarse fuera de este proceso de migración (evitar drift entre el esquema auditado y el esquema real).
5. **Definición de responsables:** Product Owner/referente HUAP (aprobación funcional), DBA/backend dual (diseño técnico), QA (pruebas), DevOps (infraestructura) — ver sección 10 del informe principal.
6. **Criterios de aprobación de la Fase 0:** backup restaurado exitosamente, collation/timezone documentados, responsables confirmados por escrito, sin cambios de esquema pendientes sin mergear.
7. **Plan de reversa:** confirmar que `PLAN_REVERSA_POSTGRESQL_DOC360.md` está leído y aceptado por todos los responsables antes de continuar a la Fase 1.

**Entregable de la Fase 0:** documento de línea base (collation, timezone, conteo de filas por tabla, tamaño de BD real) — insumo obligatorio para la Fase 1 y para la validación de la Fase 4.

---

## Fase 1 — Diseño PostgreSQL

**Objetivo:** producir el DDL completo de PostgreSQL y la estrategia de correlativos, sin escribir código de aplicación todavía.

1. **Conversión del esquema:** traducir tabla por tabla usando `MATRIZ_COMPATIBILIDAD_SQLSERVER_POSTGRESQL.md` como referencia. Como el repo no tiene un DDL consolidado (`INVENTARIO_DEPENDENCIAS_SQLSERVER_DOC360.md` sección 3), generar primero un script de extracción desde `INFORMATION_SCHEMA` del SQL Server vivo (Fase 0, paso 1) y usarlo como fuente de verdad.
2. **Mapeo de tipos de datos:** aplicar la tabla de la sección 1 de la matriz de compatibilidad. Decisión explícita requerida para: `CHAR(1)` legacy (`original`, `resuelto`, `medio`, `vigencia` — normalizar a `BOOLEAN` solo donde es binario puro; mantener `CHAR(1)`/`VARCHAR` + `CHECK` donde hay 3+ estados como `medio`), `DATETIME` → `TIMESTAMP` vs `TIMESTAMPTZ` (recomendado `TIMESTAMPTZ` con conversión explícita usando la zona horaria confirmada en Fase 0).
3. **Secuencias e identificadores:** todo `IDENTITY(1,1)` → `GENERATED ALWAYS AS IDENTITY`. Documentar explícitamente el valor de arranque de cada secuencia igual al `MAX(id)+1` actual de cada tabla (extraído en Fase 4, validado antes del corte).
4. **Claves primarias y foráneas:** preservar 1:1 la topología de FK actual, incluyendo el workaround documentado de `14-firma-simple.sql` (3 FK con `ON DELETE NO ACTION` por la limitación de SQL Server de no permitir múltiples `SET NULL` desde la misma tabla) — **evaluar si simplificar a `ON DELETE SET NULL` real en PG** (que sí lo permite) o preservar el comportamiento manual actual del backend por consistencia. Decisión debe registrarse explícitamente, no asumirse.
5. **Restricciones:** todos los `CHECK` (ej. `ck_jefatura_usuarios_distintos`, `14-firma-simple.sql:81-85`) son portables sin cambios.
6. **Índices:** traducir `INCLUDE` e índices únicos parciales (soporte directo en PG). Revisar la estrategia de índice primario/clustered — en PG no existe el concepto continuo de `CLUSTERED`, decidir explícitamente el orden físico deseado o aceptar heap tables con índices B-tree estándar (comportamiento por defecto de PG).
7. **Vistas, funciones, triggers:** ninguno activo que migrar (confirmado en el inventario) — omitir este paso salvo que una revisión adicional contra la BD viva (fuera del alcance de este repo) encuentre alguno no documentado.
8. **Full-Text Search:** diseñar `tsvector`/`tsquery` + índice GIN para `documento.materia` y `funcionario.nombres/apellidos`, en español (`to_tsvector('spanish', ...)`), replicando el índice de `05-full-text-index.sql`.
9. **Auditoría:** `auditoria.timestamp DATETIME2 DEFAULT GETDATE()` → `TIMESTAMPTZ DEFAULT NOW()`, sin pérdida de granularidad.
10. **Collation y codificación UTF-8:** definir el locale de la base PG (`es_CL.UTF-8` o `ICU es-CL`, según lo confirmado en Fase 0) y aplicarlo consistentemente a nivel de base de datos (no por columna, salvo necesidad específica).
11. **Estrategia de correlativos sin duplicación (el diseño de mayor riesgo, hallazgo C1):**
    - Evaluar 2 alternativas con pruebas de concurrencia dedicadas antes de decidir:
      - **(a) Tabla de contadores dedicada** (`contador_correlativo(anio, id_dependencia, ultimo_numero)`) actualizada con `SELECT ... FOR UPDATE` + `UPDATE` en la misma transacción — preserva exactamente la semántica actual de "reutilización automática al borrar" descrita en `CLAUDE.md`, porque el borrado de un documento puede recalcular `MAX()` sobre `memo_generado` igual que hoy (la tabla de contadores solo acelera el camino feliz; se puede recalcular `MAX()+1` con `SELECT FOR UPDATE` sobre la fila del contador en vez de la tabla completa).
      - **(b) `SELECT MAX(numero)+1 ... FOR UPDATE` directamente sobre `memo_generado`/`documento`**, replicando el patrón actual pero con `LOCK TABLE ... IN SHARE ROW EXCLUSIVE MODE` o `SELECT ... FOR UPDATE` sobre las filas del año/dependencia relevante — más simple de portar, pero requiere confirmar que el patrón de lock granular por año+dependencia (no toda la tabla) sea suficiente para evitar colisiones sin volver a serializar innecesariamente operaciones de dependencias distintas.
    - **Recomendación de diseño:** opción (a), porque desacopla el locking de la tabla transaccional principal y es el patrón idiomático recomendado en PostgreSQL para contadores concurrentes seguros, además de facilitar las pruebas de concurrencia de la Fase 5 al aislar el mecanismo en una sola tabla pequeña.
    - Documentar explícitamente cómo se preserva la reutilización de números libres al hacer `DELETE`/`softDelete()` (regla de negocio no negociable) bajo el diseño elegido.

**Entregable de la Fase 1:** script DDL completo de PostgreSQL + documento de decisiones de diseño (uno por cada punto de esta fase) + diseño validado del mecanismo de correlativos, revisado por al menos 2 personas antes de pasar a la Fase 2.

---

## Fase 2 — Adaptación de la aplicación

**Objetivo:** portar la capa de acceso a datos del backend sin cambiar comportamiento observable.

1. **Nuevo proveedor de conexión:** reemplazar `mssql` por `pg` (`node-postgres`) en `backend/src/config/database.ts`. Mantener la misma interfaz exportada (`getPool()`, `closePool()`) para minimizar el churn en los 21 módulos que la consumen.
2. **Pool de conexiones:** trasladar `max:20 min:2 idleTimeoutMillis:30000` a la configuración equivalente de `pg.Pool` (`max`, `idleTimeoutMillis`, `connectionTimeoutMillis`).
3. **Eliminación de SQL exclusivo de SQL Server:** aplicar `MATRIZ_COMPATIBILIDAD_SQLSERVER_POSTGRESQL.md` archivo por archivo (los 21 módulos listados en el inventario). Recomendado: checklist por archivo con firma de quien lo revisó, no solo búsqueda/reemplazo automatizada, dado el volumen (596 parámetros, ~150+ construcciones T-SQL individuales).
4. **Parámetros nombrados → posicionales:** convertir cada `.input('nombre', sql.Tipo, valor)` + `@nombre` en el texto SQL a `$1, $2, ...` posicional de `pg`. Alto volumen, bajo riesgo individual — recomendable escribir un script de asistencia (no reescribir 100% a mano) que al menos detecte los patrones y liste los archivos pendientes, aunque la reescritura final debe revisarse manualmente.
5. **Transacciones:** aprovechar la migración para reemplazar el patrón actual de texto plano `BEGIN TRANSACTION...COMMIT` por transacciones nativas del driver (`client.query('BEGIN')`/`COMMIT`/`ROLLBACK` con manejo explícito en `try/catch/finally`, liberando el cliente del pool en el `finally`) — mejora la garantía de rollback ante error de aplicación, no solo de BD, corrigiendo de paso el hallazgo M5 (`revertirDocumentoSinFirmar()` no transaccional).
6. **Implementación del mecanismo de correlativos rediseñado** (Fase 1, punto 11) — este es el cambio de mayor riesgo, debe implementarse y probarse de forma aislada, con su propio ciclo de revisión, antes de integrarse al resto de la Fase 2.
7. **Variables de entorno:** agregar `DB_HOST`/`DB_PORT`(5432)/`DB_USER`/`DB_PASSWORD`/`DB_DATABASE` para PG (reutilizar el campo `DATABASE_URL` ya declarado mas no usado en `env.ts:16` como la forma preferida de configurar `pg.Pool`, formato `postgres://user:pass@host:port/db`), sin romper la validación Zod existente durante el período de coexistencia (Fase 6).
8. **Manejo de errores:** reemplazar la detección de errores de Full-Text Search por mensaje de texto (`esFTSError()`, códigos SQL Server 7601/7603/7613) por detección de `SQLSTATE` de PG. Revisar `error.middleware.ts` para que siga distinguiendo correctamente errores de aplicación vs. errores de driver tras el cambio.
9. **Health checks:** actualizar el query de verificación de salud (`SELECT 1`, patrón `TOP 1 1 AS ok`) usado en `archivos.routes.ts:113,248`, `documento.service.ts:36` → `SELECT 1` simple, sin `TOP`.
10. **Compatibilidad con pruebas existentes:** no aplica directamente (no existen pruebas previas — ver Fase 5), pero cualquier prueba nueva escrita durante la Fase 2 para validar el propio cambio debe quedar como parte de la suite permanente de la Fase 5, no descartarse.

**Entregable de la Fase 2:** backend funcional contra PostgreSQL en ambiente de desarrollo, con checklist de los 21 módulos firmado, ejecutándose en paralelo (no reemplazando aún) al backend contra SQL Server.

---

## Fase 3 — Docker

**Objetivo:** incorporar PostgreSQL al mismo Docker Compose del proyecto, sin exponerlo a Internet y sin romper los perfiles `dev`/`prod`/`preprod` existentes.

1. **Imagen oficial con versión fija:** `postgres:16-alpine` (o la LTS vigente al momento de ejecutar el plan — fijar versión exacta, nunca `latest`, siguiendo el mismo criterio ya aplicado a `mcr.microsoft.com/mssql/server:2022-latest`... **nota:** el proyecto actual sí usa `-latest` para SQL Server, lo cual es una práctica a corregir en la migración, no a replicar).
2. **Servicio `postgres`** en `docker-compose.yml`, agregado junto a (no reemplazando aún) el servicio `sqlserver`, para permitir el período de coexistencia de la Fase 6.
3. **Volumen persistente:** nuevo volumen nombrado (ej. `sisdoc_postgres_data:/var/lib/postgresql/data`), independiente del volumen `sisdoc_sqlserver_data` existente.
4. **Red interna:** reutilizar la red `sisdoc_network` ya existente — sin necesidad de una red nueva.
5. **Variables de entorno:** `POSTGRES_USER`, `POSTGRES_PASSWORD`, `POSTGRES_DB=SISDOC`, siguiendo el mismo patrón ya usado para `MSSQL_SA_PASSWORD` (variable de entorno del host, nunca hardcodeada en `docker-compose.yml`).
6. **Secretos fuera del repositorio:** confirmar que `.env` (ya excluido en `.gitignore`) sea la única fuente de las credenciales PG, igual que hoy con SQL Server.
7. **`.env.example` actualizado** (raíz y `backend/`) con placeholders de PG, sin credenciales reales — extendiendo, no reemplazando, el `.env.example` actual hasta que se complete el corte (Fase 7).
8. **Healthcheck:** `pg_isready -U ${POSTGRES_USER} -d SISDOC`, siguiendo el mismo patrón de intervalos ya usado para SQL Server (`interval 15s timeout 10s retries 10 start_period 30s`).
9. **Política de reinicio:** `restart: unless-stopped`, igual que el servicio `sqlserver` actual.
10. **Dependencias saludables:** durante el período de coexistencia (Fase 6), `backend` no debe depender de `postgres` todavía (sigue dependiendo de `sqlserver`); solo tras el corte de la Fase 7 se cambia `depends_on` de `backend` de `sqlserver` a `postgres condition: service_healthy`.
11. **Inicialización controlada del esquema:** **no** usar `docker-entrypoint-initdb.d` para cargar el DDL completo de producción (riesgo de reejecución accidental) — usar un proceso de migración explícito y versionado, ejecutado manualmente o vía un job dedicado, separado del arranque normal del contenedor (igual criterio que ya aplica hoy: el repo confirma que la inicialización de SQL Server tampoco es automática, es manual y documentada en `README_PREPROD.md`).
12. **Migraciones independientes del inicio normal:** cualquier script de migración de esquema PG debe vivir en `database/scripts-postgresql/` (paralelo a `database/scripts/` existente, no reemplazándolo hasta el corte), ejecutado explícitamente, nunca como parte de `docker compose up`.
13. **Backups fuera del volumen principal:** `pg_dump` programado hacia un bind mount separado (ej. `./database/postgres-backups`), replicando el patrón ya usado para `.bak` de SQL Server (`./database:/var/opt/mssql/backup`).
14. **Restauración verificable:** documentar y probar `pg_restore` contra un volumen limpio, igual que se exige para SQL Server en la Fase 0.
15. **Límites y reservas de recursos:** agregar `deploy.resources.limits/reservations` tanto para `postgres` como (retroactivamente) para `sqlserver` — actualmente ninguno de los servicios del `docker-compose.yml` los define; no bloqueante pero se recomienda no perpetuar la ausencia de límites al agregar el nuevo servicio.
16. **No exponer el puerto de PostgreSQL a Internet:** replicar exactamente el patrón ya usado para SQL Server (`127.0.0.1:puerto:5432`, nunca `0.0.0.0` ni sin especificar interfaz) — y de paso, corregir la discrepancia de puertos ya detectada entre `docker-compose.yml` (`15433`) y la documentación (`11433`) para no repetir la confusión con el puerto de PG.
17. **Configuración diferenciada dev/test/prod:** replicar la estructura ya existente de `docker-compose.yml` (perfil `prod`) + `docker-compose.preprod.yml` (paquete autocontenido) — agregar el servicio `postgres` a ambos archivos de forma consistente.

**Entregable de la Fase 3:** `docker-compose.yml` y `docker-compose.preprod.yml` actualizados con el servicio `postgres` funcionando en paralelo a `sqlserver`, healthcheck verde, backup/restore de PG probado al menos una vez.

---

## Fase 4 — Migración de datos

**Objetivo:** mover los datos reales preservando identificadores, relaciones, fechas, estados, auditoría y trazabilidad.

**Herramienta seleccionada:** dado que DOC360 no usa ORM y el esquema es relativamente compacto (74 tablas según `PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md`), se recomienda **`pgloader`** para la migración inicial masiva de datos (soporta SQL Server como origen vía FDW/ODBC), complementado con **scripts de validación a medida en TypeScript/Node** (reutilizando el mismo driver `mssql` para leer origen y `pg` para escribir destino) para los casos que requieren transformación de tipos no trivial (la conversión de `CHAR(1)` legacy, y el recálculo del punto de arranque de cada secuencia/contador).

1. **Migración inicial:** `pgloader` con mapeo de tipos explícito (sección 1 de la matriz de compatibilidad) tabla por tabla, en orden que respete las FK (padres antes que hijos: `dependencia`/`rol`/`tipo_documento` → `funcionario`/`usuario` → `documento` → `tramite`/`archivo_digital`/`memo_generado` → tablas de auditoría/trazabilidad).
2. **Transformación de tipos:** validar explícitamente cada columna `CHAR(1)` legacy contra el mapeo decidido en Fase 1 (no dejar que `pgloader` infiera automáticamente).
3. **Preservación de identificadores:** todos los `id_*` deben migrar con el mismo valor numérico (no regenerarse) — crítico porque son referenciados desde `archivo_digital.ruta` (filesystem) y desde trazabilidad histórica.
4. **Preservación de relaciones:** validar que el 100% de las FK migradas apunten a un `id` existente en la tabla padre migrada (0 huérfanos) — script de validación dedicado, no solo confiar en `pgloader`.
5. **Preservación de fechas:** validar una muestra representativa de timestamps históricos comparando origen/destino tras la decisión de zona horaria de la Fase 1 (si se migra a `TIMESTAMPTZ`, confirmar que la conversión no desplaza las fechas un offset incorrecto).
6. **Preservación de estados:** validar contra el catálogo de `estado_documento`/`estado_compromiso` que ningún valor quedó fuera de rango tras la migración.
7. **Preservación de auditoría y trazabilidad:** la tabla `auditoria` y los eventos de `tramite` (que registran cada paso del flujo documental) deben migrar sin pérdida de orden cronológico — validar `ORDER BY fecha_sistema` produce la misma secuencia en ambos motores para una muestra de documentos.
8. **Validación de archivos/referencias documentales:** para cada fila migrada de `archivo_digital`, confirmar que el archivo físico referenciado (`ruta`) sigue existiendo en el filesystem `uploads/` (que no se toca durante esta fase — solo se migra su metadata).
9. **Reinicio correcto de secuencias:** tras la carga masiva, ejecutar `SELECT setval('tabla_id_seq', (SELECT MAX(id) FROM tabla))` para cada tabla con `IDENTITY`, y para el mecanismo de contadores de correlativos (Fase 1, punto 11), inicializar la tabla de contadores con el `MAX(numero)` real por año+dependencia extraído de `memo_generado`.
10. **Comparación de cantidad de registros:** `SELECT COUNT(*)` por tabla en origen vs. destino — debe ser exactamente igual, sin excepciones no documentadas.
11. **Validación por hashes:** generar un hash (ej. `MD5`/`SHA256` sobre una concatenación determinística de columnas clave, ordenada por PK) por tabla en origen y destino, comparar — detecta discrepancias de contenido que un `COUNT(*)` no vería.
12. **Reconciliación de datos:** cualquier discrepancia encontrada en los pasos 10/11 debe documentarse fila por fila (no "aproximarse") antes de continuar a la Fase 5, dado que se trata de documentos oficiales de una institución de salud.
13. **Registro de errores:** toda la migración de datos debe correr con logging exhaustivo (qué tabla, qué fila, qué error) para auditoría posterior — no silenciar excepciones de `pgloader` sin revisar el log completo.

**Entregable de la Fase 4:** base de datos PostgreSQL poblada con los datos migrados desde el `.bak` restaurado en la Fase 0, reporte de reconciliación firmado sin discrepancias abiertas.

---

## Fase 5 — Pruebas

Ver detalle exhaustivo en `PLAN_PRUEBAS_MIGRACION_POSTGRESQL_DOC360.md`. Resumen de alcance: pruebas funcionales de los 15 módulos, pruebas específicas de concurrencia sobre el mecanismo de correlativos rediseñado, pruebas de regresión comparando comportamiento SQL Server vs. PostgreSQL sobre el mismo dataset migrado, pruebas de seguridad (privilegios del rol `doc360_app` en PG), pruebas de rendimiento (sección 4 del informe principal) y pruebas de recuperación (backup/restore/reinicio de contenedor/persistencia de volumen).

**Entregable de la Fase 5:** suite de pruebas ejecutable (idealmente automatizada donde sea posible dado que hoy no existe ninguna), con reporte de resultados sin fallos abiertos antes de avanzar a la Fase 6.

---

## Fase 6 — Migración piloto

**Objetivo:** validar el sistema completo en un ambiente aislado antes de tocar producción.

1. **Clon anonimizado de los datos:** generar una copia de la BD de producción con PII anonimizada (especial atención a `database/scripts/09-ruts-firmantes.sql` — RUTs reales — y a cualquier dato personal de `funcionario`/`usuario`), siguiendo el mismo criterio de cuidado ya aplicado en `PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md`/`README_PREPROD.md` para el paquete de demo.
2. **Ambiente de pruebas aislado:** usar `docker-compose.preprod.yml` extendido con el servicio `postgres` (Fase 3), completamente separado de desarrollo y producción (red y volúmenes propios, ya es el patrón de este archivo: `sisdoc_preprod_*`).
3. **Comparación funcional SQL Server/PostgreSQL:** ejecutar la suite de pruebas de la Fase 5 contra ambos motores con el mismo dataset anonimizado, comparando resultados campo por campo en los endpoints críticos (documentos, correlativos, búsqueda, reportes).
4. **Pruebas de carga:** ejecutar los escenarios de la sección 4 del informe principal (latencia, throughput de creación concurrente, concurrencia de correlativos) contra el piloto PostgreSQL, comparando contra una medición equivalente en SQL Server tomada en el mismo periodo (para controlar por variabilidad de infraestructura).
5. **Corrección de incompatibilidades:** cualquier discrepancia encontrada en el paso 3 o 4 debe corregirse y volver a probar antes de continuar — no acumular deuda para la Fase 7.
6. **Aprobación técnica y funcional:** firma explícita de DBA/backend (aspecto técnico) y del referente HUAP (aspecto funcional, sobre los flujos reales: registro, recepción, derivación, despacho, cierre, memorándum, Firma Simple) antes de programar la ventana de corte.

**Entregable de la Fase 6:** informe de piloto con resultados de comparación funcional y de carga, aprobación firmada de ambos responsables.

---

## Fase 7 — Puesta en producción

**Objetivo:** cortar sobre PostgreSQL con el menor riesgo posible y la capacidad de detectar problemas de inmediato.

1. **Ventana de mantenimiento:** acordada con HUAP con anticipación suficiente, comunicada a usuarios del sistema (horario de bajo uso documental, ej. fuera de horario hábil).
2. **Respaldo final:** `BACKUP DATABASE` de SQL Server inmediatamente antes del corte (además de los respaldos ya tomados en fases previas) — última línea de defensa antes de tocar producción.
3. **Detención controlada de escrituras:** poner el backend en modo mantenimiento (rechazar nuevas escrituras, permitir solo lectura o página de mantenimiento) antes de iniciar la migración final de datos, para evitar una ventana de escritura perdida entre el corte de datos y el cambio de conexión.
4. **Migración incremental o definitiva:** dado que DOC360 es un sistema de volumen moderado (no de altísimo tráfico continuo, según lo observado en el repo), se recomienda **migración definitiva** (no incremental/dual-write) dentro de la ventana de mantenimiento, replicando exactamente el proceso ya validado en la Fase 4 y Fase 6, pero contra los datos de producción reales al momento del corte.
5. **Validación de integridad:** repetir los pasos 10-12 de la Fase 4 (conteo, hashes, reconciliación) contra los datos de producción recién migrados, antes de reabrir el sistema.
6. **Cambio de conexión:** actualizar `docker-compose.yml` (`depends_on` de `backend` apuntando a `postgres`, variables `DB_*`) y desplegar — este es el punto de no retorno operacional dentro de la ventana (aunque el plan de reversa sigue disponible después, ver `PLAN_REVERSA_POSTGRESQL_DOC360.md`).
7. **Pruebas de humo:** ejecutar un subconjunto crítico de la suite de la Fase 5 directamente contra producción recién migrada (login, crear documento, generar memorándum, búsqueda, dashboard) antes de reabrir a usuarios.
8. **Aprobación funcional:** confirmación explícita del referente HUAP de que las pruebas de humo son satisfactorias, antes de reabrir escrituras a usuarios finales.
9. **Monitoreo reforzado:** periodo de observación intensiva (mínimo 5 días hábiles) con revisión diaria de logs de errores de BD, latencia de endpoints críticos, y validación manual de que los correlativos generados durante ese periodo no presentan duplicados ni saltos inesperados.
10. **Criterios de cancelación:** definidos y acordados *antes* de iniciar la ventana (no improvisados durante el corte) — ver `PLAN_REVERSA_POSTGRESQL_DOC360.md` para el detalle completo de qué condiciones disparan una reversa y en qué plazo debe decidirse.

**Entregable de la Fase 7:** sistema en producción sobre PostgreSQL, reporte de pruebas de humo, bitácora de monitoreo reforzado de los primeros 5 días hábiles.

---

## Fase 8 — Reversa

Ver detalle completo en `PLAN_REVERSA_POSTGRESQL_DOC360.md`. Este plan debe estar **escrito, revisado y ensayado en el piloto (Fase 6)** antes de que la Fase 7 pueda autorizarse — no es un documento a redactar después de un incidente.

---

## Resumen visual de fases y dependencias

```
Fase 0 (Respaldo/Prep) ──► Fase 1 (Diseño PG) ──► Fase 2 (App) ──┐
                                                                    ├──► Fase 5 (Pruebas) ──► Fase 6 (Piloto) ──► Fase 7 (Producción)
                          Fase 3 (Docker) ─────────────────────────┘                                                    │
                          Fase 4 (Datos, ejecutada 2 veces: piloto y corte final) ─────────────────────────────────────┘
                                                                                                          Fase 8 (Reversa) — preparada en paralelo desde Fase 0, ensayada en Fase 6
```
