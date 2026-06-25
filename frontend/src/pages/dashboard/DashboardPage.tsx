import { useState, useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  FileText, Clock, CheckCircle2, Activity, ArrowRight,
  Lock, Send, Inbox, Plus, TrendingUp, Zap, ChevronRight,
  PieChart as PieIcon,
  Sparkles, CalendarDays,
} from 'lucide-react';
import {
  AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell,
} from 'recharts';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { reportesApi } from '@/lib/api/reportes.api';
import { useAuthStore, displayName } from '@/stores/auth.store';
import { useRole } from '@/hooks/useRole';
import { formatRelativo, truncate, cn, iniciales } from '@/lib/utils';
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

// Acento visual (icono 3D + glow) asociado a cada variante de badge
const ACCION_ACCENT: Record<string, string> = {
  info: 'sky', warning: 'amber', success: 'emerald', secondary: 'slate',
};

// ── Paleta de acentos para iconos 3D / glow de tarjetas ─────────
const ACCENTS: Record<string, { icon3d: string; glow: string; border: string }> = {
  indigo:  { icon3d: 'icon-3d-indigo',  glow: 'rgba(79, 70, 229, .35)',  border: 'hsl(239 84% 60% / .35)' },
  amber:   { icon3d: 'icon-3d-amber',   glow: 'rgba(217, 119, 6, .30)',  border: 'hsl(38 92% 50% / .35)' },
  red:     { icon3d: 'icon-3d-red',     glow: 'rgba(220, 38, 38, .30)',  border: 'hsl(0 84% 60% / .35)' },
  emerald: { icon3d: 'icon-3d-emerald', glow: 'rgba(5, 150, 105, .30)',  border: 'hsl(160 84% 39% / .35)' },
  violet:  { icon3d: 'icon-3d-violet',  glow: 'rgba(124, 58, 237, .30)', border: 'hsl(265 89% 62% / .35)' },
  sky:     { icon3d: 'icon-3d-sky',     glow: 'rgba(2, 132, 199, .30)',  border: 'hsl(199 89% 48% / .35)' },
  slate:   { icon3d: 'icon-3d-slate',   glow: 'rgba(100, 116, 139, .25)', border: 'hsl(220 13% 60% / .3)' },
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
    <div className={cn('flex items-center gap-3 rounded-xl border px-4 py-3 backdrop-blur-sm transition-all', cfg.borderClass, cfg.bgClass)}>
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

// ── Tarjeta KPI premium (exclusiva del Dashboard) ───────────────
interface KpiDelta { label: string; tone: 'up' | 'warn'; }

interface DashboardKpiCardProps {
  title: string;
  value: number | string;
  icon: React.ComponentType<{ className?: string }>;
  description?: string;
  accent: string;
  delta?: KpiDelta;
  loading?: boolean;
  style?: React.CSSProperties;
}

function DashboardKpiCard({ title, value, icon: Icon, description, accent, delta, loading, style }: DashboardKpiCardProps) {
  const a = ACCENTS[accent] ?? ACCENTS.slate;

  if (loading) {
    return (
      <Card className="kpi-premium border-0">
        <CardContent className="pt-4 pb-4 px-4 sm:px-5">
          <div className="flex items-start justify-between mb-3">
            <div className="h-11 w-11 animate-pulse rounded-2xl bg-muted" />
          </div>
          <div className="h-7 w-16 animate-pulse rounded bg-muted mb-2" />
          <div className="h-3 w-20 animate-pulse rounded bg-muted" />
        </CardContent>
      </Card>
    );
  }

  return (
    <div
      className="kpi-premium p-4 sm:p-5 animate-fade-in-up"
      style={{ '--kpi-glow': a.glow, '--kpi-border': a.border, ...style } as React.CSSProperties}
    >
      <div className="flex items-start justify-between gap-2">
        <div className={cn('icon-3d h-11 w-11 sm:h-12 sm:w-12 shrink-0', a.icon3d)}>
          <Icon className="h-5 w-5 text-white drop-shadow-sm relative z-10" aria-hidden="true" />
        </div>
        {delta && (
          <span className={cn(
            'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap',
            delta.tone === 'up'
              ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400'
              : 'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400',
          )}>
            <span className="h-1.5 w-1.5 rounded-full bg-current shrink-0" />
            {delta.label}
          </span>
        )}
      </div>
      <p className="mt-3 text-2xl sm:text-[1.7rem] font-bold tracking-tight text-foreground tabular-nums animate-number leading-none">
        {typeof value === 'number' ? value.toLocaleString('es-CL') : value}
      </p>
      <p className="mt-1.5 text-sm font-semibold text-foreground/85 truncate">{title}</p>
      {description && <p className="text-xs text-muted-foreground mt-0.5 truncate">{description}</p>}
    </div>
  );
}

// ── Pipeline documental ─────────────────────────────────────────
interface PipelineStageProps {
  icon: React.ComponentType<{ className?: string }>;
  label: string;
  count: number;
  accent: string;
  isLast?: boolean;
  loading?: boolean;
}

function PipelineStage({ icon: Icon, label, count, accent, isLast, loading }: PipelineStageProps) {
  const a = ACCENTS[accent] ?? ACCENTS.slate;
  return (
    <div className="flex items-center flex-1 min-w-0">
      <div className="flex-1 min-w-0 flex flex-col items-center gap-2.5 p-3 sm:p-4 group">
        <div className={cn('icon-3d h-12 w-12 sm:h-14 sm:w-14 shrink-0 transition-transform duration-300 group-hover:-translate-y-1', a.icon3d)}>
          <Icon className="h-5 w-5 sm:h-6 sm:w-6 text-white drop-shadow relative z-10" />
        </div>
        <div className="text-center min-w-0">
          {loading
            ? <Skeleton className="h-6 w-10 mx-auto mb-1" />
            : <p className="text-xl sm:text-2xl font-bold tabular-nums animate-number text-foreground">{count.toLocaleString('es-CL')}</p>
          }
          <p className="text-xs text-muted-foreground font-medium truncate">{label}</p>
        </div>
      </div>
      {!isLast && (
        <div className="hidden sm:flex items-center px-1 shrink-0 pipeline-arrow">
          <ChevronRight className="h-5 w-5 animate-arrow-float" />
        </div>
      )}
    </div>
  );
}

// ── Tooltips ejecutivos para gráficos ───────────────────────────
function AreaChartTooltip({ active, payload, label }: { active?: boolean; payload?: Array<{ value: number }>; label?: string }) {
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

function PieChartTooltip({ active, payload }: { active?: boolean; payload?: Array<{ name: string; value: number; payload: { id_estado_documento: number } }> }) {
  if (!active || !payload?.length) return null;
  const item = payload[0];
  const color = ESTADO_COLORES[item.payload.id_estado_documento] ?? '#94a3b8';
  return (
    <div className="rounded-xl border border-border bg-card/95 backdrop-blur-sm px-3 py-2 shadow-lg">
      <p className="text-xs font-semibold text-foreground flex items-center gap-1.5">
        <span className="inline-block h-2 w-2 rounded-full shrink-0" style={{ backgroundColor: color }} />
        {item.name}
      </p>
      <p className="text-xs text-muted-foreground mt-0.5">{item.value?.toLocaleString('es-CL')} docs</p>
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

  // Reloj en vivo del header — puramente decorativo, no afecta ninguna query ni regla de negocio
  const [horaActual, setHoraActual] = useState(() => format(new Date(), 'HH:mm'));
  useEffect(() => {
    const id = setInterval(() => setHoraActual(format(new Date(), 'HH:mm')), 30_000);
    return () => clearInterval(id);
  }, []);

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
    { ids: [1, 2], label: 'Despachados',   icon: Send,         accent: 'indigo' },
    { ids: [3],    label: 'Recepcionados', icon: Inbox,        accent: 'sky' },
    { ids: [4],    label: 'En Proceso',    icon: Activity,     accent: 'amber' },
    { ids: [5],    label: 'Terminados',    icon: CheckCircle2, accent: 'emerald' },
  ];

  const creadosHoy  = dashboard?.totales.creadosHoy  ?? 0;
  const cerradosHoy = dashboard?.totales.cerradosHoy ?? 0;

  // KPI metrics — Reservados solo visible para admin y of.partes
  const allMetrics = [
    {
      key: 'documentos', title: 'Documentos', value: dashboard?.totales.total ?? 0, icon: FileText,
      description: `${creadosHoy} creados hoy`, accent: 'indigo',
      delta: creadosHoy > 0 ? { label: `+${creadosHoy} hoy`, tone: 'up' as const } : undefined,
      visible: true,
    },
    {
      key: 'pendientes', title: 'Pendientes', value: dashboard?.totales.pendientes ?? 0, icon: Clock,
      description: 'Sin resolución', accent: 'amber', delta: undefined, visible: true,
    },
    {
      key: 'urgentes', title: 'Urgentes', value: urgentes, icon: Zap,
      description: 'Prioridad alta', accent: urgentes > 0 ? 'red' : 'slate',
      delta: urgentes > 0 ? { label: 'Atención', tone: 'warn' as const } : { label: 'Bajo control', tone: 'up' as const },
      visible: true,
    },
    {
      key: 'cerrados', title: 'Cerrados hoy', value: cerradosHoy, icon: CheckCircle2,
      description: 'Completados hoy', accent: 'emerald',
      delta: cerradosHoy > 0 ? { label: `+${cerradosHoy}`, tone: 'up' as const } : undefined,
      visible: true,
    },
    {
      key: 'reservados', title: 'Reservados', value: dashboard?.totales.reservados ?? 0, icon: Lock,
      description: 'Confidenciales', accent: 'violet', delta: undefined, visible: puedeVerReservados,
    },
    {
      key: 'movimientos', title: 'Movimientos', value: dashboard?.totales.tramites ?? 0, icon: Activity,
      description: 'En trazabilidad', accent: 'sky', delta: undefined, visible: true,
    },
  ];
  const metrics = allMetrics.filter((m) => m.visible);

  return (
    <div className="space-y-6 animate-fade-in">

      {/* ── Hero header premium ──────────────────────────────────── */}
      <div className="hero-glass flex flex-wrap items-center justify-between gap-5 px-5 sm:px-6 py-5">
        <div className="flex items-center gap-4 min-w-0">
          <Avatar className="h-12 w-12 sm:h-14 sm:w-14 shrink-0 border-2 border-white/50 dark:border-white/10 shadow-lg">
            <AvatarFallback className="bg-gradient-to-br from-primary to-accent text-primary-foreground text-base font-bold">
              {iniciales(nombre)}
            </AvatarFallback>
          </Avatar>
          <div className="min-w-0">
            <h1 className="text-xl sm:text-2xl font-bold text-foreground leading-tight">
              {saludo}, <span className="text-gradient-huap">{nombre.split(' ')[0]}</span>
            </h1>
            <div className="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-xs sm:text-sm text-muted-foreground">
              <span className="flex items-center gap-1.5">
                <CalendarDays className="h-3.5 w-3.5 shrink-0" />
                {fechaCapitalizada}
              </span>
              <span className="flex items-center gap-1.5">
                <span className="live-dot" />
                {horaActual} hrs
              </span>
            </div>
            <p className="text-[11px] text-muted-foreground/50 mt-1 tracking-wide uppercase flex items-center gap-1.5">
              <Sparkles className="h-3 w-3 shrink-0" />
              Centro de Control Operacional · DOC360 HUAP
            </p>
          </div>
        </div>
        <div className="flex flex-wrap items-center gap-2.5">
          <SemaforoEjecutivo nivel={nivelSemaforo} loading={loadingDash} />
          <Link to="/documentos/nuevo" className="shrink-0">
            <Button size="sm" className="btn-premium gap-2 h-10 px-4 text-sm border-0 text-primary-foreground">
              <Plus className="h-4 w-4 relative z-10" />
              <span className="hidden sm:inline relative z-10">Nuevo documento</span>
              <span className="sm:hidden relative z-10">Nuevo</span>
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
        {metrics.map((m, i) => (
          <DashboardKpiCard
            key={m.key}
            title={m.title}
            value={m.value}
            icon={m.icon}
            description={m.description}
            accent={m.accent}
            delta={m.delta}
            loading={loadingDash}
            style={{ animationDelay: `${i * 60}ms` }}
          />
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
                accent={stage.accent}
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
                      <stop offset="5%"  stopColor="hsl(var(--primary))" stopOpacity={0.32} />
                      <stop offset="50%" stopColor="hsl(var(--primary))" stopOpacity={0.08} />
                      <stop offset="95%" stopColor="hsl(var(--primary))" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                  <XAxis dataKey="mes" tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} />
                  <YAxis tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }} allowDecimals={false} />
                  <Tooltip content={<AreaChartTooltip />} cursor={{ stroke: 'hsl(var(--primary))', strokeWidth: 1, strokeDasharray: '4 4' }} />
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
                <div className="relative">
                  <ResponsiveContainer width="100%" height={120}>
                    <PieChart>
                      <defs>
                        {(dashboard?.porEstado ?? []).map((e, i) => {
                          const color = ESTADO_COLORES[e.id_estado_documento] ?? '#94a3b8';
                          return (
                            <linearGradient id={`pieGrad${i}`} key={i} x1="0" y1="0" x2="1" y2="1">
                              <stop offset="0%" stopColor={color} stopOpacity={1} />
                              <stop offset="100%" stopColor={color} stopOpacity={0.65} />
                            </linearGradient>
                          );
                        })}
                      </defs>
                      <Pie
                        data={dashboard?.porEstado ?? []}
                        dataKey="cantidad"
                        nameKey="desc_estado_documento"
                        cx="50%" cy="50%"
                        outerRadius={56}
                        innerRadius={36}
                        paddingAngle={3}
                        cornerRadius={6}
                        stroke="none"
                      >
                        {(dashboard?.porEstado ?? []).map((_, i) => (
                          <Cell key={i} fill={`url(#pieGrad${i})`} />
                        ))}
                      </Pie>
                      <Tooltip content={<PieChartTooltip />} />
                    </PieChart>
                  </ResponsiveContainer>
                  <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span className="text-lg font-bold text-foreground tabular-nums">
                      {(dashboard?.totales.total ?? 0).toLocaleString('es-CL')}
                    </span>
                    <span className="text-[9px] text-muted-foreground uppercase tracking-wide">Total</span>
                  </div>
                </div>
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
            <div className="timeline-rail">
              {(actividad ?? []).map((item, i) => {
                const badgeConfig = ACCION_BADGE[item.accion ?? ''] ?? ACCION_BADGE.MOVIMIENTO;
                const accent = ACCENTS[ACCION_ACCENT[badgeConfig.variant] ?? 'slate'];
                return (
                  <div
                    key={item.id_historial}
                    className="relative flex items-start gap-3 py-2 pl-7 rounded-lg transition-colors duration-150 hover:bg-muted/40 animate-fade-in-up"
                    style={{ animationDelay: `${i * 50}ms` }}
                  >
                    <span className={cn('icon-3d absolute left-0 top-2 flex h-7 w-7 shrink-0 items-center justify-center ring-4 ring-card', accent.icon3d)}>
                      <span className="relative z-10 text-[10px] font-bold text-white">
                        {(item.nombres_fun ?? item.usuario ?? '?')[0]?.toUpperCase()}
                      </span>
                    </span>
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
