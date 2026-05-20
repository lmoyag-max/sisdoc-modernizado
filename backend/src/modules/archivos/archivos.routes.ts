import { Router, Request, Response, NextFunction } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendError, sendCreated } from '../../shared/utils/response';
import { env } from '../../config/env';

const router = Router();
router.use(requireAuth);

const UPLOAD_DIR = path.resolve(env.UPLOAD_DIR);

// Generar nombre corto garantizado < 40 chars para caber en varchar(50)
function shortFilename(originalname: string): string {
  const ext = path.extname(originalname).toLowerCase().slice(0, 5); // e.g. ".pdf"
  const ts = Date.now().toString().slice(-8); // últimos 8 dígitos del timestamp
  return `${ts}${ext}`; // e.g. "83125525.pdf" = 12 chars max
}

const storage = multer.diskStorage({
  destination: (_req, _file, cb) => {
    fs.mkdirSync(UPLOAD_DIR, { recursive: true });
    cb(null, UPLOAD_DIR);
  },
  filename: (_req, file, cb) => {
    cb(null, shortFilename(file.originalname));
  },
});

const upload = multer({
  storage,
  limits: { fileSize: env.MAX_FILE_SIZE },
  fileFilter: (_req, file, cb) => {
    const allowed = ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.png', '.jpg', '.jpeg', '.txt', '.zip'];
    const ext = path.extname(file.originalname).toLowerCase();
    if (allowed.includes(ext)) return cb(null, true);
    cb(new Error(`Tipo no permitido: ${ext}`));
  },
});

// ── POST /archivos/upload ────────────────────────────────────
router.post('/upload', upload.single('archivo'), async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    if (!req.file) { sendError(res, 'No se recibió ningún archivo', 400); return; }

    const { idDocumento } = req.body;
    // Nombre guardado en disco (corto, cabe en varchar50)
    const rutaCorta = req.file.filename;                          // e.g. "83125525.pdf"
    // Nombre original truncado a 50 chars para columna archivo
    const archivoCorto = req.file.originalname.slice(0, 50);

    let idArchivo: number | null = null;

    if (idDocumento && !isNaN(Number(idDocumento))) {
      const pool = await getPool();
      const result = await pool.request()
        .input('idDocumento', sql.Int, Number(idDocumento))
        .input('archivo', sql.VarChar(50), archivoCorto)
        .input('ruta', sql.VarChar(50), rutaCorta)
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
      id_archivo: r.id_archivo_digital,
      id_documento: r.id_documento,
      nombre_archivo: r.archivo,
      ruta_archivo: r.ruta,
      fecha_subida: r.fecha_sistema,
      materia: r.materia,
      url: r.ruta ? `/uploads/${r.ruta}` : null,
    })));
  } catch (e) { next(e); }
});

// ── DELETE /archivos/:id ─────────────────────────────────────
router.delete('/:id', async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    const pool = await getPool();
    const res2 = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<{ ruta: string | null }>('SELECT ruta FROM archivo_digital WHERE id_archivo_digital = @id');

    const row = res2.recordset[0];
    if (!row) { sendError(res, 'Archivo no encontrado', 404); return; }

    if (row.ruta) {
      const fp = path.resolve(UPLOAD_DIR, row.ruta);
      if (fs.existsSync(fp)) fs.unlinkSync(fp);
    }
    await pool.request().input('id', sql.Int, Number(req.params.id))
      .query('DELETE FROM archivo_digital WHERE id_archivo_digital = @id');

    sendSuccess(res, null, 'Archivo eliminado');
  } catch (e) { next(e); }
});

export default router;
