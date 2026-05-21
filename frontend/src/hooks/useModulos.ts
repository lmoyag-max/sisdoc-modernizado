import { useAuthStore } from '@/stores/auth.store';

export function useModulos() {
  const user    = useAuthStore((s) => s.user);
  const modulos = user?.modulos ?? [];
  const isAdmin = user?.roles.includes('admin') ?? false;

  const puede = (modulo: string): boolean => {
    if (isAdmin) return true;
    return modulos.includes(modulo);
  };

  return { modulos, isAdmin, puede };
}
