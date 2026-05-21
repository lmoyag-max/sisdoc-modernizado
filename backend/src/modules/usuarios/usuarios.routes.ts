import { Router, Request, Response, NextFunction } from 'express';
import bcrypt from 'bcrypt';
import { requireAuth } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendCreated, sendError, sendPaginated, buildPaginationMeta } from '../../shared/utils/response';

const router = Router();
router.use(requireAuth);

// ── GET /usuarios — listar ──────────────────────────────────
router.get('/', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pagina = Math.max(1, Number(req.query.pagina ?? 1));
    const porPagina = Math.min(100, Number(req.query.porPagina ?? 20));
    const offset = (pagina - 1) * porPagina;
    const q = String(req.query.q ?? '').trim();

    const pool = await getPool();
    const request = pool.request()
      .input('offset', sql.Int, offset)
      .input('n', sql.Int, porPagina);

    let where = '1=1';
    if (q) {
      request.input('q', sql.VarChar(100), `%${q}%`);
      where += ' AND (u.usuario LIKE @q OR f.nombres LIKE @q OR f.apellidos LIKE @q)';
    }

    const result = await request.query<{
      id_usuario: number; usuario: string;
      nombres: string | null; apellidos: string | null;
      id_dependencia: number | null; desc_dependencia: string | null;
      todos_servicios: boolean; roles: string | null; total: number;
    }>(`
      SELECT u.id_usuario, u.usuario,
             f.nombres, f.apellidos, f.id_dependencia,
             d.desc_dependencia,
             ISNULL(u.todos_servicios, 1) AS todos_servicios,
             (SELECT STRING_AGG(r.codigo, ',') FROM usuario_rol ur JOIN rol r ON ur.id_rol = r.id_rol WHERE ur.id_usuario = u.id_usuario) AS roles,
             COUNT(*) OVER() AS total
      FROM usuario u
      LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      LEFT JOIN dependencia d ON f.id_dependencia = d.id_dependencia
      WHERE ${where}
      ORDER BY u.id_usuario DESC
      OFFSET @offset ROWS FETCH NEXT @n ROWS ONLY
    `);

    const total = result.recordset[0]?.total ?? 0;
    const data = result.recordset.map((r) => ({
      idUsuario:       r.id_usuario,
      usuario:         r.usuario,
      nombres:         r.nombres,
      apellidos:       r.apellidos,
      idDependencia:   r.id_dependencia,
      descDependencia: r.desc_dependencia,
      todosServicios:  r.todos_servicios ?? true,
      roles:           r.roles ? r.roles.split(',') : ['funcionario'],
    }));

    sendPaginated(res, data, buildPaginationMeta(total, pagina, porPagina));
  } catch (e) { next(e); }
});

// ── GET /usuarios/:id — obtener uno ────────────────────────
router.get('/:id', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const result = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<{
        id_usuario: number; usuario: string;
        nombres: string | null; apellidos: string | null;
        id_dependencia: number | null; desc_dependencia: string | null;
        todos_servicios: boolean; roles: string | null;
      }>(`
        SELECT u.id_usuario, u.usuario, f.nombres, f.apellidos, f.id_dependencia,
               d.desc_dependencia,
               ISNULL(u.todos_servicios, 1) AS todos_servicios,
               (SELECT STRING_AGG(r.codigo, ',') FROM usuario_rol ur JOIN rol r ON ur.id_rol = r.id_rol WHERE ur.id_usuario = u.id_usuario) AS roles
        FROM usuario u
        LEFT JOIN funcionario f ON u.id_funcionario = f.id_funcionario
        LEFT JOIN dependencia d ON f.id_dependencia = d.id_dependencia
        WHERE u.id_usuario = @id
      `);

    const row = result.recordset[0];
    if (!row) { sendError(res, 'Usuario no encontrado', 404); return; }

    sendSuccess(res, {
      idUsuario:       row.id_usuario,
      usuario:         row.usuario,
      nombres:         row.nombres,
      apellidos:       row.apellidos,
      idDependencia:   row.id_dependencia,
      descDependencia: row.desc_dependencia,
      todosServicios:  row.todos_servicios ?? true,
      roles:           row.roles ? row.roles.split(',') : ['funcionario'],
    });
  } catch (e) { next(e); }
});

