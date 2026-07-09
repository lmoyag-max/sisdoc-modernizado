import { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, useMutation } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  FileText, ArrowLeft, Send, Paperclip, X, AlertCircle,
  Building2, Tag, MessageSquare, Calendar, Loader2, ChevronRight, Lock,
  Search, CheckSquare, Square, FileStack, ShieldAlert, Eye, KeyRound,
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { useAuthStore } from '@/stores/auth.store';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';
import { useUploadRules } from '@/hooks/useUploadRules';
import { NominaModal } from '@/components/documentos/NominaModal';
import { type NominaData } from '@/lib/utils/nomina.generator';
import { MemorandumFields } from '@/components/documentos/MemorandumFields';
import { type FirmanteActivo, type MemoDocumentoPayload } from '@/components/documentos/MemorandumModal';
import { MemorandumFirmaSimpleModal, type FirmanteFirmaSimple } from '@/components/documentos/MemorandumFirmaSimpleModal';

// ── Schema ───────────────────────────────────────────────────
const schema = z.object({
  materia:            z.string().max(250).optional().default(''),
  idTipoDocumento:    z.string().min(1, 'Selecciona el tipo de documento'),
  fechaDocumento:     z.string().optional(),
  observaciones:      z.string().max(500).optional(),
  tipoDestinatario:   z.enum(['D', 'E']).default('D'),
  idTipoDistribucion: z.string().default('5'),
  idTipoCompromiso:   z.string().default('1'),
  idEstadoCompromiso: z.string().default('2'),
  diasCompromiso:     z.string().default('0'),
});
type FormData = z.infer<typeof schema>;

interface CatItem { id: number; descripcion: string }

// ID de la dependencia "Dirección" — forzado cuando reservado=true
const ID_DIRECCION = 32;

const sel = (hasErr?: boolean) => cn(
  'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm',
  'focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
  hasErr && 'border-destructive'
);

