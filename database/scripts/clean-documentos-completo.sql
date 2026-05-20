-- ============================================================
-- SISDOC: Limpieza completa de documentos de prueba (nuevo sistema)
-- Seguro: NO toca usuarios, roles, catálogos, dependencias
-- Solo elimina docs con id_documento >= 378000 (rango nuevo sistema)
-- ============================================================
USE SISDOC;
GO

DECLARE @minId INT = 378000; -- ajustar si es necesario

-- 1. Backup previo
IF OBJECT_ID('doc_completo_bak','U') IS NOT NULL DROP TABLE doc_completo_bak;
SELECT * INTO doc_completo_bak FROM documento;

IF OBJECT_ID('tramite_completo_bak','U') IS NOT NULL DROP TABLE tramite_completo_bak;
SELECT * INTO tramite_completo_bak FROM tramite;

IF OBJECT_ID('archivo_digital_bak','U') IS NOT NULL DROP TABLE archivo_digital_bak;
SELECT * INTO archivo_digital_bak FROM archivo_digital;

PRINT 'Backups creados: doc_completo_bak, tramite_completo_bak, archivo_digital_bak';

-- 2. Eliminar archivos de docs prueba
DELETE a FROM archivo_digital a
JOIN documento d ON a.id_documento = d.id_documento
WHERE d.id_documento >= @minId;
PRINT 'Archivos eliminados: ' + CAST(@@ROWCOUNT AS VARCHAR);

-- 3. Eliminar tramites de docs prueba
DELETE t FROM tramite t
JOIN documento d ON t.id_documento = d.id_documento
WHERE d.id_documento >= @minId;
PRINT 'Tramites eliminados: ' + CAST(@@ROWCOUNT AS VARCHAR);

-- 4. Eliminar descriptor_documento si existe
IF OBJECT_ID('descriptor_documento','U') IS NOT NULL
  EXEC('DELETE dd FROM descriptor_documento dd JOIN documento d ON dd.id_documento = d.id_documento WHERE d.id_documento >= ' + @minId);

-- 5. Eliminar documentos prueba
DELETE FROM documento WHERE id_documento >= @minId;
PRINT 'Documentos eliminados: ' + CAST(@@ROWCOUNT AS VARCHAR);

-- 6. Verificación final
SELECT tabla =
  CASE
    WHEN tbl='doc' THEN 'documento'
    WHEN tbl='tram' THEN 'tramite'
    ELSE 'archivo_digital'
  END,
  total
FROM (
  SELECT 'doc' tbl, COUNT(*) total FROM documento
  UNION ALL SELECT 'tram', COUNT(*) FROM tramite
  UNION ALL SELECT 'arch', COUNT(*) FROM archivo_digital
) x;
GO
