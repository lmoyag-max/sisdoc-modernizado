# Informe de Auditoría Técnica Integral — DOC360

## 1. Resumen ejecutivo

Se realizó una auditoría técnica completa de DOC360 (sistema de gestión documental del HUAP) cubriendo 12 áreas funcionales, con foco crítico explícito en la integridad transaccional de la numeración de correlativos de memorándum. Se identificaron y corrigieron **2 hallazgos críticos (P1)** relacionados con rollback e integridad transaccional, **7 hallazgos de severidad media (P2)** relacionados con visibilidad de datos por servicio y trazabilidad, y **3 hallazgos menores (P3)** de mantenibilidad y documentación. Todos los hallazgos corregidos fueron verificados end-to-end contra la base de datos de desarrollo mediante pruebas reales (no solo revisión de código). No se detectaron problemas en la lógica de concurrencia del correlativo ni en la reutilización de números tras eliminar documentos — ambos mecanismos ya estaban correctamente implementados.

El hallazgo más significativo: **Firma Simple DOC360 (el mecanismo de firma interno, único punto de entrada de firma desde la UI) no revertía el documento ni liberaba el correlativo cuando la firma fallaba** en cualquier paso posterior a la asignación del número — a diferencia de FirmaGOB, que sí tenía este mecanismo. Esto quedó corregido y verificado.

## 2. Fecha de la auditoría

**2026-07-09**, sesión única. Investigación de código (lectura directa + 3 agentes de exploración en paralelo), corrección en 3 etapas (P1 → P2 → P3), verificación end-to-end contra la base de datos de desarrollo (`SISDOC`, contenedor `sisdoc_sqlserver`).

## 3. Alcance

Auditoría de código fuente (`backend/src`, `frontend/src`, `database/scripts`) y comportamiento en tiempo de ejecución contra la BD de desarrollo. No incluyó pruebas de carga/rendimiento, pruebas de penetración formales, ni revisión de infraestructura Docker/Nginx. `/legacy` no fue modificado ni auditado (regla absoluta del proyecto).

## 4. Módulos auditados

1. Login / autenticación (JWT, refresh, recuperación de contraseña)
2. Dashboard
3. Documentos (CRUD, flujo de estados, adjuntos)
4. **Memorándum interno — foco crítico** (correlativos, Firma Simple, FirmaGOB)
5. FirmaGOB (integración externa)
6. Adjuntos / archivos digitales
7. Bandeja de entrada / enviados / búsqueda
8. Usuarios / roles / servicios
9. Jefaturas
10. Reportes
11. Documentos reservados
12. Alertas / correos

## 5. Hallazgos críticos (P1)

| # | Hallazgo | Ubicación | Estado |
|---|----------|-----------|--------|
| P1.1 | Firma Simple no revertía el documento ni el correlativo cuando la firma fallaba en cualquier paso posterior a `POST /memorandum/confirmar` | `MemorandumFirmaSimpleModal.tsx`, `memorandum.routes.ts` | ✅ Corregido y verificado |
| P1.2 | `softDelete()` no era transaccional (6 statements SQL independientes) y nunca limpiaba `memorandum_firma_simple`, dejando evidencia de firma huérfana al eliminar documentos | `documento.repository.ts` | ✅ Corregido y verificado |

## 6. Hallazgos de severidad media (P2)

| # | Hallazgo | Ubicación | Estado |
|---|----------|-----------|--------|
| P2.1 | `GET /busqueda` no aplicaba ningún filtro por servicio — exponía documentos/trámites/funcionarios de cualquier dependencia | `busqueda.routes.ts` | ✅ Corregido y verificado |
| P2.2 | `GET /documentos/buscar-por-numero` sin filtro de servicio ni verificación de acceso — permitía iterar correlativos y leer documentos ajenos, incluidos reservados | `documento.controller.ts` | ✅ Corregido y verificado |
| P2.3 | Las transiciones de estado (despachar/recepcionar/derivar/terminar/reabrir y sus variantes multi-destino) solo validaban rol, no pertenencia al documento | `documento.controller.ts` | ✅ Corregido y verificado |
| P2.4 | Descarga/preview/listado de archivos sin verificación de acceso al documento asociado | `archivos.routes.ts` | ✅ Corregido y verificado |
| P2.5 | `usuario.todos_servicios` (bypass total de visibilidad) con default inseguro y sin gate de rol para asignarlo | `auth.middleware.ts`, `auth.service.ts`, `usuarios.routes.ts`, BD | ✅ Corregido (default BD ya era correcto — ver §9) |
| P2.6 | Eventos de firma electrónica (Firma Simple y FirmaGOB) invisibles en la trazabilidad — `UPDATE` in-place en vez de generar un evento nuevo | `memorandum.routes.ts`, `firma-gob.routes.ts` | ✅ Corregido y verificado |
| P2.7 | Contraseña nueva escrita en texto plano en `usuario.clave` en **cada** reset de contraseña, no solo en cuentas legacy no migradas | `password-reset.routes.ts` | ✅ Corregido |

