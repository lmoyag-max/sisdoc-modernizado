import { useEffect, useRef, useState } from 'react';
import {
  X, Download, FileText, Image as ImageIcon, FileSpreadsheet,
  File, Loader2, AlertCircle, ZoomIn, ZoomOut, RefreshCw,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { apiClient } from '@/lib/api/client';
import { cn } from '@/lib/utils';

// ── Tipos ─────────────────────────────────────────────────────
type FileKind = 'pdf' | 'image' | 'office-word' | 'office-excel' | 'text' | 'unsupported';

function detectKind(nombre: string): FileKind {
  const ext = nombre.split('.').pop()?.toLowerCase() ?? '';
  if (ext === 'pdf') return 'pdf';
  if (['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'].includes(ext)) return 'image';
  if (['doc', 'docx'].includes(ext)) return 'office-word';
  if (['xls', 'xlsx'].includes(ext)) return 'office-excel';
  if (['txt', 'csv'].includes(ext)) return 'text';
  return 'unsupported';
}

const KIND_ICON: Record<FileKind, React.ComponentType<{ className?: string }>> = {
  pdf:            FileText,
  image:          ImageIcon,
  'office-word':  FileText,
  'office-excel': FileSpreadsheet,
  text:           FileText,
  unsupported:    File,
};
const KIND_COLOR: Record<FileKind, string> = {
  pdf:            'text-red-500',
  image:          'text-violet-500',
  'office-word':  'text-blue-500',
  'office-excel': 'text-emerald-500',
  text:           'text-muted-foreground',
  unsupported:    'text-muted-foreground',
};
const KIND_LABEL: Record<FileKind, string> = {
  pdf: 'PDF', image: 'Imagen', 'office-word': 'Word',
  'office-excel': 'Excel', text: 'Texto', unsupported: 'Archivo',
};

// ── Props ─────────────────────────────────────────────────────
export interface PreviewFile {
  id: number | string;
  nombre: string;
  previewUrl: string;   // /api/v1/archivos/:id/preview
  downloadUrl: string;  // /api/v1/archivos/:id/download
}
interface Props {
  open: boolean;
  onClose: () => void;
  file: PreviewFile | null;
}

// ── Hook: descarga blob autenticada con apiClient ─────────────
type BlobState = { status: 'idle' | 'loading' | 'ready' | 'error'; blobUrl: string | null };

function useBlobUrl(previewUrl: string | null, enabled: boolean) {
  const [state, setState] = useState<BlobState>({ status: 'idle', blobUrl: null });
  const prevUrl = useRef<string | null>(null);

  useEffect(() => {
    if (!enabled || !previewUrl) {
      setState({ status: 'idle', blobUrl: null });
      return;
    }
    // Ya cargado para esta misma URL: no recargar
    if (prevUrl.current === previewUrl && state.status === 'ready') return;

    let cancelled = false;
    prevUrl.current = previewUrl;
    setState({ status: 'loading', blobUrl: null });

    apiClient
      .get<Blob>(previewUrl, { responseType: 'blob' })
      .then((res) => {
        if (cancelled) return;
        const url = URL.createObjectURL(res.data);
        setState({ status: 'ready', blobUrl: url });
      })
      .catch(() => {
        if (!cancelled) setState({ status: 'error', blobUrl: null });
      });

    return () => {
      cancelled = true;
      // Revocamos cuando la URL cambie, no al unmount inmediato
      // (el iframe todavía puede estar usándola)
    };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [previewUrl, enabled]);

  // Limpiar blob URL al cerrar/cambiar archivo
  useEffect(() => {
    return () => {
      if (state.blobUrl) URL.revokeObjectURL(state.blobUrl);
    };
  }, [state.blobUrl]);

  return state;
}

// ── Descarga autenticada (para el botón "Descargar") ──────────
async function downloadAuthenticated(downloadUrl: string, nombre: string) {
  try {
    const res = await apiClient.get<Blob>(downloadUrl, { responseType: 'blob' });
    const url  = URL.createObjectURL(res.data);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = nombre;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  } catch {
    alert('No se pudo descargar el archivo.');
  }
}

// ── Visor PDF ─────────────────────────────────────────────────
function PdfViewer({ blobUrl }: { blobUrl: string }) {
  return (
    <iframe
      src={blobUrl}
      title="Vista previa PDF"
      className="w-full h-full border-0 rounded-lg"
    />
  );
}

// ── Visor imagen ──────────────────────────────────────────────
function ImageViewer({ blobUrl, nombre }: { blobUrl: string; nombre: string }) {
  const [zoom, setZoom] = useState(1);
  return (
    <div className="relative w-full h-full overflow-auto flex items-center justify-center bg-muted/30 rounded-lg">
      {/* Controles zoom */}
      <div className="absolute top-3 right-3 z-10 flex gap-1 bg-background/90 backdrop-blur rounded-lg p-1 shadow">
        <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => setZoom((z) => Math.max(0.2, +(z - 0.2).toFixed(1)))}>
          <ZoomOut className="h-3.5 w-3.5" />
        </Button>
        <span className="text-xs flex items-center px-2 text-muted-foreground min-w-12 justify-center font-mono">
          {Math.round(zoom * 100)}%
        </span>
        <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => setZoom((z) => Math.min(4, +(z + 0.2).toFixed(1)))}>
          <ZoomIn className="h-3.5 w-3.5" />
        </Button>
        <Button variant="ghost" size="icon" className="h-7 w-7" title="Restablecer" onClick={() => setZoom(1)}>
          <RefreshCw className="h-3.5 w-3.5" />
        </Button>
      </div>
      <img
        src={blobUrl}
        alt={nombre}
        className="max-w-none transition-transform duration-200 rounded shadow-lg"
        style={{ transform: `scale(${zoom})`, transformOrigin: 'center center' }}
      />
    </div>
  );
}

