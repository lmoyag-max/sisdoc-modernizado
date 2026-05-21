import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Shield, Plus, Pencil, X, Save, RefreshCw, ChevronDown, ChevronUp } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';

interface ModuloInfo { codigo: string; label: string; grupo: 'operativo' | 'admin' }
interface Rol {
  id_rol: number;
  codigo: string;
  nombre: string;
  activo: boolean;
  modulos: string[];
  todosModulos: string[];
}

const GRUPO_LABEL: Record<string, string> = { operativo: 'Módulos operativos', admin: 'Módulos administrativos' };
const GRUPO_COLOR: Record<string, string> = {
  operativo: 'bg-blue-50 border-blue-200 dark:bg-blue-900/10 dark:border-blue-800/30',
  admin:     'bg-violet-50 border-violet-200 dark:bg-violet-900/10 dark:border-violet-800/30',
};

interface ModalProps {
  rol: Rol | null;
  modulos: ModuloInfo[];
  onClose: () => void;
  onSaved: () => void;
}

function RolModal({ rol, modulos, onClose, onSaved }: ModalProps) {
  const isEdit = !!rol;
  const [nombre,  setNombre]  = useState(rol?.nombre  ?? '');
  const [codigo,  setCodigo]  = useState(rol?.codigo  ?? '');
  const [selects, setSelects] = useState<string[]>(rol?.modulos ?? []);

  const mutation = useMutation({
    mutationFn: async () => {
      if (isEdit) {
        await apiClient.patch(`/roles/${rol!.id_rol}`, { nombre, modulos: selects });
      } else {
        await apiClient.post('/roles', { codigo, nombre, modulos: selects });
      }
    },
    onSuccess: () => {
      toast.success(isEdit ? 'Rol actualizado' : 'Rol creado');
      onSaved(); onClose();
    },
    onError: (e: unknown) => {
      toast.error((e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al guardar');
    },
  });

  const toggle = (codigo: string) => setSelects((s) =>
    s.includes(codigo) ? s.filter((x) => x !== codigo) : [...s, codigo]
  );

  const grupos = ['operativo', 'admin'] as const;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 bg-black/50 backdrop-blur-sm" onClick={onClose} />
      <div className="relative w-full max-w-lg bg-card border border-border rounded-2xl shadow-2xl flex flex-col max-h-[90vh] animate-fade-in">

        <div className="flex items-center justify-between px-6 py-4 border-b border-border shrink-0">
          <h2 className="text-base font-semibold">{isEdit ? 'Editar rol' : 'Nuevo rol'}</h2>
          <Button variant="ghost" size="icon" onClick={onClose}><X className="h-4 w-4" /></Button>
        </div>

        <div className="overflow-y-auto flex-1 p-6 space-y-5">
          {/* Nombre */}
          <div className="space-y-1.5">
            <Label>Nombre del rol *</Label>
            <Input value={nombre} onChange={(e) => setNombre(e.target.value)} placeholder="Ej: Supervisor clínico" />
          </div>

          {/* Código — solo en creación */}
          {!isEdit && (
            <div className="space-y-1.5">
              <Label>Código interno *</Label>
              <Input
                value={codigo}
                onChange={(e) => setCodigo(e.target.value.toLowerCase().replace(/\s/g, '_'))}
                placeholder="Ej: supervisor_clinico"
              />
              <p className="text-xs text-muted-foreground">Sin espacios, en minúsculas. No puede cambiarse después.</p>
            </div>
          )}

          {/* Módulos por grupo */}
          <div className="space-y-3">
            <Label>Módulos visibles</Label>
            {grupos.map((grupo) => {
              const items = modulos.filter((m) => m.grupo === grupo);
              const todosEnGrupo = items.every((m) => selects.includes(m.codigo));
              return (
                <div key={grupo} className={cn('rounded-lg border p-3 space-y-2', GRUPO_COLOR[grupo])}>
                  <div className="flex items-center justify-between">
                    <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                      {GRUPO_LABEL[grupo]}
                    </p>
                    <button
                      type="button"
                      onClick={() => {
                        const codigos = items.map((m) => m.codigo);
                        if (todosEnGrupo) setSelects((s) => s.filter((x) => !codigos.includes(x)));
                        else setSelects((s) => [...new Set([...s, ...codigos])]);
                      }}
                      className="text-xs text-primary hover:underline"
                    >
                      {todosEnGrupo ? 'Quitar todos' : 'Seleccionar todos'}
                    </button>
                  </div>
                  <div className="grid grid-cols-2 gap-1.5">
                    {items.map((m) => (
                      <label
                        key={m.codigo}
                        className={cn(
                          'flex items-center gap-2 px-2.5 py-1.5 rounded-lg cursor-pointer text-sm transition-colors border',
                          selects.includes(m.codigo)
                            ? 'bg-primary text-primary-foreground border-primary'
                            : 'bg-background text-foreground border-border hover:border-primary/40'
                        )}
                      >
                        <input
                          type="checkbox"
                          checked={selects.includes(m.codigo)}
                          onChange={() => toggle(m.codigo)}
                          className="sr-only"
                        />
                        {m.label}
                      </label>
                    ))}
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        <div className="flex justify-end gap-2 px-6 py-4 border-t border-border shrink-0">
          <Button variant="outline" onClick={onClose}>Cancelar</Button>
          <Button onClick={() => mutation.mutate()} disabled={mutation.isPending || !nombre} className="gap-2">
            {mutation.isPending ? <RefreshCw className="h-3.5 w-3.5 animate-spin" /> : <Save className="h-3.5 w-3.5" />}
            {isEdit ? 'Guardar cambios' : 'Crear rol'}
          </Button>
        </div>
      </div>
    </div>
  );
}

// ── Página ───────────────────────────────────────────────────

export function RolesPage() {
  const [modal, setModal] = useState<'nuevo' | Rol | null>(null);
  const [expandido, setExpandido] = useState<number | null>(null);
  const qc = useQueryClient();

  const { data: roles, isLoading } = useQuery({
    queryKey: ['roles'],
    queryFn: async () => (await apiClient.get<{ data: Rol[] }>('/roles')).data.data,
  });

  const { data: modulosMeta } = useQuery({
    queryKey: ['roles-modulos-meta'],
    queryFn: async () => (await apiClient.get<{ data: ModuloInfo[] }>('/roles/meta/modulos')).data.data,
    staleTime: Infinity,
  });

  const desactivarMut = useMutation({
    mutationFn: (id: number) => apiClient.delete(`/roles/${id}`),
    onSuccess: () => { qc.invalidateQueries({ queryKey: ['roles'] }); toast.success('Rol actualizado'); },
    onError: () => toast.error('No se pudo actualizar el rol'),
  });

  const handleSaved = () => qc.invalidateQueries({ queryKey: ['roles'] });
  const MODULOS_META = modulosMeta ?? [];

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
            <Shield className="h-5 w-5 text-primary" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-foreground">Roles y Permisos</h1>
            <p className="text-sm text-muted-foreground">Configura qué módulos puede ver cada rol</p>
          </div>
        </div>
        <Button onClick={() => setModal('nuevo')} className="gap-2">
          <Plus className="h-4 w-4" />Nuevo rol
        </Button>
      </div>

      {/* Lista de roles */}
      <Card>
        <CardContent className="p-0">
          {isLoading ? (
            <div className="divide-y">
              {[1,2,3,4].map((i) => (
                <div key={i} className="flex items-center gap-4 px-6 py-5">
                  <Skeleton className="h-9 w-9 rounded-xl" />
                  <div className="flex-1 space-y-1.5"><Skeleton className="h-4 w-32" /><Skeleton className="h-3 w-48" /></div>
                </div>
              ))}
            </div>
          ) : (roles ?? []).length === 0 ? (
            <div className="py-12 text-center text-muted-foreground text-sm">No hay roles.</div>
          ) : (
            <div className="divide-y">
              {(roles ?? []).map((rol) => {
                const isExpanded = expandido === rol.id_rol;
                const modulosOp  = rol.modulos.filter((m) => MODULOS_META.find((x) => x.codigo === m)?.grupo === 'operativo');
                const modulosAdm = rol.modulos.filter((m) => MODULOS_META.find((x) => x.codigo === m)?.grupo === 'admin');

                return (
                  <div key={rol.id_rol} className="px-6 py-4">
                    <div className="flex items-center gap-4">
                      <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                        <Shield className="h-4 w-4 text-primary" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2">
                          <p className="text-sm font-semibold text-foreground">{rol.nombre}</p>
                          <span className="text-xs text-muted-foreground font-mono bg-muted px-1.5 py-0.5 rounded">{rol.codigo}</span>
                          {!rol.activo && <Badge variant="secondary" className="text-[10px]">Inactivo</Badge>}
                        </div>
                        <p className="text-xs text-muted-foreground mt-0.5">
                          {rol.modulos.length} módulo{rol.modulos.length !== 1 ? 's' : ''} asignado{rol.modulos.length !== 1 ? 's' : ''}
                        </p>
                      </div>
                      <div className="flex items-center gap-2 shrink-0">
                        <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => setModal(rol)} title="Editar">
                          <Pencil className="h-3.5 w-3.5" />
                        </Button>
                        {rol.codigo !== 'admin' && (
                          <Button
                            variant="ghost" size="icon" className="h-8 w-8 text-destructive/70 hover:bg-destructive/10"
                            onClick={() => { if (confirm(`¿${rol.activo ? 'Desactivar' : 'Eliminar'} rol "${rol.nombre}"?`)) desactivarMut.mutate(rol.id_rol); }}
                            title={rol.activo ? 'Desactivar' : 'Eliminar'}
                          >
                            <X className="h-3.5 w-3.5" />
                          </Button>
                        )}
                        <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => setExpandido(isExpanded ? null : rol.id_rol)}>
                          {isExpanded ? <ChevronUp className="h-3.5 w-3.5" /> : <ChevronDown className="h-3.5 w-3.5" />}
                        </Button>
                      </div>
                    </div>

                    {/* Módulos expandidos */}
                    {isExpanded && (
                      <div className="mt-3 ml-13 space-y-2 pl-13">
                        {[{ label: 'Operativos', items: modulosOp }, { label: 'Administración', items: modulosAdm }].map(({ label, items }) =>
                          items.length > 0 ? (
                            <div key={label}>
                              <p className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground mb-1">{label}</p>
                              <div className="flex flex-wrap gap-1.5">
                                {items.map((m) => {
                                  const meta = MODULOS_META.find((x) => x.codigo === m);
                                  return (
                                    <span key={m} className={cn(
                                      'text-[11px] px-2 py-0.5 rounded-full font-medium',
                                      meta?.grupo === 'admin'
                                        ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400'
                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                    )}>
                                      {meta?.label ?? m}
                                    </span>
                                  );
                                })}
                              </div>
                            </div>
                          ) : null
                        )}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Modal */}
      {modal !== null && (
        <RolModal
          rol={modal === 'nuevo' ? null : modal}
          modulos={MODULOS_META}
          onClose={() => setModal(null)}
          onSaved={handleSaved}
        />
      )}
    </div>
  );
}
