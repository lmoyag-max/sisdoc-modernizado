-- ============================================================
-- SCRIPT 01: Respaldo de documentos antes de limpieza
-- SISDOC v2 — Generado: 2026-05-19
-- ============================================================

USE SISDOC;

-- Respaldo completo de documentos en tabla histórica
IF OBJECT_ID('documento_backup_2026', 'U') IS NOT NULL
    DROP TABLE documento_backup_2026;

SELECT * INTO documento_backup_2026 FROM documento;

IF OBJECT_ID('tramite_backup_2026', 'U') IS NOT NULL
    DROP TABLE tramite_backup_2026;

SELECT * INTO tramite_backup_2026 FROM tramite;

PRINT 'Backup completado: documento_backup_2026 y tramite_backup_2026';
SELECT 'Documentos respaldados' AS estado, COUNT(*) AS total FROM documento_backup_2026;
