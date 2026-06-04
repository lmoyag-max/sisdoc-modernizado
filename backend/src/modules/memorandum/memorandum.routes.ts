import { Router, Request, Response, NextFunction } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { requireAuth, requireRole } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendCreated, sendError } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { env } from '../../config/env';

const router = Router();
router.use(requireAuth);

// ── Directorio para imágenes de firma/timbre ──────────────────
const MEMO_IMG_DIR = path.resolve(env.UPLOAD_DIR, 'config', 'memo');
fs.mkdirSync(MEMO_IMG_DIR, { recursive: true });

// ── Multer para imágenes de firma/timbre ──────────────────────
const imgStorage = multer.diskStorage({
  destination: (_req, _file, cb) => { cb(null, MEMO_IMG_DIR); },
  filename: (_req, file, cb) => {
    const ext  = path.extname(file.originalname).toLowerCase();
    const ts   = Date.now().toString().slice(-8);
    cb(null, `${ts}${ext}`);
  },
});

const uploadImg = multer({
  storage: imgStorage,
  limits:  { fileSize: 5 * 1024 * 1024 },
  fileFilter: (_req, file, cb) => {
    const allowed = ['.png', '.jpg', '.jpeg', '.webp'];
    cb(null, allowed.includes(path.extname(file.originalname).toLowerCase()));
  },
});

// ── Tipos locales ─────────────────────────────────────────────
interface FirmanteRow {
  id_firmante:                   number;
  nombre_titular:                string;
  cargo_titular:                 string;
  firma_titular_ruta:            string | null;   // legado — no se usa en nuevos flujos
  timbre_titular_ruta:           string | null;   // legado
  firma_timbre_titular_ruta:     string | null;   // imagen combinada (prioridad)
  activo_titular:                boolean;
  vigencia_desde_titular:        string | null;
  vigencia_hasta_titular:        string | null;
  nombre_subrogante:             string | null;
  cargo_subrogante:              string | null;
  firma_subrogante_ruta:         string | null;   // legado
  timbre_subrogante_ruta:        string | null;   // legado
  firma_timbre_subrogante_ruta:  string | null;   // imagen combinada (prioridad)
  activo_subrogante:             boolean;
  vigencia_desde_sub:            string | null;
  vigencia_hasta_sub:            string | null;
  id_dependencia:                number;
  desc_dependencia:              string | null;
}

// Determina si una configuración de firmante está vigente hoy
function esFirmanteVigenteHoy(desde: string | null, hasta: string | null): boolean {
  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);
  if (desde) {
    const d = new Date(desde); d.setHours(0, 0, 0, 0);
    if (hoy < d) return false;
  }
  if (hasta) {
    const h = new Date(hasta); h.setHours(23, 59, 59, 999);
    if (hoy > h) return false;
  }
  return true;
}

