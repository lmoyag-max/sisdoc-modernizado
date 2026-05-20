import { useEffect, useState } from 'react';
import {
  X, Download, FileText, Image as ImageIcon, FileSpreadsheet,
  File, Loader2, AlertCircle, ZoomIn, ZoomOut, RefreshCw,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useAuthStore } from '@/stores/auth.store';
import { cn } from '@/lib/utils';

// ── Tipos ─────────────────────────────────────────────────────
type FileKind = 'pdf' | 'image' | 'office-word' | 'office-excel' | 'text' | 'unsupported';

function detectKind(nombre: string): FileKind {
  const ext = nombre.split('.').pop()?.toLowerCase() ?? '';
  if (ext === 'pdf')                                   return 'pdf';
  if (['png','jpg','jpeg','webp','gif','svg'].includes(ext)) return 'image';
  if (['doc','docx'].includes(ext))                    return 'office-word';
  if (['xls','xlsx'].includes(ext))                    return 'office-excel';
  if (['txt','csv'].includes(ext))                     return 'text';
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
  pdf:'PDF', image:'Imagen', 'office-word':'Word',
  'office-excel':'Excel', text:'Texto', unsupported:'Archivo',
};

// ── Props ─────────────────────────────────────────────────────
export interface PreviewFile {
  id:          number | string;
  nombre:      string;
  /** URL pública /uploads/{ruta} — se usa directamente en iframe/img */
  previewUrl:  string;
  /** /api/v1/archivos/:id/download — requiere JWT, da nombre original */
  downloadUrl: string;
}
interface Props {
  open:    boolean;
  onClose: () => void;
  file:    PreviewFile | null;
}

// ── Descarga autenticada con fetch nativo ─────────────────────
async function downloadFile(downloadUrl: string, nombre: string, token: string | null) {
  try {
    const res = await fetch(downloadUrl, {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const blob = await res.blob();
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), { href: url, download: nombre });
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => URL.revokeObjectURL(url), 2000);
  } catch (e) {
    console.error('[FilePreview] download error', e);
    // Fallback: descarga directa sin auth (funciona para archivos públicos)
    const a = Object.assign(document.createElement('a'), { href: downloadUrl, download: nombre, target: '_blank' });
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }
}

// ── Visor PDF ─────────────────────────────────────────────────
function PdfViewer({ src }: { src: string }) {
  const [status, setStatus] = useState<'loading'|'ready'|'error'>('loading');

  return (
    <div className="relative flex-1 min-h-0 bg-muted/30 rounded-lg overflow-hidden">
      {status === 'loading' && (
        <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 z-10 bg-muted/60">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
          <p className="text-sm text-muted-foreground">Cargando PDF…</p>
        </div>
      )}
      {status === 'error' && (
        <div className="absolute inset-0 flex flex-col items-center justify-center gap-3">
          <AlertCircle className="h-10 w-10 text-destructive/60" />
          <p className="text-sm text-muted-foreground">No se pudo cargar el PDF.</p>
          <p className="text-xs text-muted-foreground/70">Usa el botón Descargar para verlo localmente.</p>
        </div>
      )}
      <iframe
        key={src}
        src={src}
        title="Vista previa PDF"
        className="w-full h-full border-0"
        onLoad={() => setStatus('ready')}
        onError={() => setStatus('error')}
      />
    </div>
  );
}

// ── Visor imagen ──────────────────────────────────────────────
function ImageViewer({ src, nombre }: { src: string; nombre: string }) {
  const [status, setStatus] = useState<'loading'|'ready'|'error'>('loading');
  const [zoom, setZoom]     = useState(1);

  return (
    <div className="relative flex-1 min-h-0 overflow-auto flex items-center justify-center bg-muted/30 rounded-lg">
      {/* Zoom controls */}
      <div className="absolute top-3 right-3 z-10 flex gap-1 bg-background/90 backdrop-blur rounded-lg p-1 shadow">
        <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => setZoom(z => Math.max(0.2, +(z-0.2).toFixed(1)))}>
          <ZoomOut className="h-3.5 w-3.5" />
        </Button>
        <span className="text-xs flex items-center px-2 text-muted-foreground min-w-12 justify-center font-mono">
          {Math.round(zoom * 100)}%
        </span>
        <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => setZoom(z => Math.min(4, +(z+0.2).toFixed(1)))}>
          <ZoomIn className="h-3.5 w-3.5" />
        </Button>
        <Button variant="ghost" size="icon" className="h-7 w-7" title="Reset" onClick={() => setZoom(1)}>
          <RefreshCw className="h-3.5 w-3.5" />
        </Button>
      </div>

      {status === 'loading' && (
        <div className="absolute inset-0 flex items-center justify-center bg-muted/60 z-10">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
        </div>
      )}
      {status === 'error' ? (
        <div className="flex flex-col items-center gap-3 text-muted-foreground">
          <AlertCircle className="h-10 w-10 text-destructive/60" />
          <p className="text-sm">No se pudo cargar la imagen.</p>
        </div>
      ) : (
        <img
          key={src}
          src={src}
          alt={nombre}
          className="max-w-none transition-transform duration-150 rounded shadow-lg"
          style={{ transform: `scale(${zoom})`, transformOrigin: 'center' }}
          onLoad={() => setStatus('ready')}
          onError={() => setStatus('error')}
        />
      )}
    </div>
  );
}

