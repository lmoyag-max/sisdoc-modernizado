import { Menu } from 'lucide-react';
import { useAuthStore, displayName } from '@/stores/auth.store';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { iniciales } from '@/lib/utils';
import { useRole } from '@/hooks/useRole';

interface HeaderProps {
  onMenuToggle?: () => void;
}

export function Header({ onMenuToggle }: HeaderProps) {
  const user     = useAuthStore((s) => s.user);
  const nombre   = displayName(user);
  const initials = iniciales(nombre);
  const { isAdmin, isOfPartes, isSupervisor } = useRole();
  const rolLabel = isAdmin ? 'Administrador' : isOfPartes ? 'Of. Partes' : isSupervisor ? 'Supervisor' : 'Funcionario';

  return (
    <header className="sticky top-0 z-40 flex h-14 items-center justify-between border-b border-border/60 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 px-4 sm:px-6 header-border">

      {/* Hamburguesa — mobile únicamente */}
      <button
        onClick={onMenuToggle}
        className="flex lg:hidden h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
        aria-label="Abrir menú"
        aria-expanded={false}
      >
        <Menu className="h-5 w-5" />
      </button>

      {/* Espacio vacío en desktop para empujar usuario a la derecha */}
      <div className="hidden lg:block" />

      {/* Usuario — siempre alineado a la derecha */}
      <div className="flex items-center gap-2.5 ml-auto">
        <div className="hidden sm:block text-right leading-tight">
          <p className="text-xs font-medium text-foreground truncate max-w-[160px]">{nombre || user?.usuario}</p>
          <p className="text-[10px] text-muted-foreground">{rolLabel}</p>
        </div>
        <Avatar className="h-7 w-7 cursor-pointer ring-2 ring-primary/20 transition-all hover:ring-primary/40">
          <AvatarFallback className="bg-primary text-primary-foreground text-[10px] font-semibold">
            {initials}
          </AvatarFallback>
        </Avatar>
      </div>
    </header>
  );
}
