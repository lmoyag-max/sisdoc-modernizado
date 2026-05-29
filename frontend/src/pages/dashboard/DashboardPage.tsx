import { useQuery } from '@tanstack/react-query';
import {
  FileText, Clock, CheckCircle2, AlertTriangle, Activity, ArrowRight,
  Lock, Send, Inbox, Plus, TrendingUp, Zap, ChevronRight,
  PieChart as PieIcon,
} from 'lucide-react';
import {
  AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell,
} from 'recharts';
import { MetricCard } from '@/components/dashboard/MetricCard';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { reportesApi } from '@/lib/api/reportes.api';
import { useAuthStore, displayName } from '@/stores/auth.store';
import { useRole } from '@/hooks/useRole';
import { formatRelativo, truncate } from '@/lib/utils';
import { cn } from '@/lib/utils';
import { Link } from 'react-router-dom';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

// ── Mapeo visual ────────────────────────────────────────────────
const ESTADO_COLORES: Record<number, string> = {
  1: 'hsl(239, 84%, 60%)',
  2: 'hsl(199, 89%, 48%)',
  3: 'hsl(38, 92%, 50%)',
  4: 'hsl(265, 89%, 62%)',
  5: 'hsl(160, 84%, 39%)',
};

const ACCION_BADGE: Record<string, { label: string; variant: 'info' | 'warning' | 'success' | 'secondary' }> = {
  INGRESADO:    { label: 'Ingresado',    variant: 'info' },
  DESPACHADO:   { label: 'Despachado',   variant: 'warning' },
  DERIVADO:     { label: 'Derivado',     variant: 'warning' },
  RECEPCIONADO: { label: 'Recepcionado', variant: 'secondary' },
  CERRADO:      { label: 'Cerrado',      variant: 'success' },
  MOVIMIENTO:   { label: 'Movimiento',   variant: 'secondary' },
};

// ── Semáforo ejecutivo ──────────────────────────────────────────
type NivelSemaforo = 'normal' | 'atencion' | 'critico';

const SEMAFORO = {
  normal:   { label: 'Sistema Normal',    sub: 'Operación estable',   textClass: 'text-emerald-700 dark:text-emerald-400', borderClass: 'border-emerald-200 dark:border-emerald-900/60', bgClass: 'bg-emerald-50/80 dark:bg-emerald-950/20' },
  atencion: { label: 'Requiere Atención', sub: 'Urgentes detectados', textClass: 'text-amber-700 dark:text-amber-400',   borderClass: 'border-amber-200 dark:border-amber-900/60',   bgClass: 'bg-amber-50/80 dark:bg-amber-950/20' },
  critico:  { label: 'Nivel Crítico',     sub: 'Acción inmediata',    textClass: 'text-red-700 dark:text-red-400',       borderClass: 'border-red-200 dark:border-red-900/60',       bgClass: 'bg-red-50/80 dark:bg-red-950/20' },
} as const;

function SemaforoEjecutivo({ nivel, loading }: { nivel: NivelSemaforo; loading?: boolean }) {
  if (loading) {
    return (
      <div className="flex items-center gap-3 rounded-xl border px-4 py-3 min-w-[190px]">
        <div className="flex gap-1.5">{[0, 1, 2].map(i => <Skeleton key={i} className="h-3 w-3 rounded-full" />)}</div>
        <Skeleton className="h-8 w-28" />
      </div>
    );
  }
  const cfg = SEMAFORO[nivel];
  return (
    <div className={cn('flex items-center gap-3 rounded-xl border px-4 py-3 transition-all', cfg.borderClass, cfg.bgClass)}>
      <div className="flex items-center gap-1.5 shrink-0">
        <div className={cn('h-3 w-3 rounded-full transition-all duration-300', nivel === 'critico' ? 'bg-red-500 semaforo-rojo' : 'bg-red-200 dark:bg-red-900/40')} />
        <div className={cn('h-3 w-3 rounded-full transition-all duration-300', nivel === 'atencion' ? 'bg-amber-500 semaforo-amarillo' : 'bg-amber-200 dark:bg-amber-900/40')} />
        <div className={cn('h-3 w-3 rounded-full transition-all duration-300', nivel === 'normal'   ? 'bg-emerald-500 semaforo-verde'   : 'bg-emerald-200 dark:bg-emerald-900/40')} />
      </div>
      <div className="min-w-0">
        <p className={cn('text-xs font-bold leading-tight', cfg.textClass)}>{cfg.label}</p>
        <p className="text-[10px] text-muted-foreground leading-tight">{cfg.sub}</p>
      </div>
    </div>
  );
}

