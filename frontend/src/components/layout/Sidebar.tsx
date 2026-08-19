import { useState, useEffect } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import {
  LayoutDashboard, FileText, Inbox, Send, ClipboardList,
  Network, Search, Upload, Users, BarChart3,
  Settings, LogOut, ChevronLeft, ChevronRight, X,
  Building2, Shield, Bell, UserCheck, PenLine, BookOpen,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { useAuthStore, displayName } from '@/stores/auth.store';
import { authApi } from '@/lib/api/auth.api';
import { apiClient } from '@/lib/api/client';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';
import { useModulos } from '@/hooks/useModulos';
import { useRole } from '@/hooks/useRole';

interface NavItem {
  label:  string;
  to:     string;
  icon:   React.ComponentType<{ className?: string }>;
  modulo: string;
}

/* Identidad visual por módulo — icono 3D con color propio (ver plan de diseño) */
const ACCENT_MODULO: Record<string, string> = {
  dashboard:      'indigo',
  documentos:     'indigo',
  bandeja:        'amber',
  enviados:       'sky',
  tramites:       'violet',
  trazabilidad:   'violet',
  busqueda:       'sky',
  archivos:       'emerald',
  'libro-referencias': 'sky',
  usuarios:       'amber',
  reportes:       'emerald',
  roles:          'violet',
  alertas:        'red',
  jefaturas:      'indigo',
  'firma-gob':    'slate',
  configuracion:  'slate',
};

const NAV_PRINCIPAL: NavItem[] = [
  { label: 'Dashboard',       to: '/dashboard',    icon: LayoutDashboard, modulo: 'dashboard'    },
  { label: 'Documentos',      to: '/documentos',   icon: FileText,        modulo: 'documentos'   },
  { label: 'Bandeja entrada', to: '/bandeja',      icon: Inbox,           modulo: 'bandeja'      },
  { label: 'Enviados',        to: '/enviados',     icon: Send,            modulo: 'enviados'     },
  { label: 'Mis Trámites',    to: '/tramites',     icon: ClipboardList,   modulo: 'tramites'     },
  { label: 'Trazabilidad',    to: '/trazabilidad', icon: Network,         modulo: 'trazabilidad' },
  { label: 'Búsqueda',        to: '/busqueda',     icon: Search,          modulo: 'busqueda'     },
  { label: 'Archivos',        to: '/archivos',     icon: Upload,          modulo: 'archivos'     },
  { label: 'Libro de Referencias', to: '/libro-referencias', icon: BookOpen, modulo: 'libro-referencias' },
];

const NAV_ADMIN: NavItem[] = [
  { label: 'Usuarios',      to: '/admin/usuarios',      icon: Users,      modulo: 'usuarios'     },
  { label: 'Reportes',      to: '/reportes',            icon: BarChart3,  modulo: 'reportes'     },
  { label: 'Roles',         to: '/admin/roles',         icon: Shield,     modulo: 'roles'        },
  { label: 'Alertas',       to: '/admin/alertas',       icon: Bell,       modulo: 'alertas'      },
  { label: 'Jefaturas',     to: '/admin/jefaturas',     icon: UserCheck,  modulo: 'jefaturas'    },
  { label: 'Firma.gob',     to: '/admin/firma-gob',     icon: PenLine,    modulo: 'firma-gob'    },
  { label: 'Configuración', to: '/admin/configuracion', icon: Settings,   modulo: 'configuracion'},
];

function iniciales(nombre: string): string {
  return nombre.split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase();
}

interface SidebarProps {
  mobileOpen?: boolean;
  onMobileClose?: () => void;
}

export function Sidebar({ mobileOpen = false, onMobileClose }: SidebarProps) {
  const [collapsed, setCollapsed] = useState(false);
  const user     = useAuthStore((s) => s.user);
  const logout   = useAuthStore((s) => s.logout);
  const navigate = useNavigate();
  const { puede, isAdmin } = useModulos();

  const nombre   = displayName(user);
  const initials = iniciales(nombre || 'US');
  const { isOfPartes, isSupervisor } = useRole();
  const rolLabel = isAdmin ? 'Administrador' : isOfPartes ? 'Of. Partes' : isSupervisor ? 'Supervisor' : 'Funcionario';

  const handleLogout = async () => {
    try { await authApi.logout(); } catch { /* silencioso */ }
    logout();
    navigate('/login');
    toast.success('Sesión cerrada');
  };

  const itemsPrincipal = NAV_PRINCIPAL.filter((item) => puede(item.modulo));
  const itemsAdmin     = NAV_ADMIN.filter((item) => puede(item.modulo));
  const mostrarAdmin   = isAdmin || itemsAdmin.length > 0;

  // Contador "por recibir" para el badge del ítem Bandeja entrada (solo lectura, mismo endpoint que BandejaPage)
  const { data: pendientesBandeja } = useQuery({
    queryKey: ['sidebar-pendientes-bandeja'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ meta: { total: number } }>('/tramites', {
        params: { idEstado: 2, pagina: 1, porPagina: 1 },
      });
      return data.meta?.total ?? 0;
    },
    enabled: puede('bandeja'),
    staleTime: 30_000,
    refetchInterval: 60_000,
  });

  const badgeFor = (modulo: string) => (modulo === 'bandeja' ? pendientesBandeja : undefined);

  // Escape key + body scroll lock cuando el drawer está abierto
  useEffect(() => {
    if (!mobileOpen) return;
    document.body.style.overflow = 'hidden';
    const onKey = (e: KeyboardEvent) => { if (e.key === 'Escape') onMobileClose?.(); };
    document.addEventListener('keydown', onKey);
    return () => {
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = '';
    };
  }, [mobileOpen, onMobileClose]);

  // Cierra el drawer al navegar en mobile
  const handleMobileNav = () => onMobileClose?.();

  return (
    <>
      {/* ── Sidebar desktop (lg+) ────────────────────────────── */}
      <aside className={cn(
        'hidden lg:relative lg:flex flex-col h-screen sidebar-gradient text-sidebar-foreground border-r border-sidebar-border transition-all duration-300 ease-in-out shrink-0',
        collapsed ? 'w-16' : 'w-64',
      )}>
        {/* Logo */}
        <div className={cn('flex items-center h-16 px-4 border-b border-sidebar-border shrink-0', collapsed && 'justify-center px-2')}>
          <div className="flex items-center gap-3">
            <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground font-bold text-xs shadow">
              <Building2 className="h-4 w-4" />
            </div>
            {!collapsed && (
              <div className="animate-fade-in min-w-0">
                <p className="text-sm font-semibold text-sidebar-foreground leading-tight">DOC360</p>
                <p className="text-xs text-sidebar-foreground/50 leading-tight">HUAP · Gestión Documental</p>
              </div>
            )}
          </div>
        </div>

        {/* Botón colapsar */}
        <button
          onClick={() => setCollapsed(!collapsed)}
          type="button"
          className="absolute -right-3 top-[72px] z-20 flex h-7 w-7 items-center justify-center rounded-full border border-sidebar-border bg-sidebar text-sidebar-foreground shadow-sm hover:bg-sidebar-accent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-primary"
          aria-label={collapsed ? 'Expandir menú de navegación' : 'Colapsar menú de navegación'}
        >
          {collapsed ? <ChevronRight className="h-3 w-3" /> : <ChevronLeft className="h-3 w-3" />}
        </button>

        {/* Nav */}
        <nav className="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2 space-y-0.5 scrollbar-thin">
          {!collapsed && itemsPrincipal.length > 0 && (
            <p className="px-3 py-1 text-xs font-semibold uppercase tracking-widest text-sidebar-foreground/40 mb-1">
              Principal
            </p>
          )}
          {itemsPrincipal.map((item) => (
            <SidebarItem key={item.to} item={item} collapsed={collapsed} badge={badgeFor(item.modulo)} />
          ))}
          {mostrarAdmin && (
            <>
              <div className="py-2 px-1"><Separator className="bg-sidebar-border" /></div>
              {!collapsed && (
                <p className="px-3 py-1 text-xs font-semibold uppercase tracking-widest text-sidebar-foreground/40 mb-1">
                  Administración
                </p>
              )}
              {itemsAdmin.map((item) => (
                <SidebarItem key={item.to} item={item} collapsed={collapsed} badge={badgeFor(item.modulo)} />
              ))}
            </>
          )}
        </nav>

        {/* Perfil */}
        <div className="border-t border-sidebar-border p-2 shrink-0">
          <div className={cn('flex items-center gap-3 rounded-lg p-2 hover:bg-sidebar-accent transition-colors', collapsed && 'justify-center')}>
            <Avatar className="h-8 w-8 shrink-0">
              <AvatarFallback className="bg-primary/20 text-primary text-xs font-semibold">{initials}</AvatarFallback>
            </Avatar>
            {!collapsed && (
              <div className="flex-1 min-w-0 animate-fade-in">
                <p className="text-xs font-semibold truncate text-sidebar-foreground leading-tight">{nombre}</p>
                <p className="text-xs text-sidebar-foreground/50 truncate leading-tight">{rolLabel}</p>
              </div>
            )}
            {!collapsed && (
              <Button variant="ghost" size="icon"
                className="h-7 w-7 shrink-0 text-sidebar-foreground/50 hover:text-destructive hover:bg-sidebar-accent"
                onClick={handleLogout} title="Cerrar sesión"
              >
                <LogOut className="h-3.5 w-3.5" />
              </Button>
            )}
          </div>
          {collapsed && (
            <Button variant="ghost" size="icon"
              className="w-full h-10 mt-1 text-sidebar-foreground/40 hover:text-destructive hover:bg-sidebar-accent"
              onClick={handleLogout}
              aria-label="Cerrar sesión"
              title="Cerrar sesión"
            >
              <LogOut className="h-4 w-4" />
            </Button>
          )}
        </div>
      </aside>

      {/* ── Drawer mobile (< lg) ─────────────────────────────── */}
      {mobileOpen && (
        <>
          {/* Backdrop */}
          <div
            className="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden animate-fade-backdrop"
            onClick={onMobileClose}
            aria-hidden="true"
          />

          {/* Panel drawer */}
          <aside
            className="fixed inset-y-0 left-0 z-50 flex flex-col w-72 sidebar-gradient text-sidebar-foreground border-r border-sidebar-border lg:hidden animate-slide-in"
            role="dialog"
            aria-modal="true"
            aria-label="Menú de navegación"
          >
            {/* Logo + cierre */}
            <div className="flex items-center justify-between h-16 px-4 border-b border-sidebar-border shrink-0">
              <div className="flex items-center gap-3">
                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground font-bold text-xs shadow">
                  <Building2 className="h-4 w-4" />
                </div>
                <div className="min-w-0">
                  <p className="text-sm font-semibold text-sidebar-foreground leading-tight">DOC360</p>
                  <p className="text-xs text-sidebar-foreground/50 leading-tight">HUAP · Gestión Documental</p>
                </div>
              </div>
              <button
                onClick={onMobileClose}
                className="flex h-8 w-8 items-center justify-center rounded-lg text-sidebar-foreground/50 hover:text-sidebar-foreground hover:bg-sidebar-accent transition-colors"
                aria-label="Cerrar menú"
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            {/* Nav */}
            <nav className="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2 space-y-0.5 scrollbar-thin">
              {itemsPrincipal.length > 0 && (
                <p className="px-3 py-1 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/40 mb-1">
                  Principal
                </p>
              )}
              {itemsPrincipal.map((item) => (
                <SidebarItem key={item.to} item={item} collapsed={false} onClick={handleMobileNav} badge={badgeFor(item.modulo)} />
              ))}
              {mostrarAdmin && (
                <>
                  <div className="py-2 px-1"><Separator className="bg-sidebar-border" /></div>
                  <p className="px-3 py-1 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/40 mb-1">
                    Administración
                  </p>
                  {itemsAdmin.map((item) => (
                    <SidebarItem key={item.to} item={item} collapsed={false} onClick={handleMobileNav} badge={badgeFor(item.modulo)} />
                  ))}
                </>
              )}
            </nav>

            {/* Perfil */}
            <div className="border-t border-sidebar-border p-2 shrink-0">
              <div className="flex items-center gap-3 rounded-lg p-2 hover:bg-sidebar-accent transition-colors">
                <Avatar className="h-8 w-8 shrink-0">
                  <AvatarFallback className="bg-primary/20 text-primary text-xs font-semibold">{initials}</AvatarFallback>
                </Avatar>
                <div className="flex-1 min-w-0">
                  <p className="text-xs font-semibold truncate text-sidebar-foreground leading-tight">{nombre}</p>
                  <p className="text-xs text-sidebar-foreground/50 truncate leading-tight">{rolLabel}</p>
                </div>
                <Button variant="ghost" size="icon"
                  className="h-7 w-7 shrink-0 text-sidebar-foreground/50 hover:text-destructive hover:bg-sidebar-accent"
                  onClick={handleLogout} title="Cerrar sesión"
                >
                  <LogOut className="h-3.5 w-3.5" />
                </Button>
              </div>
            </div>
          </aside>
        </>
      )}
    </>
  );
}

