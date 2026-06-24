-- Habilita un correlativo de memorándum independiente por dependencia
-- (formato nuevo: MEMO-{anio}-{codigoDependencia}-{numero}). Los correlativos
-- ya emitidos (formato MEMO-{anio}-{numero}, contador global) NO se modifican
-- ni se renumeran — siguen siendo válidos con su numeración original.

SET QUOTED_IDENTIFIER ON;
GO

-- 1. Ampliar columnas para el formato más largo (~23 caracteres)
IF EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'memo_generado' AND COLUMN_NAME = 'correlativo'
    AND CHARACTER_MAXIMUM_LENGTH < 30
)
BEGIN
  ALTER TABLE memo_generado ALTER COLUMN correlativo VARCHAR(30) NOT NULL;
END

IF EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'firma_gob_historial' AND COLUMN_NAME = 'correlativo_memo'
    AND CHARACTER_MAXIMUM_LENGTH < 30
)
BEGIN
  ALTER TABLE firma_gob_historial ALTER COLUMN correlativo_memo VARCHAR(30) NULL;
END

-- 2. memo_correlativo: agregar columna de dependencia (NULL = contador
--    global legado, ya no se incrementa para memos nuevos con dependencia)
IF NOT EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'memo_correlativo' AND COLUMN_NAME = 'id_dependencia'
)
BEGIN
  ALTER TABLE memo_correlativo ADD id_dependencia INT NULL;
END
GO

-- 3. Reemplazar el UNIQUE(anio) -- ya no aplica con múltiples filas por año.
--    El nombre real del constraint en la BD es 'uq_memo_corr_anio' (difiere
--    del nombre documentado en 05-memorandum-setup.sql); se cubren ambos.
IF EXISTS (SELECT 1 FROM sys.key_constraints WHERE name = 'uq_memo_corr_anio' AND parent_object_id = OBJECT_ID('memo_correlativo'))
  ALTER TABLE memo_correlativo DROP CONSTRAINT uq_memo_corr_anio;

IF EXISTS (SELECT 1 FROM sys.key_constraints WHERE name = 'uq_memo_correlativo_anio' AND parent_object_id = OBJECT_ID('memo_correlativo'))
  ALTER TABLE memo_correlativo DROP CONSTRAINT uq_memo_correlativo_anio;

IF NOT EXISTS (
  SELECT 1 FROM sys.indexes
  WHERE name = 'uq_memo_correlativo_anio_dep' AND object_id = OBJECT_ID('memo_correlativo')
)
BEGIN
  CREATE UNIQUE INDEX uq_memo_correlativo_anio_dep
    ON memo_correlativo(anio, id_dependencia)
    WHERE id_dependencia IS NOT NULL;
END

PRINT 'Migración correlativo-por-servicio aplicada.';
