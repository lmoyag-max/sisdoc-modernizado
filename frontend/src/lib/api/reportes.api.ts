import { apiClient } from './client';

export interface DashboardData {
  totales: { total: number; pendientes: number; cerradosHoy: number; urgentes: number };
  porEstado: Array<{ id_estado_documento: number; desc_estado_documento: string; cantidad: number }>;
  porMes: Array<{ mes: string; cantidad: number }>;
}

export interface ActividadItem {
  id_historial: number;
  accion: string | null;
  fecha: string;
  asunto: string | null;
  num_documento: string | null;
  usuario: string | null;
  nombres_fun: string | null;
}

export const reportesApi = {
  dashboard: async (): Promise<DashboardData> => {
    const { data } = await apiClient.get<{ ok: boolean; data: DashboardData }>('/reportes/dashboard');
    return data.data;
  },

  actividadReciente: async (): Promise<ActividadItem[]> => {
    const { data } = await apiClient.get<{ ok: boolean; data: ActividadItem[] }>('/reportes/actividad-reciente');
    return data.data;
  },
};
