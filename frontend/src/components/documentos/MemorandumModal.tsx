import { useState, useEffect, useRef } from 'react';
import {
  X, Eye, Loader2, AlertCircle,
  ArrowLeft, Send, FileText, ShieldCheck, AlertTriangle,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { apiClient } from '@/lib/api/client';
import { toast } from 'sonner';
import { generarMemorandumPDF, cargarImagenDataUrl, type MemorandumData } from '@/lib/utils/memorandum.generator';
import { cn, uploadUrl } from '@/lib/utils';

// ── Tipos ────────────────────────────────────────────────────

export interface FirmanteActivo {
  disponible:      boolean;
  mensaje?:        string;
  tipo?:           string;
  idFirmante?:     number;
  nombre?:         string;
  cargo?:          string;
  rut?:            string | null;
  firmaTimbreUrl?: string | null;
  dependencia?:    string | null;
  idDependencia?:  number;
  vigente?:        boolean;
}

/** Datos del formulario de nuevo documento que llegan al modal */
export interface MemoDocumentoPayload {
  materia:            string;
  idTipoDocumento:    number;
  fechaDocumento?:    string;
  observaciones?:     string;
  idTipoDistribucion: number;
  idTipoCompromiso:   number;
  idEstadoCompromiso: number;
  diasCompromiso:     number;
  tipoDestinatario:   'D' | 'E';
  destinos?:          number[];
  idDestino?:         number;
  tipoSoporte?:       'D' | 'F';
  reservado?:         boolean;
  despacharAhora?:    boolean;
}

export interface MemorandumModalProps {
  open:               boolean;
  onClose:            () => void;
  docPayload:         MemoDocumentoPayload;
  referencia:         string;
  cuerpo:             string;
  destinosNombres:    string[];
  origenNombre:       string;
  firmante:           FirmanteActivo;
  archivos:           File[];
  onNavigate:         (idDocumento: number) => void;
}

// ── Componente ────────────────────────────────────────────────

type Fase = 'cargando' | 'preview' | 'firmando' | 'error';

export function MemorandumModal({
  open, onClose,
  docPayload, referencia, cuerpo,
  destinosNombres, origenNombre, firmante,
  archivos, onNavigate,
}: MemorandumModalProps) {
  const [fase, setFase]       = useState<Fase>('cargando');
  const [pdfUrl, setPdfUrl]   = useState<string | null>(null);
  const [errorMsg, setErrorMsg] = useState('');
  const objUrlRef = useRef<string | null>(null);

  function liberarUrl() {
    if (objUrlRef.current) {
      URL.revokeObjectURL(objUrlRef.current);
      objUrlRef.current = null;
    }
    setPdfUrl(null);
  }

  // Genera el PDF de previsualización (borrador, sin firma — FirmaGov la agrega)
  useEffect(() => {
    if (!open) { liberarUrl(); setFase('cargando'); return; }

    setFase('cargando');

    (async () => {
      try {
        const logoB64 = await apiClient.get<{ ok: boolean; data: { logoUrl: string | null } }>('/configuracion')
          .then((r) => {
            const url = uploadUrl(r.data.data.logoUrl);
            return url ? cargarImagenDataUrl(url) : null;
          })
          .catch(() => null);

        const memoData: MemorandumData = {
          correlativo:       null,
          esBorrador:        true,
          fechaDocumento:    docPayload.fechaDocumento ?? null,
          origen:            origenNombre,
          destinos:          destinosNombres,
          materia:           docPayload.materia,
          referencia:        referencia || null,
          cuerpo,
          nombreFirmante:    firmante.nombre ?? '',
          cargoFirmante:     firmante.cargo  ?? '',
          // Sin imagen de firma — FirmaGov la aplica digitalmente
          firmaTimbreBase64: null,
          firmaTimbreNatW:   0,
          firmaTimbreNatH:   0,
          logoBase64:        logoB64,
          servicioFirmante:  firmante.dependencia ?? origenNombre,
        };

        const doc  = generarMemorandumPDF(memoData);
        const blob = doc.output('blob');
        const url  = URL.createObjectURL(blob);
        objUrlRef.current = url;
        setPdfUrl(url);
        setFase('preview');
      } catch (err) {
        console.error('[MemorandumModal] Error al generar PDF:', err);
        setErrorMsg('Error al generar la previsualización del PDF.');
        setFase('error');
      }
    })();

    return () => { liberarUrl(); };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  // ── Enviar a FirmaGov ─────────────────────────────────────────
  async function handleEnviarFirmaGov() {
    if (!firmante.rut) {
      toast.error('El firmante no tiene RUT configurado. Complétalo en Administración → Jefaturas antes de continuar.');
      return;
    }

    setFase('firmando');
    try {
      // 1. Crear el documento en estado 1 (Generado) — sin despachar todavía
      const docRes = await apiClient.post<{ ok: boolean; data: { idDocumento: number } }>(
        '/documentos',
        { ...docPayload, despacharAhora: false }
      );
      const idDocumento = docRes.data.data.idDocumento;

      // 2. Asignar correlativo definitivo
      const corrRes = await apiClient.post<{ ok: boolean; data: { correlativo: string } }>(
        '/memorandum/confirmar',
        {
          idDocumento,
          materia:           docPayload.materia,
          referencia:        referencia || null,
          cuerpo,
          nombreFirmante:    firmante.nombre,
          cargoFirmante:     firmante.cargo,
          tipoFirmante:      firmante.tipo,
          firmaTimbreRuta:   firmante.firmaTimbreUrl,
          idDependencia:     firmante.idDependencia,
        }
      );
      const { correlativo } = corrRes.data.data;

      // 3. Generar PDF definitivo SIN imagen de firma (FirmaGov la agrega digitalmente)
      const logoB64 = await apiClient.get<{ ok: boolean; data: { logoUrl: string | null } }>('/configuracion')
        .then((r) => { const url = uploadUrl(r.data.data.logoUrl); return url ? cargarImagenDataUrl(url) : null; })
        .catch(() => null);

      const memoDefin: MemorandumData = {
        correlativo,
        esBorrador:        false,
        fechaDocumento:    docPayload.fechaDocumento ?? null,
        origen:            origenNombre,
        destinos:          destinosNombres,
        materia:           docPayload.materia,
        referencia:        referencia || null,
        cuerpo,
        nombreFirmante:    firmante.nombre ?? '',
        cargoFirmante:     firmante.cargo  ?? '',
        firmaTimbreBase64: null,
        firmaTimbreNatW:   0,
        firmaTimbreNatH:   0,
        logoBase64:        logoB64,
        servicioFirmante:  firmante.dependencia ?? origenNombre,
      };

      const docPdf  = generarMemorandumPDF(memoDefin);
      const pdfBlob = docPdf.output('blob');
      const pdfFile = new File([pdfBlob], `${correlativo}.pdf`, { type: 'application/pdf' });

      // 4. Subir PDF original (sin firma) como archivo del documento
      const formPdf = new FormData();
      formPdf.append('archivo',     pdfFile);
      formPdf.append('idDocumento', String(idDocumento));
      const uploadRes = await apiClient.post<{ ok: boolean; data: { idArchivo: number } }>(
        '/archivos/upload',
        formPdf,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      const idArchivoOriginal = uploadRes.data.data?.idArchivo;
      if (!idArchivoOriginal) throw new Error('No se pudo subir el PDF del memorándum');

      // 5. Vincular con memo_generado (best-effort)
      await apiClient.patch('/memorandum/vincular-archivo', {
        correlativo, idArchivo: idArchivoOriginal,
      }).catch(() => {/* no crítico */});

      // 6. Subir archivos manuales adjuntos
      const erroresArchivos: string[] = [];
      for (const file of archivos) {
        try {
          const formFile = new FormData();
          formFile.append('archivo',     file);
          formFile.append('idDocumento', String(idDocumento));
          await apiClient.post('/archivos/upload', formFile, { headers: { 'Content-Type': 'multipart/form-data' } });
        } catch { erroresArchivos.push(file.name); }
      }

      // 7. Enviar a FirmaGov — si falla, el doc queda en estado 1 (no despachado)
      await apiClient.post('/firma-gob/solicitar', {
        idDocumento,
        correlativoMemo:   correlativo,
        idArchivoOriginal,
        tipoFirmante:      firmante.tipo      ?? '',
        nombreFirmante:    firmante.nombre    ?? '',
        run:               firmante.rut,
      });

      // 8. Éxito
      if (erroresArchivos.length > 0) {
        toast.warning(`Memorándum ${correlativo} firmado, pero ${erroresArchivos.length} adjunto(s) no se pudo(n) subir.`);
      } else {
        toast.success(`Memorándum ${correlativo} firmado y despachado correctamente`);
      }

      liberarUrl();
      onNavigate(idDocumento);
    } catch (err: unknown) {
      const msg = (err as { response?: { data?: { error?: string } } })?.response?.data?.error
        ?? 'Error al procesar el memorándum. Intenta nuevamente.';
      setErrorMsg(msg);
      setFase('error');
      toast.error(msg);
    }
  }

  if (!open) return null;

  const sinRut = fase === 'preview' && !firmante.rut;

  return (
    <div className="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4">
      <div className="modal-panel bg-background w-full max-w-4xl max-h-[92vh] flex flex-col">

        {/* Header */}
        <div className="flex items-center justify-between px-5 py-4 border-b shrink-0">
          <div className="flex items-center gap-3">
            <span className="icon-3d icon-3d-sky flex h-9 w-9 shrink-0 items-center justify-center">
              <FileText className="h-5 w-5 text-white" />
            </span>
            <div>
              <h2 className="font-semibold text-sm">Vista previa — Memorándum</h2>
              <p className="text-xs text-muted-foreground">
                {fase === 'cargando'  && 'Generando previsualización...'}
                {fase === 'preview'   && 'Revisa el documento antes de enviar a FirmaGov'}
                {fase === 'firmando'  && 'Enviando a FirmaGov…'}
                {fase === 'error'     && 'Se produjo un error'}
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            disabled={fase === 'firmando'}
            className="text-muted-foreground hover:text-foreground transition-colors disabled:opacity-40"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        {/* Body */}
        <div className="flex-1 overflow-hidden relative">

          {fase === 'cargando' && (
            <div className="flex flex-col items-center justify-center h-full gap-3 text-muted-foreground py-16">
              <Loader2 className="h-10 w-10 animate-spin" />
              <p className="text-sm">Generando previsualización del memorándum…</p>
            </div>
          )}

          {fase === 'error' && (
            <div className="flex flex-col items-center justify-center h-full gap-4 py-16 px-6">
              <AlertCircle className="h-10 w-10 text-destructive" />
              <p className="text-sm font-medium text-destructive text-center">{errorMsg}</p>
              <p className="text-xs text-muted-foreground text-center max-w-md">
                El documento puede haberse creado pero <strong>no fue despachado</strong> porque FirmaGov no respondió correctamente.
                Puedes intentarlo nuevamente o contactar al administrador.
              </p>
            </div>
          )}

          {fase === 'firmando' && (
            <div className="flex flex-col items-center justify-center h-full gap-3 text-muted-foreground py-16">
              <Loader2 className="h-10 w-10 animate-spin text-blue-500" />
              <p className="text-sm font-medium text-foreground">Enviando a FirmaGov…</p>
              <p className="text-xs">Esperando firma digital. No cierres esta ventana.</p>
            </div>
          )}

          {fase === 'preview' && pdfUrl && (
            <div className="h-full flex flex-col">
              <div className="flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800/40 shrink-0">
                <Eye className="h-3.5 w-3.5 text-amber-600 shrink-0" />
                <p className="text-xs text-amber-700 dark:text-amber-400">
                  <strong>Vista previa (borrador)</strong> — El correlativo real y la firma digital se asignarán al confirmar con FirmaGov.
                </p>
              </div>
              {sinRut && (
                <div className="flex items-center gap-2 px-4 py-2 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-200 dark:border-orange-800/40 shrink-0">
                  <AlertTriangle className="h-3.5 w-3.5 text-orange-600 shrink-0" />
                  <p className="text-xs text-orange-700 dark:text-orange-400">
                    <strong>RUT no configurado</strong> para <em>{firmante.nombre}</em> —
                    sin RUT no se puede enviar a FirmaGov. Configúralo en Administración → Jefaturas.
                  </p>
                </div>
              )}
              <iframe
                src={pdfUrl}
                title="Vista previa memorándum"
                className="flex-1 w-full border-0"
                style={{ minHeight: '400px' }}
              />
            </div>
          )}
        </div>

        {/* Footer acciones */}
        <div className={cn(
          'flex items-center px-5 py-4 border-t shrink-0 gap-3',
          fase === 'preview' ? 'justify-between' : 'justify-end'
        )}>
          {/* Firmante info (solo en preview) */}
          {fase === 'preview' && (
            <div className="text-xs text-muted-foreground flex items-center gap-1.5 min-w-0">
              <ShieldCheck className={cn('h-3.5 w-3.5 shrink-0', firmante.rut ? 'text-emerald-500' : 'text-orange-400')} />
              <span className="truncate">
                Firmante: <strong>{firmante.nombre}</strong> — {firmante.cargo}
                {firmante.tipo === 'SUBROGANTE'   && ' (Subrogante)'}
                {firmante.tipo === 'SUBROGANTE_2' && ' (2° Subrogante)'}
                {firmante.rut
                  ? <span className="text-emerald-600 ml-1">· {firmante.rut}</span>
                  : <span className="text-orange-500 ml-1">· sin RUT</span>
                }
              </span>
            </div>
          )}

          <div className="flex items-center gap-2 shrink-0">
            {(fase === 'preview' || fase === 'error') && (
              <Button variant="outline" size="sm" className="gap-2" onClick={onClose}>
                <ArrowLeft className="h-3.5 w-3.5" />
                Volver a editar
              </Button>
            )}

            {fase === 'preview' && (
              <Button
                size="sm"
                className="gap-2 bg-blue-600 hover:bg-blue-700"
                onClick={handleEnviarFirmaGov}
                disabled={sinRut}
                title={sinRut ? 'Configura el RUT del firmante primero' : undefined}
              >
                <Send className="h-3.5 w-3.5" />
                Enviar a FirmaGov
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
