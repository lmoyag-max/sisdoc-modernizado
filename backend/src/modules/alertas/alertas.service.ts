import { getPool, sql } from '../../config/database';
import { env } from '../../config/env';
import { logger } from '../../shared/utils/logger';
import { sendMail } from '../../shared/services/email.service';

// ── Types ────────────────────────────────────────────────────

export interface AlertaConfig {
  id:        number;
  activo:    boolean;
  horarios:  string[];
  updatedAt: Date;
}

export interface DocumentoPendiente {
  idDocumentoDestino: number | null;
  idDestino:          number;
  nombreServicio:     string;
  idDocumento:        number;
  numInterno:         number | null;
  materia:            string | null;
  tipoDocumento:      string | null;
  estadoDocumento:    string | null;
  idTipoCompromiso:   number;
  compromiso:         string;
  diasCompromiso:     number;
  diasTranscurridos:  number;
  fechaCreacion:      Date | null;
  origenNombre:       string | null;
}

export interface DestinatarioServicio {
  idUsuario:      number;
  usuario:        string;
  email:          string;
  nombreCompleto: string;
  idDependencia:  number;
  nombreServicio: string;
}

export interface ResumenDestinatarios {
  idDependencia:  number;
  nombreServicio: string;
  totalUsuarios:  number;
  usuarios:       { idUsuario: number; nombreCompleto: string; email: string }[];
}

export interface AlertaLog {
  id:                  number;
  idDependencia:       number;
  nombreServicio:      string;
  tipoEnvio:           string;
  idUsuarioDisparo:    number | null;
  fechaEnvio:          Date;
  destinatarios:       string[] | null;
  documentosIncluidos: number;
  urgentesIncluidos:   number;
  estado:              string;
  mensajeError:        string | null;
}

export interface ResultadoEnvio {
  enviados:  number;
  errores:   number;
  sinDocs:   number;
  sinEmail:  number;
  detalles:  { servicio: string; estado: string; mensaje?: string }[];
}

export interface ResultadoServicio {
  estado:              string;
  mensaje:             string;
  documentosIncluidos: number;
  usuariosEncontrados: number;
}

// ── Validación de email ───────────────────────────────────────
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function esEmailValido(email: string): boolean {
  return EMAIL_RE.test(email.trim());
}

// ── Configuración del scheduler ──────────────────────────────

export async function getConfiguracion(): Promise<AlertaConfig> {
  const pool = await getPool();
  const r = await pool.request().query<{ id: number; activo: boolean; horarios: string; updated_at: Date }>(`
    SELECT id, activo, horarios, updated_at FROM alerta_config WHERE id = 1
  `);
  const row = r.recordset[0];
  if (!row) throw { statusCode: 500, message: 'Configuración de alertas no encontrada' };
  return {
    id:        row.id,
    activo:    row.activo,
    horarios:  row.horarios.split(',').map((h) => h.trim()).filter(Boolean),
    updatedAt: row.updated_at,
  };
}

export async function updateConfiguracion(activo: boolean, horarios: string[]): Promise<AlertaConfig> {
  const horariosStr = horarios.filter((h) => /^\d{2}:\d{2}$/.test(h)).join(',') || '08:00';
  const pool = await getPool();
  await pool.request()
    .input('activo',   sql.Bit,         activo)
    .input('horarios', sql.VarChar(200), horariosStr)
    .query('UPDATE alerta_config SET activo = @activo, horarios = @horarios, updated_at = GETDATE() WHERE id = 1');
  return getConfiguracion();
}

// ── Documentos pendientes ────────────────────────────────────
// Usa la tabla tramite como fuente de verdad del destino actual.
// El último tramite de tipo despacho (estado 1/2/4) por documento indica
// el servicio donde el documento está realmente pendiente, incluso después
// de redespaches o derivaciones sucesivas.

