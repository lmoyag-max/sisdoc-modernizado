# Plan de Limpieza y Carga de Datos Demostrativos — DOC360

**Fecha del análisis:** 2026-08-06
**Ambiente analizado:** Desarrollo local (`docker compose up -d sqlserver` + backend/frontend en `npm run dev`)
**Base de datos:** `SISDOC` — contenedor `sisdoc_sqlserver` (SQL Server 2022), puerto host `15433`
**Alcance de este documento:** ETAPA 1 únicamente — análisis y planificación. **No se ha eliminado ni modificado ningún dato.**

---

## 1. Resumen del análisis

Se auditó la base de datos `SISDOC` completa (74 tablas), el backend (`backend/src/modules/*`) y los scripts SQL existentes en `database/scripts/`. Hallazgo principal:

> **Los 28 documentos actualmente en el sistema NO son datos de un piloto real ni contienen información institucional legítima.** Son basura de pruebas manuales de desarrollo (materias como `"yu,yu,y,i,iy"`, `"ssaccascsacsac"`, `"efefefefe"`, además de casos de prueba explícitos como `"Prueba Firma Simple DOC360"`, `"Prueba SUBROGANTE"`), creados entre el 2026-06-24 y el 2026-07-09 por las cuentas de desarrollo/testing (`ti`, `ofparte`, `lmoya`). Confirma esto un umbral ya usado por scripts previos del propio equipo (`database/scripts/clean-documentos-tramites.sql`, `clean-documentos-completo.sql`): todo `id_documento >= 378000` se considera "rango de prueba del sistema nuevo", y el 100% de los documentos actuales cae sobre ese umbral (397274–403239).

Un segundo hallazgo, más sensible, cambia el enfoque de "limpiar datos ficticios" a "distinguir con cuidado qué es ficticio y qué es real":

> **La tabla `usuario` (9 filas) NO contiene cuentas ficticias.** Contiene las cuentas institucionales reales actualmente en uso: `admin`, `ti`, `ofparte` y seis cuentas de personal (`savendano`, `lmoya`, `nvargas`, `jdiaz`, `sdonoso`, `respinoza`) vinculadas a correos reales de dominio `@redsalud.gov.cl` / `@huap.online`. Estas **no deben tocarse** — son las cuentas reales de administración/desarrollo del proyecto, no datos demostrativos. La tabla `funcionario` subyacente (616 filas) es en gran parte un import legacy del sistema ASP original y puede contener nombres/RUT reales de personal del hospital; **no se debe leer, exponer ni reutilizar su contenido** para la carga demo (regla del usuario: no usar datos personales reales).

No se detectaron triggers ni vistas en la base. Sí existen ~140 stored procedures heredados del sistema ASP clásico (`Ingreso_Documento`, `derivar_tramite2`, `busca_*`, etc.) que **el backend moderno no invoca en absoluto** (confirmado por búsqueda en `backend/src`) — quedan fuera de alcance, igual que las tablas legacy de nómina/facturación que tampoco toca el backend actual.

---

## 2. Inventario de tablas (74 tablas, con conteo actual de filas)

