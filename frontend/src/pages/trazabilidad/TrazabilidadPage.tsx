import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { GitBranch, Search, Hash, ChevronRight, FileText } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Button } from '@/components/ui/button';
import { useDebounce } from '@/hooks/useDebounce';
import { cn } from '@/lib/utils';
import { Link } from 'react-router-dom';
import { TrazabilidadTimeline, TrazabilidadSkeleton, type TramiteEvento } from '@/components/shared/TrazabilidadTimeline';

interface DocResult {
  idDocumento?: number;
  id_documento?: number;
  materia: string | null;
  numInterno?: number | null;
  num_interno?: number | null;
  tipoDocumento?: { descripcion: string | null };
  desc_tipo_documento?: string | null;
  estadoDocumento?: { descripcion: string | null };
  desc_estado_documento?: string | null;
  fecha_sistema?: string | null;
  fechaIngreso?: string | null;
}

// Normaliza ambos formatos de respuesta (búsqueda general vs buscar-por-numero)
function normalizar(doc: DocResult) {
  return {
    id_documento:          doc.idDocumento ?? doc.id_documento ?? 0,
    materia:               doc.materia,
    num_interno:           doc.numInterno ?? doc.num_interno ?? null,
    desc_tipo_documento:   doc.tipoDocumento?.descripcion ?? doc.desc_tipo_documento ?? null,
    desc_estado_documento: doc.estadoDocumento?.descripcion ?? doc.desc_estado_documento ?? null,
  };
}

type Modo = 'numero' | 'general';

