-- ============================================================
-- SCRIPT 20: Limpieza documental completa DOC360
-- Fecha: 2026-08-19
-- Ambiente: desarrollo local (contenedor sisdoc_sqlserver, BD SISDOC)
-- Ejecutado bajo confirmación explícita del usuario:
--   "CONFIRMO LIMPIEZA DOCUMENTAL DOC360"
--   + alcance ampliado ("todo") al Grupo E: relacion_documento,
--     memo_correlativo, numero_interno1, y las 27 tablas de respaldo
--     previas (*_bak_demo_20260806_*, dependencia_backup_20260608,
--     jefatura_backup_20260608).
--
-- Respaldo previo verificado (RESTORE VERIFYONLY = válido):
--   database/backups-limpieza-documental/SISDOC_pre_limpieza_documental_20260819_161922.bak
--   database/backups-limpieza-documental/uploads_pre_limpieza_20260819_161922/ (83 archivos)
--
-- NO preserva usuario, usuario_rol, funcionario, rol, rol_modulo,
-- dependencia, dependencia_externa, jefatura, memo_firmante,
-- firma_gob_config, catálogos, alerta_config, libro_referencia,
-- sisdoc.auditoria (login/libro-referencias) — ninguna de estas
-- tablas es tocada por este script.
--
-- Orden de eliminación respetando FK reales y lógicas (ver mapa de
-- dependencias entregado en el DRY RUN). Todo dentro de UNA
-- transacción: ROLLBACK ante cualquier error, COMMIT solo si todas
-- las validaciones finales pasan.
-- ============================================================

USE SISDOC;
GO
SET NOCOUNT ON;
SET QUOTED_IDENTIFIER ON;
GO

IF OBJECT_ID('tempdb..#cleanup_log') IS NOT NULL DROP TABLE #cleanup_log;
CREATE TABLE #cleanup_log (
  orden INT IDENTITY(1,1),
  tabla VARCHAR(100),
  filas_eliminadas INT
);

