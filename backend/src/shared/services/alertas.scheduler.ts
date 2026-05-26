import { logger } from '../utils/logger';
import { getConfiguracion, enviarTodasLasAlertas } from '../../modules/alertas/alertas.service';
import { getPool, sql } from '../../config/database';

let schedulerInterval: ReturnType<typeof setInterval> | null = null;

// Verifica si ya se envió una alerta automática en el slot horario actual (ventana ±25 min)
async function yaEnviadoEnEsteSlot(horario: string): Promise<boolean> {
  try {
    const [hh, mm] = horario.split(':').map(Number);
    const pool     = await getPool();
    const now      = new Date();
    const slotDate = new Date(now);
    slotDate.setHours(hh, mm, 0, 0);

    // Busca si ya hubo envío automático entre slot-25min y slot+25min HOY
    const r = await pool.request()
      .input('desde', sql.DateTime, new Date(slotDate.getTime() - 25 * 60 * 1000))
      .input('hasta', sql.DateTime, new Date(slotDate.getTime() + 25 * 60 * 1000))
      .query<{ cnt: number }>(`
        SELECT COUNT(*) AS cnt FROM alerta_log
        WHERE tipo_envio = 'auto'
          AND fecha_envio BETWEEN @desde AND @hasta
          AND CAST(fecha_envio AS DATE) = CAST(GETDATE() AS DATE)
      `);
    return (r.recordset[0]?.cnt ?? 0) > 0;
  } catch {
    return false;
  }
}

async function tick(): Promise<void> {
  try {
    const config = await getConfiguracion();
    if (!config.activo) return;

    const now       = new Date();
    const horaActual = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    for (const horario of config.horarios) {
      const [hh, mm] = horario.split(':').map(Number);
      const diffMin  = Math.abs(now.getHours() * 60 + now.getMinutes() - (hh * 60 + mm));

      // Ventana de ±2 minutos para detectar el horario
      if (diffMin > 2) continue;

      const yaEnviado = await yaEnviadoEnEsteSlot(horario);
      if (yaEnviado) continue;

      logger.info(`[Scheduler] Disparando alertas automáticas (horario ${horario})`);
      const result = await enviarTodasLasAlertas('auto');
      logger.info(`[Scheduler] Resultado: ${result.enviados} enviados, ${result.errores} errores, ${result.sinDocs} sin docs, ${result.sinEmail} sin email`);
    }
  } catch (err) {
    logger.error('[Scheduler] Error en tick de alertas: ' + String(err));
  }
}

export function startAlertaScheduler(): void {
  if (schedulerInterval) return;
  logger.info('[Scheduler] Iniciando scheduler de alertas (intervalo: 60s)');
  schedulerInterval = setInterval(() => { void tick(); }, 60 * 1000);
}

export function stopAlertaScheduler(): void {
  if (schedulerInterval) {
    clearInterval(schedulerInterval);
    schedulerInterval = null;
    logger.info('[Scheduler] Scheduler de alertas detenido');
  }
}
