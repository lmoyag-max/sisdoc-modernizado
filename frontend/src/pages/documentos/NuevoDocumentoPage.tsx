import { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, useMutation } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  FileText, ArrowLeft, Send, Paperclip, X, AlertCircle,
  Building2, Tag, MessageSquare, Calendar, Loader2, ChevronRight, Lock,
  Search, CheckSquare, Square, FileStack, ShieldAlert,
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { useAuthStore } from '@/stores/auth.store';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';
import { NominaModal } from '@/components/documentos/NominaModal';
import { type NominaData } from '@/lib/utils/nomina.generator';

// ── Schema ───────────────────────────────────────────────────
const schema = z.object({
  materia:            z.string().min(5, 'Mínimo 5 caracteres').max(250),
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

export function NuevoDocumentoPage() {
  const navigate   = useNavigate();
  const user       = useAuthStore((s) => s.user);

  // ── Estado de campos generales ────────────────────────────
  const [archivo, setArchivo]     = useState<File | null>(null);
  const [dragOver, setDragOver]   = useState(false);
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

  const { register, handleSubmit, watch, formState: { errors, isSubmitting } } = useForm<FormData>({
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
  const tipoDestinatario = watch('tipoDestinatario');
  const idTipoCompromiso = watch('idTipoCompromiso');
  const materia          = watch('materia');

  const mutation = useMutation({
    mutationFn: async (data: FormData) => {
      // Validación de destino (no corre dentro de RHF porque es estado local)
      if (data.tipoDestinatario === 'D' && destinosSeleccionados.length === 0) {
        setDestinoError('Selecciona al menos un servicio destino');
        return null;
      }
      if (data.tipoDestinatario === 'E' && !destinoExterno) {
        setDestinoError('Selecciona un destino externo');
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

      if (archivo && idDocumento) {
        const form = new FormData();
        form.append('archivo', archivo);
        form.append('idDocumento', String(idDocumento));
        await apiClient.post('/archivos/upload', form, { headers: { 'Content-Type': 'multipart/form-data' } });
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

  const isPending = isSubmitting || mutation.isPending;
  const origenNombre = user?.descDependencia ?? 'Sin servicio asignado';

  return (
    <>
      <div className="space-y-6 max-w-4xl mx-auto animate-fade-in">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate('/documentos')} className="shrink-0">
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold">Ingreso de Documento</h1>
            <p className="text-sm text-muted-foreground">Identificación del documento y trámite</p>
          </div>
        </div>

        <form onSubmit={handleSubmit((d) => mutation.mutate(d))} noValidate>
          <div className="space-y-5">

            {/* SECCIÓN 0: SOPORTE Y CONDICIÓN (solo Oficina de Partes) */}
            {esOficinaPartes && (
              <Card className="border-primary/30 bg-primary/[0.02]">
                <CardHeader className="pb-3">
                  <CardTitle className="text-base flex items-center gap-2">
                    <FileStack className="h-4 w-4 text-primary" />
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
                  <FileText className="h-4 w-4 text-primary" />
                  Identificación del Documento
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
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

                  <div className="space-y-1.5 sm:col-span-2">
                    <Label className="text-sm">Observaciones</Label>
                    <Input {...register('observaciones')} placeholder="Notas adicionales sobre el documento..." />
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* SECCIÓN 2: ORIGEN AUTOMÁTICO */}
            <Card className="border-emerald-200 dark:border-emerald-800/40">
              <CardHeader className="pb-3">
                <CardTitle className="text-base flex items-center gap-2">
                  <ChevronRight className="h-4 w-4 text-emerald-500" />
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

            {/* SECCIÓN 3: DESTINO */}
            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-base flex items-center gap-2">
                  <ChevronRight className="h-4 w-4 text-primary" />
                  Destino del Trámite
                </CardTitle>
                <CardDescription>
                  {reservado
                    ? 'Destino reservado: asignado automáticamente a Dirección'
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

            {/* SECCIÓN 4: DISTRIBUCIÓN Y COMPROMISO */}
            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-base flex items-center gap-2">
                  <Tag className="h-4 w-4 text-primary" />
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

            {/* SECCIÓN 5: ARCHIVO */}
            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-base flex items-center gap-2">
                  <Paperclip className="h-4 w-4 text-primary" />
                  Archivo Adjunto
                  <span className="text-xs text-muted-foreground font-normal">
                    {tipoSoporte === 'F' ? '(no requerido para documentos físicos)' : '(opcional)'}
                  </span>
                </CardTitle>
              </CardHeader>
              <CardContent>
                {archivo ? (
                  <div className="flex items-center gap-3 p-3 rounded-lg bg-primary/5 border border-primary/20">
                    <FileText className="h-8 w-8 text-primary shrink-0" />
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium truncate">{archivo.name}</p>
                      <p className="text-xs text-muted-foreground">{(archivo.size / 1024).toFixed(1)} KB</p>
                    </div>
                    <Button type="button" variant="ghost" size="icon" onClick={() => setArchivo(null)}>
                      <X className="h-4 w-4" />
                    </Button>
                  </div>
                ) : (
                  <div
                    onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                    onDragLeave={() => setDragOver(false)}
                    onDrop={(e) => { e.preventDefault(); setDragOver(false); const f = e.dataTransfer.files[0]; if (f) setArchivo(f); }}
                    onClick={() => document.getElementById('file-input')?.click()}
                    className={cn(
                      'flex flex-col items-center gap-3 p-8 rounded-xl border-2 border-dashed cursor-pointer transition-all',
                      dragOver ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/50 hover:bg-muted/30'
                    )}
                  >
                    <Paperclip className={cn('h-8 w-8', dragOver ? 'text-primary' : 'text-muted-foreground')} />
                    <div className="text-center">
                      <p className="text-sm font-medium">{dragOver ? 'Suelta el archivo' : 'Arrastra o haz clic para subir'}</p>
                      <p className="text-xs text-muted-foreground mt-1">PDF, DOC, DOCX, XLS, PNG, JPG — máx. 20 MB</p>
                    </div>
                    <input id="file-input" type="file" className="hidden"
                      accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                      onChange={(e) => { const f = e.target.files?.[0]; if (f) setArchivo(f); }} />
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Acciones */}
            <div className="flex items-center justify-between pt-2">
              <Button type="button" variant="outline" onClick={() => navigate('/documentos')}>
                Cancelar
              </Button>
              <Button type="submit" disabled={isPending} className="gap-2 px-8">
                {isPending
                  ? <><Loader2 className="h-4 w-4 animate-spin" />Guardando...</>
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
    </>
  );
}
