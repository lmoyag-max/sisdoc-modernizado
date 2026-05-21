import { Router, Request, Response, NextFunction } from 'express';
import { requireAuth, requireRole } from '../../middleware/auth.middleware';
import { getPool, sql } from '../../config/database';
import { sendSuccess, sendCreated, sendError } from '../../shared/utils/response';

const router = Router();
router.use(requireAuth);
router.use(requireRole('admin')); // Solo administradores

const TODOS_MODULOS = [
  'dashboard','documentos','bandeja','enviados','tramites',
  'trazabilidad','busqueda','archivos',
  'expedientes','usuarios','reportes','roles','configuracion',
];

// ── GET /roles — listar roles con sus módulos ─────────────────
router.get('/', async (_req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const roles = await pool.request().query<{ id_rol: number; codigo: string; nombre: string; activo: boolean }>(`
      SELECT id_rol, codigo, nombre, activo FROM rol ORDER BY id_rol
    `);
    const modulos = await pool.request().query<{ id_rol: number; modulo: string }>(`
      SELECT id_rol, modulo FROM rol_modulo ORDER BY id_rol, modulo
    `);

    const modulosMap: Record<number, string[]> = {};
    modulos.recordset.forEach((m) => {
      if (!modulosMap[m.id_rol]) modulosMap[m.id_rol] = [];
      modulosMap[m.id_rol].push(m.modulo);
    });

    sendSuccess(res, roles.recordset.map((r) => ({
      ...r,
      modulos: modulosMap[r.id_rol] ?? [],
      todosModulos: TODOS_MODULOS,
    })));
  } catch (e) { next(e); }
});

// ── GET /roles/:id — detalle de un rol ───────────────────────
router.get('/:id', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const pool = await getPool();
    const rolRes = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<{ id_rol: number; codigo: string; nombre: string; activo: boolean }>(`
        SELECT id_rol, codigo, nombre, activo FROM rol WHERE id_rol = @id
      `);
    if (!rolRes.recordset[0]) { sendError(res, 'Rol no encontrado', 404); return; }

    const modRes = await pool.request()
      .input('id', sql.Int, Number(req.params.id))
      .query<{ modulo: string }>('SELECT modulo FROM rol_modulo WHERE id_rol = @id ORDER BY modulo');

    sendSuccess(res, {
      ...rolRes.recordset[0],
      modulos:     modRes.recordset.map((m) => m.modulo),
      todosModulos: TODOS_MODULOS,
    });
  } catch (e) { next(e); }
});

// ── POST /roles — crear rol ───────────────────────────────────
router.post('/', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { codigo, nombre, modulos = [] } = req.body as { codigo: string; nombre: string; modulos?: string[] };
    if (!codigo || !nombre) { sendError(res, 'codigo y nombre son requeridos', 400); return; }

    const pool = await getPool();
    const exists = await pool.request()
      .input('c', sql.VarChar(50), codigo)
      .query('SELECT 1 FROM rol WHERE codigo = @c');
    if (exists.recordset.length > 0) { sendError(res, 'El código de rol ya existe', 409); return; }

    const rolRes = await pool.request()
      .input('codigo',  sql.VarChar(50),  codigo)
      .input('nombre',  sql.VarChar(100), nombre)
      .query<{ id_rol: number }>(`
        INSERT INTO rol (codigo, nombre, activo) OUTPUT INSERTED.id_rol VALUES (@codigo, @nombre, 1)
      `);
    const idRol = rolRes.recordset[0].id_rol;

    const modulosValidos = (modulos as string[]).filter((m) => TODOS_MODULOS.includes(m));
    for (const m of modulosValidos) {
      await pool.request()
        .input('id', sql.Int,        idRol)
        .input('m',  sql.VarChar(50), m)
        .query('INSERT INTO rol_modulo (id_rol, modulo) VALUES (@id, @m)');
    }

    sendCreated(res, { idRol, codigo, nombre, modulos: modulosValidos }, 'Rol creado correctamente');
  } catch (e) { next(e); }
});

