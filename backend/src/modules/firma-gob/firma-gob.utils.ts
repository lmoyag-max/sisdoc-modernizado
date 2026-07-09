import fs from 'fs';
import path from 'path';
import { sql } from '../../config/database';
import { logger } from '../../shared/utils/logger';

type Pool = Awaited<ReturnType<typeof import('../../config/database').getPool>>;

// ── Interpretación de códigos HTTP ──────────────────────────────────────────
// Mapea la respuesta de FirmaGov a un mensaje y recomendación entendibles
// para soporte/TI, sin alterar el código HTTP real devuelto al frontend.

export type ResultadoLog = 'Exitoso' | 'Advertencia' | 'Error';

export interface InterpretacionHttp {
  resultado: ResultadoLog;
  mensaje: string;
  recomendacion: string;
}

export const INTERPRETACION_HTTP: Record<number, InterpretacionHttp> = {
  200: {
    resultado: 'Exitoso',
    mensaje: 'Credenciales válidas y endpoint operativo',
    recomendacion: 'Ninguna acción requerida.',
  },
  201: {
    resultado: 'Exitoso',
    mensaje: 'Credenciales válidas y endpoint operativo',
    recomendacion: 'Ninguna acción requerida.',
  },
  400: {
    resultado: 'Error',
    mensaje: 'Payload rechazado o parámetros incompletos',
    recomendacion: 'Revisar el formato del payload enviado a FirmaGov (campos requeridos y tipos de dato).',
  },
  401: {
    resultado: 'Error',
    mensaje: 'Token inválido, vencido o mal configurado',
    recomendacion: 'Verificar el JWT Secret y el API Token configurados para este ambiente en Administración → FirmaGob.',
  },
  403: {
    resultado: 'Error',
    mensaje: 'Credenciales válidas pero sin permisos / usuario no habilitado',
    recomendacion: 'Confirmar con FirmaGov que el RUT y la entidad (entity) están habilitados para este ambiente.',
  },
  404: {
    resultado: 'Error',
    mensaje: 'Endpoint incorrecto o recurso inexistente',
    recomendacion: 'Verificar la URL configurada (url_api) para este ambiente.',
  },
  405: {
    resultado: 'Advertencia',
    mensaje: 'Endpoint alcanzable pero el método HTTP no está permitido — no valida credenciales',
    recomendacion: 'Esto es esperado para HEAD/GET en este endpoint. La conectividad está OK; use "Validar credenciales (envío real)" para verificar el resto.',
  },
  500: {
    resultado: 'Error',
    mensaje: 'Error del servicio FirmaGov o problema interno del payload',
    recomendacion: 'Reintentar más tarde. Si persiste, contactar a soporte de FirmaGov adjuntando el detalle técnico de este log.',
  },
};

const INTERPRETACION_DEFAULT_OK: InterpretacionHttp = {
  resultado: 'Exitoso',
  mensaje: 'Respuesta con código HTTP no documentado',
  recomendacion: 'Ninguna acción requerida.',
};

const INTERPRETACION_DEFAULT_ADVERTENCIA: InterpretacionHttp = {
  resultado: 'Advertencia',
  mensaje: 'Respuesta con código HTTP no documentado',
  recomendacion: 'Revisar el detalle técnico de la respuesta para más contexto.',
};

const INTERPRETACION_DEFAULT_ERROR: InterpretacionHttp = {
  resultado: 'Error',
  mensaje: 'Respuesta con código HTTP no documentado',
  recomendacion: 'Revisar el detalle técnico de la respuesta para más contexto.',
};

export function interpretarStatus(status: number): InterpretacionHttp {
  if (INTERPRETACION_HTTP[status]) return INTERPRETACION_HTTP[status];
  if (status >= 200 && status < 300) return INTERPRETACION_DEFAULT_OK;
  if (status >= 400) return INTERPRETACION_DEFAULT_ERROR;
  return INTERPRETACION_DEFAULT_ADVERTENCIA;
}

