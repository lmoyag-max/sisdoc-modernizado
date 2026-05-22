import { Router } from 'express';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendPaginated, sendForbidden, buildPaginationMeta } from '../../shared/utils/response';
import { AuthenticatedRequest } from '../../shared/types/api.types';

const router = Router();
router.use(requireAuth);

function canSeeExternals(roles: string[]): boolean {
  return roles.includes('admin') || roles.includes('of.partes');
}

function hasFullAccess(user: AuthenticatedRequest['user']): boolean {
  return user.roles.includes('admin') || user.todosServicios === true;
}

// Columnas y JOINs comunes para tramite
const SEL_COLS = `
  t.id_seguimiento, t.id_documento,
  d.materia, d.num_interno,
  td.desc_tipo_documento,
  et.id_estado_tramite, et.desc_estado_tramite,
  CASE t.tipo_procedencia
    WHEN 'D' THEN dep_p.desc_dependencia
    WHEN 'E' THEN dep_e_p.desc_dependencia_externa
    ELSE 'Interno'
  END AS desc_procedencia,
  CASE t.tipo_destinatario
    WHEN 'D' THEN dep_d.desc_dependencia
    WHEN 'E' THEN dep_e_d.desc_dependencia_externa
    ELSE 'Interno'
  END AS desc_destino,
  t.fecha_sistema, t.observaciones
`;

const SEL_JOINS = `
  LEFT JOIN documento            d       ON t.id_documento        = d.id_documento
  LEFT JOIN tipo_documento       td      ON d.id_tipo_documento   = td.id_tipo_documento
  LEFT JOIN estado_tramite       et      ON t.id_estado_tramite   = et.id_estado_tramite
  LEFT JOIN dependencia          dep_p   ON t.tipo_procedencia    = 'D' AND t.id_procedencia = dep_p.id_dependencia
  LEFT JOIN dependencia_externa  dep_e_p ON t.tipo_procedencia    = 'E' AND t.id_procedencia = dep_e_p.id_dependencia_externa
  LEFT JOIN dependencia          dep_d   ON t.tipo_destinatario   = 'D' AND t.id_destino     = dep_d.id_dependencia
  LEFT JOIN dependencia_externa  dep_e_d ON t.tipo_destinatario   = 'E' AND t.id_destino     = dep_e_d.id_dependencia_externa
`;

// ── GET /tramites — Bandeja de entrada ────────────────────────
// Una fila por DOCUMENTO: el último tramite cuyo destino es el servicio del usuario.
// Así un doc que pasó por varios servicios sólo aparece una vez.
router.get('/', async (req, res, next) => {
  try {
    const user      = (req as unknown as AuthenticatedRequest).user;
    const pagina    = Math.max(1, Number(req.query.pagina ?? 1));
    const porPagina = Math.min(50, Number(req.query.porPagina ?? 20));
    const offset    = (pagina - 1) * porPagina;
    const idEstado  = req.query.idEstado ? Number(req.query.idEstado) : null;

    const pool    = await getPool();
    const request = pool.request()
      .input('offset',    sql.Int, offset)
      .input('porPagina', sql.Int, porPagina);

    // WHERE que va DENTRO del CTE para definir qué tramites se consideran "bandeja mía"
    let cteWhere = '';
    if (!hasFullAccess(user)) {
      const idDep = user.idDependencia;
      if (!idDep) {
        sendPaginated(res, [], buildPaginationMeta(0, pagina, porPagina));
        return;
      }
      request.input('idDependencia', sql.Int, idDep);
      cteWhere = canSeeExternals(user.roles)
        ? `WHERE (t.id_destino = @idDependencia AND t.tipo_destinatario = 'D') OR t.tipo_destinatario = 'E'`
        : `WHERE t.id_destino = @idDependencia AND t.tipo_destinatario = 'D'`;
    }
    // admin/todos_servicios → sin WHERE en CTE = todos los tramites

    let estadoWhere = '';
    if (idEstado) {
      request.input('idEstado', sql.Int, idEstado);
      estadoWhere = ' AND t.id_estado_tramite = @idEstado';
    }

    // CTE: por cada documento, tomar el id del último tramite que cayó en mi servicio
    const result = await request.query<{
      id_seguimiento:      number;
      id_documento:        number | null;
      materia:             string | null;
      num_interno:         string | null;
      desc_tipo_documento: string | null;
      id_estado_tramite:   number | null;
      desc_estado_tramite: string | null;
      desc_procedencia:    string | null;
      desc_destino:        string | null;
      fecha_sistema:       Date | null;
      observaciones:       string | null;
      total:               number;
    }>(`
      WITH ultimo AS (
        SELECT t.id_documento, MAX(t.id_seguimiento) AS id_seg
        FROM tramite t
        ${cteWhere}
        GROUP BY t.id_documento
      )
      SELECT ${SEL_COLS}, COUNT(*) OVER() AS total
      FROM tramite t
      JOIN ultimo u ON t.id_seguimiento = u.id_seg
      ${SEL_JOINS}
      WHERE 1=1 ${estadoWhere}
      ORDER BY t.fecha_sistema DESC
      OFFSET @offset ROWS FETCH NEXT @porPagina ROWS ONLY
    `);

    const total = result.recordset[0]?.total ?? 0;
    sendPaginated(res, result.recordset, buildPaginationMeta(total, pagina, porPagina));
  } catch (e) { next(e); }
});

