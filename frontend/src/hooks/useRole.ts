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
    canCreate:       true,
    // Despacho inicial (solo roles con capacidad de flujo documental completo)
    canDespachar:    isAdmin || isOfPartes || isSupervisor,
    // Redespacho: cualquier usuario puede redirigir un doc que llegó a su servicio
    canRedespachar:  isAdmin || isOfPartes || isSupervisor || isFuncionario,
    canRecepcionar:  isAdmin || isOfPartes || isFuncionario || isSupervisor,
    canDerivar:      isAdmin || isOfPartes || isSupervisor,
    canTerminar:     isAdmin || isOfPartes || isSupervisor,
    canDelete:       isAdmin || isOfPartes,
    canManageUsers:  isAdmin,
    canConfig:       isAdmin,
  };
}
