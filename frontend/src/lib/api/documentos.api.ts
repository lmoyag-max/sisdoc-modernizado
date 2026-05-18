import { apiClient } from './client';

export interface Documento {
  idDocumento: number;
  numDocumento: string | null;
  asunto: string | null;
  tipoDocumento: { id: number | null; descripcion: string | null };
  estadoDocumento: { id: number | null; descripcion: string | null };
  prioridad: { id: number | null; descripcion: string | null; color: string | null };
  procedencia: { id: number | null; descripcion: string | null };
  destino: { id: number | null; descripcion: string | null };
  ingresadoPor: { id: number | null; usuario: string | null; nombre: string };
  fechaDocumento: string | null;
  fechaIngreso: string | null;
  fechaCierre: string | null;
  observacion: string | null;
}

export interface FiltrosDocumento {
  q?: string;
  idTipo?: number;
  idEstado?: number;
  idDependencia?: number;
  idPrioridad?: number;
  fechaDesde?: string;
  fechaHasta?: string;
  pagina?: number;
  porPagina?: number;
}

export interface PaginatedResult<T> {
  data: T[];
  meta: { total: number; pagina: number; porPagina: number; totalPaginas: number };
}

export const documentosApi = {
  listar: async (filtros: FiltrosDocumento = {}): Promise<PaginatedResult<Documento>> => {
    const { data } = await apiClient.get<PaginatedResult<Documento>>('/documentos', { params: filtros });
    return data;
  },

  obtener: async (id: number): Promise<Documento> => {
    const { data } = await apiClient.get<{ ok: boolean; data: Documento }>(`/documentos/${id}`);
    return data.data;
  },

  historial: async (id: number) => {
    const { data } = await apiClient.get<{ ok: boolean; data: unknown[] }>(`/documentos/${id}/historial`);
    return data.data;
  },

  crear: async (dto: unknown): Promise<Documento> => {
    const { data } = await apiClient.post<{ ok: boolean; data: Documento }>('/documentos', dto);
    return data.data;
  },

  derivar: async (id: number, dto: unknown): Promise<Documento> => {
    const { data } = await apiClient.post<{ ok: boolean; data: Documento }>(`/documentos/${id}/derivar`, dto);
    return data.data;
  },
};