| Tabla | Filas | Uso por backend moderno |
|---|---:|---|
| documento | 28 | Sí |
| documento_destino | 29 | Sí |
| tramite | 57 | Sí |
| archivo_digital | 27 | Sí |
| expediente | 4 | Sí (catálogo, no expuesto aún en UI activa) |
| memo_generado | 24 | Sí |
| memo_firmante | 1 | Sí (fallback de firmante si no hay `jefatura`) |
| memo_correlativo | 3 | **No** — huérfana, sin referencias en `backend/src` |
| memorandum_firma_simple | 19 | Sí |
| firma_gob_config | 2 | Sí (TEST/PRODUCCION, sin credenciales configuradas) |
| firma_gob_historial | 61 | Sí |
| firma_gob_logs | 52 | Sí |
| jefatura | 126 | Sí — fuente de verdad de firmantes |
| jefatura_backup_20260608 | 2 | No — backup manual previo del equipo |
| usuario | 9 | Sí — **cuentas institucionales reales** |
| usuario_rol | 9 | Sí |
| funcionario | 616 | Sí (soporte de `usuario`) — **posible PII legacy real** |
| rol | 4 | Sí |
| rol_modulo | 31 | Sí |
| refresh_token | 79 | Sí |
| password_reset_tokens | 11 | Sí |
| auditoria_reset | 18 | Sí |
| acceso | 348 | Sí — log de accesos |
| alerta_config | 1 | Sí |
| alerta_log | 68 | Sí |
| dependencia | 150 (126 vigentes) | Sí — catálogo de servicios |
| dependencia_backup_20260608 | 91 | No — backup manual previo |
| dependencia_externa | 532 | Sí — catálogo de entidades externas |
| descriptor | 179 | Sí (1 archivo) |
| tipo_documento | 81 (61 vigentes) | Sí — catálogo |
| estado_documento | 4 | Sí — catálogo (Registrado/Despachado/Recepcionado/Terminado) |
| estado_tramite | 7 | Sí — catálogo |
| estado_compromiso | 4 | Sí — catálogo |
| tipo_compromiso | 3 | Sí — catálogo (No tiene/Normal/Urgente) |
| tipo_distribucion | 126 | Sí — catálogo |
| dias_compromiso_alertas | 21 | Sí — catálogo |
| respaldo_documento | 57 | Sí — tabla de auditoría de `softDelete()` |
| coordinadores | 1 | No |
| correos_alerta | 0 | No |
| correos_alerta_codigos | 1 | No |
| dependencias_alerta | 1 | No |
| descriptor_documento | 0 | No (referenciado condicionalmente en scripts legacy) |
| detalle_facturas | 0 | No |
| encuesta | 0 | No |
| facturas | 0 | No |
| calendario | 2 892 | No |
| nomina_despacho | 375 883 | No |
| nominas_facturas | 0 | No |
| numero_interno1 | 7 556 | No |
| paso_correo / paso_correos_jefatura / paso_correos_secretaria | 0 | No |
| perfiles_facturas | 0 | No |
| plazo_flujo | 16 | No |
| proveedores | 0 | No |
| relacion_documento | 63 853 | No |
| relacion_documento_factura | 0 | No |
| respaldo_factura | 0 | No |
| servicio_correos_alerta | 0 | No |
| temas_alertas | 6 | No |
| temas_facturas | 6 | No |
| tipo_facturas | 6 | No |

*(Nota: `usuario_backup_2026`, mencionada en `CLAUDE.md`, no existe actualmente en la base — documentación desactualizada, sin impacto en este plan.)*

No existen triggers ni vistas definidas en la base. Los ~140 stored procedures existentes son 100% legacy (prefijos `busca_*`, `Ingreso_*`, `Mod_*`, `derivar_tramite*`) y no son invocados por `backend/src` (confirmado por búsqueda exhaustiva) — el backend usa exclusivamente queries parametrizadas directas vía `mssql`.

---

## 3. Clasificación de tablas

### A. Identidad institucional — **PRESERVAR, no tocar**
`usuario`, `usuario_rol`. Las 9 cuentas son reales y en uso activo (incluye la cuenta del propio equipo de desarrollo). `funcionario` se preserva en su totalidad — no se lee ni reutiliza su contenido para generar datos ficticios; los nuevos funcionarios demo se crean exclusivamente vía `POST /usuarios` (que inserta su propia fila en `funcionario`), nunca leyendo las 616 filas existentes.

### B. Catálogos / tablas maestras — **PRESERVAR íntegramente**
`rol`, `rol_modulo`, `dependencia`, `dependencia_externa`, `tipo_documento`, `estado_documento`, `estado_tramite`, `estado_compromiso`, `tipo_compromiso`, `tipo_distribucion`, `dias_compromiso_alertas`, `descriptor`, `alerta_config`, `expediente` *(ver nota abajo)*.

> Nota sobre `expediente`: sus 4 filas actuales (`"PRUEBA DE EXPEDIENTE"`, `"Test expediente API"` ×3) son basura de pruebas, no catálogo real — se reclasifican como **dato operacional a limpiar** (grupo D), no como maestro.

### C. Configuración de firmantes — **PRESERVAR estructura, puede extenderse vía API**
`jefatura` (126 filas, una por dependencia vigente; solo 3 tienen firma+timbre+usuario vinculado hoy), `memo_firmante`, `firma_gob_config`. Para la Fase 5/6/14 del plan de demo se necesitará ampliar la cobertura de firmantes (más dependencias con imagen de firma DEMO y usuario vinculado), pero **mediante los endpoints reales de administración** (`/admin/jefaturas`, `POST /memorandum/firmantes`, `POST /firmantes/:id/imagen`), no por `INSERT` directo.

