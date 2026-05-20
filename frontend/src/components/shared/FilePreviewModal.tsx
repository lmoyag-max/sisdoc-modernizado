import { useEffect, useState } from 'react';
import {
  X, Download, FileText, Image as ImageIcon, FileSpreadsheet,
  File, Loader2, AlertCircle, ZoomIn, ZoomOut,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

// ── Tipos de archivo ──────────────────────────────────────────
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
  pdf:           FileText,
  image:         ImageIcon,
  'office-word': FileText,
  'office-excel':FileSpreadsheet,
  text:          FileText,
  unsupported:   File,
};

const KIND_COLOR: Record<FileKind, string> = {
  pdf:           'text-red-500',
  image:         'text-violet-500',
  'office-word': 'text-blue-500',
  'office-excel':'text-emerald-500',
  text:          'text-muted-foreground',
  unsupported:   'text-muted-foreground',
};

// ── Props ────────────────────────────────────────────────────
export interface PreviewFile {
  id: number | string;
  nombre: string;
  previewUrl: string;  // /api/v1/archivos/:id/preview
  downloadUrl: string; // /api/v1/archivos/:id/download
}

interface Props {
  open: boolean;
  onClose: () => void;
  file: PreviewFile | null;
}

// ── Visor PDF ─────────────────────────────────────────────────
function PdfViewer({ src }: { src: string }) {
  const [loading, setLoading] = useState(true);
  const [error, setError]   = useState(false);

  return (
    <div className="relative flex-1 min-h-0 bg-muted/30 rounded-lg overflow-hidden">
      {loading && (
        <div className="absolute inset-0 flex items-center justify-center bg-muted/60 z-10">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
        </div>
      )}
      {error ? (
        <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 text-muted-foreground">
          <AlertCircle className="h-10 w-10 text-destructive/60" />
          <p className="text-sm">No se pudo cargar el PDF.</p>
        </div>
      ) : (
        <iframe
          src={src}
          title="Vista previa PDF"
          className="w-full h-full border-0"
          onLoad={() => setLoading(false)}
          onError={() => { setLoading(false); setError(true); }}
        />
      )}
    </div>
  );
}

// ── Visor imagen ──────────────────────────────────────────────
function ImageViewer({ src, nombre }: { src: string; nombre: string }) {
  const [loading, setLoading] = useState(true);
  const [error, setError]   = useState(false);
  const [zoom, setZoom]     = useState(1);

  return (
    <div className="flex-1 min-h-0 flex flex-col items-center justify-center bg-muted/30 rounded-lg overflow-auto relative">
      {/* Controles zoom */}
      <div className="absolute top-3 right-3 z-10 flex gap-1 bg-background/80 backdrop-blur rounded-lg p-1">
        <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => setZoom((z) => Math.max(0.3, z - 0.2))}>
          <ZoomOut className="h-3.5 w-3.5" />
        </Button>
        <span className="text-xs flex items-center px-1 text-muted-foreground min-w-10 justify-center">
          {Math.round(zoom * 100)}%
        </span>
        <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => setZoom((z) => Math.min(3, z + 0.2))}>
          <ZoomIn className="h-3.5 w-3.5" />
        </Button>
        <Button variant="ghost" size="icon" className="h-7 w-7" onClick={() => setZoom(1)}>
          <ZoomOut className="h-3.5 w-3.5 rotate-45" />
        </Button>
      </div>

      {loading && (
        <div className="absolute inset-0 flex items-center justify-center">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
        </div>
      )}
      {error ? (
        <div className="flex flex-col items-center gap-3 text-muted-foreground p-10">
          <AlertCircle className="h-10 w-10 text-destructive/60" />
          <p className="text-sm">No se pudo cargar la imagen.</p>
        </div>
      ) : (
        <div className="p-4 overflow-auto w-full h-full flex items-center justify-center">
          <img
            src={src}
            alt={nombre}
            className="max-w-none transition-transform duration-200 rounded shadow-lg"
            style={{ transform: `scale(${zoom})`, transformOrigin: 'center center' }}
            onLoad={() => setLoading(false)}
            onError={() => { setLoading(false); setError(true); }}
          />
        </div>
      )}
    </div>
  );
}

