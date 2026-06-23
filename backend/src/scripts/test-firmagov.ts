/**
 * Script de diagnóstico independiente para la integración FirmaGov.
 *
 * Reutiliza exactamente la misma configuración, token y lógica de
 * construcción de JWT que usa /firma-gob/solicitar y /firma-gob/test-conexion
 * (nivel 3) en producción — no duplica reglas de negocio, solo las invoca
 * para capturar el request/response REAL sin enmascarar y generar evidencia
 * para la Mesa de Servicios de Gobierno Digital.
 *
 * Uso:
 *   cd backend
 *   npx tsx src/scripts/test-firmagov.ts [RUT_PRUEBA] [AMBIENTE]
 *   npx tsx src/scripts/test-firmagov.ts 15762009-6 TEST
 *
 * Salida: carpeta evidencia-firmagov/ en la raíz del proyecto con:
 *   request.json, headers.txt, request.txt, curl.txt, response.json,
 *   response.txt, evidencia-firmagov.md
 *
 * No modifica configuración, no modifica BD (salvo lectura), no despacha
 * documentos ni toca firma_gob_historial — es de solo lectura + 1 POST.
 */
import fs from 'fs';
import path from 'path';
import crypto from 'crypto';
import jwt from 'jsonwebtoken';
import { getPool, sql, closePool } from '../config/database';
import { formatearExpirationChile, limpiarRunFirmaGov, construirPdfPrueba } from '../modules/firma-gob/firma-gob.utils';

