import { z } from 'zod';

export const crearDocumentoSchema = z.object({
  materia: z.string().min(1, 'La materia es requerida').max(250),
  idTipoDocumento: z.number().int().positive('Selecciona un tipo de documento'),
  idEstadoDocumento: z.number().int().positive().default(1),
  observaciones: z.string().max(500).optional(),
  fechaDocumento: z.string().optional(),
  numDocumento: z.string().max(50).optional(),
});

export const actualizarDocumentoSchema = z.object({
  materia: z.string().min(1).max(250).optional(),
  idTipoDocumento: z.number().int().positive().optional(),
  idEstadoDocumento: z.number().int().positive().optional(),
  observaciones: z.string().max(500).optional(),
  fechaDocumento: z.string().optional(),
});

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
  fechaDesde: z.string().optional(),
  fechaHasta: z.string().optional(),
  pagina: z.string().default('1').transform(Number),
  porPagina: z.string().default('20').transform(Number),
});

export type CrearDocumentoDto = z.infer<typeof crearDocumentoSchema>;
export type DerivarDocumentoDto = z.infer<typeof derivarDocumentoSchema>;
export type FiltrosDocumentoDto = z.infer<typeof filtrosDocumentoSchema>;
