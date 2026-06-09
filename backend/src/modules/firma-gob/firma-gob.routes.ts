import { Router, Request, Response, NextFunction } from 'express';
import crypto from 'crypto';
import fs from 'fs';
import path from 'path';
import jwt from 'jsonwebtoken';
import { requireAuth, requireRole } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendError, sendPaginated, buildPaginationMeta } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { env } from '../../config/env';
import { logger } from '../../shared/utils/logger';

const router = Router();
router.use(requireAuth);

const soloAdmin = [requireRole('admin')];

type Ambiente = 'TEST' | 'PRODUCCION';
const AMBIENTES: Ambiente[] = ['TEST', 'PRODUCCION'];

// ── GET /firma-gob/config ──────────────────────────────────────────
// Devuelve configuración de ambos ambientes (sin secretos expuestos)
router.get('/config', ...soloAdmin, async (_req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const result = await pool.request().query<{
      id: number; ambiente: string; url_api: string | null;
      entity: string | null; purpose: string | null;
      max_reintentos: number; segundos_entre_reintentos: number;
      activo: boolean; fecha_update: string;
      tiene_token: boolean; tiene_jwt_secret: boolean;
    }>(`
      SELECT
        id, ambiente, url_api, entity, purpose,
        max_reintentos, segundos_entre_reintentos, activo,
        CONVERT(VARCHAR, fecha_update, 120) AS fecha_update,
        CASE WHEN api_token_key IS NOT NULL AND LEN(api_token_key) > 0 THEN 1 ELSE 0 END AS tiene_token,
        CASE WHEN jwt_secret    IS NOT NULL AND LEN(jwt_secret)    > 0 THEN 1 ELSE 0 END AS tiene_jwt_secret
      FROM firma_gob_config
      ORDER BY ambiente
    `);
    sendSuccess(res, result.recordset);
  } catch (e) { next(e); }
});

// ── PATCH /firma-gob/config/:ambiente ─────────────────────────────
// Actualiza configuración de un ambiente. Los secretos solo se
// actualizan si se envían; si viene null se conserva el valor actual.
router.patch('/config/:ambiente', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const ambiente = req.params.ambiente.toUpperCase() as Ambiente;
    if (!AMBIENTES.includes(ambiente)) {
      sendError(res, 'Ambiente inválido. Use TEST o PRODUCCION', 400);
      return;
    }

    const body = req.body as {
      urlApi?:                  string | null;
      entity?:                  string | null;
      purpose?:                 string | null;
      apiTokenKey?:             string | null;
      jwtSecret?:               string | null;
      maxReintentos?:           number;
      segundosEntreReintentos?: number;
      activo?:                  boolean;
    };

    if (body.maxReintentos !== undefined && (body.maxReintentos < 1 || body.maxReintentos > 10)) {
      sendError(res, 'maxReintentos debe estar entre 1 y 10', 400);
      return;
    }
    if (body.segundosEntreReintentos !== undefined && (body.segundosEntreReintentos < 5 || body.segundosEntreReintentos > 300)) {
      sendError(res, 'segundosEntreReintentos debe estar entre 5 y 300', 400);
      return;
    }

    const pool = await getPool();

    // Construir UPDATE dinámico según los campos enviados
    const setParts: string[] = ['fecha_update = GETDATE()'];
    const req2 = pool.request().input('amb', sql.VarChar(20), ambiente);

    if (body.urlApi !== undefined) {
      setParts.push('url_api = @urlApi');
      req2.input('urlApi', sql.VarChar(255), body.urlApi ?? null);
    }
    if (body.entity !== undefined) {
      setParts.push('entity = @entity');
      req2.input('entity', sql.VarChar(100), body.entity ?? null);
    }
    if (body.purpose !== undefined) {
      setParts.push('purpose = @purpose');
      req2.input('purpose', sql.VarChar(255), body.purpose ?? null);
    }
    if (body.apiTokenKey !== undefined && body.apiTokenKey !== null && body.apiTokenKey !== '') {
      setParts.push('api_token_key = @apiTokenKey');
      req2.input('apiTokenKey', sql.VarChar(500), body.apiTokenKey);
    }
    if (body.jwtSecret !== undefined && body.jwtSecret !== null && body.jwtSecret !== '') {
      setParts.push('jwt_secret = @jwtSecret');
      req2.input('jwtSecret', sql.VarChar(500), body.jwtSecret);
    }
    if (body.maxReintentos !== undefined) {
      setParts.push('max_reintentos = @maxReintentos');
      req2.input('maxReintentos', sql.Int, body.maxReintentos);
    }
    if (body.segundosEntreReintentos !== undefined) {
      setParts.push('segundos_entre_reintentos = @segs');
      req2.input('segs', sql.Int, body.segundosEntreReintentos);
    }
    if (body.activo !== undefined) {
      setParts.push('activo = @activo');
      req2.input('activo', sql.Bit, body.activo ? 1 : 0);
    }

    await req2.query(`UPDATE firma_gob_config SET ${setParts.join(', ')} WHERE ambiente = @amb`);
    sendSuccess(res, null, 'Configuración actualizada');
  } catch (e) { next(e); }
});