// ── Visor texto ───────────────────────────────────────────────
function TextViewer({ blobUrl }: { blobUrl: string }) {
  const [text, setText] = useState<string | null>(null);

  useEffect(() => {
    fetch(blobUrl)
      .then((r) => r.text())
      .then(setText)
      .catch(() => setText('[Error al leer el archivo]'));
  }, [blobUrl]);

  if (text === null) return (
    <div className="flex-1 flex items-center justify-center">
      <Loader2 className="h-7 w-7 animate-spin text-primary" />
    </div>
  );

  return (
    <div className="flex-1 overflow-auto bg-muted/30 rounded-lg p-4">
      <pre className="text-xs text-foreground whitespace-pre-wrap font-mono leading-relaxed">{text}</pre>
    </div>
  );
}

// ── No soportado ──────────────────────────────────────────────
function UnsupportedViewer({ kind, downloadUrl, nombre }: { kind: FileKind; downloadUrl: string; nombre: string }) {
  const msgs: Partial<Record<FileKind, string>> = {
    'office-word':  'Los archivos Word (.doc, .docx) no pueden visualizarse en el navegador.',
    'office-excel': 'Los archivos Excel (.xls, .xlsx) no pueden visualizarse en el navegador.',
  };
  return (
    <div className="flex-1 flex flex-col items-center justify-center gap-5 bg-muted/20 rounded-lg p-10 text-center">
      <div className="flex h-20 w-20 items-center justify-center rounded-2xl bg-muted">
        <File className="h-10 w-10 text-muted-foreground" />
      </div>
      <div className="space-y-1.5 max-w-sm">
        <p className="font-semibold text-foreground">{nombre}</p>
        <p className="text-sm text-muted-foreground">
          {msgs[kind] ?? 'Este tipo de archivo no puede visualizarse en el navegador.'}
        </p>
        <p className="text-xs text-muted-foreground/70">Descárgalo para abrirlo en tu aplicación local.</p>
      </div>
      <Button className="gap-2 mt-2" onClick={() => downloadAuthenticated(downloadUrl, nombre)}>
        <Download className="h-4 w-4" />Descargar archivo
      </Button>
    </div>
  );
}

// ── Estado de carga / error ───────────────────────────────────
function LoadingState() {
  return (
    <div className="flex-1 flex flex-col items-center justify-center gap-4">
      <Loader2 className="h-10 w-10 animate-spin text-primary" />
      <p className="text-sm text-muted-foreground">Cargando archivo…</p>
    </div>
  );
}

