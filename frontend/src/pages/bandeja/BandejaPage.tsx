import { useState } from 'react';
import { useQuery, useMutation, useQueryClient, keepPreviousData } from '@tanstack/react-query';
import { Inbox, CheckCircle, Clock, RefreshCw, ChevronLeft, ChevronRight, FileText, Building2, AlertCircle } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { useAuthStore } from '@/stores/auth.store';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { EmptyState } from '@/components/shared/EmptyState';
import { formatRelativo } from '@/lib/utils';
import { cn } from '@/lib/utils';
import { toast } from 'sonner';

interface TramiteEntrada {
  id_seguimiento:      number;
  id_documento:        number | null;
  materia:             string | null;
  num_interno:         string | null;
  desc_tipo_documento: string | null;
  id_estado_tramite:   number | null;
  desc_estado_tramite: string | null;
  desc_procedencia:    string | null;
  desc_destino:        string | null;
  fecha_sistema:       string | null;
  observaciones:       string | null;
  total:               number;
}

const ESTADO_CONFIG: Record<number, { label: string; variant: 'warning' | 'info' | 'success' | 'secondary' }> = {
  1: { label: 'Generado',     variant: 'secondary' },
  2: { label: 'Por recibir',  variant: 'warning'   },
  3: { label: 'Recepcionado', variant: 'success'   },
  4: { label: 'Derivado',     variant: 'info'      },
  5: { label: 'Cerrado',      variant: 'success'   },
};

const FILTROS = [
  { label: 'Todos',         value: null },
  { label: 'Por recibir',   value: 2 },
  { label: 'Recepcionados', value: 3 },
  { label: 'Cerrados',      value: 5 },
] as const;

