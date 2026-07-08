import { Router, Request, Response, NextFunction } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import crypto from 'crypto';
import { requireAuth, requireRole } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendCreated, sendError } from '../../shared/utils/response';
import { logAuditoria } from '../../shared/utils/auditoria';
import { AuthenticatedRequest } from '../../shared/types/api.types';
import { env } from '../../config/env';
import { logger } from '../../shared/utils/logger';
import { verifyPassword } from '../auth/auth.service';

const router = Router();
router.use(requireAuth);

// ── Código corto de dependencia para el correlativo (MEMO-AÑO-COD-NNNNNN) ──
// Determinístico: 3 letras del nombre (sin tildes) + 3 dígitos del id —
// garantiza unicidad sin colisiones entre dependencias de nombre similar.
function generarCodigoDependencia(desc: string, idDependencia: number): string {
  const limpio = desc
    .toUpperCase()
    .replace(/[ÁÀÄÂ]/g, 'A').replace(/[ÉÈËÊ]/g, 'E').replace(/[ÍÌÏÎ]/g, 'I')
    .replace(/[ÓÒÖÔ]/g, 'O').replace(/[ÚÙÜÛ]/g, 'U').replace(/Ñ/g, 'N')
    .replace(/[^A-Z]/g, '');
  const prefijo = limpio.slice(0, 3) || 'DEP';
  const sufijo  = String(idDependencia).padStart(3, '0').slice(-3);
  return `${prefijo}${sufijo}`;
}

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
// 1. Busca en jefatura (nueva fuente de verdad)
// 2. Si no hay registro en jefatura, hace fallback a memo_firmante
// 3. Titular activo y vigente → usa titular
// 4. Si no, subrogante activo y vigente → usa subrogante
// 5. Si ninguno → { disponible: false, mensaje }
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

    // ── Intentar fuente primaria: jefatura ────────────────────
    let result = await pool.request()
      .input('idDep', sql.Int, idDep)
      .query<FirmanteRow>(`
        SELECT
          j.id_jefatura          AS id_firmante,
          j.id_dependencia,
          j.nombre_titular,
          j.cargo_titular,
          NULL                   AS firma_titular_ruta,
          NULL                   AS timbre_titular_ruta,
          j.firma_timbre_titular_ruta,
          j.activo_titular,
          j.vigencia_desde_titular,
          j.vigencia_hasta_titular,
          j.nombre_subrogante,
          j.cargo_subrogante,
          NULL                   AS firma_subrogante_ruta,
          NULL                   AS timbre_subrogante_ruta,
          j.firma_timbre_subrogante_ruta,
          j.activo_subrogante,
          j.vigencia_desde_sub,
          j.vigencia_hasta_sub,
          LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia
        FROM   jefatura    j
        JOIN   dependencia d ON d.id_dependencia = j.id_dependencia
        WHERE  j.id_dependencia = @idDep
      `);

    // ── Fallback a memo_firmante si no hay registro en jefatura ──
    if (!result.recordset[0]) {
      result = await pool.request()
        .input('idDep', sql.Int, idDep)
        .query<FirmanteRow>(`
          SELECT mf.*,
                 LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia
          FROM   memo_firmante mf
          JOIN   dependencia   d ON d.id_dependencia = mf.id_dependencia
          WHERE  mf.id_dependencia = @idDep
        `);
    }

    const row = result.recordset[0];
    if (!row) {
      sendSuccess(res, {
        disponible: false,
        mensaje: 'No existe configuración de firmante para tu servicio. Configúrala en Administración → Jefaturas.',
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
          mensaje: 'El firmante titular no tiene imagen de firma y timbre configurada. Súbela en Administración → Jefaturas.',
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
      mensaje: 'No hay firmante activo y vigente configurado para tu servicio. Verifica la configuración en Administración → Jefaturas.',
    });
  } catch (e) { next(e); }
});

