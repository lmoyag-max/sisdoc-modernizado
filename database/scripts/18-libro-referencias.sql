-- ============================================================
-- SCRIPT 18: Módulo "Libro de Referencias"
-- DOC360 — 2026
-- Ejecutar una sola vez. Idempotente: verifica existencia antes
-- de crear cada objeto. 100% aditivo — no modifica ninguna tabla
-- existente.
-- ============================================================

USE SISDOC;
GO

-- ── 1. Tabla principal ──────────────────────────────────────────
-- Correlativo anual REF-AAAA-NNNNNN. El número nunca se calcula sobre
-- filas filtradas por condicion: al ser eliminación lógica (la fila
-- nunca se borra), MAX(numero) jamás retrocede ni reutiliza un
-- correlativo eliminado — ver libro-referencias.repository.ts.
IF OBJECT_ID('libro_referencia', 'U') IS NULL
BEGIN
  CREATE TABLE libro_referencia (
    id_referencia           INT IDENTITY(1,1) PRIMARY KEY,

    -- Correlativo (generado exclusivamente en el backend, nunca editable)
    anio                    INT           NOT NULL,
    numero                  INT           NOT NULL,
    codigo                  VARCHAR(20)   NOT NULL,   -- REF-2026-000001

    -- Usuario que realizó el registro (identidad + instantánea del nombre
    -- para que el historial no cambie si el usuario se renombra después)
    id_usuario_creador      INT           NOT NULL REFERENCES usuario(id_usuario),
    nombre_usuario_creador  VARCHAR(100)  NOT NULL,

    -- Campos del formulario
    nombre_interesado       VARCHAR(150)  NOT NULL,
    tipo_tramite            VARCHAR(150)  NOT NULL,
    fecha_documento         DATE          NOT NULL,
    fecha_recepcion         DATE          NOT NULL,
    observaciones           VARCHAR(1000) NULL,

    -- Condición técnica interna (NO es un flujo de trabajo)
    condicion               VARCHAR(10)   NOT NULL DEFAULT 'VIGENTE'
        CONSTRAINT ck_libro_referencia_condicion CHECK (condicion IN ('VIGENTE', 'ELIMINADO')),

    -- Eliminación lógica — exclusiva de Superadministrador (rol admin)
    id_usuario_eliminacion  INT           NULL REFERENCES usuario(id_usuario),
    fecha_eliminacion       DATETIME      NULL,
    motivo_eliminacion      VARCHAR(500)  NULL,

    fecha_creacion          DATETIME      NOT NULL DEFAULT GETDATE(),
    fecha_actualizacion     DATETIME      NOT NULL DEFAULT GETDATE(),

    CONSTRAINT uq_libro_referencia_anio_numero UNIQUE (anio, numero),
    CONSTRAINT uq_libro_referencia_codigo      UNIQUE (codigo)
  );

  CREATE INDEX IX_libro_referencia_condicion       ON libro_referencia (condicion);
  CREATE INDEX IX_libro_referencia_nombre          ON libro_referencia (nombre_interesado);
  CREATE INDEX IX_libro_referencia_tipo_tramite    ON libro_referencia (tipo_tramite);
  CREATE INDEX IX_libro_referencia_fecha_documento ON libro_referencia (fecha_documento);
  CREATE INDEX IX_libro_referencia_fecha_recepcion ON libro_referencia (fecha_recepcion);
  CREATE INDEX IX_libro_referencia_anio            ON libro_referencia (anio);

  PRINT 'Tabla libro_referencia creada.';
END
ELSE
  PRINT 'Tabla libro_referencia ya existe — omitida.';
GO

-- ── 2. Registro del módulo en el RBAC existente ─────────────────
-- No crea un segundo sistema de permisos: reutiliza rol_modulo tal cual
-- lo usan el resto de los módulos (ver roles.routes.ts). El código del
-- módulo es 'libro-referencias' — debe coincidir exactamente con la
-- entrada agregada a TODOS_MODULOS en roles.routes.ts.
--
-- Asignación inicial: rol 'of.partes', tal como pide la especificación
-- ("Inicialmente podrá asignarse al rol Oficina de Partes"). El
-- Superadministrador puede quitarla o agregarla a cualquier otro rol
-- después desde /admin/roles sin tocar este script de nuevo.
IF EXISTS (SELECT 1 FROM rol WHERE codigo = 'of.partes')
   AND NOT EXISTS (
     SELECT 1 FROM rol_modulo rm
     JOIN rol r ON r.id_rol = rm.id_rol
     WHERE r.codigo = 'of.partes' AND rm.modulo = 'libro-referencias'
   )
BEGIN
  INSERT INTO rol_modulo (id_rol, modulo)
  SELECT id_rol, 'libro-referencias' FROM rol WHERE codigo = 'of.partes';
  PRINT 'Módulo libro-referencias asignado al rol of.partes.';
END
ELSE
  PRINT 'Asignación inicial de libro-referencias a of.partes ya existe u of.partes no existe — omitida.';
GO

PRINT '=== Script 18 completado ===';
