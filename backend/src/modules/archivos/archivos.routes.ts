import { Router, Request, Response, NextFunction } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendError, sendCreated, sendForbidden } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { env } from '../../config/env';
import { logger } from '../../shared/utils/logger';

const router = Router();
router.use(requireAuth);

const UPLOAD_DIR = path.resolve(env.UPLOAD_DIR);

// ── Reglas de carga configurables ──────────────────────────
// Defaults seguros usados si el archivo de configuración no existe o no es válido.
const _UPLOAD_CFG_DEFAULTS = {
  extensionesPermitidas: ['pdf','doc','docx','xls','xlsx','png','jpg','jpeg','webp','txt'],
  maxFileMB: 20,
};

// Lee las reglas de carga desde sistema.json de forma síncrona.
// Tolerante a fallos: si el archivo no existe o está corrupto devuelve los defaults.
function getUploadRules(): typeof _UPLOAD_CFG_DEFAULTS {
  try {
    const cfgFile = path.resolve(env.UPLOAD_DIR, 'config', 'sistema.json');
    if (!fs.existsSync(cfgFile)) return _UPLOAD_CFG_DEFAULTS;
    const cfg = JSON.parse(fs.readFileSync(cfgFile, 'utf-8')) as Record<string, unknown>;
    return {
      extensionesPermitidas: Array.isArray(cfg.uploadExtensionesPermitidas)
        ? (cfg.uploadExtensionesPermitidas as string[])
        : _UPLOAD_CFG_DEFAULTS.extensionesPermitidas,
      maxFileMB: typeof cfg.uploadMaxFileMB === 'number'
        ? cfg.uploadMaxFileMB
        : _UPLOAD_CFG_DEFAULTS.maxFileMB,
    };
  } catch {
    return _UPLOAD_CFG_DEFAULTS;
  }
}

// ── MIME types conocidos ──────────────────────────────────────
const MIME_MAP: Record<string, string> = {
  '.pdf':  'application/pdf',
  '.png':  'image/png',
  '.jpg':  'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.gif':  'image/gif',
  // SVG eliminado: puede contener <script> → riesgo de XSS almacenado
  // ZIP eliminado: riesgo de malware y zip-slip al descomprimir
  '.doc':  'application/msword',
  '.docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  '.xls':  'application/vnd.ms-excel',
  '.xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  '.txt':  'text/plain',
};

function getMime(filename: string): string {
  return MIME_MAP[path.extname(filename).toLowerCase()] ?? 'application/octet-stream';
}

function shortFilename(originalname: string): string {
  const ext = path.extname(originalname).toLowerCase().slice(0, 5);
  const ts = Date.now().toString().slice(-8);
  return `${ts}${ext}`;
}

const storage = multer.diskStorage({
  destination: (_req, _file, cb) => { fs.mkdirSync(UPLOAD_DIR, { recursive: true }); cb(null, UPLOAD_DIR); },
  filename: (_req, file, cb) => { cb(null, shortFilename(file.originalname)); },
});

const upload = multer({
  storage,
  limits: { fileSize: env.MAX_FILE_SIZE },
  fileFilter: (_req, file, cb) => {
    // SVG eliminado: puede contener <script> → riesgo de XSS almacenado
    // ZIP eliminado: riesgo de malware y zip-slip al descomprimir
    const allowed = ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.png', '.jpg', '.jpeg', '.webp', '.txt'];
    const ext = path.extname(file.originalname).toLowerCase();
    if (allowed.includes(ext)) return cb(null, true);
    cb(new Error(`Tipo no permitido: ${ext}`));
  },
});

// ── Helpers para obtener datos del archivo ───────────────────
async function findArchivo(id: number) {
  const pool = await getPool();
  const r = await pool.request()
    .input('id', sql.Int, id)
    .query<{ id_archivo_digital: number; archivo: string | null; ruta: string | null }>(`
      SELECT id_archivo_digital, archivo, ruta FROM archivo_digital WHERE id_archivo_digital = @id
    `);
  return r.recordset[0] ?? null;
}

// ── GET /archivos/:id/preview — visualizar inline ────────────
router.get('/:id/preview', async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    const row = await findArchivo(Number(req.params.id));
    if (!row?.ruta) { res.status(404).send('Archivo no encontrado'); return; }

    const filePath = path.resolve(UPLOAD_DIR, row.ruta);
    if (!fs.existsSync(filePath)) { res.status(404).send('Archivo no encontrado en disco'); return; }

    const nombreOriginal = row.archivo ?? row.ruta;
    const mime = getMime(nombreOriginal);

    res.setHeader('Content-Type', mime);
    res.setHeader('Content-Disposition', `inline; filename="${encodeURIComponent(nombreOriginal)}"`);
    res.setHeader('Cache-Control', 'private, max-age=300');
    fs.createReadStream(filePath).pipe(res);
  } catch (e) { next(e); }
});