// ── GET /memorandum/firmantes-disponibles ─────────────────────
// Devuelve la lista de firmantes disponibles para el memorándum del usuario.
// El usuario selecciona uno; si es subrogante pero no hay RUT configurado
// aún puede firmar (se advierte en frontend que FirmaGov necesitará el RUT).
router.get('/firmantes-disponibles', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const user  = (req as unknown as AuthenticatedRequest).user;
    const idDep = user.idDependencia;

    if (!idDep) {
      sendSuccess(res, { firmantes: [], mensaje: 'Tu usuario no tiene dependencia asignada.' });
      return;
    }

    const pool = await getPool();

    const result = await pool.request()
      .input('idDep', sql.Int, idDep)
      .query<{
        id_jefatura:                    number;
        id_dependencia:                 number;
        desc_dependencia:               string | null;
        nombre_titular:                 string;
        cargo_titular:                  string;
        rut_titular:                    string | null;
        firma_timbre_titular_ruta:      string | null;
        activo_titular:                 boolean;
        vigencia_desde_titular:         string | null;
        vigencia_hasta_titular:         string | null;
        nombre_subrogante:              string | null;
        cargo_subrogante:               string | null;
        rut_subrogante:                 string | null;
        firma_timbre_subrogante_ruta:   string | null;
        activo_subrogante:              boolean;
        vigencia_desde_sub:             string | null;
        vigencia_hasta_sub:             string | null;
        nombre_subrogante_2:            string | null;
        cargo_subrogante_2:             string | null;
        rut_subrogante_2:               string | null;
        firma_timbre_subrogante_2_ruta: string | null;
        activo_subrogante_2:            boolean;
        vigencia_desde_sub_2:           string | null;
        vigencia_hasta_sub_2:           string | null;
        id_usuario_titular:             number | null;
        usuario_titular_activo:         boolean | null;
        id_usuario_subrogante:          number | null;
        usuario_subrogante_activo:      boolean | null;
        id_usuario_subrogante_2:        number | null;
        usuario_subrogante_2_activo:    boolean | null;
      }>(`
        SELECT
          j.id_jefatura, j.id_dependencia,
          LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia,
          j.nombre_titular, j.cargo_titular, j.rut_titular,
          j.firma_timbre_titular_ruta,
          j.activo_titular, j.vigencia_desde_titular, j.vigencia_hasta_titular,
          j.nombre_subrogante, j.cargo_subrogante, j.rut_subrogante,
          j.firma_timbre_subrogante_ruta,
          j.activo_subrogante, j.vigencia_desde_sub, j.vigencia_hasta_sub,
          j.nombre_subrogante_2, j.cargo_subrogante_2, j.rut_subrogante_2,
          j.firma_timbre_subrogante_2_ruta,
          j.activo_subrogante_2, j.vigencia_desde_sub_2, j.vigencia_hasta_sub_2,
          j.id_usuario_titular,      ut.activo  AS usuario_titular_activo,
          j.id_usuario_subrogante,   us.activo  AS usuario_subrogante_activo,
          j.id_usuario_subrogante_2, us2.activo AS usuario_subrogante_2_activo
        FROM   jefatura    j
        JOIN   dependencia d ON d.id_dependencia = j.id_dependencia
        LEFT JOIN usuario ut  ON ut.id_usuario  = j.id_usuario_titular
        LEFT JOIN usuario us  ON us.id_usuario  = j.id_usuario_subrogante
        LEFT JOIN usuario us2 ON us2.id_usuario = j.id_usuario_subrogante_2
        WHERE  j.id_dependencia = @idDep
      `);

    const row = result.recordset[0];
    if (!row) {
      sendSuccess(res, {
        firmantes: [],
        mensaje: 'No existe configuración de jefatura para tu servicio.',
      });
      return;
    }

    type FirmanteDisponible = {
      tipo:               string;
      idFirmante:         number;
      nombre:             string;
      cargo:              string;
      rut:                string | null;
      firmaTimbreUrl:     string | null;
      dependencia:        string | null;
      idDependencia:      number;
      vigente:            boolean;
      usuarioVinculado:   { idUsuario: number } | null;
      estadoFirmaSimple:  'disponible' | 'sin_vincular' | 'usuario_inactivo';
      motivoNoHabilitada: string | null;
    };

    // Estado de habilitación de Firma Simple — no depende de vigencia (eso
    // ya se evalúa aparte en el campo `vigente`, existente y consumido hoy
    // por el frontend para el flujo de FirmaGOB).
    function calcularEstadoFirmaSimple(
      idUsuarioVinculado: number | null, usuarioActivo: boolean | null, firmaTimbreRuta: string | null,
    ): { estado: FirmanteDisponible['estadoFirmaSimple']; motivo: string | null } {
      if (!idUsuarioVinculado)     return { estado: 'sin_vincular',     motivo: 'Sin usuario DOC360 vinculado' };
      if (usuarioActivo === false) return { estado: 'usuario_inactivo', motivo: 'El usuario vinculado está inactivo' };
      if (!firmaTimbreRuta)        return { estado: 'sin_vincular',     motivo: 'Sin firma/timbre cargado' };
      return { estado: 'disponible', motivo: null };
    }

    const firmantes: FirmanteDisponible[] = [];

    if (row.activo_titular) {
      const fs_ = calcularEstadoFirmaSimple(row.id_usuario_titular, row.usuario_titular_activo, row.firma_timbre_titular_ruta);
      firmantes.push({
        tipo:           'TITULAR',
        idFirmante:     row.id_jefatura,
        nombre:         row.nombre_titular,
        cargo:          row.cargo_titular,
        rut:            row.rut_titular ?? null,
        firmaTimbreUrl: row.firma_timbre_titular_ruta
          ? `/uploads/config/memo/${row.firma_timbre_titular_ruta}`
          : null,
        dependencia:    row.desc_dependencia ?? null,
        idDependencia:  row.id_dependencia,
        vigente:        esFirmanteVigenteHoy(row.vigencia_desde_titular, row.vigencia_hasta_titular),
        usuarioVinculado:   row.id_usuario_titular ? { idUsuario: row.id_usuario_titular } : null,
        estadoFirmaSimple:  fs_.estado,
        motivoNoHabilitada: fs_.motivo,
      });
    }

    if (row.activo_subrogante && row.nombre_subrogante) {
      const fs_ = calcularEstadoFirmaSimple(row.id_usuario_subrogante, row.usuario_subrogante_activo, row.firma_timbre_subrogante_ruta);
      firmantes.push({
        tipo:           'SUBROGANTE',
        idFirmante:     row.id_jefatura,
        nombre:         row.nombre_subrogante,
        cargo:          row.cargo_subrogante ?? '',
        rut:            row.rut_subrogante ?? null,
        firmaTimbreUrl: row.firma_timbre_subrogante_ruta
          ? `/uploads/config/memo/${row.firma_timbre_subrogante_ruta}`
          : null,
        dependencia:    row.desc_dependencia ?? null,
        idDependencia:  row.id_dependencia,
        vigente:        esFirmanteVigenteHoy(row.vigencia_desde_sub, row.vigencia_hasta_sub),
        usuarioVinculado:   row.id_usuario_subrogante ? { idUsuario: row.id_usuario_subrogante } : null,
        estadoFirmaSimple:  fs_.estado,
        motivoNoHabilitada: fs_.motivo,
      });
    }

    if (row.activo_subrogante_2 && row.nombre_subrogante_2) {
      const fs_ = calcularEstadoFirmaSimple(row.id_usuario_subrogante_2, row.usuario_subrogante_2_activo, row.firma_timbre_subrogante_2_ruta);
      firmantes.push({
        tipo:           'SUBROGANTE_2',
        idFirmante:     row.id_jefatura,
        nombre:         row.nombre_subrogante_2,
        cargo:          row.cargo_subrogante_2 ?? '',
        rut:            row.rut_subrogante_2 ?? null,
        firmaTimbreUrl: row.firma_timbre_subrogante_2_ruta
          ? `/uploads/config/memo/${row.firma_timbre_subrogante_2_ruta}`
          : null,
        dependencia:    row.desc_dependencia ?? null,
        idDependencia:  row.id_dependencia,
        vigente:        esFirmanteVigenteHoy(row.vigencia_desde_sub_2, row.vigencia_hasta_sub_2),
        usuarioVinculado:   row.id_usuario_subrogante_2 ? { idUsuario: row.id_usuario_subrogante_2 } : null,
        estadoFirmaSimple:  fs_.estado,
        motivoNoHabilitada: fs_.motivo,
      });
    }

    sendSuccess(res, { firmantes });
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

    if (!body.idDocumento) {
      sendError(res, 'idDocumento es requerido', 400);
      return;
    }

    const pool = await getPool();
    const anio = new Date().getFullYear();

    // ── Código de dependencia (cada servicio numera su propia secuencia) ──
    // Sin dependencia conocida → bucket "GEN" (general), comparte el
    // contador legado (anio, id_dependencia=NULL) que ya existía antes de
    // este cambio — no rompe los correlativos previamente emitidos.
    const idDepKey = body.idDependencia ?? null;
    let codigoDep = 'GEN';
    if (idDepKey != null) {
      const depRes = await pool.request()
        .input('id', sql.Int, idDepKey)
        .query<{ desc_dependencia: string; cod_dependencia: string | null }>(
          'SELECT desc_dependencia, cod_dependencia FROM dependencia WHERE id_dependencia = @id'
        );
      const dep = depRes.recordset[0];
      if (dep) {
        codigoDep = dep.cod_dependencia ?? generarCodigoDependencia(dep.desc_dependencia, idDepKey);
        if (!dep.cod_dependencia) {
          await pool.request()
            .input('id',  sql.Int,         idDepKey)
            .input('cod', sql.VarChar(6),  codigoDep)
            .query('UPDATE dependencia SET cod_dependencia = @cod WHERE id_dependencia = @id');
        }
      }
    }

    // ── Asignación de correlativo: MAX(numero) + 1 sobre memos ACTIVOS ──
    // "Activo" = su documento todavía existe (el borrado es físico — ver
    // documento.repository.ts softDelete()). Si se borran todos los memos
    // de un año+servicio, el siguiente vuelve a partir en 1; si se borra uno
    // intermedio, ese número queda libre para el próximo memo nuevo (no
    // necesariamente el siguiente cronológico) — decisión de negocio
    // explícita: se prioriza no dejar números "huérfanos" por sobre la
    // garantía de unicidad histórica del correlativo entre documentos
    // distintos. TABLOCKX+HOLDLOCK: el cálculo del MAX y el INSERT ocurren
    // en una sola transacción para que dos confirmaciones concurrentes no
    // puedan calcular el mismo número.
    const ftBase = body.firmaTimbreRuta
      ? path.basename(body.firmaTimbreRuta)
      : (body.firmaRuta ? path.basename(body.firmaRuta) : null);

    const ultimoActivoRes = await pool.request()
      .input('anio',  sql.Int, anio)
      .input('idDep', sql.Int, idDepKey)
      .query<{ ultimo_activo: number | null }>(`
        SELECT MAX(mg.numero) AS ultimo_activo
        FROM memo_generado mg
        INNER JOIN documento d ON d.id_documento = mg.id_documento
        WHERE mg.anio = @anio AND ISNULL(mg.id_dependencia_origen, -1) = ISNULL(@idDep, -1)
      `);
    const ultimoActivo = ultimoActivoRes.recordset[0].ultimo_activo;
    logger.info(`[memorandum] Calculando correlativo — año=${anio} servicio=${codigoDep} (idDependencia=${idDepKey ?? 'GEN'}) último número activo encontrado=${ultimoActivo ?? '(ninguno)'}; se excluyen memos cuyo documento fue eliminado.`);

    const insertResult = await pool.request()
      .input('idDoc',         sql.Int,          body.idDocumento)
      .input('anio',          sql.Int,          anio)
      .input('idUsr',         sql.Int,          user.idUsuario)
      .input('idDep',         sql.Int,          idDepKey)
      .input('codigoDep',     sql.VarChar(6),   codigoDep)
      .input('materia',       sql.VarChar(250), (body.materia ?? '').substring(0, 250))
      .input('referencia',    sql.VarChar(250), (body.referencia ?? '').substring(0, 250) || null)
      .input('cuerpo',        sql.VarChar(8000), (body.cuerpo ?? '').substring(0, 8000) || null)
      .input('nomFirm',       sql.VarChar(100), body.nombreFirmante ?? null)
      .input('cargoFirm',     sql.VarChar(100), body.cargoFirmante ?? null)
      .input('tipoFirm',      sql.VarChar(20),  body.tipoFirmante ?? null)
      .input('ftRuta',        sql.VarChar(100), ftBase)
      .query<{ correlativo: string; numero: number }>(`
        BEGIN TRANSACTION;

        DECLARE @sigNumero INT;
        SELECT @sigNumero = ISNULL(MAX(mg.numero), 0) + 1
        FROM memo_generado mg WITH (TABLOCKX, HOLDLOCK)
        INNER JOIN documento d ON d.id_documento = mg.id_documento
        WHERE mg.anio = @anio AND ISNULL(mg.id_dependencia_origen, -1) = ISNULL(@idDep, -1);

        DECLARE @corr VARCHAR(30) = 'MEMO-' + CAST(@anio AS VARCHAR(4)) + '-' + @codigoDep + '-'
          + RIGHT('000000' + CAST(@sigNumero AS VARCHAR(6)), 6);

        INSERT INTO memo_generado
          (id_documento, correlativo, anio, numero, id_usuario_creador,
           id_dependencia_origen, materia, referencia, cuerpo,
           nombre_firmante, cargo_firmante, tipo_firmante,
           firma_timbre_ruta, generado_por_sistema, fecha_creacion)
        VALUES
          (@idDoc, @corr, @anio, @sigNumero, @idUsr,
           @idDep, @materia, @referencia, @cuerpo,
           @nomFirm, @cargoFirm, @tipoFirm,
           @ftRuta, 1, GETDATE());

        SELECT @corr AS correlativo, @sigNumero AS numero;

        COMMIT TRANSACTION;
      `);

    const { correlativo, numero } = insertResult.recordset[0];
    logger.info(`[memorandum] Correlativo asignado: ${correlativo} (documento ${body.idDocumento}).`);

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
      .input('corr',       sql.VarChar(30), correlativo)
      .input('idArchivo',  sql.Int,         idArchivo)
      .query('UPDATE memo_generado SET id_archivo_digital = @idArchivo WHERE correlativo = @corr');

    sendSuccess(res, null, 'Archivo vinculado');
  } catch (e) { next(e); }
});

