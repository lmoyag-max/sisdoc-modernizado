import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, useMutation } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  FileText, ArrowLeft, Send, Paperclip, X, AlertCircle,
  Building2, Tag, MessageSquare, Calendar, Loader2, ChevronRight,
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';

// ── Schema ───────────────────────────────────────────────────
const schema = z.object({
  materia:            z.string().min(5, 'Mínimo 5 caracteres').max(250),
  idTipoDocumento:    z.string().min(1, 'Selecciona el tipo de documento'),
  idEstadoDocumento:  z.string().default('1'),
  fechaDocumento:     z.string().optional(),
  observaciones:      z.string().max(500).optional(),
  tipoProcedencia:    z.enum(['D', 'E']).default('D'),
  idProcedencia:      z.string().min(1, 'Selecciona la procedencia'),
  tipoDestinatario:   z.enum(['D', 'E']).default('D'),
  idDestino:          z.string().min(1, 'Selecciona el destino'),
  idTipoDistribucion: z.string().default('5'),
  idTipoCompromiso:   z.string().default('1'),
  idEstadoCompromiso: z.string().default('2'),
  diasCompromiso:     z.string().default('0'),
  idExpediente:       z.string().optional(),
  despacharAhora:     z.boolean().default(false),
});
type FormData = z.infer<typeof schema>;

interface CatItem { id: number; descripcion: string }

const sel = (hasErr?: boolean) => cn(
  'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
  hasErr && 'border-destructive'
);

