import { useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  ArrowLeft, FileText, Calendar, User, Tag, Clock,
  CheckCircle2, GitBranch, Paperclip, Download, RefreshCw,
  AlertCircle, Hash, Building2,
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { documentosApi } from '@/lib/api/documentos.api';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Separator } from '@/components/ui/separator';
import { formatFechaHora, formatRelativo, cn } from '@/lib/utils';

// ── Estado del documento ─────────────────────────────────────
const ESTADO_CONFIG: Record<number, { label: string; color: string; className: string }> = {
  1: { label: 'Registrado',   color: '#6366f1', className: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' },
  2: { label: 'Recepcionado', color: '#0ea5e9', className: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400' },
  3: { label: 'Despachado',   color: '#f59e0b', className: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' },
  4: { label: 'En proceso',   color: '#8b5cf6', className: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400' },
  5: { label: 'Cerrado',      color: '#10b981', className: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' },
};

const TRAMITE_COLOR: Record<number, string> = {
  1: 'bg-amber-500',
  2: 'bg-blue-500',
  3: 'bg-emerald-500',
};
const TRAMITE_LABEL: Record<number, string> = {
  1: 'Derivado',
  2: 'Recepcionado',
  3: 'Cerrado',
};

interface HistorialItem {
  id_seguimiento: number;
  id_estado_tramite: number | null;
  observaciones: string | null;
  fecha_sistema: string | null;
  usuario: string | null;
  nombres: string | null;
}

interface ArchivoItem {
  id_archivo: number;
  id_documento: number | null;
  nombre_archivo: string | null;
  ruta_archivo: string | null;
  fecha_subida: string | null;
  url: string | null;
}

function InfoRow({ icon: Icon, label, value }: {
  icon: React.ComponentType<{ className?: string }>;
  label: string;
  value: React.ReactNode;
}) {
  return (
    <div className="flex items-start gap-3 py-2.5">
      <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-muted mt-0.5">
        <Icon className="h-3.5 w-3.5 text-muted-foreground" />
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-xs text-muted-foreground font-medium uppercase tracking-wide">{label}</p>
        <div className="text-sm text-foreground font-medium mt-0.5">{value}</div>
      </div>
    </div>
  );
}

export function DocumentoDetallePage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const idDocumento = Number(id);

  const { data: doc, isLoading, error } = useQuery({
    queryKey: ['documento', idDocumento],
    queryFn: () => documentosApi.obtener(idDocumento),
    enabled: !isNaN(idDocumento),
    retry: 1,
  });

  const { data: historial, isLoading: loadingHistorial } = useQuery({
    queryKey: ['documento-historial', idDocumento],
    queryFn: async () => {
      const { data } = await apiClient.get<{ ok: boolean; data: HistorialItem[] }>(`/documentos/${idDocumento}/historial`);
      return data.data;
    },
    enabled: !isNaN(idDocumento),
  });

  const { data: archivos, isLoading: loadingArchivos } = useQuery({
    queryKey: ['archivos', idDocumento],
    queryFn: async () => {
      const { data } = await apiClient.get<{ ok: boolean; data: ArchivoItem[] }>(`/archivos?idDocumento=${idDocumento}`);
      return data.data;
    },
    enabled: !isNaN(idDocumento),
  });

  // ── Error / loading ───────────────────────────────────────
  if (isNaN(idDocumento)) {
    return (
      <div className="flex flex-col items-center justify-center py-20 gap-4">
        <AlertCircle className="h-12 w-12 text-destructive/60" />
        <p className="text-muted-foreground">ID de documento inválido.</p>
        <Button variant="outline" onClick={() => navigate('/documentos')}>Volver a documentos</Button>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center py-20 gap-4">
        <AlertCircle className="h-12 w-12 text-destructive/60" />
        <p className="text-muted-foreground">No se pudo cargar el documento #{idDocumento}.</p>
        <Button variant="outline" onClick={() => navigate('/documentos')}>Volver a documentos</Button>
      </div>
    );
  }

  const estadoCfg = ESTADO_CONFIG[doc?.estadoDocumento?.id ?? 0];

  return (
    <div className="space-y-6 max-w-5xl mx-auto animate-fade-in">
      {/* Header */}
      <div className="flex items-start gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate('/documentos')} className="shrink-0 mt-0.5">
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div className="flex-1 min-w-0">
          {isLoading ? (
            <div className="space-y-2">
              <Skeleton className="h-7 w-2/3" />
              <Skeleton className="h-4 w-1/3" />
            </div>
          ) : (
            <>
              <h1 className="text-2xl font-bold text-foreground leading-tight line-clamp-2">
                {doc?.materia ?? doc?.asunto ?? 'Sin materia'}
              </h1>
              <div className="flex items-center gap-3 mt-1 flex-wrap">
                {doc?.numDocumento && (
                  <span className="text-sm text-muted-foreground font-mono">
                    N° {doc.numDocumento}
                  </span>
                )}
                {estadoCfg && (
                  <span className={cn('px-2.5 py-0.5 rounded-full text-xs font-semibold', estadoCfg.className)}>
                    {estadoCfg.label}
                  </span>
                )}
                {!estadoCfg && doc?.estadoDocumento?.descripcion && (
                  <Badge variant="secondary">{doc.estadoDocumento.descripcion}</Badge>
                )}
              </div>
            </>
          )}
        </div>
        <div className="flex gap-2 shrink-0">
          <Link to="/documentos/nuevo">
            <Button variant="outline" size="sm" className="gap-2">
              <FileText className="h-3.5 w-3.5" />
              Nuevo
            </Button>
          </Link>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Información principal */}
        <div className="lg:col-span-2 space-y-5">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-base flex items-center gap-2">
                <FileText className="h-4 w-4 text-primary" />
                Información del documento
              </CardTitle>
            </CardHeader>
            <CardContent className="divide-y">
              {isLoading ? (
                Array.from({ length: 5 }).map((_, i) => (
                  <div key={i} className="flex items-center gap-3 py-2.5">
                    <Skeleton className="h-7 w-7 rounded-lg" />
                    <div className="flex-1 space-y-1.5">
                      <Skeleton className="h-3 w-16" />
                      <Skeleton className="h-4 w-48" />
                    </div>
                  </div>
                ))
              ) : (
                <>
                  <InfoRow
                    icon={Hash}
                    label="Número de documento"
                    value={doc?.numDocumento ? `N° ${doc.numDocumento}` : `ID ${doc?.idDocumento}`}
                  />
                  <InfoRow
                    icon={Tag}
                    label="Tipo de documento"
                    value={doc?.tipoDocumento?.descripcion ?? '—'}
                  />
                  <InfoRow
                    icon={CheckCircle2}
                    label="Estado"
                    value={
                      estadoCfg
                        ? <span className={cn('px-2 py-0.5 rounded-full text-xs font-semibold', estadoCfg.className)}>{estadoCfg.label}</span>
                        : (doc?.estadoDocumento?.descripcion ?? '—')
                    }
                  />
                  <InfoRow
                    icon={User}
                    label="Ingresado por"
                    value={
                      <span>
                        {doc?.ingresadoPor?.nombre || doc?.ingresadoPor?.usuario || '—'}
                        {doc?.ingresadoPor?.usuario && (
                          <span className="text-muted-foreground font-normal ml-1">
                            (@{doc.ingresadoPor.usuario})
                          </span>
                        )}
                      </span>
                    }
                  />
                  <InfoRow
                    icon={Calendar}
                    label="Fecha del documento"
                    value={doc?.fechaDocumento ? formatFechaHora(doc.fechaDocumento) : '—'}
                  />
                  <InfoRow
                    icon={Clock}
                    label="Fecha de ingreso al sistema"
                    value={doc?.fechaIngreso ? formatFechaHora(doc.fechaIngreso) : '—'}
                  />
                  {doc?.observacion && (
                    <InfoRow icon={Building2} label="Observaciones" value={doc.observacion} />
                  )}
                </>
              )}
            </CardContent>
          </Card>

          {/* Archivos adjuntos */}
          <Card>
            <CardHeader className="pb-2">
              <div className="flex items-center justify-between">
                <CardTitle className="text-base flex items-center gap-2">
                  <Paperclip className="h-4 w-4 text-primary" />
                  Archivos adjuntos
                </CardTitle>
                <Link to="/archivos">
                  <Button variant="ghost" size="sm" className="text-xs gap-1 h-7">
                    Gestionar archivos
                  </Button>
                </Link>
              </div>
            </CardHeader>
            <CardContent>
              {loadingArchivos ? (
                <div className="space-y-2">
                  {[1, 2].map((i) => <Skeleton key={i} className="h-12 w-full rounded-lg" />)}
                </div>
              ) : (archivos ?? []).length === 0 ? (
                <div className="flex flex-col items-center gap-2 py-8 text-center">
                  <Paperclip className="h-8 w-8 text-muted-foreground/40" />
                  <p className="text-sm text-muted-foreground">Sin archivos adjuntos</p>
                  <Link to="/archivos">
                    <Button variant="outline" size="sm" className="gap-2 mt-1">
                      <Paperclip className="h-3.5 w-3.5" />
                      Subir archivo
                    </Button>
                  </Link>
                </div>
              ) : (
                <div className="space-y-2">
                  {(archivos ?? []).map((a) => (
                    <div key={a.id_archivo}
                      className="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-muted/40 hover:bg-muted/70 transition-colors">
                      <FileText className="h-4 w-4 text-primary shrink-0" />
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium truncate">{a.nombre_archivo ?? a.ruta_archivo ?? 'Archivo'}</p>
                        <p className="text-xs text-muted-foreground">
                          {a.fecha_subida ? formatRelativo(a.fecha_subida) : ''}
                        </p>
                      </div>
                      {a.url && (
                        <a href={a.url} target="_blank" rel="noreferrer" download>
                          <Button variant="ghost" size="icon" className="h-7 w-7" title="Descargar">
                            <Download className="h-3.5 w-3.5" />
                          </Button>
                        </a>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Panel lateral — historial */}
        <div className="space-y-5">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-base flex items-center gap-2">
                <GitBranch className="h-4 w-4 text-primary" />
                Historial de trámites
              </CardTitle>
              <CardDescription>Movimientos del documento</CardDescription>
            </CardHeader>
            <CardContent>
              {loadingHistorial ? (
                <div className="space-y-4">
                  {[1, 2, 3].map((i) => (
                    <div key={i} className="flex gap-3">
                      <Skeleton className="h-2 w-2 rounded-full shrink-0 mt-1.5" />
                      <div className="flex-1 space-y-1.5">
                        <Skeleton className="h-3 w-24" />
                        <Skeleton className="h-3 w-32" />
                      </div>
                    </div>
                  ))}
                </div>
              ) : (historial ?? []).length === 0 ? (
                <div className="text-center py-8">
                  <RefreshCw className="h-8 w-8 text-muted-foreground/40 mx-auto mb-2" />
                  <p className="text-sm text-muted-foreground">Sin movimientos registrados</p>
                </div>
              ) : (
                <div className="relative">
                  {/* Línea vertical */}
                  <div className="absolute left-1 top-2 bottom-2 w-0.5 bg-border" />
                  <div className="space-y-5 pl-6">
                    {(historial as HistorialItem[]).map((h) => {
                      const colorDot = TRAMITE_COLOR[h.id_estado_tramite ?? 0] ?? 'bg-muted-foreground';
                      const label = TRAMITE_LABEL[h.id_estado_tramite ?? 0] ?? 'Movimiento';
                      return (
                        <div key={h.id_seguimiento} className="relative">
                          <div className={cn('absolute -left-6 top-1 h-2.5 w-2.5 rounded-full border-2 border-background', colorDot)} />
                          <p className="text-xs font-semibold text-foreground">{label}</p>
                          <p className="text-xs text-muted-foreground mt-0.5">
                            {h.nombres ?? h.usuario ?? 'Sistema'}
                          </p>
                          {h.observaciones && (
                            <p className="text-xs text-muted-foreground/80 italic mt-0.5 line-clamp-2">
                              "{h.observaciones}"
                            </p>
                          )}
                          <p className="text-[10px] text-muted-foreground/60 mt-1">
                            {h.fecha_sistema ? formatRelativo(h.fecha_sistema) : ''}
                          </p>
                        </div>
                      );
                    })}
                  </div>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Acciones rápidas */}
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-base">Acciones</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              <Link to="/archivos" className="block">
                <Button variant="outline" className="w-full justify-start gap-2" size="sm">
                  <Paperclip className="h-3.5 w-3.5" />
                  Adjuntar archivo
                </Button>
              </Link>
              <Link to="/trazabilidad" className="block">
                <Button variant="outline" className="w-full justify-start gap-2" size="sm">
                  <GitBranch className="h-3.5 w-3.5" />
                  Ver trazabilidad
                </Button>
              </Link>
              <Button variant="outline" className="w-full justify-start gap-2" size="sm"
                onClick={() => navigate('/documentos')}>
                <ArrowLeft className="h-3.5 w-3.5" />
                Volver al listado
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
