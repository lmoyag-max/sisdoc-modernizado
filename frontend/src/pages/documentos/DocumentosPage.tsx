import { useState } from 'react';
import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import { Plus, Search, Filter, FileText, ArrowUpDown, ChevronLeft, ChevronRight } from 'lucide-react';
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
  1: { label: 'Nuevo', variant: 'info' },
  2: { label: 'Recepcionado', variant: 'secondary' },
  3: { label: 'Derivado', variant: 'warning' },
  4: { label: 'En proceso', variant: 'purple' },
  5: { label: 'Cerrado', variant: 'success' },
};

export function DocumentosPage() {
  const [filtros, setFiltros] = useState<FiltrosDocumento>({ pagina: 1, porPagina: 20 });
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 300);

  const queryFiltros: FiltrosDocumento = {
    ...filtros,
    q: debouncedSearch || undefined,
  };

  const { data, isLoading, isFetching } = useQuery({
    queryKey: ['documentos', queryFiltros],
    queryFn: () => documentosApi.listar(queryFiltros),
    placeholderData: keepPreviousData,
    staleTime: 15_000,
  });

  const documentos = data?.data ?? [];
  const meta = data?.meta;
  const loading = isLoading;

  const setPage = (p: number) => setFiltros((f) => ({ ...f, pagina: p }));

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-foreground">Documentos</h1>
          <p className="text-sm text-muted-foreground mt-0.5">
            {meta ? `${meta.total} documento${meta.total !== 1 ? 's' : ''} en total` : 'Cargando...'}
          </p>
        </div>
        <Link to="/documentos/nuevo">
          <Button size="sm" className="gap-2">
            <Plus className="h-4 w-4" />
            Nuevo documento
          </Button>
        </Link>
      </div>

      {/* Filtros */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col sm:flex-row gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Buscar por asunto, número de documento..."
                className="pl-9"
                value={search}
                onChange={(e) => {
                  setSearch(e.target.value);
                  setFiltros((f) => ({ ...f, pagina: 1 }));
                }}
              />
            </div>
            <Button variant="outline" size="default" className="gap-2 shrink-0">
              <Filter className="h-4 w-4" />
              Filtros
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Tabla */}
      <Card>
        <CardHeader className="px-6 py-4 border-b">
          <div className="grid grid-cols-12 gap-4 text-xs font-medium text-muted-foreground uppercase tracking-wider">
            <div className="col-span-1">N°</div>
            <div className="col-span-4 flex items-center gap-1 cursor-pointer hover:text-foreground">
              Asunto <ArrowUpDown className="h-3 w-3" />
            </div>
            <div className="col-span-2">Tipo</div>
            <div className="col-span-2">Destino</div>
            <div className="col-span-1">Prioridad</div>
            <div className="col-span-1">Estado</div>
            <div className="col-span-1">Fecha</div>
          </div>
        </CardHeader>
        <CardContent className="p-0">
          {loading ? (
            <div className="divide-y">
              {Array.from({ length: 8 }).map((_, i) => (
                <div key={i} className="grid grid-cols-12 gap-4 px-6 py-4 items-center">
                  <Skeleton className="col-span-1 h-4 w-16" />
                  <Skeleton className="col-span-4 h-4" />
                  <Skeleton className="col-span-2 h-4 w-20" />
                  <Skeleton className="col-span-2 h-4 w-24" />
                  <Skeleton className="col-span-1 h-5 w-14 rounded-full" />
                  <Skeleton className="col-span-1 h-5 w-16 rounded-full" />
                  <Skeleton className="col-span-1 h-4 w-20" />
                </div>
              ))}
            </div>
          ) : documentos.length === 0 ? (
            <EmptyState
              title="Sin documentos"
              description="No se encontraron documentos con los filtros seleccionados."
              action={
                search ? (
                  <Button variant="outline" onClick={() => setSearch('')}>
                    Limpiar búsqueda
                  </Button>
                ) : undefined
              }
            />
          ) : (
            <div className={cn('divide-y', isFetching && 'opacity-60 transition-opacity')}>
              {documentos.map((doc) => {
                const badge = ESTADO_BADGE[doc.estadoDocumento?.id ?? 0] ?? { label: 'Desconocido', variant: 'secondary' as const };
                return (
                  <Link
                    key={doc.idDocumento}
                    to={`/documentos/${doc.idDocumento}`}
                    className="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-muted/40 transition-colors cursor-pointer group"
                  >
                    <div className="col-span-1">
                      <span className="text-xs font-mono text-muted-foreground">
                        {doc.numDocumento ?? `#${doc.idDocumento}`}
                      </span>
                    </div>
                    <div className="col-span-4">
                      <p className="text-sm font-medium text-foreground group-hover:text-primary transition-colors line-clamp-1">
                        {doc.asunto ?? 'Sin asunto'}
                      </p>
                      <p className="text-xs text-muted-foreground mt-0.5">
                        {doc.ingresadoPor.nombre || doc.ingresadoPor.usuario || '—'}
                      </p>
                    </div>
                    <div className="col-span-2">
                      <span className="text-xs text-muted-foreground">
                        {doc.tipoDocumento?.descripcion ?? '—'}
                      </span>
                    </div>
                    <div className="col-span-2">
                      <span className="text-xs text-muted-foreground line-clamp-1">
                        {doc.destino?.descripcion ?? '—'}
                      </span>
                    </div>
                    <div className="col-span-1">
                      {doc.prioridad?.descripcion && (
                        <span
                          className="inline-flex h-2 w-2 rounded-full"
                          style={{ backgroundColor: doc.prioridad?.color ?? '#94a3b8' }}
                          title={doc.prioridad.descripcion}
                        />
                      )}
                    </div>
                    <div className="col-span-1">
                      <Badge variant={badge.variant}>{badge.label}</Badge>
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

        {/* Paginación */}
        {meta && meta.totalPaginas > 1 && (
          <div className="flex items-center justify-between px-6 py-4 border-t">
            <p className="text-xs text-muted-foreground">
              Mostrando {(meta.pagina - 1) * meta.porPagina + 1}–{Math.min(meta.pagina * meta.porPagina, meta.total)} de {meta.total}
            </p>
            <div className="flex items-center gap-2">
              <Button
                variant="outline"
                size="icon"
                className="h-8 w-8"
                disabled={meta.pagina <= 1}
                onClick={() => setPage(meta.pagina - 1)}
              >
                <ChevronLeft className="h-4 w-4" />
              </Button>
              <span className="text-xs text-muted-foreground px-2">
                {meta.pagina} / {meta.totalPaginas}
              </span>
              <Button
                variant="outline"
                size="icon"
                className="h-8 w-8"
                disabled={meta.pagina >= meta.totalPaginas}
                onClick={() => setPage(meta.pagina + 1)}
              >
                <ChevronRight className="h-4 w-4" />
              </Button>
            </div>
          </div>
        )}
      </Card>
    </div>
  );
}
