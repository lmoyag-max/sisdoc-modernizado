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
import { interpretarStatus, registrarLog, construirPdfPrueba, formatearExpirationChile, limpiarRunFirmaGov, revertirDocumentoSinFirmar, ResultadoLog } from './firma-gob.utils';

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
// Diagnóstico en 3 niveles:
//  - Sin "nivel" (uso normal del botón "Probar conexión"): ejecuta UNA
//    petición HEAD y deriva de su resultado el Nivel 1 (conectividad básica)
//    y el Nivel 2 (comportamiento del endpoint según el código HTTP).
//  - "nivel: 3" (botón "Validar credenciales — envío real"): solo TEST,
//    requiere "confirmar: true" y "runPrueba" — envía un POST real con un
//    PDF de prueba para validar JWT/credenciales contra FirmaGov.
router.post('/test-conexion', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const user = (req as unknown as AuthenticatedRequest).user;
    const body = req.body as { ambiente?: string; nivel?: number; runPrueba?: string; confirmar?: boolean };
    const amb = (body.ambiente ?? 'TEST').toUpperCase() as Ambiente;
    if (!AMBIENTES.includes(amb)) { sendError(res, 'Ambiente inválido', 400); return; }

    const pool = await getPool();

    // ── Nivel 3: validación real de credenciales (envío POST controlado) ──
    if (body.nivel === 3) {
      if (amb !== 'TEST') {
        sendError(res, 'La validación de credenciales (Nivel 3) solo está disponible en ambiente TEST', 400);
        return;
      }
      if (!body.confirmar) {
        sendError(res, 'Debe confirmar la acción antes de enviar una prueba real a FirmaGov', 400);
        return;
      }
      const runPrueba = (body.runPrueba ?? '').trim();
      if (!runPrueba) {
        sendError(res, 'runPrueba es requerido para la validación de Nivel 3', 400);
        return;
      }

      const cfgRes = await pool.request()
        .input('amb', sql.VarChar(20), amb)
        .query<{
          url_api: string | null; entity: string | null; purpose: string | null;
          api_token_key: string | null; jwt_secret: string | null; activo: boolean;
        }>('SELECT url_api, entity, purpose, api_token_key, jwt_secret, activo FROM firma_gob_config WHERE ambiente = @amb');

      const cfg = cfgRes.recordset[0];
      if (!cfg || !cfg.url_api || !cfg.api_token_key || !cfg.jwt_secret || !cfg.entity || !cfg.purpose) {
        sendSuccess(res, { ok: false, ambiente: amb, nivel: 3, mensaje: 'Configuración incompleta para TEST (URL, entity, purpose, token o JWT secret faltantes)' });
        return;
      }
      if (!cfg.activo) {
        sendSuccess(res, { ok: false, ambiente: amb, nivel: 3, mensaje: 'El ambiente TEST está inactivo' });
        return;
      }

      // Payload spec-compliant según manual oficial v18 (feb-2026): 'expiration'
      // como string ISO en hora de Chile (no timestamp Unix), 'run' sin puntos,
      // guión ni dígito verificador, y solo los 4 campos documentados (sin
      // 'iat'/'exp' adicionales). Se prueba aislado aquí — /solicitar no se toca.
      const runLimpio = limpiarRunFirmaGov(runPrueba);
      const expirationChile = formatearExpirationChile(new Date(Date.now() + 25 * 60 * 1000));
      const jwtPayload = { run: runLimpio, entity: cfg.entity, purpose: cfg.purpose, expiration: expirationChile };
      const firmaJwt = jwt.sign(jwtPayload, cfg.jwt_secret, { algorithm: 'HS256', noTimestamp: true });

      const pdfBuffer = construirPdfPrueba();
      const pdfBase64 = pdfBuffer.toString('base64');
      const checksum  = crypto.createHash('sha256').update(pdfBuffer).digest('hex');

      const requestBody = {
        api_token_key: cfg.api_token_key,
        token:         firmaJwt,
        files: [
          {
            'content-type': 'application/pdf',
            content:        pdfBase64,
            description:    `VALIDACION-CREDENCIALES-${Date.now()}`,
            checksum,
          },
        ],
      };

      const inicio = Date.now();
      try {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 60_000);
        // Sin header 'Authorization' adicional: el manual solo documenta
        // 'api_token_key' dentro del body JSON.
        const response = await fetch(cfg.url_api, {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          signal:  controller.signal,
          body:    JSON.stringify(requestBody),
        });
        clearTimeout(timeout);
        const tiempoMs = Date.now() - inicio;

        const textoRespuesta = await response.text().catch(() => '');
        let dataRespuesta: unknown = textoRespuesta;
        try { dataRespuesta = JSON.parse(textoRespuesta); } catch { /* respuesta no-JSON, se conserva como texto */ }

        const interpretacion = interpretarStatus(response.status);
        // El esquema de respuesta documentado usa 'idSolicitud' como
        // identificador de nivel superior (no 'id'); se mantienen
        // 'session_token'/'id' como fallback por si la API real difiere.
        const ticketFirmaGov = (() => {
          if (!dataRespuesta || typeof dataRespuesta !== 'object') return null;
          const d = dataRespuesta as Record<string, unknown>;
          const valor = d.idSolicitud ?? d.session_token ?? d.id;
          return valor != null ? String(valor) : null;
        })();

        const idLog = await registrarLog(pool, {
          ambiente:         amb,
          accion:           'test-conexion-nivel3',
          metodoHttp:       'POST',
          endpoint:         cfg.url_api,
          codigoRespuesta:  response.status,
          resultado:        interpretacion.resultado,
          mensaje:          interpretacion.mensaje,
          idUsuario:        user.idUsuario,
          usuarioNombre:    user.usuario,
          ticketFirmaGov,
          requestPayload:   requestBody,
          responsePayload:  dataRespuesta,
          tiempoRespuestaMs: tiempoMs,
          recomendacion:    interpretacion.recomendacion,
        });

        sendSuccess(res, {
          ok: response.ok,
          ambiente: amb,
          nivel: 3,
          status: response.status,
          mensaje: interpretacion.mensaje,
          recomendacion: interpretacion.recomendacion,
          ticketFirmaGov,
          idLog,
        });
      } catch (fetchErr: unknown) {
        const tiempoMs = Date.now() - inicio;
        const msg = fetchErr instanceof Error ? fetchErr.message : String(fetchErr);
        const idLog = await registrarLog(pool, {
          ambiente:      amb,
          accion:        'test-conexion-nivel3',
          metodoHttp:    'POST',
          endpoint:      cfg.url_api,
          resultado:     'Error',
          mensaje:       `No se pudo conectar a FirmaGov: ${msg}`,
          idUsuario:     user.idUsuario,
          usuarioNombre: user.usuario,
          requestPayload: requestBody,
          tiempoRespuestaMs: tiempoMs,
          stackTrace:    fetchErr instanceof Error ? (fetchErr.stack ?? null) : null,
          recomendacion: 'Verificar la URL configurada y la conectividad de red hacia FirmaGov.',
        });
        sendSuccess(res, { ok: false, ambiente: amb, nivel: 3, mensaje: `No se pudo conectar a FirmaGov: ${msg}`, idLog });
      }
      return;
    }

    // ── Niveles 1 y 2: comportamiento del botón "Probar conexión" ──────────
    const result = await pool.request()
      .input('amb', sql.VarChar(20), amb)
      .query<{ url_api: string | null; activo: boolean }>('SELECT url_api, activo FROM firma_gob_config WHERE ambiente = @amb');

    const cfg = result.recordset[0];
    if (!cfg || !cfg.url_api) {
      sendSuccess(res, { ok: false, ambiente: amb, mensaje: 'URL de API no configurada para este ambiente' });
      return;
    }
    if (!cfg.activo) {
      sendSuccess(res, { ok: false, ambiente: amb, mensaje: 'El ambiente está inactivo' });
      return;
    }

    type NivelResultado = { nivel: number; ok: boolean; resultado: ResultadoLog; status?: number; mensaje: string; recomendacion?: string; idLog: number | null };
    const niveles: NivelResultado[] = [];

    // Una sola petición HEAD — no envía datos. Su resultado alimenta Nivel 1 y Nivel 2.
    try {
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 5000);
      const inicio = Date.now();
      const response = await fetch(cfg.url_api, { method: 'HEAD', signal: controller.signal });
      const tiempoMs = Date.now() - inicio;
      clearTimeout(timeout);

      // Nivel 1: conectividad básica — el servidor respondió (sea cual sea el código)
      const idLog1 = await registrarLog(pool, {
        ambiente: amb, accion: 'test-conexion-nivel1', metodoHttp: 'HEAD', endpoint: cfg.url_api,
        codigoRespuesta: response.status, resultado: 'Exitoso',
        mensaje: 'Servidor alcanzable a nivel de red', idUsuario: user.idUsuario, usuarioNombre: user.usuario,
        tiempoRespuestaMs: tiempoMs, recomendacion: 'Ninguna acción requerida.',
      });
      niveles.push({ nivel: 1, ok: true, resultado: 'Exitoso', status: response.status, mensaje: 'Servidor alcanzable a nivel de red', idLog: idLog1 });

      // Nivel 2: comportamiento del endpoint según el código HTTP recibido
      const interpretacion = interpretarStatus(response.status);
      const idLog2 = await registrarLog(pool, {
        ambiente: amb, accion: 'test-conexion-nivel2', metodoHttp: 'HEAD', endpoint: cfg.url_api,
        codigoRespuesta: response.status, resultado: interpretacion.resultado, mensaje: interpretacion.mensaje,
        idUsuario: user.idUsuario, usuarioNombre: user.usuario,
        tiempoRespuestaMs: tiempoMs, recomendacion: interpretacion.recomendacion,
      });
      niveles.push({
        nivel: 2, ok: interpretacion.resultado !== 'Error', resultado: interpretacion.resultado, status: response.status,
        mensaje: interpretacion.mensaje, recomendacion: interpretacion.recomendacion, idLog: idLog2,
      });

      sendSuccess(res, { ok: niveles.every((n) => n.ok), ambiente: amb, niveles });
    } catch (_err) {
      const msg = _err instanceof Error ? _err.message : String(_err);
      const idLog1 = await registrarLog(pool, {
        ambiente: amb, accion: 'test-conexion-nivel1', metodoHttp: 'HEAD', endpoint: cfg.url_api,
        resultado: 'Error', mensaje: `Servidor no alcanzable: ${msg}`,
        idUsuario: user.idUsuario, usuarioNombre: user.usuario,
        recomendacion: 'Verificar la URL configurada (url_api) y la conectividad de red.',
        stackTrace: _err instanceof Error ? (_err.stack ?? null) : null,
      });
      niveles.push({ nivel: 1, ok: false, resultado: 'Error', mensaje: `Servidor no alcanzable: ${msg}`, idLog: idLog1 });
      sendSuccess(res, { ok: false, ambiente: amb, niveles });
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
    // Formato según manual oficial v18 (feb-2026): 'expiration' como string
    // ISO en hora de Chile (no timestamp Unix), 'run' sin puntos/guión/dígito
    // verificador, y solo los 4 campos documentados (sin 'iat'/'exp' extra).
    // Validado vía Nivel 3 (test-conexion): este formato pasa de un 500
    // "Unhandled Error" a un 400 documentado por FirmaGov.
    const runLimpio       = limpiarRunFirmaGov(body.run);
    const expirationChile = formatearExpirationChile(new Date(Date.now() + 25 * 60 * 1000));

    const jwtPayload = {
      run:         runLimpio,
      entity:      cfg.entity,
      purpose:     cfg.purpose,
      expiration:  expirationChile,
    };

    // LOG DIAGNÓSTICO — payload sin el secret
    logger.debug(`[FirmaGov] JWT payload → run=${runLimpio} | entity=${cfg.entity} | purpose=${cfg.purpose} | expiration=${expirationChile}`);

    const firmaJwt = jwt.sign(jwtPayload, cfg.jwt_secret, { algorithm: 'HS256', noTimestamp: true });
    logger.debug(`[FirmaGov] JWT (header.payload sin secret): ${firmaJwt.split('.').slice(0,2).join('.')}`);

    // ── 4. Registrar en historial (estado: Enviado) ───────────
    const histRes = await pool.request()
      .input('idDoc',     sql.Int,          body.idDocumento)
      .input('corrMemo',  sql.VarChar(30),  body.correlativoMemo)
      // String vacío ('') en parámetros VarChar dispara un bug conocido del
      // driver mssql/tedious: "Data type 0xA7 has an invalid data length or
      // metadata length" (TDS RPC). Usar null evita el bug y es semánticamente
      // correcto — ambas columnas son nullable.
      .input('nomFirm',   sql.VarChar(100), body.nombreFirmante || null)
      .input('tipoFirm',  sql.VarChar(30),  body.tipoFirmante   || null)
      .input('ambiente',  sql.VarChar(20),  cfg.ambiente)
      .input('runFirm',   sql.VarChar(20),  runLimpio)
      .input('entity',    sql.VarChar(100), cfg.entity)
      .input('purpose',   sql.VarChar(255), cfg.purpose)
      .query<{ id: number }>(`
        INSERT INTO firma_gob_historial
          (id_documento, correlativo_memo, nombre_firmante, tipo_firmante,
           ambiente, estado, intentos_realizados, fecha_creacion,
           run_firmante, entity, purpose)
        OUTPUT INSERTED.id AS id
        VALUES
          (@idDoc, @corrMemo, @nomFirm, @tipoFirm,
           @ambiente, 'Enviado', 1, GETDATE(),
           @runFirm, @entity, @purpose)
      `);
    const idHistorial = histRes.recordset[0].id;

    // Payload reutilizado para los logs técnicos (firma_gob_logs) — se enmascara al guardarse
    const requestPayloadLog = {
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
    };

    // ── 5. Llamar a FirmaGov ─────────────────────────────────
    let signedBase64: string;
    const inicioFetch = Date.now();
    try {
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 60_000);

      // Sin header 'Authorization' adicional: el manual solo documenta
      // 'api_token_key' dentro del body JSON.
      const firmResponse = await fetch(cfg.url_api, {
        method:  'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        signal: controller.signal,
        body: JSON.stringify(requestPayloadLog),
      });

      clearTimeout(timeout);

      if (!firmResponse.ok) {
        const errText = await firmResponse.text().catch(() => '');
        logger.warn(`[FirmaGov] Error HTTP ${firmResponse.status} | doc=${body.idDocumento} | ambiente=${cfg.ambiente} | respuesta: ${errText.substring(0, 400)}`);
        await pool.request()
          .input('id',  sql.Int,         idHistorial)
          .input('res', sql.VarChar(500), `HTTP ${firmResponse.status}: ${errText.substring(0, 400)}`)
          .query(`UPDATE firma_gob_historial SET estado='Error', resultado=@res, fecha_firma=GETDATE() WHERE id=@id`);
        const interpretacion = interpretarStatus(firmResponse.status);
        await registrarLog(pool, {
          ambiente: cfg.ambiente, accion: 'solicitar-firma', metodoHttp: 'POST', endpoint: cfg.url_api,
          codigoRespuesta: firmResponse.status, resultado: interpretacion.resultado, mensaje: interpretacion.mensaje,
          idUsuario: user.idUsuario, usuarioNombre: user.usuario, idDocumento: body.idDocumento, idHistorial,
          requestPayload: requestPayloadLog, responsePayload: { status: firmResponse.status, body: errText.substring(0, 2000) },
          tiempoRespuestaMs: Date.now() - inicioFetch, recomendacion: interpretacion.recomendacion,
        });
        await revertirDocumentoSinFirmar(pool, body.idDocumento, uploadDir);
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
        await registrarLog(pool, {
          ambiente: cfg.ambiente, accion: 'solicitar-firma', metodoHttp: 'POST', endpoint: cfg.url_api,
          codigoRespuesta: firmResponse.status, resultado: 'Error',
          mensaje: firmData.error ?? 'FirmaGov no devolvió el PDF firmado',
          idUsuario: user.idUsuario, usuarioNombre: user.usuario, idDocumento: body.idDocumento, idHistorial,
          requestPayload: requestPayloadLog, responsePayload: firmData,
          tiempoRespuestaMs: Date.now() - inicioFetch,
          recomendacion: 'Verificar que FirmaGov devuelva el campo files[0].content con el PDF firmado.',
        });
        await revertirDocumentoSinFirmar(pool, body.idDocumento, uploadDir);
        sendError(res, firmData.error ?? 'FirmaGov no devolvió el PDF firmado', 502);
        return;
      }

      signedBase64 = firmData.files[0].content;

      // ── Log técnico de éxito (aditivo, no afecta el flujo) ──────
      // El esquema documentado usa 'idSolicitud' como identificador de nivel
      // superior (no 'id'); se mantienen 'session_token'/'id' como fallback.
      const ticketFirmaGov = (() => {
        const d = firmData as unknown as Record<string, unknown>;
        const valor = d.idSolicitud ?? d.session_token ?? d.id;
        return valor != null ? String(valor) : null;
      })();
      await registrarLog(pool, {
        ambiente: cfg.ambiente, accion: 'solicitar-firma', metodoHttp: 'POST', endpoint: cfg.url_api,
        codigoRespuesta: firmResponse.status, resultado: 'Exitoso', mensaje: 'Credenciales válidas y endpoint operativo',
        idUsuario: user.idUsuario, usuarioNombre: user.usuario, idDocumento: body.idDocumento, idHistorial,
        ticketFirmaGov, requestPayload: requestPayloadLog, responsePayload: firmData,
        tiempoRespuestaMs: Date.now() - inicioFetch, recomendacion: 'Ninguna acción requerida.',
      });
    } catch (fetchErr: unknown) {
      const msg = fetchErr instanceof Error ? fetchErr.message : String(fetchErr);
      await pool.request()
        .input('id',  sql.Int,         idHistorial)
        .input('res', sql.VarChar(500), msg.substring(0, 400))
        .query(`UPDATE firma_gob_historial SET estado='Error', resultado=@res, fecha_firma=GETDATE() WHERE id=@id`);
      await registrarLog(pool, {
        ambiente: cfg.ambiente, accion: 'solicitar-firma', metodoHttp: 'POST', endpoint: cfg.url_api,
        resultado: 'Error', mensaje: `No se pudo conectar a FirmaGov: ${msg}`,
        idUsuario: user.idUsuario, usuarioNombre: user.usuario, idDocumento: body.idDocumento, idHistorial,
        requestPayload: requestPayloadLog, tiempoRespuestaMs: Date.now() - inicioFetch,
        stackTrace: fetchErr instanceof Error ? (fetchErr.stack ?? null) : null,
        recomendacion: 'Verificar la URL configurada y la conectividad de red hacia FirmaGov.',
      });
      await revertirDocumentoSinFirmar(pool, body.idDocumento, uploadDir);
      sendError(res, `No se pudo conectar a FirmaGov: ${msg}`, 503);
      return;
    }

    // ── 6. Guardar PDF firmado en disco con el nombre oficial del memo ─
    // Se reemplaza el archivo físico del borrador (mismo registro de BD,
    // ver paso 7) en vez de crear un archivo nuevo — evita dejar dos
    // adjuntos (borrador + firmado) asociados al mismo documento.
    const signedBuffer = Buffer.from(signedBase64, 'base64');
    const nombreFinal   = `${body.correlativoMemo.replace(/[^A-Za-z0-9_-]/g, '_')}.pdf`.slice(0, 50);
    const signedPath    = path.join(uploadDir, nombreFinal);
    fs.writeFileSync(signedPath, signedBuffer);

    if (arq.ruta !== nombreFinal) {
      try { fs.unlinkSync(pdfPath); } catch (e) {
        logger.warn(`[FirmaGov] No se pudo eliminar el archivo físico del borrador (${pdfPath}): ${e}`);
      }
    }

    // ── 7. Reemplazar el archivo_digital del borrador por el firmado ──
    // Mismo id_archivo_digital que el borrador — queda un único adjunto.
    await pool.request()
      .input('id',   sql.Int,          body.idArchivoOriginal)
      .input('arq',  sql.VarChar(50),  nombreFinal)
      .input('ruta', sql.VarChar(50),  nombreFinal)
      .input('tam',  sql.Int,          signedBuffer.length)
      .query(`
        UPDATE archivo_digital
        SET    archivo = @arq, ruta = @ruta, tamano = @tam, fecha_update = GETDATE()
        WHERE  id_archivo_digital = @id
      `);
    const idArchivoFirmado = body.idArchivoOriginal;

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

    // Antes esto hacía UPDATE del trámite en estado 1 in-place, sin generar
    // ningún evento visible en la trazabilidad — un usuario no-admin no
    // podía ver que el documento fue firmado electrónicamente vía FirmaGob,
    // ni por quién, ni cuándo. Ahora se inserta un trámite nuevo (mismo
    // patrón que despacharDocumento en documento.service.ts), preservando
    // intacto el trámite original.
    const tramOrigenRes = await pool.request().input('idDoc', sql.Int, body.idDocumento)
      .query<{
        id_procedencia: number; id_destino: number;
        tipo_procedencia: string; tipo_destinatario: string;
        id_tipo_distribucion: number; id_tipo_compromiso: number;
        id_estado_compromiso: number; dias_compromiso: number;
      }>(`
        SELECT TOP 1 id_procedencia, id_destino, tipo_procedencia, tipo_destinatario,
               id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso, dias_compromiso
        FROM tramite WHERE id_documento = @idDoc AND id_estado_tramite = 1
        ORDER BY fecha_sistema DESC
      `);
    const tramOrigen = tramOrigenRes.recordset[0];
    if (tramOrigen) {
      const obsFirma = `Despachado — firmado vía FirmaGob (${body.nombreFirmante}, ${body.correlativoMemo})`.substring(0, 250);
      await pool.request()
        .input('idDoc',    sql.Int,          body.idDocumento)
        .input('idUsr',    sql.Int,          user.idUsuario)
        .input('idProc',   sql.Int,          tramOrigen.id_procedencia)
        .input('idDest',   sql.Int,          tramOrigen.id_destino)
        .input('tipProc',  sql.Char(1),      tramOrigen.tipo_procedencia)
        .input('tipDest',  sql.Char(1),      tramOrigen.tipo_destinatario)
        .input('idTipDis', sql.Int,          tramOrigen.id_tipo_distribucion)
        .input('idTipCom', sql.Int,          tramOrigen.id_tipo_compromiso)
        .input('idEstCom', sql.Int,          tramOrigen.id_estado_compromiso)
        .input('dias',     sql.Int,          tramOrigen.dias_compromiso)
        .input('obs',      sql.VarChar(250), obsFirma)
        .query(`
          INSERT INTO tramite
            (id_documento, id_usuario, id_procedencia, id_destino,
             tipo_procedencia, tipo_destinatario,
             id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
             id_estado_tramite, dias_compromiso, observaciones,
             fecha_sistema, fecha_update, fecha_despacho)
          VALUES
            (@idDoc, @idUsr, @idProc, @idDest,
             @tipProc, @tipDest,
             @idTipDis, @idTipCom, @idEstCom,
             2, @dias, @obs,
             GETDATE(), GETDATE(), GETDATE())
        `);
    } else {
      await pool.request()
        .input('idDoc', sql.Int, body.idDocumento)
        .query(`
          UPDATE tramite
          SET    id_estado_tramite = 2, fecha_despacho = GETDATE(), fecha_update = GETDATE()
          WHERE  id_documento = @idDoc AND id_estado_tramite = 1
        `);
    }

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
      filename: nombreFinal,
      ambiente: cfg.ambiente,
    }, 'Documento firmado y despachado correctamente');
  } catch (e) { next(e); }
});

