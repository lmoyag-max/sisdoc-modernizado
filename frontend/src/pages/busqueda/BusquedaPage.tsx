import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Search, FileText, Users, GitBranch, X, ChevronLeft, ChevronRight } from 'lucide-react';
import { Link } from 'react-router-dom';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { formatFechaHora, truncate } from '@/lib/utils';
import { useDebounce } from '@/hooks/useDebounce';

type TipoBusqueda = 'todos' | 'documentos' | 'tramites' | 'funcionarios';

interface ResultadoBusqueda {
  documentos: Array<{
    id_documento: number; materia: string | null; num_interno: string | null;
    desc_tipo_documento: string | null; desc_estado_documento: string | null;
    fecha_sistema: string | null;
  }>;
  tramites: Array<{
    id_seguimiento: number; id_documento: number | null; materia: string | null;
    observaciones: string | null; fecha_sistema: string | null;
  }>;
  funcionarios: Array<{
    id_funcionario: number; nombres: string | null; apellidos: string | null;
    rut: string | null; desc_dependencia: string | null;
  }>;
  total: number;
  pagina: number;
  totalPaginas: number;
}

const TIPO_TABS: { value: TipoBusqueda; label: string; icon: React.ComponentType<{ className?: string }> }[] = [
  { value: 'todos',         label: 'Todo',         icon: Search },
  { value: 'documentos',    label: 'Documentos',   icon: FileText },
  { value: 'tramites',      label: 'Trámites',     icon: GitBranch },
  { value: 'funcionarios',  label: 'Funcionarios', icon: Users },
];

