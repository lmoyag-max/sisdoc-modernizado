import { Menu } from 'lucide-react';
import { useAuthStore, displayName } from '@/stores/auth.store';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { iniciales } from '@/lib/utils';

interface HeaderProps {
  onMenuToggle?: () => void;
}

export function Header({ onMenuToggle }: HeaderProps) {
  const user     = useAuthStore((s) => s.user);
  const initials = iniciales(displayName(user));

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

      {/* Avatar — único elemento visible a la derecha */}
      <Avatar className="ml-auto h-7 w-7 cursor-pointer ring-2 ring-primary/20 transition-all hover:ring-primary/40">
        <AvatarFallback className="bg-primary text-primary-foreground text-[10px] font-semibold">
          {initials}
        </AvatarFallback>
      </Avatar>
    </header>
  );
}
