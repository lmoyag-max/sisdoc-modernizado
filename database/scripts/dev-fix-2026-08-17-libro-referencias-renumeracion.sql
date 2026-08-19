-- ============================================================
-- FIX PUNTUAL (no es una migración reutilizable) — 2026-08-17
-- Libro de Referencias: reconciliación de datos de desarrollo
--
-- Contexto: antes de corregir el algoritmo de correlativos (script 19),
-- el ambiente de desarrollo quedó con 3 referencias de prueba eliminadas
-- (REF-2026-000001/2/3) y 1 vigente generada incorrectamente como
-- REF-2026-000004. Con el algoritmo corregido, esa referencia vigente
-- debería haber recibido REF-2026-000001 (el menor número libre).
--
-- Los 4 registros son inequívocamente datos de prueba creados el mismo día
-- (nombre_interesado: "Juan Pérez Testing", "ARTURO MOYA", "WEWW") — no hay
-- indicio de que sean datos productivos. Este script SOLO renumera el
-- registro vigente de prueba para que el ambiente quede consistente con el
-- algoritmo corregido; no debe ejecutarse en otro ambiente sin adaptar el
-- id_referencia. Transaccional y con verificación defensiva: si el estado
-- no coincide exactamente con lo esperado, no hace ningún cambio.
-- ============================================================

USE SISDOC;
GO

-- La tabla ahora tiene índices únicos filtrados (script 19) — cualquier
-- INSERT/UPDATE/DELETE contra ella requiere QUOTED_IDENTIFIER ON en la sesión.
SET QUOTED_IDENTIFIER ON;
GO

BEGIN TRANSACTION;

IF EXISTS (
    SELECT 1 FROM libro_referencia
    WHERE id_referencia = 60 AND codigo = 'REF-2026-000004' AND anio = 2026 AND condicion = 'VIGENTE'
  )
  AND NOT EXISTS (
    SELECT 1 FROM libro_referencia WHERE anio = 2026 AND numero = 1 AND condicion = 'VIGENTE'
  )
BEGIN
  UPDATE libro_referencia
  SET numero = 1, codigo = 'REF-2026-000001', fecha_actualizacion = GETDATE()
  WHERE id_referencia = 60;

  INSERT INTO auditoria (id_usuario, accion, recurso, detalle)
  VALUES (
    532, -- admin — acción ejecutada como parte de la corrección de esta migración
    'LIBRO_REFERENCIA_RENUMERADO',
    'REF-2026-000001',
    'Renumeración de datos de desarrollo tras corregir el algoritmo de correlativos: id_referencia=60 REF-2026-000004 -> REF-2026-000001 (script dev-fix-2026-08-17)'
  );

  PRINT 'id_referencia=60 renumerado de REF-2026-000004 a REF-2026-000001.';
END
ELSE
  PRINT 'El estado actual no coincide con lo esperado — no se aplicó ningún cambio (verificación de seguridad).';

COMMIT TRANSACTION;
GO
