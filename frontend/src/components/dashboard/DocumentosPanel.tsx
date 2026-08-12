import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, keepPreviousData } from '@tanstack/react-query';
import {
  RefreshCw, Search, ChevronLeft, ChevronRight, ArrowRight,
  Lock, Paperclip, AlertTriangle, Clock, Zap,
} from 'lucide-react';
import {
  Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetCloseButton, SheetFooter,
} from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { EmptyState } from '@/components/shared/EmptyState';
import { documentosApi, type FiltrosDocumento, type OrdenDocumentos } from '@/lib/api/documentos.api';
import { catalogosApi } from '@/lib/api/catalogos.api';
import { useEstadosDocumento, getEstadoMeta } from '@/lib/estadoDocumento';
import { useDebounce } from '@/hooks/useDebounce';
import { useAuthStore } from '@/stores/auth.store';
import { useRole } from '@/hooks/useRole';
import { cn, formatFechaHora } from '@/lib/utils';

interface DocumentosPanelProps {
  /** id_estado_documento seleccionado, o null si el panel está cerrado */
  idEstado: number | null;
  onClose: () => void;
}

const selCls = (className?: string) => cn(
  'h-8 rounded-md border border-input bg-transparent px-2 text-xs shadow-sm',
  'focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
  className,
);

