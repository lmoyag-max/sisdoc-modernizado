import { useQuery } from '@tanstack/react-query';
import {
  FileText, FolderOpen, HardDrive, Users, GitBranch,
  Download, RefreshCw, TrendingUp, Clock,
} from 'lucide-react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip,
  ResponsiveContainer, PieChart, Pie, Cell, Legend,
} from 'recharts';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import { apiClient } from '@/lib/api/client';
import { formatRelativo } from '@/lib/utils';
import { cn } from '@/lib/utils';

interface DashboardData {
  totales: {
    total: number; pendientes: number; cerradosHoy: number; urgentes: number;
    expedientes: number; archivos: number; usuarios: number; tramites: number;
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

const ESTADO_COLORS = ['#6366f1', '#0ea5e9', '#f59e0b', '#10b981', '#8b5cf6', '#ef4444', '#f97316'];

const ACCION_BADGE: Record<string, string> = {
  DERIVADO:    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
  RECEPCIONADO:'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  CERRADO:     'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
  MOVIMIENTO:  'bg-muted text-muted-foreground',
};

function MetricBox({ icon: Icon, label, value, sub, color }: {
  icon: React.ComponentType<{ className?: string }>;
  label: string; value: number | string; sub?: string; color: string;
}) {
  return (
    <Card className="card-hover">
      <CardContent className="pt-5 pb-4">
        <div className="flex items-center gap-4">
          <div className={cn('flex h-11 w-11 shrink-0 items-center justify-center rounded-xl', color)}>
            <Icon className="h-5 w-5" />
          </div>
          <div>
            <p className="text-2xl font-bold text-foreground">{value}</p>
            <p className="text-sm text-muted-foreground">{label}</p>
            {sub && <p className="text-xs text-muted-foreground/70 mt-0.5">{sub}</p>}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

export function ReportesPage() {
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

  const handleExportar = () => {
    const token = localStorage.getItem('sisdoc_token') ?? '';
    window.open(`/api/v1/reportes/exportar`, '_blank');
  };

  const metrics = data ? [
    { icon: FileText,   label: 'Documentos',   value: data.totales.total,       sub: `${data.totales.pendientes} activos`,   color: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' },
    { icon: FolderOpen, label: 'Expedientes',   value: data.totales.expedientes, sub: 'en el sistema',                       color: 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400' },
    { icon: HardDrive,  label: 'Archivos',      value: data.totales.archivos,    sub: 'digitales subidos',                   color: 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400' },
    { icon: GitBranch,  label: 'Trámites',      value: data.totales.tramites,    sub: 'movimientos',                         color: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' },
    { icon: Users,      label: 'Usuarios',      value: data.totales.usuarios,    sub: 'activos',                             color: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' },
    { icon: Clock,      label: 'Creados hoy',   value: data.totales.cerradosHoy, sub: 'nuevos hoy',                         color: 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' },
  ] : [];

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
            <TrendingUp className="h-5 w-5 text-primary" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-foreground">Reportes</h1>
            <p className="text-sm text-muted-foreground">Métricas y estadísticas del sistema</p>
          </div>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" size="sm" onClick={() => refetch()} disabled={isFetching} className="gap-2">
            <RefreshCw className={cn('h-3.5 w-3.5', isFetching && 'animate-spin')} />
            Actualizar
          </Button>
          <Button size="sm" onClick={handleExportar} className="gap-2">
            <Download className="h-3.5 w-3.5" />
            Exportar CSV
          </Button>
        </div>
      </div>

      {/* Métricas principales */}
      <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
        {isLoading
          ? Array.from({ length: 6 }).map((_, i) => <Skeleton key={i} className="h-24 rounded-xl" />)
          : metrics.map((m) => <MetricBox key={m.label} {...m} />)
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
                <BarChart data={data?.porMes ?? []} margin={{ top: 0, right: 0, left: -20, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                  <XAxis dataKey="mes" tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} />
                  <YAxis tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} allowDecimals={false} />
                  <Tooltip
                    contentStyle={{ backgroundColor: 'hsl(var(--card))', border: '1px solid hsl(var(--border))', borderRadius: 8, fontSize: 12 }}
                    cursor={{ fill: 'hsl(var(--muted))' }}
                  />
                  <Bar dataKey="cantidad" name="Documentos" radius={[4,4,0,0]} fill="hsl(var(--primary))" />
                </BarChart>
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
                    <Tooltip formatter={(v: number) => [v, 'Docs']} contentStyle={{ fontSize: 12, borderRadius: 8 }} />
                  </PieChart>
                </ResponsiveContainer>
                <div className="space-y-2 mt-2">
                  {(data?.porEstado ?? []).map((e, i) => (
                    <div key={e.id_estado_documento} className="flex items-center justify-between text-xs">
                      <div className="flex items-center gap-2">
                        <span className="h-2 w-2 rounded-full" style={{ backgroundColor: ESTADO_COLORS[i % ESTADO_COLORS.length] }} />
                        <span className="text-foreground">{e.desc_estado_documento ?? 'Sin estado'}</span>
                      </div>
                      <span className="font-semibold text-foreground">{e.cantidad}</span>
                    </div>
                  ))}
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
                <Tooltip contentStyle={{ backgroundColor: 'hsl(var(--card))', border: '1px solid hsl(var(--border))', borderRadius: 8, fontSize: 12 }} />
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
            <div className="space-y-3">
              {(actividad ?? []).map((item) => (
                <div key={item.id_historial} className="flex items-start gap-3">
                  <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold text-muted-foreground">
                    {(item.nombres_fun ?? item.usuario ?? '?')[0]?.toUpperCase()}
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-foreground line-clamp-1">{item.asunto ?? 'Sin materia'}</p>
                    <div className="flex items-center gap-2 mt-0.5 flex-wrap">
                      <span className={cn('px-1.5 py-0.5 rounded text-[10px] font-medium', ACCION_BADGE[item.accion] ?? ACCION_BADGE.MOVIMIENTO)}>
                        {item.accion}
                      </span>
                      {item.num_documento && <span className="text-xs text-muted-foreground font-mono">#{item.num_documento}</span>}
                      <span className="text-xs text-muted-foreground">
                        por {item.nombres_fun ?? item.usuario ?? 'Sistema'}
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