export function BandejaPage() {
  const [pagina, setPagina]       = useState(1);
  const [filtroEstado, setFiltro] = useState<number | null>(null);
  const user                      = useAuthStore((s) => s.user);
  const qc                        = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['bandeja', pagina, filtroEstado],
    queryFn: async () => {
      const params = new URLSearchParams({ pagina: String(pagina), porPagina: '20' });
      if (filtroEstado) params.set('idEstado', String(filtroEstado));
      const { data } = await apiClient.get<{
        data: TramiteEntrada[];
        meta: { total: number; pagina: number; totalPaginas: number; porPagina: number };
      }>(`/tramites?${params}`);
      return data;
    },
    placeholderData: keepPreviousData,
    staleTime:       15_000,
    refetchInterval: 30_000,
  });

  const recibirMutation = useMutation({
    mutationFn: (id: number) => apiClient.patch(`/tramites/${id}/recibir`),
    onSuccess: () => { qc.invalidateQueries({ queryKey: ['bandeja'] }); toast.success('Trámite recibido correctamente'); },
    onError:   () => toast.error('No se pudo recibir el trámite'),
  });

  const tramites    = data?.data ?? [];
  const meta        = data?.meta;
  const pendientes  = tramites.filter((t) => t.id_estado_tramite === 2).length;
  const recepcionados = tramites.filter((t) => t.id_estado_tramite === 3).length;
  const cerrados    = tramites.filter((t) => t.id_estado_tramite === 5).length;

  const handleFiltro = (v: number | null) => { setFiltro(v); setPagina(1); };

  return (
    <div className="space-y-6 animate-fade-in">

      {/* Header */}
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-foreground flex items-center gap-2">
            <Inbox className="h-5 w-5 sm:h-6 sm:w-6 text-primary" />
            Bandeja de Entrada
            {pendientes > 0 && (
              <span className="flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white px-1.5 animate-critical">
                {pendientes}
              </span>
            )}
          </h1>
          <p className="text-sm text-muted-foreground mt-0.5">
            {meta ? `${meta.total} documento${meta.total !== 1 ? 's' : ''} en bandeja` : 'Cargando...'}
          </p>
          {user?.descDependencia && (
            <div className="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground">
              <Building2 className="h-3 w-3" />
              Servicio: <span className="font-medium text-foreground">{user.descDependencia}</span>
            </div>
          )}
        </div>
        <Button variant="outline" size="sm" onClick={() => qc.invalidateQueries({ queryKey: ['bandeja'] })} className="gap-2 shrink-0">
          <RefreshCw className="h-4 w-4" />
          Actualizar
        </Button>
      </div>

      {/* Stats bar */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {[
          { label: 'Total',         value: meta?.total ?? '—', color: 'text-foreground',                icon: Inbox },
          { label: 'Por recibir',   value: isLoading ? '—' : pendientes,   color: 'text-amber-600',   icon: AlertCircle },
          { label: 'Recepcionados', value: isLoading ? '—' : recepcionados, color: 'text-sky-600',    icon: CheckCircle },
          { label: 'Cerrados',      value: isLoading ? '—' : cerrados,      color: 'text-emerald-600', icon: CheckCircle },
        ].map(({ label, value, color, icon: Icon }) => (
          <div key={label} className="rounded-xl border bg-card px-4 py-3 card-executive">
            <div className="flex items-center gap-2 mb-1">
              <Icon className={cn('h-3.5 w-3.5', color)} />
              <p className="text-xs text-muted-foreground">{label}</p>
            </div>
            <p className={cn('text-2xl font-bold tabular-nums animate-number', color)}>{value}</p>
          </div>
        ))}
      </div>

      {/* Filtros rápidos */}
      <div className="flex gap-2 flex-wrap">
        {FILTROS.map(({ label, value }) => (
          <Button
            key={label}
            variant={filtroEstado === value ? 'default' : 'outline'}
            size="sm"
            onClick={() => handleFiltro(value)}
            className="h-8 text-xs"
          >
            {label}
            {value === 2 && pendientes > 0 && (
              <span className="ml-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500/20 text-amber-700 dark:bg-amber-400/20 dark:text-amber-400 text-[9px] font-bold px-1">
                {pendientes}
              </span>
            )}
          </Button>
        ))}
      </div>

      {/* Lista */}
      <Card>
        <CardContent className="p-0">
          {isLoading ? (
            <div className="divide-y">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="flex items-start gap-4 px-6 py-5">
                  <Skeleton className="h-10 w-10 rounded-xl shrink-0" />
                  <div className="flex-1 space-y-2">
                    <Skeleton className="h-4 w-3/4" />
                    <Skeleton className="h-3 w-1/2" />
                  </div>
                  <Skeleton className="h-6 w-24 rounded-full" />
                </div>
              ))}
            </div>
          ) : tramites.length === 0 ? (
            <EmptyState
              icon={Inbox}
              title="Bandeja vacía"
              description={filtroEstado ? 'No hay trámites con ese estado.' : 'No tienes trámites en tu bandeja.'}
            />
          ) : (
            <div className="divide-y">
              {tramites.map((t) => {
                const estadoConf  = ESTADO_CONFIG[t.id_estado_tramite ?? 0];
                const isPendiente = t.id_estado_tramite === 2;
                const isCerrado   = t.id_estado_tramite === 5 || t.id_estado_tramite === 3;

                return (
                  <div
                    key={t.id_seguimiento}
                    className={cn(
                      'flex items-start gap-3 px-4 sm:px-6 py-4 sm:py-5 hover:bg-muted/30 transition-colors',
                      isPendiente && 'border-l-pending bg-amber-50/30 dark:bg-amber-950/10',
                      isCerrado   && 'border-l-done',
                    )}
                  >
                    {/* Icono de estado */}
                    <div className={cn(
                      'flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-xl',
                      isPendiente ? 'bg-amber-100 dark:bg-amber-900/30' :
                      isCerrado   ? 'bg-emerald-100 dark:bg-emerald-900/30' :
                      'bg-muted',
                    )}>
                      {isPendiente
                        ? <AlertCircle className="h-4 w-4 sm:h-5 sm:w-5 text-amber-600" />
                        : isCerrado
                          ? <CheckCircle className="h-4 w-4 sm:h-5 sm:w-5 text-emerald-600" />
                          : <FileText className="h-4 w-4 sm:h-5 sm:w-5 text-muted-foreground" />
                      }
                    </div>

                    {/* Contenido */}
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-semibold text-foreground line-clamp-2">
                        {t.materia ?? 'Sin materia'}
                      </p>
                      <div className="flex items-center gap-2 mt-1 flex-wrap text-xs text-muted-foreground">
                        {t.num_interno && <span className="font-mono bg-muted px-1.5 py-0.5 rounded text-[10px]">N° {t.num_interno}</span>}
                        {t.desc_tipo_documento && <span className="hidden sm:inline">{t.desc_tipo_documento}</span>}
                        <span className="flex items-center gap-1">
                          <Clock className="h-3 w-3" />
                          {formatRelativo(t.fecha_sistema)}
                        </span>
                      </div>
                      {(t.desc_procedencia || t.desc_destino) && (
                        <div className="flex items-center gap-1 mt-1 text-xs text-muted-foreground">
                          {t.desc_procedencia && <span className="truncate max-w-[120px] hidden sm:block">{t.desc_procedencia}</span>}
                          {t.desc_procedencia && t.desc_destino && <span className="hidden sm:block">→</span>}
                          {t.desc_destino && <span className="font-medium text-foreground truncate max-w-[160px]">{t.desc_destino}</span>}
                        </div>
                      )}
                      {/* Botón recibir mobile */}
                      {isPendiente && (
                        <div className="mt-2 sm:hidden">
                          <Button size="sm" variant="outline" className="h-8 text-xs gap-1.5 px-3"
                            loading={recibirMutation.isPending} onClick={() => recibirMutation.mutate(t.id_seguimiento)}>
                            <CheckCircle className="h-3.5 w-3.5" />
                            Recibir
                          </Button>
                        </div>
                      )}
                    </div>

                    {/* Badge + botón desktop */}
                    <div className="flex items-center gap-2 shrink-0">
                      {estadoConf && !isPendiente && <Badge variant={estadoConf.variant}>{estadoConf.label}</Badge>}
                      {isPendiente && (
                        <Button size="sm" variant="outline"
                          className="h-8 text-xs gap-1.5 px-3 hidden sm:flex border-amber-300 text-amber-700 hover:bg-amber-50 dark:border-amber-800 dark:text-amber-400 dark:hover:bg-amber-950/30"
                          loading={recibirMutation.isPending}
                          onClick={() => recibirMutation.mutate(t.id_seguimiento)}
                        >
                          <CheckCircle className="h-3.5 w-3.5" />
                          Recibir
                        </Button>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </CardContent>

        {/* Paginación */}
        {meta && meta.totalPaginas > 1 && (
          <div className="flex items-center justify-between px-6 py-4 border-t bg-muted/10">
            <p className="text-xs text-muted-foreground">
              Página {meta.pagina} de {meta.totalPaginas} · {meta.total} documentos
            </p>
            <div className="flex gap-2">
              <Button variant="outline" size="icon" className="h-8 w-8" disabled={pagina <= 1} onClick={() => setPagina((p) => p - 1)} aria-label="Página anterior"><ChevronLeft className="h-4 w-4" /></Button>
              <Button variant="outline" size="icon" className="h-8 w-8" disabled={pagina >= meta.totalPaginas} onClick={() => setPagina((p) => p + 1)} aria-label="Página siguiente"><ChevronRight className="h-4 w-4" /></Button>
            </div>
          </div>
        )}
      </Card>
    </div>
  );
}
