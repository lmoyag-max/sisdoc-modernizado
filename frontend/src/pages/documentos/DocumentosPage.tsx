import { useState } from 'react';
import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Plus, Search, FileText, Lock, Paperclip, ArrowUpDown, ChevronLeft, ChevronRight, Filter } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { EmptyState } from '@/components/shared/EmptyState';
import { documentosApi, type FiltrosDocumento } from '@/lib/api/documentos.api';
import { formatFechaHora, cn } from '@/lib/utils';
import { useDebounce } from '@/hooks/useDebounce';

const ESTADO_BADGE: Record<number, { label: string; variant: 'info' | 'warning' | 'success' | 'secondary' | 'purple' }> = {
  1: { label: 'Nuevo',        variant: 'info' },
  2: { label: 'Recepcionado', variant: 'secondary' },
  3: { label: 'Derivado',     variant: 'warning' },
  4: { label: 'En proceso',   variant: 'purple' },
  5: { label: 'Cerrado',      variant: 'success' },
};

export function DocumentosPage() {
  const [filtros, setFiltros] = useState<FiltrosDocumento>({ pagina: 1, porPagina: 20 });
  const [search, setSearch]   = useState('');
  const debouncedSearch       = useDebounce(search, 300);

  const queryFiltros: FiltrosDocumento = { ...filtros, q: debouncedSearch || undefined };

  const { data, isLoading, isFetching } = useQuery({
    queryKey: ['documentos', queryFiltros],
    queryFn:  () => documentosApi.listar(queryFiltros),
    placeholderData: keepPreviousData,
    staleTime: 15_000,
  });

  const documentos = data?.data ?? [];
  const meta       = data?.meta;
  const loading    = isLoading;

  const setPage = (p: number) => setFiltros((f) => ({ ...f, pagina: p }));

  return (
    <div className="space-y-6 animate-fade-in">

      {/* Header */}
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-3">
          <span className="icon-3d icon-3d-indigo hidden sm:flex h-11 w-11 shrink-0 items-center justify-center">
            <FileText className="h-5 w-5 text-white" />
          </span>
          <div>
            <h1 className="text-xl sm:text-2xl font-bold text-foreground">Documentos</h1>
            <p className="text-sm text-muted-foreground mt-0.5">
              {meta ? `${meta.total.toLocaleString('es-CL')} documento${meta.total !== 1 ? 's' : ''} en total` : 'Cargando...'}
            </p>
          </div>
        </div>
        <Link to="/documentos/nuevo">
          <Button size="sm" className="btn-premium gap-2 shrink-0 border-0 text-primary-foreground">
            <Plus className="h-4 w-4" />
            Nuevo documento
          </Button>
        </Link>
      </div>

      {/* Barra de filtros */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col sm:flex-row gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Buscar por asunto, número de documento..."
                className="pl-9"
                value={search}
                onChange={(e) => { setSearch(e.target.value); setFiltros((f) => ({ ...f, pagina: 1 })); }}
              />
            </div>
            {search && (
              <Button variant="ghost" size="sm" onClick={() => setSearch('')} className="text-xs shrink-0">
                Limpiar
              </Button>
            )}
            <Button variant="outline" size="default" className="gap-2 shrink-0">
              <Filter className="h-4 w-4" />
              Filtros
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Empty state mobile */}
      {!loading && documentos.length === 0 && (
        <Card className="md:hidden">
          <EmptyState
            title="Sin documentos"
            description="No se encontraron documentos con los filtros seleccionados."
            action={search ? <Button variant="outline" onClick={() => setSearch('')}>Limpiar búsqueda</Button> : undefined}
          />
        </Card>
      )}

      {/* Vista mobile — cards */}
      <div className={cn('md:hidden space-y-2', isFetching && 'opacity-60 transition-opacity')}>
        {loading ? (
          Array.from({ length: 5 }).map((_, i) => (
            <Card key={i} className="p-4">
              <div className="space-y-2">
                <Skeleton className="h-4 w-3/4" />
                <Skeleton className="h-3 w-1/2" />
                <div className="flex gap-2 mt-2">
                  <Skeleton className="h-5 w-16 rounded-full" />
                  <Skeleton className="h-5 w-20 rounded-full" />
                </div>
              </div>
            </Card>
          ))
        ) : documentos.length === 0 ? null : (
          documentos.map((doc) => {
            const badge = ESTADO_BADGE[doc.estadoDocumento?.id ?? 0] ?? { label: 'Desconocido', variant: 'secondary' as const };
            return (
              <Link key={doc.idDocumento} to={`/documentos/${doc.idDocumento}`} className="block">
                <Card className={cn(
                  'hover:border-primary/40 transition-colors group card-executive',
                  doc.reservado && 'border-l-4 border-l-violet-400',
                )}>
                  <CardContent className="p-4">
                    <div className="flex items-start justify-between gap-3">
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-1.5 mb-0.5">
                          {doc.reservado && <Lock className="h-3 w-3 text-violet-500 shrink-0" />}
                          {doc.tipoSoporte === 'F' && <Paperclip className="h-3 w-3 text-slate-500 shrink-0" />}
                        </div>
                        <p className="text-sm font-medium text-foreground group-hover:text-primary transition-colors line-clamp-2">
                          {doc.asunto ?? 'Sin asunto'}
                        </p>
                        <div className="flex items-center gap-2 mt-1 text-xs text-muted-foreground flex-wrap">
                          <span className="font-mono">{doc.numDocumento ?? `#${doc.idDocumento}`}</span>
                          {doc.tipoDocumento?.descripcion && <span>{doc.tipoDocumento.descripcion}</span>}
                        </div>
                      </div>
                      <div className="flex flex-col items-end gap-1 shrink-0">
                        <Badge variant={badge.variant}>{badge.label}</Badge>
                        {doc.reservado && <span className="text-[10px] text-violet-600 dark:text-violet-400 font-medium">Reservado</span>}
                      </div>
                    </div>
                    <div className="flex items-center justify-between mt-2 text-xs text-muted-foreground">
                      <span>{doc.ingresadoPor.nombre || doc.ingresadoPor.usuario || '—'}</span>
                      <span>{doc.fechaIngreso ? formatFechaHora(doc.fechaIngreso).split(' ')[0] : '—'}</span>
                    </div>
                  </CardContent>
                </Card>
              </Link>
            );
          })
        )}
      </div>

      {/* Paginación mobile */}
      {meta && meta.totalPaginas > 1 && (
        <div className="flex items-center justify-between md:hidden">
          <p className="text-xs text-muted-foreground">{meta.pagina} / {meta.totalPaginas} · {meta.total} docs</p>
          <div className="flex items-center gap-2">
            <button type="button" className="pagination-pill h-9 w-9" disabled={meta.pagina <= 1} onClick={() => setPage(meta.pagina - 1)} aria-label="Página anterior"><ChevronLeft className="h-4 w-4" /></button>
            <button type="button" className="pagination-pill h-9 w-9" disabled={meta.pagina >= meta.totalPaginas} onClick={() => setPage(meta.pagina + 1)} aria-label="Página siguiente"><ChevronRight className="h-4 w-4" /></button>
          </div>
        </div>
      )}

      {/* Tabla desktop */}
      <Card className="hidden md:block">
        <div className="overflow-x-auto">
          <div className="min-w-[780px]">
            <CardHeader className="table-head-modern px-6 py-3.5">
              <div className="grid grid-cols-12 gap-4">
                <div className="col-span-1">N°</div>
                <div className="col-span-3 flex items-center gap-1 cursor-pointer hover:text-foreground transition-colors">
                  Asunto <ArrowUpDown className="h-3 w-3" />
                </div>
                <div className="col-span-2">Tipo</div>
                <div className="col-span-2">N° Memorándum</div>
                <div className="col-span-1">Ingresado por</div>
                <div className="col-span-2">Estado</div>
                <div className="col-span-1">Fecha</div>
              </div>
            </CardHeader>
            <CardContent className="p-0">
              {loading ? (
                <div className="divide-y">
                  {Array.from({ length: 8 }).map((_, i) => (
                    <div key={i} className="grid grid-cols-12 gap-4 px-6 py-4 items-center">
                      <Skeleton className="col-span-1 h-4 w-16" />
                      <Skeleton className="col-span-3 h-4" />
                      <Skeleton className="col-span-2 h-4 w-20" />
                      <Skeleton className="col-span-2 h-4 w-24" />
                      <Skeleton className="col-span-1 h-4 w-16" />
                      <Skeleton className="col-span-2 h-5 w-20 rounded-full" />
                      <Skeleton className="col-span-1 h-4 w-16" />
                    </div>
                  ))}
                </div>
              ) : documentos.length === 0 ? (
                <EmptyState
                  title="Sin documentos"
                  description="No se encontraron documentos con los filtros seleccionados."
                  action={search ? <Button variant="outline" onClick={() => setSearch('')}>Limpiar búsqueda</Button> : undefined}
                />
              ) : (
                <div className={cn('divide-y', isFetching && 'opacity-60 transition-opacity')}>
                  {documentos.map((doc) => {
                    const badge = ESTADO_BADGE[doc.estadoDocumento?.id ?? 0] ?? { label: 'Desconocido', variant: 'secondary' as const };
                    return (
                      <Link
                        key={doc.idDocumento}
                        to={`/documentos/${doc.idDocumento}`}
                        className={cn(
                          'grid grid-cols-12 gap-4 px-6 py-3.5 items-center hover:bg-muted/30 transition-colors cursor-pointer group',
                          doc.reservado && 'border-l-2 border-l-violet-400 pl-5',
                        )}
                      >
                        <div className="col-span-1">
                          <span className="text-xs font-mono text-muted-foreground tabular-nums">
                            {doc.numDocumento ?? `#${doc.idDocumento}`}
                          </span>
                        </div>
                        <div className="col-span-3 min-w-0">
                          <div className="flex items-center gap-1.5 mb-0.5">
                            {doc.reservado && <Lock className="h-3 w-3 text-violet-500 shrink-0" aria-label="Reservado" />}
                            {doc.tipoSoporte === 'F' && <Paperclip className="h-3 w-3 text-slate-400 shrink-0" aria-label="Documento físico" />}
                          </div>
                          <p className="text-sm font-medium text-foreground group-hover:text-primary transition-colors line-clamp-1">
                            {doc.asunto ?? 'Sin asunto'}
                          </p>
                        </div>
                        <div className="col-span-2">
                          <span className="text-xs text-muted-foreground line-clamp-1">
                            {doc.tipoDocumento?.descripcion ?? '—'}
                          </span>
                        </div>
                        <div className="col-span-2">
                          {doc.tipoDocumento?.descripcion === 'Memorandum' && doc.numeroMemo && (
                            <span className="text-xs font-mono text-muted-foreground line-clamp-1">
                              {doc.numeroMemo}
                            </span>
                          )}
                        </div>
                        <div className="col-span-1">
                          <span className="text-xs text-muted-foreground line-clamp-1">
                            {doc.ingresadoPor.nombre || doc.ingresadoPor.usuario || '—'}
                          </span>
                        </div>
                        <div className="col-span-2 flex items-center gap-1.5">
                          <Badge variant={badge.variant}>{badge.label}</Badge>
                          {doc.reservado && (
                            <span className="hidden xl:inline text-[10px] font-medium text-violet-600 dark:text-violet-400 bg-violet-100 dark:bg-violet-900/30 px-1.5 py-0.5 rounded-full">
                              Reservado
                            </span>
                          )}
                        </div>
                        <div className="col-span-1">
                          <span className="text-xs text-muted-foreground">
                            {doc.fechaIngreso ? formatFechaHora(doc.fechaIngreso).split(' ')[0] : '—'}
                          </span>
                        </div>
                      </Link>
                    );
                  })}
                </div>
              )}
            </CardContent>
          </div>
        </div>

        {/* Paginación desktop */}
        {meta && meta.totalPaginas > 1 && (
          <div className="flex items-center justify-between px-6 py-4 border-t bg-muted/10">
            <p className="text-xs text-muted-foreground">
              Mostrando {(meta.pagina - 1) * meta.porPagina + 1}–{Math.min(meta.pagina * meta.porPagina, meta.total)} de {meta.total.toLocaleString('es-CL')}
            </p>
            <div className="flex items-center gap-2">
              <button type="button" className="pagination-pill" disabled={meta.pagina <= 1} onClick={() => setPage(meta.pagina - 1)} aria-label="Página anterior"><ChevronLeft className="h-3.5 w-3.5" /></button>
              <span className="text-xs text-muted-foreground px-2 tabular-nums">{meta.pagina} / {meta.totalPaginas}</span>
              <button type="button" className="pagination-pill" disabled={meta.pagina >= meta.totalPaginas} onClick={() => setPage(meta.pagina + 1)} aria-label="Página siguiente"><ChevronRight className="h-3.5 w-3.5" /></button>
            </div>
          </div>
        )}
      </Card>
    </div>
  );
}