function SidebarItem({
  item,
  collapsed,
  onClick,
  badge,
}: {
  item: NavItem;
  collapsed: boolean;
  onClick?: () => void;
  badge?: number;
}) {
  const Icon = item.icon;
  const accent = ACCENT_MODULO[item.modulo] ?? 'slate';
  const showBadge = typeof badge === 'number' && badge > 0;
  return (
    <NavLink
      to={item.to}
      title={collapsed ? item.label : undefined}
      onClick={onClick}
      className={({ isActive }) => cn(
        'relative flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm transition-all duration-150 group overflow-hidden',
        'text-sidebar-foreground/60 hover:text-sidebar-foreground hover:bg-sidebar-accent',
        isActive && 'bg-sidebar-primary/15 text-sidebar-primary font-medium hover:bg-sidebar-primary/20',
        collapsed && 'justify-center px-2',
      )}
    >
      {({ isActive }) => (
        <>
          {isActive && <span className="nav-active-indicator" />}
          <span className={cn('icon-3d-sm relative flex h-7 w-7 shrink-0 items-center justify-center', `icon-3d-${accent}`, 'group-hover:-translate-y-0.5')}>
            <Icon className="h-3.5 w-3.5 text-white" />
            {collapsed && showBadge && (
              <span className="absolute -right-0.5 -top-0.5 h-2 w-2 rounded-full bg-amber-500 ring-2 ring-sidebar-background" />
            )}
          </span>
          {!collapsed && <span className="truncate animate-fade-in flex-1">{item.label}</span>}
          {!collapsed && showBadge && (
            <span className="ml-auto inline-flex h-5 min-w-[20px] shrink-0 items-center justify-center rounded-full bg-amber-500 px-1.5 text-[10px] font-bold text-white animate-fade-in">
              {badge! > 99 ? '99+' : badge}
            </span>
          )}
        </>
      )}
    </NavLink>
  );
}
