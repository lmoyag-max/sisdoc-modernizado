import { useQuery } from '@tanstack/react-query';
import { FileText, Clock, CheckCircle2, AlertTriangle, Activity, ArrowRight } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import { MetricCard } from '@/components/dashboard/MetricCard';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { reportesApi } from '@/lib/api/reportes.api';
import { useAuthStore, displayName } from '@/stores/auth.store';
import { formatRelativo, truncate } from '@/lib/utils';
import { Link } from 'react-router-dom';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

const ESTADO_COLORES: Record<number, string> = {
  1: '#3b82f6',
  2: '#0ea5e9',
  3: '#f59e0b',
  4: '#8b5cf6',
  5: '#10b981',
};

const ACCION_BADGE: Record<string, { label: string; variant: 'info' | 'warning' | 'success' | 'secondary' }> = {
  INGRESADO: { label: 'Ingresado', variant: 'info' },
  DERIVADO: { label: 'Derivado', variant: 'warning' },
  RECEPCIONADO: { label: 'Recepcionado', variant: 'secondary' },
  CERRADO: { label: 'Cerrado', variant: 'success' },
};

export function DashboardPage() {
  const user = useAuthStore((s) => s.user);
  const nombre = displayName(user);

  const { data: dashboard, isLoading: loadingDash } = useQuery({
    queryKey: ['dashboard'],
    queryFn: reportesApi.dashboard,
    refetchInterval: 60_000,
  });

  const { data: actividad, isLoading: loadingActividad } = useQuery({
    queryKey: ['actividad-reciente'],
    queryFn: reportesApi.actividadReciente,
    refetchInterval: 30_000,
  });

  const now = new Date();
  const saludo = now.getHours() < 12 ? 'Buenos días' : now.getHours() < 18 ? 'Buenas tardes' : 'Buenas noches';
  const fecha = format(now, "EEEE, d 'de' MMMM yyyy", { locale: es });
  const fechaCapitalizada = fecha.charAt(0).toUpperCase() + fecha.slice(1);

  const metrics = [
    {
      title: 'Total documentos',
      value: dashboard?.totales.total ?? 0,
      icon: FileText,
      description: 'En el sistema',
      colorClass: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    },
    {
      title: 'Pendientes',
      value: dashboard?.totales.pendientes ?? 0,
      icon: Clock,
      description: 'Sin resolver',
      colorClass: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
    },
    {
      title: 'Cerrados hoy',
      value: dashboard?.totales.cerradosHoy ?? 0,
      icon: CheckCircle2,
      description: 'Completados hoy',
      colorClass: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
    },
    {
      title: 'Urgentes',
      value: dashboard?.totales.urgentes ?? 0,
      icon: AlertTriangle,
      description: 'Prioridad alta',
      colorClass: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
    },
  ];

  return (
    <div className="space-y-6">
      {/* Hero greeting */}
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-foreground">
            {saludo}, {nombre.split(' ')[0]}.
          </h1>
          <p className="text-muted-foreground text-sm mt-0.5">{fechaCapitalizada}</p>
        </div>
        <Link to="/documentos/nuevo">
          <Button size="sm" className="gap-2 shrink-0">
            <FileText className="h-4 w-4" />
            <span className="hidden xs:inline">Nuevo documento</span>
            <span className="xs:hidden">Nuevo</span>
          </Button>
        </Link>
      </div>

      {/* Métricas */}
      <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {metrics.map((m) => (
          <MetricCard key={m.title} {...m} loading={loadingDash} />
        ))}
      </div>

      {/* Charts + Actividad */}
      <div className="grid grid-cols-1 xl:grid-cols-5 gap-6">
        {/* Chart por mes */}
        <Card className="xl:col-span-3">
          <CardHeader>
            <CardTitle className="text-base">Documentos por mes</CardTitle>
            <CardDescription>Últimos 6 meses</CardDescription>
          </CardHeader>
          <CardContent>
            {loadingDash ? (
              <div className="h-56 flex items-end gap-2 px-2">
                {[40, 65, 50, 80, 55, 70].map((h, i) => (
                  <div key={i} className="flex-1 animate-pulse rounded-t bg-muted" style={{ height: `${h}%` }} />
                ))}
              </div>
            ) : (
              <ResponsiveContainer width="100%" height={220}>
                <BarChart data={dashboard?.porMes ?? []} margin={{ top: 0, right: 0, left: -20, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                  <XAxis dataKey="mes" tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} />
                  <YAxis tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} />
                  <Tooltip
                    contentStyle={{
                      backgroundColor: 'hsl(var(--card))',
                      border: '1px solid hsl(var(--border))',
                      borderRadius: 8,
                      fontSize: 12,
                    }}
                    cursor={{ fill: 'hsl(var(--muted))' }}
                  />
                  <Bar dataKey="cantidad" radius={[4, 4, 0, 0]} fill="hsl(var(--primary))" />
                </BarChart>
              </ResponsiveContainer>
            )}
          </CardContent>
        </Card>

        {/* Por estado */}
        <Card className="xl:col-span-2">
          <CardHeader>
            <CardTitle className="text-base">Por estado</CardTitle>
            <CardDescription>Distribución actual</CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            {loadingDash ? (
              Array.from({ length: 4 }).map((_, i) => (
                <div key={i} className="flex items-center gap-3">
                  <Skeleton className="h-3 w-3 rounded-full" />
                  <Skeleton className="h-3 flex-1" />
                  <Skeleton className="h-3 w-8" />
                </div>
              ))
            ) : (
              (dashboard?.porEstado ?? []).map((estado) => {
                const color = ESTADO_COLORES[estado.id_estado_documento] ?? '#94a3b8';
                const total = dashboard?.totales.total ?? 1;
                const pct = Math.round((estado.cantidad / total) * 100);
                return (
                  <div key={estado.id_estado_documento} className="space-y-1">
                    <div className="flex items-center justify-between text-xs">
                      <div className="flex items-center gap-2">
                        <span className="h-2 w-2 rounded-full shrink-0" style={{ backgroundColor: color }} />
                        <span className="text-foreground">{estado.desc_estado_documento}</span>
                      </div>
                      <span className="text-muted-foreground font-medium">{estado.cantidad}</span>
                    </div>
                    <div className="h-1.5 w-full rounded-full bg-muted overflow-hidden">
                      <div
                        className="h-full rounded-full transition-all duration-500"
                        style={{ width: `${pct}%`, backgroundColor: color }}
                      />
                    </div>
                  </div>
                );
              })
            )}
          </CardContent>
        </Card>
      </div>

      {/* Actividad reciente */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between">
          <div>
            <CardTitle className="text-base flex items-center gap-2">
              <Activity className="h-4 w-4 text-muted-foreground" />
              Actividad reciente
            </CardTitle>
            <CardDescription>Últimas acciones del sistema</CardDescription>
          </div>
          <Link to="/documentos">
            <Button variant="ghost" size="sm" className="gap-1 text-xs">
              Ver todos <ArrowRight className="h-3 w-3" />
            </Button>
          </Link>
        </CardHeader>
        <CardContent>
          {loadingActividad ? (
            <div className="space-y-4">
              {Array.from({ length: 5 }).map((_, i) => (
                <div key={i} className="flex items-start gap-4">
                  <Skeleton className="h-8 w-8 rounded-full shrink-0" />
                  <div className="flex-1 space-y-1.5">
                    <Skeleton className="h-3 w-3/4" />
                    <Skeleton className="h-3 w-1/2" />
                  </div>
                  <Skeleton className="h-3 w-16" />
                </div>
              ))}
            </div>
          ) : (actividad ?? []).length === 0 ? (
            <p className="text-center text-sm text-muted-foreground py-8">Sin actividad reciente</p>
          ) : (
            <div className="space-y-4">
              {(actividad ?? []).map((item) => {
                const badgeConfig = ACCION_BADGE[item.accion ?? ''] ?? { label: item.accion ?? '', variant: 'secondary' as const };
                return (
                  <div key={item.id_historial} className="flex items-start gap-3">
                    <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium text-muted-foreground">
                      {(item.nombres_fun ?? item.usuario ?? '?')[0]?.toUpperCase()}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex flex-wrap items-start justify-between gap-x-2 gap-y-0.5">
                        <p className="text-sm font-medium text-foreground truncate flex-1 min-w-0">
                          {truncate(item.asunto ?? 'Sin asunto', 55)}
                        </p>
                        <span className="text-xs text-muted-foreground whitespace-nowrap shrink-0">
                          {formatRelativo(item.fecha)}
                        </span>
                      </div>
                      <div className="flex items-center gap-2 mt-0.5 flex-wrap">
                        <Badge variant={badgeConfig.variant}>{badgeConfig.label}</Badge>
                        {item.num_documento && (
                          <span className="text-xs text-muted-foreground">{item.num_documento}</span>
                        )}
                        <span className="text-xs text-muted-foreground">
                          por {item.nombres_fun ?? item.usuario ?? 'Sistema'}
                        </span>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
