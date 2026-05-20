import { useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { FilePreviewModal, type PreviewFile } from '@/components/shared/FilePreviewModal';
import {
  ArrowLeft, FileText, Calendar, User, Tag, Clock,
  CheckCircle2, GitBranch, Paperclip, Download, RefreshCw,
  AlertCircle, Hash, Building2, Send, Loader2, Trash2,
  Image as ImageIcon, FileSpreadsheet, File, Eye,
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Separator } from '@/components/ui/separator';
import { formatFechaHora, formatRelativo, cn } from '@/lib/utils';
import { useRole } from '@/hooks/useRole';
import { toast } from 'sonner';

// ── Helper universal: nunca renderizar un objeto como React child ─────────
function safeStr(val: unknown, fallback = '—'): string {
  if (val === null || val === undefined) return fallback;
  if (typeof val === 'string') return val || fallback;
  if (typeof val === 'number') return String(val);
  if (typeof val === 'object') {
    const o = val as Record<string, unknown>;
    const name = o.nombre ?? o.usuario ?? o.descripcion ?? o.label ?? o.nombre_completo;
    return safeStr(name, fallback);
  }
  return fallback;
}

// ── Estados reales de documento ───────────────────────────────────────────
const DOC_ESTADO: Record<number, { label: string; className: string }> = {
  1: { label: 'Registrado',   className: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' },
  2: { label: 'Despachado',   className: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' },
  3: { label: 'Recepcionado', className: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400' },
  4: { label: 'Terminado',    className: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' },
};

// ── Estados de trámite en el timeline ─────────────────────────────────────
const TRAMITE_ESTADO: Record<number, { label: string; dot: string; color: string }> = {
  1: { label: 'Generado',     dot: 'bg-indigo-500',  color: 'text-indigo-600 dark:text-indigo-400' },
  2: { label: 'Despachado',   dot: 'bg-amber-500',   color: 'text-amber-600 dark:text-amber-400' },
  3: { label: 'Recepcionado', dot: 'bg-emerald-500', color: 'text-emerald-600 dark:text-emerald-400' },
  4: { label: 'Derivado',     dot: 'bg-blue-500',    color: 'text-blue-600 dark:text-blue-400' },
  5: { label: 'Cerrado',      dot: 'bg-slate-400',   color: 'text-slate-500 dark:text-slate-400' },
  6: { label: 'Entregado',    dot: 'bg-teal-500',    color: 'text-teal-600 dark:text-teal-400' },
};

// ── Interfaces alineadas con el backend real ──────────────────────────────
interface Documento {
  idDocumento: number;
  numDocumento: number | null;
  numInterno: number | null;
  materia: string | null;
  asunto: string | null;
  tipoDocumento: { id: number | null; descripcion: string | null };
  estadoDocumento: { id: number | null; descripcion: string | null };
  ingresadoPor: { id: number | null; usuario: string | null; nombre: string };
  fechaDocumento: string | null;
  fechaIngreso: string | null;
  observacion: string | null;
  tramiteActual?: TramiteEvento | null;
}

interface TramiteEvento {
  idSeguimiento: number;
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

interface ArchivoItem {
  id_archivo: number;
  nombre_archivo: string | null;
  ruta_archivo: string | null;
  fecha_subida: string | null;
  url: string | null;
  preview_url: string | null;
  download_url: string | null;
}

// ── Icono según extensión del archivo ────────────────────────
function FileIcon({ nombre }: { nombre: string }) {
  const ext = nombre.split('.').pop()?.toLowerCase() ?? '';
  if (ext === 'pdf') return <FileText className="h-4 w-4 text-red-500" />;
  if (['png', 'jpg', 'jpeg', 'webp', 'gif'].includes(ext)) return <ImageIcon className="h-4 w-4 text-violet-500" />;
  if (['xls', 'xlsx'].includes(ext)) return <FileSpreadsheet className="h-4 w-4 text-emerald-600" />;
  if (['doc', 'docx'].includes(ext)) return <FileText className="h-4 w-4 text-blue-500" />;
  return <File className="h-4 w-4 text-muted-foreground" />;
}

// ── InfoRow reutilizable ──────────────────────────────────────────────────
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

// ── Página principal ──────────────────────────────────────────────────────
export function DocumentoDetallePage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const qc = useQueryClient();
  const { canDespachar, canRecepcionar, canDerivar, canTerminar, canDelete } = useRole();
  const idDocumento = Number(id);

  const { data: doc, isLoading, error } = useQuery({
    queryKey: ['documento', idDocumento],
    queryFn: async () => {
      const { data } = await apiClient.get<{ ok: boolean; data: Documento }>(`/documentos/${idDocumento}`);
      return data.data;
    },
    enabled: !isNaN(idDocumento),
    retry: 1,
  });

  // Trazabilidad (usa el endpoint correcto)
  const { data: trazabilidad, isLoading: loadingTraz } = useQuery({
    queryKey: ['documento-trazabilidad', idDocumento],
    queryFn: async () => {
      const { data } = await apiClient.get<{ ok: boolean; data: TramiteEvento[] }>(`/documentos/${idDocumento}/trazabilidad`);
      return data.data;
    },
    enabled: !isNaN(idDocumento),
  });

  const [previewFile, setPreviewFile] = useState<PreviewFile | null>(null);

  const { data: archivos, isLoading: loadingArchivos } = useQuery({
    queryKey: ['archivos', idDocumento],
    queryFn: async () => {
      const { data } = await apiClient.get<{ ok: boolean; data: ArchivoItem[] }>(`/archivos?idDocumento=${idDocumento}`);
      return data.data;
    },
    enabled: !isNaN(idDocumento),
  });

  // Acciones del flujo documental
  const accion = (endpoint: string, msg: string) => useMutation({
    mutationFn: () => apiClient.post(`/documentos/${idDocumento}/${endpoint}`, { observaciones: `${msg} desde detalle` }),
    onSuccess: () => {
      toast.success(`Documento ${msg.toLowerCase()} correctamente`);
      qc.invalidateQueries({ queryKey: ['documento', idDocumento] });
      qc.invalidateQueries({ queryKey: ['documento-trazabilidad', idDocumento] });
    },
    onError: (e: unknown) => toast.error((e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? `Error al ${msg.toLowerCase()}`),
  });

  // eslint-disable-next-line react-hooks/rules-of-hooks
  const despacharMut   = accion('despachar',   'Despachado');
  // eslint-disable-next-line react-hooks/rules-of-hooks
  const recepcionarMut = accion('recepcionar', 'Recepcionado');
  // eslint-disable-next-line react-hooks/rules-of-hooks
  const terminarMut    = accion('terminar',    'Terminado');
  // eslint-disable-next-line react-hooks/rules-of-hooks
  const eliminarMut    = useMutation({
    mutationFn: () => apiClient.delete(`/documentos/${idDocumento}`),
    onSuccess: () => {
      toast.success('Documento eliminado');
      qc.invalidateQueries({ queryKey: ['documentos'] });
      navigate('/documentos');
    },
    onError: () => toast.error('No se pudo eliminar el documento'),
  });

  // ── Guardias ─────────────────────────────────────────────────────────────
  if (isNaN(idDocumento)) {
    return (
      <div className="flex flex-col items-center justify-center py-20 gap-4">
        <AlertCircle className="h-12 w-12 text-destructive/60" />
        <p className="text-muted-foreground">ID de documento inválido.</p>
        <Button variant="outline" onClick={() => navigate('/documentos')}>Volver</Button>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center py-20 gap-4">
        <AlertCircle className="h-12 w-12 text-destructive/60" />
        <p className="text-muted-foreground">No se pudo cargar el documento #{idDocumento}.</p>
        <Button variant="outline" onClick={() => navigate('/documentos')}>Volver</Button>
      </div>
    );
  }

  const estadoId  = doc?.estadoDocumento?.id ?? 0;
  const estadoCfg = DOC_ESTADO[estadoId];
  const materia   = safeStr(doc?.materia ?? doc?.asunto);

  return (
    <div className="space-y-6 max-w-5xl mx-auto animate-fade-in">
      {/* Header */}
      <div className="flex items-start gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate('/documentos')} className="shrink-0 mt-0.5">
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div className="flex-1 min-w-0">
          {isLoading ? (
            <div className="space-y-2"><Skeleton className="h-7 w-2/3" /><Skeleton className="h-4 w-1/3" /></div>
          ) : (
            <>
              <h1 className="text-2xl font-bold leading-tight line-clamp-2">{materia}</h1>
              <div className="flex items-center gap-3 mt-1 flex-wrap">
                {doc?.numDocumento && (
                  <span className="text-sm text-muted-foreground font-mono">N° {doc.numDocumento}</span>
                )}
                {estadoCfg
                  ? <span className={cn('px-2.5 py-0.5 rounded-full text-xs font-semibold', estadoCfg.className)}>{estadoCfg.label}</span>
                  : doc?.estadoDocumento?.descripcion && (
                    <Badge variant="secondary">{safeStr(doc.estadoDocumento.descripcion)}</Badge>
                  )
                }
              </div>
            </>
          )}
        </div>
        <div className="shrink-0">
          <Link to="/documentos/nuevo">
            <Button variant="outline" size="sm" className="gap-2">
              <FileText className="h-3.5 w-3.5" />Nuevo
            </Button>
          </Link>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* ── Columna principal ─────────────────────────────────────────── */}
        <div className="lg:col-span-2 space-y-5">

          {/* Info del documento */}
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-base flex items-center gap-2">
                <FileText className="h-4 w-4 text-primary" />Información del Documento
              </CardTitle>
            </CardHeader>
            <CardContent className="divide-y">
              {isLoading ? (
                Array.from({ length: 5 }).map((_, i) => (
                  <div key={i} className="flex items-center gap-3 py-2.5">
                    <Skeleton className="h-7 w-7 rounded-lg" />
                    <div className="flex-1 space-y-1.5"><Skeleton className="h-3 w-16" /><Skeleton className="h-4 w-48" /></div>
                  </div>
                ))
              ) : (
                <>
                  <InfoRow icon={Hash}       label="Número"      value={doc?.numDocumento ? `N° ${doc.numDocumento}` : `ID ${doc?.idDocumento}`} />
                  <InfoRow icon={Tag}        label="Tipo"        value={safeStr(doc?.tipoDocumento?.descripcion)} />
                  <InfoRow icon={CheckCircle2} label="Estado"    value={estadoCfg?.label ?? safeStr(doc?.estadoDocumento?.descripcion)} />
                  <InfoRow icon={User}       label="Ingresado por" value={safeStr(doc?.ingresadoPor)} />
                  <InfoRow icon={Calendar}   label="Fecha documento" value={doc?.fechaDocumento ? formatFechaHora(doc.fechaDocumento) : '—'} />
                  <InfoRow icon={Clock}      label="Fecha ingreso"  value={doc?.fechaIngreso ? formatFechaHora(doc.fechaIngreso) : '—'} />
                  {doc?.observacion && (
                    <InfoRow icon={Building2} label="Observaciones" value={safeStr(doc.observacion)} />
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
                  <Paperclip className="h-4 w-4 text-primary" />Archivos adjuntos
                </CardTitle>
                <Link to="/archivos">
                  <Button variant="ghost" size="sm" className="text-xs h-7">Gestionar</Button>
                </Link>
              </div>
            </CardHeader>
            <CardContent>
              {loadingArchivos ? (
                <div className="space-y-2">{[1, 2].map((i) => <Skeleton key={i} className="h-12 w-full rounded-lg" />)}</div>
              ) : (archivos ?? []).length === 0 ? (
                <div className="py-8 text-center">
                  <Paperclip className="h-8 w-8 text-muted-foreground/40 mx-auto mb-2" />
                  <p className="text-sm text-muted-foreground">Sin archivos adjuntos</p>
                  <Link to="/archivos"><Button variant="outline" size="sm" className="gap-2 mt-2"><Paperclip className="h-3.5 w-3.5" />Subir archivo</Button></Link>
                </div>
              ) : (
                <div className="space-y-2">
                  {(archivos ?? []).map((a) => {
                    const nombre = safeStr(a.nombre_archivo ?? a.ruta_archivo, 'Archivo');
                    const canPreview = !!(a.preview_url ?? a.url);
                    return (
                      <div key={a.id_archivo}
                        className="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-muted/40 hover:bg-muted/70 transition-colors group">
                        {/* Icono */}
                        <div className="shrink-0"><FileIcon nombre={nombre} /></div>

                        {/* Nombre + fecha — clic abre modal */}
                        <button
                          className="flex-1 min-w-0 text-left"
                          onClick={() => {
                            if (!canPreview) return;
                            setPreviewFile({
                              id: a.id_archivo,
                              nombre,
                              previewUrl:  a.preview_url ?? a.url ?? '',
                              downloadUrl: a.download_url ?? a.url ?? '',
                            });
                          }}
                          title="Ver archivo"
                        >
                          <p className={cn(
                            'text-sm font-medium truncate transition-colors',
                            canPreview && 'group-hover:text-primary cursor-pointer'
                          )}>
                            {nombre}
                          </p>
                          <p className="text-xs text-muted-foreground">
                            {a.fecha_subida ? formatRelativo(a.fecha_subida) : ''}
                          </p>
                        </button>

                        {/* Acciones */}
                        <div className="flex items-center gap-1 shrink-0">
                          {canPreview && (
                            <Button
                              variant="ghost" size="icon" className="h-7 w-7" title="Vista previa"
                              onClick={() => setPreviewFile({
                                id: a.id_archivo,
                                nombre,
                                previewUrl:  a.preview_url ?? a.url ?? '',
                                downloadUrl: a.download_url ?? a.url ?? '',
                              })}
                            >
                              <Eye className="h-3.5 w-3.5" />
                            </Button>
                          )}
                          {(a.download_url ?? a.url) && (
                            <a href={a.download_url ?? a.url ?? ''} download>
                              <Button variant="ghost" size="icon" className="h-7 w-7" title="Descargar">
                                <Download className="h-3.5 w-3.5" />
                              </Button>
                            </a>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* ── Panel lateral ───────────────────────────────────────────────── */}
        <div className="space-y-5">

          {/* Acciones del flujo */}
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-base">Acciones</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              {canDespachar && estadoId !== 4 && (
                <Button
                  variant="outline" size="sm"
                  className="w-full justify-start gap-2"
                  disabled={despacharMut.isPending}
                  onClick={() => {
                    const obs = prompt('Observación para el despacho (opcional):') ?? '';
                    apiClient.post(`/documentos/${idDocumento}/despachar`, {
                      idDestino: doc?.tramiteActual?.destino?.id ?? 1,
                      tipoDestinatario: doc?.tramiteActual?.destino?.tipo ?? 'D',
                      idTipoDistribucion: 5, idTipoCompromiso: 1,
                      idEstadoCompromiso: 2, diasCompromiso: 0, observaciones: obs,
                    }).then(() => {
                      toast.success('Documento despachado');
                      qc.invalidateQueries({ queryKey: ['documento', idDocumento] });
                      qc.invalidateQueries({ queryKey: ['documento-trazabilidad', idDocumento] });
                    }).catch((e: unknown) => toast.error((e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al despachar'));
                  }}
                >
                  <Send className="h-3.5 w-3.5 text-amber-500" />Despachar
                </Button>
              )}

              {canRecepcionar && estadoId === 2 && (
                <Button
                  variant="outline" size="sm"
                  className="w-full justify-start gap-2"
                  onClick={() => recepcionarMut.mutate()}
                  disabled={recepcionarMut.isPending}
                >
                  <CheckCircle2 className="h-3.5 w-3.5 text-emerald-500" />Recepcionar
                </Button>
              )}

              {canTerminar && estadoId !== 4 && (
                <Button
                  variant="outline" size="sm"
                  className="w-full justify-start gap-2"
                  onClick={() => terminarMut.mutate()}
                  disabled={terminarMut.isPending}
                >
                  <CheckCircle2 className="h-3.5 w-3.5 text-slate-500" />Terminar
                </Button>
              )}

              <Separator />

              <Link to="/trazabilidad" className="block">
                <Button variant="ghost" size="sm" className="w-full justify-start gap-2">
                  <GitBranch className="h-3.5 w-3.5" />Ver trazabilidad completa
                </Button>
              </Link>
              <Link to="/archivos" className="block">
                <Button variant="ghost" size="sm" className="w-full justify-start gap-2">
                  <Paperclip className="h-3.5 w-3.5" />Adjuntar archivo
                </Button>
              </Link>
              <Button variant="ghost" size="sm" className="w-full justify-start gap-2" onClick={() => navigate('/documentos')}>
                <ArrowLeft className="h-3.5 w-3.5" />Volver al listado
              </Button>

              {canDelete && (
                <>
                  <Separator />
                  <Button
                    variant="ghost" size="sm"
                    className="w-full justify-start gap-2 text-destructive hover:bg-destructive/10"
                    onClick={() => { if (confirm('¿Eliminar este documento? Esta acción no se puede deshacer.')) eliminarMut.mutate(); }}
                    disabled={eliminarMut.isPending}
                  >
                    {eliminarMut.isPending ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Trash2 className="h-3.5 w-3.5" />}
                    Eliminar documento
                  </Button>
                </>
              )}
            </CardContent>
          </Card>

          {/* Timeline de trámites */}
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-base flex items-center gap-2">
                <GitBranch className="h-4 w-4 text-primary" />Historial
              </CardTitle>
              <CardDescription>
                {loadingTraz ? 'Cargando...' : `${(trazabilidad ?? []).length} movimiento${(trazabilidad ?? []).length !== 1 ? 's' : ''}`}
              </CardDescription>
            </CardHeader>
            <CardContent>
              {loadingTraz ? (
                <div className="space-y-4">
                  {[1, 2, 3].map((i) => (
                    <div key={i} className="flex gap-3">
                      <Skeleton className="h-2.5 w-2.5 rounded-full shrink-0 mt-1.5" />
                      <div className="flex-1 space-y-1.5"><Skeleton className="h-3 w-24" /><Skeleton className="h-3 w-32" /></div>
                    </div>
                  ))}
                </div>
              ) : (trazabilidad ?? []).length === 0 ? (
                <div className="py-8 text-center">
                  <RefreshCw className="h-7 w-7 text-muted-foreground/40 mx-auto mb-2" />
                  <p className="text-sm text-muted-foreground">Sin movimientos</p>
                </div>
              ) : (
                <div className="relative">
                  <div className="absolute left-1 top-2 bottom-2 w-0.5 bg-border" />
                  <div className="space-y-5 pl-6">
                    {(trazabilidad ?? []).map((ev) => {
                      const cfg = TRAMITE_ESTADO[ev.estadoTramite?.id ?? 0];
                      const nombreUsuario = safeStr(ev.usuario);
                      const proc  = safeStr(ev.procedencia?.descripcion);
                      const dest  = safeStr(ev.destino?.descripcion);
                      return (
                        <div key={ev.idSeguimiento} className="relative">
                          <div className={cn('absolute -left-6 top-1 h-2.5 w-2.5 rounded-full border-2 border-background', cfg?.dot ?? 'bg-muted-foreground')} />
                          <p className={cn('text-xs font-semibold', cfg?.color ?? 'text-foreground')}>
                            {cfg?.label ?? safeStr(ev.estadoTramite?.descripcion, 'Movimiento')}
                          </p>
                          {(proc !== '—' || dest !== '—') && (
                            <p className="text-xs text-muted-foreground mt-0.5 flex items-center gap-1">
                              <span>{proc}</span>
                              <span>→</span>
                              <span className="font-medium text-foreground">{dest}</span>
                            </p>
                          )}
                          <p className="text-xs text-muted-foreground mt-0.5">
                            {nombreUsuario}
                            {ev.usuario?.usuario && (
                              <span className="opacity-60 ml-1">(@{ev.usuario.usuario})</span>
                            )}
                          </p>
                          {ev.observaciones && (
                            <p className="text-xs text-muted-foreground/80 italic mt-0.5 line-clamp-2">"{safeStr(ev.observaciones)}"</p>
                          )}
                          <p className="text-[10px] text-muted-foreground/60 mt-1">
                            {ev.fechaSistema ? formatRelativo(ev.fechaSistema) : ''}
                          </p>
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

      {/* Modal visor de archivos */}
      <FilePreviewModal
        open={!!previewFile}
        onClose={() => setPreviewFile(null)}
        file={previewFile}
      />
    </div>
  );
}