// ── Enmascarado de credenciales ─────────────────────────────────────────────

export function enmascarar(valor: string | null | undefined): string {
  if (!valor) return '';
  if (valor.length <= 8) return '***';
  return `${valor.slice(0, 4)}...${valor.slice(-4)} (${valor.length} caracteres)`;
}

// 'token' (el JWT firmado) queda fuera de este set deliberadamente: expira en
// minutos, no contiene jwt_secret ni api_token_key, y sus claims (run/entity/
// purpose/expiration) no son confidenciales — verlo completo en los logs es
// necesario para diagnóstico con FirmaGov/Gobierno Digital.
const CLAVES_SENSIBLES = new Set(['api_token_key', 'jwt_secret', 'authorization', 'Authorization']);

/** Clona un payload (request/response) ocultando credenciales y contenido binario antes de persistirlo. */
export function enmascararPayload(valor: unknown): unknown {
  if (Array.isArray(valor)) return valor.map(enmascararPayload);
  if (valor && typeof valor === 'object') {
    const resultado: Record<string, unknown> = {};
    for (const [clave, val] of Object.entries(valor as Record<string, unknown>)) {
      if (CLAVES_SENSIBLES.has(clave) && typeof val === 'string') {
        resultado[clave] = enmascarar(val);
      } else if (clave === 'content' && typeof val === 'string' && val.length > 100) {
        resultado[clave] = `<contenido omitido, ${val.length} caracteres base64>`;
      } else {
        resultado[clave] = enmascararPayload(val);
      }
    }
    return resultado;
  }
  return valor;
}

// ── Registro de logs técnicos ───────────────────────────────────────────────

export interface DatosLog {
  ambiente?: string | null;
  accion: string;
  metodoHttp?: string | null;
  endpoint?: string | null;
  codigoRespuesta?: number | null;
  resultado: ResultadoLog;
  mensaje?: string | null;
  idUsuario?: number | null;
  usuarioNombre?: string | null;
  idDocumento?: number | null;
  ticketFirmaGov?: string | null;
  requestPayload?: unknown;
  responsePayload?: unknown;
  tiempoRespuestaMs?: number | null;
  stackTrace?: string | null;
  recomendacion?: string | null;
  idHistorial?: number | null;
}

/** Inserta una fila en firma_gob_logs. Nunca lanza — un fallo de logging no debe afectar el flujo invocante. */
export async function registrarLog(pool: Pool, datos: DatosLog): Promise<number | null> {
  try {
    const result = await pool.request()
      .input('ambiente',         sql.VarChar(20),  datos.ambiente ?? null)
      .input('accion',           sql.VarChar(50),  datos.accion)
      .input('metodoHttp',       sql.VarChar(10),  datos.metodoHttp ?? null)
      .input('endpoint',         sql.VarChar(500), datos.endpoint ?? null)
      .input('codigoRespuesta',  sql.Int,          datos.codigoRespuesta ?? null)
      .input('resultado',        sql.VarChar(30),  datos.resultado)
      .input('mensaje',          sql.NVarChar(1000), datos.mensaje ?? null)
      .input('idUsuario',        sql.Int,          datos.idUsuario ?? null)
      .input('usuarioNombre',    sql.VarChar(100), datos.usuarioNombre ?? null)
      .input('idDocumento',      sql.Int,          datos.idDocumento ?? null)
      .input('ticketFirmaGov',   sql.VarChar(100), datos.ticketFirmaGov ?? null)
      .input('requestPayload',   sql.NVarChar(sql.MAX), datos.requestPayload != null ? JSON.stringify(enmascararPayload(datos.requestPayload)) : null)
      .input('responsePayload',  sql.NVarChar(sql.MAX), datos.responsePayload != null ? JSON.stringify(enmascararPayload(datos.responsePayload)) : null)
      .input('tiempoRespuestaMs', sql.Int,         datos.tiempoRespuestaMs ?? null)
      .input('stackTrace',       sql.NVarChar(sql.MAX), datos.stackTrace ?? null)
      .input('recomendacion',    sql.NVarChar(500), datos.recomendacion ?? null)
      .input('idHistorial',      sql.Int,          datos.idHistorial ?? null)
      .query<{ id: number }>(`
        INSERT INTO firma_gob_logs
          (ambiente, accion, metodo_http, endpoint, codigo_respuesta, resultado, mensaje,
           id_usuario, usuario_nombre, id_documento, ticket_firmagov,
           request_payload, response_payload, tiempo_respuesta_ms, stack_trace, recomendacion, id_historial)
        OUTPUT INSERTED.id AS id
        VALUES
          (@ambiente, @accion, @metodoHttp, @endpoint, @codigoRespuesta, @resultado, @mensaje,
           @idUsuario, @usuarioNombre, @idDocumento, @ticketFirmaGov,
           @requestPayload, @responsePayload, @tiempoRespuestaMs, @stackTrace, @recomendacion, @idHistorial)
      `);
    return result.recordset[0]?.id ?? null;
  } catch (e) {
    logger.error(`[FirmaGob][Logs] No se pudo registrar log (accion=${datos.accion}): ${String(e)}`);
    return null;
  }
}

