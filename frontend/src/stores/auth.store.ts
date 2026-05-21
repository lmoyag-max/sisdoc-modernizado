import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface UserSession {
  idUsuario:       number;
  usuario:         string;
  idFuncionario:   number | null;
  nombres:         string | null;
  apPat:           string | null;
  apMat:           string | null;
  email:           string | null;
  idDependencia:   number | null;
  descDependencia: string | null;
  todosServicios:  boolean;
  roles:           string[];
  modulos:         string[];
}

interface AuthState {
  user: UserSession | null;
  accessToken: string | null;
  isAuthenticated: boolean;
  setAuth: (user: UserSession, accessToken: string) => void;
  setAccessToken: (token: string) => void;
  logout: () => void;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      accessToken: null,
      isAuthenticated: false,

      setAuth: (user, accessToken) =>
        set({ user, accessToken, isAuthenticated: true }),

      setAccessToken: (accessToken) =>
        set({ accessToken }),

      logout: () =>
        set({ user: null, accessToken: null, isAuthenticated: false }),
    }),
    {
      name: 'sisdoc-auth',
      partialize: (state) => ({
        user: state.user,
        accessToken: state.accessToken,
        isAuthenticated: state.isAuthenticated,
      }),
    },
  ),
);

export const displayName = (user: UserSession | null): string => {
  if (!user) return '';
  const nombre = [user.nombres, user.apPat].filter(Boolean).join(' ');
  return nombre || user.usuario;
};
