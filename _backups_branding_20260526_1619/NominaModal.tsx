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

export function NominaModal({ open, onClose, data, onNavigate }: NominaModalProps) {
  const [pdfUrl,    setPdfUrl]    = useState<string | null>(null);
  const [pdfBlob,   setPdfBlob]   = useState<Blob | null>(null);
  const [generando, setGenerando] = useState(false);
  const iframeRef = useRef<HTMLIFrameElement>(null);
  const prevUrlRef = useRef<string | null>(null);

  useEffect(() => {
    if (!open) return;

    setGenerando(true);
    // defer para no bloquear el render del modal
    const timer = setTimeout(() => {
      try {
        const doc  = generarNominaPDF(data);
        const blob = doc.output('blob');
        const url  = URL.createObjectURL(blob);

        // Revocar URL anterior antes de crear una nueva
        if (prevUrlRef.current) URL.revokeObjectURL(prevUrlRef.current);
        prevUrlRef.current = url;

        setPdfBlob(blob);
        setPdfUrl(url);
      } finally {
        setGenerando(false);
      }
    }, 80);

    return () => clearTimeout(timer);
  }, [open, data]);

  // Cleanup al desmontar
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
    const a   = document.createElement('a');
    a.href    = URL.createObjectURL(pdfBlob);
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
        {/* Cabecera */}
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

        {/* Vista PDF */}
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

        {/* Barra de acciones mobile */}
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
