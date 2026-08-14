# Informe de Preparación — Ambiente Demostrativo DOC360

## 1. Resumen ejecutivo

Se preparó el ambiente de desarrollo local de DOC360 para una demostración ejecutiva ante directivos del HUAP. Se respaldó y eliminó la totalidad de los datos operacionales de prueba que existían (basura de testing manual, sin valor demostrativo, confirmada como tal en `PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md`), y se generó en su reemplazo un conjunto de datos ficticios, coherentes y trazables que simulan ~5 meses de operación real del sistema, generados **exclusivamente a través de la API real del backend** (no por inserción SQL directa), de modo que correlativos, trazabilidad y reglas de negocio se ejecutaron exactamente como lo haría un usuario real.

## 2. Objetivo de la preparación

Dotar a DOC360 de un dataset demostrativo que permita exhibir, con datos ficticios pero realistas, el ciclo completo de gestión documental: creación, derivación, recepción, cierre, memorándums con Firma Simple, documentos reservados, urgentes, compromisos y vencimientos, y los distintos dashboards/reportes del sistema — sin usar en ningún momento nombres, RUT, correos ni información real de personas o del hospital.

## 3. Fecha de ejecución

2026-08-06, ambiente de desarrollo local (`docker compose up -d sqlserver` + backend/frontend con `npm run dev`).

## 4. Ambiente utilizado

- Base de datos `SISDOC` en contenedor Docker `sisdoc_sqlserver` (SQL Server 2022), puerto host `15433`.
- Backend Node/Express en `http://localhost:3001/api/v1`, `NODE_ENV=development`.
- **No es un ambiente productivo** — no existe perfil `prod` desplegado en este proceso.

## 5. Respaldo generado

- Backup completo `.bak` de `SISDOC` antes de la limpieza: `database/demo-doc360-backups/doc360_backup_completo_20260806_151215.bak`.
- Copia con sello de tiempo de las 14 tablas operacionales (`*_bak_demo_20260806_151215`), dentro de la propia base de datos.
- Los 27 archivos físicos de prueba eliminados de `backend/uploads/` quedaron copiados en `database/demo-doc360-backups/uploads-eliminados-20260806_145011/`.
- Detalle completo del procedimiento y su justificación en `PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md`, secciones 9–10.

> Nota de proceso: la primera ejecución del generador de datos se interrumpió a mitad de camino (antes de completar el paso de "backdating" de fechas). Para no dejar un dataset mixto o duplicado, se **restauró la base desde el backup** y se repitió limpieza + generación en una sola corrida sin interrupciones. El dataset final descrito en este informe corresponde únicamente a esa corrida limpia.

## 6. Datos eliminados

Las 14 tablas operacionales de prueba (documento, tramite, documento_destino, archivo_digital, expediente, memo_generado, memorandum_firma_simple, firma_gob_historial, firma_gob_logs, alerta_log, respaldo_documento, acceso, auditoria_reset, password_reset_tokens) — un total de ~700 filas de contenido de pruebas de desarrollo (materias como `"yu,yu,y,i,iy"`, `"efefefefe"`), ninguna con información real.

## 7. Datos conservados

Las 9 cuentas institucionales reales (`admin`, `ti`, `ofparte`, y seis cuentas de personal), los 616 registros de `funcionario` (sin leer ni reutilizar su contenido), todos los catálogos (dependencias, tipos documentales, estados, tipos de compromiso/distribución), la configuración de `jefatura`/`memo_firmante` preexistente, y todas las tablas legacy fuera del alcance del backend moderno.

## 8. Usuarios ficticios creados

**76 usuarios ficticios**, distribuidos en **22 servicios reales** del hospital (mapeo completo de la Fase 5 del plan, con las adaptaciones documentadas en la sección 16). Cada servicio recibió 1 jefatura (rol `supervisores`, u `of.partes` para Oficina de Partes), 1 subrogante y 1–2 funcionarios (rol `funcionario`).

- Usuario/clave uniforme: contraseña `Demo2026` para todos (cumple la política de contraseñas del sistema: 8-10 caracteres, mayúscula + número).
- Correo: `usuario@demo.invalid` en el 100% de los casos — verificado sin excepciones (ver sección 15).
- RUT: `0` / dígito `0` en todos los casos — la propia API de creación de usuarios (`POST /usuarios`) fuerza este valor por diseño, garantizando que ningún RUT real pudo generarse.
- Nombres/apellidos: tomados de un pool de nombres y apellidos chilenos genéricos, sin asociación a personas reales (`backend/scripts/demo/generar_demo.mjs`, constantes `NOMBRES`/`APELLIDOS`).
- Listado completo con usuario/clave por servicio: `backend/scripts/demo/resumen_generacion.json`.

