-- ============================================================================
-- SISDOC / DOC360 — Validación de datos demostrativos (solo lectura)
-- ============================================================================
-- No modifica ningún dato. Verifica integridad, cronología y que no exista
-- PII real en los datos generados por backend/scripts/demo/generar_demo.mjs.
-- ============================================================================
USE SISDOC;
GO
SET NOCOUNT ON;

PRINT '=== VALIDACIÓN DOC360 DEMO ===';
PRINT '';

PRINT '--- Integridad referencial ---';
SELECT 'Documentos sin creador (id_usuario NULL o inexistente)' AS chequeo, COUNT(*) AS hallados
FROM documento d WHERE d.id_usuario IS NULL OR NOT EXISTS (SELECT 1 FROM usuario u WHERE u.id_usuario = d.id_usuario);

SELECT 'Documentos sin tipo/estado' AS chequeo, COUNT(*) AS hallados
FROM documento WHERE id_tipo_documento IS NULL OR id_estado_documento IS NULL;

SELECT 'Trámites huérfanos (id_documento sin documento)' AS chequeo, COUNT(*) AS hallados
FROM tramite t WHERE NOT EXISTS (SELECT 1 FROM documento d WHERE d.id_documento = t.id_documento);

SELECT 'Archivos huérfanos (id_documento sin documento, no legacy)' AS chequeo, COUNT(*) AS hallados
FROM archivo_digital a WHERE a.id_documento IS NOT NULL AND NOT EXISTS (SELECT 1 FROM documento d WHERE d.id_documento = a.id_documento);

SELECT 'memo_generado huérfanos' AS chequeo, COUNT(*) AS hallados
FROM memo_generado mg WHERE NOT EXISTS (SELECT 1 FROM documento d WHERE d.id_documento = mg.id_documento);

SELECT 'memorandum_firma_simple huérfanos (con id_documento no nulo)' AS chequeo, COUNT(*) AS hallados
FROM sisdoc.memorandum_firma_simple mfs WHERE mfs.id_documento IS NOT NULL AND NOT EXISTS (SELECT 1 FROM documento d WHERE d.id_documento = mfs.id_documento);

SELECT 'Usuarios sin rol asignado' AS chequeo, COUNT(*) AS hallados
FROM usuario u WHERE NOT EXISTS (SELECT 1 FROM usuario_rol ur WHERE ur.id_usuario = u.id_usuario);

PRINT '';
PRINT '--- Correlativos ---';
SELECT 'Correlativos de memorándum duplicados' AS chequeo, COUNT(*) AS hallados FROM (
  SELECT correlativo FROM memo_generado GROUP BY correlativo HAVING COUNT(*) > 1
) x;

SELECT 'num_interno duplicados en documento' AS chequeo, COUNT(*) AS hallados FROM (
  SELECT num_interno FROM documento GROUP BY num_interno HAVING COUNT(*) > 1
) x;

SELECT 'num_oficial duplicados en documento' AS chequeo, COUNT(*) AS hallados FROM (
  SELECT num_oficial FROM documento GROUP BY num_oficial HAVING COUNT(*) > 1
) x;

PRINT '';
PRINT '--- Cronología ---';
SELECT 'Trámites con fecha_recepcion anterior a fecha_despacho' AS chequeo, COUNT(*) AS hallados
FROM tramite WHERE fecha_recepcion IS NOT NULL AND fecha_despacho IS NOT NULL AND fecha_recepcion < fecha_despacho;

SELECT 'Trámites con fecha_sistema en el futuro' AS chequeo, COUNT(*) AS hallados
FROM tramite WHERE fecha_sistema > GETDATE();

SELECT 'Documentos con fecha_sistema en el futuro' AS chequeo, COUNT(*) AS hallados
FROM documento WHERE fecha_sistema > GETDATE();

SELECT 'Documentos cuyo último trámite es anterior a fecha_sistema del documento' AS chequeo, COUNT(*) AS hallados
FROM documento d
WHERE EXISTS (
  SELECT 1 FROM tramite t WHERE t.id_documento = d.id_documento AND t.fecha_sistema < d.fecha_sistema
  AND t.id_seguimiento <> (SELECT MIN(id_seguimiento) FROM tramite t2 WHERE t2.id_documento = d.id_documento)
);

