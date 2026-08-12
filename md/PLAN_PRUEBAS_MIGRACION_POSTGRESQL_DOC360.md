# Plan de pruebas — Migración DOC360 SQL Server → PostgreSQL

**Fecha:** 2026-08-12
**Precondición:** este plan asume que se ejecuta la Fase 5 de `PLAN_MIGRACION_POSTGRESQL_DOC360.md`. Como DOC360 **no tiene pruebas automatizadas previas** (confirmado en `AUDITORIA_FACTIBILIDAD_POSTGRESQL_DOC360.md`, hallazgo C2), esta suite se construye desde cero y debe quedar como activo permanente del proyecto, no descartarse tras la migración.

**Herramientas recomendadas** (consistentes con el stack ya declarado en `CLAUDE.md` como pendiente): Vitest + Supertest para pruebas de integración de API contra ambos motores; scripts Node dedicados para pruebas de concurrencia.

---

## 1. Pruebas unitarias

Alcance limitado dado que la lógica de negocio vive mayormente en SQL inline dentro de rutas Express, no en funciones puras aisladas. Priorizar:
- Funciones de utilidad puras: `shortFilename()`, `esFTSError()` (rediseñada, sección 8 de la matriz), `enmascarar()`/`enmascararPayload()` (firma-gob), `formatearExpirationChile()`.
- Validadores Zod de cada módulo (`*.schema.ts`) — deben comportarse igual independientemente del motor de BD, pero se prueban igual como parte de la red de seguridad general.

## 2. Pruebas de integración — funcionales por módulo

Cada bloque se ejecuta **contra ambos motores** (SQL Server actual y PostgreSQL piloto) con el mismo dataset de partida, comparando respuesta de API campo por campo.

### 2.1 Autenticación
- Login con usuario/clave válidos (bcrypt y legacy texto plano) → token válido.
- Login con clave incorrecta → 401.
- Refresh token válido/expirado/revocado.
- `GET /me` con token válido/expirado.
- Recuperación de contraseña: solicitud, token válido/expirado/usado, reseteo exitoso.

### 2.2 Roles y permisos
- Usuario `admin` accede a todos los módulos.
- Usuario `funcionario` sin módulo asignado recibe 403 en endpoints restringidos.
- Asignación/revocación de rol solo permitida a `admin`.
- `todos_servicios` solo modificable por `admin`, default `false` para usuarios nuevos (fail-closed, ver `CLAUDE.md`).

### 2.3 Visibilidad por servicio
- Usuario de servicio A no ve documentos/trámites/archivos de servicio B en: `GET /documentos`, `GET /tramites`, `GET /busqueda`, `GET /documentos/buscar-por-numero`, `GET /reportes/*`, `GET /archivos`, `GET /archivos/:id/{preview,download}`.
- Usuario con `todos_servicios=true` ve todo, independientemente de rol.
- Transiciones de estado (despachar/recepcionar/derivar/terminar/reabrir) rechazadas si el usuario no tiene acceso al documento, incluso con rol adecuado.

### 2.4 Registro documental
- Crear documento con campos mínimos requeridos → `num_interno`/`num_oficial` asignados correctamente, secuenciales, sin huecos inesperados.
- Crear documento con archivo adjunto único y múltiple (2+, mezcla PDF/Word/Excel/imagen).
- Rechazo de archivo > límite configurado (`maxFileMB`) y de extensión no permitida.
- Cuota total (`maxTotalMB`) respetada en carga múltiple.

### 2.5 Recepción, derivación, despacho, cierre
- `PATCH /tramites/:id/recibir` cambia estado a 2.
- `PATCH /tramites/:id/cerrar` cambia estado a 3.
- `POST /documentos/:id/derivar` crea trámite nuevo con `idDependenciaDestino` correcto.
- Cada transición genera el evento de trazabilidad correspondiente y es visible en `GET /documentos/:id/historial`.
- Reapertura (`reabrir`) solo permitida a roles autorizados (`supervisores`, `admin`).

### 2.6 Memorándums y numeración correlativa (crítico)
- Confirmar memorándum → correlativo `MEMO-AÑO-COD-NNNNNN` único, secuencial dentro de año+dependencia.
- Dos servicios distintos generando memorándums en la misma ventana de tiempo **no deben bloquearse mutuamente más de lo estrictamente necesario** (validar contra el rediseño de Fase 1 del plan de migración — este es el criterio que reemplaza el comportamiento actual de `TABLOCKX` de bloqueo total).
- Eliminar un documento con memo pendiente libera el número para el siguiente memo del mismo año+servicio (regla de negocio de `CLAUDE.md`).
- Eliminar todos los memos de un período reinicia el contador en 1.
- `DELETE /memorandum/:idDocumento/pendiente` revierte correctamente (documento/memo_generado/tramite/documento_destino borrados; `firma_gob_historial`/`memorandum_firma_simple` desvinculados, no borrados).

