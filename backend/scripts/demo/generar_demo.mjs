// ============================================================================
// DOC360 — Generador de datos demostrativos (ETAPA 7)
// ============================================================================
// Cliente HTTP contra la API real del backend (localhost:3001) — NO inserta
// directo en tablas de negocio. Respeta correlativos transaccionales,
// trazabilidad y reglas de visibilidad tal como las ejecuta la aplicación.
//
// Excepción deliberada y documentada: el motor de documento.repository.ts
// estampa fecha_sistema/fecha_despacho/fecha_recepcion siempre con GETDATE()
// (no es parametrizable vía API) — así que TODO se crea con timestamp real
// "ahora" durante la ejecución. Para simular 4-6 meses de actividad histórica
// (requisito de la Fase 4), este script ejecuta al final un pase de SQL
// ACOTADO EXCLUSIVAMENTE A COLUMNAS DE FECHA (fecha_sistema, fecha_update,
// fecha_despacho, fecha_recepcion, fecha_documento, fecha_creacion,
// fecha_firmado) sobre las filas que él mismo creó, identificadas por PK.
// Ningún otro campo, ninguna regla de negocio, ningún correlativo se toca
// en ese pase — ver PLAN_LIMPIEZA_Y_CARGA_DEMO_DOC360.md sección 11.
//
// Marca de origen: todo `observaciones` generado lleva el sufijo DEMO_TAG.
// ============================================================================

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import sql from 'mssql';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const BACKEND_DIR = path.resolve(__dirname, '..', '..');

// ── Config ──────────────────────────────────────────────────────────────
const BASE_URL = 'http://localhost:3001/api/v1';
const DEMO_TAG = '[DEMO_DOC360_2026]';
const PASSWORD_DEMO = 'Demo2026';
const HOY = new Date();
const DIAS_VENTANA = 150; // ~5 meses de historia simulada

function parseEnv(filePath) {
  const txt = fs.readFileSync(filePath, 'utf8');
  const out = {};
  for (const line of txt.split('\n')) {
    const m = line.match(/^([A-Z0-9_]+)=(.*)$/);
    if (m) out[m[1]] = m[2].trim();
  }
  return out;
}
const ENV = parseEnv(path.join(BACKEND_DIR, '.env'));

// ── Resumen / manifest ─────────────────────────────────────────────────
const resumen = {
  usuariosCreados: [],
  documentosCreados: [], // { idDocumento, numInterno, descripcion }
  memorandumsCreados: [], // { idDocumento, correlativo, firmado }
  procesosCompletos: [],
  reservados: [],
  errores: [],
};
function registrarResumen(idDocumento, numInterno, descripcion) {
  resumen.documentosCreados.push({ idDocumento, numInterno, descripcion });
}

// ── HTTP helpers ────────────────────────────────────────────────────────
const tokenCache = new Map();