// ── GET /firma-gob/logs ─────────────────────────────────────────────
// Lista paginada de logs técnicos (firma_gob_logs). Filtros opcionales
// vía query string: ambiente, accion, codigoRespuesta, idUsuario,
// fechaDesde, fechaHasta (sobre fecha_hora), soloErrores.
router.get('/logs', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pagina    = Math.max(1, parseInt(String(req.query.pagina ?? '1')));
    const porPagina = 20;
    const offset    = (pagina - 1) * porPagina;

    const ambiente        = req.query.ambiente ? String(req.query.ambiente).toUpperCase() : null;
    const accion          = req.query.accion   ? String(req.query.accion)                 : null;
    const usuario         = req.query.usuario  ? String(req.query.usuario)                : null;
    const fechaDesde      = req.query.fechaDesde ? String(req.query.fechaDesde)           : null;
    const fechaHasta      = req.query.fechaHasta ? String(req.query.fechaHasta)           : null;
    const soloErrores     = String(req.query.soloErrores ?? '') === 'true';

    const codigoRespuestaNum = req.query.codigoRespuesta ? parseInt(String(req.query.codigoRespuesta), 10) : NaN;
    const codigoRespuesta    = Number.isNaN(codigoRespuestaNum) ? null : codigoRespuestaNum;

    const idUsuarioNum = req.query.idUsuario ? parseInt(String(req.query.idUsuario), 10) : NaN;
    const idUsuario    = Number.isNaN(idUsuarioNum) ? null : idUsuarioNum;

    const pool = await getPool();

    const filtros = [
      ambiente             ? 'AND ambiente = @amb'                          : '',
      accion               ? 'AND accion = @accion'                        : '',
      usuario              ? 'AND usuario_nombre LIKE @usuario'            : '',
      codigoRespuesta != null ? 'AND codigo_respuesta = @cod'              : '',
      idUsuario != null    ? 'AND id_usuario = @idUsr'                     : '',
      fechaDesde           ? 'AND fecha_hora >= @fechaDesde'               : '',
      fechaHasta           ? 'AND fecha_hora < DATEADD(DAY, 1, @fechaHasta)' : '',
      soloErrores          ? "AND resultado = 'Error'"                     : '',
    ].filter(Boolean).join(' ');

    const buildReq = () => {
      const r = pool.request();
      if (ambiente)             r.input('amb', sql.VarChar(20), ambiente);
      if (accion)               r.input('accion', sql.VarChar(50), accion);
      if (usuario)              r.input('usuario', sql.VarChar(120), `%${usuario}%`);
      if (codigoRespuesta != null) r.input('cod', sql.Int, codigoRespuesta);
      if (idUsuario != null)    r.input('idUsr', sql.Int, idUsuario);
      if (fechaDesde)           r.input('fechaDesde', sql.Date, new Date(fechaDesde));
      if (fechaHasta)           r.input('fechaHasta', sql.Date, new Date(fechaHasta));
      return r;
    };

    const [dataRes, countRes] = await Promise.all([
      buildReq()
        .input('offset',   sql.Int, offset)
        .input('pageSize', sql.Int, porPagina)
        .query<{
          id: number; fecha_hora: string; ambiente: string | null; accion: string;
          metodo_http: string | null; endpoint: string | null; codigo_respuesta: number | null;
          resultado: string; mensaje: string | null; id_usuario: number | null;
          usuario_nombre: string | null; id_documento: number | null; ticket_firmagov: string | null;
          tiempo_respuesta_ms: number | null; recomendacion: string | null;
          revisado: boolean; id_historial: number | null;
        }>(`
          SELECT TOP (@pageSize) *
          FROM (
            SELECT ROW_NUMBER() OVER (ORDER BY fecha_hora DESC) AS rn,
              id, CONVERT(VARCHAR, fecha_hora, 120) AS fecha_hora, ambiente, accion,
              metodo_http, endpoint, codigo_respuesta, resultado, mensaje,
              id_usuario, usuario_nombre, id_documento, ticket_firmagov,
              tiempo_respuesta_ms, recomendacion, revisado, id_historial
            FROM firma_gob_logs
            WHERE 1=1 ${filtros}
          ) t
          WHERE rn > @offset
        `),
      buildReq().query<{ total: number }>(`
        SELECT COUNT(*) AS total FROM firma_gob_logs WHERE 1=1 ${filtros}
      `),
    ]);

    const total = countRes.recordset[0].total;
    sendPaginated(res, dataRes.recordset, buildPaginationMeta(total, pagina, porPagina));
  } catch (e) { next(e); }
});