// ── GET /memorandum/firmante-activo ───────────────────────────
// Resuelve el firmante del usuario autenticado:
// 1. Titular activo y vigente → usa titular
// 2. Si no, subrogante activo y vigente → usa subrogante
// 3. Si ninguno → { disponible: false, mensaje }
router.get('/firmante-activo', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const user = (req as unknown as AuthenticatedRequest).user;
    const idDep = user.idDependencia;

    if (!idDep) {
      sendSuccess(res, {
        disponible: false,
        mensaje: 'Tu usuario no tiene una dependencia asignada. Contacta al administrador.',
      });
      return;
    }

    const pool = await getPool();
    const result = await pool.request()
      .input('idDep', sql.Int, idDep)
      .query<FirmanteRow>(`
        SELECT mf.*,
               LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia
        FROM   memo_firmante mf
        JOIN   dependencia   d ON d.id_dependencia = mf.id_dependencia
        WHERE  mf.id_dependencia = @idDep
      `);

    const row = result.recordset[0];
    if (!row) {
      sendSuccess(res, {
        disponible: false,
        mensaje: 'No existe configuración de firmante para tu servicio. Configúrala en Administración → Configuración.',
      });
      return;
    }

    // Helper: resuelve la URL de la imagen combinada.
    // Prioriza firma_timbre_*_ruta; cae en firma_*_ruta si no existe (compat legado).
    const resolverFirmaTimbreUrl = (combinada: string | null, legado: string | null): string | null => {
      const ruta = combinada ?? legado;
      return ruta ? `/uploads/config/memo/${ruta}` : null;
    };

    // Intentar titular
    const titularOk = row.activo_titular &&
      esFirmanteVigenteHoy(row.vigencia_desde_titular, row.vigencia_hasta_titular);

    if (titularOk) {
      const firmaTimbreUrl = resolverFirmaTimbreUrl(row.firma_timbre_titular_ruta, row.firma_titular_ruta);
      if (!firmaTimbreUrl) {
        sendSuccess(res, {
          disponible: false,
          mensaje: 'El firmante titular no tiene imagen de firma y timbre configurada. Súbela en Configuración → Firmantes.',
        });
        return;
      }
      sendSuccess(res, {
        disponible:      true,
        tipo:            'TITULAR',
        idFirmante:      row.id_firmante,
        nombre:          row.nombre_titular,
        cargo:           row.cargo_titular,
        firmaTimbreUrl,
        dependencia:     row.desc_dependencia,
        idDependencia:   row.id_dependencia,
      });
      return;
    }

    // Intentar subrogante
    const subrOk = row.activo_subrogante && row.nombre_subrogante &&
      esFirmanteVigenteHoy(row.vigencia_desde_sub, row.vigencia_hasta_sub);

    if (subrOk) {
      const firmaTimbreUrl = resolverFirmaTimbreUrl(row.firma_timbre_subrogante_ruta, row.firma_subrogante_ruta);
      if (!firmaTimbreUrl) {
        sendSuccess(res, {
          disponible: false,
          mensaje: 'El firmante subrogante no tiene imagen de firma y timbre configurada.',
        });
        return;
      }
      sendSuccess(res, {
        disponible:      true,
        tipo:            'SUBROGANTE',
        idFirmante:      row.id_firmante,
        nombre:          row.nombre_subrogante!,
        cargo:           row.cargo_subrogante!,
        firmaTimbreUrl,
        dependencia:     row.desc_dependencia,
        idDependencia:   row.id_dependencia,
      });
      return;
    }

    sendSuccess(res, {
      disponible: false,
      mensaje: 'No hay firmante activo y vigente configurado para tu servicio. Verifica la configuración en Administración → Firmantes.',
    });
  } catch (e) { next(e); }
});

