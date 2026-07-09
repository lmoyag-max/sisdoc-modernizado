-- ============================================================
-- Firma Simple DOC360 — soporte de rollback y preservación de
-- evidencia al eliminar un documento. Aditivo e idempotente.
--
-- Contexto: memorandum_firma_simple.id_documento se creó como
-- NOT NULL (14-firma-simple.sql). Al eliminar un documento
-- (softDelete en documento.repository.ts, o revertirDocumentoSinFirmar/
-- revertirMemorandumSinFirmar tras un fallo de firma) se necesita
-- poder desvincular esta tabla igual que ya se hace con
-- firma_gob_historial.id_documento, para no perder el registro de
-- auditoría de la firma (código de verificación, hashes, IP, etc.).
-- ============================================================

-- ── 1. memorandum_firma_simple.id_documento → NULL ─────────────
IF EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'memorandum_firma_simple' AND COLUMN_NAME = 'id_documento' AND IS_NULLABLE = 'NO'
)
BEGIN
  ALTER TABLE memorandum_firma_simple ALTER COLUMN id_documento INT NULL;
  PRINT 'Columna memorandum_firma_simple.id_documento ahora permite NULL.';
END
ELSE
BEGIN
  PRINT 'memorandum_firma_simple.id_documento ya permite NULL — omitida.';
END
GO
