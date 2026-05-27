import { useState, useRef } from 'react';
import {
  X, Paperclip, Upload, Loader2, CheckCircle2,
  AlertCircle, File as FileIconGeneric,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { apiClient } from '@/lib/api/client';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';

interface Props {
  open:             boolean;
  onClose:          () => void;
  idDocumento:      number;
  estadoDocumento:  number;
  /** Llamado después de que al menos 1 archivo se sube con éxito. */
  onSuccess:        () => void;
}

// Estados que bloquean la subida de archivos.
// Criterio: documentos terminados/cerrados no deben recibir más adjuntos.
const ESTADOS_BLOQUEADOS: Record<number, string> = {
  4: 'El documento está Terminado. No se pueden adjuntar archivos adicionales.',
};

type EstadoArchivo = 'pendiente' | 'subiendo' | 'ok' | 'error';

interface ItemArchivo {
  file:    File;
  status:  EstadoArchivo;
  error?:  string;
}

function formatSize(bytes: number): string {
  if (bytes < 1024)           return `${bytes} B`;
  if (bytes < 1024 * 1024)    return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function AdjuntarArchivoModal({
  open, onClose, idDocumento, estadoDocumento, onSuccess,
}: Props) {
  const [items,    setItems]    = useState<ItemArchivo[]>([]);
  const [subiendo, setSubiendo] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  const mensajeBloqueado = ESTADOS_BLOQUEADOS[estadoDocumento];
  const todoOk     = items.length > 0 && items.every((a) => a.status === 'ok');
  const hayPendientes = items.some((a) => a.status === 'pendiente');
  const pendientesCount = items.filter((a) => a.status === 'pendiente').length;

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files ?? []);
    if (files.length === 0) return;
    setItems(files.map((f) => ({ file: f, status: 'pendiente' })));
  };

  const handleSubir = async () => {
    if (!hayPendientes || subiendo) return;
    setSubiendo(true);
    let subidos = 0;

    for (let i = 0; i < items.length; i++) {
      if (items[i].status !== 'pendiente') continue;

      setItems((prev) => prev.map((a, idx) =>
        idx === i ? { ...a, status: 'subiendo' } : a,
      ));

      try {
        const fd = new FormData();
        fd.append('archivo',     items[i].file);
        fd.append('idDocumento', String(idDocumento));

        // El interceptor de apiClient elimina Content-Type para FormData,
        // dejando que el navegador ponga 'multipart/form-data; boundary=...'
        // automáticamente. El header explícito aquí es respaldo idéntico
        // al patrón de ArchivosPage.
        await apiClient.post('/archivos/upload', fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        setItems((prev) => prev.map((a, idx) =>
          idx === i ? { ...a, status: 'ok' } : a,
        ));
        subidos++;
      } catch (err: unknown) {
        const msg =
          (err as { response?: { data?: { error?: string } } })
            ?.response?.data?.error ?? 'Error al subir archivo';
        setItems((prev) => prev.map((a, idx) =>
          idx === i ? { ...a, status: 'error', error: msg } : a,
        ));
      }
    }

    setSubiendo(false);
    if (subidos > 0) {
      toast.success(`${subidos} archivo${subidos > 1 ? 's' : ''} adjuntado${subidos > 1 ? 's' : ''} correctamente`);
      onSuccess();
    }
  };

  const handleCerrar = () => {
    if (subiendo) return;
    setItems([]);
    if (inputRef.current) inputRef.current.value = '';
    onClose();
  };

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div
        className="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onClick={handleCerrar}
        aria-hidden="true"
      />
      <div
        className="relative w-full max-w-md bg-card border border-border rounded-2xl shadow-2xl animate-fade-in"
        role="dialog"
        aria-modal="true"
        aria-label="Adjuntar archivo al documento"
      >
        {/* Header */}
        <div className="flex items-center gap-3 px-5 py-4 border-b border-border">
          <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10">
            <Paperclip className="h-4 w-4 text-primary" aria-hidden="true" />
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-semibold text-foreground">Adjuntar archivo</p>
            <p className="text-xs text-muted-foreground">Documento #{idDocumento}</p>
          </div>
          <Button
            variant="ghost" size="icon" className="h-8 w-8 shrink-0"
            onClick={handleCerrar} disabled={subiendo}
            aria-label="Cerrar"
          >
            <X className="h-4 w-4" />
          </Button>
        </div>

        {/* Body */}
        <div className="p-5 space-y-4">
          {mensajeBloqueado ? (
            /* Documento bloqueado */
            <div className="flex items-start gap-3 p-3 rounded-lg bg-destructive/10 border border-destructive/20">
              <AlertCircle className="h-4 w-4 text-destructive shrink-0 mt-0.5" aria-hidden="true" />
              <p className="text-sm text-destructive">{mensajeBloqueado}</p>
            </div>
          ) : (
            <>
              {/* Zona de selección de archivos */}
              <div>
                <input
                  ref={inputRef}
                  id="adjuntar-archivo-input"
                  type="file"
                  multiple
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp,.txt"
                  onChange={handleFileChange}
                  className="sr-only"
                  disabled={subiendo}
                  aria-label="Seleccionar archivos para adjuntar"
                />
                <label
                  htmlFor="adjuntar-archivo-input"
                  className={cn(
                    'flex flex-col items-center justify-center gap-2 p-6 rounded-xl border-2 border-dashed transition-colors',
                    subiendo
                      ? 'border-border opacity-50 cursor-not-allowed'
                      : 'border-primary/30 hover:border-primary/60 hover:bg-primary/5 cursor-pointer',
                  )}
                >
                  <Upload className="h-6 w-6 text-muted-foreground" aria-hidden="true" />
                  <p className="text-sm text-muted-foreground text-center">
                    Haz clic para seleccionar archivos
                    <br />
                    <span className="text-xs">PDF, Word, Excel, imágenes · máx. 20 MB c/u</span>
                  </p>
                </label>
              </div>

              {/* Lista de archivos seleccionados */}
              {items.length > 0 && (
                <div className="space-y-1.5 max-h-52 overflow-y-auto pr-1">
                  {items.map((a, idx) => (
                    <div
                      key={idx}
                      className="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-muted/40"
                    >
                      {a.status === 'subiendo'  && <Loader2      className="h-3.5 w-3.5 shrink-0 animate-spin text-primary" />}
                      {a.status === 'ok'        && <CheckCircle2 className="h-3.5 w-3.5 shrink-0 text-emerald-500" />}
                      {a.status === 'error'     && <AlertCircle  className="h-3.5 w-3.5 shrink-0 text-destructive" />}
                      {a.status === 'pendiente' && <FileIconGeneric className="h-3.5 w-3.5 shrink-0 text-muted-foreground" />}

                      <div className="flex-1 min-w-0">
                        <p className="text-xs font-medium truncate">{a.file.name}</p>
                        {a.status === 'error' ? (
                          <p className="text-[11px] text-destructive">{a.error}</p>
                        ) : (
                          <p className="text-[11px] text-muted-foreground">{formatSize(a.file.size)}</p>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </>
          )}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-end gap-2 px-5 py-4 border-t border-border">
          <Button variant="ghost" onClick={handleCerrar} disabled={subiendo}>
            {todoOk ? 'Cerrar' : 'Cancelar'}
          </Button>
          {!mensajeBloqueado && !todoOk && (
            <Button
              onClick={handleSubir}
              disabled={!hayPendientes || subiendo}
              className="gap-2"
            >
              {subiendo ? (
                <><Loader2 className="h-3.5 w-3.5 animate-spin" aria-hidden="true" />Subiendo…</>
              ) : (
                <><Upload className="h-3.5 w-3.5" aria-hidden="true" />
                  Adjuntar{pendientesCount > 0 ? ` (${pendientesCount})` : ''}
                </>
              )}
            </Button>
          )}
        </div>
      </div>
    </div>
  );
}