function formatSize(bytes: number): string {
  if (bytes < 1024)        return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function NuevoDocumentoPage() {
  const navigate   = useNavigate();
  const user       = useAuthStore((s) => s.user);

  // Reglas de carga configurables (lee desde /configuracion, defaults seguros si falla)
  const uploadRules       = useUploadRules();
  const MAX_FILE_MB       = uploadRules.maxFileMB;
  const MAX_FILE_BYTES    = MAX_FILE_MB * 1024 * 1024;
  const CUOTA_TOTAL_MB    = uploadRules.maxTotalMB;
  const CUOTA_TOTAL_BYTES = CUOTA_TOTAL_MB * 1024 * 1024;

  // ── Estado de campos generales ────────────────────────────
  const [archivos, setArchivos]   = useState<File[]>([]);
  const [dragOver, setDragOver]   = useState(false);
  const totalBytes    = archivos.reduce((sum, f) => sum + f.size, 0);
  const excuotaTotal  = totalBytes > CUOTA_TOTAL_BYTES;
  const [destinosSeleccionados, setDestinosSeleccionados] = useState<number[]>([]);
  const [destinoExterno, setDestinoExterno]               = useState('');
  const [searchDep, setSearchDep]                         = useState('');
  const [destinoError, setDestinoError]                   = useState('');

  // ── Estado Oficina de Partes ──────────────────────────────
  const [tipoSoporte, setTipoSoporte] = useState<'D' | 'F'>('D');
  const [reservado, setReservado]     = useState(false);

  // ── Estado modal nómina ───────────────────────────────────
  const [nominaOpen, setNominaOpen]   = useState(false);
  const [nominaData, setNominaData]   = useState<NominaData | null>(null);
  const [idDocCreado, setIdDocCreado] = useState<number | null>(null);

  // ── Estado Memorándum ─────────────────────────────────────
  const [memoMateria,           setMemoMateria]           = useState('');
  const [memoMateriaErr,        setMemoMateriaErr]        = useState<string | null>(null);
  const [memoReferencia,        setMemoReferencia]        = useState('');
  const [memoCuerpo,            setMemoCuerpo]            = useState('');
  const [memoCuerpoErr,         setMemoCuerpoErr]         = useState<string | null>(null);
  const [memoFirmante,          setMemoFirmante]          = useState<FirmanteActivo | null>(null);
  const [memoPayload,           setMemoPayload]           = useState<MemoDocumentoPayload | null>(null);
  const [firmanteSeleccionado,  setFirmanteSeleccionado]  = useState<FirmanteActivo | null>(null);
  // El memorándum se firma exclusivamente con Firma Simple DOC360 (FirmaGOB
  // queda fuera de este flujo por regla de negocio, aunque su módulo sigue
  // intacto en el sistema para otros usos futuros).
  const [memoFirmaSimpleOpen,   setMemoFirmaSimpleOpen]   = useState(false);

  // ── Estado destinatario específico del Memorándum (persona, no solo servicio) ──
  const [memoServicioDestino,    setMemoServicioDestino]    = useState('');
  const [memoDestinatario,       setMemoDestinatario]       = useState<{ id: number; nombre: string; cargo: string | null } | null>(null);
  const [memoCargoManual,        setMemoCargoManual]        = useState('');
  const [memoBuscarDestinatario, setMemoBuscarDestinatario] = useState('');
  const [memoDestinatarioErr,    setMemoDestinatarioErr]    = useState<string | null>(null);
  // Snapshot final (nombre + cargo resuelto) que se pasa al modal de Firma
  // Simple al confirmar — separado de memoDestinatario (que es la selección
  // en curso del picker) para no atarlo a la forma del catálogo.
  const [memoDestinatarioInfo,   setMemoDestinatarioInfo]   = useState<{ nombre: string; cargo: string } | null>(null);

  // Cambiar de servicio destinatario limpia la persona elegida — evita
  // enviar un destinatario que ya no corresponde al servicio seleccionado.
  useEffect(() => {
    setMemoDestinatario(null);
    setMemoCargoManual('');
    setMemoBuscarDestinatario('');
    setMemoDestinatarioErr(null);
  }, [memoServicioDestino]);

  // Solo of.partes y admin ven las opciones especiales
  const esOficinaPartes = user?.roles?.includes('of.partes') || user?.roles?.includes('admin');

  // Cuando reservado=true, forzar destino a Dirección automáticamente
  useEffect(() => {
    if (reservado) {
      setDestinosSeleccionados([ID_DIRECCION]);
      setDestinoError('');
    } else {
      // Solo limpiar si el único seleccionado era Dirección por reservado
      setDestinosSeleccionados((prev) =>
        prev.length === 1 && prev[0] === ID_DIRECCION ? [] : prev
      );
    }
  }, [reservado]);

  const toggleDestino = (id: number) => {
    if (reservado) return; // bloqueado cuando es reservado
    setDestinoError('');
    setDestinosSeleccionados((prev) =>
      prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]
    );
  };

  const agregarArchivos = (lista: FileList | File[]) => {
    const nuevos = Array.from(lista);
    const rechazados: string[] = [];
    const validos:    File[]   = [];

    for (const f of nuevos) {
      const ext = f.name.split('.').pop()?.toLowerCase() ?? '';
      if (!uploadRules.extensionesPermitidas.includes(ext)) {
        rechazados.push(`${f.name} (tipo .${ext} no permitido)`);
      } else if (f.size > MAX_FILE_BYTES) {
        rechazados.push(`${f.name} (supera ${MAX_FILE_MB} MB)`);
      } else {
        validos.push(f);
      }
    }
    if (rechazados.length > 0) {
      toast.error(
        `${rechazados.length} archivo${rechazados.length > 1 ? 's' : ''} rechazado${rechazados.length > 1 ? 's' : ''}: ` +
        `${rechazados.slice(0, 3).join(', ')}${rechazados.length > 3 ? '…' : ''}`
      );
    }
    if (validos.length > 0) {
      setArchivos((prev) => {
        const sinDuplicados = validos.filter(
          (n) => !prev.some((p) => p.name === n.name && p.size === n.size)
        );
        return [...prev, ...sinDuplicados];
      });
    }
  };

  // ── Catálogos ──────────────────────────────────────────────
  const { data: tipos }        = useQuery({ queryKey: ['tipos-doc'],    queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/tipos-documento')).data.data });
  const { data: dependencias } = useQuery({ queryKey: ['dependencias'], queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/dependencias')).data.data });
  const { data: depExternas }  = useQuery({ queryKey: ['dep-externas'],queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/dependencias-externas')).data.data });
  const { data: tiposDist }    = useQuery({ queryKey: ['tipos-dist'],   queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/tipos-distribucion')).data.data });
  const { data: tiposCom }     = useQuery({ queryKey: ['tipos-comp'],   queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/tipos-compromiso')).data.data });
  const { data: estadosCom }   = useQuery({ queryKey: ['estados-comp'], queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/estados-compromiso')).data.data });

  const depsFiltradas = useMemo(() =>
    (dependencias ?? []).filter((d) =>
      d.descripcion.toLowerCase().includes(searchDep.toLowerCase())
    ),
    [dependencias, searchDep]
  );

  // Nombre de la dependencia "Dirección" para mostrarlo al usuario
  const nombreDireccion = useMemo(() =>
    (dependencias ?? []).find((d) => d.id === ID_DIRECCION)?.descripcion ?? 'Dirección',
    [dependencias]
  );

  const { register, handleSubmit, watch, setError, formState: { errors, isSubmitting } } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: {
      fechaDocumento:     new Date().toISOString().split('T')[0],
      tipoDestinatario:   'D',
      idTipoDistribucion: '5',
      idTipoCompromiso:   '1',
      idEstadoCompromiso: '2',
      diasCompromiso:     '0',
    },
  });

  const puedeExterno = user?.roles?.includes('admin') || user?.roles?.includes('of.partes');
  const tipoDestinatario  = watch('tipoDestinatario');
  const idTipoCompromiso  = watch('idTipoCompromiso');
  const idTipoDocSelected = watch('idTipoDocumento');

  // Detecta dinámicamente si el tipo seleccionado es Memorándum
  // (por descripción, no por ID hardcodeado — robusto ante cambios en BD)
  const esMemorandum = Boolean(
    idTipoDocSelected &&
    (tipos ?? []).find((t) => t.id === Number(idTipoDocSelected))
      ?.descripcion?.toLowerCase().includes('memorandum')
  );
  const materia = watch('materia');

  // ── Firmantes disponibles (solo cuando el tipo es Memorándum) ──
  // El queryKey incluye idDependencia para que cada usuario tenga su propia caché
  // y no se contaminen resultados entre sesiones de distintos usuarios.
  const { data: firmantesDisponibles, isLoading: cargandoFirmantes } = useQuery({
    queryKey: ['firmantes-disponibles', user?.idDependencia],
    queryFn:  async () => {
      const r = await apiClient.get<{ ok: boolean; data: { firmantes: FirmanteActivo[] } }>(
        '/memorandum/firmantes-disponibles'
      );
      return r.data.data.firmantes ?? [];
    },
    enabled:   esMemorandum && Boolean(user?.idDependencia),
    staleTime: 2 * 60 * 1000,
  });

  // Auto-seleccionar el primer firmante vigente cuando carga la lista
  useEffect(() => {
    if (!firmantesDisponibles) return;
    if (firmanteSeleccionado) return;
    const primerVigente = firmantesDisponibles.find((f) => f.vigente !== false);
    if (primerVigente) setFirmanteSeleccionado(primerVigente);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [firmantesDisponibles]);

  // ── Destinatario específico del Memorándum: funcionarios del servicio elegido ──
  // Reutiliza GET /catalogos/dependencias/:idDep/funcionarios (ya existía, sin uso
  // hasta ahora), extendido en el backend para traer el cargo cuando el funcionario
  // es titular/subrogante de la jefatura de ese servicio.
  const { data: funcionariosDestino, isLoading: cargandoFuncionariosDestino } = useQuery({
    queryKey: ['funcionarios-destino', memoServicioDestino],
    queryFn: async () => {
      const r = await apiClient.get<{ ok: boolean; data: { id: number; nombre: string; cargo: string | null }[] }>(
        `/catalogos/dependencias/${memoServicioDestino}/funcionarios`
      );
      return r.data.data ?? [];
    },
    enabled:   esMemorandum && Boolean(memoServicioDestino),
    staleTime: 60 * 1000,
  });

  const funcionariosDestinoFiltrados = useMemo(() =>
    (funcionariosDestino ?? []).filter((f) =>
      f.nombre.toLowerCase().includes(memoBuscarDestinatario.toLowerCase())
    ),
    [funcionariosDestino, memoBuscarDestinatario]
  );

  const mutation = useMutation({
    mutationFn: async (data: FormData) => {
      // Para documentos que NO son memorándum, materia es obligatoria (mín. 5 chars)
      if (!data.materia || data.materia.trim().length < 5) {
        setError('materia', { type: 'manual', message: 'Mínimo 5 caracteres' });
        return null;
      }

      // Validación de destino (no corre dentro de RHF porque es estado local)
      if (data.tipoDestinatario === 'D' && destinosSeleccionados.length === 0) {
        setDestinoError('Selecciona al menos un servicio destino');
        return null;
      }
      if (data.tipoDestinatario === 'E' && !destinoExterno) {
        setDestinoError('Selecciona un destino externo');
        return null;
      }

      if (excuotaTotal) {
        toast.error(`La suma total de los archivos supera el máximo permitido (${CUOTA_TOTAL_MB} MB)`);
        return null;
      }

      const base = {
        materia:            data.materia,
        idTipoDocumento:    Number(data.idTipoDocumento),
        fechaDocumento:     data.fechaDocumento,
        observaciones:      data.observaciones,
        idTipoDistribucion: Number(data.idTipoDistribucion),
        idTipoCompromiso:   Number(data.idTipoCompromiso),
        idEstadoCompromiso: Number(data.idEstadoCompromiso),
        diasCompromiso:     Number(data.diasCompromiso),
        // Campos Oficina de Partes
        ...(esOficinaPartes && { tipoSoporte, reservado }),
      };

      const payload = data.tipoDestinatario === 'D'
        ? { ...base, tipoDestinatario: 'D', destinos: destinosSeleccionados }
        : { ...base, tipoDestinatario: 'E', idDestino: Number(destinoExterno) };

      const res = await apiClient.post<{ ok: boolean; data: Record<string, unknown> }>('/documentos', payload);
      const docData = res.data.data;
      const idDocumento = docData?.idDocumento as number | undefined;

      if (archivos.length > 0 && idDocumento) {
        const errores: string[] = [];
        for (const file of archivos) {
          try {
            const form = new FormData();
            form.append('archivo', file);
            form.append('idDocumento', String(idDocumento));
            await apiClient.post('/archivos/upload', form, { headers: { 'Content-Type': 'multipart/form-data' } });
          } catch {
            errores.push(file.name);
          }
        }
        if (errores.length > 0) {
          toast.warning(
            `Documento registrado, pero ${errores.length} archivo${errores.length > 1 ? 's' : ''} no se pudo${errores.length > 1 ? 'ron' : ''} adjuntar: ` +
            `${errores.slice(0, 3).join(', ')}${errores.length > 3 ? '…' : ''}`
          );
        }
      }
      return docData;
    },
    onSuccess: async (docData) => {
      if (!docData) return; // validación de destino falló
      const idDocumento = docData.idDocumento as number;

      if (esOficinaPartes && tipoSoporte === 'F') {
        // Obtener destinos para la nómina
        try {
          const destRes = await apiClient.get<{ ok: boolean; data: Array<{ servicio: string | null }> }>(
            `/documentos/${idDocumento}/destinos`
          );
          const listaDestinos = (destRes.data.data ?? []).map((d) => d.servicio ?? '—');

          const tramiteActual = docData.tramiteActual as { procedencia?: { descripcion?: string } } | null | undefined;

          setNominaData({
            idDocumento,
            numDocumento:   (docData.numInterno as number | null) ?? (docData.numDocumento as number | null),
            materia:        docData.materia as string | null,
            tipoDocumento:  (docData.tipoDocumento as { descripcion?: string | null } | null)?.descripcion ?? null,
            fechaDocumento: docData.fechaDocumento as string | null,
            fechaIngreso:   docData.fechaIngreso as string | null,
            ingresadoPor:   (docData.ingresadoPor as { nombre?: string; usuario?: string } | null)?.nombre
                            || (docData.ingresadoPor as { nombre?: string; usuario?: string } | null)?.usuario
                            || 'Sistema',
            origen:         tramiteActual?.procedencia?.descripcion ?? user?.descDependencia ?? null,
            destinos:       listaDestinos,
            observaciones:  null,
          });
          setIdDocCreado(idDocumento);
          setNominaOpen(true);
          toast.success('Documento registrado y despachado. Nómina generada.');
        } catch {
          toast.success('Documento registrado y despachado correctamente');
          navigate(`/documentos/${idDocumento}`);
        }
      } else {
        toast.success('Documento registrado y despachado correctamente');
        navigate(`/documentos/${idDocumento}`);
      }
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al crear documento';
      toast.error(msg);
    },
  });

  const isPending    = isSubmitting || mutation.isPending;
  const origenNombre = user?.descDependencia ?? 'Sin servicio asignado';

  // ── Handler especial para Memorándum ──────────────────────
  // Se ejecuta en lugar de mutation.mutate cuando el tipo es Memorándum.
  // Valida campos extra, usa firmante seleccionado y abre el modal de previsualización.
  async function abrirPrevisualizacionMemo(data: FormData) {
    // Validar materia (campo propio, ya no se deriva de Referencia)
    if (!memoMateria.trim()) {
      setMemoMateriaErr('La materia del memorándum es requerida');
      return;
    }
    setMemoMateriaErr(null);

    // Validar cuerpo
    if (!memoCuerpo.trim()) {
      setMemoCuerpoErr('El cuerpo del memorándum es requerido');
      return;
    }
    setMemoCuerpoErr(null);

    // Validar servicio + destinatario específico — no aplica si el documento
    // es "reservado" (Oficina de Partes), donde el destino ya está forzado
    // a Dirección y no corresponde pedir una persona específica.
    let cargoFinalDestinatario: string | null = null;
    if (!reservado) {
      if (!memoServicioDestino) {
        setMemoDestinatarioErr('Selecciona el servicio destinatario');
        return;
      }
      if (!memoDestinatario) {
        setMemoDestinatarioErr('Selecciona el destinatario específico');
        return;
      }
      cargoFinalDestinatario = memoDestinatario.cargo ?? memoCargoManual.trim();
      if (!cargoFinalDestinatario) {
        setMemoDestinatarioErr('Ingresa el cargo del destinatario');
        return;
      }
      setMemoDestinatarioErr(null);
    }

    // Verificar firmante seleccionado
    if (!firmanteSeleccionado) {
      toast.error('Selecciona un firmante antes de continuar');
      return;
    }

    // El memorándum se firma exclusivamente con Firma Simple DOC360 — si el
    // cargo elegido no la tiene habilitada, se informa el motivo exacto en
    // vez de abrir un flujo alternativo.
    if (firmanteSeleccionado.estadoFirmaSimple !== 'disponible') {
      toast.error(firmanteSeleccionado.motivoNoHabilitada
        ?? 'Este cargo no tiene Firma Simple DOC360 configurada. Vincula un usuario en Administración → Jefaturas.');
      return;
    }

    // Construir payload para POST /documentos — memorándum siempre es un
    // único destinatario interno (o Dirección, si es reservado).
    const idDestinoUnico = reservado ? ID_DIRECCION : Number(memoServicioDestino);
    const base: MemoDocumentoPayload = {
      materia:            memoMateria.trim().substring(0, 250),
      idTipoDocumento:    Number(data.idTipoDocumento),
      fechaDocumento:     data.fechaDocumento,
      observaciones:      data.observaciones,
      idTipoDistribucion: Number(data.idTipoDistribucion),
      idTipoCompromiso:   Number(data.idTipoCompromiso),
      idEstadoCompromiso: Number(data.idEstadoCompromiso),
      diasCompromiso:     Number(data.diasCompromiso),
      tipoDestinatario:   'D',
      destinos:           [idDestinoUnico],
      ...(esOficinaPartes && { tipoSoporte, reservado }),
    };

    setMemoFirmante(firmanteSeleccionado);
    setMemoPayload(base);
    setMemoDestinatarioInfo(
      reservado || !memoDestinatario
        ? null
        : { nombre: memoDestinatario.nombre, cargo: cargoFinalDestinatario ?? '' }
    );
    setMemoFirmaSimpleOpen(true);
  }

  // Nombres de destinos para el PDF del memo — memorándum siempre es un único
  // servicio (o Dirección, si es reservado); ya no usa destinosSeleccionados.
  const destinosNombresParaMemo = useMemo(() => {
    const idDest = reservado ? ID_DIRECCION : Number(memoServicioDestino);
    if (!idDest) return [];
    const nombre = (dependencias ?? []).find((d) => d.id === idDest)?.descripcion ?? `Servicio #${idDest}`;
    return [nombre];
  }, [reservado, memoServicioDestino, dependencias]);

  return (
    <>
      <div className="space-y-6 max-w-4xl mx-auto animate-fade-in">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate('/documentos')} className="shrink-0">
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <span className="icon-3d icon-3d-indigo hidden sm:flex h-11 w-11 shrink-0 items-center justify-center">
            <FileText className="h-5 w-5 text-white" />
          </span>
          <div>
            <h1 className="text-2xl font-bold">Ingreso de Documento</h1>
            <p className="text-sm text-muted-foreground">Identificación del documento y trámite</p>
          </div>
        </div>

        <form
          onSubmit={handleSubmit((d) =>
            esMemorandum ? abrirPrevisualizacionMemo(d) : mutation.mutate(d)
          )}
          noValidate
        >
          <div className="space-y-5">

            {/* SECCIÓN 0: SOPORTE Y CONDICIÓN (solo Oficina de Partes) */}
            {esOficinaPartes && (
              <Card className="border-primary/30 bg-primary/[0.02]">
                <CardHeader className="pb-3">
                  <CardTitle className="text-base flex items-center gap-2">
                    <span className="icon-3d-sm icon-3d-indigo flex h-7 w-7 shrink-0 items-center justify-center">
                      <FileStack className="h-3.5 w-3.5 text-white" />
                    </span>
                    Soporte y Condición
                    <span className="ml-auto flex items-center gap-1 text-xs font-normal text-primary">
                      <ShieldAlert className="h-3 w-3" />Oficina de Partes
                    </span>
                  </CardTitle>
                  <CardDescription>Opciones exclusivas para documentos de Oficina de Partes</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  {/* Tipo de soporte */}
                  <div className="space-y-2">
                    <Label className="text-sm font-medium">Tipo de soporte</Label>
                    <div className="flex gap-4 flex-wrap">
                      <label className={cn(
                        'flex items-center gap-2.5 cursor-pointer text-sm px-4 py-2.5 rounded-lg border-2 transition-all',
                        tipoSoporte === 'D'
                          ? 'border-primary bg-primary/5 text-primary font-medium'
                          : 'border-border text-muted-foreground hover:border-primary/40'
                      )}>
                        <input
                          type="radio"
                          name="tipoSoporte"
                          value="D"
                          checked={tipoSoporte === 'D'}
                          onChange={() => setTipoSoporte('D')}
                          className="h-4 w-4"
                        />
                        <FileText className="h-4 w-4" aria-hidden="true" />
                        Digital
                      </label>
                      <label className={cn(
                        'flex items-center gap-2.5 cursor-pointer text-sm px-4 py-2.5 rounded-lg border-2 transition-all',
                        tipoSoporte === 'F'
                          ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/10 text-amber-700 dark:text-amber-400 font-medium'
                          : 'border-border text-muted-foreground hover:border-amber-400/50'
                      )}>
                        <input
                          type="radio"
                          name="tipoSoporte"
                          value="F"
                          checked={tipoSoporte === 'F'}
                          onChange={() => setTipoSoporte('F')}
                          className="h-4 w-4"
                        />
                        <Paperclip className="h-4 w-4" aria-hidden="true" />
                        Físico / Papel
                      </label>
                    </div>
                    {tipoSoporte === 'F' && (
                      <p className="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5 mt-1">
                        <AlertCircle className="h-3 w-3 shrink-0" />
                        Al registrar, se generará automáticamente una nómina PDF para imprimir.
                      </p>
                    )}
                  </div>

                  {/* Documento reservado */}
                  <div className="space-y-2">
                    <label className={cn(
                      'flex items-start gap-3 cursor-pointer p-3 rounded-lg border-2 transition-all',
                      reservado
                        ? 'border-rose-400 bg-rose-50 dark:bg-rose-900/10'
                        : 'border-border hover:border-rose-300/60'
                    )}>
                      <input
                        type="checkbox"
                        checked={reservado}
                        onChange={(e) => setReservado(e.target.checked)}
                        className="mt-0.5 h-4 w-4 shrink-0"
                      />
                      <div>
                        <p className={cn(
                          'text-sm font-medium',
                          reservado ? 'text-rose-700 dark:text-rose-400' : 'text-foreground'
                        )}>
                          Documento Reservado
                        </p>
                        <p className="text-xs text-muted-foreground mt-0.5">
                          El destino se asignará automáticamente a <strong>Dirección</strong>.
                          Solo Dirección podrá recepcionar y cerrar este documento.
                        </p>
                      </div>
                    </label>
                  </div>
                </CardContent>
              </Card>
            )}

            {/* SECCIÓN 1: IDENTIFICACIÓN */}
            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-base flex items-center gap-2">
                  <span className="icon-3d-sm icon-3d-indigo flex h-7 w-7 shrink-0 items-center justify-center">
                    <FileText className="h-3.5 w-3.5 text-white" />
                  </span>
                  Identificación del Documento
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-1.5">
                    <Label className="flex items-center gap-1.5 text-sm">
                      <Tag className="h-3.5 w-3.5 text-muted-foreground" />
                      Tipo de Documento <span className="text-destructive">*</span>
                    </Label>
                    <select {...register('idTipoDocumento')} className={sel(!!errors.idTipoDocumento)}>
                      <option value="">Seleccionar tipo...</option>
                      {(tipos ?? []).map((t) => <option key={t.id} value={t.id}>{t.descripcion}</option>)}
                    </select>
                    {errors.idTipoDocumento && <p className="text-xs text-destructive">{errors.idTipoDocumento.message}</p>}
                  </div>

                  <div className="space-y-1.5">
                    <Label className="flex items-center gap-1.5 text-sm text-muted-foreground">
                      <Lock className="h-3.5 w-3.5" />
                      Estado inicial
                    </Label>
                    <div className={cn(sel(), 'bg-muted/40 text-muted-foreground cursor-not-allowed flex items-center gap-2')}>
                      <span className="h-2 w-2 rounded-full bg-amber-500 shrink-0" />
                      Despachado (automático)
                    </div>
                  </div>

                  <div className="space-y-1.5">
                    <Label className="flex items-center gap-1.5 text-sm">
                      <Calendar className="h-3.5 w-3.5 text-muted-foreground" />
                      Fecha del Documento
                    </Label>
                    <Input type="date" {...register('fechaDocumento')} />
                  </div>
                </div>

                {/* Materia / Asunto — al final del bloque; oculta para Memorándum (se auto-genera desde la Referencia) */}
                {!esMemorandum && (
                  <div className="space-y-1.5">
                    <Label className="flex items-center gap-1.5">
                      <MessageSquare className="h-3.5 w-3.5 text-muted-foreground" />
                      Materia / Asunto <span className="text-destructive">*</span>
                    </Label>
                    <textarea
                      rows={3}
                      {...register('materia')}
                      placeholder="Descripción del documento..."
                      className={cn(
                        'flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm',
                        'placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none',
                        errors.materia && 'border-destructive'
                      )}
                    />
                    <div className="flex justify-between">
                      {errors.materia
                        ? <p className="text-xs text-destructive flex items-center gap-1"><AlertCircle className="h-3 w-3" />{errors.materia.message}</p>
                        : <span />}
                      <span className={cn('text-xs', (materia?.length ?? 0) > 230 ? 'text-amber-500' : 'text-muted-foreground')}>
                        {materia?.length ?? 0}/250
                      </span>
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* SECCIÓN 2: ORIGEN AUTOMÁTICO */}
            <Card className="border-emerald-200 dark:border-emerald-800/40">
              <CardHeader className="pb-3">
                <CardTitle className="text-base flex items-center gap-2">
                  <span className="icon-3d-sm icon-3d-emerald flex h-7 w-7 shrink-0 items-center justify-center">
                    <ChevronRight className="h-3.5 w-3.5 text-white" />
                  </span>
                  Origen del Trámite
                  <span className="ml-auto flex items-center gap-1 text-xs font-normal text-muted-foreground">
                    <Lock className="h-3 w-3" />Automático
                  </span>
                </CardTitle>
                <CardDescription>Asignado desde tu servicio. No es editable.</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="flex items-center gap-3 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800/30">
                  <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                    <Building2 className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                  </div>
                  <div>
                    <p className="text-sm font-medium text-foreground">{origenNombre}</p>
                    <p className="text-xs text-muted-foreground">Dependencia / Servicio del usuario creador</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* SECCIÓN 2b: FIRMANTE — solo para Memorándum */}
            {esMemorandum && (
              <Card className="border-blue-200/60 bg-blue-50/30 dark:bg-blue-900/10">
                <CardHeader className="pb-3">
                  <CardTitle className="text-base flex items-center gap-2">
                    <span className="icon-3d-sm icon-3d-sky flex h-7 w-7 shrink-0 items-center justify-center">
                      <Send className="h-3.5 w-3.5 text-white" />
                    </span>
                    Firmante del Memorándum
                  </CardTitle>
                  <CardDescription>Selecciona quién firmará el memorándum con Firma Simple DOC360</CardDescription>
                </CardHeader>
                <CardContent>
                  {cargandoFirmantes ? (
                    <div className="flex items-center gap-2 text-sm text-muted-foreground py-2">
                      <Loader2 className="h-4 w-4 animate-spin" />
                      Cargando firmantes disponibles…
                    </div>
                  ) : !firmantesDisponibles || firmantesDisponibles.length === 0 ? (
                    <div className="flex items-start gap-2 text-sm text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 rounded-lg px-3 py-2.5">
                      <AlertCircle className="h-4 w-4 shrink-0 mt-0.5" />
                      <span>No hay firmantes configurados para tu servicio. Configúralos en Administración → Jefaturas.</span>
                    </div>
                  ) : (
                    <div className="flex flex-col gap-2">
                      {firmantesDisponibles.map((f) => {
                        const seleccionado = firmanteSeleccionado?.tipo === f.tipo;
                        return (
                          <label
                            key={f.tipo}
                            className={cn(
                              'flex items-start gap-3 cursor-pointer rounded-lg border-2 px-4 py-3 transition-all',
                              seleccionado
                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                : 'border-border hover:border-blue-300',
                              !f.vigente && 'opacity-60'
                            )}
                          >
                            <input
                              type="radio"
                              name="firmante"
                              checked={seleccionado}
                              onChange={() => setFirmanteSeleccionado(f)}
                              className="mt-0.5 h-4 w-4"
                              disabled={isPending}
                            />
                            <div className="flex-1 min-w-0">
                              <div className="flex items-center gap-2 flex-wrap">
                                <span className="text-sm font-medium">{f.nombre}</span>
                                <span className="text-xs text-muted-foreground">{f.cargo}</span>
                                {f.tipo !== 'TITULAR' && (
                                  <span className="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">
                                    {f.tipo === 'SUBROGANTE' ? 'Subrogante' : '2° Subrogante'}
                                  </span>
                                )}
                                {!f.vigente && (
                                  <span className="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Fuera de vigencia</span>
                                )}
                              </div>
                              {f.estadoFirmaSimple === 'disponible'
                                ? <p className="text-xs text-emerald-600 mt-0.5 flex items-center gap-1"><KeyRound className="h-3 w-3" />Firma Simple DOC360 habilitada</p>
                                : <p className="text-xs text-orange-500 mt-0.5">{f.motivoNoHabilitada ?? 'Firma Simple no configurada para este cargo'} — configúralo en Administración → Jefaturas</p>
                              }
                            </div>
                          </label>
                        );
                      })}
                    </div>
                  )}
                </CardContent>
              </Card>
            )}

            {/* SECCIÓN 3: DESTINO */}
            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-base flex items-center gap-2">
                  <span className="icon-3d-sm icon-3d-violet flex h-7 w-7 shrink-0 items-center justify-center">
                    <ChevronRight className="h-3.5 w-3.5 text-white" />
                  </span>
                  {esMemorandum ? 'Destinatario del Memorándum' : 'Destino del Trámite'}
                </CardTitle>
                <CardDescription>
                  {reservado
                    ? 'Destino reservado: asignado automáticamente a Dirección'
                    : esMemorandum
                      ? 'Selecciona el servicio y luego la persona destinataria específica'
                      : tipoDestinatario === 'D'
                        ? 'Selecciona uno o varios servicios internos destinatarios'
                        : 'Selecciona la entidad externa destinataria'}
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">

                {/* Banner informativo cuando es reservado */}
                {reservado ? (
                  <div className="flex items-center gap-3 p-3 rounded-lg bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800/30">
                    <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-100 dark:bg-rose-900/30">
                      <Lock className="h-4 w-4 text-rose-600 dark:text-rose-400" />
                    </div>
                    <div>
                      <p className="text-sm font-medium text-foreground">Destino bloqueado: <strong>{nombreDireccion}</strong></p>
                      <p className="text-xs text-muted-foreground">Documento reservado — solo Dirección puede recepcionarlo</p>
                    </div>
                  </div>
                ) : esMemorandum ? (
                  <>
                    {/* Servicio destinatario — select simple, un solo servicio para memorándum */}
                    <div className="space-y-1.5">
                      <Label className="flex items-center gap-1.5 text-sm">
                        <Building2 className="h-3.5 w-3.5 text-muted-foreground" />
                        Servicio / Unidad destinataria <span className="text-destructive">*</span>
                      </Label>
                      <select
                        value={memoServicioDestino}
                        onChange={(e) => setMemoServicioDestino(e.target.value)}
                        className={sel(!!memoDestinatarioErr && !memoServicioDestino)}
                      >
                        <option value="">Seleccionar servicio...</option>
                        {(dependencias ?? []).map((d) => <option key={d.id} value={d.id}>{d.descripcion}</option>)}
                      </select>
                    </div>

                    {/* Destinatario específico dentro del servicio elegido */}
                    {memoServicioDestino && (
                      <div className="space-y-2">
                        <Label className="flex items-center gap-1.5 text-sm">
                          <Search className="h-3.5 w-3.5 text-muted-foreground" />
                          Destinatario <span className="text-destructive">*</span>
                        </Label>

                        {memoDestinatario ? (
                          <div className="flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg bg-primary/5 border border-primary/30 text-sm">
                            <div className="min-w-0">
                              <p className="font-medium truncate">{memoDestinatario.nombre}</p>
                              <p className="text-xs text-muted-foreground truncate">
                                {(dependencias ?? []).find((d) => d.id === Number(memoServicioDestino))?.descripcion}
                              </p>
                            </div>
                            <button
                              type="button"
                              onClick={() => { setMemoDestinatario(null); setMemoCargoManual(''); }}
                              className="text-muted-foreground hover:text-foreground transition-colors shrink-0"
                            >
                              <X className="h-3.5 w-3.5" />
                            </button>
                          </div>
                        ) : (
                          <>
                            <Input
                              className="h-8 text-sm"
                              placeholder="Buscar por nombre..."
                              value={memoBuscarDestinatario}
                              onChange={(e) => setMemoBuscarDestinatario(e.target.value)}
                            />
                            <div className={cn(
                              'rounded-md border border-input overflow-y-auto max-h-48',
                              memoDestinatarioErr && 'border-destructive'
                            )}>
                              {cargandoFuncionariosDestino ? (
                                <p className="px-3 py-4 text-sm text-muted-foreground text-center">Cargando...</p>
                              ) : funcionariosDestinoFiltrados.length === 0 ? (
                                <p className="px-3 py-4 text-sm text-muted-foreground text-center">Sin resultados en este servicio</p>
                              ) : (
                                funcionariosDestinoFiltrados.map((f) => (
                                  <button
                                    key={f.id}
                                    type="button"
                                    onClick={() => { setMemoDestinatario(f); setMemoCargoManual(f.cargo ?? ''); setMemoDestinatarioErr(''); }}
                                    className="w-full px-3 py-2 text-left text-sm hover:bg-muted/50 transition-colors border-b border-border/40 last:border-0"
                                  >
                                    <p className="font-medium">{f.nombre}</p>
                                    {f.cargo && <p className="text-xs text-emerald-600 mt-0.5">{f.cargo}</p>}
                                  </button>
                                ))
                              )}
                            </div>
                          </>
                        )}
                        {memoDestinatarioErr && <p className="text-xs text-destructive flex items-center gap-1"><AlertCircle className="h-3 w-3" />{memoDestinatarioErr}</p>}

                        {/* Cargo — autocompletado desde jefatura si corresponde, o manual */}
                        {memoDestinatario && (
                          <div className="space-y-1.5">
                            <Label className="text-sm">
                              Cargo del destinatario {!memoDestinatario.cargo && <span className="text-destructive">*</span>}
                            </Label>
                            <Input
                              value={memoCargoManual}
                              onChange={(e) => setMemoCargoManual(e.target.value)}
                              placeholder="Ej: Jefe/a de Servicio"
                              disabled={!!memoDestinatario.cargo}
                              maxLength={100}
                            />
                            {memoDestinatario.cargo
                              ? <p className="text-xs text-emerald-600">Autocompletado desde Jefaturas</p>
                              : <p className="text-xs text-amber-600">Sin cargo registrado para esta persona — ingrésalo manualmente</p>
                            }
                          </div>
                        )}
                      </div>
                    )}
                  </>
                ) : (
                  <>
                    {/* Tipo de destinatario */}
                    <div className="space-y-2">
                      <Label className="text-sm font-medium">Tipo de destinatario</Label>
                      <div className="flex gap-6">
                        <label className="flex items-center gap-2 cursor-pointer text-sm">
                          <input
                            type="radio" {...register('tipoDestinatario')} value="D" className="h-4 w-4"
                            onChange={() => { setDestinosSeleccionados([]); setDestinoError(''); setSearchDep(''); }}
                          />
                          Interno (Dependencia)
                        </label>
                        {puedeExterno && (
                          <label className="flex items-center gap-2 cursor-pointer text-sm">
                            <input
                              type="radio" {...register('tipoDestinatario')} value="E" className="h-4 w-4"
                              onChange={() => { setDestinosSeleccionados([]); setDestinoError(''); setDestinoExterno(''); }}
                            />
                            Externo
                          </label>
                        )}
                      </div>
                    </div>

                    {/* Multi-select para destinos internos */}
                    {tipoDestinatario === 'D' && (
                      <div className="space-y-2">
                        <div className="flex items-center justify-between">
                          <Label className="flex items-center gap-1.5 text-sm">
                            <Building2 className="h-3.5 w-3.5 text-muted-foreground" />
                            Servicios destino <span className="text-destructive">*</span>
                          </Label>
                          {destinosSeleccionados.length > 0 && (
                            <span className="text-xs text-primary font-medium">
                              {destinosSeleccionados.length} seleccionado(s)
                            </span>
                          )}
                        </div>
                        <div className="relative">
                          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                          <Input
                            className="pl-8 h-8 text-sm"
                            placeholder="Buscar servicio..."
                            value={searchDep}
                            onChange={(e) => setSearchDep(e.target.value)}
                          />
                        </div>
                        <div className={cn(
                          'rounded-md border border-input overflow-y-auto max-h-48',
                          destinoError && 'border-destructive'
                        )}>
                          {depsFiltradas.length === 0
                            ? <p className="px-3 py-4 text-sm text-muted-foreground text-center">Sin resultados</p>
                            : depsFiltradas.map((d) => {
                              const checked = destinosSeleccionados.includes(d.id);
                              return (
                                <button
                                  key={d.id}
                                  type="button"
                                  onClick={() => toggleDestino(d.id)}
                                  className={cn(
                                    'w-full flex items-center gap-3 px-3 py-2 text-sm text-left transition-colors hover:bg-muted/50',
                                    checked && 'bg-primary/5'
                                  )}
                                >
                                  {checked
                                    ? <CheckSquare className="h-4 w-4 text-primary shrink-0" />
                                    : <Square className="h-4 w-4 text-muted-foreground shrink-0" />}
                                  <span className={checked ? 'font-medium text-foreground' : 'text-muted-foreground'}>
                                    {d.descripcion}
                                  </span>
                                </button>
                              );
                            })
                          }
                        </div>
                        {destinoError && <p className="text-xs text-destructive flex items-center gap-1"><AlertCircle className="h-3 w-3" />{destinoError}</p>}
                      </div>
                    )}

                    {/* Select único para destino externo */}
                    {tipoDestinatario === 'E' && (
                      <div className="space-y-1.5">
                        <Label className="flex items-center gap-1.5 text-sm">
                          <Building2 className="h-3.5 w-3.5 text-muted-foreground" />
                          Entidad externa <span className="text-destructive">*</span>
                        </Label>
                        <select
                          value={destinoExterno}
                          onChange={(e) => { setDestinoExterno(e.target.value); setDestinoError(''); }}
                          className={sel(!!destinoError)}
                        >
                          <option value="">Seleccionar entidad...</option>
                          {(depExternas ?? []).map((d) => <option key={d.id} value={d.id}>{d.descripcion}</option>)}
                        </select>
                        {destinoError && <p className="text-xs text-destructive">{destinoError}</p>}
                      </div>
                    )}
                  </>
                )}
              </CardContent>
            </Card>

            {/* SECCIÓN 3b: CONTENIDO MEMORÁNDUM — Materia/Referencia/Cuerpo, después de firmante y destinatario */}
            {esMemorandum && (
              <MemorandumFields
                materia={memoMateria}
                materiaError={memoMateriaErr}
                referencia={memoReferencia}
                cuerpo={memoCuerpo}
                cuerpoError={memoCuerpoErr}
                onMateria={(v) => { setMemoMateria(v); if (v.trim()) setMemoMateriaErr(null); }}
                onReferencia={setMemoReferencia}
                onCuerpo={(v) => { setMemoCuerpo(v); if (v.trim()) setMemoCuerpoErr(null); }}
                disabled={isPending}
              />
            )}

            {/* SECCIÓN 4: DISTRIBUCIÓN Y COMPROMISO — no aplica a Memorándum (usa los
                valores por defecto del formulario: distribución "Para conocimiento",
                sin compromiso). Los campos siguen registrados en react-hook-form aunque
                la card esté oculta, así que abrirPrevisualizacionMemo() los sigue leyendo
                normalmente. */}
            {!esMemorandum && (
              <Card>
                <CardHeader className="pb-3">
                  <CardTitle className="text-base flex items-center gap-2">
                    <span className="icon-3d-sm icon-3d-amber flex h-7 w-7 shrink-0 items-center justify-center">
                      <Tag className="h-3.5 w-3.5 text-white" />
                    </span>
                    Distribución y Compromiso
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div className="space-y-1.5">
                      <Label className="text-sm">Tipo de distribución</Label>
                      <select {...register('idTipoDistribucion')} className={sel()}>
                        {(tiposDist ?? []).map((d) => <option key={d.id} value={d.id}>{d.descripcion}</option>)}
                      </select>
                    </div>
                    <div className="space-y-1.5">
                      <Label className="text-sm">Tipo de compromiso</Label>
                      <select {...register('idTipoCompromiso')} className={sel()}>
                        {(tiposCom ?? []).map((c) => <option key={c.id} value={c.id}>{c.descripcion}</option>)}
                      </select>
                    </div>
                    {idTipoCompromiso !== '1' && (
                      <>
                        <div className="space-y-1.5">
                          <Label className="text-sm">Estado compromiso</Label>
                          <select {...register('idEstadoCompromiso')} className={sel()}>
                            {(estadosCom ?? []).map((e) => <option key={e.id} value={e.id}>{e.descripcion}</option>)}
                          </select>
                        </div>
                        <div className="space-y-1.5">
                          <Label className="text-sm">Días compromiso</Label>
                          <Input type="number" min="0" {...register('diasCompromiso')} />
                        </div>
                      </>
                    )}
                  </div>
                </CardContent>
              </Card>
            )}

            {/* SECCIÓN 5: ARCHIVOS ADJUNTOS */}
            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-base flex items-center gap-2">
                  <span className="icon-3d-sm icon-3d-slate flex h-7 w-7 shrink-0 items-center justify-center">
                    <Paperclip className="h-3.5 w-3.5 text-white" />
                  </span>
                  Archivos Adjuntos
                  <span className="text-xs text-muted-foreground font-normal">
                    {tipoSoporte === 'F' ? '(no requeridos para documentos físicos)' : '(opcionales)'}
                  </span>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                {/* Zona de arrastrar / clic — siempre visible para agregar más */}
                <div
                  onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                  onDragLeave={() => setDragOver(false)}
                  onDrop={(e) => { e.preventDefault(); setDragOver(false); agregarArchivos(e.dataTransfer.files); }}
                  onClick={() => document.getElementById('file-input')?.click()}
                  className={cn(
                    'flex flex-col items-center gap-3 p-6 rounded-xl border-2 border-dashed cursor-pointer transition-all',
                    dragOver ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'
                  )}
                >
                  <span className={cn('icon-3d flex h-12 w-12 items-center justify-center transition-transform', dragOver ? 'icon-3d-indigo scale-105' : 'icon-3d-slate')}>
                    <Paperclip className="h-5 w-5 text-white" />
                  </span>
                  <div className="text-center">
                    <p className="text-sm font-medium">
                      {dragOver ? 'Suelta los archivos' : 'Arrastra o haz clic para seleccionar'}
                    </p>
                    <p className="text-xs text-muted-foreground mt-1">
                      PDF, DOC, DOCX, XLS, PNG, JPG, imágenes — máx. {MAX_FILE_MB} MB por archivo · cuota total {CUOTA_TOTAL_MB} MB
                    </p>
                  </div>
                  <input
                    id="file-input"
                    type="file"
                    multiple
                    className="hidden"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp,.txt"
                    onChange={(e) => {
                      if (e.target.files) { agregarArchivos(e.target.files); e.target.value = ''; }
                    }}
                  />
                </div>

                {/* Lista de archivos seleccionados */}
                {archivos.length > 0 && (
                  <div className="space-y-1.5">
                    {archivos.map((f, idx) => (
                      <div key={idx} className="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-muted/40">
                        <FileText className="h-4 w-4 shrink-0 text-muted-foreground" />
                        <div className="flex-1 min-w-0">
                          <p className="text-sm font-medium truncate">{f.name}</p>
                          <p className="text-xs text-muted-foreground">{formatSize(f.size)}</p>
                        </div>
                        <Button
                          type="button" variant="ghost" size="icon" className="h-7 w-7 shrink-0"
                          onClick={() => setArchivos((prev) => prev.filter((_, i) => i !== idx))}
                          aria-label={`Quitar ${f.name}`}
                        >
                          <X className="h-3.5 w-3.5" />
                        </Button>
                      </div>
                    ))}

                    {/* Resumen de peso total */}
                    <div className={cn(
                      'flex items-center justify-between px-3 py-2 rounded-lg text-xs',
                      excuotaTotal
                        ? 'bg-destructive/10 border border-destructive/20 text-destructive'
                        : 'bg-muted/30 text-muted-foreground'
                    )}>
                      <span>
                        {archivos.length} archivo{archivos.length !== 1 ? 's' : ''} seleccionado{archivos.length !== 1 ? 's' : ''}
                      </span>
                      <span className="font-medium">{formatSize(totalBytes)} / {CUOTA_TOTAL_MB} MB total</span>
                    </div>

                    {excuotaTotal && (
                      <p className="text-xs text-destructive flex items-center gap-1.5">
                        <AlertCircle className="h-3 w-3 shrink-0" />
                        La suma total de los archivos supera el máximo permitido ({CUOTA_TOTAL_MB} MB). Elimina archivos para continuar.
                      </p>
                    )}
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Acciones */}
            <div className="flex items-center justify-between pt-2">
              <Button type="button" variant="outline" onClick={() => navigate('/documentos')}>
                Cancelar
              </Button>
              <Button type="submit" disabled={isPending || excuotaTotal} className="btn-premium gap-2 px-8 border-0 text-primary-foreground">
                {isPending
                  ? <><Loader2 className="h-4 w-4 animate-spin" />Procesando...</>
                  : esMemorandum
                    ? <><Eye className="h-4 w-4" />Vista Previa del Memorándum</>
                    : <><Send className="h-4 w-4" />Registrar y Despachar</>
                }
              </Button>
            </div>
          </div>
        </form>
      </div>

      {/* Modal de nómina PDF (solo para documentos físicos) */}
      {nominaData && (
        <NominaModal
          open={nominaOpen}
          onClose={() => setNominaOpen(false)}
          data={nominaData}
          onNavigate={() => {
            if (idDocCreado) navigate(`/documentos/${idDocCreado}`);
          }}
        />
      )}

      {/* Modal de Firma Simple DOC360 — único mecanismo de firma para memorándum */}
      {memoFirmaSimpleOpen && memoPayload && memoFirmante && (
        <MemorandumFirmaSimpleModal
          open={memoFirmaSimpleOpen}
          onClose={() => setMemoFirmaSimpleOpen(false)}
          docPayload={memoPayload}
          referencia={memoReferencia}
          cuerpo={memoCuerpo}
          destinosNombres={destinosNombresParaMemo}
          destinatario={memoDestinatarioInfo}
          origenNombre={origenNombre}
          firmante={{
            tipo:           memoFirmante.tipo ?? '',
            idFirmante:     memoFirmante.idFirmante ?? 0,
            nombre:         memoFirmante.nombre ?? '',
            cargo:          memoFirmante.cargo ?? '',
            rut:            memoFirmante.rut ?? null,
            firmaTimbreUrl: memoFirmante.firmaTimbreUrl ?? null,
            dependencia:    memoFirmante.dependencia ?? null,
            idDependencia:  memoFirmante.idDependencia ?? 0,
          } satisfies FirmanteFirmaSimple}
          archivos={archivos}
          onNavigate={(idDoc) => {
            setMemoFirmaSimpleOpen(false);
            navigate(`/documentos/${idDoc}`);
          }}
        />
      )}
    </>
  );
}