// ── POST /firma-gob/config/:ambiente/limpiar-secreto ─────────────
// Elimina el token o jwt_secret de un ambiente (operación explícita)
router.post('/config/:ambiente/limpiar-secreto', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const ambiente = req.params.ambiente.toUpperCase() as Ambiente;
    const { campo } = req.body as { campo: 'apiTokenKey' | 'jwtSecret' };
    if (!AMBIENTES.includes(ambiente)) { sendError(res, 'Ambiente inválido', 400); return; }

    const colMap: Record<string, string> = {
      apiTokenKey: 'api_token_key',
      jwtSecret:   'jwt_secret',
    };
    const col = colMap[campo];
    if (!col) { sendError(res, 'campo inválido', 400); return; }

    const pool = await getPool();
    await pool.request()
      .input('amb', sql.VarChar(20), ambiente)
      .query(`UPDATE firma_gob_config SET ${col} = NULL, fecha_update = GETDATE() WHERE ambiente = @amb`);

    sendSuccess(res, null, 'Secreto eliminado');
  } catch (e) { next(e); }
});

// ── GET /firma-gob/historial ───────────────────────────────────────
router.get('/historial', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pagina   = Math.max(1, parseInt(String(req.query.pagina ?? '1')));
    const porPagina = 20;
    const offset   = (pagina - 1) * porPagina;
    const ambiente = req.query.ambiente ? String(req.query.ambiente).toUpperCase() : null;
    const estado   = req.query.estado   ? String(req.query.estado)                : null;

    const pool = await getPool();

    const whereClause = [
      ambiente ? 'AND h.ambiente = @amb' : '',
      estado   ? 'AND h.estado   = @est' : '',
    ].filter(Boolean).join(' ');

    const baseReq = pool.request()
      .input('offset',   sql.Int, offset)
      .input('pageSize', sql.Int, porPagina);
    if (ambiente) baseReq.input('amb', sql.VarChar(20), ambiente);
    if (estado)   baseReq.input('est', sql.VarChar(30), estado);

    const [dataRes, countRes] = await Promise.all([
      baseReq.query<{
        id: number; id_documento: number | null; correlativo_memo: string | null;
        nombre_firmante: string | null; tipo_firmante: string | null;
        ambiente: string | null; estado: string; intentos_realizados: number;
        resultado: string | null; fecha_creacion: string; fecha_firma: string | null;
      }>(`
        SELECT TOP (@pageSize) *
        FROM (
          SELECT ROW_NUMBER() OVER (ORDER BY h.fecha_creacion DESC) AS rn,
            h.id, h.id_documento, h.correlativo_memo, h.nombre_firmante, h.tipo_firmante,
            h.ambiente, h.estado, h.intentos_realizados, h.resultado,
            CONVERT(VARCHAR, h.fecha_creacion, 120) AS fecha_creacion,
            CONVERT(VARCHAR, h.fecha_firma,    120) AS fecha_firma
          FROM firma_gob_historial h
          WHERE 1=1 ${whereClause}
        ) t
        WHERE rn > @offset
      `),
      pool.request()
        .input('amb2', sql.VarChar(20), ambiente)
        .input('est2', sql.VarChar(30), estado)
        .query<{ total: number }>(`
          SELECT COUNT(*) AS total
          FROM firma_gob_historial h
          WHERE 1=1
            ${ambiente ? 'AND h.ambiente = @amb2' : ''}
            ${estado   ? 'AND h.estado   = @est2' : ''}
        `),
    ]);

    const total = countRes.recordset[0].total;
    sendPaginated(res, dataRes.recordset, buildPaginationMeta(total, pagina, porPagina));
  } catch (e) { next(e); }
});

