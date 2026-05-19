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
        .input('nombreArchivo', sql.VarChar(200), req.file.originalname)
        .input('rutaArchivo', sql.VarChar(500), req.file.filename)
        .input('tipoMime', sql.VarChar(100), req.file.mimetype)
        .input('tamano', sql.Int, req.file.size)
        .query<{ id_archivo: number }>(`
          INSERT INTO archivo_digital (id_documento, nombre_archivo, ruta_archivo, tipo_mime, tamano, fecha_subida)
          OUTPUT INSERTED.id_archivo
          VALUES (@idDocumento, @nombreArchivo, @rutaArchivo, @tipoMime, @tamano, GETDATE())
        `);
      idArchivo = result.recordset[0]?.id_archivo ?? null;
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
      id_archivo: number; id_documento: number | null;
      nombre_archivo: string | null; ruta_archivo: string | null;
      tipo_mime: string | null; tamano: number | null; fecha_subida: Date;
      materia: string | null;
    }>(`
      SELECT a.id_archivo, a.id_documento, a.nombre_archivo, a.ruta_archivo,
             a.tipo_mime, a.tamano, a.fecha_subida,
             d.materia
      FROM archivo_digital a
      LEFT JOIN documento d ON a.id_documento = d.id_documento
      WHERE ${where}
      ORDER BY a.fecha_subida DESC
    `);
    sendSuccess(res, result.recordset.map((r: typeof result.recordset[0]) => ({
      ...r,
      url: r.ruta_archivo ? `/uploads/${r.ruta_archivo}` : null,
    })));
  } catch (e) { next(e); }
});

// DELETE /api/v1/archivos/:id — eliminar archivo
router.delete('/:id', async (req: Request, res: Response, next: NextFunction): Promise<void> => {
  try {
    const pool = await getPool();
    const result = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<{ ruta_archivo: string | null }>(`
        SELECT ruta_archivo FROM archivo_digital WHERE id_archivo = @id
      `);
    const row = result.recordset[0];
    if (!row) { sendError(res, 'Archivo no encontrado', 404); return; }

    // Eliminar de filesystem
    if (row.ruta_archivo) {
      const filePath = path.resolve(env.UPLOAD_DIR, row.ruta_archivo);
      if (fs.existsSync(filePath)) fs.unlinkSync(filePath);
    }
    await pool.request().input('id', sql.Int, Number(req.params.id))
      .query(`DELETE FROM archivo_digital WHERE id_archivo = @id`);

    sendSuccess(res, null, 'Archivo eliminado');
  } catch (e) { next(e); }
});

export default router;
