-- ============================================================================
-- SISDOC / DOC360 — Limpieza controlada de datos operacionales de prueba
-- ============================================================================
-- Elimina EXCLUSIVAMENTE las 14 tablas operacionales clasificadas como
-- "grupo D" en PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md (documentos, trámites,
-- archivos, memorándums, firma simple, firma-gob, alertas, accesos y resets
-- de contraseña — todos verificados como contenido de prueba, sin datos
-- reales, con id_documento >= 378000).
--
-- NO TOCA: usuario, usuario_rol, funcionario, rol, rol_modulo, dependencia,
-- dependencia_externa, tipo_documento, estado_documento, estado_tramite,
-- estado_compromiso, tipo_compromiso, tipo_distribucion, dias_compromiso_alertas,
-- descriptor, jefatura, memo_firmante, firma_gob_config, alerta_config,
-- ni ninguna tabla legacy (grupo E del plan).
--
-- NO usa DROP TABLE, DROP DATABASE ni TRUNCATE — solo DELETE, en el orden
-- verificado contra las FK reales de la base (sección 8 del plan).
--
-- Requiere:
--   1. Haber ejecutado 00_respaldo_pre_demo.sql con el MISMO $(Stamp) — el
--      script verifica que exista el respaldo antes de borrar nada.
--   2. Pasar la variable de confirmación exacta:
--        sqlcmd -v Stamp="<mismo valor usado en el respaldo>" ^
--               -v Confirmacion="CONFIRMO_LIMPIEZA_DOC360" ^
--               -i 01_limpiar_datos_demo_doc360.sql
--      Si no coincide EXACTAMENTE, el script aborta sin cambios.
-- ============================================================================

USE SISDOC;
GO

SET NOCOUNT ON;
SET XACT_ABORT ON;

-- ── 0. Guardas de seguridad ─────────────────────────────────────────────────

IF '$(Confirmacion)' <> 'CONFIRMO_LIMPIEZA_DOC360'
BEGIN
  RAISERROR('Confirmación ausente o incorrecta. Debe pasar -v Confirmacion="CONFIRMO_LIMPIEZA_DOC360" exactamente. Abortando sin cambios.', 16, 1);
  SET NOEXEC ON;
END
GO

IF '$(Stamp)' = '$' + '(Stamp)'
BEGIN
  RAISERROR('Falta la variable Stamp del respaldo previo. Ejecute primero 00_respaldo_pre_demo.sql y pase el mismo -v Stamp=... aquí. Abortando.', 16, 1);
  SET NOEXEC ON;
END
GO

IF OBJECT_ID('documento_bak_demo_$(Stamp)', 'U') IS NULL
BEGIN
  RAISERROR('No se encontró el respaldo documento_bak_demo_$(Stamp). Ejecute 00_respaldo_pre_demo.sql con el mismo Stamp antes de continuar. Abortando sin cambios.', 16, 1);
  SET NOEXEC ON;
END
GO

-- Bloqueo por ambiente productivo: por convención de este proyecto, producción
-- corre bajo el perfil docker-compose "prod" con NODE_ENV=production en el
-- backend, nunca contra este mismo contenedor de desarrollo directamente con
-- sqlcmd. Como defensa adicional a nivel de datos, este script se niega a
-- correr si detecta un volumen de datos incompatible con un ambiente de
-- prueba/demo (más de 500 documentos activos sugiere un ambiente en uso real
-- que este script no fue diseñado para tocar).
IF (SELECT COUNT(*) FROM documento) > 500
BEGIN
  RAISERROR('El volumen de documentos (>500) es incompatible con un ambiente de prueba/demo. Posible ambiente productivo. Abortando sin cambios.', 16, 1);
  SET NOEXEC ON;
END
GO

PRINT '── Guardas de seguridad superadas. Iniciando limpieza controlada. ──';
GO

-- ── 1. Limpieza transaccional, en el orden de dependencias verificado ──────