// ── GET /firma-gob/logs/exportar ────────────────────────────────────
// CSV con BOM (compatible Excel). Mismos filtros que GET /logs, sin
// paginar (cap MAX_EXPORT_ROWS_LOGS). Registrada ANTES de /logs/:id.
const MAX_EXPORT_ROWS_LOGS = 5000;

router.get('/logs/exportar', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const ambiente    = req.query.ambiente ? String(req.query.ambiente).toUpperCase() : null;
    const accion      = req.query.accion   ? String(req.query.accion)                 : null;
    const usuario     = req.query.usuario  ? String(req.query.usuario)                : null;
    const fechaDesde  = req.query.fechaDesde ? String(req.query.fechaDesde)           : null;
    const fechaHasta  = req.query.fechaHasta ? String(req.query.fechaHasta)           : null;
    const soloErrores = String(req.query.soloErrores ?? '') === 'true';

    const codigoRespuestaNum = req.query.codigoRespuesta ? parseInt(String(req.query.codigoRespuesta), 10) : NaN;
    const codigoRespuesta    = Number.isNaN(codigoRespuestaNum) ? null : codigoRespuestaNum;

    const idUsuarioNum = req.query.idUsuario ? parseInt(String(req.query.idUsuario), 10) : NaN;
    const idUsuario    = Number.isNaN(idUsuarioNum) ? null : idUsuarioNum;

    const pool = await getPool();

    const filtros = [
      ambiente             ? 'AND ambiente = @amb'                          : '',
      accion               ? 'AND accion = @accion'                        : '',
      usuario              ? 'AND usuario_nombre LIKE @usuario'            : '',
      codigoRespuesta != null ? 'AND codigo_respuesta = @cod'              : '',
      idUsuario != null    ? 'AND id_usuario = @idUsr'                     : '',
      fechaDesde           ? 'AND fecha_hora >= @fechaDesde'               : '',
      fechaHasta           ? 'AND fecha_hora < DATEADD(DAY, 1, @fechaHasta)' : '',
      soloErrores          ? "AND resultado = 'Error'"                     : '',
    ].filter(Boolean).join(' ');

    const request = pool.request().input('maxRows', sql.Int, MAX_EXPORT_ROWS_LOGS);
    if (ambiente)             request.input('amb', sql.VarChar(20), ambiente);
    if (accion)               request.input('accion', sql.VarChar(50), accion);
    if (usuario)              request.input('usuario', sql.VarChar(120), `%${usuario}%`);
    if (codigoRespuesta != null) request.input('cod', sql.Int, codigoRespuesta);
    if (idUsuario != null)    request.input('idUsr', sql.Int, idUsuario);
    if (fechaDesde)           request.input('fechaDesde', sql.Date, new Date(fechaDesde));
    if (fechaHasta)           request.input('fechaHasta', sql.Date, new Date(fechaHasta));

    const result = await request.query<{
      id: number; fecha_hora: string; ambiente: string | null; accion: string;
      metodo_http: string | null; endpoint: string | null; codigo_respuesta: number | null;
      resultado: string; mensaje: string | null; usuario_nombre: string | null;
      id_documento: number | null; ticket_firmagov: string | null;
      tiempo_respuesta_ms: number | null; revisado: boolean;
    }>(`
      SELECT TOP (@maxRows)
        id, CONVERT(VARCHAR, fecha_hora, 120) AS fecha_hora, ambiente, accion,
        metodo_http, endpoint, codigo_respuesta, resultado, mensaje, usuario_nombre,
        id_documento, ticket_firmagov, tiempo_respuesta_ms, revisado
      FROM firma_gob_logs
      WHERE 1=1 ${filtros}
      ORDER BY fecha_hora DESC
    `);

    const headers = ['ID','Fecha/Hora','Ambiente','Acción','Método','Endpoint','Código','Resultado','Mensaje','Usuario','Documento','Ticket FirmaGov','Tiempo (ms)','Revisado'];
    const rows = result.recordset.map((r) => [
      r.id,
      r.fecha_hora,
      r.ambiente ?? '',
      r.accion,
      r.metodo_http ?? '',
      `"${(r.endpoint ?? '').replace(/"/g, '""')}"`,
      r.codigo_respuesta ?? '',
      r.resultado,
      `"${(r.mensaje ?? '').replace(/"/g, '""')}"`,
      r.usuario_nombre ?? '',
      r.id_documento ?? '',
      r.ticket_firmagov ?? '',
      r.tiempo_respuesta_ms ?? '',
      r.revisado ? 'Sí' : 'No',
    ].join(','));

    const csv = [headers.join(','), ...rows].join('\n');
    res.setHeader('Content-Type', 'text/csv; charset=utf-8');
    res.setHeader('Content-Disposition', `attachment; filename="firma_gob_logs_${Date.now()}.csv"`);
    if (result.recordset.length >= MAX_EXPORT_ROWS_LOGS) {
      res.setHeader('X-Export-Truncated', 'true');
      res.setHeader('X-Export-Limit', String(MAX_EXPORT_ROWS_LOGS));
    }
    res.send('﻿' + csv);
  } catch (e) { next(e); }
});

