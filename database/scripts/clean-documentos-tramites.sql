-- ============================================================
-- SISDOC: Backup y limpieza de documentos/tramites de prueba
-- Ejecutar en: SISDOC
-- Fecha: 2026-05-20
-- SEGURO: No toca usuarios, catálogos, roles ni dependencias
-- ============================================================

USE SISDOC;
GO

-- ── 1. BACKUP ────────────────────────────────────────────────

IF OBJECT_ID('doc_prueba_backup_2026', 'U') IS NOT NULL
  DROP TABLE doc_prueba_backup_2026;
SELECT * INTO doc_prueba_backup_2026 FROM documento;

IF OBJECT_ID('tramite_prueba_backup_2026', 'U') IS NOT NULL
  DROP TABLE tramite_prueba_backup_2026;
SELECT * INTO tramite_prueba_backup_2026 FROM tramite;

IF OBJECT_ID('archivo_digital_prueba_backup_2026', 'U') IS NOT NULL
  DROP TABLE archivo_digital_prueba_backup_2026;
SELECT * INTO archivo_digital_prueba_backup_2026 FROM archivo_digital;

PRINT 'Backup creado: doc_prueba_backup_2026, tramite_prueba_backup_2026, archivo_digital_prueba_backup_2026';
GO

-- ── 2. LIMPIEZA ──────────────────────────────────────────────
-- Solo elimina documentos con id_documento > 378000 (rango de prueba del nuevo sistema)
-- Los documentos legacy (id < 378000) se conservan

DECLARE @id_min INT = 378000;  -- ajustar si es necesario

-- Archivos de docs de prueba
DELETE a FROM archivo_digital a
JOIN documento d ON a.id_documento = d.id_documento
WHERE d.id_documento >= @id_min;

-- Tramites de docs de prueba
DELETE t FROM tramite t
JOIN documento d ON t.id_documento = d.id_documento
WHERE d.id_documento >= @id_min;

-- Descriptor-documento de docs de prueba (si existe)
IF OBJECT_ID('descriptor_documento', 'U') IS NOT NULL
  EXEC('DELETE dd FROM descriptor_documento dd
        JOIN documento d ON dd.id_documento = d.id_documento
        WHERE d.id_documento >= ' + @id_min);

-- Documentos de prueba
DELETE FROM documento WHERE id_documento >= @id_min;

SELECT
  'Limpieza completada' AS resultado,
  (SELECT COUNT(*) FROM documento) AS documentos_restantes,
  (SELECT COUNT(*) FROM tramite) AS tramites_restantes,
  (SELECT COUNT(*) FROM archivo_digital) AS archivos_restantes;
GO

-- ── 3. VERIFICACIÓN ─────────────────────────────────────────
SELECT 'documento' AS tabla, COUNT(*) AS total FROM documento
UNION ALL SELECT 'tramite', COUNT(*) FROM tramite
UNION ALL SELECT 'archivo_digital', COUNT(*) FROM archivo_digital
UNION ALL SELECT 'usuario', COUNT(*) FROM usuario
UNION ALL SELECT 'rol', COUNT(*) FROM rol;
GO
