import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Send, Clock, ChevronLeft, ChevronRight, FileText, CheckCircle2, Building2 } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { useAuthStore } from '@/stores/auth.store';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { EmptyState } from '@/components/shared/EmptyState';
import { formatFechaHora, formatRelativo, truncate } from '@/lib/utils';

interface TramiteEnviado {
  id_seguimiento:      number;
  id_documento:        number | null;
  materia:             string | null;
  num_interno:         string | null;
  desc_tipo_documento: string | null;
  desc_estado_tramite: string | null;
  id_estado_tramite:   number | null;
  desc_procedencia:    string | null;
  desc_destino:        string | null;
  fecha_sistema:       string | null;
  fecha_despacho:      string | null;
  observaciones:       string | null;
  total:               number;
}

const ESTADO_BADGE: Record<number, { label: string; variant: 'info' | 'warning' | 'success' | 'secondary' }> = {
  1: { label: 'Generado',       variant: 'secondary' },
  2: { label: 'Despachado',     variant: 'warning' },
  3: { label: 'Recepcionado',   variant: 'info' },
  4: { label: 'Derivado',       variant: 'info' },
  5: { label: 'Cerrado',        variant: 'success' },
  6: { label: 'Entregado',      variant: 'success' },
};

export function EnviadosPage() {
  const [pagina, setPagina] = useState(1);
  const user = useAuthStore((s) => s.user);

  const { data, isLoading } = useQuery({
    queryKey: ['enviados', pagina],
    queryFn: async () => {
      const { data } = await apiClient.get<{
        data: TramiteEnviado[];
        meta: { total: number; pagina: number; totalPaginas: number; porPagina: number };
      }>(`/tramites/enviados?pagina=${pagina}&porPagina=20`);
      return data;
    },
  });

  const tramites    = data?.data ?? [];
  const meta        = data?.meta;
  const recepcionados = tramites.filter((t) => t.id_estado_tramite === 3 || t.id_estado_tramite === 5).length;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-bold text-foreground flex items-center gap-2">
            <Send className="h-6 w-6 text-primary" />
            Documentos Enviados
          </h1>
          <p className="text-sm text-muted-foreground mt-0.5">
            {meta ? `${meta.total} documento${meta.total !== 1 ? 's' : ''}` : 'Cargando...'}
            {recepcionados > 0 && (
              <span className="ml-2 text-emerald-600 font-medium">
                · {recepcionados} recepcionado{recepcionados !== 1 ? 's' : ''}
              </span>
            )}
          </p>
          {/* Indicador de servicio */}
          {user?.descDependencia && (
            <div className="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground">
              <Building2 className="h-3 w-3" />
              Enviados desde: <span className="font-medium text-foreground">{user.descDependencia}</span>
            </div>
          )}
        </div>
      </div>

      {/* Estadísticas rápidas */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        {[
          { label: 'Total enviados',  value: meta?.total ?? '—',                                              color: 'text-foreground' },
          { label: 'Despachados',     value: tramites.filter((t) => t.id_estado_tramite === 2).length,         color: 'text-amber-600' },
          { label: 'Recepcionados',   value: tramites.filter((t) => t.id_estado_tramite === 3).length,         color: 'text-blue-600' },
          { label: 'Cerrados',        value: tramites.filter((t) => t.id_estado_tramite === 5).length,         color: 'text-emerald-600' },
        ].map(({ label, value, color }) => (
          <div key={label} className="rounded-xl border bg-card p-4">
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className={`text-2xl font-bold mt-1 ${color}`}>{value}</p>
          </div>
        ))}
      </div>

      {/* Lista */}
      <Card>
        <CardContent className="p-0">
          {isLoading ? (
            <div className="divide-y">
              {Array.from({ length: 5 }).map((_, i) => (
                <div key={i} className="flex items-center gap-4 px-6 py-5">
                  <Skeleton className="h-10 w-10 rounded-xl shrink-0" />
                  <div className="flex-1 space-y-2">
                    <Skeleton className="h-4 w-2/3" />
                    <Skeleton className="h-3 w-1/3" />
                  </div>
                  <Skeleton className="h-6 w-24 rounded-full" />
                </div>
              ))}
            </div>
          ) : tramites.length === 0 ? (
            <EmptyState
              icon={Send}
              title="Sin envíos"
              description={
                user?.descDependencia
                  ? `No hay documentos enviados desde ${user.descDependencia}.`
                  : 'No has enviado documentos aún.'
              }
            />
          ) : (
            <div className="divide-y">
              {tramites.map((t) => {
                const badge     = ESTADO_BADGE[t.id_estado_tramite ?? 0];
                const isCompleto = t.id_estado_tramite === 5 || t.id_estado_tramite === 3;

                return (
                  <div key={t.id_seguimiento} className="flex items-start gap-4 px-6 py-5 hover:bg-muted/30 transition-colors">
                    <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${isCompleto ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-primary/10'}`}>
                      {isCompleto
                        ? <CheckCircle2 className="h-5 w-5 text-emerald-600" />
                        : <FileText className="h-5 w-5 text-primary" />
                      }
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-foreground line-clamp-1">
                        {truncate(t.materia ?? 'Sin materia', 75)}
                      </p>
                      <div className="flex items-center gap-3 mt-1 text-xs text-muted-foreground flex-wrap">
                        {t.num_interno && <span className="font-mono">N° {t.num_interno}</span>}
                        {t.desc_tipo_documento && <span>{t.desc_tipo_documento}</span>}
                        {/* Origen → Destino */}
                        {(t.desc_procedencia || t.desc_destino) && (
                          <span className="flex items-center gap-1">
                            <span>{t.desc_procedencia ?? '—'}</span>
                            <span>→</span>
                            <span className="font-medium text-foreground">{t.desc_destino ?? '—'}</span>
                          </span>
                        )}
                        <span className="flex items-center gap-1">
                          <Clock className="h-3 w-3" />
                          {t.fecha_sistema ? formatFechaHora(t.fecha_sistema) : '—'}
                        </span>
                        <span className="text-muted-foreground/60">{formatRelativo(t.fecha_sistema)}</span>
                      </div>
                    </div>
                    {badge && <Badge variant={badge.variant} className="shrink-0">{badge.label}</Badge>}
                  </div>
                );
              })}
            </div>
          )}
        </CardContent>

        {meta && meta.totalPaginas > 1 && (
          <div className="flex items-center justify-between px-6 py-4 border-t">
            <p className="text-xs text-muted-foreground">Página {meta.pagina} de {meta.totalPaginas}</p>
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