// ═══════════════════════════════════════════════════════════════
// Firma Simple DOC360 — mecanismo interno alternativo a FirmaGOB.
// No usa servicio externo: el propio firmante reingresa su contraseña
// DOC360 para autorizar. Flujo en 2 fases porque el PDF se genera en el
// frontend (jsPDF, ver memorandum.generator.ts) y el sello final necesita
// datos que solo el backend puede emitir de forma confiable (código de
// verificación, hash "oficial").
// ═══════════════════════════════════════════════════════════════

const TTL_FIRMA_SIMPLE_MIN = 30;

type TipoJefatura = 'TITULAR' | 'SUBROGANTE' | 'SUBROGANTE_2';

interface JefaturaFirmanteRow {
  id_dependencia:                 number;
  desc_dependencia:               string | null;
  nombre_titular:                 string;
  cargo_titular:                  string;
  rut_titular:                    string | null;
  firma_timbre_titular_ruta:      string | null;
  activo_titular:                 boolean;
  vigencia_desde_titular:         string | null;
  vigencia_hasta_titular:         string | null;
  id_usuario_titular:             number | null;
  nombre_subrogante:              string | null;
  cargo_subrogante:                string | null;
  rut_subrogante:                 string | null;
  firma_timbre_subrogante_ruta:   string | null;
  activo_subrogante:              boolean;
  vigencia_desde_sub:             string | null;
  vigencia_hasta_sub:             string | null;
  id_usuario_subrogante:          number | null;
  nombre_subrogante_2:            string | null;
  cargo_subrogante_2:             string | null;
  rut_subrogante_2:               string | null;
  firma_timbre_subrogante_2_ruta: string | null;
  activo_subrogante_2:            boolean;
  vigencia_desde_sub_2:           string | null;
  vigencia_hasta_sub_2:           string | null;
  id_usuario_subrogante_2:        number | null;
}