// ── Visor texto plano ─────────────────────────────────────────
function TextViewer({ src }: { src: string }) {
  const [content, setContent] = useState<string | null>(null);
  const [error, setError]    = useState(false);

  useEffect(() => {
    fetch(src, { credentials: 'include' })
      .then((r) => r.text())
      .then(setContent)
      .catch(() => setError(true));
  }, [src]);

  if (error) return (
    <div className="flex-1 flex flex-col items-center justify-center gap-3 text-muted-foreground">
      <AlertCircle className="h-8 w-8 text-destructive/60" />
      <p className="text-sm">No se pudo cargar el archivo.</p>
    </div>
  );

  if (content === null) return (
    <div className="flex-1 flex items-center justify-center">
      <Loader2 className="h-8 w-8 animate-spin text-primary" />
    </div>
  );

  return (
    <div className="flex-1 min-h-0 overflow-auto bg-muted/30 rounded-lg p-4">
      <pre className="text-xs text-foreground whitespace-pre-wrap font-mono">{content}</pre>
    </div>
  );
}

// ── Mensaje "no soportado" ────────────────────────────────────
function UnsupportedViewer({ kind, downloadUrl, nombre }: { kind: FileKind; downloadUrl: string; nombre: string }) {
  const msgs: Record<FileKind, string> = {
    'office-word':  'Los archivos Word (.doc, .docx) no pueden visualizarse directamente en el navegador.',
    'office-excel': 'Los archivos Excel (.xls, .xlsx) no pueden visualizarse directamente en el navegador.',
    unsupported:    'Este tipo de archivo no puede visualizarse en el navegador.',
    pdf: '', image: '', text: '',
  };

  return (
    <div className="flex-1 flex flex-col items-center justify-center gap-5 bg-muted/20 rounded-lg p-10">
      <div className="flex h-20 w-20 items-center justify-center rounded-2xl bg-muted">
        <File className="h-10 w-10 text-muted-foreground" />
      </div>
      <div className="text-center space-y-2 max-w-sm">
        <p className="font-semibold text-foreground">{nombre}</p>
        <p className="text-sm text-muted-foreground">{msgs[kind]}</p>
        <p className="text-xs text-muted-foreground">Descarga el archivo para revisarlo en tu aplicación local.</p>
      </div>
      <a href={downloadUrl} download>
        <Button className="gap-2">
          <Download className="h-4 w-4" />
          Descargar archivo
        </Button>
      </a>
    </div>
  );
}

// ── Modal principal ───────────────────────────────────────────
export function FilePreviewModal({ open, onClose, file }: Props) {
  // Cerrar con Escape
  useEffect(() => {
    if (!open) return;
    const handler = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, [open, onClose]);

  // Bloquear scroll del body
  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [open]);

  if (!open || !file) return null;

  const kind = detectKind(file.nombre);
  const KindIcon = KIND_ICON[kind];
  const kindColor = KIND_COLOR[kind];

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      {/* Overlay */}
      <div
        className="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onClick={onClose}
      />

      {/* Panel modal */}
      <div className="relative w-full max-w-6xl h-[90vh] bg-card border border-border rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-fade-in">

        {/* Header */}
        <div className="flex items-center gap-3 px-5 py-4 border-b border-border shrink-0">
          <div className={cn('flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted', kindColor)}>
            <KindIcon className="h-4 w-4" />
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-semibold text-foreground truncate">{file.nombre}</p>
            <p className="text-xs text-muted-foreground capitalize">
              {kind === 'office-word' ? 'Word' : kind === 'office-excel' ? 'Excel' : kind.toUpperCase()}
            </p>
          </div>
          <div className="flex items-center gap-2 shrink-0">
            <a href={file.downloadUrl} download onClick={(e) => e.stopPropagation()}>
              <Button variant="outline" size="sm" className="gap-2">
                <Download className="h-3.5 w-3.5" />
                Descargar
              </Button>
            </a>
            <Button variant="ghost" size="icon" onClick={onClose} className="h-8 w-8" title="Cerrar (Esc)">
              <X className="h-4 w-4" />
            </Button>
          </div>
        </div>

        {/* Contenido del visor */}
        <div className="flex-1 min-h-0 p-4 flex flex-col">
          {kind === 'pdf'   && <PdfViewer src={file.previewUrl} />}
          {kind === 'image' && <ImageViewer src={file.previewUrl} nombre={file.nombre} />}
          {kind === 'text'  && <TextViewer src={file.previewUrl} />}
          {(kind === 'office-word' || kind === 'office-excel' || kind === 'unsupported') && (
            <UnsupportedViewer kind={kind} downloadUrl={file.downloadUrl} nombre={file.nombre} />
          )}
        </div>
      </div>
    </div>
  );
}