// ── POST /memorandum/confirmar ────────────────────────────────
// Asigna correlativo definitivo de forma atómica y registra en memo_generado.
// El frontend luego genera el PDF final con ese correlativo y lo sube
// mediante el endpoint existente POST /archivos/upload.
router.post('/confirmar', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const user = (req as unknown as AuthenticatedRequest).user;
    const body = req.body as {
      idDocumento:        number;
      materia:            string;
      referencia?:        string;
      cuerpo?:            string;
      nombreFirmante:     string;
      cargoFirmante:      string;
      tipoFirmante:       string;
      firmaTimbreRuta?:   string;   // imagen combinada (nuevo)
      firmaRuta?:         string;   // legado — ignorado si hay firmaTimbreRuta
      timbreRuta?:        string;   // legado
      idDependencia?:     number;
    };

    if (!body.idDocumento || !body.materia) {
      sendError(res, 'idDocumento y materia son requeridos', 400);
      return;
    }

    const pool = await getPool();
    const anio = new Date().getFullYear();

    // ── Asignación atómica de correlativo ─────────────────────
    // MERGE garantiza upsert; UPDLOCK+SERIALIZABLE evitan race conditions.
    const corrResult = await pool.request()
      .input('anio', sql.Int, anio)
      .query<{ ultimo_numero: number }>(`
        BEGIN TRANSACTION;

        MERGE memo_correlativo WITH (HOLDLOCK) AS target
        USING (SELECT @anio AS anio) AS source ON target.anio = source.anio
        WHEN MATCHED THEN
          UPDATE SET ultimo_numero = ultimo_numero + 1, fecha_update = GETDATE()
        WHEN NOT MATCHED THEN
          INSERT (anio, ultimo_numero, fecha_update)
          VALUES (@anio, 1, GETDATE());

        SELECT ultimo_numero FROM memo_correlativo WHERE anio = @anio;

        COMMIT TRANSACTION;
      `);

    const numero     = corrResult.recordset[0].ultimo_numero;
    const correlativo = `MEMO-${anio}-${String(numero).padStart(6, '0')}`;

    // ── Insertar en memo_generado ─────────────────────────────
    // Prioriza firmaTimbreRuta (imagen combinada); cae en firmaRuta legado si no viene.
    const ftBase = body.firmaTimbreRuta
      ? path.basename(body.firmaTimbreRuta)
      : (body.firmaRuta ? path.basename(body.firmaRuta) : null);

    await pool.request()
      .input('idDoc',         sql.Int,          body.idDocumento)
      .input('corr',          sql.VarChar(20),  correlativo)
      .input('anio',          sql.Int,          anio)
      .input('numero',        sql.Int,          numero)
      .input('idUsr',         sql.Int,          user.idUsuario)
      .input('idDep',         sql.Int,          body.idDependencia ?? null)
      .input('materia',       sql.VarChar(250), body.materia.substring(0, 250))
      .input('referencia',    sql.VarChar(250), (body.referencia ?? '').substring(0, 250) || null)
      .input('cuerpo',        sql.VarChar(8000), (body.cuerpo ?? '').substring(0, 8000) || null)
      .input('nomFirm',       sql.VarChar(100), body.nombreFirmante ?? null)
      .input('cargoFirm',     sql.VarChar(100), body.cargoFirmante ?? null)
      .input('tipoFirm',      sql.VarChar(10),  body.tipoFirmante ?? null)
      .input('ftRuta',        sql.VarChar(100), ftBase)
      .query(`
        INSERT INTO memo_generado
          (id_documento, correlativo, anio, numero, id_usuario_creador,
           id_dependencia_origen, materia, referencia, cuerpo,
           nombre_firmante, cargo_firmante, tipo_firmante,
           firma_timbre_ruta, generado_por_sistema, fecha_creacion)
        VALUES
          (@idDoc, @corr, @anio, @numero, @idUsr,
           @idDep, @materia, @referencia, @cuerpo,
           @nomFirm, @cargoFirm, @tipoFirm,
           @ftRuta, 1, GETDATE())
      `);

    sendCreated(res, { correlativo, numero, anio }, 'Correlativo asignado');
  } catch (e) { next(e); }
});

// ── PATCH /memorandum/vincular-archivo ────────────────────────
// Vincula el idArchivo subido con el registro memo_generado.
// Llamado por el frontend después de subir el PDF vía /archivos/upload.
router.patch('/vincular-archivo', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { correlativo, idArchivo } = req.body as { correlativo: string; idArchivo: number };
    if (!correlativo || !idArchivo) { sendError(res, 'correlativo e idArchivo requeridos', 400); return; }

    const pool = await getPool();
    await pool.request()
      .input('corr',       sql.VarChar(20), correlativo)
      .input('idArchivo',  sql.Int,         idArchivo)
      .query('UPDATE memo_generado SET id_archivo_digital = @idArchivo WHERE correlativo = @corr');

    sendSuccess(res, null, 'Archivo vinculado');
  } catch (e) { next(e); }
});

// ═══════════════════════════════════════════════════════════════
// Gestión de firmantes — solo admin y of.partes
// ═══════════════════════════════════════════════════════════════

const soloAdmin = [requireRole('admin', 'of.partes')];

// ── GET /memorandum/firmantes ─────────────────────────────────
router.get('/firmantes', ...soloAdmin, async (_req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const result = await pool.request().query<FirmanteRow & { desc_dependencia: string }>(`
      SELECT mf.*, LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia
      FROM memo_firmante mf
      JOIN dependencia d ON d.id_dependencia = mf.id_dependencia
      ORDER BY d.desc_dependencia
    `);

    sendSuccess(res, result.recordset.map((r) => mapFirmante(r)));
  } catch (e) { next(e); }
});

// ── GET /memorandum/firmantes/:id ─────────────────────────────
router.get('/firmantes/:id', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const result = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<FirmanteRow & { desc_dependencia: string }>(`
        SELECT mf.*, LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia
        FROM memo_firmante mf
        JOIN dependencia d ON d.id_dependencia = mf.id_dependencia
        WHERE mf.id_firmante = @id
      `);
    const row = result.recordset[0];
    if (!row) { sendError(res, 'Firmante no encontrado', 404); return; }
    sendSuccess(res, mapFirmante(row));
  } catch (e) { next(e); }
});