export function DocumentosPanel({ idEstado, onClose }: DocumentosPanelProps) {
  const navigate = useNavigate();
  const { isAdmin } = useRole();
  const todosServicios = useAuthStore((s) => s.user?.todosServicios === true);
  const fullAccess = isAdmin || todosServicios;
  const { getLabel } = useEstadosDocumento();

  const [search, setSearch]               = useState('');
  const debouncedSearch                    = useDebounce(search, 300);
  const [idTipo, setIdTipo]                = useState<number | undefined>();
  const [idDependencia, setIdDependencia]  = useState<number | undefined>();
  const [soloAtrasados, setSoloAtrasados]  = useState(false);
  const [proximoAVencer, setProximoAVencer] = useState(false);
  const [orden, setOrden]                  = useState<OrdenDocumentos>('fecha_desc');
  const [pagina, setPagina]                = useState(1);

  const open = idEstado != null;

  const filtros: FiltrosDocumento = {
    idEstado: idEstado ?? undefined,
    q: debouncedSearch || undefined,
    idTipo,
    idDependencia: fullAccess ? idDependencia : undefined,
    soloAtrasados: soloAtrasados || undefined,
    proximoAVencer: proximoAVencer || undefined,
    orden,
    pagina,
    porPagina: 15,
  };

  const { data, isLoading, isFetching, isError, refetch, dataUpdatedAt } = useQuery({
    queryKey: ['documentos-panel', filtros],
    queryFn:  () => documentosApi.listar(filtros),
    enabled:  open,
    placeholderData: keepPreviousData,
    staleTime: 15_000,
  });

  const { data: tipos } = useQuery({
    queryKey: ['catalogos', 'tipos-documento'], queryFn: catalogosApi.tiposDocumento,
    staleTime: 10 * 60 * 1000, enabled: open,
  });
  const { data: dependencias } = useQuery({
    queryKey: ['catalogos', 'dependencias'], queryFn: () => catalogosApi.dependencias(),
    staleTime: 10 * 60 * 1000, enabled: open && fullAccess,
  });

  const documentos  = data?.data ?? [];
  const meta        = data?.meta;
  const resumen     = data?.resumen;
  const estadoMeta  = getEstadoMeta(idEstado);
  const Icon        = estadoMeta.icon;
  const estadoLabel = getLabel(idEstado);

  const hayFiltrosActivos = !!(search || idTipo || idDependencia || soloAtrasados || proximoAVencer);

  const resetPaginaYFiltro = <T,>(setter: (v: T) => void) => (v: T) => { setter(v); setPagina(1); };

  const limpiarFiltros = () => {
    setSearch(''); setIdTipo(undefined); setIdDependencia(undefined);
    setSoloAtrasados(false); setProximoAVencer(false); setOrden('fecha_desc'); setPagina(1);
  };

  const cerrarYResetear = () => { onClose(); limpiarFiltros(); };

  // "Ver todos" y abrir un documento navegan FUERA del dashboard — no deben llamar a
  // onClose() aquí: onClose() actualiza el ?panelEstado= de la URL del dashboard, y
  // hacerlo justo después de navigate() corre en carrera con el cambio de ruta (React
  // Router puede aplicar ese update de query string sobre la URL recién navegada y
  // perder el ?idEstado= que "Ver todos" acababa de fijar). Como DocumentosPanel se
  // desmonta junto con el Dashboard al cambiar de ruta, su estado local desaparece solo.
  const verTodos = () => {
    const params = new URLSearchParams();
    if (idEstado)       params.set('idEstado', String(idEstado));
    if (idTipo)          params.set('idTipo', String(idTipo));
    if (idDependencia)   params.set('idDependencia', String(idDependencia));
    navigate(`/documentos?${params.toString()}`);
  };

  const abrirDocumento = (id: number) => {
    navigate(`/documentos/${id}`);
  };

  return (
    <Sheet open={open} onOpenChange={(v) => { if (!v) cerrarYResetear(); }}>
      <SheetContent>
        <SheetHeader>
          <div className="flex items-start gap-3 min-w-0">
            <span className={cn('icon-3d flex h-10 w-10 shrink-0 items-center justify-center', `icon-3d-${estadoMeta.accent}`)}>
              <Icon className="h-4 w-4 text-white" aria-hidden="true" />
            </span>
            <div className="min-w-0">
              <SheetTitle>
                Documentos {estadoLabel.toLowerCase()} — {meta?.total ?? 0} resultado{meta?.total === 1 ? '' : 's'}
              </SheetTitle>
              <SheetDescription className="mt-0.5 flex items-center gap-2 flex-wrap">
                <span>Actualizado {dataUpdatedAt ? formatFechaHora(new Date(dataUpdatedAt)) : '—'}</span>
                <button
                  type="button"
                  onClick={() => refetch()}
                  className="inline-flex items-center gap-1 text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded"
                  aria-label="Refrescar resultados"
                >
                  <RefreshCw className={cn('h-3 w-3', isFetching && 'animate-spin')} aria-hidden="true" /> Refrescar
                </button>
              </SheetDescription>
            </div>
          </div>
          <SheetCloseButton />
        </SheetHeader>

        {/* Resumen de gestión — respeta los mismos permisos y filtros que la lista */}
        <div className="grid grid-cols-4 gap-2 px-5 py-3 border-b border-border shrink-0 bg-muted/20">
          <ResumenTile label="Total" value={resumen?.total ?? meta?.total ?? 0} />
          <ResumenTile label="Urgentes" value={resumen?.urgentes ?? 0} tone={resumen?.urgentes ? 'warn' : undefined} />
          <ResumenTile label="Atrasados" value={resumen?.atrasados ?? 0} tone={resumen?.atrasados ? 'bad' : undefined} />
          <ResumenTile label="Por vencer" value={resumen?.proximosAVencer ?? 0} tone={resumen?.proximosAVencer ? 'warn' : undefined} />
        </div>

        {/* Filtros dinámicos — se combinan y actualizan sin recargar el dashboard */}
        <div className="flex flex-col gap-2 px-5 py-3 border-b border-border shrink-0">
          <div className="relative">
            <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" aria-hidden="true" />
            <Input
              value={search}
              onChange={(e) => resetPaginaYFiltro(setSearch)(e.target.value)}
              placeholder="Folio, materia, responsable..."
              className="h-8 pl-8 text-xs"
              aria-label="Buscar por folio, materia o responsable"
            />
          </div>
          <div className="flex flex-wrap gap-2">
            <select
              className={selCls('w-auto')}
              value={idTipo ?? ''}
              onChange={(e) => resetPaginaYFiltro(setIdTipo)(e.target.value ? Number(e.target.value) : undefined)}
              aria-label="Filtrar por tipo de documento"
            >
              <option value="">Todos los tipos</option>
              {(tipos ?? []).map((t) => <option key={t.id} value={t.id}>{t.descripcion}</option>)}
            </select>
            {fullAccess && (
              <select
                className={selCls('w-auto')}
                value={idDependencia ?? ''}
                onChange={(e) => resetPaginaYFiltro(setIdDependencia)(e.target.value ? Number(e.target.value) : undefined)}
                aria-label="Filtrar por servicio responsable"
              >
                <option value="">Todos los servicios</option>
                {(dependencias ?? []).map((d) => <option key={d.id} value={d.id}>{d.descripcion}</option>)}
              </select>
            )}
            <select
              className={selCls('w-auto')}
              value={orden}
              onChange={(e) => setOrden(e.target.value as OrdenDocumentos)}
              aria-label="Ordenar resultados"
            >
              <option value="fecha_desc">Más recientes</option>
              <option value="fecha_asc">Más antiguos</option>
              <option value="antiguedad_desc">Mayor antigüedad</option>
              <option value="antiguedad_asc">Menor antigüedad</option>
            </select>
            <FiltroToggle active={soloAtrasados} onClick={() => resetPaginaYFiltro(setSoloAtrasados)(!soloAtrasados)} label="Solo atrasados" />
            <FiltroToggle active={proximoAVencer} onClick={() => resetPaginaYFiltro(setProximoAVencer)(!proximoAVencer)} label="Próximos a vencer" />
            {hayFiltrosActivos && (
              <button
                type="button"
                onClick={limpiarFiltros}
                className="text-xs text-muted-foreground hover:text-foreground underline underline-offset-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded"
              >
                Limpiar filtros
              </button>
            )}
          </div>
        </div>

        {/* Lista de documentos */}
        <div className={cn('flex-1 overflow-y-auto', isFetching && !isLoading && 'opacity-70 transition-opacity')}>
          {isLoading ? (
            <div className="divide-y divide-border" aria-busy="true" aria-live="polite">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="px-5 py-3 space-y-2">
                  <Skeleton className="h-4 w-3/4" />
                  <Skeleton className="h-3 w-1/2" />
                </div>
              ))}
            </div>
          ) : isError ? (
            <EmptyState
              icon={AlertTriangle}
              title="Error al consultar"
              description="No se pudo cargar la lista de documentos. Intenta nuevamente."
              action={<Button variant="outline" size="sm" onClick={() => refetch()}>Reintentar</Button>}
            />
          ) : documentos.length === 0 ? (
            <EmptyState
              title="Sin documentos"
              description={hayFiltrosActivos
                ? `No existen documentos en estado ${estadoLabel} para los filtros seleccionados.`
                : `No existen documentos en estado ${estadoLabel} en este momento.`}
              action={hayFiltrosActivos ? <Button variant="outline" size="sm" onClick={limpiarFiltros}>Limpiar filtros</Button> : undefined}
            />
          ) : (
            <ul className="divide-y divide-border" aria-live="polite">
              {documentos.map((doc) => (
                <li key={doc.idDocumento}>
                  <button
                    type="button"
                    onClick={() => abrirDocumento(doc.idDocumento)}
                    className="w-full text-left px-5 py-3 hover:bg-muted/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-inset transition-colors"
                  >
                    <div className="flex items-start justify-between gap-3">
                      <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-1.5 flex-wrap mb-0.5">
                          <span className="text-xs font-mono text-muted-foreground">{doc.numDocumento ?? `#${doc.idDocumento}`}</span>
                          {doc.tipoDocumento?.descripcion && <span className="text-xs text-muted-foreground">· {doc.tipoDocumento.descripcion}</span>}
                          {doc.reservado && <Lock className="h-3 w-3 text-violet-500 shrink-0" aria-label="Reservado" />}
                          {doc.tipoSoporte === 'F' && <Paperclip className="h-3 w-3 text-slate-400 shrink-0" aria-label="Documento físico" />}
                        </div>
                        <p className="text-sm font-medium text-foreground line-clamp-1">{doc.asunto ?? 'Sin asunto'}</p>
                        <div className="flex items-center gap-x-3 gap-y-1 mt-1 flex-wrap text-xs text-muted-foreground">
                          <span>{doc.ingresadoPor?.nombre || doc.ingresadoPor?.usuario || '—'}</span>
                          {doc.responsableActual && (
                            <span className="flex items-center gap-1"><ArrowRight className="h-3 w-3" aria-hidden="true" />{doc.responsableActual}</span>
                          )}
                          <span>{doc.fechaIngreso ? formatFechaHora(doc.fechaIngreso).split(' ')[0] : '—'}</span>
                          {doc.diasEnEstadoActual != null && (
                            <span className="flex items-center gap-1">
                              <Clock className="h-3 w-3" aria-hidden="true" />
                              {doc.diasEnEstadoActual} día{doc.diasEnEstadoActual === 1 ? '' : 's'} en este estado
                            </span>
                          )}
                        </div>
                      </div>
                      <div className="flex flex-col items-end gap-1 shrink-0">
                        {doc.urgente && <Badge variant="warning" className="gap-1"><Zap className="h-3 w-3" aria-hidden="true" />Urgente</Badge>}
                        {doc.atrasado && (
                          <Badge variant="secondary" className="bg-red-100 text-red-700 dark:bg-red-950/30 dark:text-red-400 border-transparent">
                            Atrasado
                          </Badge>
                        )}
                        {!doc.atrasado && doc.proximoAVencer && (
                          <Badge variant="secondary" className="bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border-transparent">
                            Por vencer
                          </Badge>
                        )}
                      </div>
                    </div>
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>

        <SheetFooter>
          <p className="text-xs text-muted-foreground">
            {meta && meta.total > 0
              ? `${(meta.pagina - 1) * meta.porPagina + 1}–${Math.min(meta.pagina * meta.porPagina, meta.total)} de ${meta.total}`
              : ''}
          </p>
          <div className="flex items-center gap-2">
            {meta && meta.totalPaginas > 1 && (
              <>
                <button type="button" className="pagination-pill h-7 w-7" disabled={pagina <= 1} onClick={() => setPagina((p) => p - 1)} aria-label="Página anterior">
                  <ChevronLeft className="h-3.5 w-3.5" />
                </button>
                <span className="text-xs text-muted-foreground tabular-nums px-1">{meta.pagina}/{meta.totalPaginas}</span>
                <button type="button" className="pagination-pill h-7 w-7" disabled={pagina >= meta.totalPaginas} onClick={() => setPagina((p) => p + 1)} aria-label="Página siguiente">
                  <ChevronRight className="h-3.5 w-3.5" />
                </button>
              </>
            )}
            <Button size="sm" variant="outline" onClick={verTodos} className="gap-1.5 text-xs h-8">
              Ver todos <ArrowRight className="h-3 w-3" />
            </Button>
          </div>
        </SheetFooter>
      </SheetContent>
    </Sheet>
  );
}

function ResumenTile({ label, value, tone }: { label: string; value: number; tone?: 'warn' | 'bad' }) {
  return (
    <div className="text-center">
      <p className={cn(
        'text-lg font-bold tabular-nums',
        tone === 'bad' ? 'text-red-600 dark:text-red-400' : tone === 'warn' ? 'text-amber-600 dark:text-amber-400' : 'text-foreground',
      )}>
        {value.toLocaleString('es-CL')}
      </p>
      <p className="text-[10px] text-muted-foreground uppercase tracking-wide">{label}</p>
    </div>
  );
}

function FiltroToggle({ active, onClick, label }: { active: boolean; onClick: () => void; label: string }) {
  return (
    <button
      type="button"
      onClick={onClick}
      aria-pressed={active}
      className={cn(
        'h-8 rounded-md border px-2.5 text-xs font-medium transition-colors',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
        active
          ? 'border-primary bg-primary/10 text-primary'
          : 'border-input bg-transparent text-muted-foreground hover:text-foreground',
      )}
    >
      {label}
    </button>
  );
}
