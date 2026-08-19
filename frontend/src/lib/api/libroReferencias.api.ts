import { apiClient } from './client';

export interface ReferenciaUsuario {
  id: number;
  nombre: string;
}

export interface ReferenciaEliminacion {
  usuario: string | null;
  fecha:   string | null;
  motivo:  string | null;
}

export interface Referencia {
  id:               number;
  codigo:           string;
  anio:             number;
  numero:           number;
  usuarioCreador:   ReferenciaUsuario;
  nombreInteresado: string;
  tipoTramite:      string;
  fechaDocumento:   string;
  fechaRecepcion:   string;
  observaciones:    string | null;
  condicion:        'VIGENTE' | 'ELIMINADO';
  eliminacion:      ReferenciaEliminacion | null;
  fechaCreacion:      string;
  fechaActualizacion: string;
}

export interface FiltrosReferencia {
  q?: string;
  anio?: number;
  fechaDocDesde?: string;
  fechaDocHasta?: string;
  fechaRecDesde?: string;
  fechaRecHasta?: string;
  orden?: 'correlativo_desc' | 'correlativo_asc' | 'fecha_desc' | 'fecha_asc';
  pagina?: number;
  porPagina?: number;
}

export interface PaginatedResult<T> {
  data: T[];
  meta: { total: number; pagina: number; porPagina: number; totalPaginas: number };
}

export interface MetricasReferencias {
  referenciasAnioActual: number;
  registradasHoy:        number;
  registradasMes:        number;
  eliminadas:             number;
}

export interface CrearReferenciaDto {
  nombreInteresado: string;
  tipoTramite:      string;
  fechaDocumento:   string;
  fechaRecepcion:   string;
  observaciones?:   string;
}

export type EditarReferenciaDto = CrearReferenciaDto;

export const libroReferenciasApi = {
  listar: async (filtros: FiltrosReferencia = {}): Promise<PaginatedResult<Referencia>> => {
    const { data } = await apiClient.get<PaginatedResult<Referencia>>('/libro-referencias', { params: filtros });
    return data;
  },
  listarEliminados: async (filtros: FiltrosReferencia = {}): Promise<PaginatedResult<Referencia>> => {
    const { data } = await apiClient.get<PaginatedResult<Referencia>>('/libro-referencias/eliminados', { params: filtros });
    return data;
  },
  obtener: async (id: number): Promise<Referencia> => {
    const { data } = await apiClient.get<{ ok: boolean; data: Referencia }>(`/libro-referencias/${id}`);
    return data.data;
  },
  metricas: async (): Promise<MetricasReferencias> => {
    const { data } = await apiClient.get<{ ok: boolean; data: MetricasReferencias }>('/libro-referencias/metricas');
    return data.data;
  },
  crear: async (dto: CrearReferenciaDto): Promise<Referencia> => {
    const { data } = await apiClient.post<{ ok: boolean; data: Referencia }>('/libro-referencias', dto);
    return data.data;
  },
  actualizar: async (id: number, dto: EditarReferenciaDto): Promise<Referencia> => {
    const { data } = await apiClient.patch<{ ok: boolean; data: Referencia }>(`/libro-referencias/${id}`, dto);
    return data.data;
  },
  eliminar: async (id: number, motivo: string): Promise<Referencia> => {
    const { data } = await apiClient.delete<{ ok: boolean; data: Referencia }>(`/libro-referencias/${id}`, { data: { motivo } });
    return data.data;
  },
  // Nivel 2 — eliminación física, exclusiva Superadministrador, solo sobre
  // un registro ya eliminado lógicamente. Endpoint separado a propósito.
  eliminarDefinitivo: async (id: number, motivo: string, confirmacion: string): Promise<Referencia> => {
    const { data } = await apiClient.delete<{ ok: boolean; data: Referencia }>(
      `/libro-referencias/${id}/permanent`,
      { data: { motivo, confirmacion } },
    );
    return data.data;
  },
};
