-- ============================================================
-- Firma Simple DOC360 — mecanismo de firma interno alternativo a
-- FirmaGOB. Aditivo e idempotente; no modifica ni elimina nada de
-- FirmaGOB ni de las columnas existentes de jefatura/usuario.
-- ============================================================

-- ── 1. usuario.activo ──────────────────────────────────────────
-- No existía ningún concepto de usuario activo/inactivo (el borrado
-- es físico). Se agrega solo para poder validar "usuario vinculado
-- activo" en Firma Simple; no se usa en el login existente.
IF NOT EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'usuario' AND COLUMN_NAME = 'activo'
)
BEGIN
  ALTER TABLE usuario ADD activo BIT NOT NULL DEFAULT 1;
  PRINT 'Columna usuario.activo agregada.';
END
GO

-- ── 2. Vínculo jefatura → usuario (3 slots fijos) ──────────────
-- SQL Server no permite más de un FK con acción de cascada (SET NULL)
-- desde la misma tabla hacia la misma tabla referenciada ("may cause
-- cycles or multiple cascade paths", error 1785) — por eso las 3 FK
-- usan ON DELETE NO ACTION (default). Como consecuencia, el backend
-- (DELETE /usuarios/:id, ver módulo usuarios) debe poner en NULL estas
-- 3 columnas para el usuario a eliminar ANTES del DELETE FROM usuario
-- — mismo patrón que ya usa softDelete() de documentos con
-- firma_gob_historial.id_documento.
IF NOT EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'id_usuario_titular'
)
BEGIN
  ALTER TABLE jefatura ADD id_usuario_titular INT NULL
    CONSTRAINT fk_jefatura_usuario_titular REFERENCES usuario(id_usuario);
  PRINT 'jefatura.id_usuario_titular agregada.';
END
GO

IF NOT EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'id_usuario_subrogante'
)
BEGIN
  ALTER TABLE jefatura ADD id_usuario_subrogante INT NULL
    CONSTRAINT fk_jefatura_usuario_subrogante REFERENCES usuario(id_usuario);
  PRINT 'jefatura.id_usuario_subrogante agregada.';
END
GO

IF NOT EXISTS (
  SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = 'jefatura' AND COLUMN_NAME = 'id_usuario_subrogante_2'
)
BEGIN
  ALTER TABLE jefatura ADD id_usuario_subrogante_2 INT NULL
    CONSTRAINT fk_jefatura_usuario_subrogante_2 REFERENCES usuario(id_usuario);
  PRINT 'jefatura.id_usuario_subrogante_2 agregada.';
END
GO

-- Un mismo usuario no puede ocupar 2 slots de la misma jefatura.
-- Solo restringe datos nuevos; los NULL existentes no se ven afectados.
-- Corrige un despliegue parcial anterior donde id_usuario_titular quedó
-- con ON DELETE SET NULL (falló al agregar el 2º/3er FK con cascada).
IF EXISTS (
  SELECT 1 FROM sys.foreign_keys
  WHERE name = 'fk_jefatura_usuario_titular' AND delete_referential_action <> 0
)
BEGIN
  ALTER TABLE jefatura DROP CONSTRAINT fk_jefatura_usuario_titular;
  ALTER TABLE jefatura ADD CONSTRAINT fk_jefatura_usuario_titular
    FOREIGN KEY (id_usuario_titular) REFERENCES usuario(id_usuario);
  PRINT 'fk_jefatura_usuario_titular corregida a ON DELETE NO ACTION.';
END
GO

IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'ck_jefatura_usuarios_distintos')
BEGIN
  ALTER TABLE jefatura ADD CONSTRAINT ck_jefatura_usuarios_distintos CHECK (
    (id_usuario_titular IS NULL OR id_usuario_subrogante IS NULL OR id_usuario_titular <> id_usuario_subrogante) AND
    (id_usuario_titular IS NULL OR id_usuario_subrogante_2 IS NULL OR id_usuario_titular <> id_usuario_subrogante_2) AND
    (id_usuario_subrogante IS NULL OR id_usuario_subrogante_2 IS NULL OR id_usuario_subrogante <> id_usuario_subrogante_2)
  );
  PRINT 'Constraint ck_jefatura_usuarios_distintos agregado.';
END
GO

-- ── 3. Evidencia de firma: memorandum_firma_simple ─────────────
-- id_documento / id_jefatura son INT planos, SIN FK dura hacia
-- documento/jefatura (mismo patrón ya usado por firma_gob_historial):
-- así se evita tener que tocar documento.repository.ts#softDelete(),
-- que hoy coordina cuidadosamente el orden de borrado entre tablas
-- (ver su propio comentario de advertencia sobre este caso exacto).
-- Los datos del firmante quedan CONGELADOS al momento de firmar
-- (nombre/cargo/rut/servicio) porque la jefatura puede cambiar después
-- y el documento ya firmado debe conservar la evidencia histórica.
IF OBJECT_ID('memorandum_firma_simple', 'U') IS NULL
BEGIN
  CREATE TABLE memorandum_firma_simple (
    id                        INT IDENTITY(1,1) PRIMARY KEY,
    id_documento              INT           NOT NULL,
    correlativo_memo          VARCHAR(30)   NULL,
    id_jefatura               INT           NULL,
    tipo_firmante             VARCHAR(20)   NOT NULL,   -- TITULAR | SUBROGANTE | SUBROGANTE_2
    id_usuario_firmante       INT           NULL,
    id_usuario_solicitante    INT           NULL,
    nombre_firmante           VARCHAR(100)  NOT NULL,
    cargo_firmante            VARCHAR(100)  NOT NULL,
    rut_firmante              VARCHAR(12)   NULL,
    id_dependencia_firmante   INT           NULL,
    servicio_firmante         VARCHAR(150)  NULL,
    anio                      INT           NOT NULL,
    numero                    INT           NOT NULL,
    codigo_verificacion       VARCHAR(30)   NOT NULL,
    hash_pdf_original         VARCHAR(64)   NOT NULL,
    hash_pdf_firmado          VARCHAR(64)   NULL,
    id_archivo_digital        INT           NULL,
    estado                    VARCHAR(20)   NOT NULL DEFAULT 'validado', -- validado|firmado|expirado|error
    ip_solicitud              VARCHAR(45)   NULL,
    user_agent_solicitud      VARCHAR(500)  NULL,
    fecha_creacion            DATETIME      NOT NULL DEFAULT GETDATE(),
    fecha_firmado             DATETIME      NULL,

    CONSTRAINT uq_mfs_codigo_verificacion UNIQUE (codigo_verificacion),
    CONSTRAINT uq_mfs_anio_numero         UNIQUE (anio, numero)
  );

  CREATE INDEX IX_mfs_id_documento        ON memorandum_firma_simple (id_documento);
  CREATE INDEX IX_mfs_estado              ON memorandum_firma_simple (estado);
  CREATE INDEX IX_mfs_fecha_creacion_desc ON memorandum_firma_simple (fecha_creacion DESC);
  CREATE INDEX IX_mfs_id_usuario_firmante ON memorandum_firma_simple (id_usuario_firmante);

  PRINT 'Tabla memorandum_firma_simple creada.';
END
ELSE
  PRINT 'Tabla memorandum_firma_simple ya existe — omitida.';
GO

PRINT '=== Script 14-firma-simple completado ===';
