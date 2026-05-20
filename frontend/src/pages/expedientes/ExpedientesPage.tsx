import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  FolderOpen, Plus, Search, FileText,
  ChevronLeft, ChevronRight, RefreshCw, X, Save, ChevronRight as Arrow,
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { toast } from 'sonner';
import { useDebounce } from '@/hooks/useDebounce';
import { formatFechaHora } from '@/lib/utils';

interface Expediente {
  id_expediente: number;
  descripcion: string | null;
  fecha_sistema: string | null;
  total_documentos: number;
}

interface DocExpediente {
  id_documento: number;
  materia: string | null;
  num_interno: number | null;
  desc_tipo_documento: string | null;
  desc_estado_documento: string | null;
  fecha_sistema: string | null;
}

export function ExpedientesPage() {
  const [pagina, setPagina] = useState(1);
  const [search, setSearch] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [descripcion, setDescripcion] = useState('');
  const [selected, setSelected] = useState<Expediente | null>(null);
  const debouncedSearch = useDebounce(search, 300);
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['expedientes', pagina, debouncedSearch],
    queryFn: async () => {
      const params = new URLSearchParams({ pagina: String(pagina), porPagina: '15' });
      if (debouncedSearch) params.set('q', debouncedSearch);
      const { data } = await apiClient.get<{ data: Expediente[]; meta: { total: number; totalPaginas: number; pagina: number } }>(
        `/expedientes?${params}`
      );
      return data;
    },
  });

  const { data: docsExpediente, isLoading: loadingDocs } = useQuery({
    queryKey: ['expediente-docs', selected?.id_expediente],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: DocExpediente[] }>(
        `/expedientes/${selected!.id_expediente}/documentos`
      );
      return data.data;
    },
    enabled: !!selected,
  });

  const crearMutation = useMutation({
    mutationFn: () => apiClient.post('/expedientes', { descripcion }),
    onSuccess: () => {
      toast.success('Expediente creado');
      setShowForm(false);
      setDescripcion('');
      qc.invalidateQueries({ queryKey: ['expedientes'] });
    },
    onError: () => toast.error('Error al crear expediente'),
  });

  const expedientes = data?.data ?? [];
  const meta = data?.meta;

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
            <FolderOpen className="h-5 w-5 text-primary" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-foreground">Expedientes</h1>
            <p className="text-sm text-muted-foreground">
              {meta ? `${meta.total} expediente${meta.total !== 1 ? 's' : ''}` : 'Cargando...'}
            </p>
          </div>
        </div>
        <Button onClick={() => setShowForm(true)} className="gap-2">
          <Plus className="h-4 w-4" />
          Nuevo expediente
        </Button>
      </div>

      {/* Form crear */}
      {showForm && (
        <Card className="border-primary/30 bg-primary/5">
          <CardContent className="pt-5">
            <div className="flex gap-3 items-end">
              <div className="flex-1 space-y-1.5">
                <Label>Descripción del expediente</Label>
                <Input
                  value={descripcion}
                  onChange={(e) => setDescripcion(e.target.value)}
                  placeholder="Ej: Expediente de contratación 2026..."
                  autoFocus
                />
              </div>
              <Button onClick={() => crearMutation.mutate()} disabled={!descripcion || crearMutation.isPending} className="gap-2">
                {crearMutation.isPending ? <RefreshCw className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
                Crear
              </Button>
              <Button variant="ghost" onClick={() => setShowForm(false)}><X className="h-4 w-4" /></Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Búsqueda */}
      <div className="relative max-w-sm">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input value={search} onChange={(e) => { setSearch(e.target.value); setPagina(1); }}
          placeholder="Buscar expediente..." className="pl-9" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {/* Lista expedientes */}
        <Card className={selected ? 'lg:col-span-2' : 'lg:col-span-5'}>
          <CardContent className="p-0">
            {isLoading ? (
              <div className="divide-y">
                {Array.from({ length: 5 }).map((_, i) => (
                  <div key={i} className="flex items-center gap-3 px-5 py-4">
                    <Skeleton className="h-9 w-9 rounded-lg" />
                    <div className="flex-1 space-y-1.5">
                      <Skeleton className="h-3.5 w-48" />
                      <Skeleton className="h-3 w-24" />
                    </div>
                  </div>
                ))}
              </div>
            ) : expedientes.length === 0 ? (
              <div className="py-16 text-center">
                <FolderOpen className="h-12 w-12 text-muted-foreground/40 mx-auto mb-3" />
                <p className="text-muted-foreground text-sm">
                  {search ? 'No hay expedientes con ese criterio.' : 'No hay expedientes creados.'}
                </p>
              </div>
            ) : (
              <div className="divide-y">
                {expedientes.map((exp) => (
                  <div
                    key={exp.id_expediente}
                    onClick={() => setSelected(selected?.id_expediente === exp.id_expediente ? null : exp)}
                    className={`flex items-center gap-3 px-5 py-4 cursor-pointer transition-colors ${selected?.id_expediente === exp.id_expediente ? 'bg-primary/10 border-l-4 border-l-primary' : 'hover:bg-muted/30'}`}
                  >
                    <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted">
                      <FolderOpen className="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-foreground truncate">{exp.descripcion ?? `Expediente #${exp.id_expediente}`}</p>
                      <p className="text-xs text-muted-foreground">
                        {exp.total_documentos} doc{exp.total_documentos !== 1 ? 's' : ''}
                        {exp.fecha_sistema && ` · ${formatFechaHora(exp.fecha_sistema)}`}
                      </p>
                    </div>
                    <Arrow className="h-4 w-4 text-muted-foreground shrink-0" />
                  </div>
                ))}
              </div>
            )}
          </CardContent>

          {meta && meta.totalPaginas > 1 && (
            <div className="flex items-center justify-between px-5 py-3 border-t">
              <p className="text-xs text-muted-foreground">Pág. {meta.pagina} / {meta.totalPaginas}</p>
              <div className="flex gap-1">
                <Button variant="outline" size="icon" className="h-7 w-7" disabled={pagina <= 1} onClick={() => setPagina((p) => p - 1)}>
                  <ChevronLeft className="h-3.5 w-3.5" />
                </Button>
                <Button variant="outline" size="icon" className="h-7 w-7" disabled={pagina >= meta.totalPaginas} onClick={() => setPagina((p) => p + 1)}>
                  <ChevronRight className="h-3.5 w-3.5" />
                </Button>
              </div>
            </div>
          )}
        </Card>

        {/* Detalle expediente */}
        {selected && (
          <Card className="lg:col-span-3">
            <CardHeader className="pb-3">
              <div className="flex items-start justify-between">
                <div>
                  <CardTitle className="text-base">{selected.descripcion}</CardTitle>
                  <p className="text-xs text-muted-foreground mt-0.5">
                    {(docsExpediente ?? []).length} documento{(docsExpediente ?? []).length !== 1 ? 's' : ''}
                  </p>
                </div>
                <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => setSelected(null)}>
                  <X className="h-4 w-4" />
                </Button>
              </div>
            </CardHeader>
            <CardContent className="p-0">
              {loadingDocs ? (
                <div className="px-5 space-y-3 pb-4">
                  {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-14 w-full rounded-lg" />)}
                </div>
              ) : (docsExpediente ?? []).length === 0 ? (
                <div className="py-10 text-center text-sm text-muted-foreground">
                  <FileText className="h-8 w-8 mx-auto mb-2 opacity-40" />
                  Sin documentos asociados
                </div>
              ) : (
                <div className="divide-y">
                  {(docsExpediente ?? []).map((doc) => (
                    <div key={doc.id_documento} className="flex items-start gap-3 px-5 py-3 hover:bg-muted/20 transition-colors">
                      <FileText className="h-4 w-4 text-primary shrink-0 mt-0.5" />
                      <div className="flex-1 min-w-0">
                        <p className="text-sm text-foreground line-clamp-1">{doc.materia ?? 'Sin materia'}</p>
                        <div className="flex items-center gap-2 mt-0.5 text-xs text-muted-foreground">
                          {doc.num_interno && <span className="font-mono">N° {doc.num_interno}</span>}
                          {doc.desc_tipo_documento && <span>{doc.desc_tipo_documento}</span>}
                        </div>
                      </div>
                      {doc.desc_estado_documento && (
                        <Badge variant="secondary" className="text-[10px] shrink-0">
                          {doc.desc_estado_documento}
                        </Badge>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  );
}
