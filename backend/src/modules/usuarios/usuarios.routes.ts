import { Router, Request, Response, NextFunction } from 'express';
import bcrypt from 'bcrypt';
import { requireAuth, requireModule } from '../../middleware/auth.middleware';
import { validate } from '../../middleware/validate.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendCreated, sendError, sendPaginated, buildPaginationMeta } from '../../shared/utils/response';
import { logAuditoria } from '../../shared/utils/auditoria';
import { crearUsuarioSchema, actualizarUsuarioSchema } from './usuarios.schema';

const router = Router();
router.use(requireAuth);
router.use(requireModule('usuarios'));

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
      id_usuario: number; usuario: string; email: string | null;
      nombres: string | null; apellidos: string | null;
      id_dependencia: number | null; desc_dependencia: string | null;
      todos_servicios: boolean; roles: string | null; total: number;
    }>(`
      SELECT u.id_usuario, u.usuario, u.email,
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
      email:           r.email ?? null,
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
        id_usuario: number; usuario: string; email: string | null;
        nombres: string | null; apellidos: string | null;
        id_dependencia: number | null; desc_dependencia: string | null;
        todos_servicios: boolean; roles: string | null;
      }>(`
        SELECT u.id_usuario, u.usuario, u.email, f.nombres, f.apellidos, f.id_dependencia,
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
      email:           row.email ?? null,
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
router.post('/', validate(crearUsuarioSchema), async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { usuario, clave, nombres, apellidos, idDependencia, todos_servicios, roles, email } = req.body as {
      usuario: string; clave: string; nombres: string; apellidos: string;
      idDependencia?: number; todos_servicios?: boolean; roles?: string[]; email?: string;
    };

    const pool = await getPool();

    // Verificar que el usuario no exista
    const exists = await pool.request().input('u', sql.VarChar(10), usuario)
      .query('SELECT 1 FROM usuario WHERE usuario = @u');
    if (exists.recordset.length > 0) {
      sendError(res, 'El nombre de usuario ya está en uso', 409); return;
    }

    // Verificar unicidad de email si se provee
    if (email) {
      const emailExists = await pool.request().input('email', sql.VarChar(100), email)
        .query('SELECT 1 FROM usuario WHERE email = @email');
      if (emailExists.recordset.length > 0) {
        sendError(res, 'El correo electrónico ya está registrado en otro usuario', 409); return;
      }
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
    const emailVal   = email ? email.substring(0, 100).toLowerCase() : null;
    const usrRes = await pool.request()
      .input('usuario',         sql.VarChar(10),  usuario.substring(0, 10))
      .input('clave',           sql.VarChar(10),  claveCorta)
      .input('idFun',           sql.Int,          idFuncionario)
      .input('todos_servicios', sql.Bit,          todosServ)
      .input('email',           sql.VarChar(100), emailVal)
      .query<{ id: number }>(`
        INSERT INTO usuario (usuario, clave, id_funcionario, tipo_alertas, todos_servicios, email)
        OUTPUT INSERTED.id_usuario AS id
        VALUES (@usuario, @clave, @idFun, 'A', @todos_servicios, @email)
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

    const actor = (req as unknown as import('../../shared/types/api.types').AuthenticatedRequest).user;
    await logAuditoria(pool, {
      idUsuario: actor.idUsuario,
      accion: 'USUARIO_CREADO',
      recurso: String(idUsuario),
      detalle: `usuario: ${usuario}`,
      ip: req.ip ?? null,
    });
    sendCreated(res, { idUsuario, usuario, nombres, apellidos, roles: rolList }, 'Usuario creado correctamente');
  } catch (e) { next(e); }
});

// ── PATCH /usuarios/:id — actualizar ───────────────────────
router.patch('/:id', validate(actualizarUsuarioSchema), async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { nombres, apellidos, clave, roles, idDependencia, todos_servicios, email } = req.body as {
      nombres?: string; apellidos?: string; clave?: string; roles?: string[];
      idDependencia?: number; todos_servicios?: boolean; email?: string | null;
    };
    const idUsuario    = Number(req.params.id);
    const currentUser  = (req as unknown as import('../../shared/types/api.types').AuthenticatedRequest).user;

    // Solo administradores pueden modificar roles
    if (roles !== undefined && !currentUser.roles.includes('admin')) {
      sendError(res, 'Solo administradores pueden modificar roles', 403); return;
    }

    // Validar formato email si se provee
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      sendError(res, 'Formato de correo electrónico inválido', 400); return;
    }

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

    // Actualizar email si se provee (null para borrarlo, string para asignarlo)
    if (email !== undefined) {
      if (email) {
        // Verificar unicidad: no debe existir en otro usuario
        const emailExists = await pool.request()
          .input('email', sql.VarChar(100), email)
          .input('idUsr', sql.Int, idUsuario)
          .query('SELECT 1 FROM usuario WHERE email = @email AND id_usuario <> @idUsr');
        if (emailExists.recordset.length > 0) {
          sendError(res, 'El correo electrónico ya está registrado en otro usuario', 409); return;
        }
      }
      await pool.request()
        .input('email', sql.VarChar(100), email ? email.substring(0, 100).toLowerCase() : null)
        .input('idUsr', sql.Int, idUsuario)
        .query('UPDATE usuario SET email = @email WHERE id_usuario = @idUsr');
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
      const claveHash  = await bcrypt.hash(clave, 12);
      await pool.request()
        .input('clave',     sql.VarChar(10),  claveCorta)
        .input('claveHash', sql.VarChar(255), claveHash)
        .input('idUsr',     sql.Int,          idUsuario)
        .query('UPDATE usuario SET clave = @clave, clave_hash = @claveHash WHERE id_usuario = @idUsr');
      // Revocar todos los refresh tokens activos para forzar re-login
      await pool.request()
        .input('idUsr', sql.Int, idUsuario)
        .query('UPDATE refresh_token SET revoked_at = GETDATE() WHERE id_usuario = @idUsr AND revoked_at IS NULL');
      await logAuditoria(pool, {
        idUsuario: currentUser.idUsuario,
        accion: 'CONTRASENA_CAMBIADA',
        recurso: String(idUsuario),
        ip: req.ip ?? null,
      });
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
    const idUsuario   = Number(req.params.id);
    const currentUser = (req as unknown as import('../../shared/types/api.types').AuthenticatedRequest).user;

    if (idUsuario === currentUser.idUsuario) {
      sendError(res, 'No puedes eliminar tu propio usuario', 400); return;
    }

    const pool = await getPool();

    // Verificar si es el último administrador activo del sistema
    const adminCheck = await pool.request()
      .input('id', sql.Int, idUsuario)
      .query<{ esAdmin: number; totalAdmins: number }>(`
        SELECT
          (SELECT COUNT(*) FROM usuario_rol ur JOIN rol r ON ur.id_rol = r.id_rol
           WHERE ur.id_usuario = @id AND r.codigo = 'admin') AS esAdmin,
          (SELECT COUNT(DISTINCT ur2.id_usuario) FROM usuario_rol ur2 JOIN rol r2 ON ur2.id_rol = r2.id_rol
           WHERE r2.codigo = 'admin') AS totalAdmins
      `);
    const { esAdmin, totalAdmins } = adminCheck.recordset[0] ?? { esAdmin: 0, totalAdmins: 0 };
    if (esAdmin > 0 && totalAdmins <= 1) {
      sendError(res, 'No se puede eliminar el único administrador del sistema', 400); return;
    }

    await pool.request().input('id', sql.Int, idUsuario)
      .query('DELETE FROM usuario_rol WHERE id_usuario = @id');
    await pool.request().input('id', sql.Int, idUsuario)
      .query('DELETE FROM usuario WHERE id_usuario = @id');
    await logAuditoria(pool, {
      idUsuario: currentUser.idUsuario,
      accion: 'USUARIO_ELIMINADO',
      recurso: String(idUsuario),
      ip: req.ip ?? null,
    });
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