// Extrae los campos del slot correspondiente (titular/subrogante/subrogante_2)
// de una fila de jefatura ya cargada — evita construir SQL dinámico por tipo.
function camposPorTipo(j: JefaturaFirmanteRow, tipo: TipoJefatura) {
  switch (tipo) {
    case 'TITULAR':
      return {
        nombre: j.nombre_titular, cargo: j.cargo_titular, rut: j.rut_titular,
        firmaTimbreRuta: j.firma_timbre_titular_ruta, activo: j.activo_titular,
        vigDesde: j.vigencia_desde_titular, vigHasta: j.vigencia_hasta_titular,
        idUsuario: j.id_usuario_titular,
      };
    case 'SUBROGANTE':
      return {
        nombre: j.nombre_subrogante, cargo: j.cargo_subrogante, rut: j.rut_subrogante,
        firmaTimbreRuta: j.firma_timbre_subrogante_ruta, activo: j.activo_subrogante,
        vigDesde: j.vigencia_desde_sub, vigHasta: j.vigencia_hasta_sub,
        idUsuario: j.id_usuario_subrogante,
      };
    case 'SUBROGANTE_2':
      return {
        nombre: j.nombre_subrogante_2, cargo: j.cargo_subrogante_2, rut: j.rut_subrogante_2,
        firmaTimbreRuta: j.firma_timbre_subrogante_2_ruta, activo: j.activo_subrogante_2,
        vigDesde: j.vigencia_desde_sub_2, vigHasta: j.vigencia_hasta_sub_2,
        idUsuario: j.id_usuario_subrogante_2,
      };
  }
}

