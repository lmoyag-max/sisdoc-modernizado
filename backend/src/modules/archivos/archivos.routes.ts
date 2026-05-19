import { Router, Request, Response, NextFunction } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendError, sendCreated } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { env } from '../../config/env';

const router = Router();
router.use(requireAuth);

// Configurar multer para almacenamiento en disco
const storage = multer.diskStorage({
  destination: (_req, _file, cb) => {
    const dir = path.resolve(env.UPLOAD_DIR);
    fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: (_req, file, cb) => {
    const ts = Date.now();
    const ext = path.extname(file.originalname);
    const base = path.basename(file.originalname, ext).replace(/[^a-zA-Z0-9_-]/g, '_');
    cb(null, `${ts}_${base}${ext}`);
  },
});

const upload = multer({
  storage,
  limits: { fileSize: env.MAX_FILE_SIZE },
  fileFilter: (_req, file, cb) => {
    const allowed = ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.png', '.jpg', '.jpeg', '.txt'];
    const ext = path.extname(file.originalname).toLowerCase();
    if (allowed.includes(ext)) return cb(null, true);
    cb(new Error(`Tipo de archivo no permitido: ${ext}`));
  },
});

// POST /api/v1/archivos/upload — subir archivo
router.post('/upload', upload.single('archivo'), async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    if (!req.file) {
      sendError(res, 'No se recibió ningún archivo', 400);
      return;
    }
    const { idDocumento } = req.body;
    const pool = await getPool();

    let idArchivo: number | null = null;

    if (idDocumento) {
      const result = await pool.request()
        .input('idDocumento', sql.Int, Number(idDocumento))
        .input('archivo', sql.VarChar(200), req.file.originalname)
        .input('ruta', sql.VarChar(500), req.file.filename)
        .query<{ id_archivo_digital: number }>(`
          INSERT INTO archivo_digital (id_documento, archivo, ruta, fecha_sistema)
          OUTPUT INSERTED.id_archivo_digital
          VALUES (@idDocumento, @archivo, @ruta, GETDATE())
        `);
      idArchivo = result.recordset[0]?.id_archivo_digital ?? null;
    }

    sendCreated(res, {
      idArchivo,
      nombreOriginal: req.file.originalname,
      nombreGuardado: req.file.filename,
      tamano: req.file.size,
      tipoMime: req.file.mimetype,
      url: `/uploads/${req.file.filename}`,
    }, 'Archivo subido correctamente');
  } catch (e) { next(e); }
});

// GET /api/v1/archivos — listar archivos del usuario o por documento
router.get('/', async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    const pool = await getPool();
    const { idDocumento } = req.query;
    const request = pool.request();
    let where = '1=1';
    if (idDocumento) {
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
    sendSuccess(res, result.recordset.map((r: typeof result.recordset[0]) => ({
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

// DELETE /api/v1/archivos/:id — eliminar archivo
router.delete('/:id', async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    const pool = await getPool();
    const result = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<{ ruta: string | null }>(`
        SELECT ruta FROM archivo_digital WHERE id_archivo_digital = @id
      `);
    const row = result.recordset[0];
    if (!row) { sendError(res, 'Archivo no encontrado', 404); return; }

    if (row.ruta) {
      const filePath = path.resolve(env.UPLOAD_DIR, row.ruta);
      if (fs.existsSync(filePath)) fs.unlinkSync(filePath);
    }
    await pool.request().input('id', sql.Int, Number(req.params.id))
      .query(`DELETE FROM archivo_digital WHERE id_archivo_digital = @id`);

    sendSuccess(res, null, 'Archivo eliminado');
  } catch (e) { next(e); }
});

export default router;
