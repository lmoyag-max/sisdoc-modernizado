import { useAuthStore } from '@/stores/auth.store';

export function useRole() {
  const roles = useAuthStore((s) => s.user?.roles ?? []);

  const isAdmin        = roles.includes('admin');
  const isOfPartes     = roles.includes('of.partes');
  const isSupervisor   = roles.includes('supervisores');
  const isFuncionario  = roles.includes('funcionario') || roles.length === 0;

  // Alias retrocompatible — of.partes hereda los permisos del antiguo coordinador
  const isCoordinador  = isOfPartes;

  return {
    roles,
    isAdmin,
    isOfPartes,
    isCoordinador,
    isSupervisor,
    isFuncionario,
    canCreate:      true,
    canDespachar:   isAdmin || isOfPartes,
    canRecepcionar: isAdmin || isOfPartes || isFuncionario || isSupervisor,
    canDerivar:     isAdmin || isOfPartes,
    canTerminar:    isAdmin || isOfPartes,
    canDelete:      isAdmin || isOfPartes,
    canManageUsers: isAdmin,
    canConfig:      isAdmin,
  };
}