// ── GET /firma-gob/logs/:id ──────────────────────────────────────────
// Detalle completo de un log técnico (incluye payloads enmascarados,
// stack trace y recomendación).
router.get('/logs/:id', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const id = parseInt(req.params.id, 10);
    if (Number.isNaN(id)) { sendError(res, 'ID inválido', 400); return; }

    const pool = await getPool();
    const result = await pool.request()
      .input('id', sql.Int, id)
      .query<{
        id: number; fecha_hora: string; ambiente: string | null; accion: string;
        metodo_http: string | null; endpoint: string | null; codigo_respuesta: number | null;
        resultado: string; mensaje: string | null; id_usuario: number | null;
        usuario_nombre: string | null; id_documento: number | null; ticket_firmagov: string | null;
        request_payload: string | null; response_payload: string | null;
        tiempo_respuesta_ms: number | null; stack_trace: string | null; recomendacion: string | null;
        revisado: boolean; fecha_revisado: string | null; id_usuario_revisor: number | null;
        id_historial: number | null;
      }>(`
        SELECT id, CONVERT(VARCHAR, fecha_hora, 120) AS fecha_hora, ambiente, accion,
          metodo_http, endpoint, codigo_respuesta, resultado, mensaje,
          id_usuario, usuario_nombre, id_documento, ticket_firmagov,
          request_payload, response_payload, tiempo_respuesta_ms, stack_trace, recomendacion,
          revisado, CONVERT(VARCHAR, fecha_revisado, 120) AS fecha_revisado, id_usuario_revisor, id_historial
        FROM firma_gob_logs
        WHERE id = @id
      `);

    const log = result.recordset[0];
    if (!log) { sendError(res, 'Log no encontrado', 404); return; }

    const parseJson = (val: string | null): unknown => {
      if (!val) return null;
      try { return JSON.parse(val); } catch { return val; }
    };

    sendSuccess(res, {
      ...log,
      request_payload:  parseJson(log.request_payload),
      response_payload: parseJson(log.response_payload),
    });
  } catch (e) { next(e); }
});