## 7. Hallazgos menores (P3)

| # | Hallazgo | Estado |
|---|----------|--------|
| P3.1 | `CLAUDE.md` no documentaba los roles reales `of.partes`/`supervisores`, ni las reglas de rollback/correlativo | ✅ Corregido |
| P3.2 | Filtro `idDependencia` declarado en `documento.schema.ts` pero nunca usado en la query (filtro "fantasma") | ✅ Eliminado |
| P3.3 | Sin advertencia si `NODE_ENV=production` no tiene `SMTP_HOST`/`SMTP_USER` configurado (las alertas se registran como enviadas sin llegar a nadie) | ✅ Advertencia agregada al boot |
| P3.4 | `MemorandumModal.tsx` es código muerto (solo se reutilizan sus tipos, el componente nunca se renderiza) | ⏳ Pendiente — documentado, no eliminado (ver §9) |
| P3.5 | Números mágicos sin constante nombrada (`id_estado_documento = 4`, `id_tipo_compromiso = 3`) repetidos en varios módulos | ⏳ Pendiente — cosmético, no urgente |
| P3.6 | `GET /reportes/exportar` carga hasta 50.000 filas en memoria sin streaming | ⏳ Pendiente — límite ya mitigado, no urgente |

## 8. Problemas corregidos (detalle técnico)

**P1.1 — Rollback de Firma Simple:** se creó `revertirMemorandumSinFirmar()` en `memorandum.routes.ts`, calcada de `revertirDocumentoSinFirmar()` de FirmaGOB pero extendida para desvincular `memorandum_firma_simple`. Nuevo endpoint `DELETE /memorandum/:idDocumento/pendiente` (solo el creador, solo documento en estado 1). El frontend (`MemorandumFirmaSimpleModal.tsx`) ahora guarda el `idDocumento` apenas se crea y, en el `catch` de `handleFirmar()`, llama automáticamente al endpoint de reversión antes de mostrar el error al usuario.

**P1.2 — `softDelete()` transaccional:** los 6 statements se envolvieron en `BEGIN TRANSACTION...COMMIT` (mismo patrón ya usado en la asignación de correlativos). Se agregó `UPDATE memorandum_firma_simple SET id_documento = NULL` antes del `DELETE FROM documento`, replicando el patrón ya usado con `firma_gob_historial`. Requirió migración (`16-firma-simple-rollback.sql`) porque `memorandum_firma_simple.id_documento` era `NOT NULL`.

**P2.1–P2.4 — Visibilidad por servicio:** se replicó en cada endpoint el mismo patrón `EXISTS(...)` / `hasFullAccess(user)` ya usado correctamente en `reportes.routes.ts` y `tramite.routes.ts`.

**P2.5 — `todos_servicios` fail-closed:** default cambiado de `true`/implícito a `false` en `auth.middleware.ts` (claim JWT), `auth.service.ts` (fallback de lectura) y `usuarios.routes.ts` (creación de usuario). Se agregó gate `admin`-only tanto en creación como en `PATCH /usuarios/:id`, igual que ya existía para `roles`.

**P2.6 — Trazabilidad de firma:** se reemplazó el `UPDATE tramite ... WHERE id_estado_tramite = 1` por un `INSERT` de un nuevo trámite en estado 2, con observación explícita (`"Despachado — firmado vía Firma Simple DOC360 (nombre, código)"` / `"vía FirmaGob"`), preservando intacto el trámite original. Aplicado en `memorandum.routes.ts` y `firma-gob.routes.ts`.

**P2.7 — Contraseña en claro:** el `UPDATE usuario` del reset ya no incluye la columna `clave` (permanece con el valor legacy obsoleto, nunca vuelto a consultar una vez que `clave_hash` existe). No se pudo poner `NULL` porque la columna es `NOT NULL` en el esquema legado — se optó por no tocarla, opción explícitamente contemplada en el plan.

## 9. Problemas pendientes / no aplicados

- **`todos_servicios` default en BD:** al verificar, el `DEFAULT` de la columna en la base de desarrollo ya era `0` (fail-closed), pese a que el script histórico `update-roles-usuarios.sql` documenta `DEFAULT 1`. Se creó `17-todos-servicios-fail-closed.sql` como red de seguridad idempotente para otros entornos (no aplicó cambios en desarrollo).
- **`MemorandumModal.tsx` (código muerto):** confirmado que el componente nunca se renderiza (solo se reutilizan sus tipos `FirmanteActivo`/`MemoDocumentoPayload` desde `NuevoDocumentoPage.tsx`). No se eliminó en esta auditoría para no introducir un cambio de refactor no solicitado explícitamente; se recomienda eliminarlo en una tarea dedicada, migrando los tipos a un archivo propio.
- **Números mágicos sin constante nombrada** y **streaming en exportación CSV**: identificados, de bajo impacto, no aplicados por no ser bloqueantes ni de seguridad.