// ── Formato de payload JWT según manual oficial Firma.gob (v18, feb-2026) ──
// El manual exige 'expiration' como string ISO en hora de Chile
// (YYYY-MM-DDTHH:MM:SS, sin offset) y 'run' sin puntos, guión ni dígito
// verificador. Usado por el Nivel 3 ("Validar credenciales") para probar
// el formato documentado de forma aislada, sin afectar /solicitar.

/** Formatea una fecha como YYYY-MM-DDTHH:MM:SS en hora de Santiago de Chile. */
export function formatearExpirationChile(fecha: Date): string {
  const formatter = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'America/Santiago',
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit',
    hour12: false,
  });
  const partes = formatter.formatToParts(fecha);
  const obtener = (tipo: string) => partes.find(p => p.type === tipo)?.value ?? '00';
  const hora = obtener('hour') === '24' ? '00' : obtener('hour');
  return `${obtener('year')}-${obtener('month')}-${obtener('day')}T${hora}:${obtener('minute')}:${obtener('second')}`;
}

/** Quita puntos, espacios, guión y dígito verificador de un RUT (ej: "15.762.009-6" → "15762009"). */
export function limpiarRunFirmaGov(run: string): string {
  const limpio = run.replace(/[.\s]/g, '');
  return limpio.includes('-') ? limpio.split('-')[0] : limpio.slice(0, -1);
}

// ── PDF de prueba para validación de credenciales (Nivel 3) ────────────────
// Genera un PDF mínimo válido en memoria (con tabla xref correcta calculada
// dinámicamente), usado únicamente para probar que FirmaGov acepta el JWT y
// el payload — nunca se asocia a un documento ni se guarda en archivo_digital.

export function construirPdfPrueba(): Buffer {
  const objetos: string[] = [
    '<< /Type /Catalog /Pages 2 0 R >>',
    '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
    '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 320 160] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
    '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
  ];

  const texto = 'VALIDACION DE CREDENCIALES DOC360 - NO VALIDO PARA TRAMITES';
  const stream = `BT /F1 10 Tf 10 80 Td (${texto}) Tj ET`;
  objetos.push(`<< /Length ${stream.length} >>\nstream\n${stream}\nendstream`);

  let pdf = '%PDF-1.4\n';
  const offsets: number[] = [];
  objetos.forEach((cuerpo, i) => {
    offsets.push(Buffer.byteLength(pdf, 'latin1'));
    pdf += `${i + 1} 0 obj\n${cuerpo}\nendobj\n`;
  });

  const xrefOffset = Buffer.byteLength(pdf, 'latin1');
  const totalObjetos = objetos.length + 1;
  pdf += `xref\n0 ${totalObjetos}\n0000000000 65535 f \n`;
  for (const offset of offsets) {
    pdf += `${offset.toString().padStart(10, '0')} 00000 n \n`;
  }
  pdf += `trailer\n<< /Size ${totalObjetos} /Root 1 0 R >>\nstartxref\n${xrefOffset}\n%%EOF`;

  return Buffer.from(pdf, 'latin1');
}

