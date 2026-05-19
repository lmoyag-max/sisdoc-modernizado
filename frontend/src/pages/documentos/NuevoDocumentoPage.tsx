import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, useMutation } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  FileText, ArrowLeft, Send, Paperclip, X, AlertCircle,
  Building2, Tag, MessageSquare, Calendar
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { catalogosApi } from '@/lib/api/catalogos.api';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';

const schema = z.object({
  materia: z.string().min(5, 'Mínimo 5 caracteres').max(250),
  idTipoDocumento: z.string().min(1, 'Selecciona el tipo de documento'),
  idEstadoDocumento: z.string().default('1'),
  observaciones: z.string().max(500).optional(),
  fechaDocumento: z.string().optional(),
});

type FormData = z.infer<typeof schema>;

export function NuevoDocumentoPage() {
  const navigate = useNavigate();
  const [archivo, setArchivo] = useState<File | null>(null);
  const [dragOver, setDragOver] = useState(false);

  const { data: tipos } = useQuery({ queryKey: ['tipos-doc'], queryFn: catalogosApi.tiposDocumento });
  const { data: estados } = useQuery({ queryKey: ['estados'], queryFn: catalogosApi.estados });

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
    watch,
  } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: { idEstadoDocumento: '1', fechaDocumento: new Date().toISOString().split('T')[0] },
  });

  const crearMutation = useMutation({
    mutationFn: async (data: FormData) => {
      const payload = {
        idTipoDocumento: Number(data.idTipoDocumento),
        idEstadoDocumento: Number(data.idEstadoDocumento),
        materia: data.materia,
        observaciones: data.observaciones,
        fechaDocumento: data.fechaDocumento,
      };
      const res = await apiClient.post<{ ok: boolean; data: { idDocumento: number } }>('/documentos', payload);
      if (archivo && res.data.data?.idDocumento) {
        const form = new FormData();
        form.append('archivo', archivo);
        form.append('idDocumento', String(res.data.data.idDocumento));
        await apiClient.post('/archivos/upload', form, { headers: { 'Content-Type': 'multipart/form-data' } });
      }
      return res.data.data;
    },
    onSuccess: (data) => {
      toast.success('Documento creado correctamente');
      navigate(`/documentos`);
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al crear documento';
      toast.error(msg);
    },
  });

  const materia = watch('materia');

  return (
    <div className="space-y-6 max-w-3xl mx-auto animate-fade-in">
      {/* Header */}
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate('/documentos')} className="shrink-0">
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div>
          <h1 className="text-2xl font-bold text-foreground">Nuevo Documento</h1>
          <p className="text-sm text-muted-foreground">Complete el formulario para registrar un nuevo documento</p>
        </div>
      </div>

      <form onSubmit={handleSubmit((d) => crearMutation.mutate(d))} noValidate>
        <div className="space-y-5">
          {/* Card principal */}
          <Card>
            <CardHeader>
              <CardTitle className="text-base flex items-center gap-2">
                <FileText className="h-4 w-4 text-primary" />
                Información del Documento
              </CardTitle>
              <CardDescription>Datos principales del documento a registrar</CardDescription>
            </CardHeader>
            <CardContent className="space-y-5">
              {/* Materia */}
              <div className="space-y-2">
                <Label htmlFor="materia" className="flex items-center gap-1.5">
                  <MessageSquare className="h-3.5 w-3.5 text-muted-foreground" />
                  Materia / Asunto <span className="text-destructive">*</span>
                </Label>
                <textarea
                  id="materia"
                  rows={3}
                  {...register('materia')}
                  placeholder="Descripción clara y concisa del documento..."
                  className={cn(
                    'flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none',
                    errors.materia && 'border-destructive focus-visible:ring-destructive'
                  )}
                />
                <div className="flex items-center justify-between">
                  {errors.materia
                    ? <p className="text-xs text-destructive flex items-center gap-1"><AlertCircle className="h-3 w-3" />{errors.materia.message}</p>
                    : <p className="text-xs text-muted-foreground">Máximo 250 caracteres</p>
                  }
                  <span className={cn('text-xs', (materia?.length ?? 0) > 230 ? 'text-amber-500' : 'text-muted-foreground')}>
                    {materia?.length ?? 0}/250
                  </span>
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {/* Tipo documento */}
                <div className="space-y-2">
                  <Label htmlFor="tipo" className="flex items-center gap-1.5">
                    <Tag className="h-3.5 w-3.5 text-muted-foreground" />
                    Tipo de Documento <span className="text-destructive">*</span>
                  </Label>
                  <select
                    id="tipo"
                    {...register('idTipoDocumento')}
                    className={cn(
                      'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
                      errors.idTipoDocumento && 'border-destructive'
                    )}
                  >
                    <option value="">Seleccionar tipo...</option>
                    {(tipos ?? []).map((t) => (
                      <option key={t.id} value={t.id}>{t.descripcion}</option>
                    ))}
                  </select>
                  {errors.idTipoDocumento && (
                    <p className="text-xs text-destructive">{errors.idTipoDocumento.message}</p>
                  )}
                </div>

                {/* Fecha documento */}
                <div className="space-y-2">
                  <Label htmlFor="fechaDoc" className="flex items-center gap-1.5">
                    <Calendar className="h-3.5 w-3.5 text-muted-foreground" />
                    Fecha del Documento
                  </Label>
                  <Input
                    id="fechaDoc"
                    type="date"
                    {...register('fechaDocumento')}
                  />
                </div>
              </div>

              {/* Estado */}
              <div className="space-y-2">
                <Label className="flex items-center gap-1.5">
                  <Building2 className="h-3.5 w-3.5 text-muted-foreground" />
                  Estado inicial
                </Label>
                <div className="flex flex-wrap gap-2">
                  {(estados ?? []).map((e) => {
                    const current = watch('idEstadoDocumento');
                    const isSelected = String(e.id) === String(current);
                    return (
                      <label key={e.id} className="cursor-pointer">
                        <input type="radio" {...register('idEstadoDocumento')} value={e.id} className="sr-only" />
                        <Badge
                          variant={isSelected ? 'default' : 'outline'}
                          className={cn('cursor-pointer transition-all', isSelected && 'bg-primary text-primary-foreground')}
                        >
                          {e.descripcion}
                        </Badge>
                      </label>
                    );
                  })}
                </div>
              </div>

              {/* Observaciones */}
              <div className="space-y-2">
                <Label htmlFor="obs">Observaciones (opcional)</Label>
                <textarea
                  id="obs"
                  rows={2}
                  {...register('observaciones')}
                  placeholder="Notas adicionales sobre el documento..."
                  className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none"
                />
              </div>
            </CardContent>
          </Card>

          {/* Card archivo adjunto */}
          <Card>
            <CardHeader>
              <CardTitle className="text-base flex items-center gap-2">
                <Paperclip className="h-4 w-4 text-primary" />
                Archivo Adjunto
              </CardTitle>
              <CardDescription>Opcional — PDF, Word, Excel, imágenes (máx. 20 MB)</CardDescription>
            </CardHeader>
            <CardContent>
              {archivo ? (
                <div className="flex items-center gap-3 p-3 rounded-lg bg-primary/5 border border-primary/20">
                  <FileText className="h-8 w-8 text-primary shrink-0" />
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium truncate">{archivo.name}</p>
                    <p className="text-xs text-muted-foreground">{(archivo.size / 1024).toFixed(1)} KB</p>
                  </div>
                  <Button type="button" variant="ghost" size="icon" onClick={() => setArchivo(null)} className="shrink-0">
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
                    'flex flex-col items-center gap-3 p-8 rounded-xl border-2 border-dashed cursor-pointer transition-all duration-200',
                    dragOver ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'
                  )}
                >
                  <Paperclip className={cn('h-8 w-8', dragOver ? 'text-primary' : 'text-muted-foreground')} />
                  <div className="text-center">
                    <p className="text-sm font-medium">{dragOver ? 'Suelta el archivo' : 'Arrastra o haz clic'}</p>
                    <p className="text-xs text-muted-foreground mt-1">PDF, DOC, DOCX, XLS, PNG, JPG</p>
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
            <Button type="submit" loading={isSubmitting || crearMutation.isPending} className="gap-2 px-8">
              <Send className="h-4 w-4" />
              Guardar Documento
            </Button>
          </div>
        </div>
      </form>
    </div>
  );
}
