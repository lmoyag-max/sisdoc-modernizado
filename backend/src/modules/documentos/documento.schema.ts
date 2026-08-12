import { z } from 'zod';

// ── Crear documento + trámite inicial ───────────────────────
// Nota: idEstadoDocumento, tipoProcedencia, idProcedencia y despacharAhora
// son ignorados — el backend los asigna automáticamente desde el usuario autenticado.
export const crearDocumentoSchema = z.object({
  // Identificación del documento
  materia: z.string().max(250).optional().default(''),
  idTipoDocumento: z.number().int().positive('Selecciona un tipo de documento'),
  fechaDocumento: z.string().optional(),
  observaciones: z.string().max(500).optional(),
  medio: z.string().max(1).optional(),
  original: z.string().max(1).default('S'),
  // Trámite — Destino: uno o múltiples servicios
  // destinos[] tiene prioridad. Si solo se envía idDestino, se convierte internamente.
  tipoDestinatario: z.enum(['D', 'E']).default('D'),
  idDestino: z.number().int().positive().optional(),
  destinos:  z.array(z.number().int().positive()).min(1).max(20).optional(),

  // Trámite — Distribución / compromiso
  idTipoDistribucion: z.number().int().positive().default(5),
  idTipoCompromiso: z.number().int().positive().default(1),
  idEstadoCompromiso: z.number().int().positive().default(2),
  diasCompromiso: z.number().int().min(0).default(0),

  // Campos Oficina de Partes (solo of.partes y admin pueden enviarlos)
  // tipoSoporte: 'D'=Digital (flujo normal), 'F'=Físico/Papel (genera nómina)
  tipoSoporte: z.enum(['D', 'F']).default('D').optional(),
  // reservado: true → destino forzado a Dirección (id=32), sin nomina
  reservado: z.boolean().default(false).optional(),

  // Campos ignorados desde frontend (mantenidos por retrocompatibilidad)
  idEstadoDocumento: z.number().int().optional(),
  tipoProcedencia: z.enum(['D', 'E']).optional(),
  idProcedencia: z.number().int().optional(),
  despacharAhora: z.boolean().optional(),
});

// ── Despachar documento ──────────────────────────────────────
export const despacharSchema = z.object({
  idDestino: z.number().int().positive('El destino es requerido'),
  tipoDestinatario: z.enum(['D', 'E']).default('D'),
  idTipoDistribucion: z.number().int().positive().default(5),
  idTipoCompromiso: z.number().int().positive().default(1),
  idEstadoCompromiso: z.number().int().positive().default(2),
  diasCompromiso: z.number().int().min(0).default(0),
  observaciones: z.string().max(500).optional(),
});

// ── Recepcionar documento ────────────────────────────────────
export const recepcionarSchema = z.object({
  idSeguimiento: z.number().int().positive().optional(),
  observaciones: z.string().max(500).optional(),
});

// ── Derivar documento ────────────────────────────────────────
export const derivarSchema = z.object({
  idDestino: z.number().int().positive('El destino es requerido'),
  tipoDestinatario: z.enum(['D', 'E']).default('D'),
  idTipoDistribucion: z.number().int().positive().default(5),
  idTipoCompromiso: z.number().int().positive().default(1),
  diasCompromiso: z.number().int().min(0).default(0),
  observaciones: z.string().max(500).optional(),
});

// ── Terminar documento ───────────────────────────────────────
export const terminarSchema = z.object({
  observaciones: z.string().max(500).optional(),
});

// ── Reabrir documento (Terminado → Recepcionado) ─────────────
export const reabrirSchema = z.object({
  observaciones: z.string().min(1, 'Debe indicar el motivo de reapertura').max(500),
});

// ── Filtros de listado ───────────────────────────────────────
export const filtrosDocumentoSchema = z.object({
  q: z.string().optional(),
  idTipo: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  idEstado: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  // Servicio actualmente responsable del documento (destino del último trámite).
  // Solo tiene efecto para usuarios con acceso total (admin / todos_servicios) —
  // para el resto, la visibilidad ya está acotada a su propio servicio.
  idDependencia: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  fechaDesde: z.string().optional(),
  fechaHasta: z.string().optional(),
  soloAtrasados: z.string().optional().transform((v) => v === 'true'),
  proximoAVencer: z.string().optional().transform((v) => v === 'true'),
  orden: z.enum(['fecha_desc', 'fecha_asc', 'antiguedad_desc', 'antiguedad_asc']).default('fecha_desc'),
  pagina: z.string().default('1').transform(Number),
  porPagina: z.string().default('20').transform(Number),
});

export type CrearDocumentoDto   = z.infer<typeof crearDocumentoSchema>;
export type DespacharDto        = z.infer<typeof despacharSchema>;
export type RecepcionarDto      = z.infer<typeof recepcionarSchema>;
export type DerivarDto          = z.infer<typeof derivarSchema>;
export type TerminarDto         = z.infer<typeof terminarSchema>;
export type ReabrirDto          = z.infer<typeof reabrirSchema>;
export type FiltrosDocumentoDto = z.infer<typeof filtrosDocumentoSchema>;
