import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { GitCommitHorizontal, Search, FileText, CheckCircle2, ArrowRight, Clock } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { formatFechaHora, formatRelativo } from '@/lib/utils';

interface HistorialItem {
  id_seguimiento: number;
  id_estado_tramite: number | null;
  observaciones: string | null;
  fecha_sistema: string | null;
  usuario: string | null;
  nombres: string | null;
}

interface DocumentoDetalle {
  idDocumento: number;
  numDocumento: string | null;
  asunto: string | null;
  tipoDocumento: { descripcion: string | null };
  estadoDocumento: { id: number | null; descripcion: string | null };
  fechaIngreso: string | null;
}

const PASO_COLOR: Record<number, string> = {
  1: 'bg-amber-500',
  2: 'bg-blue-500',
  3: 'bg-emerald-500',
  4: 'bg-red-500',
};

const PASO_LABEL: Record<number, string> = {
  1: 'Derivado',
  2: 'Recepcionado',
  3: 'Completado',
  4: 'Rechazado',
};

export function TrazabilidadPage() {
  const [query, setQuery] = useState('');
  const [busqueda, setBusqueda] = useState('');
  const [idDocumento, setIdDocumento] = useState<number | null>(null);

  const { data: docData, isLoading: loadingDoc } = useQuery({
    queryKey: ['trazabilidad-doc', idDocumento],
    queryFn: () => apiClient.get<{ ok: boolean; data: DocumentoDetalle }>(`/documentos/${idDocumento}`).then((r) => r.data.data),
    enabled: !!idDocumento,
  });

  const { data: historialData, isLoading: loadingHist } = useQuery({
    queryKey: ['trazabilidad-hist', idDocumento],
    queryFn: () => apiClient.get<{ ok: boolean; data: HistorialItem[] }>(`/documentos/${idDocumento}/historial`).then((r) => r.data.data),
    enabled: !!idDocumento,
  });

  const { data: busquedaData, isLoading: loadingBusq } = useQuery({
    queryKey: ['trazabilidad-search', busqueda],
    queryFn: () => apiClient.get<{ ok: boolean; data: { documentos: DocumentoDetalle[] } }>(`/busqueda?q=${busqueda}&tipo=documentos&porPagina=8`).then((r) => r.data.data),
    enabled: busqueda.length >= 2,
  });

  const handleSearch = () => {
    if (query.trim()) setBusqueda(query.trim());
  };

  const historial = historialData ?? [];
  const isLoading = loadingDoc || loadingHist;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-foreground flex items-center gap-2">
          <GitCommitHorizontal className="h-6 w-6 text-primary" />
          Trazabilidad Documental
        </h1>
        <p className="text-sm text-muted-foreground mt-0.5">
          Seguimiento completo del ciclo de vida de cada documento
        </p>
      </div>

      {/* Buscador */}
      <Card>
        <CardContent className="p-4">
          <div className="flex gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Buscar documento por materia, número..."
                className="pl-9"
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
              />
            </div>
            <Button onClick={handleSearch} disabled={query.length < 2}>
              <Search className="h-4 w-4 mr-2" />
              Buscar
            </Button>
          </div>

          {/* Resultados de búsqueda */}
          {busqueda && (
            <div className="mt-4 space-y-2">
              {loadingBusq ? (
                <div className="space-y-2">
                  {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-12 w-full rounded-lg" />)}
                </div>
              ) : (busquedaData?.documentos ?? []).length === 0 ? (
                <p className="text-sm text-muted-foreground text-center py-4">Sin resultados para "{busqueda}"</p>
              ) : (
                <div className="rounded-lg border divide-y overflow-hidden">
                  {(busquedaData?.documentos ?? []).map((doc) => (
                    <button
                      key={doc.idDocumento}
                      onClick={() => { setIdDocumento(doc.idDocumento); setBusqueda(''); setQuery(''); }}
                      className="w-full flex items-center gap-3 px-4 py-3 hover:bg-muted/50 transition-colors text-left"
                    >
                      <FileText className="h-4 w-4 text-muted-foreground shrink-0" />
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-foreground truncate">{doc.asunto ?? 'Sin materia'}</p>
                        <p className="text-xs text-muted-foreground">{doc.numDocumento ?? `ID: ${doc.idDocumento}`} · {doc.tipoDocumento.descripcion}</p>
                      </div>
                      <ArrowRight className="h-4 w-4 text-muted-foreground shrink-0" />
                    </button>
                  ))}
                </div>
              )}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Trazabilidad del documento seleccionado */}
      {idDocumento && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Info del documento */}
          <Card>
            <CardHeader>
              <CardTitle className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">
                Documento
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              {loadingDoc ? (
                <div className="space-y-3">
                  <Skeleton className="h-4 w-full" />
                  <Skeleton className="h-4 w-3/4" />
                  <Skeleton className="h-6 w-24 rounded-full" />
                </div>
              ) : docData ? (
                <>
                  <div>
                    <p className="text-xs text-muted-foreground">Materia</p>
                    <p className="text-sm font-medium mt-0.5">{docData.asunto ?? '—'}</p>
                  </div>
                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <p className="text-xs text-muted-foreground">N° documento</p>
                      <p className="text-sm font-mono mt-0.5">{docData.numDocumento ?? `#${docData.idDocumento}`}</p>
                    </div>
                    <div>
                      <p className="text-xs text-muted-foreground">Tipo</p>
                      <p className="text-sm mt-0.5">{docData.tipoDocumento.descripcion ?? '—'}</p>
                    </div>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground">Estado actual</p>
                    <Badge className="mt-1" variant="info">{docData.estadoDocumento.descripcion ?? '—'}</Badge>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground">Fecha ingreso</p>
                    <p className="text-sm mt-0.5">{docData.fechaIngreso ? formatFechaHora(docData.fechaIngreso) : '—'}</p>
                  </div>
                </>
              ) : null}
            </CardContent>
          </Card>

          {/* Timeline */}
          <div className="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">
                  Historial de movimientos ({historial.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                {isLoading ? (
                  <div className="space-y-6">
                    {Array.from({ length: 4 }).map((_, i) => (
                      <div key={i} className="flex gap-4">
                        <Skeleton className="h-8 w-8 rounded-full shrink-0" />
                        <div className="flex-1 space-y-2">
                          <Skeleton className="h-4 w-1/2" />
                          <Skeleton className="h-3 w-3/4" />
                        </div>
                      </div>
                    ))}
                  </div>
                ) : historial.length === 0 ? (
                  <p className="text-sm text-muted-foreground text-center py-8">
                    Sin movimientos registrados para este documento
                  </p>
                ) : (
                  <div className="relative">
                    {/* Línea vertical */}
                    <div className="absolute left-4 top-4 bottom-4 w-px bg-border" />
                    <div className="space-y-6">
                      {historial.map((item, idx) => {
                        const color = PASO_COLOR[item.id_estado_tramite ?? 0] ?? 'bg-muted-foreground';
                        const label = PASO_LABEL[item.id_estado_tramite ?? 0] ?? 'Movimiento';
                        const isLast = idx === historial.length - 1;

                        return (
                          <div key={item.id_seguimiento} className="flex gap-4 relative">
                            {/* Dot */}
                            <div className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${color} text-white z-10 shadow-sm`}>
                              {isLast ? <CheckCircle2 className="h-4 w-4" /> : <Clock className="h-4 w-4" />}
                            </div>
                            {/* Contenido */}
                            <div className="flex-1 pb-2">
                              <div className="flex items-center justify-between flex-wrap gap-2">
                                <p className="text-sm font-semibold text-foreground">{label}</p>
                                <span className="text-xs text-muted-foreground">{formatRelativo(item.fecha_sistema)}</span>
                              </div>
                              <p className="text-xs text-muted-foreground mt-0.5">
                                {formatFechaHora(item.fecha_sistema)}
                                {item.nombres && ` · ${item.nombres}`}
                                {item.usuario && ` (${item.usuario})`}
                              </p>
                              {item.observaciones && (
                                <p className="text-xs text-muted-foreground mt-1 italic bg-muted rounded px-2 py-1">
                                  "{item.observaciones}"
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
        </div>
      )}

      {/* Estado inicial — sin documento seleccionado */}
      {!idDocumento && !busqueda && (
        <Card className="py-16">
          <CardContent>
            <div className="flex flex-col items-center text-center gap-4">
              <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-muted">
                <GitCommitHorizontal className="h-8 w-8 text-muted-foreground" />
              </div>
              <div>
                <p className="font-semibold text-foreground">Selecciona un documento</p>
                <p className="text-sm text-muted-foreground mt-1">
                  Busca por materia o número de documento para ver su trazabilidad completa
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
