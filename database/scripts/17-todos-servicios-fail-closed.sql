-- ============================================================
-- todos_servicios fail-closed — el default a nivel de columna era 1
-- (acceso total), lo que significa que cualquier INSERT manual que no
-- especifique la columna (fuera de la aplicación, que siempre la pasa
-- explícitamente) otorgaría bypass total de visibilidad por defecto.
-- Aditivo e idempotente; no cambia los valores ya existentes en filas
-- actuales — solo el DEFAULT para futuros inserts que omitan la columna.
-- ============================================================

IF EXISTS (
  SELECT 1
  FROM sys.default_constraints dc
  JOIN sys.columns c ON c.object_id = dc.parent_object_id AND c.column_id = dc.parent_column_id
  WHERE dc.parent_object_id = OBJECT_ID('usuario') AND c.name = 'todos_servicios'
    AND dc.definition = '((1))'
)
BEGIN
  DECLARE @constraintName NVARCHAR(200);
  SELECT @constraintName = dc.name
  FROM sys.default_constraints dc
  JOIN sys.columns c ON c.object_id = dc.parent_object_id AND c.column_id = dc.parent_column_id
  WHERE dc.parent_object_id = OBJECT_ID('usuario') AND c.name = 'todos_servicios';

  EXEC('ALTER TABLE usuario DROP CONSTRAINT ' + @constraintName);
  ALTER TABLE usuario ADD CONSTRAINT df_usuario_todos_servicios DEFAULT 0 FOR todos_servicios;
  PRINT 'Default de usuario.todos_servicios cambiado de 1 a 0 (fail-closed).';
END
ELSE
BEGIN
  PRINT 'usuario.todos_servicios ya no tiene default=1 — omitida.';
END
GO