BEGIN TRY
  BEGIN TRANSACTION;

  DECLARE @n INT;

  -- Captura de rutas físicas ANTES de borrar archivo_digital, para poder
  -- limpiar backend/uploads/ manualmente después (tabla de sesión, no persiste).
  IF OBJECT_ID('tempdb..#archivos_a_borrar') IS NOT NULL DROP TABLE #archivos_a_borrar;
  SELECT id_archivo_digital, ruta, archivo INTO #archivos_a_borrar FROM archivo_digital;

  -- firma_gob_logs y memorandum_firma_simple viven en el schema "sisdoc",
  -- no en "dbo" (verificado contra sys.tables/sys.schemas) — deben calificarse
  -- explícitamente o fallan con "Invalid object name" bajo logins cuyo
  -- default schema es dbo (p. ej. sa).
  DELETE FROM sisdoc.firma_gob_logs;
  SET @n = @@ROWCOUNT;
  PRINT 'sisdoc.firma_gob_logs: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM firma_gob_historial;
  SET @n = @@ROWCOUNT;
  PRINT 'firma_gob_historial: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM sisdoc.memorandum_firma_simple;
  SET @n = @@ROWCOUNT;
  PRINT 'sisdoc.memorandum_firma_simple: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM memo_generado;
  SET @n = @@ROWCOUNT;
  PRINT 'memo_generado: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM documento_destino;
  SET @n = @@ROWCOUNT;
  PRINT 'documento_destino: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM tramite;
  SET @n = @@ROWCOUNT;
  PRINT 'tramite: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM respaldo_documento;
  SET @n = @@ROWCOUNT;
  PRINT 'respaldo_documento: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM archivo_digital;
  SET @n = @@ROWCOUNT;
  PRINT 'archivo_digital: ' + CAST(@n AS VARCHAR) + ' filas eliminadas (recuerde borrar también los archivos físicos en backend/uploads/ — ver listado emitido más abajo)';

  DELETE FROM expediente;
  SET @n = @@ROWCOUNT;
  PRINT 'expediente: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM documento;
  SET @n = @@ROWCOUNT;
  PRINT 'documento: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  -- Tablas independientes (sin relación con documento) ──────────────────────
  DELETE FROM alerta_log;
  SET @n = @@ROWCOUNT;
  PRINT 'alerta_log: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM acceso;
  SET @n = @@ROWCOUNT;
  PRINT 'acceso: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM auditoria_reset;
  SET @n = @@ROWCOUNT;
  PRINT 'auditoria_reset: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  DELETE FROM password_reset_tokens;
  SET @n = @@ROWCOUNT;
  PRINT 'password_reset_tokens: ' + CAST(@n AS VARCHAR) + ' filas eliminadas';

  COMMIT TRANSACTION;
  PRINT '── Limpieza completada y confirmada (COMMIT). ──';
END TRY
BEGIN CATCH
  IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION;
  PRINT '── ERROR: limpieza revertida (ROLLBACK). Ningún dato fue modificado. ──';
  PRINT ERROR_MESSAGE();
  THROW;
END CATCH
GO

-- ── 2. Verificación final ───────────────────────────────────────────────────
SELECT 'documento' tabla, COUNT(*) filas_restantes FROM documento
UNION ALL SELECT 'documento_destino', COUNT(*) FROM documento_destino
UNION ALL SELECT 'tramite', COUNT(*) FROM tramite
UNION ALL SELECT 'archivo_digital', COUNT(*) FROM archivo_digital
UNION ALL SELECT 'expediente', COUNT(*) FROM expediente
UNION ALL SELECT 'memo_generado', COUNT(*) FROM memo_generado
UNION ALL SELECT 'memorandum_firma_simple', COUNT(*) FROM sisdoc.memorandum_firma_simple
UNION ALL SELECT 'firma_gob_historial', COUNT(*) FROM firma_gob_historial
UNION ALL SELECT 'firma_gob_logs', COUNT(*) FROM sisdoc.firma_gob_logs
UNION ALL SELECT 'alerta_log', COUNT(*) FROM alerta_log
UNION ALL SELECT 'respaldo_documento', COUNT(*) FROM respaldo_documento
UNION ALL SELECT 'acceso', COUNT(*) FROM acceso
UNION ALL SELECT 'auditoria_reset', COUNT(*) FROM auditoria_reset
UNION ALL SELECT 'password_reset_tokens', COUNT(*) FROM password_reset_tokens
UNION ALL SELECT 'usuario (debe seguir en 9)', COUNT(*) FROM usuario
UNION ALL SELECT 'funcionario (debe seguir en 616)', COUNT(*) FROM funcionario
UNION ALL SELECT 'dependencia (debe seguir en 150)', COUNT(*) FROM dependencia;
GO

-- ── 3. Listado de archivos físicos a eliminar manualmente en backend/uploads/ ──
-- Capturado ANTES del DELETE de archivo_digital (paso 1), en #archivos_a_borrar.
-- backend/uploads/config/ (logo, fondo de login) NUNCA aparece aquí porque
-- archivo_digital solo registra adjuntos de documentos, no assets de configuración.
SELECT id_archivo_digital, ruta, archivo FROM #archivos_a_borrar ORDER BY id_archivo_digital;

PRINT '── Verificación final impresa arriba. Elimine manualmente en backend/uploads/ solo los archivos listados en la consulta anterior. ──';
GO