export async function getDocumentosPendientes(idDependencia?: number): Promise<DocumentoPendiente[]> {
  const pool = await getPool();
  const req  = pool.request();
  let filterClause = '';
  if (idDependencia) {
    req.input('idDep', sql.Int, idDependencia);
    filterClause = 'AND t.id_destino = @idDep';
  }

  const result = await req.query<{
    id: number; id_destino: number; nombre_servicio: string;
    id_documento: number; num_interno: number | null; materia: string | null;
    tipo_documento: string | null; estado_documento: string | null;
    id_tipo_compromiso: number; compromiso: string;
    dias_compromiso: number; dias_transcurridos: number;
    fecha_creacion: Date | null; origen_nombre: string | null;
  }>(`
    WITH ultimo_despacho AS (
      SELECT t.id_documento, MAX(t.id_seguimiento) AS id_seg
      FROM tramite t
      WHERE t.id_estado_tramite IN (1, 2, 4)
      GROUP BY t.id_documento
    )
    SELECT
      t.id_seguimiento                          AS id,
      t.id_destino,
      LTRIM(RTRIM(dep.desc_dependencia))        AS nombre_servicio,
      doc.id_documento,
      doc.num_interno,
      doc.materia,
      td.desc_tipo_documento                    AS tipo_documento,
      ed.desc_estado_documento                  AS estado_documento,
      t.id_tipo_compromiso,
      tc.desc_tipo_compromiso                   AS compromiso,
      t.dias_compromiso,
      DATEDIFF(DAY, t.fecha_sistema, GETDATE()) AS dias_transcurridos,
      t.fecha_sistema                           AS fecha_creacion,
      LTRIM(RTRIM(dep_orig.desc_dependencia))   AS origen_nombre
    FROM ultimo_despacho ud
    JOIN tramite t          ON  t.id_seguimiento         = ud.id_seg
    JOIN documento doc      ON  t.id_documento            = doc.id_documento
    JOIN dependencia dep    ON  t.id_destino              = dep.id_dependencia
    JOIN tipo_documento td  ON  doc.id_tipo_documento     = td.id_tipo_documento
    JOIN estado_documento ed ON doc.id_estado_documento   = ed.id_estado_documento
    JOIN tipo_compromiso tc ON  t.id_tipo_compromiso      = tc.id_tipo_compromiso
    LEFT JOIN dependencia dep_orig ON dep_orig.id_dependencia = t.id_procedencia
    WHERE t.tipo_destinatario = 'D'
      AND doc.id_estado_documento NOT IN (4)
      ${filterClause}
    ORDER BY t.id_destino, t.id_tipo_compromiso DESC, doc.fecha_sistema
  `);

  return result.recordset.map((r) => ({
    idDocumentoDestino: r.id,
    idDestino:          r.id_destino,
    nombreServicio:     r.nombre_servicio,
    idDocumento:        r.id_documento,
    numInterno:         r.num_interno,
    materia:            r.materia,
    tipoDocumento:      r.tipo_documento,
    estadoDocumento:    r.estado_documento,
    idTipoCompromiso:   r.id_tipo_compromiso,
    compromiso:         r.compromiso,
    diasCompromiso:     r.dias_compromiso,
    diasTranscurridos:  r.dias_transcurridos,
    fechaCreacion:      r.fecha_creacion,
    origenNombre:       r.origen_nombre,
  }));
}

// ── Destinatarios dinámicos desde usuarios del sistema ────────
// Fuente oficial: usuario.email + funcionario.id_dependencia
// No depende de tablas manuales de correos.