// ── GET /archivos/:id/download — forzar descarga ─────────────
router.get('/:id/download', async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    const row = await findArchivo(Number(req.params.id));
    if (!row?.ruta) { res.status(404).send('Archivo no encontrado'); return; }

    const filePath = path.resolve(UPLOAD_DIR, row.ruta);
    if (!fs.existsSync(filePath)) { res.status(404).send('Archivo no encontrado en disco'); return; }

    const nombreOriginal = row.archivo ?? row.ruta;
    const mime = getMime(nombreOriginal);

    res.setHeader('Content-Type', mime);
    res.setHeader('Content-Disposition', `attachment; filename="${encodeURIComponent(nombreOriginal)}"`);
    fs.createReadStream(filePath).pipe(res);
  } catch (e) { next(e); }
});

// ── POST /archivos/upload ────────────────────────────────────
// Si se provee idDocumento (adjunto a documento existente):
//   - Verifica estado: bloquea si el documento está Terminado (estado 4)
//   - Verifica permisos: admin/of.partes/todosServicios/creador/servicio relacionado
//   - Registra id_usuario en archivo_digital
//   - Inserta evento de trazabilidad en tramite (id_estado_tramite=7)
// Si NO se provee idDocumento: comportamiento legacy (sin validaciones adicionales).
router.post('/upload', upload.single('archivo'), async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    if (!req.file) { sendError(res, 'No se recibió ningún archivo', 400); return; }

    // ── Validación adicional contra reglas configurables (ADDITIVE sobre multer) ──
    // multer ya bloqueó extensiones no permitidas y archivos > MAX_FILE_SIZE.
    // Aquí se aplican las restricciones configuradas desde el módulo Configuración.
    const uploadCfg = getUploadRules();
    const fileExt   = path.extname(req.file.originalname).toLowerCase().slice(1);
    if (!uploadCfg.extensionesPermitidas.includes(fileExt)) {
      fs.unlinkSync(req.file.path);
      sendError(res, `Tipo de archivo no permitido por la configuración del sistema: .${fileExt}`, 400);
      return;
    }
    const cfgMaxBytes = uploadCfg.maxFileMB * 1024 * 1024;
    if (req.file.size > cfgMaxBytes) {
      fs.unlinkSync(req.file.path);
      sendError(res, `El archivo supera el tamaño máximo configurado (${uploadCfg.maxFileMB} MB)`, 400);
      return;
    }

    const user = (req as unknown as AuthenticatedRequest).user;
    const { idDocumento, observaciones } = req.body;
    const rutaCorta    = req.file.filename;
    const archivoCorto = req.file.originalname.slice(0, 50);
    let idArchivo: number | null = null;

    if (idDocumento && !isNaN(Number(idDocumento))) {
      const docId = Number(idDocumento);
      const pool  = await getPool();

      // ── Verificar que el documento existe ─────────────────
      const docCheck = await pool.request()
        .input('id', sql.Int, docId)
        .query<{ id_estado_documento: number; id_usuario_creador: number }>(`
          SELECT id_estado_documento, id_usuario AS id_usuario_creador
          FROM documento WHERE id_documento = @id
        `);
      if (!docCheck.recordset[0]) {
        fs.unlinkSync(req.file.path);
        sendError(res, 'Documento no encontrado', 404);
        return;
      }
      const { id_estado_documento: estadoDoc, id_usuario_creador: idCreador } = docCheck.recordset[0];

      // ── Bloquear si el documento está Terminado (estado 4) ─
      // Regla: no se adjuntan archivos a documentos terminados/cerrados.
      // Evaluar en el futuro si debe permitirse para anulados/archivados.
      if (estadoDoc === 4) {
        fs.unlinkSync(req.file.path);
        sendError(res, 'No se pueden adjuntar archivos a un documento Terminado', 403);
        return;
      }

      // ── Control de acceso ─────────────────────────────────
      // Pueden adjuntar: admin, of.partes, todosServicios,
      // creador del documento, y cualquier usuario cuya dependencia
      // figure como origen o destino en la trazabilidad del documento.
      const esAdmin    = user.roles.includes('admin');
      const esOfPartes = user.roles.includes('of.partes');
      if (!esAdmin && !esOfPartes && !user.todosServicios) {
        const esCreador = idCreador === user.idUsuario;
        if (!esCreador) {
          const idDep = user.idDependencia;
          if (!idDep) {
            fs.unlinkSync(req.file.path);
            sendForbidden(res, 'No tienes permiso para adjuntar archivos a este documento');
            return;
          }
          const permCheck = await pool.request()
            .input('idDoc', sql.Int, docId)
            .input('idDep', sql.Int, idDep)
            .query<{ ok: number }>(`
              SELECT TOP 1 1 AS ok FROM tramite
              WHERE id_documento = @idDoc
                AND (
                  (id_destino     = @idDep AND tipo_destinatario = 'D')
                  OR (id_procedencia = @idDep AND tipo_procedencia  = 'D')
                )
            `);
          if (!permCheck.recordset[0]) {
            fs.unlinkSync(req.file.path);
            sendForbidden(res, 'No tienes permiso para adjuntar archivos a este documento');
            return;
          }
        }
      }

      // ── Insertar registro de archivo ───────────────────────
      const result = await pool.request()
        .input('idDocumento', sql.Int,          docId)
        .input('idUsuario',   sql.Int,          user.idUsuario)
        .input('archivo',     sql.VarChar(50),  archivoCorto)
        .input('ruta',        sql.VarChar(50),  rutaCorta)
        .input('tamano',      sql.Int,          req.file.size)
        .input('tipoMime',    sql.VarChar(100), getMime(req.file.originalname))
        .query<{ id_archivo_digital: number }>(`
          INSERT INTO archivo_digital (id_documento, id_usuario, archivo, ruta, tamano, tipo_mime, fecha_sistema, fecha_update)
          OUTPUT INSERTED.id_archivo_digital
          VALUES (@idDocumento, @idUsuario, @archivo, @ruta, @tamano, @tipoMime, GETDATE(), GETDATE())
        `);
      idArchivo = result.recordset[0]?.id_archivo_digital ?? null;

      // ── Trazabilidad: insertar evento "Archivo adjuntado" ──
      // Usa id_destino del último trámite de enrutamiento para no alterar
      // el contexto que leen despachar/recepcionar/terminar/reabrir.
      // id_estado_tramite=7 existe en estado_tramite (insertado vía migración).
      // El bloque try/catch aísla este paso: si falla, la subida del archivo
      // ya fue exitosa y se registró en archivo_digital — la trazabilidad
      // se pierde pero el adjunto queda correctamente asociado al documento.
      try {
        const lastTramite = await pool.request()
          .input('idDoc', sql.Int, docId)
          .query<{ id_destino: number; tipo_destinatario: string }>(`
            SELECT TOP 1 id_destino, tipo_destinatario
            FROM tramite WHERE id_documento = @idDoc
            ORDER BY fecha_sistema DESC
          `);
        const idDest  = lastTramite.recordset[0]?.id_destino  ?? 1;
        const tipDest = (lastTramite.recordset[0]?.tipo_destinatario ?? 'D').charAt(0);

        const obsTexto = [
          `Archivo adjuntado: ${req.file.originalname}`,
          observaciones ? String(observaciones).trim() : '',
        ].filter(Boolean).join(' — ').substring(0, 250);

        await pool.request()
          .input('idDoc',   sql.Int,          docId)
          .input('idUsr',   sql.Int,          user.idUsuario)
          .input('idDest',  sql.Int,          idDest)
          .input('tipDest', sql.Char(1),      tipDest)
          .input('obs',     sql.VarChar(250), obsTexto)
          .query(`
            INSERT INTO tramite
              (id_documento, id_usuario, id_procedencia, id_destino,
               tipo_procedencia, tipo_destinatario,
               id_tipo_distribucion, id_tipo_compromiso, id_estado_compromiso,
               id_estado_tramite, dias_compromiso, observaciones,
               fecha_sistema, fecha_update)
            VALUES
              (@idDoc, @idUsr, @idDest, @idDest,
               @tipDest, @tipDest,
               5, 1, 2,
               7, 0, @obs,
               GETDATE(), GETDATE())
          `);
      } catch (trazErr) {
        // El archivo ya fue guardado — solo registrar el error de trazabilidad
        logger.warn('Trazabilidad de archivo adjuntado falló (el archivo SÍ fue guardado)', {
          idDocumento: docId, idArchivo, error: String(trazErr),
        });
      }

    } else {
      // Sin idDocumento: comportamiento legacy — almacena sin asociar a documento
      const pool = await getPool();
      const result = await pool.request()
        .input('archivo',  sql.VarChar(50),  archivoCorto)
        .input('ruta',     sql.VarChar(50),  rutaCorta)
        .input('tamano',   sql.Int,          req.file.size)
        .input('tipoMime', sql.VarChar(100), getMime(req.file.originalname))
        .query<{ id_archivo_digital: number }>(`
          INSERT INTO archivo_digital (archivo, ruta, tamano, tipo_mime, fecha_sistema, fecha_update)
          OUTPUT INSERTED.id_archivo_digital
          VALUES (@archivo, @ruta, @tamano, @tipoMime, GETDATE(), GETDATE())
        `);
      idArchivo = result.recordset[0]?.id_archivo_digital ?? null;
    }

    sendCreated(res, {
      idArchivo,
      nombreOriginal: req.file.originalname,
      nombreGuardado: rutaCorta,
      tamano: req.file.size,
      tipoMime: req.file.mimetype,
      url: `/uploads/${rutaCorta}`,
    }, 'Archivo adjuntado correctamente');
  } catch (e) { next(e); }
});