### 2.7 Firma Simple DOC360
- Fase A (`POST /:id/firmar-simple`): valida contraseña correcta/incorrecta, emite código de verificación con hash.
- Expiración del código de verificación (`DATEDIFF(MINUTE,...)` rediseñado) se calcula igual en ambos motores para el mismo intervalo.
- Fase B (`PATCH /:id/firmar-simple/:idFirmaSimple/completar`): recalcula hash server-side, despacha documento sin sobrescribir el original.
- Fallo en cualquier paso posterior a `POST /confirmar` dispara rollback automático desde el frontend (`DELETE /memorandum/:idDocumento/pendiente`) — número de correlativo queda libre de inmediato, verificado con una segunda confirmación inmediata que debe reutilizar el mismo número.

### 2.8 Documentos reservados
- Campos de visibilidad restringida se comportan igual en ambos motores (verificar contra el criterio de servicio de la sección 2.3, dado que "reservado" en DOC360 se modela vía visibilidad por servicio, no como un flag aparte, según la evidencia del repo — ajustar si el diseño real difiere).

### 2.9 Alertas
- Configuración de horarios y activación (`GET`/`PUT /alertas/configuracion`).
- Envío manual (`POST /alertas/enviar-manual`) y envío masivo (`POST /alertas/enviar-todos`).
- `destinatarios` (JSON serializado en `NVARCHAR`/`TEXT` según motor) se lee y escribe idénticamente.
- Prueba de conectividad SMTP (`POST /alertas/probar-servicio/:id`) — no depende del motor de BD, incluir igual para cobertura de regresión general.
- Historial paginado (`GET /alertas/logs`) — mismo orden y contenido en ambos motores.

### 2.10 Reportes
- `GET /reportes/dashboard` — totales, porEstado, porMes, porTipo idénticos entre motores para el mismo dataset. Verificar particularmente el filtro `DATEADD(MONTH,-6,GETDATE())` traducido.
- `GET /reportes/actividad-reciente` — mismas 15 filas, mismo orden.
- `GET /reportes/exportar` — CSV con BOM, mismo contenido byte a byte salvo por diferencias de fin de línea esperadas.

### 2.11 Búsqueda (crítico por el rediseño de FTS)
- Búsqueda por término exacto, término parcial, con tildes/ñ — resultados equivalentes semánticamente entre `CONTAINS()` (SQL Server) y `tsquery` (PostgreSQL), aunque el ranking pueda diferir (documentar cualquier diferencia de orden, no debe tratarse como bug si el conjunto de resultados es el mismo).
- Fallback a `LIKE`/`ILIKE` cuando corresponda.
- Sanitización de queries maliciosas/con caracteres especiales no rompe la búsqueda en ningún motor.
- Búsqueda filtrada por servicio (cruce con sección 2.3).

### 2.12 Auditoría y trazabilidad
- Cada acción sensible (login, reset de contraseña, cambios de rol, `todos_servicios`) genera fila en `auditoria`/`auditoria_reset` con timestamp correcto.
- Eventos de firma electrónica (Firma Simple/FirmaGOB) aparecen como trámites propios en la trazabilidad (no como `UPDATE` in-place del trámite original), preservando el hallazgo ya corregido documentado en `CLAUDE.md`.

### 2.13 Integraciones — FirmaGOB
- Configuración de ambientes TEST/PRODUCCION persiste y se enmascara correctamente en logs.
- `POST /firma-gob/test-conexion` — no depende del motor de BD, pero el historial de la prueba sí.
- `revertirDocumentoSinFirmar()` — **validar que la corrección a transaccional (hallazgo M5, aplicada durante Fase 2) efectivamente deja el sistema consistente ante fallo simulado a mitad de secuencia** (matar el proceso Node entre el DELETE de `tramite` y el DELETE de `documento` en un entorno de prueba controlado).

## 3. Pruebas de concurrencia (críticas, específicas del rediseño C1)

- **N clientes concurrentes generando memorándums del mismo año+dependencia:** ejecutar 20-50 requests simultáneos vía script de carga, verificar 0 correlativos duplicados y 0 huecos no explicados (todo hueco debe corresponder a un rollback esperado, no a un bug).
- **N clientes concurrentes generando memorándums de años/dependencias distintos:** verificar que NO se bloquean entre sí de forma innecesaria (mejora esperada del rediseño respecto al `TABLOCKX` actual) — medir tiempo total y compararlo contra el mismo escenario en SQL Server.
- **Creación concurrente de documentos** (`num_interno`/`num_oficial`): mismo criterio, 20-50 requests simultáneos, 0 duplicados.
- **Firma Simple concurrente:** 2 usuarios intentando confirmar el mismo memorándum simultáneamente — solo uno debe tener éxito, el otro debe recibir un error claro, sin estado inconsistente.
- **Prueba de estrés del mecanismo de contadores** (si se elige la opción (a) de la Fase 1 del plan de migración): 100+ requests concurrentes contra la tabla de contadores dedicada, medir contención y confirmar ausencia de deadlocks.

