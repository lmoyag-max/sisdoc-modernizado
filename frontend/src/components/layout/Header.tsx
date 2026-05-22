import { Menu, Search } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { Input } from '@/components/ui/input';
import { useAuthStore, displayName } from '@/stores/auth.store';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { iniciales } from '@/lib/utils';
import { useRole } from '@/hooks/useRole';
import { useState } from 'react';

interface HeaderProps {
  onMenuToggle?: () => void;
}

export function Header({ onMenuToggle }: HeaderProps) {
  const user     = useAuthStore((s) => s.user);
  const nombre   = displayName(user);
  const initials = iniciales(nombre);
  const navigate = useNavigate();
  const { isAdmin, isOfPartes, isSupervisor } = useRole();
  const rolLabel = isAdmin ? 'Administrador' : isOfPartes ? 'Of. Partes' : isSupervisor ? 'Supervisor' : 'Funcionario';

  const [searchVal, setSearchVal] = useState('');

  const handleSearch = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter' && searchVal.trim().length >= 2) {
      navigate(`/busqueda?q=${encodeURIComponent(searchVal.trim())}`);
      setSearchVal('');
    }
  };

  return (
    <header className="sticky top-0 z-40 flex h-14 items-center gap-3 border-b border-border/60 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 px-4 sm:px-6 header-border">

      {/* Hamburguesa — mobile únicamente */}
      <button
        onClick={onMenuToggle}
        className="flex lg:hidden h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors shrink-0"
        aria-label="Abrir menú"
        aria-expanded={false}
      >
        <Menu className="h-5 w-5" />
      </button>

      {/* Búsqueda — ocupa el espacio central disponible */}
      <div className="relative flex-1 max-w-xs hidden sm:block">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
        <Input
          type="search"
          value={searchVal}
          onChange={(e) => setSearchVal(e.target.value)}
          onKeyDown={handleSearch}
          placeholder="Buscar documentos, trámites…"
          className="pl-9 h-8 text-sm bg-muted/40 border-transparent focus-visible:border-border focus-visible:bg-background transition-colors rounded-lg"
        />
      </div>

      {/* Empuja el bloque usuario hacia la derecha */}
      <div className="flex-1" />

      {/* Usuario — alineado a la derecha */}
      <div className="flex items-center gap-2.5">
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
