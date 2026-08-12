# Plan de reversa — Migración DOC360 SQL Server → PostgreSQL

**Fecha:** 2026-08-12
**Precondición:** este plan debe estar redactado, revisado y **ensayado en el ambiente piloto** (Fase 6 de `PLAN_MIGRACION_POSTGRESQL_DOC360.md`) antes de que la Fase 7 (puesta en producción) pueda autorizarse. Un plan de reversa no ensayado no es un plan de reversa, es una suposición.

---

## 1. Principio rector

DOC360 gestiona documentos oficiales de una institución de salud (HUAP). Ante cualquier duda razonable sobre la integridad de los datos tras el corte a PostgreSQL, **la decisión por defecto es revertir**, no "esperar a ver si se estabiliza". El costo de una reversa bien ejecutada es bajo (horas); el costo de un correlativo duplicado o un documento corrupto en un sistema institucional es alto y de difícil reparación posterior.

---

## 2. Tiempo máximo para decidir la reversa

- **Durante la ventana de mantenimiento (Fase 7, previo a reabrir a usuarios):** si las pruebas de humo (paso 7 de la Fase 7) no pasan al 100% dentro de **60 minutos** desde el cambio de conexión, se declara reversa inmediata sin excepciones — no se reabre el sistema a usuarios en un estado dudoso.
- **Post-apertura, dentro de las primeras 24 horas:** cualquier criterio objetivo de la sección 3 detectado dispara una decisión de reversa en un plazo máximo de **2 horas** desde su detección (tiempo de decisión, no de ejecución — la ejecución de la reversa técnica se estima en la sección 5).
- **Entre 24 horas y 5 días hábiles (periodo de monitoreo reforzado, Fase 7 paso 9):** cualquier hallazgo de integridad de datos (no de rendimiento o UX menor) dispara evaluación inmediata por el comité de responsables (sección 6), con decisión en un plazo máximo de **4 horas**.
- **Después de 5 días hábiles de monitoreo reforzado sin incidentes de integridad:** se considera la migración estabilizada; a partir de este punto, una reversa completa deja de ser la respuesta por defecto ante un problema — se evalúa caso a caso, priorizando corrección directa sobre PostgreSQL antes que reversa (dado que revertir en este punto implicaría perder o reconciliar manualmente los datos generados en PostgreSQL desde el corte).

---

## 3. Criterios objetivos que disparan una reversa

No son apreciaciones subjetivas — cualquiera de los siguientes, por sí solo, es suficiente:

1. **Correlativo duplicado detectado** en `memo_generado.correlativo` o en `num_interno`/`num_oficial` de `documento` — viola la regla de negocio no negociable de `CLAUDE.md`. Criterio de mayor severidad de todos.
2. **Discrepancia de conteo de registros** entre lo esperado (línea base de la Fase 4/7) y lo observado en PostgreSQL, no explicable por actividad legítima post-corte.
3. **Fallo de autenticación generalizado** (login no funciona para una proporción significativa de usuarios) no resuelto en el plazo de decisión de la sección 2.
4. **Pérdida o corrupción de trazabilidad**: un documento pierde su historial de trámites o el orden cronológico de eventos se altera respecto al esperado.
5. **Error 500 sostenido** (no puntual) en cualquiera de los flujos críticos: creación de documento, derivación, despacho, cierre, confirmación de memorándum, Firma Simple.
6. **Corrupción de texto en español** (tildes/ñ ilegibles) detectada en datos migrados — indicaría un problema de collation/encoding no resuelto en la Fase 1/4, ver hallazgo M2 del informe principal.
7. **Fecha/hora incorrecta** en registros nuevos o migrados que afecte el orden de trazabilidad o el cálculo de vigencia/expiración (jefaturas, tokens, códigos de Firma Simple).
8. **Pérdida de acceso a archivos adjuntos** (referencias `archivo_digital.ruta` no resuelven al archivo físico esperado) para una proporción no trivial de documentos.
9. **Violación de visibilidad por servicio**: un usuario ve documentos/trámites de un servicio que no es el suyo (regresión de seguridad, máxima severidad junto con el criterio 1).

Criterios que **no** disparan reversa automática, pero sí investigación y posible corrección puntual sobre PostgreSQL (no ameritan volver completo a SQL Server):
- Diferencias de ranking en resultados de búsqueda (esperado por el rediseño de FTS, documentado en `PLAN_PRUEBAS_MIGRACION_POSTGRESQL_DOC360.md` sección 4).
- Degradación de rendimiento aislada en un endpoint no crítico, sin impacto funcional.
- Errores de configuración de infraestructura (ej. healthcheck mal ajustado) que no afectan datos.

---

## 4. Restauración de la conexión anterior

**Precondición:** SQL Server (contenedor `sisdoc_sqlserver`, volumen `sisdoc_sqlserver_data`) **no se destruye ni se detiene** durante la Fase 7 — permanece disponible en paralelo (apagado o en modo solo lectura, a decidir en la Fase 7) durante todo el periodo de monitoreo reforzado (mínimo 5 días hábiles), precisamente para que este paso sea instantáneo.

