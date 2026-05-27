import { useState, useEffect, useRef } from 'react';
import { X, Printer, Download, FileText, Loader2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { generarNominaPDF, type NominaData } from '@/lib/utils/nomina.generator';

interface NominaModalProps {
  open:    boolean;
  onClose: () => void;
  data:    NominaData;
  /** Permite navegar al documento después de cerrar el modal */
  onNavigate?: () => void;
}

/**
 * Carga una imagen desde `logoPath` usando fetch (same-origin a través del proxy de Vite),
 * la convierte a JPEG vía canvas con fondo blanco (elimina transparencia RGBA).
 *
 * Por qué fetch + blob URL en lugar de new Image() con crossOrigin='anonymous':
 *   La ruta /uploads/config/* es servida por express.static. Aunque el CORS middleware
 *   de Express está registrado antes, en algunas versiones de Helmet la cabecera
 *   Access-Control-Allow-Origin puede no propagarse al blob de la respuesta estática,
 *   lo que contamina el canvas (canvas taint) y hace que toDataURL() lance SecurityError.
 *   Con fetch() + URL.createObjectURL(), el blob URL siempre es same-origin para el canvas
 *   y no puede contaminarse, garantizando que toDataURL() funcione en cualquier entorno.
 */
async function cargarLogoBase64(logoPath: string): Promise<string | null> {
  try {
    const res = await fetch(logoPath);
    if (!res.ok) return null;

    const blob    = await res.blob();
    const blobUrl = URL.createObjectURL(blob);

    return new Promise<string | null>((resolve) => {
      const img = new Image();

      img.onload = () => {
        URL.revokeObjectURL(blobUrl);
        try {
          // Escalar a 200 px máx para mantener el JPEG liviano (~15-30 KB)
          const maxPx   = 200;
          const scale   = Math.min(1, maxPx / Math.max(img.naturalWidth, img.naturalHeight));
          const canvas  = document.createElement('canvas');
          canvas.width  = Math.round(img.naturalWidth  * scale);
          canvas.height = Math.round(img.naturalHeight * scale);
          const ctx     = canvas.getContext('2d');
          if (!ctx) { resolve(null); return; }
          // Fondo blanco: aplana canal alfa (RGBA → RGB), requisito para JPEG
          ctx.fillStyle = '#ffffff';
          ctx.fillRect(0, 0, canvas.width, canvas.height);
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
          resolve(canvas.toDataURL('image/jpeg', 0.92));
        } catch {
          resolve(null);
        }
      };

      img.onerror = () => {
        URL.revokeObjectURL(blobUrl);
        resolve(null);
      };

      img.src = blobUrl;
    });
  } catch {
    return null;
  }
}

export function NominaModal({ open, onClose, data, onNavigate }: NominaModalProps) {
  const [pdfUrl,    setPdfUrl]    = useState<string | null>(null);
  const [pdfBlob,   setPdfBlob]   = useState<Blob | null>(null);
  const [generando, setGenerando] = useState(false);
  const iframeRef  = useRef<HTMLIFrameElement>(null);
  const prevUrlRef = useRef<string | null>(null);

  useEffect(() => {
    if (!open) return;

    let cancelled = false;
    setGenerando(true);

    const timer = setTimeout(async () => {
      try {
        // PNG primero; si falla (404 o error), intenta JPG
        const logoBase64 =
          await cargarLogoBase64('/uploads/config/logo.png') ??
          await cargarLogoBase64('/uploads/config/logo.jpg');

        if (cancelled) return;

        const doc  = generarNominaPDF({ ...data, logoBase64 });
        const blob = doc.output('blob');
        const url  = URL.createObjectURL(blob);

        if (prevUrlRef.current) URL.revokeObjectURL(prevUrlRef.current);
        prevUrlRef.current = url;

        setPdfBlob(blob);
        setPdfUrl(url);
      } finally {
        if (!cancelled) setGenerando(false);
      }
    }, 80);

    return () => {
      cancelled = true;
      clearTimeout(timer);
    };
  }, [open, data]);

  // Limpieza al desmontar
  useEffect(() => {
    return () => {
      if (prevUrlRef.current) URL.revokeObjectURL(prevUrlRef.current);
    };
  }, []);

  const handleImprimir = () => {
    if (!pdfUrl) return;
    const win = window.open(pdfUrl, '_blank');
    if (win) {
      win.addEventListener('load', () => win.print(), { once: true });
    }
  };

  const handleDescargar = () => {
    if (!pdfBlob) return;
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(pdfBlob);
    a.download = `NOMINA_N_${data.numDocumento ?? data.idDocumento}.pdf`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  };

  const handleCerrar = () => {
    onClose();
    if (onNavigate) onNavigate();
  };

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6">
      {/* Overlay */}
      <div
        className="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onClick={handleCerrar}
        aria-hidden="true"
      />

      {/* Panel */}
      <div
        className="relative w-full max-w-3xl bg-card border border-border rounded-2xl shadow-2xl flex flex-col"
        style={{ maxHeight: '92vh' }}
        role="dialog"
        aria-modal="true"
        aria-label="Vista previa de nómina"
      >
        {/* Cabecera del modal */}
        <div className="flex items-center justify-between px-5 py-4 border-b shrink-0">
          <div className="flex items-center gap-3">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10">
              <FileText className="h-4 w-4 text-primary" aria-hidden="true" />
            </div>
            <div>
              <p className="text-sm font-bold text-foreground">
                NÓMINA N° {data.numDocumento ?? data.idDocumento}
              </p>
              <p className="text-xs text-muted-foreground">Documento físico / papel</p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <Button
              type="button" variant="outline" size="sm"
              onClick={handleImprimir}
              disabled={!pdfUrl || generando}
              className="gap-2 hidden sm:flex"
            >
              <Printer className="h-3.5 w-3.5" aria-hidden="true" />
              Imprimir
            </Button>
            <Button
              type="button" variant="outline" size="sm"
              onClick={handleDescargar}
              disabled={!pdfBlob || generando}
              className="gap-2 hidden sm:flex"
            >
              <Download className="h-3.5 w-3.5" aria-hidden="true" />
              Descargar
            </Button>
            <Button
              type="button" variant="ghost" size="icon"
              onClick={handleCerrar}
              aria-label="Cerrar vista previa"
            >
              <X className="h-4 w-4" />
            </Button>
          </div>
        </div>

        {/* Vista previa PDF */}
        <div className="flex-1 overflow-hidden rounded-b-2xl min-h-0" style={{ minHeight: '55vh' }}>
          {generando ? (
            <div className="flex flex-col items-center justify-center h-full py-16 gap-3">
              <Loader2 className="h-8 w-8 animate-spin text-primary" aria-hidden="true" />
              <p className="text-sm text-muted-foreground">Generando nómina…</p>
            </div>
          ) : pdfUrl ? (
            <iframe
              ref={iframeRef}
              src={pdfUrl}
              title={`Nómina N° ${data.numDocumento ?? data.idDocumento}`}
              className="w-full h-full rounded-b-2xl"
              style={{ minHeight: '55vh', border: 'none' }}
            />
          ) : (
            <div className="flex items-center justify-center h-full py-16">
              <p className="text-sm text-muted-foreground">Error al generar la nómina</p>
            </div>
          )}
        </div>

        {/* Barra acciones mobile */}
        <div className="flex sm:hidden gap-2 px-5 py-3 border-t shrink-0">
          <Button
            type="button" variant="outline" size="sm" className="flex-1 gap-2"
            onClick={handleImprimir} disabled={!pdfUrl || generando}
          >
            <Printer className="h-3.5 w-3.5" aria-hidden="true" />Imprimir
          </Button>
          <Button
            type="button" variant="outline" size="sm" className="flex-1 gap-2"
            onClick={handleDescargar} disabled={!pdfBlob || generando}
          >
            <Download className="h-3.5 w-3.5" aria-hidden="true" />Descargar
          </Button>
          <Button
            type="button" variant="default" size="sm" className="flex-1"
            onClick={handleCerrar}
          >
            Ir al documento
          </Button>
        </div>

        {/* Acción principal desktop */}
        <div className="hidden sm:flex justify-end px-5 py-3 border-t shrink-0">
          <Button type="button" onClick={handleCerrar} className="gap-2">
            Ir al documento
          </Button>
        </div>
      </div>
    </div>
  );
}
