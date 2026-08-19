# Informe de Limpieza Documental — DOC360

## 1. Fecha y hora

Ejecutado el 2026-08-19, entre 16:19 y 20:29 (hora local del servidor, UTC-4 aprox.).

## 2. Ambiente intervenido

Desarrollo local. Contenedor `sisdoc_sqlserver` (SQL Server 2022), base de datos `SISDOC`, puerto host `15433`. No existe ambiente de producción desplegado — confirmado antes de iniciar (único contenedor `sisdoc*` activo).

## 3. Base de datos utilizada

`SISDOC`, verificada contra el esquema real (no por nombres supuestos): 92 tablas inventariadas, incluidas 4 que viven en el schema `sisdoc` en vez de `dbo` (`auditoria`, `firma_gob_logs`, `libro_referencia`, `memorandum_firma_simple`) — detalle confirmado por consulta directa a `sys.schemas`/`sys.tables`, no asumido.

## 4. Respaldo generado y su ubicación

- **Backup completo de la BD** (previo a cualquier cambio):
  `database/backups-limpieza-documental/SISDOC_pre_limpieza_documental_20260819_161922.bak` (30.3 MB)
  Verificado con `RESTORE VERIFYONLY` → `"The backup set on file 1 is valid."`
- **Respaldo de archivos físicos** (previo a cualquier borrado):
  `database/backups-limpieza-documental/uploads_pre_limpieza_20260819_161922/` (83 archivos, 9.1 MB — incluye tanto los 63 archivos que se iban a eliminar como los 20 huérfanos preexistentes, por seguridad).

## 5. Método de restauración

Restauración completa de la BD (revierte toda la limpieza):
```powershell
docker exec sisdoc_sqlserver /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "<MSSQL_SA_PASSWORD>" -C -Q "ALTER DATABASE SISDOC SET SINGLE_USER WITH ROLLBACK IMMEDIATE; RESTORE DATABASE SISDOC FROM DISK = '/var/opt/mssql/backup/backups-limpieza-documental/SISDOC_pre_limpieza_documental_20260819_161922.bak' WITH REPLACE; ALTER DATABASE SISDOC SET MULTI_USER;"
```
Restauración de archivos físicos:
```powershell
Copy-Item "database\backups-limpieza-documental\uploads_pre_limpieza_20260819_161922\*" "backend\uploads\" -Force
```

## 6. Tablas analizadas

92 tablas de la base `SISDOC`, clasificadas en 5 grupos tras auditar backend (`backend/src/modules/*`), esquema real (`INFORMATION_SCHEMA`, `sys.foreign_keys`, `sys.indexes`) y precedente documentado (`md/PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md` del 2026-08-06, revalidado contra el estado actual — se detectó y sumó al análisis el módulo nuevo `libro_referencia`, sin commitear, que ese precedente no cubría).

## 7. Tablas limpiadas (43 tablas — 73.849 filas eliminadas en total)

### Grupo D — operacional documental (809 filas)

| Tabla | Filas eliminadas |
|---|---:|
| `sisdoc.firma_gob_logs` | 2 |
| `dbo.firma_gob_historial` | 0 |
| `sisdoc.memorandum_firma_simple` | 8 |
| `dbo.memo_generado` | 15 |
| `dbo.documento_destino` | 162 |
| `dbo.tramite` | 371 |
| `dbo.respaldo_documento` | 0 (+1 generado y limpiado durante la validación post, ver sección 10) |
| `dbo.archivo_digital` | 63 |
| `dbo.expediente` | 0 |
| `dbo.descriptor_documento` | 0 |
| `dbo.documento` | 161 |
| `dbo.alerta_log` | 27 |

### Grupo E — legado huérfano + respaldos previos (73.040 filas, incluido por decisión explícita del usuario tras confirmarse ambigüedad — ver sección 13)