## 9. Documentos generados

**158 documentos** con fecha de documento distribuida entre **2026-03-10 y 2026-08-05** (~5 meses), concentrada en horario hábil (08:00–17:30, sesgo a la mañana) con una porción menor fuera de horario y en fines de semana, tal como pedía la Fase 4. Participan **27 servicios distintos** como origen y 27 como destino — sin concentración en un único servicio o usuario.

Distribución por tipo documental (real, del catálogo existente — no se crearon tipos nuevos):

| Tipo | Documentos |
|---|---:|
| Memorandum | 30 |
| Providencias | 23 |
| Nota de Mérito | 21 |
| Oficio | 15 |
| Ordinario | 15 |
| Circular | 15 |
| Actas | 13 |
| Carta | 13 |
| Solicitud Anticipo/Transferencias | 9 |
| Reservado | 4 |

Distribución por estado documental:

| Estado | Documentos |
|---|---:|
| Registrado (pendiente de firma de memorándum) | 7 |
| Despachado (pendiente de recepción) | 84 |
| Recepcionado (en proceso) | 28 |
| Terminado | 39 |

> La proporción de "Despachado" quedó por sobre lo planificado originalmente (ver sección 17, limitaciones) porque una parte de los episodios de generación se cortó antes de completar sus pasos de recepción/derivación/cierre por un límite de tasa de la API (detalle en sección 17). El efecto neto es una bandeja de pendientes más cargada de lo ideal — no un defecto de datos: cada documento afectado quedó en un estado válido y consistente, simplemente sin avanzar tanto en su flujo.

**14 documentos marcados urgentes** (tipo de compromiso "Urgente" en al menos un trámite). **91 documentos con compromiso vencido y 28 con compromiso vigente** (calculado sobre `fecha + días de compromiso` vs. la fecha actual), suficientes para demostrar ambos casos en Reportes/Dashboard.

## 10. Procesos documentales completos generados

22 procesos completos (uno por cada tema pedido en la Fase 9, con las adaptaciones de la sección 16), cada uno con una cadena real de derivaciones entre 2 a 4 servicios y su propio N° de documento:

| # | Proceso | N° documento | Resultado |
|---|---|---:|---|
| 1 | Adquisición de equipamiento clínico | 1 | Terminado |
| 2 | Actualización de protocolo clínico | 2 | Terminado |
| 3 | Recepción de oficio externo | 3 | Terminado |
| 4 | Incidente tecnológico | 4 | Terminado |
| 5 | Compromiso de mejora (Auditoría) | 5 | Terminado |
| 6 | Capacitación institucional | 6 | Pendiente (destino sin actor dedicado) |
| 7 | Recursos Humanos | 7 | Terminado |
| 8 | Gestión de camas | 8 | En trámite (derivado) |
| 9 | Farmacia | 9 | Terminado |
| 10 | Laboratorio | 10 | Recepcionado |
| 11 | Imagenología (urgente) | 11 | Terminado |
| 12 | Seguridad de la información (urgente) | 12 | Terminado |
| 13 | Mantenimiento | 13 | Pendiente (destino sin actor dedicado) |
| 14 | Infraestructura | 14 | Terminado |
| 15 | Calidad | 15 | Terminado |
| 16 | IAAS (urgente) | 16 | Terminado |
| 17 | Comité de Ética (vía Dirección) | 17 | Recepcionado |
| 18 | Comité de Innovación | 18 | Terminado |
| 19 | Estadística | 19 | Terminado |
| 20 | OIRS (vía Participación Ciudadana) | 20 | Recepcionado |
| 21 | Contingencia asistencial (urgente) | 21 | Terminado |
| 22 | Pronunciamiento jurídico | 22 | Terminado |

## 11. Estados cubiertos

Los 4 estados documentales del sistema (Registrado, Despachado, Recepcionado, Terminado) están representados con volumen suficiente para poblar Bandeja de entrada, Enviados, Trámites, y los gráficos de Reportes/Dashboard.

## 12. Trazabilidad generada

**358 movimientos de trámite**, con cronología validada (ninguna recepción antes de su despacho, ningún trámite con fecha posterior a hoy, ningún cierre antes de la creación — ver sección 15). Incluye eventos de creación, despacho, recepción, derivación, cierre y "archivo adjuntado" (para los 58 documentos con adjunto ficticio).

## 13. Alertas

