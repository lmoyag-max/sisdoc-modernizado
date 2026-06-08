-- ============================================================
-- SCRIPT 06: Jefaturas y Módulo Firma.gob
-- SysDoc 2.0 — 2026
-- Idempotente: verifica existencia antes de crear cada objeto.
-- Migra datos existentes de memo_firmante → jefatura.
-- ============================================================

USE SISDOC;
GO

-- ── 1. Tabla jefatura (nueva fuente de verdad de firmantes) ────────
-- Reemplaza conceptualmente a memo_firmante como origen de firmantes
-- para Memorándum Institucional. memo_firmante se conserva como respaldo.
IF OBJECT_ID('jefatura', 'U') IS NULL
BEGIN
  CREATE TABLE jefatura (
    id_jefatura              INT IDENTITY(1,1) PRIMARY KEY,
    id_dependencia           INT NOT NULL
        REFERENCES dependencia(id_dependencia),

    -- Firmante titular
    nombre_titular           VARCHAR(100)  NOT NULL,
    cargo_titular            VARCHAR(100)  NOT NULL,
    firma_timbre_titular_ruta VARCHAR(100) NULL,   -- filename en uploads/config/memo/
    activo_titular           BIT           NOT NULL DEFAULT 1,
    vigencia_desde_titular   DATE          NULL,
    vigencia_hasta_titular   DATE          NULL,

    -- Firmante subrogante (opcional)
    nombre_subrogante        VARCHAR(100)  NULL,
    cargo_subrogante         VARCHAR(100)  NULL,
    firma_timbre_subrogante_ruta VARCHAR(100) NULL,
    activo_subrogante        BIT           NOT NULL DEFAULT 0,
    vigencia_desde_sub       DATE          NULL,
    vigencia_hasta_sub       DATE          NULL,

    fecha_sistema            DATETIME      NOT NULL DEFAULT GETDATE(),
    fecha_update             DATETIME      NOT NULL DEFAULT GETDATE(),
    id_usuario_modificacion  INT           NULL,

    CONSTRAINT uq_jefatura_dep UNIQUE (id_dependencia)
  );
  PRINT 'Tabla jefatura creada.';
END
ELSE
  PRINT 'Tabla jefatura ya existe — omitida.';
GO

-- ── 2. Agregar columnas de imagen combinada a memo_firmante (si faltan) ──
-- Necesarias para la migración de datos y retrocompatibilidad.
IF OBJECT_ID('memo_firmante', 'U') IS NOT NULL
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'memo_firmante'
      AND COLUMN_NAME = 'firma_timbre_titular_ruta'
  )
  BEGIN
    ALTER TABLE memo_firmante ADD firma_timbre_titular_ruta VARCHAR(100) NULL;
    PRINT 'Columna firma_timbre_titular_ruta agregada a memo_firmante.';
  END

  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'memo_firmante'
      AND COLUMN_NAME = 'firma_timbre_subrogante_ruta'
  )
  BEGIN
    ALTER TABLE memo_firmante ADD firma_timbre_subrogante_ruta VARCHAR(100) NULL;
    PRINT 'Columna firma_timbre_subrogante_ruta agregada a memo_firmante.';
  END
END
GO

-- ── 3. Migrar datos de memo_firmante → jefatura ────────────────────
-- Solo inserta registros que no existen en jefatura todavía.
IF OBJECT_ID('memo_firmante', 'U') IS NOT NULL
  AND OBJECT_ID('jefatura', 'U') IS NOT NULL
BEGIN
  INSERT INTO jefatura (
    id_dependencia,
    nombre_titular, cargo_titular,
    firma_timbre_titular_ruta,
    activo_titular, vigencia_desde_titular, vigencia_hasta_titular,
    nombre_subrogante, cargo_subrogante,
    firma_timbre_subrogante_ruta,
    activo_subrogante, vigencia_desde_sub, vigencia_hasta_sub,
    fecha_sistema, fecha_update
  )
  SELECT
    mf.id_dependencia,
    mf.nombre_titular, mf.cargo_titular,
    -- Prioriza imagen combinada; fallback a firma individual legacy
    COALESCE(mf.firma_timbre_titular_ruta, mf.firma_titular_ruta),
    mf.activo_titular, mf.vigencia_desde_titular, mf.vigencia_hasta_titular,
    mf.nombre_subrogante, mf.cargo_subrogante,
    COALESCE(mf.firma_timbre_subrogante_ruta, mf.firma_subrogante_ruta),
    mf.activo_subrogante, mf.vigencia_desde_sub, mf.vigencia_hasta_sub,
    mf.fecha_sistema, mf.fecha_update
  FROM memo_firmante mf
  WHERE NOT EXISTS (
    SELECT 1 FROM jefatura j WHERE j.id_dependencia = mf.id_dependencia
  );

  DECLARE @migrados INT = @@ROWCOUNT;
  PRINT CONCAT('Migrados ', @migrados, ' registros de memo_firmante a jefatura.');
