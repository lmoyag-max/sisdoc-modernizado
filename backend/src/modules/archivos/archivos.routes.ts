import { Router, Request, Response, NextFunction } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendError, sendCreated, sendForbidden } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { env } from '../../config/env';

const router = Router();
router.use(requireAuth);

const UPLOAD_DIR = path.resolve(env.UPLOAD_DIR);

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
router.post('/upload', upload.single('archivo'), async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    if (!req.file) { sendError(res, 'No se recibió ningún archivo', 400); return; }
    const { idDocumento } = req.body;
    const rutaCorta   = req.file.filename;
    const archivoCorto = req.file.originalname.slice(0, 50);
    let idArchivo: number | null = null;

    if (idDocumento && !isNaN(Number(idDocumento))) {
      const pool = await getPool();
      const result = await pool.request()
        .input('idDocumento', sql.Int, Number(idDocumento))
        .input('archivo',    sql.VarChar(50), archivoCorto)
        .input('ruta',       sql.VarChar(50), rutaCorta)
        .query<{ id_archivo_digital: number }>(`
          INSERT INTO archivo_digital (id_documento, archivo, ruta, fecha_sistema, fecha_update)
          OUTPUT INSERTED.id_archivo_digital
          VALUES (@idDocumento, @archivo, @ruta, GETDATE(), GETDATE())
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
    }, 'Archivo subido correctamente');
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
      fecha_sistema: Date | null; materia: string | null;
    }>(`
      SELECT a.id_archivo_digital, a.id_documento, a.archivo, a.ruta,
             a.fecha_sistema, d.materia
      FROM archivo_digital a
      LEFT JOIN documento d ON a.id_documento = d.id_documento
      WHERE ${where}
      ORDER BY a.fecha_sistema DESC
    `);

    sendSuccess(res, result.recordset.map((r) => ({
      id_archivo:     r.id_archivo_digital,
      id_documento:   r.id_documento,
      nombre_archivo: r.archivo,
      ruta_archivo:   r.ruta,
      fecha_subida:   r.fecha_sistema,
      materia:        r.materia,
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