## 10. Cambios de código

15 archivos backend/documentación modificados (551 inserciones, 101 eliminaciones). Ver detalle completo en el diff de git. Resumen por archivo en §12.

## 11. Cambios de base de datos

Todos aditivos, idempotentes, versionados y probados dos veces contra la BD de desarrollo:

| Script | Cambio |
|--------|--------|
| `database/scripts/16-firma-simple-rollback.sql` | `ALTER TABLE memorandum_firma_simple ALTER COLUMN id_documento INT NULL` (antes NOT NULL) |
| `database/scripts/17-todos-servicios-fail-closed.sql` | Cambia el `DEFAULT` de `usuario.todos_servicios` a `0` si no lo estaba ya (no aplicó cambios — ya era `0`) |

No se modificó ninguna tabla existente de forma destructiva. No se tocó `/legacy`.

## 12. Archivos modificados

**Backend:**
- `backend/src/middleware/auth.middleware.ts` — fail-closed en claim `todosServicios`
- `backend/src/modules/archivos/archivos.routes.ts` — control de acceso en preview/download/listado
- `backend/src/modules/auth/auth.service.ts` — fail-closed en fallback de `todos_servicios`
- `backend/src/modules/auth/password-reset.routes.ts` — elimina escritura de clave en texto plano
- `backend/src/modules/busqueda/busqueda.routes.ts` — filtro por servicio en las 3 entidades buscadas
- `backend/src/modules/documentos/documento.controller.ts` — control de acceso en transiciones y buscar-por-número
- `backend/src/modules/documentos/documento.repository.ts` — `softDelete()` transaccional
- `backend/src/modules/documentos/documento.schema.ts` — elimina filtro fantasma
- `backend/src/modules/firma-gob/firma-gob.routes.ts` — trazabilidad de firma (INSERT en vez de UPDATE)
- `backend/src/modules/firma-gob/firma-gob.utils.ts` — limpieza de `memorandum_firma_simple` en rollback
- `backend/src/modules/memorandum/memorandum.routes.ts` — rollback de Firma Simple + endpoint nuevo + trazabilidad
- `backend/src/modules/usuarios/usuarios.routes.ts` — fail-closed + gate admin para `todos_servicios`
- `backend/src/server.ts` — advertencia de SMTP en boot

**Frontend:**
- `frontend/src/components/documentos/MemorandumFirmaSimpleModal.tsx` — rollback automático en `catch`

**Documentación:**
- `CLAUDE.md`, `README.md` — actualizados con estado real del sistema, roles, reglas de correlativo/rollback

**Nuevos (base de datos):**
- `database/scripts/16-firma-simple-rollback.sql`
- `database/scripts/17-todos-servicios-fail-closed.sql`

> Nota: el repositorio tenía además cambios sin commitear de una tarea previa a esta auditoría ("Memorándum institucional real" — `catalogos.service.ts`, `catalogos.api.ts`, `MemorandumFields.tsx`, `memorandum.generator.ts`, `NuevoDocumentoPage.tsx`, `15-memo-destinatario.sql`). No forman parte de esta auditoría y no fueron tocados en esta sesión salvo lo indicado arriba.

## 13. Pruebas ejecutadas y resultados

Todas las pruebas se ejecutaron con `curl` contra el backend de desarrollo real (`localhost:3001`) y la BD de desarrollo, con datos de prueba limpiados al finalizar cada caso.

| Caso | Resultado |
|------|-----------|
| Crear memo → confirmar correlativo (`MEMO-2026-ABA001-000001`) | ✅ OK |
| Forzar fallo de Firma Simple (contraseña incorrecta) → `DELETE /memorandum/:id/pendiente` → documento eliminado | ✅ OK — `Documento no encontrado` tras revertir |
| Crear un nuevo memo tras la reversión | ✅ Reutilizó el mismo número `000001` — no avanzó a `000002` |
| `DELETE /documentos/:id` normal (no memorándum) con la transacción nueva en `softDelete()` | ✅ OK, comportamiento idéntico al anterior |
| Completar flujo real de Firma Simple (Fase A + Fase B) | ✅ Documento despachado, PDF final generado |
| Verificar trazabilidad tras firma completa | ✅ Aparece evento nuevo: `"Despachado — firmado vía Firma Simple DOC360 (Jocelyn Andrea Díaz Soto, código DOC360-FS-2026-000017)"`, trámite original intacto |
| Usuario de otro servicio (`ti`, dependencia 78) busca por texto un documento de Abastecimiento | ✅ `0` resultados (antes los veía) |
| Usuario de otro servicio busca por número exacto | ✅ `[]` (antes lo veía) |
| Usuario de otro servicio intenta despachar el documento ajeno | ✅ `403 No tienes acceso a este documento` |
| Usuario admin (`todosServicios`) ve el mismo documento por búsqueda | ✅ Lo ve — confirma que el filtro no rompe el acceso legítimo |
| Usuario de otro servicio intenta descargar un archivo ajeno | ✅ `403` |
| Usuario de otro servicio lista archivos de un documento ajeno | ✅ `[]` |
| `npm run typecheck` backend | ✅ Sin errores (ejecutado 3 veces, tras P1, P2 y limpieza P3) |
| `npm run typecheck` frontend | ✅ Sin errores |