### D. Datos operacionales de prueba — **candidatos a limpieza (pendiente confirmación)**
`documento` (28), `documento_destino` (29), `tramite` (57), `archivo_digital` (27, + archivos físicos reales en `backend/uploads/`), `expediente` (4), `memo_generado` (24), `memorandum_firma_simple` (19), `firma_gob_historial` (61), `firma_gob_logs` (52), `alerta_log` (68), `respaldo_documento` (57 — backups de soft-deletes previos de estos mismos documentos de prueba), `acceso` (348), `refresh_token` (79 — sesiones; se recomienda limpiar solo las expiradas/revocadas), `password_reset_tokens` (11), `auditoria_reset` (18).

### E. Legacy / fuera de alcance — **no tocar bajo ninguna circunstancia**
Todo lo no listado arriba: `nomina_despacho`, `relacion_documento`, `numero_interno1`, `calendario`, `facturas`/`detalle_facturas`/`nominas_facturas`/`perfiles_facturas`/`respaldo_factura`/`temas_facturas`/`tipo_facturas`, `proveedores`, `encuesta`, `plazo_flujo`, `paso_correo*`, `correos_alerta*`, `dependencias_alerta`, `servicio_correos_alerta`, `coordinadores`, `descriptor_documento`, `relacion_documento_factura`, `memo_correlativo` (huérfana), `dependencia_backup_20260608`, `jefatura_backup_20260608`. Estas tablas pertenecen al universo legacy (aunque viven en la misma base de datos que el sistema moderno) y no son alcanzadas por ningún endpoint del backend actual — tocarlas no aporta nada a la demo y viola el espíritu de la regla "nunca modificar `/legacy`".

---

## 4. Datos que se conservarán

- Las 9 cuentas de `usuario` / `usuario_rol` reales, con sus roles y permisos intactos.
- Los 616 registros de `funcionario` (sin lectura ni reutilización de su contenido).
- Todos los catálogos (dependencias, tipos de documento, estados, tipos de compromiso/distribución, días de compromiso).
- La configuración de `jefatura` / `memo_firmante` existente (los 3 firmantes ya operativos hoy se mantienen; se añaden más, no se reemplazan).
- `firma_gob_config` (sin credenciales — se mantiene así; la demo usará exclusivamente Firma Simple, no requiere FirmaGOB).
- Todas las tablas legacy del grupo E, intactas.
- Los backups manuales previos del equipo (`dependencia_backup_20260608`, `jefatura_backup_20260608`).
- Los archivos de configuración de UI (`backend/uploads/config/` — logo, fondo de login).

## 5. Datos que se eliminarán (previa confirmación explícita)

Las filas del grupo D completo (documento/tramite/archivos/memorándums/firma simple/firma-gob/alertas/accesos/resets de prueba) **y** los 27 archivos físicos correspondientes en `backend/uploads/`. Cantidad total estimada: ~700 filas operacionales, ninguna con información real (confirmado por muestreo de contenido).

---

## 6. Riesgos encontrados

