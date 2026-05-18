import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { GitBranch, CheckCircle, Clock, ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { EmptyState } from '@/components/shared/EmptyState';
import { formatRelativo } from '@/lib/utils';
import { toast } from 'sonner';

interface Tramite {
  id_tramite: number;
  id_documento: number;
  asunto: string | null;
  num_documento: string | null;
  desc_tipo_documento: string | null;
  id_estado_tramite: number | null;
  desc_estado_tramite: string | null;
  desc_procedencia: string | null;
  fecha_derivacion: string | null;
  fecha_cierre: string | null;
  observacion: string | null;
  total: number;
}

const ESTADO_TRAMITE_BADGE: Record<number, { label: string; variant: 'info' | 'warning' | 'success' | 'secondary' }> = {
  1: { label: 'Pendiente', variant: 'warning' },
  2: { label: 'En proceso', variant: 'info' },
  3: { label: 'Completado', variant: 'success' },
  4: { label: 'Rechazado', variant: 'secondary' },
};

export function TramitesPage() {
  const [pagina, setPagina] = useState(1);
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['tramites', pagina],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: Tramite[]; meta: { total: number; pagina: number; porPagina: number; totalPaginas: number } }>(
        `/tramites?pagina=${pagina}&porPagina=20`
      );
      return data;
    },
  });

  const recibirMutation = useMutation({
    mutationFn: (id: number) => apiClient.patch(`/tramites/${id}/recibir`),
    onSuccess: () => { qc.invalidateQueries({ queryKey: ['tramites'] }); toast.success('Trámite recibido'); },
  });

  const cerrarMutation = useMutation({
    mutationFn: (id: number) => apiClient.patch(`/tramites/${id}/cerrar`),
    onSuccess: () => { qc.invalidateQueries({ queryKey: ['tramites'] }); toast.success('Trámite cerrado'); },
  });

  const tramites = data?.data ?? [];
  const meta = data?.meta;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-foreground">Mis Trámites</h1>
        <p className="text-sm text-muted-foreground mt-0.5">
          {meta ? `${meta.total} trámite${meta.total !== 1 ? 's' : ''}` : 'Cargando...'}
        </p>
      </div>

      <Card>
        <CardContent className="p-0">
          {isLoading ? (
            <div className="divide-y">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="flex items-center gap-4 px-6 py-5">
                  <Skeleton className="h-10 w-10 rounded-xl shrink-0" />
                  <div className="flex-1 space-y-2">
                    <Skeleton className="h-4 w-2/3" />
                    <Skeleton className="h-3 w-1/2" />
                  </div>
                  <Skeleton className="h-6 w-20 rounded-full" />
                </div>
              ))}
            </div>
          ) : tramites.length === 0 ? (
            <EmptyState icon={GitBranch} title="Sin trámites" description="No tienes trámites asignados." />
          ) : (
            <div className="divide-y">
              {tramites.map((t) => {
                const badge = ESTADO_TRAMITE_BADGE[t.id_estado_tramite ?? 0] ?? { label: '—', variant: 'secondary' as const };
                const isPendiente = t.id_estado_tramite === 1;
                const isEnProceso = t.id_estado_tramite === 2;

                return (
                  <div key={t.id_tramite} className="flex items-start gap-4 px-6 py-5 hover:bg-muted/30 transition-colors">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                      <GitBranch className="h-5 w-5 text-primary" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-foreground line-clamp-1">
                        {t.asunto ?? 'Sin asunto'}
                      </p>
                      <div className="flex items-center gap-3 mt-1 flex-wrap">
                        {t.num_documento && (
                          <span className="text-xs font-mono text-muted-foreground">{t.num_documento}</span>
                        )}
                        {t.desc_tipo_documento && (
                          <span className="text-xs text-muted-foreground">{t.desc_tipo_documento}</span>
                        )}
                        {t.desc_procedencia && (
                          <span className="text-xs text-muted-foreground">desde {t.desc_procedencia}</span>
                        )}
                        <span className="text-xs text-muted-foreground flex items-center gap-1">
                          <Clock className="h-3 w-3" />
                          {formatRelativo(t.fecha_derivacion)}
                        </span>
                      </div>
                      {t.observacion && (
                        <p className="text-xs text-muted-foreground mt-1 italic">"{t.observacion}"</p>
                      )}
                    </div>
                    <div className="flex items-center gap-2 shrink-0">
                      <Badge variant={badge.variant}>{badge.label}</Badge>
                      {isPendiente && (
                        <Button
                          size="sm"
                          variant="outline"
                          className="h-7 text-xs"
                          onClick={() => recibirMutation.mutate(t.id_tramite)}
                          loading={recibirMutation.isPending}
                        >
                          <CheckCircle className="h-3 w-3 mr-1" />
                          Recibir
                        </Button>
                      )}
                      {isEnProceso && (
                        <Button
                          size="sm"
                          className="h-7 text-xs"
                          onClick={() => cerrarMutation.mutate(t.id_tramite)}
                          loading={cerrarMutation.isPending}
                        >
                          Cerrar
                        </Button>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </CardContent>

        {meta && meta.totalPaginas > 1 && (
          <div className="flex items-center justify-between px-6 py-4 border-t">
            <p className="text-xs text-muted-foreground">
              Página {meta.pagina} de {meta.totalPaginas}
            </p>
            <div className="flex gap-2">
              <Button variant="outline" size="icon" className="h-8 w-8" disabled={meta.pagina <= 1} onClick={() => setPagina(p => p - 1)}>
                <ChevronLeft className="h-4 w-4" />
              </Button>
              <Button variant="outline" size="icon" className="h-8 w-8" disabled={meta.pagina >= meta.totalPaginas} onClick={() => setPagina(p => p + 1)}>
                <ChevronRight className="h-4 w-4" />
              </Button>
            </div>
          </div>
        )}
      </Card>
    </div>
  );
}