export function NuevoDocumentoPage() {
  const navigate = useNavigate();
  const [archivo, setArchivo] = useState<File | null>(null);
  const [dragOver, setDragOver] = useState(false);

  // Catálogos
  const { data: tipos }        = useQuery({ queryKey: ['tipos-doc'],    queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/tipos-documento')).data.data });
  const { data: estadosDoc }   = useQuery({ queryKey: ['estados-doc'],  queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/estados')).data.data });
  const { data: dependencias } = useQuery({ queryKey: ['dependencias'], queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/dependencias')).data.data });
  const { data: depExternas }  = useQuery({ queryKey: ['dep-externas'],queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/dependencias-externas')).data.data });
  const { data: tiposDist }    = useQuery({ queryKey: ['tipos-dist'],   queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/tipos-distribucion')).data.data });
  const { data: tiposCom }     = useQuery({ queryKey: ['tipos-comp'],   queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/tipos-compromiso')).data.data });
  const { data: estadosCom }   = useQuery({ queryKey: ['estados-comp'], queryFn: async () => (await apiClient.get<{ data: CatItem[] }>('/catalogos/estados-compromiso')).data.data });

  const { register, handleSubmit, watch, formState: { errors, isSubmitting } } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: {
      idEstadoDocumento: '1',
      fechaDocumento: new Date().toISOString().split('T')[0],
      tipoProcedencia: 'D', tipoDestinatario: 'D',
      idTipoDistribucion: '5', idTipoCompromiso: '1',
      idEstadoCompromiso: '2', diasCompromiso: '0',
      despacharAhora: false,
    },
  });

  const tipoProcedencia  = watch('tipoProcedencia');
  const tipoDestinatario = watch('tipoDestinatario');
  const idTipoCompromiso = watch('idTipoCompromiso');
  const despacharAhora   = watch('despacharAhora');
  const materia          = watch('materia');

  const procOptions  = tipoProcedencia  === 'D' ? (dependencias ?? []) : (depExternas ?? []);
  const destOptions  = tipoDestinatario === 'D' ? (dependencias ?? []) : (depExternas ?? []);

  const mutation = useMutation({
    mutationFn: async (data: FormData) => {
      const payload = {
        materia:            data.materia,
        idTipoDocumento:    Number(data.idTipoDocumento),
        idEstadoDocumento:  Number(data.idEstadoDocumento),
        fechaDocumento:     data.fechaDocumento,
        observaciones:      data.observaciones,
        tipoProcedencia:    data.tipoProcedencia,
        idProcedencia:      Number(data.idProcedencia),
        tipoDestinatario:   data.tipoDestinatario,
        idDestino:          Number(data.idDestino),
        idTipoDistribucion: Number(data.idTipoDistribucion),
        idTipoCompromiso:   Number(data.idTipoCompromiso),
        idEstadoCompromiso: Number(data.idEstadoCompromiso),
        diasCompromiso:     Number(data.diasCompromiso),
        despacharAhora:     data.despacharAhora,
        idExpediente:       data.idExpediente ? Number(data.idExpediente) : undefined,
      };
      const res = await apiClient.post<{ ok: boolean; data: { idDocumento: number } }>('/documentos', payload);
      const idDocumento = res.data.data?.idDocumento;
      if (archivo && idDocumento) {
        const form = new FormData();
        form.append('archivo', archivo);
        form.append('idDocumento', String(idDocumento));
        await apiClient.post('/archivos/upload', form, { headers: { 'Content-Type': 'multipart/form-data' } });
      }
      return res.data.data;
    },
    onSuccess: (data) => {
      toast.success('Documento registrado correctamente');
      navigate(`/documentos/${data?.idDocumento}`);
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al crear documento';
      toast.error(msg);
    },
  });

  const isPending = isSubmitting || mutation.isPending;

  return (
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

          {/* SECCIÓN 1: IDENTIFICACIÓN */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base flex items-center gap-2">
                <FileText className="h-4 w-4 text-primary" />
                Identificación del Documento
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {/* Materia */}
              <div className="space-y-1.5">
                <Label className="flex items-center gap-1.5">
                  <MessageSquare className="h-3.5 w-3.5 text-muted-foreground" />
                  Materia / Asunto <span className="text-destructive">*</span>
                </Label>
                <textarea rows={3} {...register('materia')} placeholder="Descripción del documento..."
                  className={cn('flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none', errors.materia && 'border-destructive')} />
                <div className="flex justify-between">
                  {errors.materia
                    ? <p className="text-xs text-destructive flex items-center gap-1"><AlertCircle className="h-3 w-3" />{errors.materia.message}</p>
                    : <span />}
                  <span className={cn('text-xs', (materia?.length ?? 0) > 230 ? 'text-amber-500' : 'text-muted-foreground')}>{materia?.length ?? 0}/250</span>
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {/* Tipo */}
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

                {/* Estado */}
                <div className="space-y-1.5">
                  <Label className="text-sm">Estado inicial</Label>
                  <select {...register('idEstadoDocumento')} className={sel()}>
                    {(estadosDoc ?? [{ id: 1, descripcion: 'Registrado' }]).map((e) =>
                      <option key={e.id} value={e.id}>{e.descripcion}</option>)}
                  </select>
                </div>

                {/* Fecha */}
                <div className="space-y-1.5">
                  <Label className="flex items-center gap-1.5 text-sm">
                    <Calendar className="h-3.5 w-3.5 text-muted-foreground" />
                    Fecha del Documento
                  </Label>
                  <Input type="date" {...register('fechaDocumento')} />
                </div>

                {/* Expediente */}
                <div className="space-y-1.5">
                  <Label className="text-sm">N° Expediente (opcional)</Label>
                  <Input type="number" {...register('idExpediente')} placeholder="Dejar vacío si no aplica" />
                </div>

                {/* Observaciones */}
                <div className="space-y-1.5 sm:col-span-2">
                  <Label className="text-sm">Observaciones</Label>
                  <Input {...register('observaciones')} placeholder="Notas adicionales sobre el documento..." />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* SECCIÓN 2: TRÁMITE ORIGEN */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base flex items-center gap-2">
                <ChevronRight className="h-4 w-4 text-emerald-500" />
                Origen del Trámite
              </CardTitle>
              <CardDescription>¿De dónde proviene el documento?</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label className="text-sm font-medium">Tipo de procedencia</Label>
                  <div className="flex gap-6">
                    {[{ v: 'D', l: 'Interno (Dependencia)' }, { v: 'E', l: 'Externo' }].map(({ v, l }) => (
                      <label key={v} className="flex items-center gap-2 cursor-pointer text-sm">
                        <input type="radio" {...register('tipoProcedencia')} value={v} className="h-4 w-4" />
                        {l}
                      </label>
                    ))}
                  </div>
                </div>
                <div className="space-y-1.5">
                  <Label className="flex items-center gap-1.5 text-sm">
                    <Building2 className="h-3.5 w-3.5 text-muted-foreground" />
                    Procedencia <span className="text-destructive">*</span>
                  </Label>
                  <select {...register('idProcedencia')} className={sel(!!errors.idProcedencia)}>
                    <option value="">Seleccionar {tipoProcedencia === 'D' ? 'dependencia' : 'entidad'}...</option>
                    {procOptions.map((d) => <option key={d.id} value={d.id}>{d.descripcion}</option>)}
                  </select>
                  {errors.idProcedencia && <p className="text-xs text-destructive">{errors.idProcedencia.message}</p>}
                </div>
              </div>
            </CardContent>
          </Card>

          {/* SECCIÓN 3: TRÁMITE DESTINO */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base flex items-center gap-2">
                <ChevronRight className="h-4 w-4 text-primary rotate-180" />
                Destino del Trámite
              </CardTitle>
              <CardDescription>¿A dónde se envía el documento?</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label className="text-sm font-medium">Tipo de destinatario</Label>
                  <div className="flex gap-6">
                    {[{ v: 'D', l: 'Interno (Dependencia)' }, { v: 'E', l: 'Externo' }].map(({ v, l }) => (
                      <label key={v} className="flex items-center gap-2 cursor-pointer text-sm">
                        <input type="radio" {...register('tipoDestinatario')} value={v} className="h-4 w-4" />
                        {l}
                      </label>
                    ))}
                  </div>
                </div>
                <div className="space-y-1.5">
                  <Label className="flex items-center gap-1.5 text-sm">
                    <Building2 className="h-3.5 w-3.5 text-muted-foreground" />
                    Destino <span className="text-destructive">*</span>
                  </Label>
                  <select {...register('idDestino')} className={sel(!!errors.idDestino)}>
                    <option value="">Seleccionar {tipoDestinatario === 'D' ? 'dependencia' : 'entidad'}...</option>
                    {destOptions.map((d) => <option key={d.id} value={d.id}>{d.descripcion}</option>)}
                  </select>
                  {errors.idDestino && <p className="text-xs text-destructive">{errors.idDestino.message}</p>}
                </div>
              </div>
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

              <div className="flex items-center gap-3 pt-3 border-t border-border">
                <input type="checkbox" id="despacharAhora" {...register('despacharAhora')} className="h-4 w-4 rounded" />
                <Label htmlFor="despacharAhora" className="cursor-pointer text-sm font-medium">
                  Guardar y despachar inmediatamente al destino seleccionado
                </Label>
              </div>
            </CardContent>
          </Card>

          {/* SECCIÓN 5: ARCHIVO */}
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base flex items-center gap-2">
                <Paperclip className="h-4 w-4 text-primary" />
                Archivo Adjunto <span className="text-xs text-muted-foreground font-normal">(opcional)</span>
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
                  className={cn('flex flex-col items-center gap-3 p-8 rounded-xl border-2 border-dashed cursor-pointer transition-all',
                    dragOver ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/50 hover:bg-muted/30')}
                >
                  <Paperclip className={cn('h-8 w-8', dragOver ? 'text-primary' : 'text-muted-foreground')} />
                  <div className="text-center">
                    <p className="text-sm font-medium">{dragOver ? 'Suelta el archivo' : 'Arrastra o haz clic para subir'}</p>
                    <p className="text-xs text-muted-foreground mt-1">PDF, DOC, DOCX, XLS, PNG, JPG — máx. 20 MB</p>
                  </div>
                  <input id="file-input" type="file" className="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
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
                : <><Send className="h-4 w-4" />{despacharAhora ? 'Guardar y Despachar' : 'Guardar Documento'}</>
              }
            </Button>
          </div>
        </div>
      </form>
    </div>
  );
}