// ── PATCH /firma-gob/logs/:id/revisado ───────────────────────────────
// Marca/desmarca un log como revisado. Body: { revisado: boolean }
router.patch('/logs/:id/revisado', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const user = (req as unknown as AuthenticatedRequest).user;
    const id = parseInt(req.params.id, 10);
    if (Number.isNaN(id)) { sendError(res, 'ID inválido', 400); return; }

    const { revisado } = req.body as { revisado?: boolean };
    if (typeof revisado !== 'boolean') { sendError(res, 'revisado (boolean) es requerido', 400); return; }

    const pool = await getPool();
    const result = await pool.request()
      .input('id',       sql.Int, id)
      .input('revisado', sql.Bit, revisado ? 1 : 0)
      .input('idUsr',    sql.Int, revisado ? user.idUsuario : null)
      .query(`
        UPDATE firma_gob_logs
        SET revisado = @revisado,
            fecha_revisado = CASE WHEN @revisado = 1 THEN GETDATE() ELSE NULL END,
            id_usuario_revisor = @idUsr
        WHERE id = @id
      `);

    if (result.rowsAffected[0] === 0) { sendError(res, 'Log no encontrado', 404); return; }
    sendSuccess(res, null, revisado ? 'Log marcado como revisado' : 'Log marcado como pendiente');
  } catch (e) { next(e); }
});