// ── GET /archivos ────────────────────────────────────────────
router.get('/', async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    const pool = await getPool();
    const { idDocumento } = req.query;
    const request = pool.request();
    let where = '1=1';
    if (idDocumento && !isNaN(Number(idDocumento))) {
      request.input('idDocumento', sql.Int, Number(idDocumento));
      where += ' AND a.id_documento = @idDocumento';
    }
    const result = await request.query<{
      id_archivo_digital: number; id_documento: number | null;
      archivo: string | null; ruta: string | null;
      tamano: number | null; tipo_mime: string | null;
      fecha_sistema: Date | null; materia: string | null;
      usuario_upload: string | null; nombre_upload: string | null;
    }>(`
      SELECT a.id_archivo_digital, a.id_documento, a.archivo, a.ruta,
             a.tamano, a.tipo_mime,
             a.fecha_sistema, d.materia,
             u.usuario AS usuario_upload,
             LTRIM(RTRIM(ISNULL(f.nombres, '') + ' ' + ISNULL(f.apellidos, ''))) AS nombre_upload
      FROM archivo_digital a
      LEFT JOIN documento   d ON a.id_documento  = d.id_documento
      LEFT JOIN usuario     u ON a.id_usuario    = u.id_usuario
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      WHERE ${where}
      ORDER BY a.fecha_sistema DESC
    `);

    sendSuccess(res, result.recordset.map((r) => ({
      id_archivo:     r.id_archivo_digital,
      id_documento:   r.id_documento,
      nombre_archivo: r.archivo,
      ruta_archivo:   r.ruta,
      tamano:         r.tamano,
      tipo_mime:      r.tipo_mime,
      fecha_subida:   r.fecha_sistema,
      materia:        r.materia,
      subido_por:     r.nombre_upload?.trim() || r.usuario_upload || null,
      url:            r.ruta ? `/uploads/${r.ruta}` : null,
      preview_url:    `/api/v1/archivos/${r.id_archivo_digital}/preview`,
      download_url:   `/api/v1/archivos/${r.id_archivo_digital}/download`,
    })));
  } catch (e) { next(e); }
});