END
GO

-- ── 4. Tabla firma_gob_config ──────────────────────────────────────
-- Configuración del servicio Firma.gob por ambiente.
-- Un registro por ambiente ('TEST' | 'PRODUCCION').
IF OBJECT_ID('firma_gob_config', 'U') IS NULL
BEGIN
  CREATE TABLE firma_gob_config (
    id                        INT IDENTITY(1,1) PRIMARY KEY,
    ambiente                  VARCHAR(20)  NOT NULL,   -- 'TEST' | 'PRODUCCION'
    url_api                   VARCHAR(255) NULL,
    entity                    VARCHAR(100) NULL,       -- RUT institución (Firma.gob "entity")
    purpose                   VARCHAR(255) NULL,       -- propósito descriptivo del proceso
    api_token_key             VARCHAR(500) NULL,       -- token de autenticación API
    jwt_secret                VARCHAR(500) NULL,       -- secreto JWT para firma de payloads
    max_reintentos            INT          NOT NULL DEFAULT 3,
    segundos_entre_reintentos INT          NOT NULL DEFAULT 30,
    activo                    BIT          NOT NULL DEFAULT 0,
    fecha_sistema             DATETIME     NOT NULL DEFAULT GETDATE(),
    fecha_update              DATETIME     NOT NULL DEFAULT GETDATE(),
    CONSTRAINT uq_firma_gob_config_amb UNIQUE (ambiente)
  );

  -- Inserta ambientes por defecto (TEST activo por defecto, PRODUCCION inactivo)
  INSERT INTO firma_gob_config (ambiente, activo)
  VALUES ('TEST', 1), ('PRODUCCION', 0);

  PRINT 'Tabla firma_gob_config creada con configs iniciales.';
END
ELSE
  PRINT 'Tabla firma_gob_config ya existe — omitida.';
GO

-- ── 5. Tabla firma_gob_historial ───────────────────────────────────
-- Registro de todas las solicitudes de firma electrónica enviadas.
-- Un registro por documento/intento de firma.
IF OBJECT_ID('firma_gob_historial', 'U') IS NULL
BEGIN
  CREATE TABLE firma_gob_historial (
    id                    INT IDENTITY(1,1) PRIMARY KEY,
    id_documento          INT          NULL
        REFERENCES documento(id_documento),
    correlativo_memo      VARCHAR(20)  NULL,
    id_usuario            INT          NULL,
    nombre_firmante       VARCHAR(100) NULL,
    tipo_firmante         VARCHAR(20)  NULL,      -- 'TITULAR' | 'SUBROGANTE'
    ambiente              VARCHAR(20)  NULL,      -- 'TEST' | 'PRODUCCION'
    -- Estados: Pendiente|Enviado|Firmado|Error|Reintentando|Cancelado|Expirado
    estado                VARCHAR(30)  NOT NULL DEFAULT 'Pendiente',
    intentos_realizados   INT          NOT NULL DEFAULT 0,
    resultado             NVARCHAR(500)  NULL,
    error_detalle         NVARCHAR(MAX)  NULL,
    run_firmante          VARCHAR(20)  NULL,
    entity                VARCHAR(100) NULL,
    purpose               VARCHAR(255) NULL,
    id_archivo_original   INT          NULL,
    id_archivo_firmado    INT          NULL,
    fecha_creacion        DATETIME     NOT NULL DEFAULT GETDATE(),
    fecha_envio           DATETIME     NULL,
    fecha_firma           DATETIME     NULL,
    fecha_update          DATETIME     NOT NULL DEFAULT GETDATE()
  );

  CREATE INDEX IX_fgh_id_documento ON firma_gob_historial (id_documento);
  CREATE INDEX IX_fgh_estado       ON firma_gob_historial (estado);
  CREATE INDEX IX_fgh_fecha_desc   ON firma_gob_historial (fecha_creacion DESC);

  PRINT 'Tabla firma_gob_historial creada.';
END
ELSE
  PRINT 'Tabla firma_gob_historial ya existe — omitida.';
GO

-- ── 6. Columna firma_timbre_ruta en memo_generado (si falta) ─────
-- El campo fue referenciado en el backend pero puede faltar en BD antigua.
IF OBJECT_ID('memo_generado', 'U') IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'memo_generado'
      AND COLUMN_NAME = 'firma_timbre_ruta'
  )
BEGIN
  ALTER TABLE memo_generado ADD firma_timbre_ruta VARCHAR(100) NULL;
  PRINT 'Columna firma_timbre_ruta agregada a memo_generado.';
END
GO

PRINT '=== Script 06 completado ===';