// ── POST /firma-gob/logs/:id/reintentar ──────────────────────────────
// Solo para logs de diagnóstico (accion empieza con 'test-conexion-').
// Crea una NUEVA fila de log con el resultado actual — no muta la original.
// Para 'solicitar-firma' devuelve indicación de usar el flujo del documento.
router.post('/logs/:id/reintentar', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const user = (req as unknown as AuthenticatedRequest).user;
    const id = parseInt(req.params.id, 10);
    if (Number.isNaN(id)) { sendError(res, 'ID inválido', 400); return; }

    const pool = await getPool();
    const logRes = await pool.request()
      .input('id', sql.Int, id)
      .query<{ id: number; accion: string; ambiente: string | null }>(
        'SELECT id, accion, ambiente FROM firma_gob_logs WHERE id = @id'
      );
    const log = logRes.recordset[0];
    if (!log) { sendError(res, 'Log no encontrado', 404); return; }

    if (!log.accion.startsWith('test-conexion-')) {
      sendError(res, 'Para reintentar una firma, ve al documento y vuelve a solicitar la firma desde el flujo de memorándum', 400);
      return;
    }

    const amb = (log.ambiente ?? 'TEST').toUpperCase() as Ambiente;
    if (!AMBIENTES.includes(amb)) { sendError(res, 'Ambiente inválido en el log original', 400); return; }

    if (log.accion === 'test-conexion-nivel3') {
      sendError(res, 'Para repetir la validación de credenciales (Nivel 3), use el botón "Validar credenciales (envío real)" en la pestaña Configuración — requiere confirmación y RUT de prueba.', 400);
      return;
    }

    // Niveles 1/2 — re-ejecuta una petición HEAD con la configuración actual del ambiente
    const cfgRes = await pool.request()
      .input('amb', sql.VarChar(20), amb)
      .query<{ url_api: string | null; activo: boolean }>('SELECT url_api, activo FROM firma_gob_config WHERE ambiente = @amb');

    const cfg = cfgRes.recordset[0];
    if (!cfg || !cfg.url_api) {
      sendSuccess(res, { ok: false, ambiente: amb, mensaje: 'URL de API no configurada para este ambiente' });
      return;
    }
    if (!cfg.activo) {
      sendSuccess(res, { ok: false, ambiente: amb, mensaje: 'El ambiente está inactivo' });
      return;
    }

    type NivelResultado = { nivel: number; ok: boolean; resultado: ResultadoLog; status?: number; mensaje: string; recomendacion?: string; idLog: number | null };
    const niveles: NivelResultado[] = [];

    try {
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 5000);
      const inicio = Date.now();
      const response = await fetch(cfg.url_api, { method: 'HEAD', signal: controller.signal });
      const tiempoMs = Date.now() - inicio;
      clearTimeout(timeout);

      const idLog1 = await registrarLog(pool, {
        ambiente: amb, accion: 'test-conexion-nivel1', metodoHttp: 'HEAD', endpoint: cfg.url_api,
        codigoRespuesta: response.status, resultado: 'Exitoso',
        mensaje: 'Servidor alcanzable a nivel de red', idUsuario: user.idUsuario, usuarioNombre: user.usuario,
        tiempoRespuestaMs: tiempoMs, recomendacion: 'Ninguna acción requerida.',
      });
      niveles.push({ nivel: 1, ok: true, resultado: 'Exitoso', status: response.status, mensaje: 'Servidor alcanzable a nivel de red', idLog: idLog1 });

      const interpretacion = interpretarStatus(response.status);
      const idLog2 = await registrarLog(pool, {
        ambiente: amb, accion: 'test-conexion-nivel2', metodoHttp: 'HEAD', endpoint: cfg.url_api,
        codigoRespuesta: response.status, resultado: interpretacion.resultado, mensaje: interpretacion.mensaje,
        idUsuario: user.idUsuario, usuarioNombre: user.usuario,
        tiempoRespuestaMs: tiempoMs, recomendacion: interpretacion.recomendacion,
      });
      niveles.push({
        nivel: 2, ok: interpretacion.resultado !== 'Error', resultado: interpretacion.resultado, status: response.status,
        mensaje: interpretacion.mensaje, recomendacion: interpretacion.recomendacion, idLog: idLog2,
      });

      sendSuccess(res, { ok: niveles.every((n) => n.ok), ambiente: amb, niveles });
    } catch (_err) {
      const msg = _err instanceof Error ? _err.message : String(_err);
      const idLog1 = await registrarLog(pool, {
        ambiente: amb, accion: 'test-conexion-nivel1', metodoHttp: 'HEAD', endpoint: cfg.url_api,
        resultado: 'Error', mensaje: `Servidor no alcanzable: ${msg}`,
        idUsuario: user.idUsuario, usuarioNombre: user.usuario,
        recomendacion: 'Verificar la URL configurada (url_api) y la conectividad de red.',
        stackTrace: _err instanceof Error ? (_err.stack ?? null) : null,
      });
      niveles.push({ nivel: 1, ok: false, resultado: 'Error', mensaje: `Servidor no alcanzable: ${msg}`, idLog: idLog1 });
      sendSuccess(res, { ok: false, ambiente: amb, niveles });
    }
  } catch (e) { next(e); }
});

export default router;
