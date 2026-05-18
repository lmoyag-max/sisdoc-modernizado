import { apiClient } from './client';
import type { UserSession } from '@/stores/auth.store';

export interface LoginResponse {
  user: UserSession;
  accessToken: string;
  expiresIn: number;
}

export const authApi = {
  login: async (usuario: string, clave: string): Promise<LoginResponse> => {
    const { data } = await apiClient.post<{ ok: boolean; data: LoginResponse }>('/auth/login', { usuario, clave });
    return data.data;
  },

  logout: async (): Promise<void> => {
    await apiClient.post('/auth/logout');
  },

  me: async (): Promise<UserSession> => {
    const { data } = await apiClient.get<{ ok: boolean; data: UserSession }>('/auth/me');
    return data.data;
  },

  refresh: async (): Promise<{ accessToken: string; expiresIn: number }> => {
    const { data } = await apiClient.post<{ ok: boolean; data: { accessToken: string; expiresIn: number } }>('/auth/refresh');
    return data.data;
  },
};