export function BusquedaPage() {
  const [query, setQuery]         = useState('');
  const [tipo, setTipo]           = useState<TipoBusqueda>('todos');
  const [pagina, setPagina]       = useState(1);
  const debouncedQuery            = useDebounce(query, 400);

  const { data, isLoading, isFetching } = useQuery({
    queryKey: ['busqueda', debouncedQuery, tipo, pagina],
    queryFn: async (): Promise<ResultadoBusqueda> => {
      const { data } = await apiClient.get<{ ok: boolean; data: ResultadoBusqueda }>(
        `/busqueda?q=${encodeURIComponent(debouncedQuery)}&tipo=${tipo}&pagina=${pagina}&porPagina=20`
      );
      return data.data;
    },
    enabled: debouncedQuery.length >= 2,
  });

  const totalResultados = (data?.documentos.length ?? 0) + (data?.tramites.length ?? 0) + (data?.funcionarios.length ?? 0);
  const buscando = debouncedQuery.length >= 2;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-foreground flex items-center gap-2">
          <Search className="h-6 w-6 text-primary" />
          Búsqueda Avanzada
        </h1>
        <p className="text-sm text-muted-foreground mt-0.5">
          Busca en documentos, trámites y funcionarios del sistema
        </p>
      </div>

      {/* Buscador principal */}
      <Card>
        <CardContent className="p-6">
          <div className="relative">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
            <input
              type="text"
              value={query}
              onChange={(e) => { setQuery(e.target.value); setPagina(1); }}
              placeholder="Buscar por materia, número de documento, nombre de funcionario, RUT..."
              className="w-full h-12 pl-12 pr-12 rounded-xl border border-input bg-background text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring transition-all"
              autoFocus
            />
            {query && (
              <button
                onClick={() => setQuery('')}
                className="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
              >
                <X className="h-4 w-4" />
              </button>
            )}
          </div>

          {/* Tipo de búsqueda */}
          <div className="flex gap-2 mt-4 flex-wrap">
            {TIPO_TABS.map(({ value, label, icon: Icon }) => (
              <button
                key={value}
                onClick={() => { setTipo(value); setPagina(1); }}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-colors ${
                  tipo === value
                    ? 'bg-primary text-primary-foreground shadow-sm'
                    : 'bg-muted text-muted-foreground hover:text-foreground hover:bg-muted/80'
                }`}
              >
                <Icon className="h-3.5 w-3.5" />
                {label}
              </button>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Resultados */}
      {!buscando && (
        <div className="text-center py-16 text-muted-foreground">
          <Search className="h-12 w-12 mx-auto mb-4 opacity-20" />
          <p className="text-sm">Escribe al menos 2 caracteres para buscar</p>
        </div>
      )}

      {buscando && isLoading && (
        <div className="space-y-4">
          {Array.from({ length: 5 }).map((_, i) => (
            <Card key={i}>
              <CardContent className="p-4">
                <Skeleton className="h-4 w-3/4 mb-2" />
                <Skeleton className="h-3 w-1/2" />
              </CardContent>
            </Card>
          ))}
        </div>
      )}

      {buscando && !isLoading && (
        <div className={`space-y-6 ${isFetching ? 'opacity-60' : ''}`}>
          <p className="text-sm text-muted-foreground">
            {totalResultados > 0
              ? `${data?.total ?? totalResultados} resultado${(data?.total ?? totalResultados) !== 1 ? 's' : ''} para "${debouncedQuery}"`
              : `Sin resultados para "${debouncedQuery}"`
            }
          </p>

          {/* Documentos */}
          {(tipo === 'todos' || tipo === 'documentos') && (data?.documentos ?? []).length > 0 && (
            <section>
              <h2 className="flex items-center gap-2 text-sm font-semibold text-muted-foreground uppercase tracking-wide mb-3">
                <FileText className="h-4 w-4" />
                Documentos ({data?.documentos.length})
              </h2>
              <div className="space-y-2">
                {data?.documentos.map((doc) => (
                  <Link key={doc.id_documento} to={`/documentos/${doc.id_documento}`}>
                    <Card className="hover:shadow-md transition-shadow cursor-pointer">
                      <CardContent className="p-4">
                        <div className="flex items-start justify-between gap-4">
                          <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-foreground line-clamp-1">
                              {truncate(doc.materia ?? 'Sin materia', 80)}
                            </p>
                            <div className="flex items-center gap-3 mt-1 text-xs text-muted-foreground flex-wrap">
                              {doc.num_interno && <span className="font-mono">N° {doc.num_interno}</span>}
                              {doc.desc_tipo_documento && <span>{doc.desc_tipo_documento}</span>}
                              {doc.fecha_sistema && <span>{formatFechaHora(doc.fecha_sistema)}</span>}
                            </div>
                          </div>
                          <div className="shrink-0">
                            {doc.desc_estado_documento && (
                              <Badge variant="secondary">{doc.desc_estado_documento}</Badge>
                            )}
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  </Link>
                ))}
              </div>
            </section>
          )}

          {/* Trámites */}
          {(tipo === 'todos' || tipo === 'tramites') && (data?.tramites ?? []).length > 0 && (
            <section>
              <h2 className="flex items-center gap-2 text-sm font-semibold text-muted-foreground uppercase tracking-wide mb-3">
                <GitBranch className="h-4 w-4" />
                Trámites ({data?.tramites.length})
              </h2>
              <div className="space-y-2">
                {data?.tramites.map((t) => (
                  <Card key={t.id_seguimiento} className="hover:shadow-sm transition-shadow">
                    <CardContent className="p-4">
                      <p className="text-sm font-medium text-foreground line-clamp-1">
                        {truncate(t.materia ?? 'Sin materia', 80)}
                      </p>
                      <div className="flex items-center gap-3 mt-1 text-xs text-muted-foreground">
                        <span>Trámite #{t.id_seguimiento}</span>
                        {t.observaciones && <span className="italic">"{truncate(t.observaciones, 40)}"</span>}
                        {t.fecha_sistema && <span>{formatFechaHora(t.fecha_sistema)}</span>}
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            </section>
          )}

          {/* Funcionarios */}
          {(tipo === 'todos' || tipo === 'funcionarios') && (data?.funcionarios ?? []).length > 0 && (
            <section>
              <h2 className="flex items-center gap-2 text-sm font-semibold text-muted-foreground uppercase tracking-wide mb-3">
                <Users className="h-4 w-4" />
                Funcionarios ({data?.funcionarios.length})
              </h2>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                {data?.funcionarios.map((f) => (
                  <Card key={f.id_funcionario} className="hover:shadow-sm transition-shadow">
                    <CardContent className="p-4 flex items-center gap-3">
                      <div className="h-10 w-10 shrink-0 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-sm">
                        {(f.nombres ?? '?')[0]?.toUpperCase()}
                      </div>
                      <div className="min-w-0">
                        <p className="text-sm font-medium text-foreground">
                          {[f.nombres, f.apellidos].filter(Boolean).join(' ') || '—'}
                        </p>
                        <p className="text-xs text-muted-foreground truncate">
                          {f.rut && `RUT: ${f.rut} · `}{f.desc_dependencia ?? '—'}
                        </p>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            </section>
          )}

          {/* Paginación documentos */}
          {tipo === 'documentos' && data && data.totalPaginas > 1 && (
            <div className="flex items-center justify-center gap-2">
              <Button variant="outline" size="icon" className="h-8 w-8" disabled={pagina <= 1} onClick={() => setPagina((p) => p - 1)}>
                <ChevronLeft className="h-4 w-4" />
              </Button>
              <span className="text-sm text-muted-foreground">{pagina} / {data.totalPaginas}</span>
              <Button variant="outline" size="icon" className="h-8 w-8" disabled={pagina >= data.totalPaginas} onClick={() => setPagina((p) => p + 1)}>
                <ChevronRight className="h-4 w-4" />
              </Button>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
