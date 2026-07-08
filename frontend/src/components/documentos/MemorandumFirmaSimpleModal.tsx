import { useState, useEffect, useRef } from 'react';
import {
  X, Eye, Loader2, AlertCircle, ArrowLeft, KeyRound, FileText, Lock,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { apiClient } from '@/lib/api/client';
import { toast } from 'sonner';
import { generarMemorandumPDF, cargarImagenDataUrl, type MemorandumData } from '@/lib/utils/memorandum.generator';
import { cn, uploadUrl } from '@/lib/utils';
import type { MemoDocumentoPayload } from './MemorandumModal';

// ── Tipos ────────────────────────────────────────────────────

/** Firmante con Firma Simple habilitada — subconjunto de la respuesta de
 *  GET /memorandum/firmantes-disponibles */
export interface FirmanteFirmaSimple {
  tipo:              string;
  idFirmante:        number; // = id_jefatura
  nombre:            string;
  cargo:             string;
  rut:               string | null;
  firmaTimbreUrl:    string | null;
  dependencia:       string | null;
  idDependencia:     number;
}

export interface MemorandumFirmaSimpleModalProps {
  open:            boolean;
  onClose:         () => void;
  docPayload:      MemoDocumentoPayload;
  referencia:      string;
  cuerpo:          string;
  destinosNombres: string[];
  origenNombre:    string;
  firmante:        FirmanteFirmaSimple;
  archivos:        File[];
  onNavigate:      (idDocumento: number) => void;
}

type Fase = 'cargando' | 'preview' | 'firmando' | 'error';

export function MemorandumFirmaSimpleModal({
  open, onClose,
  docPayload, referencia, cuerpo,
  destinosNombres, origenNombre, firmante,
  archivos, onNavigate,
}: MemorandumFirmaSimpleModalProps) {
  const [fase, setFase]         = useState<Fase>('cargando');
  const [pdfUrl, setPdfUrl]     = useState<string | null>(null);
  const [errorMsg, setErrorMsg] = useState('');
  const [password, setPassword] = useState('');
  const [declaro, setDeclaro]   = useState(false);
  const objUrlRef = useRef<string | null>(null);

  function liberarUrl() {
    if (objUrlRef.current) { URL.revokeObjectURL(objUrlRef.current); objUrlRef.current = null; }
    setPdfUrl(null);
  }

  // Genera el PDF de previsualización (pendiente de Firma Simple — sin evidencia todavía)
  useEffect(() => {
    if (!open) { liberarUrl(); setFase('cargando'); setPassword(''); setDeclaro(false); return; }

    setFase('cargando');

    (async () => {
      try {
        const logoB64 = await apiClient.get<{ ok: boolean; data: { logoUrl: string | null } }>('/configuracion')
          .then((r) => { const url = uploadUrl(r.data.data.logoUrl); return url ? cargarImagenDataUrl(url) : null; })
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
          nombreFirmante:    firmante.nombre,
          cargoFirmante:     firmante.cargo,
          firmaTimbreBase64: null,
          firmaTimbreNatW:   0,
          firmaTimbreNatH:   0,
          logoBase64:        logoB64,
          servicioFirmante:  firmante.dependencia ?? origenNombre,
          mecanismoFirma:    'FIRMA_SIMPLE',
        };

        const doc  = generarMemorandumPDF(memoData);
        const blob = doc.output('blob');
        const url  = URL.createObjectURL(blob);
        objUrlRef.current = url;
        setPdfUrl(url);
        setFase('preview');
      } catch (err) {
        console.error('[MemorandumFirmaSimpleModal] Error al generar PDF:', err);
        setErrorMsg('Error al generar la previsualización del PDF.');
        setFase('error');
      }
    })();

    return () => { liberarUrl(); };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  async function handleFirmar() {
    if (!declaro) {
      toast.error('Debes marcar la declaración de firma para continuar');
      return;
    }
    if (!password) {
      toast.error('Ingresa tu contraseña DOC360');
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
          materia:        docPayload.materia,
          referencia:     referencia || null,
          cuerpo,
          nombreFirmante: firmante.nombre,
          cargoFirmante:  firmante.cargo,
          tipoFirmante:   firmante.tipo,
          firmaTimbreRuta: firmante.firmaTimbreUrl,
          idDependencia:  firmante.idDependencia,
        }
      );
      const { correlativo } = corrRes.data.data;

      // 3. Generar PDF pre-firma (sin evidencia todavía) y subirlo como borrador
      const logoB64 = await apiClient.get<{ ok: boolean; data: { logoUrl: string | null } }>('/configuracion')
        .then((r) => { const url = uploadUrl(r.data.data.logoUrl); return url ? cargarImagenDataUrl(url) : null; })
        .catch(() => null);

      const memoPrefirma: MemorandumData = {
        correlativo, esBorrador: false,
        fechaDocumento:   docPayload.fechaDocumento ?? null,
        origen:           origenNombre, destinos: destinosNombres,
        materia:          docPayload.materia, referencia: referencia || null, cuerpo,
        nombreFirmante:   firmante.nombre, cargoFirmante: firmante.cargo,
        firmaTimbreBase64: null, firmaTimbreNatW: 0, firmaTimbreNatH: 0,
        logoBase64:       logoB64,
        servicioFirmante: firmante.dependencia ?? origenNombre,
        mecanismoFirma:   'FIRMA_SIMPLE',
      };
      const pdfPrefirma  = generarMemorandumPDF(memoPrefirma);
      const blobPrefirma = pdfPrefirma.output('blob');
      const filePrefirma = new File([blobPrefirma], `${correlativo}.pdf`, { type: 'application/pdf' });

      const formPdf = new FormData();
      formPdf.append('archivo',     filePrefirma);
      formPdf.append('idDocumento', String(idDocumento));
      const uploadRes = await apiClient.post<{ ok: boolean; data: { idArchivo: number } }>(
        '/archivos/upload', formPdf, { headers: { 'Content-Type': 'multipart/form-data' } }
      );
      const idArchivoOriginal = uploadRes.data.data?.idArchivo;
      if (!idArchivoOriginal) throw new Error('No se pudo subir el PDF del memorándum');

      await apiClient.patch('/memorandum/vincular-archivo', { correlativo, idArchivo: idArchivoOriginal })
        .catch(() => {/* no crítico */});

      // 4. Adjuntos manuales
      const erroresArchivos: string[] = [];
      for (const file of archivos) {
        try {
          const formFile = new FormData();
          formFile.append('archivo',     file);
          formFile.append('idDocumento', String(idDocumento));
          await apiClient.post('/archivos/upload', formFile, { headers: { 'Content-Type': 'multipart/form-data' } });
        } catch { erroresArchivos.push(file.name); }
      }

      // 5. Fase A — validar identidad del firmante y obtener evidencia
      const firmaSimpleRes = await apiClient.post<{
        ok: boolean; data: {
          idFirmaSimple: number; codigoVerificacion: string; hashOriginalCorto: string;
          fechaFirma: string; firmante: { nombre: string; cargo: string; servicio: string | null; tipo: string };
        };
      }>(`/memorandum/${idDocumento}/firmar-simple`, {
        idJefatura:   firmante.idFirmante,
        tipoJefatura: firmante.tipo,
        password,
        confirmacion: true,
      });
      const { idFirmaSimple, codigoVerificacion, hashOriginalCorto, fechaFirma } = firmaSimpleRes.data.data;

      // 6. Generar el PDF final con el sello ya con evidencia real
      const firmaTimbreB64 = firmante.firmaTimbreUrl
        ? await cargarImagenDataUrl(uploadUrl(firmante.firmaTimbreUrl) ?? firmante.firmaTimbreUrl).catch(() => null)
        : null;

      const memoFinal: MemorandumData = {
        ...memoPrefirma,
        firmaTimbreBase64: firmaTimbreB64,
        datosFirmaSimple: { codigoVerificacion, hashOriginalCorto, fechaFirma },
      };
      const pdfFinal   = generarMemorandumPDF(memoFinal);
      const blobFinal  = pdfFinal.output('blob');
      const fileFinal  = new File([blobFinal], `${correlativo}.pdf`, { type: 'application/pdf' });

      // 7. Fase B — subir el PDF final firmado, el backend recalcula el hash y despacha
      const formFinal = new FormData();
      formFinal.append('archivo', fileFinal);
      await apiClient.patch(`/memorandum/${idDocumento}/firmar-simple/${idFirmaSimple}/completar`, formFinal, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      if (erroresArchivos.length > 0) {
        toast.warning(`Memorándum ${correlativo} firmado, pero ${erroresArchivos.length} adjunto(s) no se pudo(n) subir.`);
      } else {
        toast.success(`Memorándum ${correlativo} firmado con Firma Simple DOC360 (${codigoVerificacion})`);
      }

      liberarUrl();
      onNavigate(idDocumento);
    } catch (err: unknown) {
      const msg = (err as { response?: { data?: { error?: string } } })?.response?.data?.error
        ?? 'Error al procesar la Firma Simple. Intenta nuevamente.';
      setErrorMsg(msg);
      setFase('error');
      toast.error(msg);
    }
  }

  if (!open) return null;

  return (
    <div className="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4">
      <div className="modal-panel bg-background w-full max-w-4xl max-h-[92vh] flex flex-col">

        {/* Header */}
        <div className="flex items-center justify-between px-5 py-4 border-b shrink-0">
          <div className="flex items-center gap-3">
            <span className="icon-3d icon-3d-emerald flex h-9 w-9 shrink-0 items-center justify-center">
              <KeyRound className="h-5 w-5 text-white" />
            </span>
            <div>
              <h2 className="font-semibold text-sm">Firma Simple DOC360</h2>
              <p className="text-xs text-muted-foreground">
                {fase === 'cargando' && 'Generando previsualización...'}
                {fase === 'preview'  && 'Revisa el documento y autoriza la firma con tu contraseña'}
                {fase === 'firmando' && 'Procesando firma…'}
                {fase === 'error'    && 'Se produjo un error'}
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
        <div className="flex-1 overflow-hidden relative flex">

          {fase === 'cargando' && (
            <div className="flex flex-col items-center justify-center w-full gap-3 text-muted-foreground py-16">
              <Loader2 className="h-10 w-10 animate-spin" />
              <p className="text-sm">Generando previsualización del memorándum…</p>
            </div>
          )}

          {fase === 'error' && (
            <div className="flex flex-col items-center justify-center w-full gap-4 py-16 px-6">
              <AlertCircle className="h-10 w-10 text-destructive" />
              <p className="text-sm font-medium text-destructive text-center">{errorMsg}</p>
              <p className="text-xs text-muted-foreground text-center max-w-md">
                El documento puede haberse creado pero <strong>no fue despachado</strong> porque la firma no se completó.
                Puedes intentarlo nuevamente desde el detalle del documento.
              </p>
            </div>
          )}

          {fase === 'firmando' && (
            <div className="flex flex-col items-center justify-center w-full gap-3 text-muted-foreground py-16">
              <Loader2 className="h-10 w-10 animate-spin text-emerald-500" />
              <p className="text-sm font-medium text-foreground">Procesando Firma Simple…</p>
              <p className="text-xs">No cierres esta ventana.</p>
            </div>
          )}

          {fase === 'preview' && pdfUrl && (
            <>
              <div className="flex-1 flex flex-col border-r">
                <div className="flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800/40 shrink-0">
                  <Eye className="h-3.5 w-3.5 text-amber-600 shrink-0" />
                  <p className="text-xs text-amber-700 dark:text-amber-400">
                    <strong>Vista previa</strong> — el código de verificación y el hash se generan al firmar.
                  </p>
                </div>
                <iframe src={pdfUrl} title="Vista previa memorándum" className="flex-1 w-full border-0" style={{ minHeight: '400px' }} />
              </div>

              {/* Panel de firma */}
              <div className="w-80 shrink-0 flex flex-col p-4 gap-3 overflow-y-auto">
                <div className="rounded-lg border border-border/60 bg-muted/20 p-3 space-y-1">
                  <p className="text-xs text-muted-foreground">Firmante</p>
                  <p className="text-sm font-medium">{firmante.nombre}</p>
                  <p className="text-xs text-muted-foreground">{firmante.cargo}</p>
                  <p className="text-xs text-muted-foreground">{firmante.dependencia}</p>
                  {firmante.tipo === 'SUBROGANTE'   && <p className="text-xs text-amber-600">Subrogante 1</p>}
                  {firmante.tipo === 'SUBROGANTE_2' && <p className="text-xs text-amber-600">Subrogante 2</p>}
                </div>

                <p className="text-xs text-muted-foreground leading-relaxed">
                  Está a punto de firmar electrónicamente mediante <strong>Firma Simple DOC360</strong> este memorándum.
                  Esta acción quedará registrada con usuario, fecha, hora, trazabilidad y hash del documento.
                </p>

                <div className="space-y-1.5">
                  <label className="text-xs font-medium text-muted-foreground">Contraseña DOC360 de {firmante.nombre}</label>
                  <div className="relative">
                    <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                    <input
                      type="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="Contraseña"
                      className="w-full h-9 pl-9 pr-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                    />
                  </div>
                </div>

                <label className="flex items-start gap-2 cursor-pointer">
                  <input type="checkbox" checked={declaro} onChange={(e) => setDeclaro(e.target.checked)} className="h-4 w-4 mt-0.5" />
                  <span className="text-xs text-muted-foreground">
                    Declaro que soy el firmante indicado y autorizo la firma simple de este memorándum mediante mis credenciales institucionales DOC360.
                  </span>
                </label>
              </div>
            </>
          )}
        </div>

        {/* Footer acciones */}
        <div className="flex items-center justify-end px-5 py-4 border-t shrink-0 gap-2">
          {(fase === 'preview' || fase === 'error') && (
            <Button variant="outline" size="sm" className="gap-2" onClick={onClose}>
              <ArrowLeft className="h-3.5 w-3.5" />
              Cancelar
            </Button>
          )}
          {fase === 'preview' && (
            <Button
              size="sm"
              className={cn('gap-2 bg-emerald-600 hover:bg-emerald-700', (!declaro || !password) && 'opacity-60')}
              onClick={handleFirmar}
              disabled={!declaro || !password}
            >
              <FileText className="h-3.5 w-3.5" />
              Firmar documento
            </Button>
          )}
        </div>
      </div>
    </div>
  );
}
