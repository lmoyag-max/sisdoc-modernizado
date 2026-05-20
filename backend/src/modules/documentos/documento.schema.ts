import { z } from 'zod';

// ── Crear documento + trámite inicial ───────────────────────
export const crearDocumentoSchema = z.object({
  // Identificación del documento
  materia: z.string().min(1, 'La materia es requerida').max(250),
  idTipoDocumento: z.number().int().positive('Selecciona un tipo de documento'),
  idEstadoDocumento: z.number().int().positive().default(1),
  fechaDocumento: z.string().optional(),
  observaciones: z.string().max(500).optional(),
  medio: z.string().max(1).optional(),              // M=Papel, E=Electrónico
  original: z.string().max(1).default('S'),         // S/N
  idExpediente: z.number().int().positive().optional(),

  // Trámite — Origen
  tipoProcedencia: z.enum(['D', 'E']).default('D'), // D=Dependencia interna, E=Externa
  idProcedencia: z.number().int().positive().optional(),

  // Trámite — Destino
  tipoDestinatario: z.enum(['D', 'E']).default('D'),
  idDestino: z.number().int().positive().optional(),

  // Trámite — Distribución / compromiso
  idTipoDistribucion: z.number().int().positive().default(5), // 5=Para conocimiento
  idTipoCompromiso: z.number().int().positive().default(1),   // 1=No tiene
  idEstadoCompromiso: z.number().int().positive().default(2), // 2=En trámite
  diasCompromiso: z.number().int().min(0).default(0),

  // Acción inmediata
  despacharAhora: z.boolean().default(false),
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

// ── Filtros de listado ───────────────────────────────────────
export const filtrosDocumentoSchema = z.object({
  q: z.string().optional(),
  idTipo: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  idEstado: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  idDependencia: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  fechaDesde: z.string().optional(),
  fechaHasta: z.string().optional(),
  pagina: z.string().default('1').transform(Number),
  porPagina: z.string().default('20').transform(Number),
});

export type CrearDocumentoDto   = z.infer<typeof crearDocumentoSchema>;
export type DespacharDto        = z.infer<typeof despacharSchema>;
export type RecepcionarDto      = z.infer<typeof recepcionarSchema>;
export type DerivarDto          = z.infer<typeof derivarSchema>;
export type TerminarDto         = z.infer<typeof terminarSchema>;
export type FiltrosDocumentoDto = z.infer<typeof filtrosDocumentoSchema>;
