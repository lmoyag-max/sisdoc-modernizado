import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Users, Plus, Search, Pencil, Trash2, RefreshCw,
  Shield, ChevronLeft, ChevronRight, X, Save,
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';
import { useDebounce } from '@/hooks/useDebounce';

interface Usuario {
  idUsuario: number;
  usuario: string;
  nombres: string | null;
  apellidos: string | null;
  descDependencia: string | null;
  roles: string[];
}

interface Rol {
  id_rol: number;
  codigo: string;
  nombre: string;
}

const ROL_BADGE: Record<string, { label: string; className: string }> = {
  admin:       { label: 'Admin',        className: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400' },
  coordinador: { label: 'Coordinador',  className: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
  funcionario: { label: 'Funcionario',  className: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' },
};

function iniciales(nombre: string): string {
  return nombre.split(' ').slice(0, 2).map((n) => n[0] ?? '').join('').toUpperCase();
}

// ── Modal crear/editar ───────────────────────────────────────
interface ModalProps {
  usuario: Usuario | null;
  roles: Rol[];
  onClose: () => void;
  onSaved: () => void;
}

function UsuarioModal({ usuario, roles, onClose, onSaved }: ModalProps) {
  const isEdit = !!usuario;
  const [form, setForm] = useState({
    usuario: usuario?.usuario ?? '',
    clave: '',
    nombres: usuario?.nombres ?? '',
    apellidos: usuario?.apellidos ?? '',
    roles: usuario?.roles ?? ['funcionario'],
  });

  const mutation = useMutation({
    mutationFn: async () => {
      if (isEdit) {
        await apiClient.patch(`/usuarios/${usuario!.idUsuario}`, {
          nombres: form.nombres,
          apellidos: form.apellidos,
          roles: form.roles,
          ...(form.clave ? { clave: form.clave } : {}),
        });
      } else {
        await apiClient.post('/usuarios', form);
      }
    },
    onSuccess: () => {
      toast.success(isEdit ? 'Usuario actualizado' : 'Usuario creado correctamente');
      onSaved();
      onClose();
    },
    onError: (e: unknown) => {
      const msg = (e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al guardar';
      toast.error(msg);
    },
  });

  const toggleRol = (codigo: string) => {
    setForm((f) => ({
      ...f,
      roles: f.roles.includes(codigo)
        ? f.roles.filter((r) => r !== codigo)
        : [...f.roles, codigo],
    }));
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/50 backdrop-blur-sm" onClick={onClose} />
      <div className="relative w-full max-w-md bg-card border border-border rounded-2xl shadow-2xl animate-fade-in">
        <div className="flex items-center justify-between p-6 border-b border-border">
          <h2 className="text-lg font-semibold">{isEdit ? 'Editar usuario' : 'Nuevo usuario'}</h2>
          <Button variant="ghost" size="icon" onClick={onClose}><X className="h-4 w-4" /></Button>
        </div>
        <div className="p-6 space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-1.5">
              <Label>Nombres</Label>
              <Input value={form.nombres} onChange={(e) => setForm((f) => ({ ...f, nombres: e.target.value }))} placeholder="Nombres" />
            </div>
            <div className="space-y-1.5">
              <Label>Apellidos</Label>
              <Input value={form.apellidos} onChange={(e) => setForm((f) => ({ ...f, apellidos: e.target.value }))} placeholder="Apellidos" />
            </div>
          </div>
          <div className="space-y-1.5">
            <Label>Nombre de usuario</Label>
            <Input
              value={form.usuario}
              onChange={(e) => setForm((f) => ({ ...f, usuario: e.target.value.slice(0, 10) }))}
              placeholder="máx. 10 caracteres"
              disabled={isEdit}
              maxLength={10}
            />
          </div>
          <div className="space-y-1.5">
            <Label>{isEdit ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña'}</Label>
            <Input
              type="password"
              value={form.clave}
              onChange={(e) => setForm((f) => ({ ...f, clave: e.target.value.slice(0, 10) }))}
              placeholder={isEdit ? 'Sin cambios' : 'máx. 10 caracteres'}
              maxLength={10}
            />
          </div>
          <div className="space-y-2">
            <Label>Roles</Label>
            <div className="flex flex-wrap gap-2">
              {roles.map((r) => {
                const sel = form.roles.includes(r.codigo);
                return (
                  <button
                    key={r.codigo}
                    type="button"
                    onClick={() => toggleRol(r.codigo)}
                    className={cn(
                      'px-3 py-1 rounded-full text-xs font-medium border transition-all',
                      sel ? 'bg-primary text-primary-foreground border-primary' : 'bg-muted text-muted-foreground border-border hover:border-primary/50'
                    )}
                  >
                    {r.nombre}
                  </button>
                );
              })}
            </div>
          </div>
        </div>
        <div className="flex justify-end gap-2 px-6 pb-6">
          <Button variant="outline" onClick={onClose}>Cancelar</Button>
          <Button onClick={() => mutation.mutate()} disabled={mutation.isPending} className="gap-2">
            {mutation.isPending ? <RefreshCw className="h-3.5 w-3.5 animate-spin" /> : <Save className="h-3.5 w-3.5" />}
            {isEdit ? 'Guardar cambios' : 'Crear usuario'}
          </Button>
        </div>
      </div>
    </div>
  );
}

// ── Página principal ─────────────────────────────────────────
export function UsuariosPage() {
  const [pagina, setPagina] = useState(1);
  const [search, setSearch] = useState('');
  const [modal, setModal] = useState<'nuevo' | Usuario | null>(null);
  const debouncedSearch = useDebounce(search, 300);
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['usuarios', pagina, debouncedSearch],
    queryFn: async () => {
      const params = new URLSearchParams({ pagina: String(pagina), porPagina: '15' });
      if (debouncedSearch) params.set('q', debouncedSearch);
      const { data } = await apiClient.get<{ data: Usuario[]; meta: { total: number; totalPaginas: number; pagina: number } }>(
        `/usuarios?${params}`
      );
      return data;
    },
  });

  const { data: roles } = useQuery({
    queryKey: ['roles'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: Rol[] }>('/usuarios/meta/roles');
      return data.data;
    },
    staleTime: Infinity,
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => apiClient.delete(`/usuarios/${id}`),
    onSuccess: () => { qc.invalidateQueries({ queryKey: ['usuarios'] }); toast.success('Usuario eliminado'); },
    onError: () => toast.error('No se pudo eliminar el usuario'),
  });

  const usuarios = data?.data ?? [];
  const meta = data?.meta;

  const handleSaved = () => qc.invalidateQueries({ queryKey: ['usuarios'] });

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
            <Users className="h-5 w-5 text-primary" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-foreground">Usuarios</h1>
            <p className="text-sm text-muted-foreground">
              {meta ? `${meta.total} usuario${meta.total !== 1 ? 's' : ''} en el sistema` : 'Cargando...'}
            </p>
          </div>
        </div>
        <Button onClick={() => setModal('nuevo')} className="gap-2">
          <Plus className="h-4 w-4" />
          Nuevo usuario
        </Button>
      </div>

      {/* Búsqueda */}
      <div className="relative max-w-sm">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          value={search}
          onChange={(e) => { setSearch(e.target.value); setPagina(1); }}
          placeholder="Buscar por usuario o nombre..."
          className="pl-9"
        />
      </div>

      {/* Tabla */}
      <Card>
        <CardContent className="p-0">
          {isLoading ? (
            <div className="divide-y">
              {Array.from({ length: 5 }).map((_, i) => (
                <div key={i} className="flex items-center gap-4 px-6 py-4">
                  <Skeleton className="h-9 w-9 rounded-full" />
                  <div className="flex-1 space-y-1.5">
                    <Skeleton className="h-3.5 w-40" />
                    <Skeleton className="h-3 w-24" />
                  </div>
                  <Skeleton className="h-6 w-20 rounded-full" />
                </div>
              ))}
            </div>
          ) : usuarios.length === 0 ? (
            <div className="py-16 text-center text-muted-foreground text-sm">
              {search ? 'No se encontraron usuarios con ese criterio.' : 'No hay usuarios registrados.'}
            </div>
          ) : (
            <div className="divide-y">
              {usuarios.map((u) => {
                const nombreCompleto = [u.nombres, u.apellidos].filter(Boolean).join(' ') || u.usuario;
                return (
                  <div key={u.idUsuario} className="flex items-center gap-4 px-6 py-4 hover:bg-muted/30 transition-colors">
                    <Avatar className="h-9 w-9 shrink-0">
                      <AvatarFallback className="bg-primary/10 text-primary text-xs font-semibold">
                        {iniciales(nombreCompleto)}
                      </AvatarFallback>
                    </Avatar>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-semibold text-foreground truncate">{nombreCompleto}</p>
                      <div className="flex items-center gap-2 text-xs text-muted-foreground mt-0.5">
                        <span className="font-mono">@{u.usuario}</span>
                        {u.descDependencia && (
                          <><span>·</span><span className="truncate">{u.descDependencia}</span></>
                        )}
                      </div>
                    </div>
                    <div className="flex items-center gap-1.5 shrink-0">
                      {u.roles.slice(0, 2).map((rol) => {
                        const cfg = ROL_BADGE[rol] ?? { label: rol, className: 'bg-muted text-muted-foreground' };
                        return (
                          <span key={rol} className={cn('px-2 py-0.5 rounded-full text-[11px] font-medium', cfg.className)}>
                            {cfg.label}
                          </span>
                        );
                      })}
                    </div>
                    <div className="flex items-center gap-1 shrink-0">
                      <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => setModal(u)} title="Editar">
                        <Pencil className="h-3.5 w-3.5" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                        onClick={() => {
                          if (confirm(`¿Eliminar usuario "${u.usuario}"?`)) deleteMutation.mutate(u.idUsuario);
                        }}
                        title="Eliminar"
                      >
                        <Trash2 className="h-3.5 w-3.5" />
                      </Button>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </CardContent>

        {/* Paginación */}
        {meta && meta.totalPaginas > 1 && (
          <div className="flex items-center justify-between px-6 py-4 border-t">
            <p className="text-xs text-muted-foreground">
              Página {meta.pagina} de {meta.totalPaginas} · {meta.total} usuarios
            </p>
            <div className="flex gap-2">
              <Button variant="outline" size="icon" className="h-8 w-8" disabled={pagina <= 1} onClick={() => setPagina((p) => p - 1)}>
                <ChevronLeft className="h-4 w-4" />
              </Button>
              <Button variant="outline" size="icon" className="h-8 w-8" disabled={pagina >= meta.totalPaginas} onClick={() => setPagina((p) => p + 1)}>
                <ChevronRight className="h-4 w-4" />
              </Button>
            </div>
          </div>
        )}
      </Card>

      {/* Modal */}
      {modal !== null && (
        <UsuarioModal
          usuario={modal === 'nuevo' ? null : modal}
          roles={roles ?? []}
          onClose={() => setModal(null)}
          onSaved={handleSaved}
        />
      )}
    </div>
  );
}
