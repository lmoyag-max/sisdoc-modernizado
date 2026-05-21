-- ============================================================
-- SISDOC: Actualización de roles y campo todos_servicios
-- ============================================================
USE SISDOC;
GO

-- 1. Renombrar coordinador → of.partes
UPDATE rol SET codigo = 'of.partes', nombre = 'Of. de Partes'
WHERE codigo = 'coordinador';
PRINT 'Rol coordinador renombrado a of.partes: ' + CAST(@@ROWCOUNT AS VARCHAR) + ' fila(s)';

-- 2. Agregar rol supervisores (si no existe)
IF NOT EXISTS (SELECT 1 FROM rol WHERE codigo = 'supervisores')
BEGIN
  INSERT INTO rol (codigo, nombre, activo) VALUES ('supervisores', 'Supervisor', 1);
  PRINT 'Rol supervisores creado';
END
ELSE
  PRINT 'Rol supervisores ya existia';

-- 3. Agregar columna todos_servicios a usuario (si no existe)
IF NOT EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'usuario' AND COLUMN_NAME = 'todos_servicios'
)
BEGIN
  ALTER TABLE usuario ADD todos_servicios BIT NOT NULL DEFAULT 1;
  PRINT 'Columna todos_servicios agregada a usuario (default=1 = puede ver todo)';
END
ELSE
  PRINT 'Columna todos_servicios ya existia';
GO

-- 4. Verificación final
SELECT id_rol, codigo, nombre, activo FROM rol ORDER BY id_rol;
SELECT id_usuario, usuario, todos_servicios FROM usuario;
GO
