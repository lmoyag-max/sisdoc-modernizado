# AUDITORIA_FUNCIONALIDADES_DOC360.md
## Reporte de Auditoría Documental — DOC360

| Campo | Valor |
|---|---|
| Objeto de la auditoría | Comparar `md/FUNCIONALIDADES_DOC360.md` (2026-08-06) contra el estado real del código fuente al 2026-08-17, y producir `md/FUNCIONALIDADES_DOC360_ACTUALIZADO.md` |
| Fecha del informe | 2026-08-17 |
| Método | Ver "Nota de método" en `FUNCIONALIDADES_DOC360_ACTUALIZADO.md` — evidencia de `git log`/`git diff --stat`, lectura íntegra de cada archivo tocado, verificación cruzada contra el código vivo |
| Restricciones respetadas | Sin modificación de código, sin cambios de BD/migraciones, sin comandos destructivos, sin instalación de dependencias, sin exposición de secretos, sin sobrescribir ni eliminar el documento original |

---

## 1. Resumen Ejecutivo

El documento funcional base (`FUNCIONALIDADES_DOC360.md`, 2026-08-06) resultó, tras esta auditoría, **mayoritariamente vigente y preciso**. De sus 14 secciones y 20 módulos documentados, **ninguno presenta una discrepancia entre lo documentado y el código real** — todos los cambios detectados desde esa fecha son **adiciones** (nuevo módulo, nuevas capacidades sobre módulos existentes) o **correcciones puntuales de código que el propio proceso de cambio ya resolvió** (un defecto SQL real en `documento.repository.ts`, corregido como parte del mismo trabajo que lo introdujo).

Se identificaron y comprobaron con evidencia directa:
- **2 commits de git** entre la fecha del documento base y hoy (`f804586`, `39b47b9`), con un diff exacto de 36 archivos.
- **1 módulo nuevo completo** (Libro de Referencias), aún sin comprometer a control de versiones.
- **1 hallazgo de estructura nuevo, no crítico**: carpeta de módulo backend vacía y desconectada (`expedientes/`).
- **1 defecto de código en producción, ya corregido** durante el mismo período (bug de SQL en el listado de documentos).
- **0 vulnerabilidades de seguridad nuevas**; **0 secretos expuestos** en ningún archivo revisado.
- **0 contradicciones** entre lo que el documento base afirmaba y lo que el código demuestra — el documento base fue, en la práctica, un trabajo de auditoría de alta fidelidad.

---

## 2. Archivos y áreas revisadas

- `md/FUNCIONALIDADES_DOC360.md` — 1474 líneas, leído en su totalidad.
- `git log --since="2026-08-06" --oneline --reverse` y `git diff --stat f804586~1 HEAD` — 36 archivos, +7054/-77 líneas.
- Backend: `documento.repository.ts`, `documento.service.ts`, `documento.controller.ts`, `documento.schema.ts`, `shared/utils/response.ts`, `shared/types/api.types.ts`, y el módulo completo nuevo `libro-referencias/` (6 archivos, 1204 líneas).
- Frontend: `DashboardPage.tsx`, `DocumentosPage.tsx`, `DocumentosPanel.tsx` (nuevo), `estadoDocumento.ts` (nuevo), `sheet.tsx` (nuevo), `globals.css`, `utils.ts` (helper de descarga autenticada), `LibroReferenciasPage.tsx` (nuevo), `libroReferencias.api.ts` (nuevo).
- Infraestructura: `docker-compose.yml`, `.gitignore`.
- Base de datos: `database/scripts/18-libro-referencias.sql`, `database/scripts/19-libro-referencias-correlativo-reutilizable.sql`.
- Estructura de directorios: `backend/src/modules/*` (16 carpetas), `frontend/src/pages/*` (22 archivos `.tsx`), verificación de `app.ts` y `roles.routes.ts` (`TODOS_MODULOS`).
- `git status --porcelain=v1` — para delimitar exactamente qué archivos del módulo nuevo aún no están comprometidos.

**Explícitamente fuera de alcance de esta revisión** (por instrucción del encargo): `node_modules/`, `dist/`, `build/`, `coverage/`, archivos temporales, backups, logs, código de terceros, y cualquier valor de `.env`/credenciales (solo se reportan hallazgos de forma genérica cuando corresponde).

---

## 3. Diferencias entre la documentación original y el código