export async function getDestinatariosServicio(idDependencia: number): Promise<DestinatarioServicio[]> {
  const pool = await getPool();
  const r = await pool.request()
    .input('idDep', sql.Int, idDependencia)
    .query<{
      id_usuario: number; usuario: string; email: string;
      nombres: string | null; apellidos: string | null;
      id_dependencia: number; nombre_servicio: string;
    }>(`
      SELECT
        u.id_usuario,
        u.usuario,
        LTRIM(RTRIM(u.email)) AS email,
        LTRIM(RTRIM(f.nombres))   AS nombres,
        LTRIM(RTRIM(f.apellidos)) AS apellidos,
        f.id_dependencia,
        LTRIM(RTRIM(d.desc_dependencia)) AS nombre_servicio
      FROM usuario u
      JOIN funcionario f ON u.id_funcionario = f.id_funcionario
      JOIN dependencia  d ON f.id_dependencia = d.id_dependencia
      WHERE f.id_dependencia = @idDep
        AND u.email IS NOT NULL
        AND LEN(LTRIM(RTRIM(u.email))) > 5
        AND u.email LIKE '%@%'
      ORDER BY f.apellidos, f.nombres
    `);

  return r.recordset
    .filter((row) => esEmailValido(row.email))
    .map((row) => ({
      idUsuario:      row.id_usuario,
      usuario:        row.usuario,
      email:          row.email.toLowerCase().trim(),
      nombreCompleto: [row.nombres, row.apellidos].filter(Boolean).join(' '),
      idDependencia:  row.id_dependencia,
      nombreServicio: row.nombre_servicio,
    }));
}

export async function getResumenDestinatarios(idDependencia?: number): Promise<ResumenDestinatarios[]> {
  const pool = await getPool();
  const req  = pool.request();
  let where  = `u.email IS NOT NULL AND LEN(LTRIM(RTRIM(u.email))) > 5 AND u.email LIKE '%@%'`;
  if (idDependencia) {
    req.input('idDep', sql.Int, idDependencia);
    where += ' AND f.id_dependencia = @idDep';
  }

  const r = await req.query<{
    id_usuario: number; usuario: string; email: string;
    nombres: string | null; apellidos: string | null;
    id_dependencia: number; nombre_servicio: string;
  }>(`
    SELECT
      u.id_usuario,
      u.usuario,
      LTRIM(RTRIM(u.email)) AS email,
      LTRIM(RTRIM(f.nombres))   AS nombres,
      LTRIM(RTRIM(f.apellidos)) AS apellidos,
      f.id_dependencia,
      LTRIM(RTRIM(d.desc_dependencia)) AS nombre_servicio
    FROM usuario u
    JOIN funcionario f ON u.id_funcionario = f.id_funcionario
    JOIN dependencia  d ON f.id_dependencia = d.id_dependencia
    WHERE ${where}
    ORDER BY d.desc_dependencia, f.apellidos, f.nombres
  `);

  // Agrupa por servicio en JavaScript — evita STRING_AGG y es más flexible
  const map = new Map<number, ResumenDestinatarios>();
  for (const row of r.recordset) {
    if (!esEmailValido(row.email)) continue;
    const email  = row.email.toLowerCase().trim();
    const nombre = [row.nombres, row.apellidos].filter(Boolean).join(' ');
    if (!map.has(row.id_dependencia)) {
      map.set(row.id_dependencia, {
        idDependencia:  row.id_dependencia,
        nombreServicio: row.nombre_servicio,
        totalUsuarios:  0,
        usuarios:       [],
      });
    }
    const srv = map.get(row.id_dependencia)!;
    // Deduplicar por email (unicidad garantizada por BD, pero defensive)
    if (!srv.usuarios.some((u) => u.email === email)) {
      srv.usuarios.push({ idUsuario: row.id_usuario, nombreCompleto: nombre, email });
      srv.totalUsuarios++;
    }
  }

  return Array.from(map.values());
}

// ── Logs ─────────────────────────────────────────────────────

interface AlertaLogRow {
  id: number; id_dependencia: number; nombre_servicio: string;
  tipo_envio: string; id_usuario_disparo: number | null;
  fecha_envio: Date; destinatarios: string | null;
  documentos_incluidos: number; urgentes_incluidos: number;
  estado: string; mensaje_error: string | null; total: number;
}