async function login(usuario, clave) {
  const res = await fetch(`${BASE_URL}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ usuario, clave }),
  });
  const json = await res.json().catch(() => null);
  if (!res.ok || !json?.ok) throw new Error(`Login falló para ${usuario}: ${res.status} ${JSON.stringify(json)}`);
  tokenCache.set(usuario, json.data.accessToken);
  return json.data.accessToken;
}
async function getToken(usuario, clave) {
  if (tokenCache.has(usuario)) return tokenCache.get(usuario);
  return login(usuario, clave);
}

async function api(method, urlPath, { usuario, clave, body } = {}) {
  const token = usuario ? await getToken(usuario, clave) : null;
  const headers = { 'Content-Type': 'application/json' };
  if (token) headers.Authorization = `Bearer ${token}`;
  const res = await fetch(`${BASE_URL}${urlPath}`, {
    method, headers, body: body !== undefined ? JSON.stringify(body) : undefined,
  });
  const json = await res.json().catch(() => null);
  if (!res.ok || json?.ok === false) {
    throw new Error(`${method} ${urlPath} -> ${res.status}: ${JSON.stringify(json)}`);
  }
  return json?.data;
}

async function uploadFile(method, urlPath, { usuario, clave, fields = {}, fileFieldName, fileBuffer, fileName, mime }) {
  const token = await getToken(usuario, clave);
  const form = new FormData();
  for (const [k, v] of Object.entries(fields)) form.append(k, String(v));
  form.append(fileFieldName, new Blob([fileBuffer], { type: mime || 'application/octet-stream' }), fileName);
  const res = await fetch(`${BASE_URL}${urlPath}`, {
    method, headers: { Authorization: `Bearer ${token}` }, body: form,
  });
  const json = await res.json().catch(() => null);
  if (!res.ok || json?.ok === false) {
    throw new Error(`${method} ${urlPath} (upload) -> ${res.status}: ${JSON.stringify(json)}`);
  }
  return json?.data;
}

// ── Archivos ficticios ──────────────────────────────────────────────────
function fakePdfBuffer(lines) {
  const safe = lines.map((l) => String(l).replace(/[()\\]/g, ''));
  const body = safe.map((l, i) => `1 0 0 1 50 ${740 - i * 16} Tm (${l}) Tj`).join('\n');
  const content = `BT /F1 11 Tf\n${body}\nET`;
  const pdf = `%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Resources<</Font<</F1 4 0 R>>>>/Contents 5 0 R>>endobj\n4 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n5 0 obj<</Length ${content.length}>>\nstream\n${content}\nendstream\nendobj\ntrailer<</Root 1 0 R/Size 6>>\n%%EOF`;
  return Buffer.from(pdf, 'latin1');
}
// PNG 1x1 válido — placeholder de firma/timbre DEMO (no es una firma real).
const DEMO_PNG = Buffer.from(
  'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
  'base64',
);

// ── Fechas realistas ────────────────────────────────────────────────────
function ajustarHorario(d) {
  const r = new Date(d);
  if (r.getDay() === 0) r.setDate(r.getDate() + 1);
  if (r.getDay() === 6) r.setDate(r.getDate() + 2);
  const h = r.getHours();
  if (h < 8) r.setHours(8 + Math.floor(Math.random() * 2), Math.floor(Math.random() * 60), Math.floor(Math.random()*60));
  else if (h > 17 || (h === 17 && r.getMinutes() > 30)) r.setHours(15 + Math.floor(Math.random() * 2), Math.floor(Math.random() * 60), Math.floor(Math.random()*60));
  return r;
}
function randomFechaEnVentana() {
  const diasAtras = Math.floor(Math.pow(Math.random(), 1.3) * DIAS_VENTANA) + 1;
  let d = new Date(HOY.getTime() - diasAtras * 86400000);
  const dow = d.getDay();
  if ((dow === 0 || dow === 6) && Math.random() < 0.85) {
    d = new Date(d.getTime() + (dow === 0 ? 1 : -1) * 86400000);
  }
  let hour, minute;
  if (Math.random() < 0.9) {
    hour = Math.random() < 0.6 ? 8 + Math.floor(Math.random() * 4) : 12 + Math.floor(Math.random() * 6);
    minute = hour === 17 ? Math.floor(Math.random() * 30) : Math.floor(Math.random() * 60);
  } else {
    hour = Math.floor(Math.random() * 24);
    minute = Math.floor(Math.random() * 60);
  }
  d.setHours(hour, minute, Math.floor(Math.random() * 60), 0);
  return d;
}
function addMinutos(d, min) { return new Date(d.getTime() + min * 60000); }
function addDiasHabiles(d, dias) {
  let r = new Date(d);
  let count = 0;
  while (count < dias) {
    r = new Date(r.getTime() + 86400000);
    if (r.getDay() !== 0 && r.getDay() !== 6) count++;
  }
  return r;
}

// ── Backdating plan (pase SQL acotado a columnas de fecha) ─────────────
const backdatePlan = [];
function planFecha(table, idCol, idVal, cols) {
  const clean = {};
  for (const [k, v] of Object.entries(cols)) if (v !== undefined && v !== null) clean[k] = v;
  if (Object.keys(clean).length === 0) return;
  backdatePlan.push({ table, idCol, idVal, cols: clean });
}

// ── Nombres ficticios (genéricos, no asociados a personas reales) ──────
const NOMBRES = ['Javiera','Matías','Fernanda','Diego','Camila','Rodrigo','Constanza','Felipe','Antonia','Cristóbal','Valentina','Sebastián','Francisca','Ignacio','Catalina','Tomás','Josefa','Benjamín','Daniela','Vicente','Paula','Gonzalo','Trinidad','Nicolás','Carolina','Andrés','Isidora','Cristian','Macarena','Pablo'];
const APELLIDOS = ['Muñoz','Rojas','Contreras','Silva','Espinoza','Tapia','Vergara','Bravo','Carrasco','Reyes','Morales','Araya','Fuentes','Soto','Pizarro','Cortés','Sepúlveda','Toro','Guzmán','Vidal','Riquelme','Aravena','Órdenes','Sandoval','Godoy','Salinas','Fernández','Castillo','Lagos','Vega'];
function pick(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

const handlesUsados = new Set();
function loginHandle(nombre, apellido) {
  const base = (nombre[0] + apellido).toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z]/g, '').slice(0, 8) || 'usr';
  let handle = base, n = 1;
  while (handlesUsados.has(handle)) { handle = (base.slice(0, Math.max(1, 9 - String(n).length)) + n); n++; }
  handlesUsados.add(handle);
  return handle;
}

async function crearUsuarioDemo({ nombres, apellidos, idDependencia, roles }) {
  const usuario = loginHandle(nombres, apellidos);
  const email = `${usuario}@demo.invalid`;
  const data = await api('POST', '/usuarios', {
    usuario: 'admin', clave: ENV.ADMIN_DEMO_PASS,
    body: { usuario, clave: PASSWORD_DEMO, nombres, apellidos, idDependencia, roles, email },
  });
  const u = { idUsuario: data.idUsuario, usuario, clave: PASSWORD_DEMO, nombres, apellidos, idDependencia };
  resumen.usuariosCreados.push({ ...u, roles });
  return u;
}

// ── Servicios (mapeados a dependencias reales vigentes) ────────────────
const SERVICIOS = [
  { nombre: 'Dirección', id: 32 },
  { nombre: 'Subdirección Gestión Clínica', id: 76 },
  { nombre: 'Subdirección Gestión Administrativa y Financiera', id: 75 },
  { nombre: 'Gestion y Desarrollo de Personas', id: 40 },
  { nombre: 'Departamento Gestion de las Personas', id: 2055 },
  { nombre: 'Oficina de Partes', id: 54 },
  { nombre: 'Servicio Clínico de Urgencia', id: 69 },
  { nombre: 'Servicio Clinico UTI', id: 72 },
  { nombre: 'Pabellon y Anestesia', id: 2012 },
  { nombre: 'Apoyo Imagenología', id: 12 },
  { nombre: 'Apoyo Laboratorio clínico', id: 13 },
  { nombre: 'Farmacia Clinica', id: 2009 },
  { nombre: 'Gestion de Calidad y Seguridad del Paciente', id: 37 },
  { nombre: 'I.A.A.S', id: 43 },
  { nombre: 'Estadística (Información para la Gestion Clínica)', id: 33 },
  { nombre: 'Abastecimiento', id: 1 },
  { nombre: 'Finanzas', id: 35 },
  { nombre: 'Tecnologías De la Información', id: 78 },
  { nombre: 'Jurídica', id: 46 },
  { nombre: 'Auditoria', id: 18 },
  { nombre: 'Gestión de Pacientes', id: 39 },
  { nombre: 'Participacion Ciudadana', id: 2003 },
];
const SERVICIOS_DESTINO_EXTRA = [
  { nombre: 'Capacitación', id: 24 },
  { nombre: 'Mantenimiento de Equipos Médicos', id: 50 },
  { nombre: 'Medicina Física y Rehabilitación', id: 51 },
  { nombre: 'Servicio Social', id: 74 },
  { nombre: 'Prevención de Riesgos', id: 56 },
];
const SERVICIOS_FIRMA_SIMPLE = [32, 76, 75, 69, 72, 37, 43, 35];

const actores = {}; // idDependencia -> { servicio, jefatura, subrogante, funcionarios: [] }
function actorAleatorio(idDep) {
  const a = actores[idDep];
  if (!a) return null;
  const pool = [a.jefatura, a.subrogante, ...a.funcionarios];
  return pick(pool);
}
function actorJefatura(idDep) { return actores[idDep]?.jefatura ?? null; }
function nombreServicio(idDep) {
  return actores[idDep]?.servicio ?? SERVICIOS_DESTINO_EXTRA.find((s) => s.id === idDep)?.nombre ?? String(idDep);
}

// ── Documento API wrappers ──────────────────────────────────────────────
async function crearDocumento({ actor, idTipoDocumento, materia, observaciones, idDestino, tipoDestinatario = 'D', idTipoCompromiso = 2, diasCompromiso = 5, reservado = false, tipoSoporte = 'D', fechaDocumento, despacharAhora }) {
  const obs = `${observaciones ?? ''} ${DEMO_TAG}`.trim();
  const body = {
    materia, idTipoDocumento, destinos: [idDestino], tipoDestinatario,
    idTipoCompromiso, diasCompromiso, observaciones: obs, reservado, tipoSoporte,
    fechaDocumento: fechaDocumento.toISOString(),
  };
  if (despacharAhora === false) body.despacharAhora = false;
  return api('POST', '/documentos', { usuario: actor.usuario, clave: actor.clave, body });
}
async function recepcionar(idDocumento, actor) {
  return api('POST', `/documentos/${idDocumento}/recepcionar`, { usuario: actor.usuario, clave: actor.clave, body: {} });
}
async function derivar(idDocumento, actor, idDestino, obs) {
  return api('POST', `/documentos/${idDocumento}/derivar`, { usuario: actor.usuario, clave: actor.clave, body: { idDestino, observaciones: `${obs} ${DEMO_TAG}` } });
}
async function terminar(idDocumento, actor, obs) {
  return api('POST', `/documentos/${idDocumento}/terminar`, { usuario: actor.usuario, clave: actor.clave, body: { observaciones: `${obs} ${DEMO_TAG}` } });
}

async function crearYAvanzarDocumento(opts) {
  const {
    origenId, destinos, idTipoDocumento, materia, observacion = '',
    urgente = false, reservado = false, tipoSoporte = 'D',
    fechaBase = randomFechaEnVentana(), outcome, diasCompromiso = 5,
    attachPdf = false, tag = '',
  } = opts;

  const actorOrigen = origenId === 54 ? actores[54].jefatura : actorAleatorio(origenId);
  const idTipoCompromiso = urgente ? 3 : 2;
  const primerDestino = destinos[0];

  const creado = await crearDocumento({
    actor: actorOrigen, idTipoDocumento, materia, observaciones: observacion,
    idDestino: primerDestino, idTipoCompromiso, diasCompromiso, reservado, tipoSoporte, fechaDocumento: fechaBase,
  });
  const idDocumento = creado.idDocumento;
  const numInterno = creado.numInterno;

  planFecha('documento', 'id_documento', idDocumento, { fecha_documento: fechaBase, fecha_sistema: fechaBase, fecha_update: fechaBase });
  if (creado.tramiteActual) {
    planFecha('tramite', 'id_seguimiento', creado.tramiteActual.idSeguimiento, { fecha_sistema: fechaBase, fecha_update: fechaBase, fecha_despacho: fechaBase });
  }

  if (attachPdf) {
    const pdf = fakePdfBuffer([materia, `Documento N° ${numInterno}`, tag, DEMO_TAG]);
    const up = await uploadFile('POST', '/archivos/upload', {
      usuario: actorOrigen.usuario, clave: actorOrigen.clave, fields: { idDocumento },
      fileFieldName: 'archivo', fileBuffer: pdf, fileName: `adjunto_${numInterno}_demo.pdf`, mime: 'application/pdf',
    });
    if (up?.idArchivo) planFecha('archivo_digital', 'id_archivo_digital', up.idArchivo, { fecha_sistema: fechaBase, fecha_update: fechaBase });
  }

  let tActual = fechaBase;
  if (outcome === 'pendiente') { registrarResumen(idDocumento, numInterno, `Pendiente — ${tag || materia}`); return { idDocumento, numInterno }; }

  let servicioActualId = primerDestino;
  let actorActual = actorAleatorio(servicioActualId);
  if (!actorActual) { registrarResumen(idDocumento, numInterno, `Pendiente (destino sin actor) — ${tag || materia}`); return { idDocumento, numInterno }; }

  tActual = ajustarHorario(addMinutos(tActual, 30 + Math.floor(Math.random() * 180)));
  let resp = await recepcionar(idDocumento, actorActual);
  if (resp.tramiteActual) planFecha('tramite', 'id_seguimiento', resp.tramiteActual.idSeguimiento, { fecha_sistema: tActual, fecha_update: tActual, fecha_recepcion: tActual });
  planFecha('documento', 'id_documento', idDocumento, { fecha_update: tActual });

  if (outcome === 'recepcionado') { registrarResumen(idDocumento, numInterno, `Recepcionado en ${nombreServicio(servicioActualId)} — ${tag || materia}`); return { idDocumento, numInterno }; }

  for (const siguienteDestino of destinos.slice(1)) {
    const jefe = actorJefatura(servicioActualId);
    if (!jefe) break;
    tActual = ajustarHorario(addDiasHabiles(tActual, 1));
    resp = await derivar(idDocumento, jefe, siguienteDestino, `Derivado a ${nombreServicio(siguienteDestino)}`);
    if (resp.tramiteActual) planFecha('tramite', 'id_seguimiento', resp.tramiteActual.idSeguimiento, { fecha_sistema: tActual, fecha_update: tActual });
    planFecha('documento', 'id_documento', idDocumento, { fecha_update: tActual });

    servicioActualId = siguienteDestino;
    actorActual = actorAleatorio(servicioActualId);
    if (!actorActual) break;
    tActual = ajustarHorario(addMinutos(tActual, 30 + Math.floor(Math.random() * 180)));
    resp = await recepcionar(idDocumento, actorActual);
    if (resp.tramiteActual) planFecha('tramite', 'id_seguimiento', resp.tramiteActual.idSeguimiento, { fecha_sistema: tActual, fecha_update: tActual, fecha_recepcion: tActual });
    planFecha('documento', 'id_documento', idDocumento, { fecha_update: tActual });
  }

  if (outcome === 'derivado') { registrarResumen(idDocumento, numInterno, `En trámite (derivado) en ${nombreServicio(servicioActualId)} — ${tag || materia}`); return { idDocumento, numInterno }; }

  tActual = ajustarHorario(addDiasHabiles(tActual, 1 + Math.floor(Math.random() * 4)));
  resp = await terminar(idDocumento, actorActual, `Proceso finalizado${tag ? ' — ' + tag : ''}`);
  if (resp.tramiteActual) planFecha('tramite', 'id_seguimiento', resp.tramiteActual.idSeguimiento, { fecha_sistema: tActual, fecha_update: tActual });
  planFecha('documento', 'id_documento', idDocumento, { fecha_update: tActual });

  registrarResumen(idDocumento, numInterno, `Terminado — ${tag || materia}`);
  return { idDocumento, numInterno };
}

// ── Materias / tipos ambientales ────────────────────────────────────────
const MATERIAS_AMBIENTE = [
  'Solicitud de reposición de monitores multiparámetro',
  'Actualización del protocolo de aislamiento respiratorio',
  'Informe de cumplimiento del plan de mantenimiento preventivo',
  'Solicitud de habilitación de acceso al sistema institucional',
  'Coordinación de capacitación sobre seguridad de la información',
  'Revisión de procedimiento para traslado intrahospitalario',
  'Solicitud de adquisición de insumos críticos',
  'Informe mensual de tiempos de respuesta documental',
  'Remisión de antecedentes para revisión jurídica',
  'Citación a reunión de coordinación asistencial',
  'Actualización de nómina de responsables por servicio',
  'Solicitud de pronunciamiento técnico',
  'Seguimiento de compromiso de mejora',
  'Informe sobre contingencia de infraestructura',
  'Remisión de acta del Comité de Innovación',
  'Solicitud de mantención correctiva de equipo biomédico',
  'Coordinación de turnos para fin de semana largo',
  'Informe de indicadores de calidad del trimestre',
  'Solicitud de autorización de horas extraordinarias',
  'Remisión de antecedentes para licitación de insumos',
  'Actualización de protocolo de limpieza de pabellones',
  'Solicitud de evaluación de riesgo ergonómico',
  'Informe de auditoría interna de historia clínica',
  'Coordinación de campaña de vacunación institucional',
  'Solicitud de habilitación de nueva unidad de camas',
  'Remisión de informe de satisfacción usuaria',
  'Actualización de flujograma de derivación de pacientes',
  'Solicitud de capacitación en normativa de bioseguridad',
  'Informe de disponibilidad de insumos críticos',
  'Coordinación de mantenimiento de red de gases clínicos',
];
const TIPOS_DOC_AMBIENTE = [1, 5, 35, 30, 84, 6, 10, 2, 9];

// ── Procesos completos (Fase 9) ──────────────────────────────────────────
const PROCESOS = [
  { tag: 'Adquisición de equipamiento clínico', origenId: 69, destinos: [1, 35, 32, 1], materia: 'Solicitud de reposición de monitores multiparámetro para Urgencia', outcome: 'terminado', attachPdf: true },
  { tag: 'Actualización de protocolo clínico', origenId: 37, destinos: [43, 76, 37, 32], materia: 'Actualización del protocolo de aislamiento respiratorio', outcome: 'terminado' },
  { tag: 'Recepción de oficio externo', origenId: 54, destinos: [46, 54], materia: 'Oficio recibido de Servicio de Salud — solicitud de antecedentes', outcome: 'terminado' },
  { tag: 'Incidente tecnológico', origenId: 75, destinos: [78, 75], materia: 'Reporte de interrupción del sistema institucional en Subdirección Administrativa', outcome: 'terminado', urgente: true },
  { tag: 'Compromiso de mejora (Auditoría)', origenId: 18, destinos: [2009, 18], materia: 'Observación de auditoría — seguimiento de compromiso de mejora en gestión de stock', outcome: 'terminado', attachPdf: true },
  { tag: 'Capacitación institucional', origenId: 2055, destinos: [24], materia: 'Coordinación de capacitación sobre seguridad de la información', outcome: 'pendiente' },
  { tag: 'Recursos Humanos', origenId: 40, destinos: [2055], materia: 'Actualización de nómina de responsables por servicio', outcome: 'terminado' },
  { tag: 'Gestión de camas', origenId: 69, destinos: [39, 72], materia: 'Solicitud de habilitación de nueva unidad de camas', outcome: 'derivado' },
  { tag: 'Farmacia', origenId: 72, destinos: [2009], materia: 'Solicitud de adquisición de insumos críticos de farmacia', outcome: 'terminado' },
  { tag: 'Laboratorio', origenId: 69, destinos: [13], materia: 'Solicitud de priorización de exámenes de laboratorio', outcome: 'recepcionado' },
  { tag: 'Imagenología', origenId: 72, destinos: [12], materia: 'Coordinación de estudio imagenológico urgente', outcome: 'terminado', urgente: true },
  { tag: 'Seguridad de la información', origenId: 78, destinos: [32], materia: 'Informe de incidente de seguridad de la información', outcome: 'terminado', urgente: true },
  { tag: 'Mantenimiento', origenId: 72, destinos: [50], materia: 'Solicitud de mantención correctiva de equipo biomédico', outcome: 'pendiente' },
  { tag: 'Infraestructura', origenId: 75, destinos: [1], materia: 'Informe sobre contingencia de infraestructura en red de gases clínicos', outcome: 'terminado' },
  { tag: 'Calidad', origenId: 43, destinos: [37], materia: 'Informe de indicadores de calidad del trimestre', outcome: 'terminado' },
  { tag: 'IAAS', origenId: 72, destinos: [43], materia: 'Notificación de vigilancia epidemiológica intrahospitalaria', outcome: 'terminado', urgente: true },
  { tag: 'Comité de Ética (vía Dirección)', origenId: 76, destinos: [32], materia: 'Remisión de antecedentes para evaluación del Comité de Ética', outcome: 'recepcionado' },
  { tag: 'Comité de Innovación', origenId: 32, destinos: [75], materia: 'Remisión de acta del Comité de Innovación', outcome: 'terminado' },
  { tag: 'Estadística', origenId: 33, destinos: [32], materia: 'Informe mensual de tiempos de respuesta documental', outcome: 'terminado' },
  { tag: 'OIRS (vía Participación Ciudadana)', origenId: 54, destinos: [2003], materia: 'Solicitud ciudadana derivada para respuesta institucional', outcome: 'recepcionado' },
  { tag: 'Contingencia asistencial', origenId: 69, destinos: [32, 69], materia: 'Informe de contingencia asistencial — capacidad crítica de urgencia', outcome: 'terminado', urgente: true },
  { tag: 'Pronunciamiento jurídico', origenId: 35, destinos: [46], materia: 'Solicitud de pronunciamiento técnico sobre convenio de suministro', outcome: 'terminado' },
];

// ── Reservados (Fase 12) ─────────────────────────────────────────────────
const MATERIAS_RESERVADAS = [
  'Revisión reservada de procedimiento administrativo DEMO',
  'Antecedentes ficticios para evaluación interna DEMO',
  'Documento reservado de prueba para Dirección DEMO',
  'Revisión reservada de antecedentes institucionales DEMO',
];

// ── Memorándums (Fase 11 + Fase 14 Firma Simple) ────────────────────────
async function crearMemorandum({ idDependencia, materia, referencia, cuerpo, firmar }) {
  const actor = actores[idDependencia].jefatura;
  const fechaBase = randomFechaEnVentana();
  const destinoMemo = idDependencia === 32 ? 75 : 32;

  const creado = await crearDocumento({
    actor, idTipoDocumento: 35, materia, observaciones: '', idDestino: destinoMemo,
    idTipoCompromiso: 2, diasCompromiso: 5, fechaDocumento: fechaBase, despacharAhora: false,
  });
  const idDocumento = creado.idDocumento;
  planFecha('documento', 'id_documento', idDocumento, { fecha_documento: fechaBase, fecha_sistema: fechaBase, fecha_update: fechaBase });

  const cargoFirmante = `Jefe(a) de ${actores[idDependencia].servicio} (DEMO)`;
  const confirmado = await api('POST', '/memorandum/confirmar', {
    usuario: actor.usuario, clave: actor.clave,
    body: {
      idDocumento, materia, referencia, cuerpo,
      nombreFirmante: `${actor.nombres} ${actor.apellidos}`, cargoFirmante, tipoFirmante: 'TITULAR', idDependencia,
    },
  });
  planFecha('memo_generado', 'id_documento', idDocumento, { fecha_creacion: fechaBase });

  const pdfBorrador = fakePdfBuffer([materia, confirmado.correlativo, 'BORRADOR', DEMO_TAG]);
  const up = await uploadFile('POST', '/archivos/upload', {
    usuario: actor.usuario, clave: actor.clave, fields: { idDocumento },
    fileFieldName: 'archivo', fileBuffer: pdfBorrador, fileName: `${confirmado.correlativo}.pdf`, mime: 'application/pdf',
  });
  if (up?.idArchivo) planFecha('archivo_digital', 'id_archivo_digital', up.idArchivo, { fecha_sistema: fechaBase, fecha_update: fechaBase });

  await api('PATCH', '/memorandum/vincular-archivo', { usuario: actor.usuario, clave: actor.clave, body: { correlativo: confirmado.correlativo, idArchivo: up.idArchivo } });

  if (!firmar) {
    resumen.memorandumsCreados.push({ idDocumento, correlativo: confirmado.correlativo, firmado: false });
    registrarResumen(idDocumento, creado.numInterno, `Memorándum ${confirmado.correlativo} (pendiente de firma)`);
    return;
  }

  const idJef = jefaturaIdPorDependencia[idDependencia];
  const fase1 = await api('POST', `/memorandum/${idDocumento}/firmar-simple`, {
    usuario: actor.usuario, clave: actor.clave,
    body: { idJefatura: idJef, tipoJefatura: 'TITULAR', password: actor.clave, confirmacion: true },
  });
  const tFirma = ajustarHorario(addMinutos(fechaBase, 20 + Math.floor(Math.random() * 100)));

  const pdfFirmado = fakePdfBuffer([materia, confirmado.correlativo, 'FIRMADO DIGITALMENTE (DEMO)', DEMO_TAG]);
  await uploadFile('PATCH', `/memorandum/${idDocumento}/firmar-simple/${fase1.idFirmaSimple}/completar`, {
    usuario: actor.usuario, clave: actor.clave, fileFieldName: 'archivo',
    fileBuffer: pdfFirmado, fileName: `${confirmado.correlativo}_firmado.pdf`, mime: 'application/pdf',
  });
  planFecha('memorandum_firma_simple', 'id_documento', idDocumento, { fecha_creacion: fechaBase, fecha_firmado: tFirma });
  planFecha('documento', 'id_documento', idDocumento, { fecha_update: tFirma });

  resumen.memorandumsCreados.push({ idDocumento, correlativo: confirmado.correlativo, firmado: true });
  registrarResumen(idDocumento, creado.numInterno, `Memorándum ${confirmado.correlativo} (FIRMADO — Firma Simple DOC360)`);
}

// ── main ──────────────────────────────────────────────────────────────
let jefaturaIdPorDependencia = {};
let dbPool;

async function main() {
  console.log('=== DOC360 — Generación de datos demostrativos ===');
  console.log(`Ventana histórica: ${DIAS_VENTANA} días (hasta ${HOY.toISOString()})`);

  dbPool = await sql.connect({
    server: 'localhost',
    port: Number(ENV.DB_PORT),
    user: ENV.DB_USER,
    password: ENV.DB_PASSWORD,
    database: ENV.DB_DATABASE,
    options: { encrypt: ENV.DB_ENCRYPT === 'true', trustServerCertificate: ENV.DB_TRUST_CERT === 'true' },
  });
  const jefRows = (await dbPool.request().query('SELECT id_jefatura, id_dependencia FROM jefatura')).recordset;
  jefaturaIdPorDependencia = Object.fromEntries(jefRows.map((r) => [r.id_dependencia, r.id_jefatura]));

  // admin ya existe — password documentada en CLAUDE.md
  ENV.ADMIN_DEMO_PASS = 'Huap.2025';
  await login('admin', ENV.ADMIN_DEMO_PASS);
  console.log('Login admin OK.');

  // ── 1. Usuarios ficticios por servicio ─────────────────────────────
  console.log(`\n--- Creando usuarios ficticios para ${SERVICIOS.length} servicios ---`);
  for (const s of SERVICIOS) {
    const rolJefatura = s.id === 54 ? ['of.partes'] : ['supervisores'];
    const jefatura = await crearUsuarioDemo({ nombres: pick(NOMBRES), apellidos: pick(APELLIDOS), idDependencia: s.id, roles: rolJefatura });
    const subrogante = await crearUsuarioDemo({ nombres: pick(NOMBRES), apellidos: pick(APELLIDOS), idDependencia: s.id, roles: ['funcionario'] });
    const funcionarios = [];
    const nFunc = 1 + Math.floor(Math.random() * 2);
    for (let i = 0; i < nFunc; i++) {
      funcionarios.push(await crearUsuarioDemo({ nombres: pick(NOMBRES), apellidos: pick(APELLIDOS), idDependencia: s.id, roles: ['funcionario'] }));
    }
    actores[s.id] = { servicio: s.nombre, jefatura, subrogante, funcionarios };
    console.log(`  ${s.nombre}: jefatura=${jefatura.usuario}, subrogante=${subrogante.usuario}, funcionarios=${funcionarios.map((f) => f.usuario).join(',')}`);
  }

  // ── 2. Firma Simple: configurar jefaturas seleccionadas ────────────
  console.log(`\n--- Configurando Firma Simple para ${SERVICIOS_FIRMA_SIMPLE.length} servicios ---`);
  for (const idDep of SERVICIOS_FIRMA_SIMPLE) {
    const idJef = jefaturaIdPorDependencia[idDep];
    const act = actores[idDep].jefatura;
    if (!idJef) { console.warn(`  Sin jefatura para dependencia ${idDep}, se omite.`); continue; }
    await api('POST', '/jefaturas', {
      usuario: 'admin', clave: ENV.ADMIN_DEMO_PASS,
      body: { idDependencia: idDep, nombreTitular: `${act.nombres} ${act.apellidos}`, cargoTitular: `Jefe(a) de ${actores[idDep].servicio} (DEMO)`, activoTitular: true },
    });
    await api('PATCH', `/jefaturas/${idJef}/vincular-usuario`, { usuario: 'admin', clave: ENV.ADMIN_DEMO_PASS, body: { tipo: 'TITULAR', idUsuario: act.idUsuario } });
    await uploadFile('POST', `/jefaturas/${idJef}/imagen?tipo=firma_timbre_titular`, {
      usuario: 'admin', clave: ENV.ADMIN_DEMO_PASS, fileFieldName: 'imagen',
      fileBuffer: DEMO_PNG, fileName: 'firma_timbre_demo.png', mime: 'image/png',
    });
    console.log(`  Firma Simple habilitada: ${actores[idDep].servicio} (${act.nombres} ${act.apellidos})`);
  }

  // ── 3. Procesos documentales completos ─────────────────────────────
  console.log(`\n--- Generando ${PROCESOS.length} procesos documentales completos ---`);
  for (const p of PROCESOS) {
    try {
      const r = await crearYAvanzarDocumento({ ...p, idTipoDocumento: pick(TIPOS_DOC_AMBIENTE), fechaBase: randomFechaEnVentana() });
      resumen.procesosCompletos.push({ ...r, tag: p.tag });
      console.log(`  [${p.tag}] Documento N° ${r.numInterno}`);
    } catch (e) {
      console.error(`  ERROR en proceso "${p.tag}": ${e.message}`);
      resumen.errores.push({ contexto: `proceso:${p.tag}`, error: e.message });
    }
  }

  // ── 4. Documentos ambientales ───────────────────────────────────────
  const N_AMBIENTE = 160;
  console.log(`\n--- Generando ${N_AMBIENTE} documentos ambientales ---`);
  const OUTCOMES = ['pendiente', 'pendiente', 'pendiente', 'recepcionado', 'recepcionado', 'derivado', 'terminado', 'terminado', 'terminado'];
  const todosDestinoIds = [...SERVICIOS.map((s) => s.id), ...SERVICIOS_DESTINO_EXTRA.map((s) => s.id)];
  for (let i = 0; i < N_AMBIENTE; i++) {
    const origen = pick(SERVICIOS);
    let destino = pick(todosDestinoIds);
    let tries = 0;
    while (destino === origen.id && tries < 5) { destino = pick(todosDestinoIds); tries++; }
    const outcome = pick(OUTCOMES);
    const urgente = Math.random() < 0.15;
    const attachPdf = Math.random() < 0.35;
    const destinos = [destino];
    if (outcome === 'derivado' && actores[destino] && Math.random() < 0.5) {
      let terc = pick(todosDestinoIds);
      if (terc !== destino) destinos.push(terc);
    }
    try {
      await crearYAvanzarDocumento({
        origenId: origen.id, destinos, idTipoDocumento: pick(TIPOS_DOC_AMBIENTE),
        materia: materiaConVariacion(), outcome, urgente, attachPdf,
        diasCompromiso: urgente ? 2 : 5 + Math.floor(Math.random() * 10),
        fechaBase: randomFechaEnVentana(),
      });
    } catch (e) {
      resumen.errores.push({ contexto: `ambiente:${i}`, error: e.message });
      if (resumen.errores.length % 10 === 0) console.error(`  (${resumen.errores.length} errores acumulados; último: ${e.message})`);
    }
    if ((i + 1) % 20 === 0) console.log(`  ...${i + 1}/${N_AMBIENTE}`);
  }

  // ── 5. Documentos reservados ────────────────────────────────────────
  console.log(`\n--- Generando ${MATERIAS_RESERVADAS.length} documentos reservados ---`);
  for (const materia of MATERIAS_RESERVADAS) {
    try {
      const outcome = pick(['recepcionado', 'terminado']);
      const r = await crearYAvanzarDocumento({
        origenId: 54, destinos: [32], idTipoDocumento: 15, materia, reservado: true,
        outcome, fechaBase: randomFechaEnVentana(), tag: 'Documento reservado',
      });
      resumen.reservados.push(r);
      console.log(`  Reservado N° ${r.numInterno}`);
    } catch (e) {
      console.error(`  ERROR reservado: ${e.message}`);
      resumen.errores.push({ contexto: 'reservado', error: e.message });
    }
  }

  // ── 6. Memorándums ───────────────────────────────────────────────────
  const SERVICIOS_MEMO_SIN_FIRMA = [40, 2055, 54, 78, 46, 18, 39, 2003, 33, 2009];
  console.log(`\n--- Generando memorándums (${SERVICIOS_FIRMA_SIMPLE.length} firmados + ${SERVICIOS_MEMO_SIN_FIRMA.length} pendientes) ---`);
  for (const idDep of SERVICIOS_FIRMA_SIMPLE) {
    try {
      await crearMemorandum({
        idDependencia: idDep, materia: `Memorándum interno — ${materiaConVariacion()}`,
        referencia: 'Coordinación interna DEMO', cuerpo: `Se remite el presente memorándum en el contexto de la gestión documental de ${actores[idDep].servicio}. ${DEMO_TAG}`,
        firmar: true,
      });
    } catch (e) {
      console.error(`  ERROR memo firmado (${nombreServicio(idDep)}): ${e.message}`);
      resumen.errores.push({ contexto: `memo-firmado:${idDep}`, error: e.message });
    }
  }
  for (const idDep of SERVICIOS_MEMO_SIN_FIRMA) {
    try {
      await crearMemorandum({
        idDependencia: idDep, materia: `Memorándum interno — ${materiaConVariacion()}`,
        referencia: 'Coordinación interna DEMO', cuerpo: `Se remite el presente memorándum en el contexto de la gestión documental de ${actores[idDep].servicio}. ${DEMO_TAG}`,
        firmar: false,
      });
    } catch (e) {
      console.error(`  ERROR memo pendiente (${nombreServicio(idDep)}): ${e.message}`);
      resumen.errores.push({ contexto: `memo-pendiente:${idDep}`, error: e.message });
    }
  }

  // ── 7. Backdating de fechas ──────────────────────────────────────────
  console.log(`\n--- Ejecutando backdating de fechas sobre ${backdatePlan.length} filas ---`);
  let i = 0;
  for (const entry of backdatePlan) {
    const req = dbPool.request();
    const setClauses = [];
    let p = 0;
    for (const [col, val] of Object.entries(entry.cols)) {
      const pname = `p${p++}`;
      req.input(pname, sql.DateTime, val);
      setClauses.push(`${col} = @${pname}`);
    }
    req.input('idval', sql.Int, entry.idVal);
    try {
      await req.query(`UPDATE ${entry.table} SET ${setClauses.join(', ')} WHERE ${entry.idCol} = @idval`);
    } catch (e) {
      resumen.errores.push({ contexto: `backdate:${entry.table}:${entry.idVal}`, error: e.message });
    }
    i++;
    if (i % 200 === 0) console.log(`  ...${i}/${backdatePlan.length}`);
  }
  console.log('Backdating completo.');

  // ── 8. Resumen final ──────────────────────────────────────────────
  const manifestPath = path.join(__dirname, 'resumen_generacion.json');
  fs.writeFileSync(manifestPath, JSON.stringify(resumen, null, 2), 'utf8');
  console.log(`\n=== RESUMEN ===`);
  console.log(`Usuarios ficticios creados: ${resumen.usuariosCreados.length}`);
  console.log(`Documentos creados: ${resumen.documentosCreados.length}`);
  console.log(`Procesos completos: ${resumen.procesosCompletos.length}`);
  console.log(`Reservados: ${resumen.reservados.length}`);
  console.log(`Memorándums: ${resumen.memorandumsCreados.length} (firmados: ${resumen.memorandumsCreados.filter((m) => m.firmado).length})`);
  console.log(`Errores: ${resumen.errores.length}`);
  console.log(`Manifest: ${manifestPath}`);

  await dbPool.close();
}

function materiaConVariacion() {
  const base = pick(MATERIAS_AMBIENTE);
  const sufijos = ['', ' — seguimiento', ' — actualización', ' — respuesta', ' — solicitud complementaria', ''];
  return (base + pick(sufijos)).trim();
}

main().catch(async (e) => {
  console.error('ERROR FATAL:', e);
  try { if (dbPool) await dbPool.close(); } catch {}
  process.exitCode = 1;
});
