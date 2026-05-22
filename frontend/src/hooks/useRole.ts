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
    // Terminar: cualquier rol puede cerrar un doc que está Recepcionado (estado=3)
    // El frontend valida además que estadoId === 3 antes de mostrar el botón
    canTerminar:     isAdmin || isOfPartes || isSupervisor || isFuncionario,
    // Reabrir (Terminado → Recepcionado): SOLO admin y supervisores; of.partes y funcionario NO
    canReabrir:      isAdmin || isSupervisor,
    // Eliminar: solo admin
    canDelete:       isAdmin,
    canManageUsers:  isAdmin,
    canConfig:       isAdmin,
  };
}