// ── POST /firma-gob/test-conexion ─────────────────────────────────
// Verifica alcanzabilidad del endpoint de Firma.gob
router.post('/test-conexion', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { ambiente } = req.body as { ambiente?: string };
    const amb = (ambiente ?? 'TEST').toUpperCase() as Ambiente;
    if (!AMBIENTES.includes(amb)) { sendError(res, 'Ambiente inválido', 400); return; }

    const pool = await getPool();
    const result = await pool.request()
      .input('amb', sql.VarChar(20), amb)
      .query<{ url_api: string | null; activo: boolean }>('SELECT url_api, activo FROM firma_gob_config WHERE ambiente = @amb');

    const cfg = result.recordset[0];
    if (!cfg || !cfg.url_api) {
      sendSuccess(res, { ok: false, mensaje: 'URL de API no configurada para este ambiente' });
      return;
    }
    if (!cfg.activo) {
      sendSuccess(res, { ok: false, mensaje: 'El ambiente está inactivo' });
      return;
    }

    // Intento de conexión real (HEAD request — no envía datos)
    try {
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 5000);
      const response = await fetch(cfg.url_api, {
        method: 'HEAD',
        signal: controller.signal,
      });
      clearTimeout(timeout);

      const reachable          = response.status < 500;
      const isMethodNotAllowed = response.status === 405;
      let mensaje: string;
      if (response.ok) {
        mensaje = 'Servidor alcanzable y respondiendo correctamente';
      } else if (isMethodNotAllowed) {
        mensaje = `Servidor alcanzable (status ${response.status} — el endpoint existe pero no acepta HEAD). La conectividad está OK; las credenciales y payload solo se validan en un envío real.`;
      } else {
        mensaje = `Servidor respondió con status ${response.status} — puede indicar error de URL o configuración`;
      }
      sendSuccess(res, { ok: reachable, status: response.status, mensaje });
    } catch (_err) {
      sendSuccess(res, { ok: false, mensaje: 'No se pudo conectar al endpoint de Firma.gob' });
    }
  } catch (e) { next(e); }
});