async function main() {
  const runPrueba = process.argv[2] ?? '15762009-6';
  const ambiente  = (process.argv[3] ?? 'TEST').toUpperCase();

  const outDir = path.resolve(__dirname, '../../../evidencia-firmagov');
  fs.mkdirSync(outDir, { recursive: true });

  console.log(`[test-firmagov] Ambiente: ${ambiente} | RUT prueba: ${runPrueba}`);

  const pool = await getPool();
  const cfgRes = await pool.request()
    .input('amb', sql.VarChar(20), ambiente)
    .query<{
      url_api: string | null; entity: string | null; purpose: string | null;
      api_token_key: string | null; jwt_secret: string | null; activo: boolean;
    }>('SELECT url_api, entity, purpose, api_token_key, jwt_secret, activo FROM firma_gob_config WHERE ambiente = @amb');

  const cfg = cfgRes.recordset[0];
  if (!cfg || !cfg.url_api || !cfg.api_token_key || !cfg.jwt_secret || !cfg.entity || !cfg.purpose) {
    console.error('[test-firmagov] Configuración incompleta para', ambiente, cfg);
    await closePool();
    process.exit(1);
  }

  // ── Construcción del JWT — idéntica a /solicitar y /test-conexion nivel 3 ──
  const runLimpio       = limpiarRunFirmaGov(runPrueba);
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

  const requestHeaders = { 'Content-Type': 'application/json' };

  console.log(`[test-firmagov] JWT payload: ${JSON.stringify(jwtPayload)}`);
  console.log(`[test-firmagov] Enviando POST a ${cfg.url_api} ...`);

  const inicio = Date.now();
  const response = await fetch(cfg.url_api, {
    method:  'POST',
    headers: requestHeaders,
    body:    JSON.stringify(requestBody),
  });
  const tiempoMs = Date.now() - inicio;

  const responseHeaders: Record<string, string> = {};
  response.headers.forEach((value, key) => { responseHeaders[key] = value; });

  const textoRespuesta = await response.text().catch(() => '');
  let dataRespuesta: unknown = textoRespuesta;
  try { dataRespuesta = JSON.parse(textoRespuesta); } catch { /* respuesta no-JSON */ }

  console.log(`[test-firmagov] HTTP ${response.status} en ${tiempoMs}ms`);

  // ── Decodificar JWT para evidencia (header + payload, sin firmar de nuevo) ──
  const [jwtHeaderB64, jwtPayloadB64] = firmaJwt.split('.');
  const jwtHeaderDecoded  = JSON.parse(Buffer.from(jwtHeaderB64, 'base64url').toString('utf8'));
  const jwtPayloadDecoded = JSON.parse(Buffer.from(jwtPayloadB64, 'base64url').toString('utf8'));

  // ── Generar archivos de evidencia ───────────────────────────────────────
  fs.writeFileSync(path.join(outDir, 'request.json'), JSON.stringify(requestBody, null, 2));

  fs.writeFileSync(path.join(outDir, 'headers.txt'),
    Object.entries(requestHeaders).map(([k, v]) => `${k}: ${v}`).join('\n') + '\n');

  const requestTxt = [
    `POST ${cfg.url_api} HTTP/1.1`,
    ...Object.entries(requestHeaders).map(([k, v]) => `${k}: ${v}`),
    '',
    JSON.stringify(requestBody, null, 2),
  ].join('\n');
  fs.writeFileSync(path.join(outDir, 'request.txt'), requestTxt);

  const curlTxt = `curl -X POST '${cfg.url_api}' \\\n` +
    Object.entries(requestHeaders).map(([k, v]) => `  -H '${k}: ${v}' \\\n`).join('') +
    `  -d '${JSON.stringify(requestBody)}'\n`;
  fs.writeFileSync(path.join(outDir, 'curl.txt'), curlTxt);

  fs.writeFileSync(path.join(outDir, 'response.json'), JSON.stringify({
    status: response.status,
    headers: responseHeaders,
    body: dataRespuesta,
    tiempoRespuestaMs: tiempoMs,
  }, null, 2));

  const responseTxt = [
    `HTTP/1.1 ${response.status} ${response.statusText}`,
    ...Object.entries(responseHeaders).map(([k, v]) => `${k}: ${v}`),
    '',
    typeof dataRespuesta === 'string' ? dataRespuesta : JSON.stringify(dataRespuesta, null, 2),
  ].join('\n');
  fs.writeFileSync(path.join(outDir, 'response.txt'), responseTxt);

  const md = `# Evidencia técnica — Integración FirmaGov (${ambiente})

Generado automáticamente por \`test-firmagov.ts\` el ${new Date().toISOString()}.

## 1. Endpoint

\`\`\`
POST ${cfg.url_api}
\`\`\`

## 2. Headers enviados

\`\`\`
${Object.entries(requestHeaders).map(([k, v]) => `${k}: ${v}`).join('\n')}
\`\`\`

## 3. Payload enviado (request.json)

\`\`\`json
${JSON.stringify({ ...requestBody, files: [{ ...requestBody.files[0], content: '<PDF base64 omitido — ver request.json>' }] }, null, 2)}
\`\`\`

## 4. JWT decodificado

**Header:**
\`\`\`json
${JSON.stringify(jwtHeaderDecoded, null, 2)}
\`\`\`

**Payload:**
\`\`\`json
${JSON.stringify(jwtPayloadDecoded, null, 2)}
\`\`\`

## 5. Respuesta recibida

- **HTTP Status:** ${response.status} ${response.statusText}
- **Tiempo de respuesta:** ${tiempoMs} ms

\`\`\`json
${JSON.stringify(dataRespuesta, null, 2)}
\`\`\`

## 6. Datos de configuración usados (entity / token)

- **entity:** \`${cfg.entity}\`
- **purpose:** \`${cfg.purpose}\`
- **api_token_key (UUID):** \`${cfg.api_token_key}\`
- **run enviado (RUT firmante, limpio):** \`${runLimpio}\`
`;
  fs.writeFileSync(path.join(outDir, 'evidencia-firmagov.md'), md);

  console.log(`[test-firmagov] Evidencia guardada en: ${outDir}`);
  console.log('[test-firmagov] ADVERTENCIA: estos archivos contienen el token real. No subir a git ni compartir fuera de Gobierno Digital.');

  await closePool();
}

main().catch((err) => {
  console.error('[test-firmagov] Error:', err);
  process.exit(1);
});
