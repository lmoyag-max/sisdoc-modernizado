import {
  FileText, Send, CheckCircle2, GitBranch, XCircle,
  Package, Clock, User, Building2, ChevronRight, MessageSquare,
} from 'lucide-react';
import { formatFechaHora, formatRelativo, cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';

export interface TramiteEvento {
  idSeguimiento:   number;
  idDocumento:     number;
  estadoTramite:   { id: number | null; descripcion: string | null };
  procedencia:     { id: number | null; descripcion: string | null; tipo: string | null };
  destino:         { id: number | null; descripcion: string | null; tipo: string | null };
  tipoDistribucion:{ id: number | null; descripcion: string | null };
  tipoCompromiso:  { id: number | null; descripcion: string | null };
  diasCompromiso:  number | null;
  observaciones:   string | null;
  fechaSistema:    string | null;
  fechaDespacho:   string | null;
  fechaRecepcion:  string | null;
  usuario:         { usuario: string | null; nombre: string | null };
}

interface EstadoCfg {
  label:  string;
  icon:   React.ComponentType<{ className?: string }>;
  dot:    string;
  badge:  string;
  color:  string;
  accion: string;
}

const ESTADO_CFG: Record<number, EstadoCfg> = {
  1: {
    label: 'Generado',     accion: 'Documento generado',
    icon:   FileText,      dot:   'bg-indigo-500',
    badge:  'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
    color:  'text-indigo-600 dark:text-indigo-400',
  },
  2: {
    label: 'Despachado',   accion: 'Documento despachado',
    icon:   Send,          dot:   'bg-amber-500',
    badge:  'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    color:  'text-amber-600 dark:text-amber-400',
  },
  3: {
    label: 'Recepcionado', accion: 'Documento recepcionado',
    icon:   CheckCircle2,  dot:   'bg-emerald-500',
    badge:  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    color:  'text-emerald-600 dark:text-emerald-400',
  },
  4: {
    label: 'Derivado',     accion: 'Documento derivado',
    icon:   GitBranch,     dot:   'bg-blue-500',
    badge:  'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    color:  'text-blue-600 dark:text-blue-400',
  },
  5: {
    label: 'Cerrado',      accion: 'Documento cerrado',
    icon:   XCircle,       dot:   'bg-slate-500',
    badge:  'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    color:  'text-slate-600 dark:text-slate-400',
  },
  6: {
    label: 'Entregado',    accion: 'Documento entregado',
    icon:   Package,       dot:   'bg-teal-500',
    badge:  'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
    color:  'text-teal-600 dark:text-teal-400',
  },
};

// ── Variante compacta (para el sidebar del detalle) ───────────
function EventoCompacto({ ev, isLast }: { ev: TramiteEvento; isLast: boolean }) {
  const cfg = ESTADO_CFG[ev.estadoTramite?.id ?? 0];
  if (!cfg) return null;
  const Icon = cfg.icon;

  const mismoLugar = ev.procedencia?.id === ev.destino?.id &&
                     ev.procedencia?.descripcion === ev.destino?.descripcion;

  return (
    <div className={cn('relative flex gap-3', !isLast && 'pb-5')}>
      {/* Línea vertical */}
      {!isLast && (
        <div className="absolute left-[13px] top-7 bottom-0 w-0.5 bg-border" />
      )}
      {/* Dot */}
      <div className={cn('relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full', cfg.dot)}>
        <Icon className="h-3.5 w-3.5 text-white" />
      </div>
      {/* Contenido */}
      <div className="flex-1 min-w-0 pt-0.5">
        <span className={cn('text-xs font-semibold', cfg.color)}>{cfg.label}</span>
        {!mismoLugar && ev.procedencia?.descripcion && (
          <p className="text-[11px] text-muted-foreground mt-0.5 flex items-center gap-1 flex-wrap">
            <span className="truncate max-w-[90px]">{ev.procedencia.descripcion}</span>
            <ChevronRight className="h-2.5 w-2.5 shrink-0" />
            <span className="font-medium text-foreground truncate max-w-[90px]">{ev.destino?.descripcion ?? '—'}</span>
          </p>
        )}
        {mismoLugar && ev.destino?.descripcion && (
          <p className="text-[11px] text-muted-foreground mt-0.5 truncate">{ev.destino.descripcion}</p>
        )}
        <p className="text-[10px] text-muted-foreground/60 mt-0.5 flex items-center gap-1">
          {ev.usuario?.nombre && <span>{ev.usuario.nombre.split(' ')[0]}</span>}
          {ev.usuario?.nombre && <span>·</span>}
          <span>{formatRelativo(ev.fechaSistema)}</span>
        </p>
      </div>
    </div>
  );
}

// ── Variante completa (para TrazabilidadPage y modal ampliado) ─
function EventoCompleto({ ev, isLast, index }: { ev: TramiteEvento; isLast: boolean; index: number }) {
  const cfg = ESTADO_CFG[ev.estadoTramite?.id ?? 0];
  if (!cfg) return null;
  const Icon = cfg.icon;

  const mismoLugar = ev.procedencia?.id === ev.destino?.id &&
                     ev.procedencia?.descripcion === ev.destino?.descripcion;
  const esExterno = ev.procedencia?.tipo === 'E' || ev.destino?.tipo === 'E';

  return (
    <div className={cn('relative flex gap-4', !isLast && 'pb-8')}>
      {!isLast && (
        <div className="absolute left-[19px] top-10 bottom-0 w-0.5 bg-border" />
      )}

      {/* Dot grande */}
      <div className={cn('relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full ring-4 ring-background', cfg.dot)}>
        <Icon className="h-4.5 w-4.5 text-white" />
      </div>

      {/* Card del evento */}
      <div className="flex-1 min-w-0 rounded-xl border border-border bg-card p-3.5 shadow-sm">
        {/* Cabecera */}
        <div className="flex items-start justify-between gap-2 flex-wrap">
          <div className="flex items-center gap-2">
            <span className={cn('text-xs font-bold px-2 py-0.5 rounded-full', cfg.badge)}>
              {cfg.label}
            </span>
            {esExterno && (
              <Badge variant="outline" className="text-[10px] py-0 h-4">Externo</Badge>
            )}
            <span className="text-[10px] text-muted-foreground/60 font-mono">#{index + 1}</span>
          </div>
          <div className="flex flex-col items-end text-[11px] text-muted-foreground/70">
            <span>{ev.fechaSistema ? formatFechaHora(ev.fechaSistema) : '—'}</span>
            <span className="text-[10px]">{formatRelativo(ev.fechaSistema)}</span>
          </div>
        </div>

        {/* Origen → Destino */}
        {!mismoLugar && (ev.procedencia?.descripcion || ev.destino?.descripcion) && (
          <div className="flex items-center gap-1.5 mt-2 text-xs text-muted-foreground flex-wrap">
            <Building2 className="h-3 w-3 shrink-0 text-muted-foreground/50" />
            <span className="max-w-[160px] truncate">{ev.procedencia?.descripcion ?? '—'}</span>
            <ChevronRight className="h-3 w-3 shrink-0 text-muted-foreground/40" />
            <span className="font-medium text-foreground max-w-[160px] truncate">{ev.destino?.descripcion ?? '—'}</span>
          </div>
        )}
        {mismoLugar && ev.destino?.descripcion && (
          <div className="flex items-center gap-1.5 mt-2 text-xs text-muted-foreground">
            <Building2 className="h-3 w-3 shrink-0" />
            <span>{ev.destino.descripcion}</span>
          </div>
        )}

        {/* Usuario */}
        <div className="flex items-center gap-3 mt-2 text-[11px] text-muted-foreground flex-wrap">
          <span className="flex items-center gap-1">
            <User className="h-3 w-3" />
            {ev.usuario?.nombre ?? ev.usuario?.usuario ?? 'Sistema'}
            {ev.usuario?.usuario && (
              <span className="opacity-60">(@{ev.usuario.usuario})</span>
            )}
          </span>

          {ev.fechaDespacho && (
            <span className="flex items-center gap-1 text-amber-600">
              <Send className="h-3 w-3" />Despacho: {formatFechaHora(ev.fechaDespacho)}
            </span>
          )}
          {ev.fechaRecepcion && (
            <span className="flex items-center gap-1 text-emerald-600">
              <CheckCircle2 className="h-3 w-3" />Recepción: {formatFechaHora(ev.fechaRecepcion)}
            </span>
          )}
        </div>

        {/* Distribución / Compromiso */}
        {(ev.tipoDistribucion?.descripcion || (ev.tipoCompromiso?.id !== 1 && (ev.diasCompromiso ?? 0) > 0)) && (
          <div className="flex items-center gap-2 mt-1.5 text-[11px] text-muted-foreground flex-wrap">
            {ev.tipoDistribucion?.descripcion && (
              <span className="px-1.5 py-0.5 rounded bg-muted text-muted-foreground">
                {ev.tipoDistribucion.descripcion}
              </span>
            )}
            {ev.tipoCompromiso?.id !== 1 && (ev.diasCompromiso ?? 0) > 0 && (
              <span className="text-amber-600">
                {ev.tipoCompromiso?.descripcion} — {ev.diasCompromiso} días
              </span>
            )}
          </div>
        )}

        {/* Observaciones */}
        {ev.observaciones && ev.observaciones.trim() && (
          <p className="mt-2 text-[11px] text-muted-foreground italic flex items-start gap-1 border-t border-border pt-2">
            <MessageSquare className="h-3 w-3 shrink-0 mt-0.5 text-muted-foreground/50" />
            "{ev.observaciones}"
          </p>
        )}
      </div>
    </div>
  );
}

// ── Skeletons ─────────────────────────────────────────────────

export function TrazabilidadSkeleton({ count = 3 }: { count?: number }) {
  return (
    <div className="space-y-5">
      {Array.from({ length: count }).map((_, i) => (
        <div key={i} className="flex gap-4">
          <Skeleton className="h-10 w-10 rounded-full shrink-0" />
          <div className="flex-1 space-y-2">
            <Skeleton className="h-4 w-24" />
            <Skeleton className="h-3 w-48" />
            <Skeleton className="h-3 w-32" />
          </div>
        </div>
      ))}
    </div>
  );
}

// ── Componente principal ──────────────────────────────────────

interface Props {
  eventos:  TramiteEvento[];
  variante?: 'compacto' | 'completo';
  className?: string;
}

export function TrazabilidadTimeline({ eventos, variante = 'completo', className }: Props) {
  if (eventos.length === 0) {
    return (
      <div className="py-10 text-center text-muted-foreground">
        <Clock className="h-8 w-8 mx-auto mb-2 text-muted-foreground/40" />
        <p className="text-sm">Sin movimientos registrados</p>
      </div>
    );
  }

  return (
    <div className={cn('relative', className)}>
      {variante === 'compacto' ? (
        <div className="pl-1">
          {eventos.map((ev, idx) => (
            <EventoCompacto key={ev.idSeguimiento} ev={ev} isLast={idx === eventos.length - 1} />
          ))}
        </div>
      ) : (
        <div>
          {eventos.map((ev, idx) => (
            <EventoCompleto key={ev.idSeguimiento} ev={ev} isLast={idx === eventos.length - 1} index={idx} />
          ))}
        </div>
      )}
    </div>
  );
}