| # | Riesgo | Detalle | Mitigación |
|---|---|---|---|
| 1 | **Confusión ficticio/real en `usuario`/`funcionario`** | El pedido original asumía que todo lo actual era "ficticio". No lo es: `usuario` son cuentas reales del equipo. | No tocar `usuario`/`usuario_rol`; no leer/reutilizar `funcionario` existente. |
| 2 | **Integridad referencial sin FK en varias tablas** | `tramite.id_documento`, `archivo_digital.id_documento`, `memorandum_firma_simple.id_documento`, `respaldo_documento.id_documento` **no tienen FK a nivel de motor** (se valida solo en la aplicación). Un `DELETE` en el orden incorrecto no fallará por FK pero puede dejar huérfanos silenciosos. | Seguir estrictamente el orden de la sección 8, verificado contra las FK reales existentes (`memo_generado`→`documento`, `memo_generado`→`archivo_digital`, `documento_destino`→`documento`, `firma_gob_historial`→`documento`). |
| 3 | **Correlativos globales, no reseteables por diseño** | `documento.num_interno`/`num_oficial` se calculan como `MAX(...)+1` sobre **toda** la tabla (con `UPDLOCK+HOLDLOCK`, sin filtro por año/servicio). Si se eliminan todos los documentos, el próximo correlativo vuelve a partir de 1 — comportamiento esperado y deseable para la demo, ya documentado como diseño deliberado en `CLAUDE.md`. | Ninguna acción especial requerida; simplemente no manipular manualmente `num_interno`/`num_oficial`. |
| 4 | **Correlativo de memorándum por año+servicio (`memo_generado`)** | Igual lógica `MAX(numero)+1` con `TABLOCKX+HOLDLOCK`, por `id_dependencia_origen`+`anio`. Reutilización automática al eliminar — sin pasos manuales. | Generar memorándums demo exclusivamente vía `POST /memorandum/confirmar` (nunca INSERT directo), como exige además la regla "no modificar reglas de negocio". |
| 5 | **`archivo_digital` referencia archivos físicos reales en disco** | Eliminar filas sin eliminar (o sin poder recrear) los archivos en `backend/uploads/` deja el filesystem desincronizado. | El script de limpieza debe emitir también la lista de rutas físicas a borrar; ejecutar el borrado de archivos como paso manual verificado, no automático dentro del SQL. |
| 6 | **`respaldo_documento` es en sí misma una tabla de auditoría** | Contiene el rastro de eliminaciones (`softDelete()`) de los mismos 28 documentos de prueba. Limpiarla es correcto para la demo, pero **no debe limpiarse en producción real** — es evidencia de auditoría legítima ahí. | Confirmado ambiente = desarrollo local (no producción); ver control de seguridad en sección 11. |
| 7 | **`memorandum_firma_simple.id_documento` es nullable a propósito** | Al revertir/eliminar un memorándum sin firmar, el sistema desvincula (no borra) esta tabla para preservar evidencia. Un `DELETE` masivo directo sobre `memorandum_firma_simple` sin pasar por la lógica de la app pierde ese matiz, pero es aceptable **solo** porque estos 19 registros son 100% de prueba, sin valor de auditoría real que preservar. | Aceptado explícitamente para este caso; no generalizar a datos reales futuros. |
| 8 | **`funcionario` puede contener PII real (import legacy)** | 616 filas, de las cuales solo 14 tienen `vigencia='S'`; el resto (`N`/`NULL`) es probablemente import legacy de personal real. | No leer, no exportar, no usar como fuente de nombres/RUT para la demo. Los funcionarios demo se crean 100% nuevos vía API. |
| 9 | **Reglas de visibilidad por servicio recién auditadas (2026-07-09)** | El sistema tiene lógica fail-closed de `todos_servicios` y filtros `EXISTS` por servicio en casi todos los endpoints de listado (ver `CLAUDE.md`). Generar datos demo sin respetar esto podría, sin querer, crear usuarios con visibilidad cruzada indebida. | Todos los usuarios demo se crean con `todos_servicios=false` salvo que se quiera demostrar explícitamente ese caso con un usuario admin adicional, y siempre vía `POST/PATCH /usuarios` con rol `admin` autenticado. |
| 10 | **Puerto de SQL Server cambió de 11433 a 15433 en esta sesión** | Windows reservó dinámicamente el rango 11403–11502 (Hyper-V), obligando a remapear el puerto host en `docker-compose.yml` y `backend/.env`. | Ya aplicado y documentado; sin impacto en este plan, pero relevante si se reinicia el entorno. |

---

## 7. Dependencias entre tablas (relevantes para el orden de limpieza)

FK reales a nivel de motor (confirmadas por consulta a `sys.foreign_keys`):

```
memo_generado.id_documento        → documento.id_documento
memo_generado.id_archivo_digital  → archivo_digital.id_archivo_digital
documento_destino.id_documento    → documento.id_documento
firma_gob_historial.id_documento  → documento.id_documento   (nullable)
documento.id_tipo_documento       → tipo_documento
documento.id_estado_documento     → estado_documento
jefatura.id_dependencia           → dependencia
jefatura.id_usuario_titular/subrogante/subrogante_2 → usuario
memo_firmante.id_dependencia      → dependencia
password_reset_tokens.id_usuario  → usuario
```

Relaciones **sin FK de motor** (validadas solo en la aplicación — deben respetarse igual en el orden de borrado):