| # | Funcionalidad | Documento base decía | Código real dice | Clasificación |
|---|---|---|---|---|
| 1 | Dashboard — "Flujo Documental" | Pipeline visual, solo lectura, sin interacción (§4.3) | Pipeline y gráfico "Por estado" son clicables, abren panel lateral con datos reales | **Funcionalidad agregada** |
| 2 | `GET /documentos` | Filtros: `q, idTipo, idEstado, fechaDesde, fechaHasta` (`CLAUDE.md`, consistente con el doc. base) | Se agregaron `idDependencia, soloAtrasados, proximoAVencer, orden`, más campos calculados por fila y bloque `resumen` | **Funcionalidad agregada** |
| 3 | `documento.repository.ts#findMany()` | No documentado como defectuoso | Tenía un defecto real de SQL (columna tras `FROM`/`JOIN`) — **corregido** en el mismo cambio que lo introdujo | **Corregido durante el propio período auditado** (no es una discrepancia documental, es un defecto de código ya cerrado) |
| 4 | Descarga de archivos | No se documentaba ningún mecanismo de descarga autenticada específico | Se agregó `descargarArchivoAutenticado()` para evitar fallos 401 en enlaces `<a download>` | **Funcionalidad agregada / corrección de UX** |
| 5 | Catálogo de dependencias | 150 filas aproximadas (implícito en el conteo de tablas) | 214 filas tras corrección contra el organigrama oficial (74 renombradas, 64 creadas) | **Dato actualizado** (no una discrepancia de comportamiento, sino de contenido de catálogo) |
| 6 | Módulos backend | 15 módulos (`backend/src/modules/`) | 16 carpetas: 15 con código operativo (incluye el nuevo `libro-referencias/`) + 1 vacía (`expedientes/`) no mencionada antes | **Hallazgo nuevo, no crítico** |
| 7 | Libro de Referencias | No existía | Módulo completo, operativo, no comprometido a git | **Funcionalidad completamente nueva** |
| 8 | Pruebas automatizadas | El documento base no describe ningún test existente (y `CLAUDE.md` lo listaba como "pendiente") | 30 pruebas Vitest+Supertest, exclusivas del módulo nuevo | **Funcionalidad agregada, cobertura parcial** |

**No se encontró ningún caso** de "el documento afirma X y el código hace Y distinto" sobre las funcionalidades ya existentes al 2026-08-06 — la totalidad de las diferencias corresponde a evolución del sistema en el período, no a errores de documentación previa.

---

## 4. Funcionalidades agregadas, corregidas, no encontradas o parciales

### 4.1 Agregadas
- Panel interactivo "Flujo Documental" en el Dashboard (§4.3 del documento actualizado).
- Extensión de `GET /documentos` (filtros y campos calculados) (§4.4 del documento actualizado).
- Descarga autenticada de archivos (helper de utilidad, sin endpoint nuevo).
- Módulo completo **Libro de Referencias**, con eliminación en dos niveles (§4.21 del documento actualizado).
- Primera suite de pruebas automatizadas del proyecto (Vitest + Supertest).

### 4.2 Corregidas (defectos de código detectados y ya resueltos en el período)
- Defecto de sintaxis SQL en `documento.repository.ts#findMany()` (columna agregada tras `FROM`/`JOIN`) — corregido como parte del mismo cambio.
- Corrección de la inconsistencia de etiquetas/umbrales entre el pipeline del Dashboard y el gráfico "Por estado" (documentada como hallazgo activo hasta este período; ahora resuelta mediante `estadoDocumento.ts` como fuente única de verdad).
- Corrección del algoritmo de correlativo de Libro de Referencias (de `MAX()+1` global no reutilizable, script 18, a "primer hueco libre" reutilizable entre vigentes, script 19) — corrección interna al propio desarrollo del módulo nuevo, no un defecto heredado de otro período.

### 4.3 No encontradas / no verificables
Ninguna. Ver §16 del documento actualizado — toda funcionalidad revisada, tanto la del documento base como la nueva, cuenta con evidencia directa de código.

### 4.4 Parciales
- **Libro de Referencias sin comprometer a git**: funcionalmente completo y probado, pero en un estado de control de versiones que lo hace vulnerable a pérdida si no se comitea.
- **Cobertura de pruebas automatizadas**: existe, pero acotada a un solo módulo de 16 — el resto del sistema, incluyendo los flujos más sensibles (correlativos de Memorándum, Firma Simple, visibilidad por servicio), sigue sin pruebas automatizadas.

---

## 5. Inconsistencias entre frontend, backend y base de datos

No se detectó ninguna inconsistencia frontend/backend/BD **nueva** introducida en este período. Se reconfirmaron, sin cambios, las 8 inconsistencias ya consolidadas en el documento base (§8.2) — ninguna fue corregida ni empeorada por los cambios de este período. Adicionalmente:

- El nuevo bloque `resumen` de `GET /documentos` usa el parámetro opcional `extra` agregado a `sendPaginated()` — se verificó que este cambio es **retrocompatible**: todos los demás llamadores de `sendPaginated()` en el sistema (Documentos, Trámites, Búsqueda, Usuarios, Alertas, etc.) siguen funcionando sin modificación, porque el parámetro es opcional y no altera la forma `{data, meta}` cuando no se usa.
- Libro de Referencias no presenta ninguna inconsistencia estructural: su capa `schema→repository→service→controller→routes` sigue exactamente el mismo patrón que `documentos`, y su frontend (`libroReferencias.api.ts`) tipa la respuesta de forma consistente con lo que el backend realmente devuelve (verificado campo por campo contra `mapReferencia()` en `libro-referencias.service.ts`).

---

## 6. Riesgos funcionales

| # | Riesgo | Impacto si se materializa | Severidad |
|---|---|---|---|
| 1 | Módulo Libro de Referencias (backend, frontend, migraciones, pruebas) no comprometido a git | Pérdida total del módulo ante falla del entorno de desarrollo local, sin posibilidad de recuperación | Media-alta (operacional, no de datos ya en producción) |
| 2 | Eliminación definitiva (nivel 2) de Libro de Referencias es irreversible por diseño | Un uso incorrecto o apresurado por parte de un `admin` no puede deshacerse — solo queda el resumen textual en `auditoria` | Media (mitigado por las salvaguardas de confirmación explícita documentadas en §4.21.4 del documento actualizado) |
| 3 | Carpeta `expedientes/` vacía y desconectada | Ninguno funcional directo — es código muerto que puede confundir a un desarrollador futuro que asuma que ese módulo existe | Baja |
| 4 | Cobertura de pruebas automatizadas limitada a 1 de 16 módulos | Cambios futuros en los módulos sin cobertura (especialmente Memorándum/Firma Simple, los de mayor complejidad transaccional) no tienen una red de seguridad automatizada | Media (ya señalada como pendiente general en `CLAUDE.md`, no exclusiva de este período) |

---

## 7. Riesgos de autorización y seguridad

No se identificó ningún riesgo de autorización o seguridad **nuevo** introducido en este período. Se reconfirma que:

- Los 8 endpoints de Libro de Referencias están correctamente cubiertos por `requireAuth` + `requireModule('libro-referencias')`, con `requireRole('admin')` adicional en las 3 rutas sensibles (`/eliminados`, `DELETE /:id`, `DELETE /:id/permanent`) — verificado línea por línea en `libro-referencias.routes.ts`.
- La eliminación definitiva valida el guard `condicion='ELIMINADO'` dentro del propio `DELETE` SQL (no en un paso previo separado), cerrando la ventana de condición de carrera que existiría con un patrón "verificar-luego-borrar" en dos pasos.
- Los nuevos filtros de `GET /documentos` (`idDependencia`, `soloAtrasados`, `proximoAVencer`) heredan el mismo filtro `EXISTS` de visibilidad por servicio ya aplicado al resto de la consulta — no se agregó una vía de lectura que lo eluda.
- Se confirma explícitamente que **Libro de Referencias no aplica visibilidad por servicio** — a diferencia del resto del sistema. Esto se documenta como decisión de diseño en el documento actualizado (§3, §4.21), no como una brecha, porque el propio dominio de datos (bitácora única de Oficina de Partes) no tiene un concepto de "servicio destinatario" que filtrar. Se señala aquí para que quede trazable como una desviación intencional del patrón transversal, sujeta a confirmación explícita del área de negocio si en el futuro se esperara lo contrario.
- No se encontró ningún secreto, contraseña, token ni cadena de conexión expuesta en ninguno de los archivos revisados (se verificó específicamente que ningún archivo nuevo escribe valores de `.env` en logs, respuestas de API o el propio código fuente).

---

## 8. Código aparentemente obsoleto o desconectado

| Elemento | Evidencia | Estado |
|---|---|---|
| `backend/src/modules/expedientes/` | Carpeta con 0 archivos (`.`/`..` únicamente), fechada mayo 2026; sin registro en `app.ts`; sin referencia en `frontend/src` | Obsoleto/desconectado — nunca se pobló con código, no representa riesgo por no ser alcanzable, pero es cruft de estructura |
| `memo_correlativo` (tabla SQL) | Ya documentado en el documento base (§4.11, §5.3) — vestigial, el correlativo real se calcula por `MAX()` | Sin cambios en este período — se reconfirma vigente |
| `MemorandumModal.tsx` / flujo Firma.gob desde creación de Memorándum | Ya documentado en el documento base (§4.13) — implementado pero no alcanzable desde la pantalla de creación | Sin cambios en este período — se reconfirma vigente |