## 4. Pruebas de regresión

- Ejecutar la suite completa de la sección 2 contra SQL Server (comportamiento actual, línea base) y contra PostgreSQL (piloto), diff automatizado de las respuestas JSON de cada endpoint.
- Cualquier diferencia debe clasificarse explícitamente como: (a) bug de la migración (corregir), (b) diferencia esperada y documentada (ej. orden de resultados de búsqueda por ranking distinto), o (c) mejora intencional (ej. corrección del hallazgo M5) — ninguna diferencia queda "sin explicar".

## 5. Pruebas de seguridad

- Confirmar que el rol de aplicación en PostgreSQL (equivalente a `doc360_app` en SQL Server) tiene privilegios mínimos: sin `SUPERUSER`, sin `CREATEDB`, solo `SELECT/INSERT/UPDATE/DELETE` sobre las tablas de la aplicación, sin acceso a catálogos de sistema más allá de lo necesario.
- Confirmar que el puerto de PostgreSQL no está expuesto a Internet (solo `127.0.0.1:puerto` o red interna Docker) — revisar `docker-compose.yml`/`docker-compose.preprod.yml` tras la Fase 3.
- Re-ejecutar cualquier prueba de inyección SQL ya cubierta implícitamente por el uso de parámetros (`.input()` → `$1,$2,...`) para confirmar que la migración no introdujo concatenación de input de usuario en ningún punto (revisión especialmente cuidadosa de los patrones de "columna dinámica" `SET ${col} = ...` identificados en el inventario).
- Confirmar enmascaramiento de credenciales de FirmaGOB en logs se mantiene igual tras la migración.

## 6. Pruebas de rendimiento

Ver también sección 4 de `AUDITORIA_FACTIBILIDAD_POSTGRESQL_DOC360.md` (métricas a capturar). Ejecutar en el ambiente piloto (Fase 6):

- Latencia p50/p95/p99 de `GET /documentos`, `GET /busqueda`, `GET /reportes/dashboard` bajo carga simulada (usar `PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md` como referencia de volumen de datos de prueba, escalado a un volumen proyectado de 2-3 años si es posible).
- Throughput de creación de documentos y memorándums, con y sin concurrencia.
- Tiempo de exportación CSV con el volumen máximo esperado (hasta 50.000 filas, límite actual documentado).
- Uso de CPU/memoria del contenedor de BD bajo la misma carga, límites de recursos Docker idénticos entre ambas pruebas (SQL Server vs. PostgreSQL) para que la comparación sea válida.

## 7. Pruebas de recuperación

- **Reinicio de contenedor:** derribar y levantar el contenedor `postgres` (`docker compose restart postgres`), confirmar que el backend reconecta automáticamente sin intervención manual (validar el comportamiento del pool `pg` bajo el mismo criterio que hoy aplica a `sql.ConnectionPool`).
- **Persistencia del volumen:** derribar el contenedor completo (`docker compose down`, sin `-v`) y volver a levantarlo, confirmar que los datos persisten intactos.
- **Backup y restauración completa:** ejecutar `pg_dump` → destruir el contenedor y su volumen (en un ambiente de prueba, nunca en el piloto compartido) → recrear desde cero → `pg_restore` → validar conteo de filas y hashes contra la línea base, igual criterio que la Fase 0/4 del plan de migración.
- **Simulación de fallo a mitad de transacción:** matar el proceso del backend durante una operación transaccional (creación de memorándum, softDelete) y confirmar que PostgreSQL deja la transacción abortada limpiamente (sin registros parciales), validando el comportamiento ACID esperado.

## 8. Criterios de salida de la Fase 5 (Pruebas)

La Fase 5 se da por completa y se autoriza avanzar a la Fase 6 (Piloto) solo cuando:

- [ ] 100% de las pruebas funcionales de la sección 2 pasan en PostgreSQL con paridad de comportamiento respecto a SQL Server (o diferencia documentada y aceptada explícitamente).
- [ ] 0 duplicados y 0 comportamiento inesperado en las 5 pruebas de concurrencia de la sección 3, ejecutadas al menos 3 veces cada una.
- [ ] 0 diferencias no explicadas en la comparación de regresión de la sección 4.
- [ ] Rol de aplicación PG verificado con privilegios mínimos (sección 5).
- [ ] Métricas de rendimiento capturadas y documentadas (sin exigir que PG sea más rápido — solo que el resultado esté medido y sea aceptable para el uso real).
- [ ] Las 4 pruebas de recuperación de la sección 7 pasan sin intervención manual no documentada.
