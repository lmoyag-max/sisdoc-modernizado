import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';
import { useAuthStore } from '@/stores/auth.store';

const API_BASE = '/api/v1';

export const apiClient = axios.create({
  baseURL: API_BASE,
  withCredentials: true,
  headers: { 'Content-Type': 'application/json' },
  timeout: 30_000,
});

// Adjuntar access token en cada request.
// Si el body es FormData, eliminar Content-Type para que Axios/browser
// ponga automáticamente 'multipart/form-data; boundary=...' con el boundary correcto.
// Sin esto, el default 'application/json' bloquea al browser de añadir el boundary
// y multer recibe un request sin cuerpo multipart válido.
apiClient.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = useAuthStore.getState().accessToken;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  if (config.data instanceof FormData) {
    delete config.headers['Content-Type'];
  }
  return config;
});

let isRefreshing = false;
let queue: Array<{ resolve: (token: string) => void; reject: (err: unknown) => void }> = [];

const processQueue = (error: unknown, token: string | null) => {
  queue.forEach(({ resolve, reject }) => (token ? resolve(token) : reject(error)));
  queue = [];
};

// Auto-refresh cuando el token expira (401)
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const original = error.config as InternalAxiosRequestConfig & { _retry?: boolean };

    if (error.response?.status !== 401 || original._retry) {
      return Promise.reject(error);
    }

    // Evitar loop de refresh en /auth/login y /auth/refresh
    const url = original.url ?? '';
    if (url.includes('/auth/login') || url.includes('/auth/refresh')) {
      useAuthStore.getState().logout();
      return Promise.reject(error);
    }

    if (isRefreshing) {
      return new Promise((resolve, reject) => {
        queue.push({ resolve, reject });
      }).then((token) => {
        original.headers.Authorization = `Bearer ${token}`;
        return apiClient(original);
      });
    }

    original._retry = true;
    isRefreshing = true;

    try {
      const { data } = await axios.post(`${API_BASE}/auth/refresh`, {}, { withCredentials: true });
      const newToken: string = data.data.accessToken;
      useAuthStore.getState().setAccessToken(newToken);
      processQueue(null, newToken);
      original.headers.Authorization = `Bearer ${newToken}`;
      return apiClient(original);
    } catch (refreshError) {
      processQueue(refreshError, null);
      useAuthStore.getState().logout();
      return Promise.reject(refreshError);
    } finally {
      isRefreshing = false;
    }
  },
);
