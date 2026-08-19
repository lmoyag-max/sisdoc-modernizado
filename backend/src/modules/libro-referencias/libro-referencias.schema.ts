import { z } from 'zod';

// ── Crear registro ───────────────────────────────────────────
// El correlativo, año, código y usuario creador NUNCA vienen del body —
// el backend los asigna (ver libro-referencias.service.ts).
export const crearReferenciaSchema = z.object({
  nombreInteresado: z.string().trim().min(1, 'El nombre del funcionario o interesado es requerido').max(150),
  tipoTramite:      z.string().trim().min(1, 'El tipo de trámite es requerido').max(150),
  fechaDocumento:   z.string().min(1, 'La fecha del documento es requerida'),
  fechaRecepcion:   z.string().min(1, 'La fecha de recepción es requerida'),
  observaciones:    z.string().trim().max(1000).optional(),
});

// ── Editar registro ──────────────────────────────────────────
// Mismos campos editables que la creación — correlativo/año/usuario
// creador/fecha técnica de creación quedan fuera intencionalmente.
export const editarReferenciaSchema = crearReferenciaSchema;

// ── Eliminar lógicamente (solo Superadministrador) ───────────
export const eliminarReferenciaSchema = z.object({
  motivo: z.string().trim().min(5, 'El motivo de eliminación es requerido (mínimo 5 caracteres)').max(500),
});

// ── Eliminar definitivamente (nivel 2 — solo Superadministrador) ──
// Solo aplica sobre un registro ya en condición ELIMINADO. Exige motivo y
// una palabra de confirmación escrita, distinto de la eliminación lógica.
export const eliminarDefinitivoSchema = z.object({
  motivo: z.string().trim().min(5, 'El motivo de eliminación definitiva es requerido (mínimo 5 caracteres)').max(500),
  confirmacion: z.string().refine((v) => v === 'ELIMINAR', {
    message: 'Debes escribir ELIMINAR para confirmar la eliminación definitiva',
  }),
});

// ── Filtros de listado ───────────────────────────────────────
export const filtrosReferenciaSchema = z.object({
  q:                 z.string().optional(),
  anio:              z.string().optional().transform((v) => (v ? Number(v) : undefined)),
  fechaDocDesde:     z.string().optional(),
  fechaDocHasta:     z.string().optional(),
  fechaRecDesde:     z.string().optional(),
  fechaRecHasta:     z.string().optional(),
  orden: z.enum(['correlativo_desc', 'correlativo_asc', 'fecha_desc', 'fecha_asc']).default('correlativo_desc'),
  pagina:    z.string().default('1').transform(Number),
  porPagina: z.string().default('20').transform(Number),
});

export type CrearReferenciaDto        = z.infer<typeof crearReferenciaSchema>;
export type EditarReferenciaDto       = z.infer<typeof editarReferenciaSchema>;
export type EliminarReferenciaDto     = z.infer<typeof eliminarReferenciaSchema>;
export type EliminarDefinitivoDto     = z.infer<typeof eliminarDefinitivoSchema>;
export type FiltrosReferenciaDto      = z.infer<typeof filtrosReferenciaSchema>;