| Tabla | Filas eliminadas |
|---|---:|
| `dbo.relacion_documento` | 63 853 |
| `dbo.memo_correlativo` | 3 |
| `dbo.numero_interno1` | 7 556 |
| `dbo.acceso_bak_demo_20260806_144704` / `_151215` | 348 + 348 |
| `dbo.alerta_log_bak_demo_20260806_144704` / `_151215` | 68 + 68 |
| `dbo.archivo_digital_bak_demo_20260806_144704` / `_151215` | 27 + 27 |
| `dbo.auditoria_reset_bak_demo_20260806_144704` / `_151215` | 18 + 18 |
| `dbo.dependencia_backup_20260608` | 91 |
| `dbo.documento_bak_demo_20260806_144704` / `_151215` | 28 + 28 |
| `dbo.documento_destino_bak_demo_20260806_144704` / `_151215` | 29 + 29 |
| `dbo.expediente_bak_demo_20260806_144704` / `_151215` | 4 + 4 |
| `dbo.firma_gob_historial_bak_demo_20260806_144704` / `_151215` | 61 + 61 |
| `dbo.firma_gob_logs_bak_demo_20260806_151215` | 52 |
| `dbo.jefatura_backup_20260608` | 2 |
| `dbo.memo_generado_bak_demo_20260806_144704` / `_151215` | 24 + 24 |
| `dbo.memorandum_firma_simple_bak_demo_20260806_151215` | 19 |
| `dbo.password_reset_tokens_bak_demo_20260806_144704` / `_151215` | 11 + 11 |
| `dbo.respaldo_documento_bak_demo_20260806_144704` / `_151215` | 57 + 57 |
| `dbo.tramite_bak_demo_20260806_144704` / `_151215` | 57 + 57 |

Script versionado y reutilizable: `database/scripts/20-limpieza-documental-2026-08-19.sql` (transaccional, `BEGIN TRAN` / `COMMIT` solo tras validaciones, `ROLLBACK` automático ante cualquier error — un primer intento falló por falta de `SET QUOTED_IDENTIFIER ON` requerido por un índice filtrado en `memo_correlativo`; la transacción hizo rollback correctamente sin dejar cambios parciales, se corrigió el script y se re-ejecutó con éxito).

## 8. Tablas conservadas (sin modificar — verificado antes/después dentro de la misma transacción)

| Tabla | Filas antes | Filas después |
|---|---:|---:|
| `usuario` | 84 | 84 |
| `funcionario` | 692 | 692 |
| `rol` | 4 | 4 |
| `dependencia` | 214 | 214 |
| `jefatura` | 126 | 126 |
| `sisdoc.libro_referencia` | 11 | 11 |

También sin tocar (fuera del alcance de "documentos", no forman parte de ningún grupo de limpieza): `usuario_rol`(84), `rol_modulo`(32), `dependencia_externa`(532), `memo_firmante`(1), `firma_gob_config`(2), `tipo_documento`(81), `estado_documento`(4), `estado_tramite`(7), `estado_compromiso`(4), `tipo_compromiso`(3), `tipo_distribucion`(126), `dias_compromiso_alertas`(21), `descriptor`(179), `alerta_config`(1), `refresh_token`(177), `password_reset_tokens`(0), `auditoria_reset`(0), `acceso`(0), `sisdoc.auditoria`(207 — verificado: 0 filas correspondían a acciones sobre `documento`/`tramite`/`memorándum`, todo era `LOGIN_EXITOSO`/`USUARIO_ELIMINADO`/`LIBRO_REFERENCIA_*`, por lo que no requería filtrado), y todas las tablas legacy fuera de alcance del backend moderno (`calendario`, `nomina_despacho`, tablas de `facturas`, etc.).

## 9. Archivos eliminados

63 archivos físicos en `backend/uploads/` (4.5 MB según metadata de `archivo_digital`), identificados con precisión reconstruyendo la lista de rutas desde el propio backup (`RESTORE` a una base temporal de solo verificación, `SISDOC_scratch_verif`, consultada y eliminada inmediatamente después — nunca tocó la BD en vivo). Los 63 nombres fueron borrados 1:1, verificados contra el respaldo físico ya generado (0 discrepancias: 63 borrados, 0 no encontrados).

**No se tocaron:**
- 20 archivos huérfanos preexistentes en `backend/uploads/` (sin fila en `archivo_digital` desde antes del 2026-08-06, documentados en `md/INFORME_PREPARACION_DEMO_DOC360.md` — fuera del alcance confirmado de esta operación).
- `backend/uploads/config/` (7.1 MB — logo y fondo institucional).

## 10. Correlativos reiniciados