1. Revertir `docker-compose.yml`/`docker-compose.preprod.yml`: `depends_on` de `backend` vuelve a apuntar a `sqlserver`, variables `DB_*` vuelven a la configuración SQL Server (`DB_SERVER=sqlserver`, puerto, credenciales `doc360_app`).
2. Redesplegar el servicio `backend` con esa configuración — el código de aplicación de la Fase 2 debe soportar ambos motores durante el periodo de coexistencia (recomendado explícitamente en la Fase 3 del plan de migración: no eliminar el soporte a `mssql` hasta que el periodo de monitoreo reforzado concluya sin incidentes).
3. Poner el backend en modo mantenimiento (mismo mecanismo del paso 3 de la Fase 7) mientras se ejecuta la restauración de datos del punto siguiente.

---

## 5. Recuperación de datos

**Caso A — reversa dentro de la ventana de mantenimiento (antes de reabrir a usuarios, sección 2 primer punto):** no hay datos nuevos que recuperar — SQL Server no recibió escrituras durante el corte (estaba en modo mantenimiento). Revertir la conexión (sección 4) es suficiente. **Tiempo estimado: 15-30 minutos.**

**Caso B — reversa dentro de las primeras 24 horas o durante el monitoreo reforzado (sección 2, puntos 2-3):** SQL Server no recibió las escrituras generadas en PostgreSQL durante el periodo en que estuvo activo. Pasos:
1. Extraer de PostgreSQL únicamente las filas creadas/modificadas desde el momento del corte (filtrar por `fecha_sistema`/`fecha_creacion` posterior al timestamp de corte, registrado explícitamente en la Fase 7).
2. Validar manualmente cada documento/memorándum/trámite creado en ese periodo — dado que se espera un volumen bajo (ventana corta, sistema institucional no de altísimo tráfico), la validación manual fila por fila es factible y preferible a un script automatizado no probado bajo presión.
3. Re-insertar esas filas en SQL Server **respetando la asignación de correlativos que corresponda según las reglas de SQL Server** (recalculando `MAX()+1` bajo `TABLOCKX`/`HOLDLOCK` como siempre ha funcionado) — **no** copiar literalmente los correlativos generados en PostgreSQL si hubo alguna reasignación por el rediseño del mecanismo (validar caso por caso).
4. Confirmar con los usuarios que crearon esos documentos que el contenido re-insertado es correcto antes de cerrar el incidente.

**Caso C — reversa después del periodo de monitoreo reforzado (más de 5 días hábiles, sección 2 último punto):** este escenario debe evitarse por diseño (es la razón de ser del periodo de monitoreo reforzado y de los criterios de la sección 3). Si ocurriera de todas formas, el volumen de datos a reconciliar manualmente sería alto — en ese caso, la respuesta preferida **no es una reversa completa** sino una corrección dirigida sobre PostgreSQL (con el mismo rigor de respaldo/validación de cualquier cambio en producción), reservando la reversa completa solo si la corrección dirigida resulta inviable.

---

## 6. Comunicación del incidente

1. **Responsable de decisión:** el comité formado por DBA/backend dual, Product Owner/referente HUAP, y DevOps (los mismos roles definidos en la Fase 0 del plan de migración) — cualquiera de los tres puede *proponer* una reversa ante los criterios de la sección 3, pero la decisión final requiere acuerdo de al menos 2 de los 3.
2. **Comunicación interna inmediata:** al declararse una reversa, notificar de inmediato al equipo técnico completo y al referente HUAP, indicando: criterio(s) de la sección 3 que la disparó, hora de inicio, tiempo estimado de resolución (según el caso A/B/C de la sección 5).
3. **Comunicación a usuarios del sistema:** mensaje claro (banner en la aplicación o comunicación institucional según el canal ya usado por HUAP para mantenimientos) indicando que el sistema vuelve temporalmente al estado anterior, sin necesidad de detallar causas técnicas a usuarios finales.
4. **Registro del incidente:** documentar en un post-mortem breve (no necesita ser extenso, pero sí completo): qué disparó la reversa, cuándo se detectó, cuándo se decidió, cuándo se completó la restauración, qué datos (si los hubo) requirieron reconciliación manual, y qué se corregirá antes de reintentar la migración.
5. **Reintento:** no se reprograma una nueva ventana de corte hasta que la causa raíz de la reversa esté corregida y **vuelta a validar en el ambiente piloto** (Fase 6 completa, no un subconjunto) — la reversa no invalida el trabajo de las fases anteriores, pero sí exige repetir la validación del punto específico que falló.

---

## 7. Validación posterior a la reversa

- [ ] Conteo de registros en SQL Server post-reversa coincide con el esperado (línea base + datos reconciliados del caso B, si aplica).
- [ ] Pruebas de humo (mismo subconjunto del paso 7 de la Fase 7) ejecutadas exitosamente contra SQL Server restaurado.
- [ ] Ningún correlativo duplicado ni hueco no explicado en `memo_generado`/`documento` tras la reconciliación.
- [ ] Usuarios afectados (caso B) confirman que sus documentos/memorándums están correctos.
- [ ] Sistema reabierto a usuarios con el banner de mantenimiento removido.
- [ ] Post-mortem del incidente completado y compartido con todos los responsables antes de cerrar el ciclo.

---

## 8. Nota final

Este plan de reversa depende críticamente de una condición operacional simple pero fácil de omitir bajo presión: **no destruir ni desmontar el contenedor/volumen de SQL Server hasta que el periodo de monitoreo reforzado de la Fase 7 concluya sin incidentes.** Cualquier decisión de liberar esos recursos antes de ese punto debe tratarse como una decisión explícita y documentada, no como limpieza rutinaria de infraestructura.