// ── POST /usuarios — crear ──────────────────────────────────
router.post('/', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { usuario, clave, nombres, apellidos, idDependencia, todos_servicios, roles } = req.body as {
      usuario: string; clave: string; nombres: string; apellidos: string;
      idDependencia?: number; todos_servicios?: boolean; roles?: string[];
    };

    if (!usuario || !clave || !nombres || !apellidos) {
      sendError(res, 'Campos requeridos: usuario, clave, nombres, apellidos', 400); return;
    }

    const pool = await getPool();

    // Verificar que el usuario no exista
    const exists = await pool.request().input('u', sql.VarChar(10), usuario)
      .query('SELECT 1 FROM usuario WHERE usuario = @u');
    if (exists.recordset.length > 0) {
      sendError(res, 'El nombre de usuario ya está en uso', 409); return;
    }

    const defaultDep = await pool.request()
      .query<{ id: number }>('SELECT TOP 1 id_dependencia AS id FROM dependencia ORDER BY id_dependencia');
    const depId = idDependencia || (defaultDep.recordset[0]?.id ?? 1);

    // Crear funcionario
    const funRes = await pool.request()
      .input('nombres', sql.VarChar(30), nombres.substring(0, 30))
      .input('apellidos', sql.VarChar(30), apellidos.substring(0, 30))
      .input('rut', sql.VarChar(8), '0')
      .input('dig', sql.VarChar(1), '0')
      .input('depId', sql.Int, depId)
      .query<{ id: number }>(`
        INSERT INTO funcionario (rut, dig, nombres, apellidos, id_dependencia, vigencia)
        OUTPUT INSERTED.id_funcionario AS id
        VALUES (@rut, @dig, @nombres, @apellidos, @depId, 'S')
      `);

    const idFuncionario = funRes.recordset[0].id;

    // Crear usuario — clave en texto plano (max 10 chars, se hashea en primer login)
    const claveCorta = clave.substring(0, 10);
    const todosServ  = todos_servicios !== false; // default true
    const usrRes = await pool.request()
      .input('usuario',         sql.VarChar(10), usuario.substring(0, 10))
      .input('clave',           sql.VarChar(10), claveCorta)
      .input('idFun',           sql.Int,         idFuncionario)
      .input('todos_servicios', sql.Bit,         todosServ)
      .query<{ id: number }>(`
        INSERT INTO usuario (usuario, clave, id_funcionario, tipo_alertas, todos_servicios)
        OUTPUT INSERTED.id_usuario AS id
        VALUES (@usuario, @clave, @idFun, 'A', @todos_servicios)
      `);

    const idUsuario = usrRes.recordset[0].id;

    // Asignar roles
    const rolList: string[] = Array.isArray(roles) && roles.length > 0 ? roles : ['funcionario'];
    for (const codigoRol of rolList) {
      await pool.request()
        .input('idUsr', sql.Int, idUsuario)
        .input('codigo', sql.VarChar(50), codigoRol)
        .query(`
          INSERT INTO usuario_rol (id_usuario, id_rol)
          SELECT @idUsr, id_rol FROM rol WHERE codigo = @codigo
          AND NOT EXISTS (SELECT 1 FROM usuario_rol WHERE id_usuario = @idUsr AND id_rol = (SELECT id_rol FROM rol WHERE codigo = @codigo))
        `);
    }

    sendCreated(res, { idUsuario, usuario, nombres, apellidos, roles: rolList }, 'Usuario creado correctamente');
  } catch (e) { next(e); }
});

// ── PATCH /usuarios/:id — actualizar ───────────────────────
router.patch('/:id', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { nombres, apellidos, clave, roles, idDependencia, todos_servicios } = req.body as {
      nombres?: string; apellidos?: string; clave?: string; roles?: string[];
      idDependencia?: number; todos_servicios?: boolean;
    };
    const idUsuario = Number(req.params.id);
    const pool = await getPool();

    // Actualizar funcionario si hay campos de nombre
    if (nombres || apellidos || idDependencia) {
      await pool.request()
        .input('nombres', sql.VarChar(30), nombres?.substring(0, 30) ?? null)
        .input('apellidos', sql.VarChar(30), apellidos?.substring(0, 30) ?? null)
        .input('depId', sql.Int, idDependencia ?? null)
        .input('idUsr', sql.Int, idUsuario)
        .query(`
          UPDATE f SET
            nombres    = ISNULL(@nombres, f.nombres),
            apellidos  = ISNULL(@apellidos, f.apellidos),
            id_dependencia = ISNULL(@depId, f.id_dependencia)
          FROM funcionario f
          JOIN usuario u ON u.id_funcionario = f.id_funcionario
          WHERE u.id_usuario = @idUsr
        `);
    }

    // Actualizar todos_servicios si se provee
    if (todos_servicios !== undefined) {
      await pool.request()
        .input('ts',     sql.Bit, todos_servicios)
        .input('idUsr',  sql.Int, idUsuario)
        .query('UPDATE usuario SET todos_servicios = @ts WHERE id_usuario = @idUsr');
    }

    // Actualizar clave si se provee
    if (clave) {
      const claveCorta = clave.substring(0, 10);
      await pool.request()
        .input('clave', sql.VarChar(10), claveCorta)
        .input('idUsr', sql.Int, idUsuario)
        .query('UPDATE usuario SET clave = @clave WHERE id_usuario = @idUsr');
    }

    // Actualizar roles si se provee
    if (Array.isArray(roles)) {
      await pool.request().input('idUsr', sql.Int, idUsuario)
        .query('DELETE FROM usuario_rol WHERE id_usuario = @idUsr');
      for (const codigoRol of roles) {
        await pool.request()
          .input('idUsr', sql.Int, idUsuario)
          .input('codigo', sql.VarChar(50), codigoRol)
          .query(`
            INSERT INTO usuario_rol (id_usuario, id_rol)
            SELECT @idUsr, id_rol FROM rol WHERE codigo = @codigo
          `);
      }
    }

    sendSuccess(res, { idUsuario }, 'Usuario actualizado');
  } catch (e) { next(e); }
});

// ── DELETE /usuarios/:id — eliminar ────────────────────────
router.delete('/:id', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const idUsuario = Number(req.params.id);
    const pool = await getPool();
    await pool.request().input('id', sql.Int, idUsuario)
      .query('DELETE FROM usuario_rol WHERE id_usuario = @id');
    await pool.request().input('id', sql.Int, idUsuario)
      .query('DELETE FROM usuario WHERE id_usuario = @id');
    sendSuccess(res, null, 'Usuario eliminado');
  } catch (e) { next(e); }
});

// ── GET /usuarios/meta/roles — lista de roles disponibles ──
router.get('/meta/roles', async (_req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const result = await pool.request()
      .query<{ id_rol: number; codigo: string; nombre: string }>(`
        SELECT id_rol, codigo, nombre FROM rol WHERE activo = 1 ORDER BY id_rol
      `);
    sendSuccess(res, result.recordset);
  } catch (e) { next(e); }
});

export default router;