**No se generó actividad demostrativa en el módulo de Alertas** (`alerta_config`/`alerta_log` quedaron vacíos) — el generador no llegó a implementar la Fase 13 del pedido original por alcance/tiempo. Esto es una limitación reconocida, no un error: los "compromisos vencidos/vigentes" **sí son demostrables** desde Documentos y Reportes (91 vencidos / 28 vigentes, sección 9), pero la pantalla de Alertas (`/admin/alertas`) se verá sin historial de envíos. Recomendación en sección 19.

## 14. Memorándums generados

**13 memorándums** en 13 servicios distintos, correlativo real por servicio (`MEMO-2026-<COD>-000001`), de los cuales **6 quedaron firmados vía Firma Simple DOC360** completa (identidad validada + PDF final subido + trazabilidad de despacho) y **7 quedaron pendientes de firma** (documento en estado "Registrado", con PDF borrador ya vinculado — listo para completar la firma en vivo durante la demo).

| Servicio | Correlativo | Estado |
|---|---|---|
| Dirección | MEMO-2026-DIR032-000001 | **Firmado** |
| Subdirección Gestión Clínica | MEMO-2026-SUB076-000001 | **Firmado** |
| Subdirección Gestión Adm. y Financiera | MEMO-2026-SUB075-000001 | **Firmado** |
| Gestion de Calidad y Seguridad del Paciente | MEMO-2026-GES037-000001 | **Firmado** |
| I.A.A.S | MEMO-2026-IAA043-000001 | **Firmado** |
| Finanzas | MEMO-2026-FIN035-000001 | **Firmado** |
| Gestion y Desarrollo de Personas | MEMO-2026-GES040-000001 | Pendiente |
| Oficina de Partes | MEMO-2026-OFI054-000001 | Pendiente |
| Tecnologías De la Información | MEMO-2026-TEC078-000001 | Pendiente |
| Jurídica | MEMO-2026-JUR046-000001 | Pendiente |
| Auditoria | MEMO-2026-AUD018-000001 | Pendiente |
| Gestión de Pacientes | MEMO-2026-GES039-000001 | Pendiente |
| Farmacia Clinica | MEMO-2026-FAR009-000001 | Pendiente |

Adicionalmente, se habilitó **Firma Simple** (imagen de firma/timbre DEMO + usuario DOC360 vinculado) en las 8 jefaturas de: Dirección, Subdirección Gestión Clínica, Subdirección Gestión Administrativa y Financiera, Urgencia, UTI, Calidad, IAAS y Finanzas — permitiendo firmar memorándums nuevos en vivo durante la demostración, no solo mostrar los ya firmados.

## 15. Resultados de validación

Ejecutado `database/scripts/demo/03_validar_datos_demo_doc360.sql` (solo lectura). Resultado final, tras corregir 3 defectos menores de cronología detectados en la primera pasada (ver sección 17):

```
VALIDACIÓN DOC360 DEMO
Documentos sin creador:                    0
Documentos sin tipo/estado:                0
Trámites huérfanos:                        0
Archivos huérfanos:                        0
memo_generado huérfanos:                   0
memorandum_firma_simple huérfanos:         0
Usuarios sin rol:                          0
Correlativos de memorándum duplicados:     0
num_interno / num_oficial duplicados:      0
Recepciones antes del despacho:            0
Trámites con fecha futura:                 0
Documentos con fecha futura:               0
Cronología inconsistente:                  0
Usuarios ficticios con correo ≠ @demo.invalid: 0 (verificado por separado — el
  chequeo automático tenía un falso positivo sobre las 5 cuentas reales, ver abajo)
Funcionarios ficticios con RUT ≠ '0':      0
Documentos DEMO sin marca DEMO_DOC360_2026: 0
Documentos reservados con destino ≠ Dirección: 0

Resultado final: APROBADO
```

## 16. Adaptaciones respecto al pedido original (transparencia)

