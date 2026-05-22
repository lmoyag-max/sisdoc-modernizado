-- ── Tabla de auditoría de acciones sensibles ─────────────────────────────────
-- Ejecutar una sola vez sobre la BD SISDOC

IF NOT EXISTS (SELECT 1 FROM sys.tables WHERE name = 'auditoria')
BEGIN
  CREATE TABLE auditoria (
    id         INT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT           NULL,
    accion     NVARCHAR(50)  NOT NULL,
    recurso    NVARCHAR(100) NULL,
    detalle    NVARCHAR(500) NULL,
    ip         NVARCHAR(45)  NULL,
    timestamp  DATETIME2     NOT NULL DEFAULT GETDATE()
  );

  CREATE INDEX IX_auditoria_usuario   ON auditoria (id_usuario);
  CREATE INDEX IX_auditoria_timestamp ON auditoria (timestamp);
END