// ── POST /memorandum/:id/firmar-simple ─────────────────────────
// Fase A: valida identidad del firmante y emite evidencia (hash del
// borrador + código de verificación). No despacha el documento todavía
// — eso ocurre en la Fase B, cuando el frontend sube el PDF ya sellado.
router.post('/:id/firmar-simple', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const user = (req as unknown as AuthenticatedRequest).user;
    const idDocumento = Number(req.params.id);
    const body = req.body as {
      idJefatura?: number; tipoJefatura?: TipoJefatura; password?: string; confirmacion?: boolean;
    };

    if (!body.idJefatura || !body.tipoJefatura || !body.password || body.confirmacion !== true) {
      sendError(res, 'idJefatura, tipoJefatura, password y confirmacion son requeridos', 400);
      return;
    }
    if (!['TITULAR', 'SUBROGANTE', 'SUBROGANTE_2'].includes(body.tipoJefatura)) {
      sendError(res, 'tipoJefatura inválido', 400);
      return;
    }

    const pool = await getPool();

    const memoRes = await pool.request().input('idDoc', sql.Int, idDocumento)
      .query<{
        id_documento: number; id_usuario_creador: number; id_dependencia_origen: number | null;
        id_archivo_digital: number | null; correlativo: string; id_estado_documento: number;
      }>(`
        SELECT mg.id_documento, mg.id_usuario_creador, mg.id_dependencia_origen,
               mg.id_archivo_digital, mg.correlativo, d.id_estado_documento
        FROM memo_generado mg
        JOIN documento d ON d.id_documento = mg.id_documento
        WHERE mg.id_documento = @idDoc
      `);
    const memo = memoRes.recordset[0];
    if (!memo) { sendError(res, 'Este documento no tiene un memorándum asociado', 404); return; }

    // Sin este chequeo, cualquier usuario autenticado podría usar este
    // endpoint para probar la contraseña de otra persona contra un
    // documento ajeno — FirmaGOB no lo necesita porque ahí el creador
    // conduce todo el flujo sobre sí mismo.
    if (memo.id_usuario_creador !== user.idUsuario) {
      sendError(res, 'Solo quien creó el memorándum puede solicitar su firma', 403);
      return;
    }
    if (memo.id_estado_documento !== 1) {
      sendError(res, 'El documento ya fue despachado o no está pendiente de firma', 409);
      return;
    }

    const yaFirmado = await pool.request().input('idDoc', sql.Int, idDocumento)
      .query<{ n: number }>(`SELECT COUNT(*) AS n FROM memorandum_firma_simple WHERE id_documento = @idDoc AND estado = 'firmado'`);
    if ((yaFirmado.recordset[0]?.n ?? 0) > 0) {
      sendError(res, 'Este memorándum ya fue firmado', 409);
      return;
    }

    const jefRes = await pool.request().input('id', sql.Int, body.idJefatura)
      .query<JefaturaFirmanteRow>(`
        SELECT j.*, LTRIM(RTRIM(d.desc_dependencia)) AS desc_dependencia
        FROM jefatura j JOIN dependencia d ON d.id_dependencia = j.id_dependencia
        WHERE j.id_jefatura = @id
      `);
    const jefatura = jefRes.recordset[0];
    if (!jefatura) { sendError(res, 'Jefatura no encontrada', 404); return; }
    if (jefatura.id_dependencia !== memo.id_dependencia_origen) {
      sendError(res, 'El firmante seleccionado no corresponde a la dependencia de este memorándum', 400);
      return;
    }

    const slot = camposPorTipo(jefatura, body.tipoJefatura);
    if (!slot.activo) { sendError(res, 'El cargo seleccionado no está activo', 400); return; }
    if (!esFirmanteVigenteHoy(slot.vigDesde, slot.vigHasta)) {
      sendError(res, 'El firmante seleccionado está fuera de su periodo de vigencia', 400);
      return;
    }
    if (!slot.idUsuario) {
      sendError(res, 'Este cargo no tiene un usuario DOC360 vinculado. Pide a un administrador vincularlo en Jefaturas.', 400);
      return;
    }
    if (!slot.firmaTimbreRuta) {
      sendError(res, 'Este cargo no tiene firma/timbre cargado', 400);
      return;
    }

    const usrRes = await pool.request().input('id', sql.Int, slot.idUsuario)
      .query<{ clave: string | null; clave_hash: string | null; activo: boolean }>(
        'SELECT clave, clave_hash, activo FROM usuario WHERE id_usuario = @id'
      );
    const usuarioFirmante = usrRes.recordset[0];
    if (!usuarioFirmante) { sendError(res, 'El usuario vinculado a este cargo ya no existe', 400); return; }
    if (!usuarioFirmante.activo) { sendError(res, 'El usuario vinculado a este cargo está inactivo', 409); return; }

    const passwordOk = await verifyPassword(body.password, usuarioFirmante.clave, usuarioFirmante.clave_hash);
    if (!passwordOk) {
      await logAuditoria(pool, {
        idUsuario: slot.idUsuario, accion: 'FIRMA_SIMPLE_PASSWORD_INCORRECTA',
        recurso: String(idDocumento), ip: req.ip ?? null,
      });
      sendError(res, 'Contraseña incorrecta', 401);
      return;
    }

    if (!memo.id_archivo_digital) {
      sendError(res, 'No se encontró el PDF borrador del memorándum', 409);
      return;
    }
    const arqRes = await pool.request().input('id', sql.Int, memo.id_archivo_digital)
      .query<{ ruta: string }>('SELECT ruta FROM archivo_digital WHERE id_archivo_digital = @id');
    const rutaBorrador = arqRes.recordset[0]?.ruta;
    const pdfPath = rutaBorrador ? path.join(path.resolve(env.UPLOAD_DIR), rutaBorrador) : null;
    if (!pdfPath || !fs.existsSync(pdfPath)) {
      sendError(res, 'No se encontró el archivo físico del PDF borrador', 409);
      return;
    }
    const hashOriginal = crypto.createHash('sha256').update(fs.readFileSync(pdfPath)).digest('hex');

    const anio = new Date().getFullYear();
    const insertRes = await pool.request()
      .input('idDoc',        sql.Int,          idDocumento)
      .input('corrMemo',     sql.VarChar(30),  memo.correlativo)
      .input('idJef',        sql.Int,          body.idJefatura)
      .input('tipoFirm',     sql.VarChar(20),  body.tipoJefatura)
      .input('idUsrFirm',    sql.Int,          slot.idUsuario)
      .input('idUsrSol',     sql.Int,          user.idUsuario)
      .input('nombreFirm',   sql.VarChar(100), slot.nombre)
      .input('cargoFirm',    sql.VarChar(100), slot.cargo)
      .input('rutFirm',      sql.VarChar(12),  slot.rut ?? null)
      .input('idDepFirm',    sql.Int,          jefatura.id_dependencia)
      .input('servFirm',     sql.VarChar(150), jefatura.desc_dependencia ?? null)
      .input('anio',         sql.Int,          anio)
      .input('hashOriginal', sql.VarChar(64),  hashOriginal)
      .input('idArchivo',    sql.Int,          memo.id_archivo_digital)
      .input('ip',           sql.VarChar(45),  req.ip ?? null)
      .input('userAgent',    sql.VarChar(500), (req.headers['user-agent'] ?? '').toString().substring(0, 500))
      .query<{ id: number; numero: number; codigo: string }>(`
        BEGIN TRANSACTION;

        DECLARE @sigNumero INT;
        SELECT @sigNumero = ISNULL(MAX(numero), 0) + 1
        FROM memorandum_firma_simple WITH (TABLOCKX, HOLDLOCK)
        WHERE anio = @anio;

        DECLARE @codigo VARCHAR(30) = 'DOC360-FS-' + CAST(@anio AS VARCHAR(4)) + '-'
          + RIGHT('000000' + CAST(@sigNumero AS VARCHAR(6)), 6);

        INSERT INTO memorandum_firma_simple
          (id_documento, correlativo_memo, id_jefatura, tipo_firmante,
           id_usuario_firmante, id_usuario_solicitante,
           nombre_firmante, cargo_firmante, rut_firmante,
           id_dependencia_firmante, servicio_firmante,
           anio, numero, codigo_verificacion, hash_pdf_original, id_archivo_digital,
           estado, ip_solicitud, user_agent_solicitud, fecha_creacion)
        VALUES
          (@idDoc, @corrMemo, @idJef, @tipoFirm,
           @idUsrFirm, @idUsrSol,
           @nombreFirm, @cargoFirm, @rutFirm,
           @idDepFirm, @servFirm,
           @anio, @sigNumero, @codigo, @hashOriginal, @idArchivo,
           'validado', @ip, @userAgent, GETDATE());

        SELECT SCOPE_IDENTITY() AS id, @sigNumero AS numero, @codigo AS codigo;

        COMMIT TRANSACTION;
      `);

    const idFirmaSimple = insertRes.recordset[0].id;
    const codigoVerificacion = insertRes.recordset[0].codigo;

    await logAuditoria(pool, {
      idUsuario: user.idUsuario, accion: 'FIRMA_SIMPLE_VALIDADA',
      recurso: String(idDocumento),
      detalle: `codigo=${codigoVerificacion} firmante=${slot.nombre} (${body.tipoJefatura})`,
      ip: req.ip ?? null,
    });

    sendCreated(res, {
      idFirmaSimple,
      codigoVerificacion,
      hashOriginalCorto: hashOriginal.substring(0, 16),
      fechaFirma: new Date().toISOString(),
      firmante: { nombre: slot.nombre, cargo: slot.cargo, servicio: jefatura.desc_dependencia ?? null, tipo: body.tipoJefatura },
    }, 'Identidad validada — genera y sube el PDF final para completar la firma');
  } catch (e) { next(e); }
});