PRINT '';
PRINT '--- Datos personales (no deben existir reales) ---';
SELECT 'Usuarios DEMO con correo fuera de @demo.invalid' AS chequeo, COUNT(*) AS hallados
FROM usuario u JOIN funcionario f ON f.id_funcionario = u.id_funcionario
WHERE f.rut = '0' AND u.email IS NOT NULL AND u.email NOT LIKE '%@demo.invalid';

SELECT 'Funcionarios DEMO con RUT distinto de 0 (marcador legacy de "sin RUT real")' AS chequeo, COUNT(*) AS hallados
FROM funcionario f JOIN usuario u ON u.id_funcionario = f.id_funcionario
WHERE u.email LIKE '%@demo.invalid' AND f.rut <> '0';

PRINT '';
PRINT '--- Marca DEMO ---';
SELECT 'Documentos creados por usuarios DEMO SIN marca DEMO_DOC360_2026 en algún trámite' AS chequeo, COUNT(*) AS hallados
FROM documento d
JOIN usuario u ON u.id_usuario = d.id_usuario
WHERE u.email LIKE '%@demo.invalid'
  AND NOT EXISTS (SELECT 1 FROM tramite t WHERE t.id_documento = d.id_documento AND t.observaciones LIKE '%DEMO_DOC360_2026%');

PRINT '';
PRINT '--- Cobertura funcional ---';
SELECT ed.desc_estado_documento, COUNT(*) AS documentos
FROM documento d JOIN estado_documento ed ON ed.id_estado_documento = d.id_estado_documento
GROUP BY ed.desc_estado_documento ORDER BY ed.desc_estado_documento;

SELECT 'Documentos urgentes (tipo_compromiso=Urgente en algún trámite)' AS chequeo, COUNT(DISTINCT t.id_documento) AS hallados
FROM tramite t WHERE t.id_tipo_compromiso = 3;

SELECT 'Documentos reservados (resuelto=S)' AS chequeo, COUNT(*) AS hallados FROM documento WHERE resuelto = 'S';

SELECT 'Documentos reservados cuyo único destino NO es Dirección (id=32)' AS chequeo, COUNT(*) AS hallados
FROM documento d WHERE d.resuelto = 'S' AND EXISTS (
  SELECT 1 FROM tramite t WHERE t.id_documento = d.id_documento AND t.id_destino <> 32 AND t.tipo_destinatario = 'D'
  AND t.id_seguimiento = (SELECT MIN(id_seguimiento) FROM tramite t2 WHERE t2.id_documento = d.id_documento)
);

SELECT 'Memorándums firmados vía Firma Simple' AS chequeo, COUNT(*) AS hallados FROM memo_generado WHERE estado_firma = 'firmado';
SELECT 'Memorándums pendientes de firma' AS chequeo, COUNT(*) AS hallados FROM memo_generado WHERE estado_firma IS NULL OR estado_firma <> 'firmado';

SELECT 'Rango de fecha_documento (documento)' AS chequeo, CONVERT(VARCHAR, MIN(fecha_documento), 23) AS desde, CONVERT(VARCHAR, MAX(fecha_documento), 23) AS hasta FROM documento;

PRINT '';
PRINT '--- Resumen ---';
SELECT 'Usuarios ficticios' AS metrica, COUNT(*) AS valor FROM usuario u JOIN funcionario f ON f.id_funcionario=u.id_funcionario WHERE f.rut='0'
UNION ALL SELECT 'Documentos totales', COUNT(*) FROM documento
UNION ALL SELECT 'Trámites totales', COUNT(*) FROM tramite
UNION ALL SELECT 'Memorándums', COUNT(*) FROM memo_generado
UNION ALL SELECT 'Memorándums firmados', COUNT(*) FROM memo_generado WHERE estado_firma='firmado'
UNION ALL SELECT 'Documentos reservados', COUNT(*) FROM documento WHERE resuelto='S'
UNION ALL SELECT 'Archivos adjuntos', COUNT(*) FROM archivo_digital;

PRINT '';
PRINT '=== FIN VALIDACIÓN ===';
GO