// ── PATCH /roles/:id — actualizar nombre + módulos ───────────
router.patch('/:id', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const idRol = Number(req.params.id);
    const { nombre, modulos, activo } = req.body as { nombre?: string; modulos?: string[]; activo?: boolean };
    const pool = await getPool();

    if (nombre !== undefined) {
      await pool.request()
        .input('nombre', sql.VarChar(100), nombre)
        .input('id',     sql.Int,          idRol)
        .query('UPDATE rol SET nombre = @nombre WHERE id_rol = @id');
    }

    if (activo !== undefined) {
      await pool.request()
        .input('activo', sql.Bit, activo)
        .input('id',     sql.Int, idRol)
        .query('UPDATE rol SET activo = @activo WHERE id_rol = @id');
    }

    if (Array.isArray(modulos)) {
      const validos = modulos.filter((m) => TODOS_MODULOS.includes(m));
      await pool.request().input('id', sql.Int, idRol)
        .query('DELETE FROM rol_modulo WHERE id_rol = @id');
      for (const m of validos) {
        await pool.request()
          .input('id', sql.Int,        idRol)
          .input('m',  sql.VarChar(50), m)
          .query('INSERT INTO rol_modulo (id_rol, modulo) VALUES (@id, @m)');
      }
    }

    sendSuccess(res, { idRol }, 'Rol actualizado');
  } catch (e) { next(e); }
});

// ── DELETE /roles/:id — desactivar (no borrar si tiene usuarios) ──
router.delete('/:id', async (req: Request, res: Response, next: NextFunction) => {
  try {
    const idRol = Number(req.params.id);
    const pool  = await getPool();

    const enUso = await pool.request().input('id', sql.Int, idRol)
      .query('SELECT 1 FROM usuario_rol WHERE id_rol = @id');
    if (enUso.recordset.length > 0) {
      // Desactivar en lugar de borrar si tiene usuarios asignados
      await pool.request().input('id', sql.Int, idRol)
        .query('UPDATE rol SET activo = 0 WHERE id_rol = @id');
      sendSuccess(res, null, 'Rol desactivado (tiene usuarios asignados)');
      return;
    }

    await pool.request().input('id', sql.Int, idRol)
      .query('DELETE FROM rol_modulo WHERE id_rol = @id');
    await pool.request().input('id', sql.Int, idRol)
      .query('DELETE FROM rol WHERE id_rol = @id');
    sendSuccess(res, null, 'Rol eliminado');
  } catch (e) { next(e); }
});

// ── GET /roles/meta/modulos — lista completa de módulos ───────
router.get('/meta/modulos', (_req: Request, res: Response) => {
  const LABELS: Record<string, { label: string; grupo: 'operativo' | 'admin' }> = {
    dashboard:     { label: 'Dashboard',       grupo: 'operativo' },
    documentos:    { label: 'Documentos',       grupo: 'operativo' },
    bandeja:       { label: 'Bandeja Entrada',  grupo: 'operativo' },
    enviados:      { label: 'Enviados',          grupo: 'operativo' },
    tramites:      { label: 'Mis Trámites',      grupo: 'operativo' },
    trazabilidad:  { label: 'Trazabilidad',      grupo: 'operativo' },
    busqueda:      { label: 'Búsqueda',          grupo: 'operativo' },
    archivos:      { label: 'Archivos',           grupo: 'operativo' },
    expedientes:   { label: 'Expedientes',        grupo: 'admin' },
    usuarios:      { label: 'Usuarios',           grupo: 'admin' },
    reportes:      { label: 'Reportes',           grupo: 'admin' },
    roles:         { label: 'Roles',              grupo: 'admin' },
    configuracion: { label: 'Configuración',      grupo: 'admin' },
  };
  sendSuccess(res, TODOS_MODULOS.map((m) => ({ codigo: m, ...LABELS[m] })));
});

export default router;
