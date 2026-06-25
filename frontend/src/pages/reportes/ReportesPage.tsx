import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { toast } from 'sonner';
import {
  FileText, HardDrive, Users, GitBranch,
  Download, RefreshCw, TrendingUp, Clock, Lock,
} from 'lucide-react';
import { MetricCard } from '@/components/dashboard/MetricCard';
import {
  AreaChart, Area, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip,
  ResponsiveContainer, PieChart, Pie, Cell,
} from 'recharts';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import { apiClient } from '@/lib/api/client';
import { useRole } from '@/hooks/useRole';
import { formatRelativo } from '@/lib/utils';
import { cn } from '@/lib/utils';

interface DashboardData {
  totales: {
    total: number; pendientes: number; cerradosHoy: number; creadosHoy: number; urgentes: number;
    archivos: number; usuarios: number; tramites: number; reservados: number | null;
  };
  porEstado: { id_estado_documento: number; desc_estado_documento: string; cantidad: number }[];
  porMes: { mes: string; cantidad: number }[];
  porTipo: { desc_tipo_documento: string; cantidad: number }[];
}

interface ActividadItem {
  id_historial: number; accion: string; fecha: string | null;
  asunto: string | null; num_documento: string | null;
  usuario: string | null; nombres_fun: string | null;
}

const ESTADO_COLORS = [
  'hsl(239, 84%, 60%)',
  'hsl(199, 89%, 48%)',
  'hsl(38, 92%, 50%)',
  'hsl(160, 84%, 39%)',
  'hsl(265, 89%, 62%)',
  'hsl(0, 84%, 60%)',
  'hsl(24, 95%, 53%)',
];

const ACCION_BADGE: Record<string, string> = {
  DERIVADO:    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
  RECEPCIONADO:'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  CERRADO:     'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
  MOVIMIENTO:  'bg-muted text-muted-foreground',
};

const ACCION_ACCENT: Record<string, string> = {
  DERIVADO: 'amber', RECEPCIONADO: 'sky', CERRADO: 'emerald', MOVIMIENTO: 'slate',
};

function MesTooltip({ active, payload, label }: { active?: boolean; payload?: Array<{ value: number }>; label?: string }) {
  if (!active || !payload?.length) return null;
  return (
    <div className="rounded-xl border border-border bg-card/95 backdrop-blur-sm px-3 py-2 shadow-lg">
      <p className="text-xs font-semibold text-foreground">{label}</p>
      <p className="text-xs text-muted-foreground mt-0.5 flex items-center gap-1.5">
        <span className="inline-block h-2 w-2 rounded-full bg-primary shrink-0" />
        {payload[0].value?.toLocaleString('es-CL')} documentos
      </p>
    </div>
  );
}

function EstadoTooltip({ active, payload }: { active?: boolean; payload?: Array<{ name: string; value: number; color?: string }> }) {
  if (!active || !payload?.length) return null;
  const item = payload[0];
  return (
    <div className="rounded-xl border border-border bg-card/95 backdrop-blur-sm px-3 py-2 shadow-lg">
      <p className="text-xs font-semibold text-foreground flex items-center gap-1.5">
        <span className="inline-block h-2 w-2 rounded-full shrink-0" style={{ backgroundColor: item.color ?? 'hsl(var(--primary))' }} />
        {item.name}
      </p>
      <p className="text-xs text-muted-foreground mt-0.5">{item.value?.toLocaleString('es-CL')} docs</p>
    </div>
  );
}

function TipoTooltip({ active, payload, label }: { active?: boolean; payload?: Array<{ value: number }>; label?: string }) {
  if (!active || !payload?.length) return null;
  return (
    <div className="rounded-xl border border-border bg-card/95 backdrop-blur-sm px-3 py-2 shadow-lg">
      <p className="text-xs font-semibold text-foreground line-clamp-1">{label}</p>
      <p className="text-xs text-muted-foreground mt-0.5">{payload[0].value?.toLocaleString('es-CL')} documentos</p>
    </div>
  );
}


