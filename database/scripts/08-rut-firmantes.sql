-- ============================================================
-- SCRIPT 08: RUT firmantes en jefatura + id_archivo_firmado en memo_generado
-- SysDoc 2.0 — 2026
-- Idempotente: verifica existencia antes de agregar cada columna.
-- Prerequisito: scripts 06 y 07 ya ejecutados.
-- ============================================================

USE SISDOC;
GO

-- ── 1. Columnas RUT en jefatura ────────────────────────────────
-- Almacenan el RUN/RUT del firmante en formato "12345678-9".
-- Requeridos por FirmaGov para identificar al firmante.
IF OBJECT_ID('jefatura', 'U') IS NOT NULL
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'rut_titular'
  )
  BEGIN
    ALTER TABLE jefatura ADD rut_titular VARCHAR(12) NULL;
    PRINT 'Columna rut_titular agregada a jefatura.';
  END
  ELSE PRINT 'rut_titular ya existe.';

  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'rut_subrogante'
  )
  BEGIN
    ALTER TABLE jefatura ADD rut_subrogante VARCHAR(12) NULL;
    PRINT 'Columna rut_subrogante agregada a jefatura.';
  END
  ELSE PRINT 'rut_subrogante ya existe.';

  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'rut_subrogante_2'
  )
  BEGIN
    ALTER TABLE jefatura ADD rut_subrogante_2 VARCHAR(12) NULL;
    PRINT 'Columna rut_subrogante_2 agregada a jefatura.';
  END
  ELSE PRINT 'rut_subrogante_2 ya existe.';
END
ELSE
  PRINT 'Tabla jefatura no encontrada — columnas RUT omitidas.';
GO

-- ── 2. Columna id_archivo_firmado en memo_generado ─────────────
-- Referencia al archivo_digital que contiene el PDF firmado por FirmaGov.
IF OBJECT_ID('memo_generado', 'U') IS NOT NULL
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'memo_generado' AND COLUMN_NAME = 'id_archivo_firmado'
  )
  BEGIN
    ALTER TABLE memo_generado ADD id_archivo_firmado INT NULL;
    PRINT 'Columna id_archivo_firmado agregada a memo_generado.';
  END
  ELSE PRINT 'id_archivo_firmado ya existe en memo_generado.';
END
ELSE
  PRINT 'Tabla memo_generado no encontrada — columna id_archivo_firmado omitida.';
GO

-- ── 3. Columna estado_firma en memo_generado ───────────────────
-- Controla el estado FirmaGov del memorándum.
-- Valores: NULL (no aplica / flujo legado), 'pendiente', 'enviado', 'firmado', 'error'
IF OBJECT_ID('memo_generado', 'U') IS NOT NULL
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'memo_generado' AND COLUMN_NAME = 'estado_firma'
  )
  BEGIN
    ALTER TABLE memo_generado ADD estado_firma VARCHAR(20) NULL;
    PRINT 'Columna estado_firma agregada a memo_generado.';
  END
  ELSE PRINT 'estado_firma ya existe en memo_generado.';
END
GO

PRINT '=== Script 08 completado ===';
