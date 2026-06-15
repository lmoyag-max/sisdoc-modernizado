-- ── Tabla de logs técnicos del módulo Firma.gob ──────────────────────────────
-- Aditiva: no modifica firma_gob_config ni firma_gob_historial.
-- Ejecutar una sola vez sobre la BD SISDOC

IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'firma_gob_logs')
BEGIN
  CREATE TABLE firma_gob_logs (
    id                  INT IDENTITY(1,1) PRIMARY KEY,
    fecha_hora          DATETIME       NOT NULL DEFAULT GETDATE(),
    ambiente            VARCHAR(20)    NULL,            -- 'TEST' | 'PRODUCCION'
    accion              VARCHAR(50)    NOT NULL,        -- 'test-conexion-nivel1' | 'nivel2' | 'nivel3' | 'solicitar-firma'
    metodo_http         VARCHAR(10)    NULL,            -- 'HEAD' | 'POST'
    endpoint            VARCHAR(500)   NULL,
    codigo_respuesta    INT            NULL,
    resultado           VARCHAR(30)    NOT NULL,        -- 'Exitoso' | 'Advertencia' | 'Error'
    mensaje             NVARCHAR(1000) NULL,
    id_usuario          INT            NULL,
    usuario_nombre      VARCHAR(100)   NULL,
    id_documento        INT            NULL,
    ticket_firmagov     VARCHAR(100)   NULL,
    request_payload     NVARCHAR(MAX)  NULL,
    response_payload    NVARCHAR(MAX)  NULL,
    tiempo_respuesta_ms INT            NULL,
    stack_trace         NVARCHAR(MAX)  NULL,
    recomendacion       NVARCHAR(500)  NULL,
    revisado            BIT            NOT NULL DEFAULT 0,
    fecha_revisado      DATETIME       NULL,
    id_usuario_revisor  INT            NULL,
    id_historial        INT            NULL            -- referencia informal a firma_gob_historial.id (sin FK)
  );

  CREATE INDEX IX_firma_gob_logs_fecha ON firma_gob_logs (fecha_hora DESC);
  CREATE INDEX IX_firma_gob_logs_ambiente_accion ON firma_gob_logs (ambiente, accion);
END