export function ReportesPage() {
  const { puedeVerReservados } = useRole();
  const { data, isLoading, refetch, isFetching } = useQuery({
    queryKey: ['reportes-dashboard'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: DashboardData }>('/reportes/dashboard');
      return data.data;
    },
    staleTime: 30_000,
    refetchInterval: 60_000,
  });

  const { data: actividad, isLoading: loadingAct } = useQuery({
    queryKey: ['reportes-actividad'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: ActividadItem[] }>('/reportes/actividad-reciente');
      return data.data;
    },
    staleTime: 15_000,
    refetchInterval: 30_000,
  });

  const [isDownloading, setIsDownloading] = useState(false);

  const handleExportar = async () => {
    try {
      setIsDownloading(true);
      const response = await apiClient.get('/reportes/exportar', { responseType: 'blob' });
      const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `documentos_${Date.now()}.csv`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch {
      toast.error('Error al exportar CSV');
    } finally {
      setIsDownloading(false);
    }
  };

  const allMetrics = data ? [
    { icon: FileText,  title: 'Documentos',  value: data.totales.total,      description: `${data.totales.pendientes} activos`,  colorClass: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400', visible: true },
    { icon: HardDrive, title: 'Archivos',    value: data.totales.archivos,   description: 'digitales subidos',                   colorClass: 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',     visible: true },
    { icon: GitBranch, title: 'Movimientos', value: data.totales.tramites,   description: 'en trazabilidad',                     colorClass: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400', visible: true },
    { icon: Users,     title: 'Usuarios',    value: data.totales.usuarios,   description: 'activos',                             colorClass: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400', visible: true },
    { icon: Clock,     title: 'Creados hoy', value: data.totales.creadosHoy, description: 'nuevos hoy',                          colorClass: 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',  visible: true },
    { icon: Lock,      title: 'Reservados',  value: data.totales.reservados ?? 0, description: 'documentos reservados',            colorClass: 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400', visible: puedeVerReservados },
  ] : [];
  const metrics = allMetrics.filter((m) => m.visible);

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div className="flex items-center gap-3">
          <span className="icon-3d icon-3d-emerald flex h-11 w-11 shrink-0 items-center justify-center">
            <TrendingUp className="h-5 w-5 text-white" />
          </span>
          <div>
            <h1 className="text-xl sm:text-2xl font-bold text-foreground">Reportes</h1>
            <p className="text-sm text-muted-foreground">Métricas y estadísticas del sistema</p>
          </div>
        </div>
        <div className="flex gap-2 shrink-0">
          <Button variant="outline" size="sm" onClick={() => refetch()} disabled={isFetching} className="gap-2">
            <RefreshCw className={cn('h-3.5 w-3.5', isFetching && 'animate-spin')} />
            Actualizar
          </Button>
          <Button size="sm" onClick={handleExportar} disabled={isDownloading} className="gap-2">
            <Download className={cn('h-3.5 w-3.5', isDownloading && 'animate-bounce')} />
            {isDownloading ? 'Exportando...' : 'Exportar CSV'}
          </Button>
        </div>
      </div>

      {/* Métricas principales */}
      <div className={cn(
        'grid gap-4',
        puedeVerReservados
          ? 'grid-cols-2 sm:grid-cols-3 xl:grid-cols-6'
          : 'grid-cols-2 sm:grid-cols-3 xl:grid-cols-5',
      )}>
        {isLoading
          ? Array.from({ length: puedeVerReservados ? 6 : 5 }).map((_, i) => <MetricCard key={i} title="" value="" icon={FileText} variant="compact" loading />)
          : metrics.map((m) => <MetricCard key={m.title} {...m} variant="compact" />)
        }
      </div>

      {/* Gráficos */}
      <div className="grid grid-cols-1 xl:grid-cols-5 gap-6">
        {/* Documentos por mes */}
        <Card className="xl:col-span-3">
          <CardHeader>
            <CardTitle className="text-base">Documentos por mes</CardTitle>
            <CardDescription>Últimos 6 meses</CardDescription>
          </CardHeader>
          <CardContent>
            {isLoading ? (
              <div className="h-52 flex items-end gap-2">
                {[40, 65, 50, 80, 55, 70].map((h, i) => (
                  <div key={i} className="flex-1 animate-pulse rounded-t bg-muted" style={{ height: `${h}%` }} />
                ))}
              </div>
            ) : (data?.porMes ?? []).length === 0 ? (
              <div className="h-52 flex items-center justify-center text-muted-foreground text-sm">Sin datos de los últimos 6 meses</div>
            ) : (
              <ResponsiveContainer width="100%" height={210}>
                <AreaChart data={data?.porMes ?? []} margin={{ top: 4, right: 0, left: -20, bottom: 0 }}>
                  <defs>
                    <linearGradient id="reportGrad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%"  stopColor="hsl(var(--primary))" stopOpacity={0.25} />
                      <stop offset="95%" stopColor="hsl(var(--primary))" stopOpacity={0.02} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" vertical={false} />
                  <XAxis dataKey="mes" tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} axisLine={false} tickLine={false} />
                  <YAxis tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} allowDecimals={false} axisLine={false} tickLine={false} />
                  <Tooltip content={<MesTooltip />} cursor={{ stroke: 'hsl(var(--primary))', strokeWidth: 1, strokeDasharray: '4 4' }} />
                  <Area dataKey="cantidad" name="Documentos" type="monotone" stroke="hsl(var(--primary))" strokeWidth={2} fill="url(#reportGrad)" dot={false} activeDot={{ r: 4, fill: 'hsl(var(--primary))' }} />
                </AreaChart>
              </ResponsiveContainer>
            )}
          </CardContent>
        </Card>

        {/* Por estado — pie */}
        <Card className="xl:col-span-2">
          <CardHeader>
            <CardTitle className="text-base">Por estado</CardTitle>
            <CardDescription>Distribución actual</CardDescription>
          </CardHeader>
          <CardContent>
            {isLoading ? (
              <div className="space-y-3">
                {Array.from({ length: 3 }).map((_, i) => (
                  <div key={i} className="flex items-center gap-3">
                    <Skeleton className="h-3 w-3 rounded-full" />
                    <Skeleton className="h-3 flex-1" />
                    <Skeleton className="h-3 w-8" />
                  </div>
                ))}
              </div>
            ) : (
              <>
                <ResponsiveContainer width="100%" height={130}>
                  <PieChart>
                    <Pie data={data?.porEstado ?? []} dataKey="cantidad" nameKey="desc_estado_documento"
                      cx="50%" cy="50%" outerRadius={55} innerRadius={30}>
                      {(data?.porEstado ?? []).map((_, i) => (
                        <Cell key={i} fill={ESTADO_COLORS[i % ESTADO_COLORS.length]} />
                      ))}
                    </Pie>
                    <Tooltip content={<EstadoTooltip />} />
                  </PieChart>
                </ResponsiveContainer>
                <div className="space-y-2 mt-2">
                  {(data?.porEstado ?? []).map((e, i) => {
                    const total = (data?.porEstado ?? []).reduce((s, x) => s + x.cantidad, 0);
                    const pct = total > 0 ? Math.round((e.cantidad / total) * 100) : 0;
                    return (
                      <div key={e.id_estado_documento} className="space-y-0.5">
                        <div className="flex items-center justify-between text-xs">
                          <div className="flex items-center gap-2">
                            <span className="h-2 w-2 rounded-full shrink-0" style={{ backgroundColor: ESTADO_COLORS[i % ESTADO_COLORS.length] }} />
                            <span className="text-foreground">{e.desc_estado_documento ?? 'Sin estado'}</span>
                          </div>
                          <span className="font-semibold text-foreground tabular-nums">{e.cantidad}</span>
                        </div>
                        <div className="h-1 w-full rounded-full bg-muted overflow-hidden">
                          <div className="h-full rounded-full transition-all duration-500" style={{ width: `${pct}%`, backgroundColor: ESTADO_COLORS[i % ESTADO_COLORS.length] }} />
                        </div>
                      </div>
                    );
                  })}
                </div>
              </>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Por tipo de documento */}
      {!isLoading && (data?.porTipo ?? []).length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Por tipo de documento</CardTitle>
            <CardDescription>Top 8 tipos más usados</CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={180}>
              <BarChart data={data?.porTipo ?? []} layout="vertical" margin={{ top: 0, right: 20, left: 0, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" horizontal={false} />
                <XAxis type="number" tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} allowDecimals={false} />
                <YAxis type="category" dataKey="desc_tipo_documento" width={160} tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} />
                <Tooltip content={<TipoTooltip />} cursor={{ fill: 'hsl(var(--muted) / 0.4)' }} />
                <Bar dataKey="cantidad" name="Documentos" radius={[0,4,4,0]} fill="hsl(var(--accent))" />
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      )}

      {/* Actividad reciente */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Actividad reciente</CardTitle>
          <CardDescription>Últimos 15 movimientos del sistema</CardDescription>
        </CardHeader>
        <CardContent>
          {loadingAct ? (
            <div className="space-y-4">
              {Array.from({ length: 5 }).map((_, i) => (
                <div key={i} className="flex items-start gap-3">
                  <Skeleton className="h-8 w-8 rounded-full shrink-0" />
                  <div className="flex-1 space-y-1.5"><Skeleton className="h-3 w-3/4" /><Skeleton className="h-3 w-1/2" /></div>
                  <Skeleton className="h-3 w-16 shrink-0" />
                </div>
              ))}
            </div>
          ) : (actividad ?? []).length === 0 ? (
            <p className="py-8 text-center text-sm text-muted-foreground">Sin actividad registrada</p>
          ) : (
            <div className="timeline-rail space-y-1">
              {(actividad ?? []).map((item, i) => (
                <div
                  key={item.id_historial}
                  className="relative flex items-start gap-3 py-2 pl-7 rounded-lg transition-colors duration-150 hover:bg-muted/40 group animate-fade-in-up"
                  style={{ animationDelay: `${i * 50}ms` }}
                >
                  <span className={`icon-3d-sm absolute left-0 top-2 flex h-7 w-7 shrink-0 items-center justify-center ring-4 ring-card icon-3d-${ACCION_ACCENT[item.accion] ?? 'slate'} transition-transform duration-200 group-hover:scale-105`}>
                    <span className="text-[10px] font-bold text-white">
                      {(item.nombres_fun ?? item.usuario ?? '?')[0]?.toUpperCase()}
                    </span>
                  </span>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-foreground line-clamp-1 group-hover:text-primary transition-colors">{item.asunto ?? 'Sin materia'}</p>
                    <div className="flex items-center gap-2 mt-0.5 flex-wrap">
                      <span className={cn('px-1.5 py-0.5 rounded text-xs font-medium', ACCION_BADGE[item.accion] ?? ACCION_BADGE.MOVIMIENTO)}>
                        {item.accion}
                      </span>
                      {item.num_documento && <span className="text-xs text-muted-foreground font-mono">N° {item.num_documento}</span>}
                      <span className="text-xs text-muted-foreground">
                        {item.nombres_fun ?? item.usuario ?? 'Sistema'}
                      </span>
                    </div>
                  </div>
                  <span className="text-xs text-muted-foreground whitespace-nowrap shrink-0">
                    {item.fecha ? formatRelativo(item.fecha) : '—'}
                  </span>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