No se detectó ningún elemento adicional de código obsoleto/desconectado más allá de los ya señalados en el documento base y el nuevo hallazgo de `expedientes/`.

---

## 9. Recomendaciones priorizadas

### Crítica
- Ninguna. No se detectó ningún riesgo de integridad de datos, seguridad activa explotable, o pérdida de datos en producción durante esta auditoría.

### Alta
1. **Comprometer a git el módulo Libro de Referencias** (backend, frontend, migraciones, pruebas) a la brevedad — es la única funcionalidad completa del sistema que hoy existe únicamente en un árbol de trabajo local.

### Media
2. **Confirmar explícitamente con el área de negocio (Oficina de Partes)** si la ausencia de visibilidad por servicio en Libro de Referencias es la conducta esperada (todo usuario con el módulo ve todos los registros, sin acotar por dependencia) — hoy es una decisión de diseño razonada pero no fue objeto de una confirmación de negocio documentada dentro del alcance de esta auditoría.
3. **Extender la suite de pruebas automatizadas** (ya validada en Libro de Referencias) a los módulos de mayor complejidad transaccional — Memorándum, Firma Simple, visibilidad por servicio — antes de que crezcan más sin red de seguridad automatizada.
4. **Retirar o documentar explícitamente como reservada** la carpeta vacía `backend/src/modules/expedientes/`.

### Baja
5. **Unificar el patrón de modal/panel** del frontend (evaluar migrar los modales "hechos a mano" al nuevo primitivo `Sheet`/Radix introducido con el panel del Dashboard), dado que ahora conviven dos patrones distintos.
6. Las 13 oportunidades de mejora del documento base (§11) permanecen vigentes y no se repiten aquí — ver ese documento para el detalle completo.

---

## 10. Matriz de Trazabilidad

| Funcionalidad | Estado | Evidencia | Observación |
|---|---|---|---|
| Dashboard — panel interactivo Flujo Documental | ✅ Verificada, operativa | `DashboardPage.tsx`, `DocumentosPanel.tsx`, `estadoDocumento.ts` | Sin regresión sobre lo documentado en el doc. base |
| `GET /documentos` extendido | ✅ Verificada, operativa | `documento.repository.ts`, `documento.schema.ts` | Defecto SQL detectado y corregido en el mismo cambio |
| Descarga autenticada de archivos | ✅ Verificada, operativa | `frontend/src/lib/utils.ts` | Corrige un 401 real en enlaces de descarga previos |
| Corrección de catálogo de dependencias | ✅ Verificada (dato, no código) | 150→214 filas en `dependencia`, vía endpoints existentes | No requirió cambios de código, solo datos |
| Libro de Referencias — CRUD + correlativo | ✅ Verificada, operativa | 6 archivos backend + migraciones 18/19 + `LibroReferenciasPage.tsx` | Sin comprometer a git (ver riesgo #1) |
| Libro de Referencias — eliminación lógica | ✅ Verificada, operativa | `libro-referencias.repository.ts#eliminarLogico` | Libera correlativo de inmediato |
| Libro de Referencias — eliminación definitiva | ✅ Verificada, operativa | `libro-referencias.repository.ts#eliminarDefinitivo` | Guard de carrera dentro del propio DELETE; evidencia preservada en `auditoria` |
| Pruebas automatizadas (Libro de Referencias) | ✅ Verificada, existente | `libro-referencias.test.ts` (531 líneas, 30 pruebas) | Único módulo cubierto de 16 |
| Carpeta `expedientes/` vacía | ⚠️ Hallazgo confirmado | `ls -la`, ausencia en `app.ts` y en frontend | No crítico — recomendación baja/media |
| 8 brechas de seguridad del documento base | ⚪ Sin cambios | Re-verificadas, ninguna corregida ni empeorada | Ver documento base §8.2 |
| 25 hallazgos técnicos del documento base | ⚪ Sin cambios (salvo el ítem de SQL ya corregido, ver #30 del doc. actualizado) | Re-verificados uno por uno contra el diff del período | — |

---

*Fin del informe. Ningún archivo de código fuente, configuración, base de datos o infraestructura fue modificado durante la elaboración de esta auditoría. El documento original `md/FUNCIONALIDADES_DOC360.md` permanece intacto.*