// ── Visor texto ───────────────────────────────────────────────
function TextViewer({ src }: { src: string }) {
  const [text, setText]   = useState<string | null>(null);
  const [error, setError] = useState(false);

  useEffect(() => {
    setText(null); setError(false);
    fetch(src)
      .then(r => { if (!r.ok) throw new Error(); return r.text(); })
      .then(setText)
      .catch(() => setError(true));
  }, [src]);

  if (error) return (
    <div className="flex-1 flex flex-col items-center justify-center gap-3 text-muted-foreground">
      <AlertCircle className="h-8 w-8 text-destructive/60" />
      <p className="text-sm">No se pudo leer el archivo de texto.</p>
    </div>
  );
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
function UnsupportedViewer({ kind, onDownload, nombre }: {
  kind: FileKind; nombre: string; onDownload: () => void;
}) {
  const msgs: Partial<Record<FileKind, string>> = {
    'office-word':  'Los archivos Word no pueden visualizarse directamente en el navegador.',
    'office-excel': 'Los archivos Excel no pueden visualizarse directamente en el navegador.',
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
        <p className="text-xs text-muted-foreground/70">
          Descárgalo para abrirlo con tu aplicación local.
        </p>
      </div>
      <Button className="gap-2" onClick={onDownload}>
        <Download className="h-4 w-4" />Descargar archivo
      </Button>
    </div>
  );
}

// ── Modal principal ───────────────────────────────────────────
export function FilePreviewModal({ open, onClose, file }: Props) {
  const token   = useAuthStore(s => s.accessToken);
  const kind    = file ? detectKind(file.nombre) : 'unsupported';
  const KindIcon = KIND_ICON[kind];

  const handleDownload = () => {
    if (!file) return;
    downloadFile(file.downloadUrl, file.nombre, token);
  };

  // Cerrar con Escape
  useEffect(() => {
    if (!open) return;
    const h = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [open, onClose]);

  // Bloquear scroll
  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [open]);

  if (!open || !file) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6">
      {/* Overlay */}
      <div className="absolute inset-0 bg-black/65 backdrop-blur-sm" onClick={onClose} />

      {/* Panel */}
      <div className="relative w-full max-w-6xl h-[90vh] bg-card border border-border rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-fade-in">

        {/* Header */}
        <div className="flex items-center gap-3 px-5 py-3.5 border-b border-border shrink-0">
          <div className={cn('flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted', KIND_COLOR[kind])}>
            <KindIcon className="h-4 w-4" />
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-semibold text-foreground truncate">{file.nombre}</p>
            <p className="text-xs text-muted-foreground">{KIND_LABEL[kind]}</p>
          </div>
          <div className="flex items-center gap-2 shrink-0">
            <Button variant="outline" size="sm" className="gap-2 hidden sm:flex" onClick={handleDownload}>
              <Download className="h-3.5 w-3.5" />Descargar
            </Button>
            <Button variant="ghost" size="icon" className="h-8 w-8" onClick={onClose} title="Cerrar (Esc)">
              <X className="h-4 w-4" />
            </Button>
          </div>
        </div>

        {/* Visor */}
        <div className="flex-1 min-h-0 p-4 flex flex-col">
          {kind === 'pdf'   && <PdfViewer   src={file.previewUrl} />}
          {kind === 'image' && <ImageViewer src={file.previewUrl} nombre={file.nombre} />}
          {kind === 'text'  && <TextViewer  src={file.previewUrl} />}
          {(kind === 'office-word' || kind === 'office-excel' || kind === 'unsupported') && (
            <UnsupportedViewer kind={kind} nombre={file.nombre} onDownload={handleDownload} />
          )}
        </div>

        {/* Footer móvil */}
        <div className="sm:hidden px-4 pb-4 shrink-0">
          <Button variant="outline" className="w-full gap-2" onClick={handleDownload}>
            <Download className="h-4 w-4" />Descargar
          </Button>
        </div>
      </div>
    </div>
  );
}