// ── Reversión de documento cuando FirmaGov no firma exitosamente ──────────
// El documento/memo/correlativo se crean ANTES de llamar a FirmaGov (porque
// el PDF que se envía a firmar necesita el correlativo definitivo). Si
// FirmaGov falla, ese documento no debe quedar a medio camino — sin firma,
// sin despachar, con un PDF con el sello visual de firma electrónica ya
// grabado (aunque nunca se firmó realmente). Esta función deshace esa
// creación por completo. El correlativo se calcula como MAX(numero de memos
// con documento activo)+1 (ver memorandum.routes.ts /confirmar) — al borrar
// memo_generado y documento más abajo, el número queda libre automáticamente
// para el siguiente intento, sin necesidad de tocar ningún contador. Los
// logs técnicos (firma_gob_historial/firma_gob_logs) se conservan para
// auditoría, solo se desvincula la referencia al documento eliminado.
export async function revertirDocumentoSinFirmar(pool: Pool, idDocumento: number, uploadDir: string): Promise<void> {
  try {
    const archivosRes = await pool.request()
      .input('idDoc', sql.Int, idDocumento)
      .query<{ ruta: string | null }>('SELECT ruta FROM archivo_digital WHERE id_documento = @idDoc');

    for (const fila of archivosRes.recordset) {
      if (!fila.ruta) continue;
      try { fs.unlinkSync(path.join(uploadDir, fila.ruta)); } catch { /* best-effort */ }
    }

    const memoRes = await pool.request()
      .input('idDoc', sql.Int, idDocumento)
      .query<{ anio: number; numero: number; id_dependencia_origen: number | null }>(
        'SELECT anio, numero, id_dependencia_origen FROM memo_generado WHERE id_documento = @idDoc'
      );
    const memo = memoRes.recordset[0];

    await pool.request()
      .input('idDoc', sql.Int, idDocumento)
      .query('UPDATE firma_gob_historial SET id_documento = NULL WHERE id_documento = @idDoc');
    await pool.request()
      .input('idDoc', sql.Int, idDocumento)
      .query('UPDATE memorandum_firma_simple SET id_documento = NULL WHERE id_documento = @idDoc');

    await pool.request().input('idDoc', sql.Int, idDocumento)
      .query('DELETE FROM documento_destino WHERE id_documento = @idDoc');
    await pool.request().input('idDoc', sql.Int, idDocumento)
      .query('DELETE FROM tramite WHERE id_documento = @idDoc');
    await pool.request().input('idDoc', sql.Int, idDocumento)
      .query('DELETE FROM memo_generado WHERE id_documento = @idDoc');
    await pool.request().input('idDoc', sql.Int, idDocumento)
      .query('DELETE FROM archivo_digital WHERE id_documento = @idDoc');
    await pool.request().input('idDoc', sql.Int, idDocumento)
      .query('DELETE FROM documento WHERE id_documento = @idDoc');

    if (memo) {
      // El correlativo se calcula como MAX(numero activo)+1 (ver
      // memorandum.routes.ts /confirmar) — al borrar memo_generado y
      // documento arriba, este número queda automáticamente libre para el
      // próximo memo del mismo año+servicio. No requiere ningún paso extra.
      logger.info(`[FirmaGov] Documento ${idDocumento} revertido tras fallo de firma. Número ${memo.numero} (año ${memo.anio}, servicio ${memo.id_dependencia_origen ?? 'GEN'}) liberado para reuso.`);
    } else {
      logger.info(`[FirmaGov] Documento ${idDocumento} revertido tras fallo de firma (sin memo_generado asociado).`);
    }
  } catch (e) {
    logger.error(`[FirmaGov] No se pudo revertir el documento ${idDocumento} tras fallo de firma: ${e}`);
  }
}