// ── DELETE /archivos/:id ─────────────────────────────────────
router.delete('/:id', async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    const user      = (req as unknown as AuthenticatedRequest).user;
    const idArchivo = Number(req.params.id);
    const pool      = await getPool();

    const res2 = await pool.request()
      .input('id', sql.Int, idArchivo)
      .query<{ ruta: string | null }>('SELECT ruta FROM archivo_digital WHERE id_archivo_digital = @id');
    const row = res2.recordset[0];
    if (!row) { sendError(res, 'Archivo no encontrado', 404); return; }

    // Verificar que el archivo pertenezca a un documento de la dependencia del usuario
    if (!user.roles.includes('admin') && !user.todosServicios) {
      const idDep = user.idDependencia;
      if (!idDep) { sendForbidden(res, 'No tienes permiso para eliminar este archivo'); return; }
      const ownerCheck = await pool.request()
        .input('id',    sql.Int, idArchivo)
        .input('idDep', sql.Int, idDep)
        .query(`
          SELECT 1 AS ok FROM archivo_digital a
          JOIN tramite t ON t.id_documento = a.id_documento
          WHERE a.id_archivo_digital = @id
            AND t.id_destino = @idDep AND t.tipo_destinatario = 'D'
        `);
      if (!ownerCheck.recordset[0]) {
        sendForbidden(res, 'No tienes permiso para eliminar este archivo');
        return;
      }
    }

    if (row.ruta) {
      const fp = path.resolve(UPLOAD_DIR, row.ruta);
      if (fs.existsSync(fp)) fs.unlinkSync(fp);
    }
    await pool.request()
      .input('id', sql.Int, idArchivo)
      .query('DELETE FROM archivo_digital WHERE id_archivo_digital = @id');
    sendSuccess(res, null, 'Archivo eliminado');
  } catch (e) { next(e); }
});

export default router;