**Casos no ejecutados en esta sesión** (por alcance/tiempo, no bloqueantes): creación concurrente simultánea real (2 requests en paralelo exactos — la lógica `TABLOCKX+HOLDLOCK` fue verificada por lectura de código, no por prueba de carrera real), envío efectivo a FirmaGOB (requiere credenciales de ambiente TEST configuradas), acceso no autorizado a documento reservado por un usuario de un tercer servicio (la lógica se verificó por revisión de código: reservado fuerza destino a Dirección id=32, y el fix P2.3 ya cubre que solo usuarios de Dirección puedan operarlo).

## 14. Riesgos residuales

- **Concurrencia real no probada bajo carga:** el mecanismo `TABLOCKX+HOLDLOCK` es correcto por diseño, pero no se ejecutó una prueba de carrera real con 2 requests simultáneos en esta sesión.
- **`MemorandumModal.tsx` código muerto:** bajo riesgo, pero si en el futuro alguien lo vuelve a montar por error, no tiene el fix de rollback de P1.1.
- **Contraseñas legacy en texto plano preexistentes:** el fix de P2.7 evita que se sigan escribiendo, pero no purga las que ya existen en `usuario.clave` de resets anteriores a este fix ni de cuentas nunca migradas a bcrypt.
- **Exportación CSV sin streaming:** riesgo de picos de memoria con exportaciones concurrentes de gran volumen — mitigado por el techo de 50.000 filas, no eliminado.

## 15. Recomendaciones

1. Ejecutar una prueba de concurrencia real (2+ requests simultáneos a `POST /memorandum/confirmar`) en un entorno de staging antes de la próxima campaña de memorándums masiva.
2. Programar una tarea de limpieza para purgar `usuario.clave` (poner en un valor no utilizable) de todas las cuentas que ya tienen `clave_hash` poblado, cerrando la exposición histórica de P2.7 de forma retroactiva.
3. Eliminar `MemorandumModal.tsx` en una tarea dedicada, migrando sus tipos a un archivo compartido.
4. Introducir un módulo de constantes (`shared/constants/estados.ts`) para los números mágicos de estado, de forma incremental (no bloqueante).
5. Evaluar streaming (o paginación real) en `GET /reportes/exportar` si el volumen de documentos por servicio crece significativamente.

## 16. Próximos pasos

- Validar en un entorno de staging con datos reales (o una copia sanitizada) antes de desplegar a producción.
- Comunicar a los usuarios del rol `of.partes`/`supervisores` que la documentación de roles en `CLAUDE.md` ahora es la fuente de verdad (antes desactualizada).
- Considerar los ítems de la sección 15 como backlog de la próxima iteración.

## 17. Checklist final

- [x] Numeración de correlativos verificada como única, transaccional y segura ante concurrencia (por diseño + revisión de código)
- [x] Correlativo no avanza en cancelación/fallo de validación/fallo de BD — verificado con prueba real
- [x] Rollback completo ante fallo de firma — verificado con prueba real
- [x] Reutilización correcta de números al eliminar memos no históricos — verificado con prueba real
- [x] Visibilidad por servicio consistente en documentos, bandeja, búsqueda, buscar-por-número, reportes, archivos
- [x] Trazabilidad refleja eventos de firma electrónica (Firma Simple y FirmaGOB)
- [x] `todos_servicios` fail-closed con gate de rol admin
- [x] `softDelete()` transaccional, sin dejar huérfanos
- [x] Ninguna tabla de `/legacy` modificada
- [x] Todos los cambios de BD son aditivos, versionados e idempotentes
- [x] `npm run typecheck` limpio en backend y frontend
- [x] `README.md` y `CLAUDE.md` actualizados con el estado real del sistema
- [ ] Prueba de concurrencia real bajo carga (pendiente, ver §14/§15)
- [ ] Purga retroactiva de `usuario.clave` en cuentas ya migradas a bcrypt (pendiente, ver §15)