- `documento.num_interno` / `num_oficial`: se calculan `MAX(...)+1` sin tabla contador separada. Verificado en vivo: al crear un documento de prueba post-limpieza, `numInterno=1`, `numOficial=1` — confirmado correcto.
- `memo_generado.numero`: correlativo por `id_dependencia_origen + año`. Las 13 combinaciones activas antes de la limpieza (la mayoría en 1, una en 3) quedaron liberadas; el próximo memorándum de cualquier servicio en 2026 comenzará en 1.
- **No se reiniciaron manualmente** — es diseño deliberado del sistema (`MAX()+1` sobre tabla vacía = 1), documentado en `CLAUDE.md`. No existían otras tablas de correlativos vivas (`memo_correlativo` y `numero_interno1` eran huérfanas, confirmadas sin uso en `backend/src` antes de eliminarlas).

## 11. Validaciones realizadas

Dentro de la transacción, antes del `COMMIT`:
- Conteo antes/después de `usuario`, `funcionario`, `rol`, `dependencia`, `jefatura`, `libro_referencia` — 0 diferencias.
- Cero filas remanentes en `tramite`, `archivo_digital`, `memo_generado` tras el borrado (verificación de que el orden de eliminación no dejó huérfanos).

Post-`COMMIT`, contra el sistema real (no solo SQL):
- `curl /api/health` → `ok:true`.
- Frontend (`localhost:5173`) → HTTP 200.
- Login `admin` / `Huap.2025` → token emitido correctamente.
- `GET /reportes/dashboard` → `total:0, tramites:0, reservados:0, archivos:0, usuarios:84`.
- `GET /documentos` → lista vacía, `meta.total:0`.
- `POST /documentos` (documento de prueba) → creado con `numInterno:1`, `numOficial:1`.
- `DELETE /documentos/:id` (vía endpoint real, respeta `softDelete()` transaccional) → eliminado correctamente; generó 1 fila de auditoría en `respaldo_documento` (comportamiento esperado), limpiada después para dejar el sistema en cero real.
- Logs del backend (proceso `npm run dev` en curso) revisados → sin errores ni warnings.

## 12. Resultados de las pruebas

**Aprobado.** Sistema operativo de principio a fin: arranque, conexión a BD, login, bandejas vacías, dashboard en cero, creación de documento nuevo con numeración correcta, eliminación vía endpoint real sin errores.

## 13. Errores o advertencias

- Primer intento de ejecución del script falló (`Msg 1934`, `QUOTED_IDENTIFIER` incorrecto) por un índice filtrado preexistente en `memo_correlativo`. La transacción protegió los datos — `ROLLBACK` automático, 0 filas afectadas — se corrigió el script (`SET QUOTED_IDENTIFIER ON`) y se re-ejecutó con éxito.
- El alcance del Grupo E (`relacion_documento`, `memo_correlativo`, `numero_interno1`, 27 tablas de respaldo previas) era ambiguo en el pedido original del usuario (mensaje "todo" llegó interrumpido). Se pausó y se solicitó confirmación explícita mediante `AskUserQuestion` antes de incluirlo — el usuario confirmó "Todo — incluir Grupo E completo".
- No fue posible garantizar al 100%, por revisión manual fila por fila, que los 161 documentos eliminados no contenían ningún dato real — el análisis por muestreo (fechas, contenido de `materia`) indicó que eran datos de demo/prueba (consistente con `md/INFORME_PREPARACION_DEMO_DOC360.md`), con 3 documentos adicionales de basura de pruebas manuales del 2026-08-07. Esto se comunicó explícitamente al usuario antes de la confirmación final.

## 14. Evidencia de conservación de usuarios, roles, servicios y configuraciones

Ver tabla de la sección 8 — conteos idénticos antes/después capturados dentro de la misma transacción atómica (no en pasos separados vulnerables a condiciones de carrera). Adicionalmente verificado en caliente: login de `admin` exitoso post-limpieza, dashboard reporta `usuarios:84` sin cambios.

## 15. Estado final de los contenedores

```
sisdoc_sqlserver   Up 3h+   healthy
```
Backend (`npm run dev`, puerto 3001) y frontend (Vite, puerto 5173) corriendo sin errores, respondiendo HTTP 200/ok en todos los checks.

## 16. Procedimiento para revertir la operación completa

1. Detener el backend (para evitar escrituras durante la restauración).
2. Ejecutar el comando de restauración de la sección 5 (revierte la BD al estado exacto previo a la limpieza).
3. Restaurar los archivos físicos con el comando de la sección 5.
4. Reiniciar el backend.

Esto revierte **todo** — incluidos el documento de prueba creado y eliminado durante la validación (que ya no existe en la BD actual, pero si se restaura el `.bak` tampoco reaparecerá, porque el backup se tomó antes de crearlo).