const uploadFirmaSimple = multer({
  storage: multer.memoryStorage(),
  limits:  { fileSize: env.MAX_FILE_SIZE },
  fileFilter: (_req, file, cb) => { cb(null, path.extname(file.originalname).toLowerCase() === '.pdf'); },
});

// ── PATCH /memorandum/:id/firmar-simple/:idFirmaSimple/completar ──
// Fase B: recibe el PDF final (ya con el sello dibujado por el frontend),
// recalcula el hash EN EL SERVIDOR (nunca se confía en un hash que mande
// el cliente — mismo principio que ya usa firma-gob/solicitar), sobrescribe
// el mismo archivo_digital y despacha el documento.
router.patch('/:id/firmar-simple/:idFirmaSimple/completar',
  uploadFirmaSimple.single('archivo'),
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      const user = (req as unknown as AuthenticatedRequest).user;
      const idDocumento    = Number(req.params.id);
      const idFirmaSimple  = Number(req.params.idFirmaSimple);

      if (!req.file) { sendError(res, 'No se recibió el PDF firmado', 400); return; }

      const pool = await getPool();

      const fsRes = await pool.request().input('id', sql.Int, idFirmaSimple)
        .query<{
          id_documento: number; id_usuario_solicitante: number | null; estado: string;
          minutos_transcurridos: number; correlativo_memo: string | null; id_archivo_digital: number | null;
        }>(`
          SELECT id_documento, id_usuario_solicitante, estado,
                 DATEDIFF(MINUTE, fecha_creacion, GETDATE()) AS minutos_transcurridos,
                 correlativo_memo, id_archivo_digital
          FROM memorandum_firma_simple WHERE id = @id
        `);
      const registro = fsRes.recordset[0];
      if (!registro || registro.id_documento !== idDocumento) {
        sendError(res, 'Registro de firma simple no encontrado', 404); return;
      }
      if (registro.id_usuario_solicitante !== user.idUsuario) {
        sendError(res, 'Solo quien solicitó la firma puede completarla', 403); return;
      }
      if (registro.estado !== 'validado') {
        sendError(res, `Este registro ya no está pendiente de completar (estado: ${registro.estado})`, 409); return;
      }

      // DATEDIFF calculado en el propio SQL Server (fecha_creacion y GETDATE()
      // son ambos del mismo reloj/zona horaria) — evita el desfase que se
      // produce al comparar un DATETIME de BD contra Date.now() en Node
      // cuando el servidor de BD no está en UTC.
      if (registro.minutos_transcurridos > TTL_FIRMA_SIMPLE_MIN) {
        await pool.request().input('id', sql.Int, idFirmaSimple)
          .query(`UPDATE memorandum_firma_simple SET estado = 'expirado' WHERE id = @id`);
        sendError(res, 'La validación de identidad expiró. Vuelve a firmar desde el inicio.', 409);
        return;
      }
      if (!registro.id_archivo_digital) {
        sendError(res, 'No se encontró el archivo original asociado a este registro', 409); return;
      }

      const signedBuffer = req.file.buffer;
      const hashFirmado  = crypto.createHash('sha256').update(signedBuffer).digest('hex');

      const uploadDir   = path.resolve(env.UPLOAD_DIR);
      const nombreFinal = `${(registro.correlativo_memo ?? `FS-${idFirmaSimple}`).replace(/[^A-Za-z0-9_-]/g, '_')}.pdf`.slice(0, 50);
      const signedPath  = path.join(uploadDir, nombreFinal);

      const arqPrevRes = await pool.request().input('id', sql.Int, registro.id_archivo_digital)
        .query<{ ruta: string }>('SELECT ruta FROM archivo_digital WHERE id_archivo_digital = @id');
      const rutaAnterior = arqPrevRes.recordset[0]?.ruta;

      fs.writeFileSync(signedPath, signedBuffer);
      if (rutaAnterior && rutaAnterior !== nombreFinal) {
        const rutaAnteriorAbs = path.join(uploadDir, rutaAnterior);
        try { if (fs.existsSync(rutaAnteriorAbs)) fs.unlinkSync(rutaAnteriorAbs); } catch (e) {
          logger.warn(`[FirmaSimple] No se pudo eliminar el archivo físico del borrador (${rutaAnteriorAbs}): ${e}`);
        }
      }

      // Mismo id_archivo_digital que el borrador — evita dejar dos adjuntos
      // (mismo patrón que firma-gob/solicitar).
      await pool.request()
        .input('id',   sql.Int,         registro.id_archivo_digital)
        .input('arq',  sql.VarChar(50), nombreFinal)
        .input('ruta', sql.VarChar(50), nombreFinal)
        .input('tam',  sql.Int,         signedBuffer.length)
        .query(`
          UPDATE archivo_digital
          SET    archivo = @arq, ruta = @ruta, tamano = @tam, fecha_update = GETDATE()
          WHERE  id_archivo_digital = @id
        `);

      await pool.request()
        .input('id',   sql.Int,         idFirmaSimple)
        .input('hash', sql.VarChar(64), hashFirmado)
        .query(`UPDATE memorandum_firma_simple SET hash_pdf_firmado = @hash, estado = 'firmado', fecha_firmado = GETDATE() WHERE id = @id`);

      await pool.request()
        .input('idDoc', sql.Int, idDocumento)
        .input('idArq', sql.Int, registro.id_archivo_digital)
        .query(`UPDATE memo_generado SET id_archivo_firmado = @idArq, estado_firma = 'firmado' WHERE id_documento = @idDoc`);

      await pool.request().input('idDoc', sql.Int, idDocumento)
        .query(`UPDATE documento SET id_estado_documento = 2, fecha_update = GETDATE() WHERE id_documento = @idDoc AND id_estado_documento = 1`);
      await pool.request().input('idDoc', sql.Int, idDocumento)
        .query(`UPDATE tramite SET id_estado_tramite = 2, fecha_despacho = GETDATE(), fecha_update = GETDATE() WHERE id_documento = @idDoc AND id_estado_tramite = 1`);

      await logAuditoria(pool, {
        idUsuario: user.idUsuario, accion: 'FIRMA_SIMPLE_COMPLETADA',
        recurso: String(idDocumento), detalle: `idFirmaSimple=${idFirmaSimple} hash=${hashFirmado.substring(0, 16)}`,
        ip: req.ip ?? null,
      });

      sendSuccess(res, { idArchivoFirmado: registro.id_archivo_digital, filename: nombreFinal },
        'Documento firmado y despachado correctamente');
    } catch (e) { next(e); }
  }
);

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