// ── POST /memorandum/firmantes ────────────────────────────────
router.post('/firmantes', ...soloAdmin, async (req: Request, res: Response, next: NextFunction) => {
  try {
    const body = req.body as {
      idDependencia:         number;
      nombreTitular:         string;
      cargoTitular:          string;
      activoTitular?:        boolean;
      vigenciaDesde?:        string;
      vigenciaHasta?:        string;
      nombreSubrogante?:     string;
      cargoSubrogante?:      string;
      activoSubrogante?:     boolean;
      vigenciaDesdeSubr?:    string;
      vigenciaHastaSubr?:    string;
    };

    if (!body.idDependencia || !body.nombreTitular || !body.cargoTitular) {
      sendError(res, 'idDependencia, nombreTitular y cargoTitular son requeridos', 400);
      return;
    }

    const pool = await getPool();

    // UPSERT — si ya existe para esa dependencia, actualiza
    const exists = await pool.request()
      .input('idDep', sql.Int, body.idDependencia)
      .query<{ id_firmante: number }>('SELECT id_firmante FROM memo_firmante WHERE id_dependencia = @idDep');

    if (exists.recordset[0]) {
      const id = exists.recordset[0].id_firmante;
      await pool.request()
        .input('id',           sql.Int,         id)
        .input('nomTit',       sql.VarChar(100), body.nombreTitular)
        .input('cargoTit',     sql.VarChar(100), body.cargoTitular)
        .input('activoTit',    sql.Bit,          body.activoTitular !== false ? 1 : 0)
        .input('vDesde',       sql.Date,         body.vigenciaDesde   ?? null)
        .input('vHasta',       sql.Date,         body.vigenciaHasta   ?? null)
        .input('nomSubr',      sql.VarChar(100), body.nombreSubrogante  ?? null)
        .input('cargoSubr',    sql.VarChar(100), body.cargoSubrogante   ?? null)
        .input('activoSubr',   sql.Bit,          body.activoSubrogante  ? 1 : 0)
        .input('vDesdeSubr',   sql.Date,         body.vigenciaDesdeSubr ?? null)
        .input('vHastaSubr',   sql.Date,         body.vigenciaHastaSubr ?? null)
        .query(`
          UPDATE memo_firmante SET
            nombre_titular = @nomTit, cargo_titular = @cargoTit,
            activo_titular = @activoTit,
            vigencia_desde_titular = @vDesde, vigencia_hasta_titular = @vHasta,
            nombre_subrogante = @nomSubr, cargo_subrogante = @cargoSubr,
            activo_subrogante = @activoSubr,
            vigencia_desde_sub = @vDesdeSubr, vigencia_hasta_sub = @vHastaSubr,
            fecha_update = GETDATE()
          WHERE id_firmante = @id
        `);
      sendSuccess(res, { id }, 'Firmante actualizado');
    } else {
      const ins = await pool.request()
        .input('idDep',        sql.Int,         body.idDependencia)
        .input('nomTit',       sql.VarChar(100), body.nombreTitular)
        .input('cargoTit',     sql.VarChar(100), body.cargoTitular)
        .input('activoTit',    sql.Bit,          body.activoTitular !== false ? 1 : 0)
        .input('vDesde',       sql.Date,         body.vigenciaDesde   ?? null)
        .input('vHasta',       sql.Date,         body.vigenciaHasta   ?? null)
        .input('nomSubr',      sql.VarChar(100), body.nombreSubrogante  ?? null)
        .input('cargoSubr',    sql.VarChar(100), body.cargoSubrogante   ?? null)
        .input('activoSubr',   sql.Bit,          body.activoSubrogante  ? 1 : 0)
        .input('vDesdeSubr',   sql.Date,         body.vigenciaDesdeSubr ?? null)
        .input('vHastaSubr',   sql.Date,         body.vigenciaHastaSubr ?? null)
        .query<{ id: number }>(`
          INSERT INTO memo_firmante
            (id_dependencia, nombre_titular, cargo_titular, activo_titular,
             vigencia_desde_titular, vigencia_hasta_titular,
             nombre_subrogante, cargo_subrogante, activo_subrogante,
             vigencia_desde_sub, vigencia_hasta_sub)
          OUTPUT INSERTED.id_firmante AS id
          VALUES
            (@idDep, @nomTit, @cargoTit, @activoTit,
             @vDesde, @vHasta,
             @nomSubr, @cargoSubr, @activoSubr,
             @vDesdeSubr, @vHastaSubr)
        `);
      sendCreated(res, { id: ins.recordset[0].id }, 'Firmante creado');
    }
  } catch (e) { next(e); }
});

