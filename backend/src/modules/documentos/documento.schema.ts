import { z } from 'zod';

export const crearDocumentoSchema = z.object({
  idTipoDocumento: z.number().int().positive(),
  numDocumento: z.string().max(50).optional(),
  asunto: z.string().min(1).max(300),
  idPrioridad: z.number().int().positive().default(1),
  idProcedencia: z.number().int().positive().optional(),
  idProcedenciaExterna: z.number().int().positive().optional(),
  idDestino: z.number().int().positive(),
  idExpediente: z.number().int().positive().optional(),
  fechaDocumento: z.string().optional(),
  observacion: z.string().max(500).optional(),
  descriptores: z.array(z.number().int().positive()).default([]),
});

export const actualizarDocumentoSchema = crearDocumentoSchema.partial();

export const derivarDocumentoSchema = z.object({
  idDependenciaDestino: z.number().int().positive(),
  idFuncionarioDestino: z.number().int().positive().optional(),
  observacion: z.string().max(500).optional(),
});

export const filtrosDocumentoSchema = z.object({
  q: z.string().optional(),
  idTipo: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  idEstado: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  idDependencia: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  idPrioridad: z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  fechaDesde: z.string().optional(),
  fechaHasta: z.string().optional(),
  pagina: z.string().default('1').transform(Number),
  porPagina: z.string().default('20').transform(Number),
});

export type CrearDocumentoDto = z.infer<typeof crearDocumentoSchema>;
export type DerivarDocumentoDto = z.infer<typeof derivarDocumentoSchema>;
export type FiltrosDocumentoDto = z.infer<typeof filtrosDocumentoSchema>;
