import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  GitBranch, Search, Clock, CheckCircle2, Send, RefreshCw,
  Building2, User, MessageSquare, ChevronRight, FileText,
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { useDebounce } from '@/hooks/useDebounce';
import { formatFechaHora, formatRelativo, cn } from '@/lib/utils';
import { Link } from 'react-router-dom';

interface DocResult {
  id_documento: number; materia: string | null;
  num_interno: number | null; desc_tipo_documento: string | null;
  desc_estado_documento: string | null; fecha_sistema: string | null;
}

interface TramiteEvento {
  idSeguimiento: number; idDocumento: number;
  estadoTramite: { id: number | null; descripcion: string | null };
  procedencia: { id: number | null; descripcion: string | null; tipo: string | null };
  destino: { id: number | null; descripcion: string | null; tipo: string | null };
  tipoDistribucion: { id: number | null; descripcion: string | null };
  tipoCompromiso: { id: number | null; descripcion: string | null };
  diasCompromiso: number | null;
  observaciones: string | null;
  fechaSistema: string | null;
  fechaDespacho: string | null;
  fechaRecepcion: string | null;
  usuario: { usuario: string | null; nombre: string | null };
}

const ESTADO_CFG: Record<number, { label: string; icon: React.ComponentType<{ className?: string }>; dot: string; color: string }> = {
  1: { label: 'Generado',     icon: FileText,    dot: 'bg-indigo-500',  color: 'text-indigo-600' },
  2: { label: 'Despachado',   icon: Send,        dot: 'bg-amber-500',   color: 'text-amber-600' },
  3: { label: 'Recepcionado', icon: CheckCircle2,dot: 'bg-emerald-500', color: 'text-emerald-600' },
  4: { label: 'Derivado',     icon: GitBranch,   dot: 'bg-blue-500',    color: 'text-blue-600' },
  5: { label: 'Cerrado',      icon: CheckCircle2,dot: 'bg-slate-400',   color: 'text-slate-500' },
  6: { label: 'Entregado',    icon: CheckCircle2,dot: 'bg-teal-500',    color: 'text-teal-600' },
};