function ErrorState({ onRetry }: { onRetry: () => void }) {
  return (
    <div className="flex-1 flex flex-col items-center justify-center gap-4">
      <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive/10">
        <AlertCircle className="h-7 w-7 text-destructive" />
      </div>
      <div className="text-center space-y-1">
        <p className="font-semibold text-foreground">No se pudo cargar el archivo</p>
        <p className="text-sm text-muted-foreground">Verifica tu conexión o intenta de nuevo.</p>
      </div>
      <Button variant="outline" onClick={onRetry} className="gap-2">
        <RefreshCw className="h-4 w-4" />Reintentar
      </Button>
    </div>
  );
}

// ── Modal principal ───────────────────────────────────────────
export function FilePreviewModal({ open, onClose, file }: Props) {
  const kind     = file ? detectKind(file.nombre) : 'unsupported';
  const canFetch = open && !!file && kind !== 'office-word' && kind !== 'office-excel' && kind !== 'unsupported';

  const { status, blobUrl } = useBlobUrl(file?.previewUrl ?? null, canFetch);

  // Función para forzar recarga (cambia previewUrl para re-trigger el efecto)
  const [retryKey, setRetryKey] = useState(0);
  const previewUrlWithRetry = file?.previewUrl ? `${file.previewUrl}?r=${retryKey}` : null;
  const { status: retryStatus, blobUrl: retryBlobUrl } = useBlobUrl(previewUrlWithRetry, canFetch && retryKey > 0);
  const finalStatus  = retryKey > 0 ? retryStatus  : status;
  const finalBlobUrl = retryKey > 0 ? retryBlobUrl : blobUrl;

  // Cerrar con Escape
  useEffect(() => {
    if (!open) return;
    const h = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [open, onClose]);

  // Bloquear scroll del body
  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [open]);

  if (!open || !file) return null;

  const KindIcon  = KIND_ICON[kind];
  const kindColor = KIND_COLOR[kind];
  const kindLabel = KIND_LABEL[kind];

  const showViewer = canFetch && finalStatus === 'ready' && finalBlobUrl;
  const showLoading = canFetch && finalStatus === 'loading';
  const showError   = canFetch && finalStatus === 'error';
  const showUnsupported = !canFetch;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6">
      {/* Overlay */}
      <div className="absolute inset-0 bg-black/65 backdrop-blur-sm" onClick={onClose} />

      {/* Panel */}
      <div className="relative w-full max-w-6xl h-[90vh] bg-card border border-border rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-fade-in">

        {/* Header */}
        <div className="flex items-center gap-3 px-5 py-3.5 border-b border-border shrink-0 bg-card">
          <div className={cn('flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted', kindColor)}>
            <KindIcon className="h-4 w-4" />
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-semibold text-foreground truncate leading-tight">{file.nombre}</p>
            <p className="text-xs text-muted-foreground">{kindLabel}</p>
          </div>
          <div className="flex items-center gap-2 shrink-0">
            <Button
              variant="outline" size="sm" className="gap-2 hidden sm:flex"
              onClick={() => downloadAuthenticated(file.downloadUrl, file.nombre)}
            >
              <Download className="h-3.5 w-3.5" />Descargar
            </Button>
            <Button variant="ghost" size="icon" className="h-8 w-8" onClick={onClose} title="Cerrar (Esc)">
              <X className="h-4 w-4" />
            </Button>
          </div>
        </div>

        {/* Contenido */}
        <div className="flex-1 min-h-0 p-4 flex flex-col">
          {showLoading    && <LoadingState />}
          {showError      && <ErrorState onRetry={() => setRetryKey((k) => k + 1)} />}
          {showUnsupported && <UnsupportedViewer kind={kind} downloadUrl={file.downloadUrl} nombre={file.nombre} />}
          {showViewer && kind === 'pdf'   && <PdfViewer   blobUrl={finalBlobUrl!} />}
          {showViewer && kind === 'image' && <ImageViewer blobUrl={finalBlobUrl!} nombre={file.nombre} />}
          {showViewer && kind === 'text'  && <TextViewer  blobUrl={finalBlobUrl!} />}
        </div>

        {/* Footer móvil — botón descargar */}
        <div className="sm:hidden px-4 pb-4 shrink-0">
          <Button variant="outline" className="w-full gap-2"
            onClick={() => downloadAuthenticated(file.downloadUrl, file.nombre)}>
            <Download className="h-4 w-4" />Descargar
          </Button>
        </div>
      </div>
    </div>
  );
}
