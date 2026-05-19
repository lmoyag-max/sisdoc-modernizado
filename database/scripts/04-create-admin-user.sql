-- ============================================================
-- Script 04: Crear tablas de roles y usuario administrador
-- Ejecutar en: SISDOC
-- Seguro de re-ejecutar (usa IF NOT EXISTS)
-- ============================================================

USE SISDOC;
GO

-- ── Tabla rol ────────────────────────────────────────────────
IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'rol')
BEGIN
  CREATE TABLE rol (
    id_rol  INT          IDENTITY(1,1) PRIMARY KEY,
    codigo  VARCHAR(50)  NOT NULL UNIQUE,
    nombre  VARCHAR(100) NOT NULL,
    activo  BIT          NOT NULL DEFAULT 1
  );
  INSERT INTO rol (codigo, nombre) VALUES
    ('admin',       'Administrador del sistema'),
    ('coordinador', 'Coordinador'),
    ('funcionario', 'Funcionario');
  PRINT 'Tabla rol creada con 3 roles.';
END
ELSE
  PRINT 'Tabla rol ya existe — omitida.';
GO

-- ── Tabla usuario_rol ────────────────────────────────────────
IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'usuario_rol')
BEGIN
  CREATE TABLE usuario_rol (
    id_usuario INT NOT NULL,
    id_rol     INT NOT NULL,
    PRIMARY KEY (id_usuario, id_rol)
  );
  PRINT 'Tabla usuario_rol creada.';
END
ELSE
  PRINT 'Tabla usuario_rol ya existe — omitida.';
GO

-- ── Funcionario admin ────────────────────────────────────────
-- Solo inserta si no existe un funcionario con rut '00000000'
IF NOT EXISTS (SELECT 1 FROM funcionario WHERE rut = '00000000')
BEGIN
  INSERT INTO funcionario (rut, dig, nombres, apellidos, id_dependencia, vigencia)
  SELECT '00000000', '0', 'Administrador', 'Sistema',
         MIN(id_dependencia), 'S'
  FROM dependencia;
  PRINT 'Funcionario Administrador creado.';
END
ELSE
  PRINT 'Funcionario admin ya existe — omitido.';
GO

-- ── Usuario admin ────────────────────────────────────────────
-- clave = 'admin' (texto plano; el sistema la migra a bcrypt en primer login)
IF NOT EXISTS (SELECT 1 FROM usuario WHERE usuario = 'admin')
BEGIN
  DECLARE @idFun INT;
  SELECT @idFun = id_funcionario FROM funcionario WHERE rut = '00000000';

  INSERT INTO usuario (usuario, clave, id_funcionario, tipo_alertas)
  VALUES ('admin', 'admin', @idFun, 'A');
  PRINT 'Usuario admin creado.';

  -- Asignar rol admin
  DECLARE @idUsr INT, @idRol INT;
  SELECT @idUsr = id_usuario FROM usuario WHERE usuario = 'admin';
  SELECT @idRol = id_rol     FROM rol       WHERE codigo  = 'admin';

  IF @idUsr IS NOT NULL AND @idRol IS NOT NULL
    INSERT INTO usuario_rol (id_usuario, id_rol) VALUES (@idUsr, @idRol);

  PRINT 'Rol admin asignado.';
END
ELSE
  PRINT 'Usuario admin ya existe — omitido.';
GO

-- ── Verificacion final ───────────────────────────────────────
SELECT u.id_usuario, u.usuario, f.nombres + ' ' + f.apellidos AS nombre_completo,
       r.codigo AS rol, d.desc_dependencia
FROM usuario u
JOIN funcionario f  ON u.id_funcionario  = f.id_funcionario
JOIN dependencia d  ON f.id_dependencia  = d.id_dependencia
JOIN usuario_rol ur ON u.id_usuario      = ur.id_usuario
JOIN rol r          ON ur.id_rol         = r.id_rol
WHERE u.usuario = 'admin';
GO