export function TrazabilidadPage() {
  const [search, setSearch] = useState('');
  const [selectedDoc, setSelectedDoc] = useState<DocResult | null>(null);
  const debouncedSearch = useDebounce(search, 400);

  const { data: resultados, isLoading: loadingSearch } = useQuery({
    queryKey: ['trazabilidad-busqueda', debouncedSearch],
    queryFn: async () => {
      if (debouncedSearch.length < 2) return [];
      const { data } = await apiClient.get<{ data: { documentos: DocResult[] } }>(
        `/busqueda?q=${encodeURIComponent(debouncedSearch)}&tipo=documentos&porPagina=10`
      );
      return data.data.documentos ?? [];
    },
    enabled: debouncedSearch.length >= 2,
  });

  const { data: trazabilidad, isLoading: loadingTraz } = useQuery({
    queryKey: ['trazabilidad-detalle', selectedDoc?.id_documento],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: TramiteEvento[] }>(
        `/documentos/${selectedDoc!.id_documento}/trazabilidad`
      );
      return data.data;
    },
    enabled: !!selectedDoc,
  });

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex items-center gap-3">
        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
          <GitBranch className="h-5 w-5 text-primary" />
        </div>
        <div>
          <h1 className="text-2xl font-bold">Trazabilidad Documental</h1>
          <p className="text-sm text-muted-foreground">Historial completo de movimientos</p>
        </div>
      </div>

      {/* Búsqueda */}
      <Card>
        <CardContent className="pt-4 pb-4">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input value={search}
              onChange={(e) => { setSearch(e.target.value); if (!e.target.value) setSelectedDoc(null); }}
              placeholder="Buscar por materia o número de documento..." className="pl-9" autoFocus />
          </div>

          {debouncedSearch.length >= 2 && (
            <div className="mt-3 space-y-1">
              {loadingSearch ? (
                Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-10 w-full rounded-lg" />)
              ) : (resultados ?? []).length === 0 ? (
                <p className="text-sm text-muted-foreground py-3 text-center">No se encontraron documentos</p>
              ) : (resultados ?? []).map((doc) => (
                <button key={doc.id_documento} onClick={() => setSelectedDoc(doc)}
                  className={cn('w-full text-left px-3 py-2.5 rounded-lg flex items-center gap-3 text-sm transition-colors',
                    selectedDoc?.id_documento === doc.id_documento
                      ? 'bg-primary/10 border border-primary/30'
                      : 'hover:bg-muted/50 border border-transparent')}>
                  <FileText className="h-4 w-4 text-muted-foreground shrink-0" />
                  <div className="flex-1 min-w-0">
                    <p className="font-medium truncate">{doc.materia ?? 'Sin materia'}</p>
                    <p className="text-xs text-muted-foreground">
                      N° {doc.num_interno ?? '—'} · {doc.desc_tipo_documento ?? '—'} · {doc.desc_estado_documento ?? '—'}
                    </p>
                  </div>
                  <ChevronRight className="h-4 w-4 text-muted-foreground shrink-0" />
                </button>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Detalle trazabilidad */}
      {selectedDoc && (
        <div className="space-y-4 animate-fade-in">
          {/* Info doc */}
          <Card className="border-primary/20 bg-primary/5">
            <CardContent className="pt-4 pb-4">
              <div className="flex items-start gap-3">
                <FileText className="h-5 w-5 text-primary shrink-0 mt-0.5" />
                <div className="flex-1 min-w-0">
                  <p className="font-semibold line-clamp-1">{selectedDoc.materia}</p>
                  <div className="flex items-center gap-3 mt-0.5 text-xs text-muted-foreground flex-wrap">
                    {selectedDoc.num_interno && <span className="font-mono">N° {selectedDoc.num_interno}</span>}
                    {selectedDoc.desc_tipo_documento && <span>{selectedDoc.desc_tipo_documento}</span>}
                    <Badge variant="outline" className="text-[10px]">{selectedDoc.desc_estado_documento}</Badge>
                  </div>
                </div>
                <Link to={`/documentos/${selectedDoc.id_documento}`} className="text-xs text-primary hover:underline shrink-0">
                  Ver documento
                </Link>
              </div>
            </CardContent>
          </Card>

          {/* Timeline */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base">Timeline de movimientos</CardTitle>
              <CardDescription>{loadingTraz ? 'Cargando...' : `${(trazabilidad ?? []).length} evento${(trazabilidad ?? []).length !== 1 ? 's' : ''}`}</CardDescription>
            </CardHeader>
            <CardContent>
              {loadingTraz ? (
                <div className="space-y-5">
                  {Array.from({ length: 3 }).map((_, i) => (
                    <div key={i} className="flex gap-4">
                      <Skeleton className="h-8 w-8 rounded-full shrink-0" />
                      <div className="flex-1 space-y-2"><Skeleton className="h-4 w-32" /><Skeleton className="h-3 w-48" /></div>
                    </div>
                  ))}
                </div>
              ) : (trazabilidad ?? []).length === 0 ? (
                <div className="py-10 text-center">
                  <RefreshCw className="h-8 w-8 text-muted-foreground/40 mx-auto mb-2" />
                  <p className="text-sm text-muted-foreground">Sin movimientos registrados</p>
                </div>
              ) : (
                <div className="relative">
                  <div className="absolute left-4 top-4 bottom-4 w-0.5 bg-border" />
                  <div className="space-y-0">
                    {(trazabilidad ?? []).map((ev, idx) => {
                      const cfg = ESTADO_CFG[ev.estadoTramite?.id ?? 0];
                      const Icon = cfg?.icon ?? Clock;
                      const isLast = idx === (trazabilidad ?? []).length - 1;
                      return (
                        <div key={ev.idSeguimiento} className={cn('relative flex gap-4', !isLast && 'pb-7')}>
                          <div className={cn('relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-background', cfg?.dot ?? 'bg-muted-foreground')}>
                            <Icon className="h-3.5 w-3.5 text-white" />
                          </div>
                          <div className="flex-1 min-w-0 pt-1">
                            <div className="flex items-center gap-2 flex-wrap">
                              <span className={cn('text-sm font-semibold', cfg?.color ?? 'text-foreground')}>
                                {cfg?.label ?? ev.estadoTramite?.descripcion ?? 'Movimiento'}
                              </span>
                              {ev.tipoDistribucion?.descripcion && (
                                <span className="text-xs px-2 py-0.5 rounded-full bg-muted text-muted-foreground">
                                  {ev.tipoDistribucion.descripcion}
                                </span>
                              )}
                            </div>

                            {(ev.procedencia?.descripcion || ev.destino?.descripcion) && (
                              <div className="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground flex-wrap">
                                <Building2 className="h-3 w-3 shrink-0" />
                                <span>{ev.procedencia?.descripcion ?? '—'}</span>
                                <ChevronRight className="h-3 w-3" />
                                <span className="font-medium text-foreground">{ev.destino?.descripcion ?? '—'}</span>
                                {ev.procedencia?.tipo === 'E' && <Badge variant="outline" className="text-[9px] py-0">Externo</Badge>}
                              </div>
                            )}

                            <div className="flex items-center gap-3 mt-1 text-xs text-muted-foreground flex-wrap">
                              <span className="flex items-center gap-1">
                                <User className="h-3 w-3" />{ev.usuario?.nombre ?? ev.usuario?.usuario ?? 'Sistema'}
                              </span>
                              <span className="flex items-center gap-1">
                                <Clock className="h-3 w-3" />{ev.fechaSistema ? formatRelativo(ev.fechaSistema) : '—'}
                              </span>
                              {ev.fechaDespacho && (
                                <span className="text-amber-600 flex items-center gap-1">
                                  <Send className="h-3 w-3" />Desp. {formatFechaHora(ev.fechaDespacho)}
                                </span>
                              )}
                              {ev.fechaRecepcion && (
                                <span className="text-emerald-600 flex items-center gap-1">
                                  <CheckCircle2 className="h-3 w-3" />Recep. {formatFechaHora(ev.fechaRecepcion)}
                                </span>
                              )}
                            </div>

                            {ev.tipoCompromiso?.id !== 1 && (ev.diasCompromiso ?? 0) > 0 && (
                              <div className="mt-1 text-xs text-amber-600">
                                Compromiso: {ev.tipoCompromiso?.descripcion} — {ev.diasCompromiso} días
                              </div>
                            )}

                            {ev.observaciones && (
                              <p className="mt-1.5 text-xs text-muted-foreground italic flex items-start gap-1">
                                <MessageSquare className="h-3 w-3 shrink-0 mt-0.5" />"{ev.observaciones}"
                              </p>
                            )}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      )}

      {!selectedDoc && !search && (
        <div className="py-20 text-center">
          <GitBranch className="h-12 w-12 text-muted-foreground/30 mx-auto mb-4" />
          <p className="text-muted-foreground">Busca un documento para ver su trazabilidad</p>
          <p className="text-sm text-muted-foreground/60 mt-1">Ingresa la materia o número de documento</p>
        </div>
      )}
    </div>
  );
}
