-- ============================================================
-- SCRIPT 05: Tablas auxiliares para Memorándum Institucional
-- SysDoc 2.0 — 2026
-- Ejecutar una sola vez. Idempotente: verifica existencia antes
-- de crear cada objeto.
-- ============================================================

USE SISDOC;
GO

-- ── 1. Firmantes por servicio/dependencia ──────────────────────
-- Almacena firmante titular y subrogante por cada dependencia.
-- No altera ninguna tabla existente.
IF OBJECT_ID('memo_firmante', 'U') IS NULL
BEGIN
  CREATE TABLE memo_firmante (
    id_firmante              INT IDENTITY(1,1) PRIMARY KEY,
    id_dependencia           INT NOT NULL
        REFERENCES dependencia(id_dependencia),

    -- Firmante titular
    nombre_titular           VARCHAR(100)  NOT NULL,
    cargo_titular            VARCHAR(100)  NOT NULL,
    firma_titular_ruta       VARCHAR(100)  NULL,   -- filename en uploads/config/memo/
    timbre_titular_ruta      VARCHAR(100)  NULL,
    activo_titular           BIT           NOT NULL DEFAULT 1,
    vigencia_desde_titular   DATE          NULL,
    vigencia_hasta_titular   DATE          NULL,

    -- Firmante subrogante (opcional)
    nombre_subrogante        VARCHAR(100)  NULL,
    cargo_subrogante         VARCHAR(100)  NULL,
    firma_subrogante_ruta    VARCHAR(100)  NULL,
    timbre_subrogante_ruta   VARCHAR(100)  NULL,
    activo_subrogante        BIT           NOT NULL DEFAULT 0,
    vigencia_desde_sub       DATE          NULL,
    vigencia_hasta_sub       DATE          NULL,

    fecha_sistema            DATETIME      NOT NULL DEFAULT GETDATE(),
    fecha_update             DATETIME      NOT NULL DEFAULT GETDATE(),

    -- Solo un firmante activo por dependencia
    CONSTRAINT uq_memo_firmante_dep UNIQUE (id_dependencia)
  );
  PRINT 'Tabla memo_firmante creada.';
END
ELSE
  PRINT 'Tabla memo_firmante ya existe — omitida.';
GO

-- ── 2. Control de correlativos anuales ─────────────────────────
-- Un registro por año; ultimo_numero se incrementa atómicamente
-- con UPDLOCK para evitar duplicados en acceso concurrente.
IF OBJECT_ID('memo_correlativo', 'U') IS NULL
BEGIN
  CREATE TABLE memo_correlativo (
    id             INT IDENTITY(1,1) PRIMARY KEY,
    anio           INT      NOT NULL,
    ultimo_numero  INT      NOT NULL DEFAULT 0,
    fecha_update   DATETIME NOT NULL DEFAULT GETDATE(),
    CONSTRAINT uq_memo_correlativo_anio UNIQUE (anio)
  );
  PRINT 'Tabla memo_correlativo creada.';
END
ELSE
  PRINT 'Tabla memo_correlativo ya existe — omitida.';
GO

-- ── 3. Registro/auditoría de memorándums generados ────────────
-- Un registro por memorándum generado definitivamente.
-- id_archivo_digital puede ser NULL si el archivo falla al subir
-- (el correlativo ya fue asignado y el documento existe).
IF OBJECT_ID('memo_generado', 'U') IS NULL
BEGIN
  CREATE TABLE memo_generado (
    id                    INT IDENTITY(1,1) PRIMARY KEY,
    id_documento          INT           NOT NULL
        REFERENCES documento(id_documento),
    id_archivo_digital    INT           NULL
        REFERENCES archivo_digital(id_archivo_digital),
    correlativo           VARCHAR(20)   NOT NULL,   -- MEMO-2026-000001
    anio                  INT           NOT NULL,
    numero                INT           NOT NULL,
    id_usuario_creador    INT           NOT NULL,
    id_dependencia_origen INT           NULL,

    -- Contenido enviado al PDF
    materia               VARCHAR(250)  NOT NULL,
    referencia            VARCHAR(250)  NULL,
    cuerpo                VARCHAR(MAX)  NULL,

    -- Firmante usado al momento de generar
    nombre_firmante       VARCHAR(100)  NULL,
    cargo_firmante        VARCHAR(100)  NULL,
    tipo_firmante         VARCHAR(10)   NULL,       -- 'TITULAR' | 'SUBROGANTE'
    firma_ruta            VARCHAR(100)  NULL,
    timbre_ruta           VARCHAR(100)  NULL,

    -- Metadatos del proceso
    generado_por_sistema  BIT           NOT NULL DEFAULT 1,
    fecha_creacion        DATETIME      NOT NULL DEFAULT GETDATE(),

    CONSTRAINT uq_memo_generado_correlativo UNIQUE (correlativo)
  );
  PRINT 'Tabla memo_generado creada.';
END
ELSE
  PRINT 'Tabla memo_generado ya existe — omitida.';
GO

PRINT '=== Script 05 completado ===';