```
tramite.id_documento              → documento   (no enforced)
archivo_digital.id_documento      → documento   (no enforced)
memorandum_firma_simple.id_documento → documento (no enforced, nullable por diseño)
respaldo_documento.id_documento   → documento   (no enforced)
alerta_log.id_dependencia         → dependencia (no enforced)
acceso.id_usuario / id_dependencia → usuario/dependencia (no enforced)
```

---

## 8. Orden recomendado de limpieza (grupo D únicamente)

1. `firma_gob_logs` (sin FK saliente relevante)
2. `firma_gob_historial` (FK → `documento`)
3. `memorandum_firma_simple` (FK lógica → `documento`, nullable)
4. `memo_generado` (FK → `documento` y → `archivo_digital`) — **debe ir antes que ambas**
5. `documento_destino` (FK → `documento`)
6. `tramite` (FK lógica → `documento`)
7. `respaldo_documento` (FK lógica → `documento`)
8. `archivo_digital` (ya liberada de la FK de `memo_generado`)
9. `expediente` (sin FK entrante desde los documentos actuales — todos `id_expediente IS NULL`)
10. `documento` (última — ya sin referencias entrantes)
11. `alerta_log`, `acceso`, `auditoria_reset`, `password_reset_tokens` (independientes, sin relación con `documento`; pueden limpiarse en cualquier momento del proceso)

Este orden es el mismo que ya usaban, de forma parcial, los scripts previos del equipo (`clean-documentos-tramites.sql`, `clean-documentos-completo.sql`), extendido para cubrir las tablas de memorándum/firma que esos scripts no contemplaban (motivo por el cual quedaban huérfanos en `memo_generado`/`firma_gob_historial` si se ejecutaban tal cual).

---

## 9. Estrategia de respaldo (a ejecutar en Fase 2, tras confirmación)

1. **Backup completo de la base** (`BACKUP DATABASE SISDOC TO DISK=...`) montado en el volumen `./database:/var/opt/mssql/backup` ya existente en `docker-compose.yml`.
2. **Backup adicional solo de las 14 tablas operacionales del grupo D** vía `SELECT * INTO {tabla}_bak_demo_<fecha>` (mismo patrón que los scripts previos del equipo, con nombre versionado por fecha para no perder respaldos anteriores).
3. **Conteo de filas por tabla** (las 74 tablas) exportado a CSV antes de cualquier cambio.
4. **Archivo de verificación** con fecha/hora, nombre de BD, hash o tamaño del `.bak`, y el conteo total.
5. Todo almacenado bajo `/backups/demo-doc360/` (carpeta a crear; no existe aún).
6. Comandos `docker exec` usando el contenedor real (`sisdoc_sqlserver`) y las credenciales reales de `.env` / `backend/.env` (`sa` para el backup completo — es la única operación que legítimamente requiere `sa`; el resto de la limpieza puede correr con `doc360_app` si sus permisos `db_owner` lo permiten, a verificar en Fase 2).

## 10. Estrategia de restauración

- Restauración completa: `RESTORE DATABASE SISDOC FROM DISK=... WITH REPLACE`, documentada paso a paso en el propio script de backup.
- Restauración parcial (solo tablas operacionales): `DELETE` + `INSERT ... SELECT * FROM {tabla}_bak_demo_<fecha>`, para poder revertir sin perder los datos demo ya cargados si algo falla a mitad de la Fase 7.

## 11. Estrategia de generación de datos demostrativos

**Regla rectora (ya exigida por `CLAUDE.md` y por el pedido del usuario):** no duplicar lógica de negocio en SQL. El backend ya expone, vía API REST, todo lo necesario para crear datos respetando trazabilidad, correlativos transaccionales y reglas de visibilidad:

- Usuarios ficticios → `POST /usuarios` (crea `funcionario` + `usuario` + `usuario_rol` en un solo paso, sin tocar los 616 funcionarios legacy existentes).
- Documentos → `POST /documentos` (respeta el `UPDLOCK+HOLDLOCK` de correlativos).
- Derivación / recepción / cierre → `POST /documentos/:id/derivar`, `PATCH /tramites/:id/recibir`, `PATCH /tramites/:id/cerrar`.
- Memorándums → `POST /memorandum/confirmar` (correlativo transaccional real) + `POST /memorandum/:id/firmar-simple` + `PATCH .../completar`.
- Jefaturas/firmantes demo adicionales → endpoints de `/admin/jefaturas` y `POST /memorandum/firmantes` / `POST /firmantes/:id/imagen`.
- Archivos adjuntos → `POST /archivos/upload` con PDFs ficticios generados localmente (texto plano "DOCUMENTO FICTICIO PARA DEMOSTRACIÓN DOC360").

