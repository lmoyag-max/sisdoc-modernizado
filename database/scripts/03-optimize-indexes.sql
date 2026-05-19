-- ============================================================
-- SCRIPT 03: Optimización de índices SISDOC
-- Mejora rendimiento de consultas frecuentes
-- ============================================================

USE SISDOC;
SET NOCOUNT ON;

-- Documento
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_doc_fecha_sistema' AND object_id=OBJECT_ID('documento'))
    CREATE INDEX IX_doc_fecha_sistema ON documento(fecha_sistema DESC) INCLUDE(id_tipo_documento,id_estado_documento,id_usuario,materia,num_interno,num_oficial);

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_doc_estado' AND object_id=OBJECT_ID('documento'))
    CREATE INDEX IX_doc_estado ON documento(id_estado_documento,fecha_sistema DESC);

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_doc_usuario' AND object_id=OBJECT_ID('documento'))
    CREATE INDEX IX_doc_usuario ON documento(id_usuario,fecha_sistema DESC);

-- Tramite
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_tramite_usuario' AND object_id=OBJECT_ID('tramite'))
    CREATE INDEX IX_tramite_usuario ON tramite(id_usuario,id_estado_tramite,fecha_sistema DESC);

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_tramite_destino' AND object_id=OBJECT_ID('tramite'))
    CREATE INDEX IX_tramite_destino ON tramite(id_destino,id_estado_tramite,fecha_sistema DESC);

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name='IX_tramite_documento' AND object_id=OBJECT_ID('tramite'))
    CREATE INDEX IX_tramite_documento ON tramite(id_documento,fecha_sistema DESC);

PRINT 'Índices de optimización creados correctamente.';
SELECT 'Índices OK' AS estado;
