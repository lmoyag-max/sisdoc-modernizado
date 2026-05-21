import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { GitBranch, Search, RefreshCw, ChevronRight, FileText } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { useDebounce } from '@/hooks/useDebounce';
import { cn } from '@/lib/utils';
import { Link } from 'react-router-dom';
import { TrazabilidadTimeline, TrazabilidadSkeleton, type TramiteEvento } from '@/components/shared/TrazabilidadTimeline';

interface DocResult {
  id_documento: number; materia: string | null;
  num_interno: number | null; desc_tipo_documento: string | null;
  desc_estado_documento: string | null; fecha_sistema: string | null;
}

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
              <CardTitle className="text-base flex items-center gap-2">
                <GitBranch className="h-4 w-4 text-primary" />
                Timeline completo de movimientos
              </CardTitle>
              <CardDescription>
                {loadingTraz
                  ? 'Cargando...'
                  : `${(trazabilidad ?? []).length} movimiento${(trazabilidad ?? []).length !== 1 ? 's' : ''} registrado${(trazabilidad ?? []).length !== 1 ? 's' : ''}`}
              </CardDescription>
            </CardHeader>
            <CardContent>
              {loadingTraz
                ? <TrazabilidadSkeleton count={3} />
                : <TrazabilidadTimeline eventos={trazabilidad ?? []} variante="completo" />
              }
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