// ── POST /firma-gob/solicitar ─────────────────────────────────
// Envía un PDF a FirmaGov (modo desatendido/síncrono), guarda el PDF
// firmado como nuevo archivo_digital, vincula a memo_generado y despacha
// el documento (cambia estado de 1→2).
// Body: { idDocumento, correlativoMemo, idArchivoOriginal, tipoFirmante, run }
router.post('/solicitar', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const user = (req as unknown as AuthenticatedRequest).user;
    const body = req.body as {
      idDocumento:       number;
      correlativoMemo:   string;
      idArchivoOriginal: number;
      tipoFirmante:      string;
      nombreFirmante:    string;
      run:               string;   // RUT sin puntos con dígito verificador, ej: "12345678-9"
    };

    if (!body.idDocumento || !body.correlativoMemo || !body.idArchivoOriginal || !body.run) {
      sendError(res, 'idDocumento, correlativoMemo, idArchivoOriginal y run son requeridos', 400);
      return;
    }

    const pool = await getPool();

    // ── 1. Obtener configuración activa ───────────────────────
    const cfgRes = await pool.request().query<{
      id: number; ambiente: string; url_api: string;
      entity: string; purpose: string;
      api_token_key: string; jwt_secret: string;
    }>(`SELECT TOP 1 id, ambiente, url_api, entity, purpose, api_token_key, jwt_secret
        FROM firma_gob_config
        WHERE activo = 1 AND url_api IS NOT NULL
          AND api_token_key IS NOT NULL AND jwt_secret IS NOT NULL
        ORDER BY CASE ambiente WHEN 'PRODUCCION' THEN 0 ELSE 1 END`);

    const cfg = cfgRes.recordset[0];
    if (!cfg) {
      sendError(res, 'No hay configuración activa de FirmaGov. Configúrala en Administración → FirmaGov.', 503);
      return;
    }

    // ── 2. Leer el PDF original del disco ────────────────────
    const arqRes = await pool.request()
      .input('idArq', sql.Int, body.idArchivoOriginal)
      .input('idDoc', sql.Int, body.idDocumento)
      .query<{ ruta: string; archivo: string }>(`
        SELECT ruta, archivo FROM archivo_digital
        WHERE id_archivo_digital = @idArq AND id_documento = @idDoc
      `);

    const arq = arqRes.recordset[0];
    if (!arq) {
      sendError(res, 'Archivo original no encontrado', 404);
      return;
    }

    const uploadDir = path.resolve(env.UPLOAD_DIR);
    const pdfPath   = path.join(uploadDir, arq.ruta);

    if (!fs.existsSync(pdfPath)) {
      sendError(res, 'Archivo físico no encontrado en el servidor', 404);
      return;
    }

    const pdfBuffer = fs.readFileSync(pdfPath);
    const pdfBase64 = pdfBuffer.toString('base64');
    const checksum  = crypto.createHash('sha256').update(pdfBuffer).digest('hex');

    // ── 3. Construir JWT para FirmaGov ───────────────────────
    // El RUN se limpia (solo dígitos + guion + verificador)
    const runLimpio = body.run.trim();
    const nowSec    = Math.floor(Date.now() / 1000);

    // FirmaGov espera el campo 'expiration' sin milisegundos ni sufijo de zona (no ISO completo).
    // Formato correcto: "YYYY-MM-DDTHH:mm:ss" — se obtiene cortando ".mmmZ" del toISOString().
    const expirationStr = new Date(Date.now() + 30 * 60 * 1000)
      .toISOString()
      .replace(/\.\d{3}Z$/, '');

    const jwtPayload = {
      run:         runLimpio,
      entity:      cfg.entity,
      purpose:     cfg.purpose,
      expiration:  expirationStr,
      iat:         nowSec,
      exp:         nowSec + 1800,
    };

    const firmaJwt = jwt.sign(jwtPayload, cfg.jwt_secret, { algorithm: 'HS256' });

    // ── 4. Registrar en historial (estado: Enviado) ───────────
    const histRes = await pool.request()
      .input('idDoc',     sql.Int,          body.idDocumento)
      .input('corrMemo',  sql.VarChar(30),  body.correlativoMemo)
      .input('nomFirm',   sql.VarChar(100), body.nombreFirmante ?? '')
      .input('tipoFirm',  sql.VarChar(30),  body.tipoFirmante   ?? '')
      .input('ambiente',  sql.VarChar(20),  cfg.ambiente)
      .query<{ id: number }>(`
        INSERT INTO firma_gob_historial
          (id_documento, correlativo_memo, nombre_firmante, tipo_firmante,
           ambiente, estado, intentos_realizados, fecha_creacion)
        OUTPUT INSERTED.id AS id
        VALUES
          (@idDoc, @corrMemo, @nomFirm, @tipoFirm,
           @ambiente, 'Enviado', 1, GETDATE())
      `);
    const idHistorial = histRes.recordset[0].id;

    // ── 5. Llamar a FirmaGov ─────────────────────────────────
    let signedBase64: string;
    try {
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 60_000);

      const firmResponse = await fetch(cfg.url_api, {
        method:  'POST',
        headers: {
          'Content-Type':  'application/json',
          'Authorization': `Token ${cfg.api_token_key}`,
        },
        signal: controller.signal,
        body: JSON.stringify({
          api_token_key: cfg.api_token_key,
          token:         firmaJwt,
          files: [
            {
              'content-type': 'application/pdf',
              content:        pdfBase64,
              description:    body.correlativoMemo,
              checksum:       checksum,
            },
          ],
        }),
      });

      clearTimeout(timeout);

      if (!firmResponse.ok) {
        const errText = await firmResponse.text().catch(() => '');
        logger.warn('[FirmaGov] Error HTTP %d | doc=%d | ambiente=%s | respuesta: %s',
          firmResponse.status, body.idDocumento, cfg.ambiente,
          errText.substring(0, 300)
        );
        await pool.request()
          .input('id',  sql.Int,         idHistorial)
          .input('res', sql.VarChar(500), `HTTP ${firmResponse.status}: ${errText.substring(0, 400)}`)
          .query(`UPDATE firma_gob_historial SET estado='Error', resultado=@res, fecha_firma=GETDATE() WHERE id=@id`);
        sendError(res, `FirmaGov respondió con error ${firmResponse.status}. Revisa la configuración.`, 502);
        return;
      }

      const firmData = await firmResponse.json() as {
        files?: Array<{ content?: string; checksum?: string }>;
        error?: string;
      };

      if (!firmData.files || !firmData.files[0]?.content) {
        await pool.request()
          .input('id',  sql.Int,         idHistorial)
          .input('res', sql.VarChar(500), firmData.error ?? 'Respuesta sin archivo firmado')
          .query(`UPDATE firma_gob_historial SET estado='Error', resultado=@res, fecha_firma=GETDATE() WHERE id=@id`);
        sendError(res, firmData.error ?? 'FirmaGov no devolvió el PDF firmado', 502);
        return;
      }

      signedBase64 = firmData.files[0].content;
    } catch (fetchErr: unknown) {
      const msg = fetchErr instanceof Error ? fetchErr.message : String(fetchErr);
      await pool.request()
        .input('id',  sql.Int,         idHistorial)
        .input('res', sql.VarChar(500), msg.substring(0, 400))
        .query(`UPDATE firma_gob_historial SET estado='Error', resultado=@res, fecha_firma=GETDATE() WHERE id=@id`);
      sendError(res, `No se pudo conectar a FirmaGov: ${msg}`, 503);
      return;
    }

    // ── 6. Guardar PDF firmado en disco ───────────────────────
    const signedBuffer   = Buffer.from(signedBase64, 'base64');
    const ts             = Date.now().toString().slice(-8);
    const signedFilename = `${ts}f.pdf`;                 // sufijo 'f' indica firmado
    const signedPath     = path.join(uploadDir, signedFilename);
    fs.writeFileSync(signedPath, signedBuffer);

    // ── 7. Registrar en archivo_digital ──────────────────────
    const insertArqRes = await pool.request()
      .input('idDoc',  sql.Int,          body.idDocumento)
      .input('idUsr',  sql.Int,          user.idUsuario)
      .input('arq',    sql.VarChar(50),  signedFilename)
      .input('ruta',   sql.VarChar(50),  signedFilename)
      .input('tam',    sql.Int,          signedBuffer.length)
      .query<{ id_archivo_digital: number }>(`
        INSERT INTO archivo_digital
          (id_documento, id_usuario, archivo, ruta, tamano, tipo_mime, fecha_sistema, fecha_update)
        OUTPUT INSERTED.id_archivo_digital
        VALUES
          (@idDoc, @idUsr, @arq, @ruta, @tam, 'application/pdf', GETDATE(), GETDATE())
      `);
    const idArchivoFirmado = insertArqRes.recordset[0].id_archivo_digital;

    // ── 8. Vincular PDF firmado a memo_generado ───────────────
    await pool.request()
      .input('idDoc',  sql.Int,          body.idDocumento)
      .input('idArq',  sql.Int,          idArchivoFirmado)
      .query(`
        UPDATE memo_generado
        SET    id_archivo_firmado = @idArq, estado_firma = 'firmado'
        WHERE  id_documento = @idDoc
      `);

    // ── 9. Despachar el documento (estado 1→2) ─────────────────
    // Actualiza documento + trámite(s) a estado 2 (Despachado)
    await pool.request()
      .input('idDoc', sql.Int, body.idDocumento)
      .query(`
        UPDATE documento
        SET    id_estado_documento = 2, fecha_update = GETDATE()
        WHERE  id_documento = @idDoc AND id_estado_documento = 1
      `);

    await pool.request()
      .input('idDoc', sql.Int, body.idDocumento)
      .query(`
        UPDATE tramite
        SET    id_estado_tramite = 2, fecha_despacho = GETDATE(), fecha_update = GETDATE()
        WHERE  id_documento = @idDoc AND id_estado_tramite = 1
      `);

    // ── 10. Actualizar historial a Firmado ────────────────────
    await pool.request()
      .input('id',    sql.Int,         idHistorial)
      .input('idArq', sql.Int,         idArchivoFirmado)
      .query(`
        UPDATE firma_gob_historial
        SET    estado = 'Firmado', resultado = 'PDF firmado guardado',
               fecha_firma = GETDATE()
        WHERE  id = @id
      `);

    sendSuccess(res, {
      idArchivoFirmado,
      filename: signedFilename,
      ambiente: cfg.ambiente,
    }, 'Documento firmado y despachado correctamente');
  } catch (e) { next(e); }
});

export default router;
