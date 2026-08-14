-- ============================================================================
-- SISDOC / DOC360 — Respaldo previo a preparación de ambiente demo
-- ============================================================================
-- Ejecutar SIEMPRE antes de 01_limpiar_datos_demo_doc360.sql.
-- Genera:
--   1. Backup completo de la base SISDOC (.bak) dentro del volumen ya montado
--      por docker-compose (./database:/var/opt/mssql/backup en el host).
--   2. Copia de respaldo de las 14 tablas operacionales que 01_limpiar...
--      va a modificar, con sello de tiempo, para poder restaurarlas de forma
--      selectiva sin necesidad de restaurar el .bak completo.
--   3. Conteo total de filas de TODAS las tablas (no solo las operacionales)
--      antes de cualquier cambio, como línea base de verificación.
--
-- Requiere permisos de BACKUP DATABASE (usar login "sa" — es la única
-- operación de este proceso que legítimamente lo requiere; el resto puede
-- correr con doc360_app).
--
-- Invocación (PowerShell, desde la raíz del repo):
--   $SA_PASS = (Get-Content backend\.env | Select-String '^DB_PASSWORD=').ToString().Split('=')[1]
--   $STAMP   = Get-Date -Format "yyyyMMdd_HHmmss"
--   docker exec sisdoc_sqlserver /opt/mssql-tools18/bin/sqlcmd `
--     -S localhost -U sa -P "<MSSQL_SA_PASSWORD del .env raiz>" -C -d SISDOC `
--     -v Stamp="$STAMP" `
--     -i /var/opt/mssql/backup/scripts/demo/00_respaldo_pre_demo.sql
--
-- Nota: este .sql debe ser accesible dentro del contenedor. La ruta más simple
-- es copiarlo al volumen ya montado antes de ejecutar:
--   docker cp database\scripts\demo\00_respaldo_pre_demo.sql sisdoc_sqlserver:/var/opt/mssql/backup/00_respaldo_pre_demo.sql
--   docker exec sisdoc_sqlserver /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "..." -C -d SISDOC -v Stamp="$STAMP" -i /var/opt/mssql/backup/00_respaldo_pre_demo.sql
--
-- IMPORTANTE: anote el valor de $STAMP generado — 01_limpiar_datos_demo_doc360.sql
-- lo exige como prerequisito (verifica que exista el backup con ese mismo sello
-- antes de permitir cualquier DELETE).
-- ============================================================================

USE SISDOC;
GO

SET NOCOUNT ON;

IF '$(Stamp)' = '$' + '(Stamp)'
BEGIN
  RAISERROR('Falta la variable Stamp. Invocar con: sqlcmd -v Stamp="yyyyMMdd_HHmmss" -i 00_respaldo_pre_demo.sql', 16, 1);
  RETURN;
END
GO

-- ── 1. Backup completo de la base (.bak) ───────────────────────────────────
DECLARE @backupPath NVARCHAR(400) =
  N'/var/opt/mssql/backup/demo-doc360-backups/doc360_backup_completo_$(Stamp).bak';

BACKUP DATABASE SISDOC
TO DISK = @backupPath
WITH FORMAT, INIT, NAME = 'SISDOC - respaldo previo a demo', SKIP, NOREWIND, NOUNLOAD, STATS = 10;
GO

PRINT '── Backup completo de la base finalizado ──';
GO

-- ── 2. Backup de tablas operacionales (grupo D del plan) ───────────────────
-- Usa el mismo patrón ya validado por el equipo en database/scripts/clean-documentos-*.sql
-- (SELECT ... INTO), extendido a las 14 tablas operacionales reales.

-- NOTA IMPORTANTE: la mayoría de las tablas vive en el schema "dbo", pero
-- memorandum_firma_simple y firma_gob_logs viven en el schema "sisdoc"
-- (verificado contra sys.tables/sys.schemas). El schema se resuelve dinámicamente
-- por tabla — NO asumir dbo, un intento previo con QUOTENAME(@tabla) sin
-- calificar produjo "Invalid object name" silencioso en esas dos tablas al
-- ejecutarse con un login (sa) cuyo default schema es dbo.

DECLARE @sql NVARCHAR(MAX) = N'';
DECLARE @tabla SYSNAME, @schema SYSNAME;
DECLARE @tablas TABLE (nombre SYSNAME);
INSERT INTO @tablas (nombre) VALUES
  ('documento'), ('documento_destino'), ('tramite'), ('archivo_digital'),
  ('expediente'), ('memo_generado'), ('memorandum_firma_simple'),
  ('firma_gob_historial'), ('firma_gob_logs'), ('alerta_log'),
  ('respaldo_documento'), ('acceso'), ('auditoria_reset'), ('password_reset_tokens');

DECLARE tabla_cursor CURSOR LOCAL FAST_FORWARD FOR SELECT nombre FROM @tablas;
OPEN tabla_cursor;
FETCH NEXT FROM tabla_cursor INTO @tabla;
WHILE @@FETCH_STATUS = 0
BEGIN
  SELECT @schema = s.name
  FROM sys.tables t JOIN sys.schemas s ON t.schema_id = s.schema_id
  WHERE t.name = @tabla;

  IF @schema IS NULL
  BEGIN
    RAISERROR('Tabla no encontrada, abortando respaldo: %s', 16, 1, @tabla);
    SET NOEXEC ON;
    BREAK;
  END

  SET @sql = N'IF OBJECT_ID(''dbo.' + @tabla + N'_bak_demo_$(Stamp)'', ''U'') IS NOT NULL DROP TABLE ' + QUOTENAME(@tabla + N'_bak_demo_$(Stamp)') + N';' +
             N'SELECT * INTO ' + QUOTENAME(@tabla + N'_bak_demo_$(Stamp)') + N' FROM ' + QUOTENAME(@schema) + N'.' + QUOTENAME(@tabla) + N';';
  EXEC sp_executesql @sql;
  IF OBJECT_ID('dbo.' + @tabla + '_bak_demo_$(Stamp)', 'U') IS NULL
  BEGIN
    RAISERROR('El respaldo de %s no se creó correctamente. Abortando.', 16, 1, @tabla);
    SET NOEXEC ON;
    BREAK;
  END
  PRINT 'Respaldada: ' + @schema + '.' + @tabla + ' → ' + @tabla + '_bak_demo_$(Stamp)';
  FETCH NEXT FROM tabla_cursor INTO @tabla;
END
CLOSE tabla_cursor;
DEALLOCATE tabla_cursor;
GO

-- ── 3. Conteo total de filas de TODAS las tablas (línea base) ──────────────
SELECT
  t.name AS tabla,
  SUM(p.rows) AS filas,
  '$(Stamp)' AS sello_respaldo,
  GETDATE() AS fecha_conteo,
  DB_NAME() AS base_datos
FROM sys.tables t
JOIN sys.partitions p ON t.object_id = p.object_id AND p.index_id IN (0, 1)
WHERE t.name NOT LIKE '%\_bak\_demo\_%' ESCAPE '\'
GROUP BY t.name
ORDER BY t.name;
GO

PRINT '── Respaldo previo a demo completado. Guarde el valor de Stamp para el paso de limpieza. ──';
GO