Por lo tanto, el "script de generación" de la Fase 7 en adelante debería ser, preferentemente, **un script Node/TypeScript que actúa como cliente HTTP autenticado contra el propio backend** (`http://localhost:3001/api/v1`), no un script SQL de inserciones masivas. El SQL directo se reserva exclusivamente para: el respaldo, la limpieza (Fase 3) y las validaciones de solo lectura (Fase 18).

Cada registro creado por este proceso llevará una marca identificable (`DEMO_DOC360_2026`) en un campo de texto libre disponible (p. ej. dentro de `observaciones` u `materia`, según el tipo de registro), para permitir identificarlos y —si se decide— revertir la carga demo de forma selectiva más adelante.

## 12. Controles de seguridad

- **Bloqueo por ambiente:** el script de limpieza (Fase 3) deberá verificar `NODE_ENV`/nombre de servidor antes de ejecutar cualquier `DELETE`, y abortar si detecta indicios de producción (por convención de este proyecto, ambiente productivo corre bajo `docker compose --profile prod`, con `NODE_ENV=production` en `backend/.env` — hoy el `.env` tiene `NODE_ENV=development`, confirmado).
- **Confirmación explícita obligatoria** (`DEMO_RESET_CONFIRMATION=CONFIRMO_LIMPIEZA_DOC360`) antes de cualquier `DELETE`, a implementar en la Fase 3.
- **Ningún `DROP TABLE` ni `DROP DATABASE`** en ningún script propuesto.
- **Ningún `TRUNCATE`** — se usará `DELETE` en el orden de la sección 8, dado que varias tablas tienen FK reales entrantes.
- **Sin datos personales reales**: los usuarios/funcionarios demo se generan 100% nuevos vía API, sin leer la tabla `funcionario` existente; RUT ficticios en formato claramente inválido (no calculable como RUT real); correos bajo dominio `demo.invalid`.
- **SMTP real deshabilitado durante la carga**: verificar `SMTP_HOST`/`SMTP_USER` en `backend/.env` y, si se ejecuta el generador de alertas, hacerlo sin disparar envío real (usar el endpoint de "registro sin envío" si existe, o dejar el scheduler de alertas en modo simulado durante la carga).

## 13. Plan de validación (Fase 18, a futuro)

Antes de dar por lista la demo se validará automáticamente (script de solo lectura, sin modificar datos):

- Cero documentos sin `id_usuario` o sin `id_tipo_documento`/`id_estado_documento`.
- Cero filas huérfanas en `tramite`, `archivo_digital`, `memo_generado`, `memorandum_firma_simple`, `firma_gob_historial` respecto a `documento`.
- Cero usuarios sin fila en `usuario_rol`.
- Cero correlativos duplicados en `memo_generado` (`correlativo` único) ni en `documento` (`num_interno`/`num_oficial` únicos).
- Cronología válida: `fecha_recepcion >= fecha_despacho`, `fecha_cierre >= fecha_creacion` en `tramite`/`documento_destino`.
- Presencia de datos en todos los estados clave (`estado_documento`, `estado_tramite`) para poblar bandejas y dashboard.
- Ningún correo con dominio real, ningún RUT con formato válido de RUT chileno real entre los registros marcados `DEMO_DOC360_2026`.
- Documentos reservados visibles solo para los roles/servicios autorizados (re-verificación funcional, no solo de datos).

---

## 14. Próximos pasos

Este documento cierra la **ETAPA 1**. Antes de continuar a la ETAPA 2 (presentación formal de hallazgos y riesgos — ya resumidos en las secciones 6 y 11 de este mismo documento) y, sobre todo, antes de tocar cualquier dato, se requiere confirmación explícita del usuario para avanzar a:

- **ETAPA 3:** redacción de los scripts concretos de respaldo (`01`) y limpieza (`02`), aún no creados.
- **ETAPA 5–6:** ejecución real del respaldo y de la limpieza controlada.

**No se ha ejecutado ningún `DELETE`, `INSERT`, `BACKUP` ni cambio de esquema. La base de datos permanece exactamente como estaba al iniciar este análisis.**
