-- ============================================================
-- SCRIPT 19: Libro de Referencias — correlativo reutilizable
-- DOC360 — 2026
--
-- Corrige una regla de negocio mal implementada en el script 18: el
-- correlativo debe ser único solo entre registros VIGENTES del mismo año.
-- Al eliminar lógicamente una referencia, su número debe quedar disponible
-- para el siguiente registro que se cree (menor número libre entre los
-- vigentes) — antes el cálculo usaba MAX(numero)+1 sobre TODAS las filas
-- (vigentes y eliminadas), por lo que un número eliminado nunca volvía a
-- usarse. Ver libro-referencias.repository.ts#crear() para el nuevo
-- algoritmo (ejecutado en la aplicación, no en este script).
--
-- Este script NO modifica el script 18 (ya ejecutado) — es aditivo y
-- reemplaza únicamente las dos restricciones únicas que creó, por
-- restricciones únicas FILTRADAS (solo condicion='VIGENTE'). Esto permite
-- que exista una fila ELIMINADO y una fila VIGENTE con el mismo
-- (anio, numero) / codigo, sin permitir dos VIGENTE con el mismo valor.
--
-- Idempotente. 100% aislado al módulo Libro de Referencias — no toca
-- ninguna otra tabla.
-- ============================================================

USE SISDOC;
GO

-- Requerido por SQL Server para crear índices filtrados (CREATE INDEX ... WHERE ...).
SET QUOTED_IDENTIFIER ON;
GO

IF EXISTS (
  SELECT 1 FROM sys.indexes
  WHERE name = 'uq_libro_referencia_anio_numero' AND object_id = OBJECT_ID('libro_referencia')
)
BEGIN
  BEGIN TRANSACTION;

    -- Sin ventana insegura: DROP + CREATE corren dentro de la misma
    -- transacción, así que en ningún momento queda la tabla sin una
    -- restricción de unicidad vigente sobre (anio, numero) / codigo.
    ALTER TABLE libro_referencia DROP CONSTRAINT uq_libro_referencia_anio_numero;
    ALTER TABLE libro_referencia DROP CONSTRAINT uq_libro_referencia_codigo;

    CREATE UNIQUE INDEX uq_libro_referencia_anio_numero_vigente
      ON libro_referencia (anio, numero)
      WHERE condicion = 'VIGENTE';

    CREATE UNIQUE INDEX uq_libro_referencia_codigo_vigente
      ON libro_referencia (codigo)
      WHERE condicion = 'VIGENTE';

  COMMIT TRANSACTION;
  PRINT 'Restricciones únicas de libro_referencia migradas a únicas filtradas (solo VIGENTE).';
END
ELSE
  PRINT 'Script 19 ya aplicado (o el script 18 no se ejecutó aún) — omitido.';
GO

PRINT '=== Script 19 completado ===';
GO

-- ── Rollback (ejecutar manualmente solo si es necesario revertir) ──
-- BEGIN TRANSACTION;
--   DROP INDEX uq_libro_referencia_anio_numero_vigente ON libro_referencia;
--   DROP INDEX uq_libro_referencia_codigo_vigente ON libro_referencia;
--   ALTER TABLE libro_referencia ADD CONSTRAINT uq_libro_referencia_anio_numero UNIQUE (anio, numero);
--   ALTER TABLE libro_referencia ADD CONSTRAINT uq_libro_referencia_codigo UNIQUE (codigo);
-- COMMIT TRANSACTION;
-- NOTA: el rollback fallará si en ese momento existen filas ELIMINADO y
-- VIGENTE con el mismo (anio, numero)/codigo (exactamente el estado que
-- esta corrección permite) — habría que resolver esos duplicados a mano
-- antes de revertir.