// ── POST /memorandum/firmantes/:id/imagen ─────────────────────
// Sube una imagen de firma o timbre.
// Query param: tipo = 'firma_titular' | 'timbre_titular' |
//                     'firma_subrogante' | 'timbre_subrogante'
router.post('/firmantes/:id/imagen',
  ...soloAdmin,
  uploadImg.single('imagen'),
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      if (!req.file) { sendError(res, 'No se recibió imagen', 400); return; }

      const id   = Number(req.params.id);
      const tipo = String(req.query.tipo ?? '');
      const cols: Record<string, string> = {
        firma_timbre_titular:     'firma_timbre_titular_ruta',    // nuevo campo combinado
        firma_timbre_subrogante:  'firma_timbre_subrogante_ruta', // nuevo campo combinado
        // legado — mantenidos para compat con registros anteriores
        firma_titular:            'firma_titular_ruta',
        timbre_titular:           'timbre_titular_ruta',
        firma_subrogante:         'firma_subrogante_ruta',
        timbre_subrogante:        'timbre_subrogante_ruta',
      };
      const col = cols[tipo];
      if (!col) {
        fs.unlinkSync(req.file.path);
        sendError(res, `tipo inválido: ${tipo}`, 400);
        return;
      }

      const pool = await getPool();

      // Eliminar imagen anterior si existe
      const prev = await pool.request()
        .input('id', sql.Int, id)
        .query<Record<string, string | null>>(`SELECT ${col} AS ruta FROM memo_firmante WHERE id_firmante = @id`);
      const rutaAnterior = prev.recordset[0]?.ruta;
      if (rutaAnterior) {
        const fp = path.join(MEMO_IMG_DIR, rutaAnterior);
        if (fs.existsSync(fp)) fs.unlinkSync(fp);
      }

      await pool.request()
        .input('ruta', sql.VarChar(100), req.file.filename)
        .input('id',   sql.Int,          id)
        .query(`UPDATE memo_firmante SET ${col} = @ruta, fecha_update = GETDATE() WHERE id_firmante = @id`);

      sendSuccess(res, {
        url:      `/uploads/config/memo/${req.file.filename}`,
        filename: req.file.filename,
      }, 'Imagen guardada');
    } catch (e) { next(e); }
  }
);

// ── Mapper helper ──────────────────────────────────────────────
function mapFirmante(r: FirmanteRow & { desc_dependencia?: string }) {
  return {
    id:               r.id_firmante,
    idDependencia:    r.id_dependencia,
    dependencia:      r.desc_dependencia ?? null,
    titular: {
      nombre:           r.nombre_titular,
      cargo:            r.cargo_titular,
      // Prioriza imagen combinada; fallback a firma legado si existe
      firmaTimbreUrl:   (r.firma_timbre_titular_ruta ?? r.firma_titular_ruta)
                          ? `/uploads/config/memo/${r.firma_timbre_titular_ruta ?? r.firma_titular_ruta}`
                          : null,
      activo:           !!r.activo_titular,
      vigenciaDesde:    r.vigencia_desde_titular ?? null,
      vigenciaHasta:    r.vigencia_hasta_titular ?? null,
    },
    subrogante: {
      nombre:           r.nombre_subrogante    ?? null,
      cargo:            r.cargo_subrogante     ?? null,
      firmaTimbreUrl:   (r.firma_timbre_subrogante_ruta ?? r.firma_subrogante_ruta)
                          ? `/uploads/config/memo/${r.firma_timbre_subrogante_ruta ?? r.firma_subrogante_ruta}`
                          : null,
      activo:           !!r.activo_subrogante,
      vigenciaDesde:    r.vigencia_desde_sub  ?? null,
      vigenciaHasta:    r.vigencia_hasta_sub  ?? null,
    },
  };
}

export default router;
