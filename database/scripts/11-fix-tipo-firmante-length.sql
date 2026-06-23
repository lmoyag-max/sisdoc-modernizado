-- ============================================================
-- Fix: memo_generado.tipo_firmante demasiado corta para 'SUBROGANTE_2'
-- ============================================================
-- Contexto: 07-segundo-subrogante.sql agregó el 2° subrogante a la tabla
-- 'jefatura', pero memo_generado.tipo_firmante quedó en VARCHAR(10) — el
-- valor 'SUBROGANTE_2' (12 caracteres) no entra, lo que corrompe el
-- protocolo TDS al insertar (error 8016 "Data type 0xA7 has an invalid
-- data length or metadata length") y bloquea POST /memorandum/confirmar
-- con 500 cada vez que firma un segundo subrogante.
--
-- Cambio: ampliar a VARCHAR(20), igual que firma_gob_historial.tipo_firmante
-- (que ya usa ese tamaño). No afecta datos existentes, no es destructivo.

IF EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'memo_generado' AND COLUMN_NAME = 'tipo_firmante'
    AND CHARACTER_MAXIMUM_LENGTH < 20
)
BEGIN
  ALTER TABLE memo_generado ALTER COLUMN tipo_firmante VARCHAR(20) NULL;
END