// ── GET /tramites/enviados ────────────────────────────────────
// Una fila por DOCUMENTO: el último tramite del documento.
// Para usuarios sin acceso total: solo docs donde su servicio fue el origen en algún tramite.
router.get('/enviados', async (req, res, next) => {
  try {
    const user      = (req as unknown as AuthenticatedRequest).user;
    const pagina    = Math.max(1, Number(req.query.pagina ?? 1));
    const porPagina = Math.min(50, Number(req.query.porPagina ?? 20));
    const offset    = (pagina - 1) * porPagina;

    const pool    = await getPool();
    const request = pool.request()
      .input('offset',    sql.Int, offset)
      .input('porPagina', sql.Int, porPagina);

    // Para no-admin: filtrar sólo docs donde mi servicio fue procedencia en algún tramite
    let docsCte  = '';  // CTE adicional (con coma final si se usa)
    let docsJoin = '';  // JOIN al CTE en el CTE "ultimo"

    if (!hasFullAccess(user)) {
      const idDep = user.idDependencia;
      if (!idDep) {
        sendPaginated(res, [], buildPaginationMeta(0, pagina, porPagina));
        return;
      }
      request.input('idDependencia', sql.Int, idDep);
      docsCte  = `docs_env AS (
        SELECT DISTINCT id_documento FROM tramite
        WHERE id_procedencia = @idDependencia AND tipo_procedencia = 'D'
      ),`;
      docsJoin = `JOIN docs_env de ON t.id_documento = de.id_documento`;
    }
    // admin/todos_servicios → sin filtro de origen; último tramite de cada doc

    const result = await request.query<{
      id_seguimiento:      number;
      id_documento:        number | null;
      materia:             string | null;
      num_interno:         string | null;
      desc_tipo_documento: string | null;
      id_estado_tramite:   number | null;
      desc_estado_tramite: string | null;
      desc_procedencia:    string | null;
      desc_destino:        string | null;
      fecha_sistema:       Date | null;
      observaciones:       string | null;
      total:               number;
    }>(`
      WITH ${docsCte}
      ultimo AS (
        SELECT t.id_documento, MAX(t.id_seguimiento) AS id_seg
        FROM tramite t
        ${docsJoin}
        GROUP BY t.id_documento
      )
      SELECT ${SEL_COLS}, COUNT(*) OVER() AS total
      FROM tramite t
      JOIN ultimo u ON t.id_seguimiento = u.id_seg
      ${SEL_JOINS}
      ORDER BY t.fecha_sistema DESC
      OFFSET @offset ROWS FETCH NEXT @porPagina ROWS ONLY
    `);

    const total = result.recordset[0]?.total ?? 0;
    sendPaginated(res, result.recordset, buildPaginationMeta(total, pagina, porPagina));
  } catch (e) { next(e); }
});

// ── PATCH /tramites/:id/recibir ───────────────────────────────
router.patch('/:id/recibir', async (req, res, next) => {
  try {
    const user   = (req as unknown as AuthenticatedRequest).user;
    const idTram = Number(req.params.id);
    const pool   = await getPool();

    if (!hasFullAccess(user) && user.idDependencia) {
      const check = await pool.request()
        .input('id',    sql.Int, idTram)
        .input('idDep', sql.Int, user.idDependencia)
        .query(`SELECT 1 AS ok FROM tramite
                WHERE id_seguimiento = @id
                  AND id_destino = @idDep AND tipo_destinatario = 'D'`);
      if (!check.recordset[0]) {
        sendForbidden(res, 'No tienes permiso para recibir este trámite');
        return;
      }
    }

    await pool.request()
      .input('idTramite', sql.Int,      idTram)
      .input('idUsuario', sql.Int,      user.idUsuario)
      .input('fechaRec',  sql.DateTime, new Date())
      .query(`UPDATE tramite
              SET id_estado_tramite = 3,
                  fecha_recepcion   = @fechaRec,
                  usuario_recepcion = @idUsuario,
                  fecha_update      = GETDATE()
              WHERE id_seguimiento = @idTramite`);

    sendSuccess(res, null, 'Trámite recibido');
  } catch (e) { next(e); }
});

// ── PATCH /tramites/:id/cerrar ────────────────────────────────
router.patch('/:id/cerrar', async (req, res, next) => {
  try {
    const user   = (req as unknown as AuthenticatedRequest).user;
    const idTram = Number(req.params.id);
    const pool   = await getPool();

    if (!hasFullAccess(user) && user.idDependencia) {
      const check = await pool.request()
        .input('id',    sql.Int, idTram)
        .input('idDep', sql.Int, user.idDependencia)
        .query(`SELECT 1 AS ok FROM tramite
                WHERE id_seguimiento = @id
                  AND id_destino = @idDep AND tipo_destinatario = 'D'`);
      if (!check.recordset[0]) {
        sendForbidden(res, 'No tienes permiso para cerrar este trámite');
        return;
      }
    }

    await pool.request()
      .input('idTramite', sql.Int, idTram)
      .query(`UPDATE tramite SET id_estado_tramite = 5, fecha_update = GETDATE()
              WHERE id_seguimiento = @idTramite`);
    sendSuccess(res, null, 'Trámite cerrado');
  } catch (e) { next(e); }
});

export default router;
