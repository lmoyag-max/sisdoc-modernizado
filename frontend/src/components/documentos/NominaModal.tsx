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

/** Convierte un Blob en data URL usando FileReader. */
function blobADataUrl(blob: Blob): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader    = new FileReader();
    reader.onload   = () => resolve(reader.result as string);
    reader.onerror  = () => reject(new Error('FileReader error'));
    reader.readAsDataURL(blob);
  });
}

/**
 * Carga el logo desde `url` y devuelve un data URL JPEG listo para jsPDF.
 *
 * Estrategia por tipo de archivo:
 *   - JPEG (.jpg/.jpeg): FileReader directo sobre el blob — sin canvas,
 *     sin conversión, sin riesgo de CORS taint. Más rápido y fiable.
 *   - PNG y otros: canvas con fondo blanco para eliminar canal alfa
 *     (RGBA → RGB), exportar como JPEG.
 *
 * Se usa fetch() + blob URL en todos los casos para evitar el "canvas taint"
 * que ocurre cuando el browser considera que la respuesta del servidor
 * no lleva los headers CORS exactos requeridos para canvas.toDataURL().
 */
async function cargarLogoDataUrl(url: string): Promise<string | null> {
  try {
    const res = await fetch(url);
    if (!res.ok) return null;
    const blob = await res.blob();

    const esJpeg = blob.type.includes('jpeg') || /\.(jpg|jpeg)$/i.test(url);
    if (esJpeg) {
      // Camino simple: FileReader → data:image/jpeg;base64,...
      return await blobADataUrl(blob);
    }

    // Camino canvas: eliminar alfa del PNG antes de pasar a jsPDF
    const blobUrl = URL.createObjectURL(blob);
    return new Promise<string | null>((resolve) => {
      const img = new Image();

      img.onload = () => {
        if (!img.naturalWidth || !img.naturalHeight) {
          URL.revokeObjectURL(blobUrl);
          resolve(null);
          return;
        }
        try {
          const maxPx   = 300;
          const scale   = Math.min(1, maxPx / Math.max(img.naturalWidth, img.naturalHeight));
          const canvas  = document.createElement('canvas');
          canvas.width  = Math.round(img.naturalWidth  * scale) || 1;
          canvas.height = Math.round(img.naturalHeight * scale) || 1;
          const ctx     = canvas.getContext('2d');
          if (!ctx) { URL.revokeObjectURL(blobUrl); resolve(null); return; }
          ctx.fillStyle = '#ffffff';
          ctx.fillRect(0, 0, canvas.width, canvas.height);
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
          const jpeg = canvas.toDataURL('image/jpeg', 0.92);
          URL.revokeObjectURL(blobUrl);  // revocar DESPUÉS de todas las ops
          resolve(jpeg);
        } catch {
          URL.revokeObjectURL(blobUrl);
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

/**
 * Carga cualquier imagen (JPEG o PNG) y la devuelve siempre como JPEG RGB
 * limpio, sin metadatos Adobe/XMP, usando canvas como único camino.
 *
 * Problema que resuelve: jsPDF falla silenciosamente con JPEGs que tienen
 * metadatos XMP pesados embebidos por Photoshop/Illustrator. Canvas garantiza
 * un JPEG estándar RGB sin markers propietarios.
 */
async function cargarImagenComoJpegRgb(url: string): Promise<string | null> {
  try {
    const res = await fetch(url);
    if (!res.ok) return null;
    const blob    = await res.blob();
    const blobUrl = URL.createObjectURL(blob);

    return new Promise<string | null>((resolve) => {
      const img = new Image();

      img.onload = () => {
        if (!img.naturalWidth || !img.naturalHeight) {
          URL.revokeObjectURL(blobUrl);
          resolve(null);
          return;
        }
        try {
          const maxPx   = 300;
          const scale   = Math.min(1, maxPx / Math.max(img.naturalWidth, img.naturalHeight));
          const canvas  = document.createElement('canvas');
          canvas.width  = Math.round(img.naturalWidth  * scale) || 1;
          canvas.height = Math.round(img.naturalHeight * scale) || 1;
          const ctx     = canvas.getContext('2d');
          if (!ctx) { URL.revokeObjectURL(blobUrl); resolve(null); return; }
          ctx.fillStyle = '#ffffff';
          ctx.fillRect(0, 0, canvas.width, canvas.height);
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
          const jpeg = canvas.toDataURL('image/jpeg', 0.92);
          URL.revokeObjectURL(blobUrl);
          resolve(jpeg);
        } catch (err) {
          console.warn('[NominaModal] canvas→JPEG falló para', url, err);
          URL.revokeObjectURL(blobUrl);
          resolve(null);
        }
      };

      img.onerror = (err) => {
        console.warn('[NominaModal] img.onerror para', url, err);
        URL.revokeObjectURL(blobUrl);
        resolve(null);
      };

      img.src = blobUrl;
    });
  } catch (err) {
    console.warn('[NominaModal] fetch falló para', url, err);
    return null;
  }
}

/**
 * Resuelve el logo institucional con cadena de fallback:
 *   1. /uploads/config/logo.jpg  (siempre via canvas → JPEG RGB limpio)
 *   2. /uploads/config/logo.png  (idem)
 *   3. /logo-huap.png            (logo fijo en frontend/public)
 *   4. null                      (PDF se genera sin logo)
 */
async function resolveNominaLogo(): Promise<string | null> {
  const candidatos = [
    '/uploads/config/logo.jpg',
    '/uploads/config/logo.png',
    '/logo-huap.png',
  ];
  for (const url of candidatos) {
    const result = await cargarImagenComoJpegRgb(url);
    if (result) return result;
  }
  console.warn('[NominaModal] No se encontró logo institucional — PDF sin logo');
  return null;
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
        const logoBase64 = await resolveNominaLogo();

        if (cancelled) return;

        const doc  = generarNominaPDF({
          ...data,
          logoBase64,
          watermarkBase64: logoBase64,  // misma imagen, mayor tamaño en el PDF
        });
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
