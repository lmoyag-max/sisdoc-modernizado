-- ============================================================
-- Memorándum institucional real — destinatario específico (persona,
-- no solo servicio). Aditivo e idempotente; no modifica columnas
-- existentes de memo_generado ni la lógica de documento_destino
-- (el ruteo del trámite sigue siendo por servicio, sin cambios).
-- Los datos quedan CONGELADOS al confirmar el memo, mismo patrón ya
-- usado por nombre_firmante/cargo_firmante.
-- ============================================================

IF NOT EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'memo_generado' AND COLUMN_NAME = 'nombre_destinatario'
)
BEGIN
  ALTER TABLE memo_generado ADD nombre_destinatario VARCHAR(100) NULL;
  PRINT 'memo_generado.nombre_destinatario agregada.';
END
GO

IF NOT EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'memo_generado' AND COLUMN_NAME = 'cargo_destinatario'
)
BEGIN
  ALTER TABLE memo_generado ADD cargo_destinatario VARCHAR(100) NULL;
  PRINT 'memo_generado.cargo_destinatario agregada.';
END
GO

-- Sin FK dura (mismo patrón que el resto de memo_generado): evita tener
-- que tocar documento.repository.ts#softDelete() si se borra el funcionario.
IF NOT EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'memo_generado' AND COLUMN_NAME = 'id_funcionario_destinatario'
)
BEGIN
  ALTER TABLE memo_generado ADD id_funcionario_destinatario INT NULL;
  PRINT 'memo_generado.id_funcionario_destinatario agregada.';
END
GO

PRINT '=== Script 15-memo-destinatario completado ===';