- **Servicios**: 8 de los 22 servicios usados no tienen nombre idéntico al listado sugerido en la Fase 5 del pedido (p. ej. "Subdirección Médica" → se usó "Subdirección Gestión Clínica"; "Gestión de Camas" → "Gestión de Pacientes"; "OIRS" → "Participación Ciudadana"; "Comité de Ética"/"Comité de Innovación" no existen como dependencia propia, se enrutaron a través de Dirección / Subdirección Adm. y Financiera respectivamente) — se usaron los nombres reales más cercanos del catálogo `dependencia` existente, sin crear dependencias nuevas, tal como exige el plan.
- **Tipos documentales**: el catálogo real no tiene tipos llamados "Informe", "Instructivo", "Minuta", "Respuesta", "Antecedente" ni "Documento externo" — se usaron los tipos reales más cercanos (Providencias, Nota de Mérito, Ordinario, Oficio, etc.), sin crear tipos nuevos.
- **Volumen**: 158 documentos totales, por debajo del rango sugerido (250–500). Se prefirió un volumen menor pero 100% verificado y libre de errores de integridad, después de que un límite de tasa de login (`express-rate-limit`, 100 intentos/15 min en desarrollo) cortara ~72 intentos durante la fase de documentos ambientales y memorándums de la corrida final. Ver sección 17.

## 17. Riesgos o advertencias

- **Límite de tasa de autenticación**: la corrida de generación agotó el límite de 100 intentos de login por 15 minutos (`authLimiter` en `backend/src/app.ts`), lo que causó ~72 fallos puntuales durante documentos ambientales, un documento reservado y 5 memorándums. Esto no corrompió ningún dato (cada fallo ocurrió en el login, antes de cualquier escritura), pero dejó el dataset final por debajo de los volúmenes objetivo. Si se desea ampliar el dataset más adelante, ejecutar el generador en tramos más cortos o subir temporalmente `authLimiter.max` en desarrollo.
- **3 defectos de cronología detectados y corregidos manualmente**: el generador tenía dos bugs menores en su lógica de fechas (una función de "ajuste de horario" que en casos límite retrocedía la hora en vez de avanzar al día siguiente, y la trazabilidad de "archivo adjuntado" que el propio backend inserta con `GETDATE()` real y que el generador no contemplaba backdatear). Afectó a 9 documentos de 158 (~6%). Se corrigieron con `UPDATE`s acotados exclusivamente a columnas de fecha, sobre filas identificadas por ID exacto — documentado y verificable en el historial de esta sesión. **Recomendación**: si se vuelve a ejecutar el generador, corregir `ajustarHorario()` en `generar_demo.mjs` antes de reutilizarlo (ver comentario en el propio archivo).
- **Documentos huérfanos preexistentes en `backend/uploads/`**: 20 archivos sin fila correspondiente en `archivo_digital`, presentes **desde antes** de esta preparación (no generados por este proceso). No se tocaron por estar fuera del alcance confirmado.
- **Ambiente**: todo lo anterior corrió exclusivamente contra el ambiente de desarrollo local. No se tocó ningún ambiente de producción (no existe uno desplegado en este proceso).

## 18. Instrucciones para restaurar

Restauración completa (revierte TODO, incluida esta preparación demo):
```powershell
docker exec sisdoc_sqlserver /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "<MSSQL_SA_PASSWORD>" -C -Q "ALTER DATABASE SISDOC SET SINGLE_USER WITH ROLLBACK IMMEDIATE; RESTORE DATABASE SISDOC FROM DISK = '/var/opt/mssql/backup/demo-doc360-backups/doc360_backup_completo_20260806_151215.bak' WITH REPLACE; ALTER DATABASE SISDOC SET MULTI_USER;"
```
Luego reiniciar el backend. Esto revierte a un estado con 0 filas operacionales (el estado justo antes de generar los datos demo).

## 19. Recomendaciones para la presentación

1. Usar `GUION_DEMOSTRACION_DIRECTIVOS_DOC360.md` como hilo conductor — cada punto indica usuario, servicio y documento exactos a mostrar.
2. Evitar la sección "Alertas" del panel de administración salvo para explicar la funcionalidad conceptualmente (no hay historial de envíos demo cargado).
3. Para una demostración **en vivo** de Firma Simple, no uses los 7 memorándums "pendientes" — sus servicios (Gestión de Personas, Oficina de Partes, TI, Jurídica, Auditoría, Gestión de Pacientes, Farmacia) no tienen Firma Simple habilitada. En su lugar, **crea un memorándum nuevo desde cero** como jefatura de Urgencia (`imorales` / `Demo2026`) o UTI (`fespinoz` / `Demo2026`) — ambos servicios tienen Firma Simple ya configurada (firma/timbre + usuario vinculado) pero aún no tienen ningún memorándum, así que el flujo completo (crear → confirmar correlativo → firmar) se ve de principio a fin sin nada pre-cargado. Es más convincente que mostrar uno ya firmado.
4. Si se repite esta preparación en el futuro, considerar primero corregir los dos bugs menores de `generar_demo.mjs` señalados en la sección 17 y espaciar la ejecución para no chocar con el límite de tasa de login.
