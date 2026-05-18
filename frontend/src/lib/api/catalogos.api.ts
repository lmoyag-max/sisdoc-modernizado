import { apiClient } from './client';

export interface CatalogoItem {
  id: number;
  descripcion: string;
  [key: string]: unknown;
}

const get = async <T>(url: string, params?: Record<string, unknown>): Promise<T[]> => {
  const { data } = await apiClient.get<{ ok: boolean; data: T[] }>(url, { params });
  return data.data;
};

export const catalogosApi = {
  tiposDocumento: () => get<CatalogoItem>('/catalogos/tipos-documento'),
  estados: () => get<CatalogoItem>('/catalogos/estados'),
  prioridades: () => get<CatalogoItem & { color: string }>('/catalogos/prioridades'),
  dependencias: (todas = false) => get<CatalogoItem & { sigla: string }>('/catalogos/dependencias', { todas }),
  dependenciasExternas: () => get<CatalogoItem>('/catalogos/dependencias-externas'),
  descriptores: () => get<CatalogoItem>('/catalogos/descriptores'),
  funcionariosPorDependencia: (idDep: number) =>
    get<{ id: number; nombre: string; email: string }>(`/catalogos/dependencias/${idDep}/funcionarios`),
};
