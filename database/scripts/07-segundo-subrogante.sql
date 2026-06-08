-- ============================================================
-- SCRIPT 07: Segundo Subrogante en tabla jefatura
-- SysDoc 2.0 — 2026
-- Idempotente: verifica existencia antes de agregar cada columna.
-- ============================================================

USE SISDOC;
GO

IF OBJECT_ID('jefatura', 'U') IS NOT NULL
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'nombre_subrogante_2'
  )
  BEGIN
    ALTER TABLE jefatura ADD nombre_subrogante_2 VARCHAR(100) NULL;
    PRINT 'Columna nombre_subrogante_2 agregada.';
  END
  ELSE PRINT 'nombre_subrogante_2 ya existe.';

  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'cargo_subrogante_2'
  )
  BEGIN
    ALTER TABLE jefatura ADD cargo_subrogante_2 VARCHAR(100) NULL;
    PRINT 'Columna cargo_subrogante_2 agregada.';
  END
  ELSE PRINT 'cargo_subrogante_2 ya existe.';

  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'firma_timbre_subrogante_2_ruta'
  )
  BEGIN
    ALTER TABLE jefatura ADD firma_timbre_subrogante_2_ruta VARCHAR(100) NULL;
    PRINT 'Columna firma_timbre_subrogante_2_ruta agregada.';
  END
  ELSE PRINT 'firma_timbre_subrogante_2_ruta ya existe.';

  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'activo_subrogante_2'
  )
  BEGIN
    ALTER TABLE jefatura ADD activo_subrogante_2 BIT NOT NULL DEFAULT 0;
    PRINT 'Columna activo_subrogante_2 agregada.';
  END
  ELSE PRINT 'activo_subrogante_2 ya existe.';

  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'vigencia_desde_sub_2'
  )
  BEGIN
    ALTER TABLE jefatura ADD vigencia_desde_sub_2 DATE NULL;
    PRINT 'Columna vigencia_desde_sub_2 agregada.';
  END
  ELSE PRINT 'vigencia_desde_sub_2 ya existe.';

  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'vigencia_hasta_sub_2'
  )
  BEGIN
    ALTER TABLE jefatura ADD vigencia_hasta_sub_2 DATE NULL;
    PRINT 'Columna vigencia_hasta_sub_2 agregada.';
  END
  ELSE PRINT 'vigencia_hasta_sub_2 ya existe.';
END
ELSE
  PRINT 'Tabla jefatura no encontrada — script omitido.';
GO

PRINT '=== Script 07 completado ===';
