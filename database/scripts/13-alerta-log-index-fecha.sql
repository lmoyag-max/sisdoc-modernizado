-- ══════════════════════════════════════════════════════════════
-- Índice de soporte para la paginación del historial de Alertas
-- (GET /api/v1/alertas/logs — ORDER BY fecha_envio DESC + OFFSET/FETCH)
--
-- Motivo: alerta_log solo tenía el PK clustered sobre "id". Sin un
-- índice sobre fecha_envio, cada página requeriría un escaneo
-- completo + sort de toda la tabla a medida que crezca el historial.
-- Es un cambio aditivo (no toca columnas, datos ni procesos de
-- escritura/Scheduler), solo agrega un índice no-clustered.
-- ══════════════════════════════════════════════════════════════

IF NOT EXISTS (
  SELECT 1 FROM sys.indexes
  WHERE name = 'IX_alerta_log_fecha_envio' AND object_id = OBJECT_ID('alerta_log')
)
BEGIN
  CREATE NONCLUSTERED INDEX IX_alerta_log_fecha_envio
    ON alerta_log(fecha_envio DESC);
END

PRINT 'Índice IX_alerta_log_fecha_envio aplicado.';