// ── Pipeline documental ─────────────────────────────────────────
interface PipelineStageProps {
  icon: React.ComponentType<{ className?: string }>;
  label: string;
  count: number;
  colorClass: string;
  bgClass: string;
  isLast?: boolean;
  loading?: boolean;
}

function PipelineStage({ icon: Icon, label, count, colorClass, bgClass, isLast, loading }: PipelineStageProps) {
  return (
    <div className="flex items-center flex-1 min-w-0">
      <div className="flex-1 min-w-0 flex flex-col items-center gap-2 p-3 sm:p-4">
        <div className={cn('flex h-10 w-10 items-center justify-center rounded-xl shrink-0', bgClass)}>
          <Icon className={cn('h-5 w-5', colorClass)} />
        </div>
        <div className="text-center min-w-0">
          {loading
            ? <Skeleton className="h-6 w-10 mx-auto mb-1" />
            : <p className={cn('text-xl font-bold tabular-nums animate-number', colorClass)}>{count.toLocaleString('es-CL')}</p>
          }
          <p className="text-xs text-muted-foreground font-medium truncate">{label}</p>
        </div>
      </div>
      {!isLast && <ChevronRight className="h-4 w-4 text-border shrink-0 pipeline-arrow" />}
    </div>
  );
}

// ── Componente principal ────────────────────────────────────────
export function DashboardPage() {
  const user    = useAuthStore((s) => s.user);
  const nombre  = displayName(user);
  const { puedeVerReservados } = useRole();

  const { data: dashboard, isLoading: loadingDash } = useQuery({
    queryKey: ['dashboard'],
    queryFn:  reportesApi.dashboard,
    staleTime:            0,        // siempre obsoleto → refetch al montar/volver al módulo
    refetchOnMount:       true,     // recarga al entrar al Dashboard desde cualquier ruta
    refetchOnWindowFocus: false,    // no recargar al volver a la pestaña del navegador
    refetchInterval:      120_000,  // refresco en background cada 2 min (sin spam)
  });

  const { data: actividad, isLoading: loadingActividad } = useQuery({
    queryKey: ['actividad-reciente'],
    queryFn:  reportesApi.actividadReciente,
    refetchInterval: 30_000,
  });

  const now              = new Date();
  const saludo           = now.getHours() < 12 ? 'Buenos días' : now.getHours() < 18 ? 'Buenas tardes' : 'Buenas noches';
  const fecha            = format(now, "EEEE, d 'de' MMMM yyyy", { locale: es });
  const fechaCapitalizada = fecha.charAt(0).toUpperCase() + fecha.slice(1);

  // Semáforo
  const urgentes  = dashboard?.totales.urgentes  ?? 0;
  const pendientes = dashboard?.totales.pendientes ?? 0;
  const ratio     = pendientes > 0 ? urgentes / pendientes : 0;
  const nivelSemaforo: NivelSemaforo =
    ratio > 0.15 || urgentes > 20 ? 'critico' :
    ratio > 0.05 || urgentes > 8  ? 'atencion' :
    'normal';

  // Pipeline — cuenta por estado
  const getCount = (ids: number[]) =>
    dashboard?.porEstado?.filter(e => ids.includes(e.id_estado_documento)).reduce((s, e) => s + e.cantidad, 0) ?? 0;

  const pipelineStages = [
    { ids: [1, 2], label: 'Despachados',  icon: Send,         colorClass: 'text-indigo-600 dark:text-indigo-400', bgClass: 'bg-indigo-100 dark:bg-indigo-900/30' },
    { ids: [3],    label: 'Recepcionados',icon: Inbox,        colorClass: 'text-sky-600 dark:text-sky-400',       bgClass: 'bg-sky-100 dark:bg-sky-900/30' },
    { ids: [4],    label: 'En Proceso',   icon: Activity,     colorClass: 'text-amber-600 dark:text-amber-400',   bgClass: 'bg-amber-100 dark:bg-amber-900/30' },
    { ids: [5],    label: 'Terminados',   icon: CheckCircle2, colorClass: 'text-emerald-600 dark:text-emerald-400', bgClass: 'bg-emerald-100 dark:bg-emerald-900/30' },
  ];

  // KPI metrics — Reservados solo visible para admin y of.partes
  const allMetrics = [
    { title: 'Documentos',   value: dashboard?.totales.total      ?? 0, icon: FileText,     description: `${dashboard?.totales.creadosHoy ?? 0} creados hoy`,   colorClass: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',   visible: true },
    { title: 'Pendientes',   value: dashboard?.totales.pendientes  ?? 0, icon: Clock,        description: 'Sin resolución',                                     colorClass: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',     visible: true },
    { title: 'Urgentes',     value: urgentes,                             icon: Zap,          description: 'Prioridad alta',                                     colorClass: urgentes > 0 ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-muted text-muted-foreground', visible: true },
    { title: 'Cerrados hoy', value: dashboard?.totales.cerradosHoy ?? 0, icon: CheckCircle2, description: 'Completados hoy',                                    colorClass: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400', visible: true },
    { title: 'Reservados',   value: (dashboard?.totales.reservados ?? 0), icon: Lock,          description: 'Confidenciales',                                    colorClass: 'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',   visible: puedeVerReservados },
    { title: 'Movimientos',  value: dashboard?.totales.tramites    ?? 0, icon: Activity,     description: 'En trazabilidad',                                    colorClass: 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',             visible: true },
  ];
  const metrics = allMetrics.filter((m) => m.visible);

  return (
    <div className="space-y-6 animate-fade-in">

      {/* ── Hero header ─────────────────────────────────────────── */}
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-foreground">
            {saludo}, <span className="text-primary">{nombre.split(' ')[0]}</span>.
          </h1>
          <p className="text-sm text-muted-foreground mt-0.5">{fechaCapitalizada}</p>
          <p className="text-[11px] text-muted-foreground/50 mt-0.5 tracking-wide uppercase">
            Centro de Control Operacional · SISDOC HUAP
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2.5">
          <SemaforoEjecutivo nivel={nivelSemaforo} loading={loadingDash} />
          <Link to="/documentos/nuevo" className="shrink-0">
            <Button size="sm" className="gap-2 h-9">
              <Plus className="h-3.5 w-3.5" />
              <span className="hidden sm:inline">Nuevo documento</span>
              <span className="sm:hidden">Nuevo</span>
            </Button>
          </Link>
        </div>
      </div>

      {/* ── KPI grid ─────────────────────────────────────────────── */}
      <div className={cn(
        'grid gap-4',
        puedeVerReservados
          ? 'grid-cols-2 sm:grid-cols-3 xl:grid-cols-6'
          : 'grid-cols-2 sm:grid-cols-3 xl:grid-cols-5',
      )}>
        {metrics.map((m) => (
          <MetricCard key={m.title} {...m} loading={loadingDash} variant="compact" />
        ))}
      </div>

      {/* ── Pipeline documental ──────────────────────────────────── */}
      <Card className="overflow-hidden">
        <CardHeader className="pb-2 pt-4 px-5 sm:px-6">
          <div className="flex items-center justify-between">
            <div>
              <CardTitle className="text-base font-semibold flex items-center gap-2">
                <TrendingUp className="h-4 w-4 text-primary" />
                Flujo Documental
              </CardTitle>
              <CardDescription className="text-xs">Distribución actual por etapa del proceso</CardDescription>
            </div>
            <Link to="/trazabilidad">
              <Button variant="ghost" size="sm" className="text-xs gap-1 h-8">
                Trazabilidad <ArrowRight className="h-3 w-3" />
              </Button>
            </Link>
          </div>
        </CardHeader>
        <CardContent className="pb-4 px-3 sm:px-4">
          <div className="flex items-stretch overflow-x-auto">
            {pipelineStages.map((stage, i) => (
              <PipelineStage
                key={stage.label}
                icon={stage.icon}
                label={stage.label}
                count={getCount(stage.ids)}
                colorClass={stage.colorClass}
                bgClass={stage.bgClass}
                isLast={i === pipelineStages.length - 1}
                loading={loadingDash}
              />
            ))}
          </div>
        </CardContent>
      </Card>

      {/* ── Charts ───────────────────────────────────────────────── */}
      <div className="grid grid-cols-1 xl:grid-cols-5 gap-6">

        {/* Evolución mensual — AreaChart */}
        <Card className="xl:col-span-3">
          <CardHeader>
            <CardTitle className="text-base">Evolución documental</CardTitle>
            <CardDescription>Últimos 6 meses</CardDescription>
          </CardHeader>
          <CardContent>
            {loadingDash ? (
              <div className="h-56 flex items-end gap-2 px-2">
                {[40, 65, 50, 80, 55, 70].map((h, i) => (
                  <Skeleton key={i} className="flex-1 rounded-t rounded-b-none" style={{ height: `${h}%` }} />
                ))}
              </div>
            ) : (dashboard?.porMes ?? []).length === 0 ? (
              <div className="h-56 flex items-center justify-center text-muted-foreground text-sm">
                Sin datos de los últimos 6 meses
              </div>
            ) : (
              <ResponsiveContainer width="100%" height={220}>
                <AreaChart data={dashboard?.porMes ?? []} margin={{ top: 5, right: 5, left: -20, bottom: 0 }}>
                  <defs>
                    <linearGradient id="dashGrad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%"  stopColor="hsl(var(--primary))" stopOpacity={0.22} />
                      <stop offset="95%" stopColor="hsl(var(--primary))" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                  <XAxis dataKey="mes" tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} />
                  <YAxis tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} allowDecimals={false} />
                  <Tooltip
                    contentStyle={{ backgroundColor: 'hsl(var(--card))', border: '1px solid hsl(var(--border))', borderRadius: 8, fontSize: 12 }}
                    cursor={{ stroke: 'hsl(var(--primary))', strokeWidth: 1, strokeDasharray: '4 4' }}
                  />
                  <Area
                    type="monotone"
                    dataKey="cantidad"
                    name="Documentos"
                    stroke="hsl(var(--primary))"
                    strokeWidth={2.5}
                    fill="url(#dashGrad)"
                    dot={{ fill: 'hsl(var(--primary))', strokeWidth: 2, r: 3 }}
                    activeDot={{ r: 5, strokeWidth: 2 }}
                  />
                </AreaChart>
              </ResponsiveContainer>
            )}
          </CardContent>
        </Card>

        {/* Distribución por estado */}
        <Card className="xl:col-span-2">
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2">
              <PieIcon className="h-4 w-4 text-muted-foreground" />
              Por estado
            </CardTitle>
            <CardDescription>Distribución actual</CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            {loadingDash ? (
              <>
                <Skeleton className="h-[110px] w-full rounded-lg mb-2" />
                {Array.from({ length: 3 }).map((_, i) => (
                  <div key={i} className="flex items-center gap-3">
                    <Skeleton className="h-2 w-2 rounded-full shrink-0" />
                    <Skeleton className="h-2.5 flex-1" />
                    <Skeleton className="h-2.5 w-8" />
                  </div>
                ))}
              </>
            ) : (
              <>
                <ResponsiveContainer width="100%" height={110}>
                  <PieChart>
                    <Pie data={dashboard?.porEstado ?? []} dataKey="cantidad" nameKey="desc_estado_documento" cx="50%" cy="50%" outerRadius={48} innerRadius={28}>
                      {(dashboard?.porEstado ?? []).map((e, i) => (
                        <Cell key={i} fill={ESTADO_COLORES[e.id_estado_documento] ?? '#94a3b8'} />
                      ))}
                    </Pie>
                    <Tooltip
                      formatter={(v: number) => [v, 'Docs']}
                      contentStyle={{ fontSize: 12, borderRadius: 8, backgroundColor: 'hsl(var(--card))', border: '1px solid hsl(var(--border))' }}
                    />
                  </PieChart>
                </ResponsiveContainer>
                <div className="space-y-2">
                  {(dashboard?.porEstado ?? []).map((estado) => {
                    const color = ESTADO_COLORES[estado.id_estado_documento] ?? '#94a3b8';
                    const total = dashboard?.totales.total ?? 1;
                    const pct   = Math.round((estado.cantidad / total) * 100);
                    return (
                      <div key={estado.id_estado_documento} className="space-y-1">
                        <div className="flex items-center justify-between text-xs">
                          <div className="flex items-center gap-2">
                            <span className="h-2 w-2 rounded-full shrink-0" style={{ backgroundColor: color }} />
                            <span className="text-foreground">{estado.desc_estado_documento ?? 'Sin estado'}</span>
                          </div>
                          <span className="text-muted-foreground font-medium tabular-nums">{estado.cantidad}</span>
                        </div>
                        <div className="h-1.5 w-full rounded-full bg-muted overflow-hidden">
                          <div className="h-full rounded-full transition-all duration-700" style={{ width: `${pct}%`, backgroundColor: color }} />
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

      {/* ── Actividad reciente ───────────────────────────────────── */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between pb-3">
          <div>
            <CardTitle className="text-base flex items-center gap-2">
              <Activity className="h-4 w-4 text-muted-foreground" />
              Actividad reciente
            </CardTitle>
            <CardDescription>Últimas acciones del sistema</CardDescription>
          </div>
          <Link to="/documentos">
            <Button variant="ghost" size="sm" className="gap-1 text-xs h-8">
              Ver todos <ArrowRight className="h-3 w-3" />
            </Button>
          </Link>
        </CardHeader>
        <CardContent>
          {loadingActividad ? (
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
            <p className="text-center text-sm text-muted-foreground py-8">Sin actividad reciente</p>
          ) : (
            <div className="space-y-3">
              {(actividad ?? []).map((item) => {
                const badgeConfig = ACCION_BADGE[item.accion ?? ''] ?? ACCION_BADGE.MOVIMIENTO;
                return (
                  <div key={item.id_historial} className="flex items-start gap-3 py-0.5">
                    <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                      {(item.nombres_fun ?? item.usuario ?? '?')[0]?.toUpperCase()}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex flex-wrap items-start justify-between gap-x-2">
                        <p className="text-sm font-medium text-foreground truncate flex-1 min-w-0">
                          {truncate(item.asunto ?? 'Sin asunto', 55)}
                        </p>
                        <span className="text-xs text-muted-foreground whitespace-nowrap shrink-0">
                          {formatRelativo(item.fecha)}
                        </span>
                      </div>
                      <div className="flex items-center gap-2 mt-0.5 flex-wrap">
                        <Badge variant={badgeConfig.variant}>{badgeConfig.label}</Badge>
                        {item.num_documento && <span className="text-xs text-muted-foreground font-mono">#{item.num_documento}</span>}
                        <span className="text-xs text-muted-foreground">por {item.nombres_fun ?? item.usuario ?? 'Sistema'}</span>
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