export async function getLogs(pagina = 1, porPagina = 30): Promise<{ data: AlertaLog[]; total: number }> {
  const pool   = await getPool();
  const offset = (pagina - 1) * porPagina;
  const r = await pool.request()
    .input('offset',    sql.Int, offset)
    .input('porPagina', sql.Int, porPagina)
    .query<AlertaLogRow>(`
      SELECT
        al.id, al.id_dependencia,
        LTRIM(RTRIM(dep.desc_dependencia)) AS nombre_servicio,
        al.tipo_envio, al.id_usuario_disparo, al.fecha_envio,
        al.destinatarios, al.documentos_incluidos, al.urgentes_incluidos,
        al.estado, al.mensaje_error,
        COUNT(*) OVER() AS total
      FROM alerta_log al
      JOIN dependencia dep ON al.id_dependencia = dep.id_dependencia
      ORDER BY al.fecha_envio DESC
      OFFSET @offset ROWS FETCH NEXT @porPagina ROWS ONLY
    `);

  return {
    total: r.recordset[0]?.total ?? 0,
    data:  r.recordset.map((row) => ({
      id:                  row.id,
      idDependencia:       row.id_dependencia,
      nombreServicio:      row.nombre_servicio,
      tipoEnvio:           row.tipo_envio,
      idUsuarioDisparo:    row.id_usuario_disparo,
      fechaEnvio:          row.fecha_envio,
      destinatarios:       row.destinatarios ? JSON.parse(row.destinatarios) as string[] : null,
      documentosIncluidos: row.documentos_incluidos,
      urgentesIncluidos:   row.urgentes_incluidos,
      estado:              row.estado,
      mensajeError:        row.mensaje_error,
    })),
  };
}

// ── Core: enviar alerta a un servicio (usa usuarios del sistema) ─

export async function enviarAlertaServicio(
  idDependencia:    number,
  tipoEnvio:        'auto' | 'manual',
  idUsuarioDisparo: number | null = null,
): Promise<ResultadoServicio> {
  const pool = await getPool();

  // Verificar que la dependencia existe
  const depRes = await pool.request().input('id', sql.Int, idDependencia)
    .query<{ desc_dependencia: string }>(
      'SELECT LTRIM(RTRIM(desc_dependencia)) AS desc_dependencia FROM dependencia WHERE id_dependencia = @id'
    );
  const dep = depRes.recordset[0];
  if (!dep) {
    await registrarLog(pool, idDependencia, tipoEnvio, idUsuarioDisparo, [], 0, 0, 'error', 'Dependencia no encontrada');
    return { estado: 'error', mensaje: 'Dependencia no encontrada', documentosIncluidos: 0, usuariosEncontrados: 0 };
  }

  // Documentos pendientes para ese servicio
  const docs = await getDocumentosPendientes(idDependencia);
  if (docs.length === 0) {
    await registrarLog(pool, idDependencia, tipoEnvio, idUsuarioDisparo, [], 0, 0, 'sin_docs', null);
    return { estado: 'sin_docs', mensaje: 'No hay documentos pendientes para este servicio', documentosIncluidos: 0, usuariosEncontrados: 0 };
  }

  // Destinatarios dinámicos: usuarios del servicio con email válido
  const destinatarios = await getDestinatariosServicio(idDependencia);
  if (destinatarios.length === 0) {
    await registrarLog(pool, idDependencia, tipoEnvio, idUsuarioDisparo, [], docs.length, 0, 'sin_correo', null);
    return {
      estado:              'sin_correo',
      mensaje:             `El servicio "${dep.desc_dependencia}" no tiene usuarios con correo configurado`,
      documentosIncluidos: docs.length,
      usuariosEncontrados: 0,
    };
  }

  // Deduplicar y validar correos (la BD garantiza unicidad, pero defensive)
  const correos = [...new Set(destinatarios.map((d) => d.email))].filter(esEmailValido);
  if (correos.length === 0) {
    await registrarLog(pool, idDependencia, tipoEnvio, idUsuarioDisparo, [], docs.length, 0, 'sin_correo', 'Correos inválidos tras filtrado');
    return {
      estado:              'sin_correo',
      mensaje:             'Los correos encontrados no tienen formato válido',
      documentosIncluidos: docs.length,
      usuariosEncontrados: destinatarios.length,
    };
  }

  const urgentes       = docs.filter((d) => d.idTipoCompromiso === 3).length;
  const nombreServicio = dep.desc_dependencia;
  const { html, text } = buildAlertaEmail(nombreServicio, docs);

  try {
    await sendMail({
      to:      correos.join(', '),
      subject: `[DOC360] Documentos pendientes — ${nombreServicio}`,
      html,
      text,
    });

    await registrarLog(pool, idDependencia, tipoEnvio, idUsuarioDisparo, correos, docs.length, urgentes, 'ok', null);
    logger.info(`Alerta enviada a ${nombreServicio} (${correos.length} usuario(s), ${docs.length} docs, ${urgentes} urgentes)`);
    return {
      estado:              'ok',
      mensaje:             `Alerta enviada a ${correos.join(', ')}`,
      documentosIncluidos: docs.length,
      usuariosEncontrados: destinatarios.length,
    };
  } catch (err) {
    const msg = String(err);
    await registrarLog(pool, idDependencia, tipoEnvio, idUsuarioDisparo, correos, docs.length, urgentes, 'error', msg);
    logger.error(`Error enviando alerta a ${nombreServicio}: ${msg}`);
    return {
      estado:              'error',
      mensaje:             `Error SMTP: ${msg}`,
      documentosIncluidos: docs.length,
      usuariosEncontrados: destinatarios.length,
    };
  }
}

