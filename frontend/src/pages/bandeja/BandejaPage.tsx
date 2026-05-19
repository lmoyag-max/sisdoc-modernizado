import { useState } from 'react';
import { useQuery, useMutation, useQueryClient, keepPreviousData } from '@tanstack/react-query';
import { Inbox, CheckCircle, Clock, RefreshCw, ChevronLeft, ChevronRight, FileText } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { useAuthStore } from '@/stores/auth.store';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { EmptyState } from '@/components/shared/EmptyState';
import { formatRelativo, truncate } from '@/lib/utils';
import { toast } from 'sonner';

interface TramiteEntrada {
  id_seguimiento: number;
  id_documento: number | null;
  materia: string | null;
  num_interno: string | null;
  desc_tipo_documento: string | null;
  id_estado_tramite: number | null;
  desc_estado_tramite: string | null;
  fecha_sistema: string | null;
  observaciones: string | null;
  total: number;
}

const ESTADO_CONFIG: Record<number, { label: string; variant: 'warning' | 'info' | 'success' | 'secondary'; icon: React.ComponentType<{ className?: string }> }> = {
  1: { label: 'Pendiente',    variant: 'warning',   icon: Clock },
  2: { label: 'En proceso',   variant: 'info',      icon: RefreshCw },
  3: { label: 'Completado',   variant: 'success',   icon: CheckCircle },
};

export function BandejaPage() {
  const [pagina, setPagina] = useState(1);
  const [filtroEstado, setFiltroEstado] = useState<number | null>(null);
  const user = useAuthStore((s) => s.user);
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['bandeja', pagina, filtroEstado],
    queryFn: async () => {
      const params = new URLSearchParams({ pagina: String(pagina), porPagina: '20' });
      if (filtroEstado) params.set('idEstado', String(filtroEstado));
      const { data } = await apiClient.get<{ data: TramiteEntrada[]; meta: { total: number; pagina: number; totalPaginas: number; porPagina: number } }>(
        `/tramites?${params}`
      );
      return data;
    },
    placeholderData: keepPreviousData,
    staleTime: 15_000,
    refetchInterval: 30_000,
  });

  const recibirMutation = useMutation({
    mutationFn: (id: number) => apiClient.patch(`/tramites/${id}/recibir`),
    onSuccess: () => { qc.invalidateQueries({ queryKey: ['bandeja'] }); toast.success('Trámite recibido correctamente'); },
    onError: () => toast.error('No se pudo recibir el trámite'),
  });

  const tramites = data?.data ?? [];
  const meta = data?.meta;
  const pendientes = tramites.filter((t) => t.id_estado_tramite === 1).length;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-bold text-foreground flex items-center gap-2">
            <Inbox className="h-6 w-6 text-primary" />
            Bandeja de Entrada
          </h1>
          <p className="text-sm text-muted-foreground mt-0.5">
            {meta ? `${meta.total} trámite${meta.total !== 1 ? 's' : ''} en total` : 'Cargando...'}
            {pendientes > 0 && (
              <span className="ml-2 inline-flex items-center gap-1 text-amber-600 font-medium">
                · {pendientes} pendiente{pendientes !== 1 ? 's' : ''}
              </span>
            )}
          </p>
        </div>
        <Button
          variant="outline"
          size="sm"
          onClick={() => qc.invalidateQueries({ queryKey: ['bandeja'] })}
          className="gap-2"
        >
          <RefreshCw className="h-4 w-4" />
          Actualizar
        </Button>
      </div>

      {/* Filtros rápidos */}
      <div className="flex gap-2 flex-wrap">
        {[
          { label: 'Todos', value: null },
          { label: 'Pendientes', value: 1 },
          { label: 'En proceso', value: 2 },
          { label: 'Completados', value: 3 },
        ].map(({ label, value }) => (
          <Button
            key={label}
            variant={filtroEstado === value ? 'default' : 'outline'}
            size="sm"
            onClick={() => { setFiltroEstado(value); setPagina(1); }}
          >
            {label}
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
                  <Skeleton className="h-6 w-20 rounded-full" />
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
                const estadoConf = ESTADO_CONFIG[t.id_estado_tramite ?? 0];
                const isPendiente = t.id_estado_tramite === 1;

                return (
                  <div
                    key={t.id_seguimiento}
                    className={`flex items-start gap-4 px-6 py-5 hover:bg-muted/30 transition-colors ${isPendiente ? 'border-l-4 border-l-amber-400' : ''}`}
                  >
                    <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${isPendiente ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-muted'}`}>
                      <FileText className={`h-5 w-5 ${isPendiente ? 'text-amber-600' : 'text-muted-foreground'}`} />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-semibold text-foreground line-clamp-1">
                        {truncate(t.materia ?? 'Sin materia', 80)}
                      </p>
                      <div className="flex items-center gap-3 mt-1 flex-wrap text-xs text-muted-foreground">
                        {t.num_interno && <span className="font-mono">N° {t.num_interno}</span>}
                        {t.desc_tipo_documento && <span>{t.desc_tipo_documento}</span>}
                        {t.observaciones && <span className="italic">"{truncate(t.observaciones, 40)}"</span>}
                        <span className="flex items-center gap-1">
                          <Clock className="h-3 w-3" />
                          {formatRelativo(t.fecha_sistema)}
                        </span>
                      </div>
                    </div>
                    <div className="flex items-center gap-2 shrink-0">
                      {estadoConf && (
                        <Badge variant={estadoConf.variant}>{estadoConf.label}</Badge>
                      )}
                      {isPendiente && (
                        <Button
                          size="sm"
                          variant="outline"
                          className="h-7 text-xs gap-1"
                          loading={recibirMutation.isPending}
                          onClick={() => recibirMutation.mutate(t.id_seguimiento)}
                        >
                          <CheckCircle className="h-3 w-3" />
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
          <div className="flex items-center justify-between px-6 py-4 border-t">
            <p className="text-xs text-muted-foreground">
              Página {meta.pagina} de {meta.totalPaginas} · {meta.total} trámites
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
    </div>
  );
}