BEGIN TRY
  BEGIN TRANSACTION limpieza_documental;

    -- ── Snapshot de tablas que DEBEN quedar intactas (validación post) ──
    DECLARE @usuarios_antes INT, @funcionarios_antes INT, @roles_antes INT,
            @dependencias_antes INT, @jefaturas_antes INT, @libro_ref_antes INT;
    SELECT @usuarios_antes = COUNT(*) FROM usuario;
    SELECT @funcionarios_antes = COUNT(*) FROM funcionario;
    SELECT @roles_antes = COUNT(*) FROM rol;
    SELECT @dependencias_antes = COUNT(*) FROM dependencia;
    SELECT @jefaturas_antes = COUNT(*) FROM jefatura;
    SELECT @libro_ref_antes = COUNT(*) FROM sisdoc.libro_referencia;

    -- ── Grupo D: operacional documental (orden por FK) ──
    DELETE FROM sisdoc.firma_gob_logs;
    INSERT INTO #cleanup_log VALUES ('sisdoc.firma_gob_logs', @@ROWCOUNT);

    DELETE FROM dbo.firma_gob_historial;
    INSERT INTO #cleanup_log VALUES ('dbo.firma_gob_historial', @@ROWCOUNT);

    DELETE FROM sisdoc.memorandum_firma_simple;
    INSERT INTO #cleanup_log VALUES ('sisdoc.memorandum_firma_simple', @@ROWCOUNT);

    DELETE FROM dbo.memo_generado;
    INSERT INTO #cleanup_log VALUES ('dbo.memo_generado', @@ROWCOUNT);

    DELETE FROM dbo.documento_destino;
    INSERT INTO #cleanup_log VALUES ('dbo.documento_destino', @@ROWCOUNT);

    DELETE FROM dbo.tramite;
    INSERT INTO #cleanup_log VALUES ('dbo.tramite', @@ROWCOUNT);

    DELETE FROM dbo.respaldo_documento;
    INSERT INTO #cleanup_log VALUES ('dbo.respaldo_documento', @@ROWCOUNT);

    DELETE FROM dbo.archivo_digital;
    INSERT INTO #cleanup_log VALUES ('dbo.archivo_digital', @@ROWCOUNT);

    DELETE FROM dbo.expediente;
    INSERT INTO #cleanup_log VALUES ('dbo.expediente', @@ROWCOUNT);

    DELETE FROM dbo.descriptor_documento;
    INSERT INTO #cleanup_log VALUES ('dbo.descriptor_documento', @@ROWCOUNT);

    DELETE FROM dbo.documento;
    INSERT INTO #cleanup_log VALUES ('dbo.documento', @@ROWCOUNT);

    DELETE FROM dbo.alerta_log;
    INSERT INTO #cleanup_log VALUES ('dbo.alerta_log', @@ROWCOUNT);

    -- ── Grupo E: legado huérfano + respaldos previos (alcance "todo") ──
    DELETE FROM dbo.relacion_documento;
    INSERT INTO #cleanup_log VALUES ('dbo.relacion_documento', @@ROWCOUNT);

    DELETE FROM dbo.memo_correlativo;
    INSERT INTO #cleanup_log VALUES ('dbo.memo_correlativo', @@ROWCOUNT);

    DELETE FROM dbo.numero_interno1;
    INSERT INTO #cleanup_log VALUES ('dbo.numero_interno1', @@ROWCOUNT);

    DELETE FROM dbo.acceso_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.acceso_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.acceso_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.acceso_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.alerta_log_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.alerta_log_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.alerta_log_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.alerta_log_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.archivo_digital_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.archivo_digital_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.archivo_digital_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.archivo_digital_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.auditoria_reset_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.auditoria_reset_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.auditoria_reset_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.auditoria_reset_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.dependencia_backup_20260608;
    INSERT INTO #cleanup_log VALUES ('dbo.dependencia_backup_20260608', @@ROWCOUNT);

    DELETE FROM dbo.documento_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.documento_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.documento_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.documento_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.documento_destino_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.documento_destino_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.documento_destino_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.documento_destino_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.expediente_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.expediente_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.expediente_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.expediente_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.firma_gob_historial_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.firma_gob_historial_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.firma_gob_historial_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.firma_gob_historial_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.firma_gob_logs_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.firma_gob_logs_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.jefatura_backup_20260608;
    INSERT INTO #cleanup_log VALUES ('dbo.jefatura_backup_20260608', @@ROWCOUNT);

    DELETE FROM dbo.memo_generado_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.memo_generado_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.memo_generado_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.memo_generado_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.memorandum_firma_simple_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.memorandum_firma_simple_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.password_reset_tokens_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.password_reset_tokens_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.password_reset_tokens_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.password_reset_tokens_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.respaldo_documento_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.respaldo_documento_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.respaldo_documento_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.respaldo_documento_bak_demo_20260806_151215', @@ROWCOUNT);

    DELETE FROM dbo.tramite_bak_demo_20260806_144704;
    INSERT INTO #cleanup_log VALUES ('dbo.tramite_bak_demo_20260806_144704', @@ROWCOUNT);
    DELETE FROM dbo.tramite_bak_demo_20260806_151215;
    INSERT INTO #cleanup_log VALUES ('dbo.tramite_bak_demo_20260806_151215', @@ROWCOUNT);

    -- ── Validaciones finales — deben pasar TODAS o se hace ROLLBACK ──
    DECLARE @usuarios_despues INT, @funcionarios_despues INT, @roles_despues INT,
            @dependencias_despues INT, @jefaturas_despues INT, @libro_ref_despues INT,
            @huerfanos_tramite INT, @huerfanos_archivo INT, @huerfanos_memo INT;

    SELECT @usuarios_despues = COUNT(*) FROM usuario;
    SELECT @funcionarios_despues = COUNT(*) FROM funcionario;
    SELECT @roles_despues = COUNT(*) FROM rol;
    SELECT @dependencias_despues = COUNT(*) FROM dependencia;
    SELECT @jefaturas_despues = COUNT(*) FROM jefatura;
    SELECT @libro_ref_despues = COUNT(*) FROM sisdoc.libro_referencia;

    SELECT @huerfanos_tramite = COUNT(*) FROM dbo.tramite;
    SELECT @huerfanos_archivo = COUNT(*) FROM dbo.archivo_digital;
    SELECT @huerfanos_memo = COUNT(*) FROM dbo.memo_generado;

    IF @usuarios_antes <> @usuarios_despues
      THROW 51000, 'Validación fallida: cambió el conteo de usuario.', 1;
    IF @funcionarios_antes <> @funcionarios_despues
      THROW 51001, 'Validación fallida: cambió el conteo de funcionario.', 1;
    IF @roles_antes <> @roles_despues
      THROW 51002, 'Validación fallida: cambió el conteo de rol.', 1;
    IF @dependencias_antes <> @dependencias_despues
      THROW 51003, 'Validación fallida: cambió el conteo de dependencia.', 1;
    IF @jefaturas_antes <> @jefaturas_despues
      THROW 51004, 'Validación fallida: cambió el conteo de jefatura.', 1;
    IF @libro_ref_antes <> @libro_ref_despues
      THROW 51005, 'Validación fallida: cambió el conteo de libro_referencia.', 1;
    IF @huerfanos_tramite <> 0
      THROW 51006, 'Validación fallida: quedaron filas en tramite.', 1;
    IF @huerfanos_archivo <> 0
      THROW 51007, 'Validación fallida: quedaron filas en archivo_digital.', 1;
    IF @huerfanos_memo <> 0
      THROW 51008, 'Validación fallida: quedaron filas en memo_generado.', 1;

  COMMIT TRANSACTION limpieza_documental;
  PRINT '=== COMMIT exitoso — limpieza documental aplicada ===';

  SELECT * FROM #cleanup_log ORDER BY orden;
  SELECT 'usuario' AS tabla_preservada, @usuarios_antes AS antes, @usuarios_despues AS despues
  UNION ALL SELECT 'funcionario', @funcionarios_antes, @funcionarios_despues
  UNION ALL SELECT 'rol', @roles_antes, @roles_despues
  UNION ALL SELECT 'dependencia', @dependencias_antes, @dependencias_despues
  UNION ALL SELECT 'jefatura', @jefaturas_antes, @jefaturas_despues
  UNION ALL SELECT 'libro_referencia', @libro_ref_antes, @libro_ref_despues;

END TRY
BEGIN CATCH
  IF XACT_STATE() <> 0 ROLLBACK TRANSACTION limpieza_documental;
  PRINT '=== ROLLBACK — la limpieza NO se aplicó ===';
  PRINT 'Error: ' + ERROR_MESSAGE();
  SELECT * FROM #cleanup_log ORDER BY orden; -- referencial, no aplicado por el ROLLBACK
  THROW;
END CATCH
GO