// ── Core: enviar a todos los servicios con pendientes ─────────

export async function enviarTodasLasAlertas(tipoEnvio: 'auto' | 'manual'): Promise<ResultadoEnvio> {
  const docs              = await getDocumentosPendientes();
  const serviciosConDocs  = [...new Set(docs.map((d) => d.idDestino))];
  const resultado: ResultadoEnvio = { enviados: 0, errores: 0, sinDocs: 0, sinEmail: 0, detalles: [] };

  for (const idDep of serviciosConDocs) {
    const r      = await enviarAlertaServicio(idDep, tipoEnvio, null);
    const nombre = docs.find((d) => d.idDestino === idDep)?.nombreServicio ?? String(idDep);
    resultado.detalles.push({ servicio: nombre, estado: r.estado, mensaje: r.mensaje });
    if (r.estado === 'ok')           resultado.enviados++;
    else if (r.estado === 'error')   resultado.errores++;
    else if (r.estado === 'sin_docs')   resultado.sinDocs++;
    else if (r.estado === 'sin_correo') resultado.sinEmail++;
  }

  return resultado;
}

// ── Helper: registrar en alerta_log ──────────────────────────

async function registrarLog(
  pool: Awaited<ReturnType<typeof getPool>>,
  idDependencia:    number,
  tipoEnvio:        string,
  idUsuarioDisparo: number | null,
  destinatarios:    string[],
  docsIncluidos:    number,
  urgentes:         number,
  estado:           string,
  mensajeError:     string | null,
): Promise<void> {
  try {
    await pool.request()
      .input('idDep',  sql.Int,           idDependencia)
      .input('tipo',   sql.VarChar(10),   tipoEnvio)
      .input('idUsr',  sql.Int,           idUsuarioDisparo)
      .input('dest',   sql.NVarChar(4000), destinatarios.length ? JSON.stringify(destinatarios) : null)
      .input('docs',   sql.Int,           docsIncluidos)
      .input('urg',    sql.Int,           urgentes)
      .input('estado', sql.VarChar(10),   estado)
      .input('err',    sql.NVarChar(4000), mensajeError)
      .query(`
        INSERT INTO alerta_log
          (id_dependencia, tipo_envio, id_usuario_disparo, destinatarios,
           documentos_incluidos, urgentes_incluidos, estado, mensaje_error)
        VALUES (@idDep, @tipo, @idUsr, @dest, @docs, @urg, @estado, @err)
      `);
  } catch (e) {
    logger.warn('No se pudo insertar en alerta_log: ' + String(e));
  }
}

