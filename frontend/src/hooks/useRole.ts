import { useAuthStore } from '@/stores/auth.store';

export function useRole() {
  const roles = useAuthStore((s) => s.user?.roles ?? []);

  const isAdmin       = roles.includes('admin');
  const isCoordinador = roles.includes('coordinador');
  const isFuncionario = roles.includes('funcionario') || roles.length === 0;

  return {
    roles,
    isAdmin,
    isCoordinador,
    isFuncionario,
    canCreate:      true,
    canDespachar:   isAdmin || isCoordinador,
    canRecepcionar: isAdmin || isCoordinador || isFuncionario,
    canDerivar:     isAdmin || isCoordinador,
    canTerminar:    isAdmin || isCoordinador,
    canDelete:      isAdmin || isCoordinador,
    canManageUsers: isAdmin,
    canConfig:      isAdmin,
  };
}
