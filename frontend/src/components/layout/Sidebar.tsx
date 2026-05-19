import { useState } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import {
  LayoutDashboard, FileText, Inbox, Send, GitBranch,
  FolderOpen, Search, Upload, Users, BarChart3,
  Settings, LogOut, ChevronLeft, ChevronRight,
  Building2, Bell,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { useAuthStore, displayName } from '@/stores/auth.store';
import { authApi } from '@/lib/api/auth.api';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';

interface NavItem {
  label: string;
  to: string;
  icon: React.ComponentType<{ className?: string }>;
  badge?: number;
}

const navPrincipal: NavItem[] = [
  { label: 'Dashboard',        to: '/dashboard',     icon: LayoutDashboard },
  { label: 'Documentos',       to: '/documentos',    icon: FileText },
  { label: 'Bandeja entrada',  to: '/bandeja',       icon: Inbox },
  { label: 'Enviados',         to: '/enviados',      icon: Send },
  { label: 'Mis Trámites',     to: '/tramites',      icon: GitBranch },
  { label: 'Trazabilidad',     to: '/trazabilidad',  icon: GitBranch },
  { label: 'Búsqueda',         to: '/busqueda',      icon: Search },
  { label: 'Archivos',         to: '/archivos',      icon: Upload },
];

const navAdmin: NavItem[] = [
  { label: 'Expedientes',   to: '/expedientes',           icon: FolderOpen },
  { label: 'Usuarios',      to: '/admin/usuarios',        icon: Users },
  { label: 'Reportes',      to: '/reportes',              icon: BarChart3 },
  { label: 'Configuración', to: '/admin/configuracion',   icon: Settings },
];

function iniciales(nombre: string): string {
  return nombre.split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase();
}

export function Sidebar() {
  const [collapsed, setCollapsed] = useState(false);
  const user = useAuthStore((s) => s.user);
  const logout = useAuthStore((s) => s.logout);
  const navigate = useNavigate();

  const nombre = displayName(user);
  const initials = iniciales(nombre || 'US');

  const handleLogout = async () => {
    try { await authApi.logout(); } catch { /* silencioso */ }
    logout();
    navigate('/login');
    toast.success('Sesión cerrada');
  };

  return (
    <aside
      className={cn(
        'relative flex flex-col h-screen sidebar-gradient text-sidebar-foreground border-r border-sidebar-border transition-all duration-300 ease-in-out shrink-0',
        collapsed ? 'w-16' : 'w-64',
      )}
    >
      {/* Logo */}
      <div className={cn('flex items-center h-16 px-4 border-b border-sidebar-border shrink-0', collapsed && 'justify-center px-2')}>
        <div className="flex items-center gap-3">
          <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground font-bold text-xs shadow">
            <Building2 className="h-4 w-4" />
          </div>
          {!collapsed && (
            <div className="animate-fade-in min-w-0">
              <p className="text-sm font-semibold text-sidebar-foreground leading-tight">SISDOC</p>
              <p className="text-[10px] text-sidebar-foreground/50 leading-tight">HUAP · Gestión Documental</p>
            </div>
          )}
        </div>
      </div>

      {/* Botón collapse */}
      <button
        onClick={() => setCollapsed(!collapsed)}
        className="absolute -right-3 top-[72px] z-20 flex h-6 w-6 items-center justify-center rounded-full border border-sidebar-border bg-sidebar text-sidebar-foreground shadow-sm hover:bg-sidebar-accent transition-colors"
        aria-label={collapsed ? 'Expandir menú' : 'Colapsar menú'}
      >
        {collapsed ? <ChevronRight className="h-3 w-3" /> : <ChevronLeft className="h-3 w-3" />}
      </button>

      {/* Navegación */}
      <nav className="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2 space-y-0.5 scrollbar-thin">
        {/* Sección principal */}
        {!collapsed && (
          <p className="px-3 py-1 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/40 mb-1">
            Principal
          </p>
        )}
        {navPrincipal.map((item) => (
          <SidebarItem key={item.to} item={item} collapsed={collapsed} />
        ))}

        <div className="py-2 px-1">
          <Separator className="bg-sidebar-border" />
        </div>

        {/* Sección administración */}
        {!collapsed && (
          <p className="px-3 py-1 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/40 mb-1">
            Administración
          </p>
        )}
        {navAdmin.map((item) => (
          <SidebarItem key={item.to} item={item} collapsed={collapsed} />
        ))}
      </nav>

      {/* Perfil de usuario */}
      <div className="border-t border-sidebar-border p-2 shrink-0">
        <div className={cn('flex items-center gap-3 rounded-lg p-2 hover:bg-sidebar-accent transition-colors', collapsed && 'justify-center')}>
          <Avatar className="h-8 w-8 shrink-0">
            <AvatarFallback className="bg-primary/20 text-primary text-xs font-semibold">
              {initials}
            </AvatarFallback>
          </Avatar>
          {!collapsed && (
            <div className="flex-1 min-w-0 animate-fade-in">
              <p className="text-xs font-semibold truncate text-sidebar-foreground leading-tight">{nombre}</p>
              <p className="text-[10px] text-sidebar-foreground/50 truncate leading-tight">{user?.usuario}</p>
            </div>
          )}
          {!collapsed && (
            <Button
              variant="ghost"
              size="icon"
              className="h-7 w-7 shrink-0 text-sidebar-foreground/50 hover:text-destructive hover:bg-sidebar-accent"
              onClick={handleLogout}
              title="Cerrar sesión"
            >
              <LogOut className="h-3.5 w-3.5" />
            </Button>
          )}
        </div>
        {collapsed && (
          <Button
            variant="ghost"
            size="icon"
            className="w-full h-8 mt-1 text-sidebar-foreground/40 hover:text-destructive hover:bg-sidebar-accent"
            onClick={handleLogout}
            title="Cerrar sesión"
          >
            <LogOut className="h-4 w-4" />
          </Button>
        )}
      </div>
    </aside>
  );
}

function SidebarItem({ item, collapsed }: { item: NavItem; collapsed: boolean }) {
  const Icon = item.icon;
  return (
    <NavLink
      to={item.to}
      title={collapsed ? item.label : undefined}
      className={({ isActive }) =>
        cn(
          'relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-all duration-150 group overflow-hidden',
          'text-sidebar-foreground/60 hover:text-sidebar-foreground hover:bg-sidebar-accent',
          isActive && 'bg-sidebar-primary/15 text-sidebar-primary font-medium hover:bg-sidebar-primary/20',
          collapsed && 'justify-center px-2',
        )
      }
    >
      {({ isActive }) => (
        <>
          {isActive && <span className="nav-active-indicator" />}
          <Icon className={cn('h-4 w-4 shrink-0 transition-colors', isActive ? 'text-sidebar-primary' : 'group-hover:text-sidebar-foreground')} />
          {!collapsed && <span className="truncate animate-fade-in">{item.label}</span>}
          {!collapsed && item.badge && (
            <span className="ml-auto shrink-0 flex h-5 min-w-5 items-center justify-center rounded-full bg-sidebar-primary/20 text-xs font-medium text-sidebar-primary px-1">
              {item.badge}
            </span>
          )}
        </>
      )}
    </NavLink>
  );
}
