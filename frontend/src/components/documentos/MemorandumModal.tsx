import { useState, useEffect, useRef } from 'react';
import {
  X, Eye, CheckCircle2, Loader2, AlertCircle,
  ArrowLeft, Send, FileText,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { apiClient } from '@/lib/api/client';
import { toast } from 'sonner';
import { generarMemorandumPDF, cargarImagenDataUrl, type MemorandumData } from '@/lib/utils/memorandum.generator';
import { cn, uploadUrl } from '@/lib/utils';

// ── Carga firma+timbre preservando formato original ──────────
// A diferencia de cargarImagenDataUrl, NO convierte a JPEG ni usa canvas.
// Lee el archivo tal cual (PNG conserva canal alfa → el timbre permanece circular).
// También captura las dimensiones naturales para escala proporcional en el PDF.
async function cargarFirmaTimbreOriginal(url: string): Promise<{
  dataUrl: string | null;
  natW:    number;
  natH:    number;
}> {
  try {
    const res = await fetch(url, { credentials: 'include' });
    if (!res.ok) {
      console.warn(`[memoModal] firma+timbre HTTP ${res.status}: ${url}`);
      return { dataUrl: null, natW: 0, natH: 0 };
    }
    const blob = await res.blob();
    if (!blob.size) { return { dataUrl: null, natW: 0, natH: 0 }; }

    // Leer como data URL sin modificar el formato (preserva PNG transparente)
    const dataUrl = await new Promise<string | null>((resolve) => {
      const reader   = new FileReader();
      reader.onload  = () => resolve(reader.result as string);
      reader.onerror = () => { console.warn('[memoModal] FileReader firma+timbre falló'); resolve(null); };
      reader.readAsDataURL(blob);
    });
    if (!dataUrl) return { dataUrl: null, natW: 0, natH: 0 };

    // Capturar dimensiones naturales vía blob URL
    const blobUrl = URL.createObjectURL(blob);
    const dims = await new Promise<{ w: number; h: number }>((resolve) => {
      const img   = new Image();
      img.onload  = () => { URL.revokeObjectURL(blobUrl); resolve({ w: img.naturalWidth, h: img.naturalHeight }); };
      img.onerror = () => { URL.revokeObjectURL(blobUrl); resolve({ w: 0, h: 0 }); };
      img.src     = blobUrl;
    });

    console.log(`[memoModal] firma+timbre cargada: ${dims.w}×${dims.h}px — ${Math.round(blob.size / 1024)} KB`);
    return { dataUrl, natW: dims.w, natH: dims.h };
  } catch (err) {
    console.warn('[memoModal] Error cargando firma+timbre:', err);
    return { dataUrl: null, natW: 0, natH: 0 };
  }
}

// ── Tipos ────────────────────────────────────────────────────

export interface FirmanteActivo {
  disponible:      boolean;
  mensaje?:        string;
  tipo?:           'TITULAR' | 'SUBROGANTE';
  idFirmante?:     number;
  nombre?:         string;
  cargo?:          string;
  firmaTimbreUrl?: string | null;   // imagen combinada firma+timbre
  dependencia?:    string | null;
  idDependencia?:  number;
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
  /** Ids de destinos internos */
  destinos?:          number[];
  /** Id de destino externo */
  idDestino?:         number;
  /** Campos de Oficina de Partes */
  tipoSoporte?:       'D' | 'F';
  reservado?:         boolean;
}

export interface MemorandumModalProps {
  open:               boolean;
  onClose:            () => void;
  /** Payload para POST /documentos */
  docPayload:         MemoDocumentoPayload;
  /** Campos específicos del memorándum */
  referencia:         string;
  cuerpo:             string;
  /** Nombres de destinos para el PDF */
  destinosNombres:    string[];
  /** Nombre del origen (servicio del usuario) */
  origenNombre:       string;
  /** Firmante resuelto */
  firmante:           FirmanteActivo;
  /** Archivos manuales adjuntos (se suben después del PDF) */
  archivos:           File[];
  onNavigate:         (idDocumento: number) => void;
}

// ── Componente ────────────────────────────────────────────────

export function MemorandumModal({
  open, onClose,
  docPayload, referencia, cuerpo,
  destinosNombres, origenNombre, firmante,
  archivos, onNavigate,
}: MemorandumModalProps) {
  const [fase, setFase] = useState<'cargando' | 'preview' | 'confirmando' | 'error'>('cargando');
  const [pdfUrl,    setPdfUrl]    = useState<string | null>(null);
  const [errorMsg,  setErrorMsg]  = useState('');
  const objUrlRef = useRef<string | null>(null);

  // Limpia el blob URL cuando el modal se desmonta o cierra
  function liberarUrl() {
    if (objUrlRef.current) {
      URL.revokeObjectURL(objUrlRef.current);
      objUrlRef.current = null;
    }
    setPdfUrl(null);
  }

  // Genera el PDF de previsualización cuando el modal se abre
  useEffect(() => {
    if (!open) { liberarUrl(); setFase('cargando'); return; }

    setFase('cargando');

    (async () => {
      try {
        // Cargar logo e imagen firma+timbre en paralelo.
        // - Logo: usa cargarImagenDataUrl (JPEG, sin transparencia — es solo decorativo)
        // - Firma+timbre: usa cargarFirmaTimbreOriginal (preserva PNG transparente + dimensiones)
        const ftUrl = firmante.firmaTimbreUrl ? uploadUrl(firmante.firmaTimbreUrl) : null;

        const [logoB64, ftResult] = await Promise.all([
          apiClient.get<{ ok: boolean; data: { logoUrl: string | null } }>('/configuracion')
            .then((r) => {
              const url = uploadUrl(r.data.data.logoUrl);
              return url ? cargarImagenDataUrl(url) : null;
            })
            .catch(() => null),
          ftUrl ? cargarFirmaTimbreOriginal(ftUrl) : Promise.resolve({ dataUrl: null, natW: 0, natH: 0 }),
        ]);

        if (!logoB64)           console.warn('[memoModal] Logo no disponible — verificar Configuración → Logo del Sistema');
        if (!ftResult.dataUrl)  console.warn('[memoModal] Firma+Timbre no cargada — URL:', firmante.firmaTimbreUrl);

        const memoData: MemorandumData = {
          correlativo:        null,
          esBorrador:         true,
          fechaDocumento:     docPayload.fechaDocumento ?? null,
          origen:             origenNombre,
          destinos:           destinosNombres,
          materia:            docPayload.materia,
          referencia:         referencia || null,
          cuerpo,
          nombreFirmante:     firmante.nombre ?? '',
          cargoFirmante:      firmante.cargo  ?? '',
          firmaTimbreBase64:  ftResult.dataUrl,
          firmaTimbreNatW:    ftResult.natW,
          firmaTimbreNatH:    ftResult.natH,
          logoBase64:         logoB64,
        };

        const doc    = generarMemorandumPDF(memoData);
        const blob   = doc.output('blob');
        const url    = URL.createObjectURL(blob);
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

  // ── Confirmar y crear definitivamente ────────────────────────
  async function handleConfirmar() {
    setFase('confirmando');
    try {
      // 1. Crear el documento (flujo normal existente)
      const docRes = await apiClient.post<{ ok: boolean; data: { idDocumento: number } }>(
        '/documentos',
        docPayload
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

      // 3. Generar PDF definitivo con correlativo real
      const ftUrlDef = firmante.firmaTimbreUrl ? uploadUrl(firmante.firmaTimbreUrl) : null;

      const [logoB64Def, ftResultDef] = await Promise.all([
        apiClient.get<{ ok: boolean; data: { logoUrl: string | null } }>('/configuracion')
          .then((r) => {
            const url = uploadUrl(r.data.data.logoUrl);
            return url ? cargarImagenDataUrl(url) : null;
          })
          .catch(() => null),
        ftUrlDef ? cargarFirmaTimbreOriginal(ftUrlDef) : Promise.resolve({ dataUrl: null, natW: 0, natH: 0 }),
      ]);

      const memoDefin: MemorandumData = {
        correlativo,
        esBorrador:         false,
        fechaDocumento:     docPayload.fechaDocumento ?? null,
        origen:             origenNombre,
        destinos:           destinosNombres,
        materia:            docPayload.materia,
        referencia:         referencia || null,
        cuerpo,
        nombreFirmante:     firmante.nombre ?? '',
        cargoFirmante:      firmante.cargo  ?? '',
        firmaTimbreBase64:  ftResultDef.dataUrl,
        firmaTimbreNatW:    ftResultDef.natW,
        firmaTimbreNatH:    ftResultDef.natH,
        logoBase64:         logoB64Def,
      };

      const docPdf  = generarMemorandumPDF(memoDefin);
      const pdfBlob = docPdf.output('blob');
      const pdfFile = new File([pdfBlob], `${correlativo}.pdf`, { type: 'application/pdf' });

      // 4. Subir PDF como adjunto del documento (endpoint existente sin modificar)
      const formPdf = new FormData();
      formPdf.append('archivo',    pdfFile);
      formPdf.append('idDocumento', String(idDocumento));
      const uploadRes = await apiClient.post<{ ok: boolean; data: { idArchivo: number } }>(
        '/archivos/upload',
        formPdf,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      const idArchivo = uploadRes.data.data?.idArchivo ?? null;

      // 5. Vincular archivo con memo_generado (best-effort)
      if (idArchivo) {
        await apiClient.patch('/memorandum/vincular-archivo', { correlativo, idArchivo }).catch(() => {/* no crítico */});
      }

      // 6. Subir archivos manuales adjuntos (si los hay)
      const erroresArchivos: string[] = [];
      for (const file of archivos) {
        try {
          const formFile = new FormData();
          formFile.append('archivo',    file);
          formFile.append('idDocumento', String(idDocumento));
          await apiClient.post('/archivos/upload', formFile, {
            headers: { 'Content-Type': 'multipart/form-data' },
          });
        } catch {
          erroresArchivos.push(file.name);
        }
      }
      if (erroresArchivos.length > 0) {
        toast.warning(`Memorándum ${correlativo} creado, pero ${erroresArchivos.length} adjunto(s) manual(es) no se pudieron subir.`);
      } else {
        toast.success(`Memorándum ${correlativo} generado y despachado correctamente`);
      }

      liberarUrl();
      onNavigate(idDocumento);
    } catch (err: unknown) {
      const msg = (err as { response?: { data?: { error?: string } } })?.response?.data?.error
        ?? 'Error al crear el memorándum. Intenta nuevamente.';
      setErrorMsg(msg);
      setFase('error');
      toast.error(msg);
    }
  }

  if (!open) return null;

  return (
    <div className="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
      <div className="bg-background rounded-xl border shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col">

        {/* Header */}
        <div className="flex items-center justify-between px-5 py-4 border-b shrink-0">
          <div className="flex items-center gap-3">
            <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-600 shrink-0">
              <FileText className="h-5 w-5" />
            </div>
            <div>
              <h2 className="font-semibold text-sm">Vista previa — Memorándum</h2>
              <p className="text-xs text-muted-foreground">
                {fase === 'cargando'    && 'Generando previsualización...'}
                {fase === 'preview'     && 'Revisa el documento antes de confirmar'}
                {fase === 'confirmando' && 'Creando el memorándum...'}
                {fase === 'error'       && 'Se produjo un error'}
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            disabled={fase === 'confirmando'}
            className="text-muted-foreground hover:text-foreground transition-colors disabled:opacity-40"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        {/* Body */}
        <div className="flex-1 overflow-hidden relative">

          {/* Estado: cargando */}
          {fase === 'cargando' && (
            <div className="flex flex-col items-center justify-center h-full gap-3 text-muted-foreground py-16">
              <Loader2 className="h-10 w-10 animate-spin" />
              <p className="text-sm">Generando previsualización del memorándum…</p>
            </div>
          )}

          {/* Estado: error */}
          {fase === 'error' && (
            <div className="flex flex-col items-center justify-center h-full gap-3 text-destructive py-16">
              <AlertCircle className="h-10 w-10" />
              <p className="text-sm font-medium">{errorMsg}</p>
            </div>
          )}

          {/* Estado: confirmando */}
          {fase === 'confirmando' && (
            <div className="flex flex-col items-center justify-center h-full gap-3 text-muted-foreground py-16">
              <Loader2 className="h-10 w-10 animate-spin text-blue-500" />
              <p className="text-sm font-medium text-foreground">Asignando correlativo y guardando…</p>
              <p className="text-xs">No cierres esta ventana</p>
            </div>
          )}

          {/* Estado: preview */}
          {fase === 'preview' && pdfUrl && (
            <div className="h-full flex flex-col">
              {/* Banner de borrador */}
              <div className="flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800/40 shrink-0">
                <Eye className="h-3.5 w-3.5 text-amber-600 shrink-0" />
                <p className="text-xs text-amber-700 dark:text-amber-400">
                  <strong>Vista previa (borrador)</strong> — El correlativo real se asignará al confirmar.
                  Verifica el contenido antes de continuar.
                </p>
              </div>
              {/* iframe PDF */}
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
          'flex items-center justify-between px-5 py-4 border-t shrink-0 gap-3',
          fase !== 'preview' && 'justify-end'
        )}>
          {/* Firmante info (solo en preview) */}
          {fase === 'preview' && (
            <div className="text-xs text-muted-foreground flex items-center gap-1.5 min-w-0">
              <CheckCircle2 className="h-3.5 w-3.5 text-emerald-500 shrink-0" />
              <span className="truncate">
                Firmante: <strong>{firmante.nombre}</strong> — {firmante.cargo}
                {firmante.tipo === 'SUBROGANTE' && ' (Subrogante)'}
              </span>
            </div>
          )}

          <div className="flex items-center gap-2 shrink-0">
            {/* Editar */}
            {(fase === 'preview' || fase === 'error') && (
              <Button
                variant="outline"
                size="sm"
                className="gap-2"
                onClick={onClose}
              >
                <ArrowLeft className="h-3.5 w-3.5" />
                Volver a editar
              </Button>
            )}

            {/* Confirmar */}
            {fase === 'preview' && (
              <Button
                size="sm"
                className="gap-2 bg-blue-600 hover:bg-blue-700"
                onClick={handleConfirmar}
              >
                <Send className="h-3.5 w-3.5" />
                Confirmar y Despachar
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
