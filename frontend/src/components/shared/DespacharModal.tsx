import { useEffect, useState } from 'react';
import { X, Send, Loader2, Building2, Globe } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { apiClient } from '@/lib/api/client';
import { cn } from '@/lib/utils';

interface Dependencia { id: number; descripcion: string }

interface Props {
  open:        boolean;
  onClose:     () => void;
  idDocumento: number;
  materia:     string;
  onSuccess:   () => void;
}

export function DespacharModal({ open, onClose, idDocumento, materia, onSuccess }: Props) {
  const [tipo, setTipo]       = useState<'D' | 'E'>('D');
  const [idDest, setIdDest]   = useState<string>('');
  const [obs, setObs]         = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError]     = useState<string | null>(null);

  const [depInternas,  setDepInternas]  = useState<Dependencia[]>([]);
  const [depExternas,  setDepExternas]  = useState<Dependencia[]>([]);
  const [loadingDeps,  setLoadingDeps]  = useState(false);

  // Cargar catálogos una vez que el modal se abre
  useEffect(() => {
    if (!open) return;
    setLoadingDeps(true);
    Promise.all([
      apiClient.get<{ ok: boolean; data: Dependencia[] }>('/catalogos/dependencias'),
      apiClient.get<{ ok: boolean; data: Dependencia[] }>('/catalogos/dependencias-externas'),
    ])
      .then(([intRes, extRes]) => {
        setDepInternas(intRes.data.data ?? []);
        setDepExternas(extRes.data.data ?? []);
      })
      .catch(() => setError('No se pudieron cargar los destinos disponibles.'))
      .finally(() => setLoadingDeps(false));
  }, [open]);

  // Reset al abrir
  useEffect(() => {
    if (open) {
      setTipo('D');
      setIdDest('');
      setObs('');
      setError(null);
    }
  }, [open]);

  // Cerrar con Escape
  useEffect(() => {
    if (!open) return;
    const h = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', h);
    return () => window.removeEventListener('keydown', h);
  }, [open, onClose]);

  const lista = tipo === 'D' ? depInternas : depExternas;

  const handleSubmit = async () => {
    if (!idDest) { setError('Selecciona un destino.'); return; }
    setError(null);
    setLoading(true);
    try {
      await apiClient.post(`/documentos/${idDocumento}/despachar`, {
        idDestino:          Number(idDest),
        tipoDestinatario:   tipo,
        idTipoDistribucion: 5,
        idTipoCompromiso:   1,
        idEstadoCompromiso: 2,
        diasCompromiso:     0,
        observaciones:      obs.trim() || undefined,
      });
      onSuccess();
      onClose();
    } catch (e: unknown) {
      const msg = (e as { response?: { data?: { error?: string } } })?.response?.data?.error;
      setError(msg ?? 'Error al despachar el documento.');
    } finally {
      setLoading(false);
    }
  };

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      {/* Overlay */}
      <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={onClose} />

      {/* Panel */}
      <div className="relative w-full max-w-lg bg-card border border-border rounded-2xl shadow-2xl flex flex-col animate-fade-in">

        {/* Header */}
        <div className="flex items-center gap-3 px-5 py-4 border-b border-border">
          <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
            <Send className="h-4 w-4 text-amber-600 dark:text-amber-400" />
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-semibold text-foreground">Despachar documento</p>
            <p className="text-xs text-muted-foreground truncate">{materia}</p>
          </div>
          <Button variant="ghost" size="icon" className="h-8 w-8 shrink-0" onClick={onClose}>
            <X className="h-4 w-4" />
          </Button>
        </div>

        {/* Body */}
        <div className="p-5 space-y-4">

          {/* Tipo de destino */}
          <div className="space-y-1.5">
            <Label className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
              Tipo de destino
            </Label>
            <div className="flex gap-2">
              <button
                type="button"
                onClick={() => { setTipo('D'); setIdDest(''); }}
                className={cn(
                  'flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-lg border text-sm font-medium transition-colors',
                  tipo === 'D'
                    ? 'bg-primary text-primary-foreground border-primary'
                    : 'bg-background text-muted-foreground border-border hover:bg-muted'
                )}
              >
                <Building2 className="h-3.5 w-3.5" />
                Dependencia interna
              </button>
              <button
                type="button"
                onClick={() => { setTipo('E'); setIdDest(''); }}
                className={cn(
                  'flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-lg border text-sm font-medium transition-colors',
                  tipo === 'E'
                    ? 'bg-primary text-primary-foreground border-primary'
                    : 'bg-background text-muted-foreground border-border hover:bg-muted'
                )}
              >
                <Globe className="h-3.5 w-3.5" />
                Dependencia externa
              </button>
            </div>
          </div>

          {/* Selector de destino */}
          <div className="space-y-1.5">
            <Label htmlFor="destino-select" className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
              Destino *
            </Label>
            {loadingDeps ? (
              <div className="h-10 rounded-lg bg-muted animate-pulse" />
            ) : (
              <select
                id="destino-select"
                value={idDest}
                onChange={(e) => { setIdDest(e.target.value); setError(null); }}
                className={cn(
                  'w-full h-10 rounded-lg border px-3 text-sm bg-background text-foreground',
                  'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-0',
                  !idDest ? 'text-muted-foreground' : 'text-foreground',
                  error && !idDest ? 'border-destructive' : 'border-input'
                )}
              >
                <option value="">
                  {lista.length === 0
                    ? '— Sin opciones disponibles —'
                    : `— Selecciona ${tipo === 'D' ? 'dependencia' : 'entidad externa'} —`}
                </option>
                {lista.map((d) => (
                  <option key={d.id} value={d.id}>{d.descripcion}</option>
                ))}
              </select>
            )}
          </div>

          {/* Observaciones */}
          <div className="space-y-1.5">
            <Label htmlFor="obs-input" className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
              Observaciones <span className="normal-case font-normal">(opcional)</span>
            </Label>
            <textarea
              id="obs-input"
              value={obs}
              onChange={(e) => setObs(e.target.value)}
              placeholder="Instrucciones, notas o razón del despacho…"
              maxLength={500}
              rows={3}
              className={cn(
                'w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground',
                'placeholder:text-muted-foreground resize-none',
                'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-0'
              )}
            />
            <p className="text-right text-[11px] text-muted-foreground/60">{obs.length}/500</p>
          </div>

          {/* Error */}
          {error && (
            <p className="text-sm text-destructive bg-destructive/10 border border-destructive/20 rounded-lg px-3 py-2">
              {error}
            </p>
          )}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-end gap-2 px-5 py-4 border-t border-border">
          <Button variant="ghost" onClick={onClose} disabled={loading}>
            Cancelar
          </Button>
          <Button
            onClick={handleSubmit}
            disabled={loading || !idDest}
            className="gap-2"
          >
            {loading
              ? <><Loader2 className="h-3.5 w-3.5 animate-spin" />Despachando…</>
              : <><Send className="h-3.5 w-3.5" />Despachar</>
            }
          </Button>
        </div>
      </div>
    </div>
  );
}