// ── Template HTML de alerta ───────────────────────────────────

export function buildAlertaEmail(nombreServicio: string, docs: DocumentoPendiente[]): { html: string; text: string } {
  const urgentes = docs.filter((d) => d.idTipoCompromiso === 3);
  const normales  = docs.filter((d) => d.idTipoCompromiso === 2);
  const sinComp   = docs.filter((d) => d.idTipoCompromiso === 1);
  const fecha     = new Date().toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' });

  const rowsHtml = docs.map((d, i) => {
    const esUrgente = d.idTipoCompromiso === 3;
    const rowBg     = esUrgente ? '#fff8f8' : (i % 2 === 0 ? '#f8fafc' : '#ffffff');
    const badge     = esUrgente
      ? '<span style="background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;">URGENTE</span>'
      : d.idTipoCompromiso === 2
        ? '<span style="background:#fef9c3;color:#854d0e;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;">Normal</span>'
        : '<span style="background:#f1f5f9;color:#64748b;padding:2px 8px;border-radius:12px;font-size:11px;">Sin compromiso</span>';

    const link = `${env.FRONTEND_URL}/documentos/${d.idDocumento}`;

    return `
      <tr style="background:${rowBg};border-bottom:1px solid #e2e8f0;">
        <td style="padding:10px 14px;font-size:12px;color:#1e293b;font-weight:600;">${d.numInterno ?? d.idDocumento}</td>
        <td style="padding:10px 14px;font-size:12px;color:#374151;max-width:220px;">${d.materia ?? '—'}</td>
        <td style="padding:10px 14px;font-size:12px;color:#64748b;">${d.tipoDocumento ?? '—'}</td>
        <td style="padding:10px 14px;font-size:12px;color:#64748b;">${d.origenNombre ?? '—'}</td>
        <td style="padding:10px 14px;font-size:12px;color:#64748b;">${d.estadoDocumento ?? '—'}</td>
        <td style="padding:10px 14px;text-align:center;">${badge}</td>
        <td style="padding:10px 14px;font-size:12px;color:#64748b;text-align:center;">${d.diasTranscurridos} día(s)</td>
        <td style="padding:10px 14px;text-align:center;">
          <a href="${link}" style="color:#4f46e5;font-size:11px;font-weight:600;text-decoration:none;background:#ede9fe;padding:4px 10px;border-radius:6px;">Ver</a>
        </td>
      </tr>`;
  }).join('');

  const html = `<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 0;">
  <tr><td align="center">
    <table width="680" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
      <tr>
        <td style="background:linear-gradient(135deg,#1e293b 0%,#312e81 100%);padding:24px 32px;">
          <table cellpadding="0" cellspacing="0">
            <tr>
              <td style="background:#4f46e5;border-radius:10px;width:36px;height:36px;text-align:center;vertical-align:middle;">
                <span style="color:#fff;font-weight:bold;font-size:13px;">SD</span>
              </td>
              <td style="padding-left:12px;">
                <p style="margin:0;color:#ffffff;font-weight:700;font-size:15px;">DOC360</p>
                <p style="margin:0;color:#94a3b8;font-size:11px;">Sistema de Gestión Documental · HUAP</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="padding:28px 32px 8px;">
          <h1 style="margin:0 0 4px;color:#1e293b;font-size:19px;font-weight:700;">Documentos pendientes de gestión</h1>
          <p style="margin:0 0 20px;color:#64748b;font-size:13px;">Servicio: <strong>${nombreServicio}</strong> · ${fecha}</p>
          <table cellpadding="0" cellspacing="0" style="width:100%;margin-bottom:24px;">
            <tr>
              <td style="background:#fef2f2;border-radius:8px;padding:12px 16px;text-align:center;width:25%;">
                <p style="margin:0;color:#b91c1c;font-size:22px;font-weight:700;">${docs.length}</p>
                <p style="margin:0;color:#64748b;font-size:11px;">Total pendientes</p>
              </td>
              <td style="width:4%;"></td>
              <td style="background:#fef9c3;border-radius:8px;padding:12px 16px;text-align:center;width:25%;">
                <p style="margin:0;color:#b45309;font-size:22px;font-weight:700;">${urgentes.length}</p>
                <p style="margin:0;color:#64748b;font-size:11px;">Urgentes</p>
              </td>
              <td style="width:4%;"></td>
              <td style="background:#fafafa;border-radius:8px;padding:12px 16px;text-align:center;width:25%;">
                <p style="margin:0;color:#374151;font-size:22px;font-weight:700;">${normales.length}</p>
                <p style="margin:0;color:#64748b;font-size:11px;">Normales</p>
              </td>
              <td style="width:4%;"></td>
              <td style="background:#fafafa;border-radius:8px;padding:12px 16px;text-align:center;width:25%;">
                <p style="margin:0;color:#6b7280;font-size:22px;font-weight:700;">${sinComp.length}</p>
                <p style="margin:0;color:#64748b;font-size:11px;">Sin compromiso</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="padding:0 32px 28px;">
          <div style="overflow-x:auto;">
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
            <tr style="background:#f8fafc;">
              <th style="padding:10px 14px;font-size:11px;color:#64748b;text-align:left;font-weight:600;border-bottom:1px solid #e2e8f0;">N°</th>
              <th style="padding:10px 14px;font-size:11px;color:#64748b;text-align:left;font-weight:600;border-bottom:1px solid #e2e8f0;">Materia</th>
              <th style="padding:10px 14px;font-size:11px;color:#64748b;text-align:left;font-weight:600;border-bottom:1px solid #e2e8f0;">Tipo</th>
              <th style="padding:10px 14px;font-size:11px;color:#64748b;text-align:left;font-weight:600;border-bottom:1px solid #e2e8f0;">Origen</th>
              <th style="padding:10px 14px;font-size:11px;color:#64748b;text-align:left;font-weight:600;border-bottom:1px solid #e2e8f0;">Estado</th>
              <th style="padding:10px 14px;font-size:11px;color:#64748b;text-align:center;font-weight:600;border-bottom:1px solid #e2e8f0;">Compromiso</th>
              <th style="padding:10px 14px;font-size:11px;color:#64748b;text-align:center;font-weight:600;border-bottom:1px solid #e2e8f0;">Días</th>
              <th style="padding:10px 14px;font-size:11px;color:#64748b;text-align:center;font-weight:600;border-bottom:1px solid #e2e8f0;">Enlace</th>
            </tr>
            ${rowsHtml}
          </table>
          </div>
        </td>
      </tr>
      <tr>
        <td style="border-top:1px solid #e2e8f0;padding:16px 32px;background:#f8fafc;">
          <p style="margin:0;color:#94a3b8;font-size:11px;text-align:center;">
            © 2026 DOC360 — Hospital Universitario Asociado de Puebla<br>
            Este es un correo automático generado por el sistema. Por favor no respondas a este mensaje.
          </p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>`;

  const textRows = docs.map((d, i) =>
    `${i + 1}. Doc N°${d.numInterno ?? d.idDocumento} — ${d.materia ?? '—'} | ${d.compromiso} | ${d.diasTranscurridos} día(s)\n   ${env.FRONTEND_URL}/documentos/${d.idDocumento}`
  ).join('\n');

  const text = `
DOC360 — Documentos pendientes: ${nombreServicio}
${'='.repeat(50)}

Fecha: ${fecha}
Total pendientes: ${docs.length}  |  Urgentes: ${urgentes.length}  |  Normales: ${normales.length}

Detalle:
${textRows}

${'—'.repeat(50)}
DOC360 — HUAP
`.trim();

  return { html, text };
}