export function TrazabilidadPage() {
  const [modo, setModo]             = useState<Modo>('numero');

  // ── Modo: por número ──────────────────────────────────────
  const [inputNum, setInputNum]     = useState('');
  const [numBuscado, setNumBuscado] = useState<number | null>(null);
  const [numError, setNumError]     = useState('');

  // ── Modo: general ─────────────────────────────────────────
  const [search, setSearch]         = useState('');
  const debouncedSearch             = useDebounce(search, 400);

  // ── Documento seleccionado (compartido por ambos modos) ───
  const [selectedDoc, setSelectedDoc] = useState<ReturnType<typeof normalizar> | null>(null);

  // ── Query: búsqueda por número (exacta, rápida) ───────────
  const { data: resultadosNum, isLoading: loadingNum } = useQuery({
    queryKey: ['trazabilidad-por-numero', numBuscado],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: DocResult[] }>(
        `/documentos/buscar-por-numero?numero=${numBuscado}`
      );
      return (data.data ?? []).map(normalizar);
    },
    enabled: numBuscado !== null,
  });

  // ── Query: búsqueda general (existente, sin cambios) ─────
  const { data: resultadosGen, isLoading: loadingGen } = useQuery({
    queryKey: ['trazabilidad-busqueda', debouncedSearch],
    queryFn: async () => {
      if (debouncedSearch.length < 2) return [];
      const { data } = await apiClient.get<{ data: { documentos: DocResult[] } }>(
        `/busqueda?q=${encodeURIComponent(debouncedSearch)}&tipo=documentos&porPagina=10`
      );
      return (data.data.documentos ?? []).map(normalizar);
    },
    enabled: debouncedSearch.length >= 2,
  });

  // ── Query: trazabilidad del documento seleccionado ────────
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

  // ── Handlers ──────────────────────────────────────────────
  const handleBuscarNumero = () => {
    setNumError('');
    const val = inputNum.trim();
    if (!val) { setNumError('Ingresa un número de documento'); return; }
    if (!/^\d+$/.test(val)) { setNumError('Solo se aceptan números enteros'); return; }
    const n = parseInt(val, 10);
    if (n <= 0) { setNumError('El número debe ser mayor a 0'); return; }
    setSelectedDoc(null);
    setNumBuscado(n);
  };

  const cambiarModo = (m: Modo) => {
    setModo(m);
    setSelectedDoc(null);
    setNumBuscado(null);
    setNumError('');
    setInputNum('');
    setSearch('');
  };

  // Resultados activos según modo
  const resultados   = modo === 'numero' ? resultadosNum : resultadosGen;
  const loadingSearch = modo === 'numero' ? loadingNum : loadingGen;
  const hayBusqueda  = modo === 'numero' ? numBuscado !== null : debouncedSearch.length >= 2;

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex items-center gap-3">
        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
          <GitBranch className="h-5 w-5 text-primary" />
        </div>
        <div>
          <h1 className="text-2xl font-bold">Trazabilidad Documental</h1>
          <p className="text-sm text-muted-foreground">Historial completo de movimientos</p>
        </div>
      </div>

      {/* Selector de modo */}
      <div className="flex gap-1 p-1 bg-muted rounded-xl w-fit">
        <button
          onClick={() => cambiarModo('numero')}
          className={cn(
            'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all',
            modo === 'numero'
              ? 'bg-background text-foreground shadow-sm'
              : 'text-muted-foreground hover:text-foreground'
          )}
        >
          <Hash className="h-3.5 w-3.5" />
          Por N° documento
        </button>
        <button
          onClick={() => cambiarModo('general')}
          className={cn(
            'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all',
            modo === 'general'
              ? 'bg-background text-foreground shadow-sm'
              : 'text-muted-foreground hover:text-foreground'
          )}
        >
          <Search className="h-3.5 w-3.5" />
          Búsqueda general
        </button>
      </div>

      {/* Panel de búsqueda */}
      <Card>
        <CardContent className="pt-4 pb-4">

          {/* ── Modo: por número ── */}
          {modo === 'numero' && (
            <div className="space-y-3">
              <p className="text-xs text-muted-foreground">
                Ingresa el número exacto del documento (N° interno o ID).
              </p>
              <div className="flex gap-2">
                <div className="relative flex-1 max-w-xs">
                  <Hash className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    value={inputNum}
                    onChange={(e) => {
                      const v = e.target.value.replace(/\D/g, ''); // solo dígitos
                      setInputNum(v);
                      setNumError('');
                      if (!v) { setNumBuscado(null); setSelectedDoc(null); }
                    }}
                    onKeyDown={(e) => { if (e.key === 'Enter') handleBuscarNumero(); }}
                    placeholder="Ej: 1234"
                    className={cn('pl-9', numError && 'border-destructive focus-visible:ring-destructive')}
                    inputMode="numeric"
                    autoFocus
                  />
                </div>
                <Button onClick={handleBuscarNumero} disabled={!inputNum} className="gap-2">
                  <Search className="h-3.5 w-3.5" />
                  Buscar
                </Button>
              </div>
              {numError && (
                <p className="text-xs text-destructive">{numError}</p>
              )}
            </div>
          )}

          {/* ── Modo: general (sin cambios en comportamiento) ── */}
          {modo === 'general' && (
            <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                value={search}
                onChange={(e) => { setSearch(e.target.value); if (!e.target.value) setSelectedDoc(null); }}
                placeholder="Buscar por materia o número de documento..."
                className="pl-9"
                autoFocus
              />
            </div>
          )}

          {/* Resultados comunes */}
          {hayBusqueda && (
            <div className="mt-3 space-y-1">
              {loadingSearch ? (
                Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-10 w-full rounded-lg" />)
              ) : (resultados ?? []).length === 0 ? (
                <p className="text-sm text-muted-foreground py-3 text-center">
                  {modo === 'numero'
                    ? `No se encontró ningún documento con N° ${numBuscado}`
                    : 'No se encontraron documentos'}
                </p>
              ) : (resultados ?? []).map((doc) => (
                <button
                  key={doc.id_documento}
                  onClick={() => setSelectedDoc(doc)}
                  className={cn(
                    'w-full text-left px-3 py-2.5 rounded-lg flex items-center gap-3 text-sm transition-colors',
                    selectedDoc?.id_documento === doc.id_documento
                      ? 'bg-primary/10 border border-primary/30'
                      : 'hover:bg-muted/50 border border-transparent'
                  )}
                >
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

      {/* Detalle trazabilidad — igual que antes */}
      {selectedDoc && (
        <div className="space-y-4 animate-fade-in">
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

      {!selectedDoc && !hayBusqueda && (
        <div className="py-20 text-center">
          <GitBranch className="h-12 w-12 text-muted-foreground/30 mx-auto mb-4" />
          <p className="text-muted-foreground">Busca un documento para ver su trazabilidad</p>
          <p className="text-sm text-muted-foreground/60 mt-1">
            {modo === 'numero'
              ? 'Ingresa el número exacto del documento'
              : 'Ingresa la materia o número de documento'}
          </p>
        </div>
      )}
    </div>
  );
}
