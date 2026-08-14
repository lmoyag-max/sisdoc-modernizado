import { useRef, useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Upload, File, FileText, Trash2, Download, ImageIcon, CloudUpload } from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { EmptyState } from '@/components/shared/EmptyState';
import { formatFechaHora, descargarArchivoAutenticado } from '@/lib/utils';
import { useUploadRules } from '@/hooks/useUploadRules';
import { toast } from 'sonner';

interface ArchivoDigital {
  id_archivo: number;
  id_documento: number | null;
  nombre_archivo: string | null;
  ruta_archivo: string | null;
  tipo_mime: string | null;
  tamano: number | null;
  fecha_subida: string;
  materia: string | null;
  url: string | null;
}

function formatBytes(bytes: number | null): string {
  if (!bytes) return '—';
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function FileIcon({ mime }: { mime: string | null }) {
  if (!mime) return <File className="h-5 w-5" />;
  if (mime.startsWith('image/')) return <ImageIcon className="h-5 w-5 text-purple-500" />;
  if (mime.includes('pdf')) return <FileText className="h-5 w-5 text-red-500" />;
  if (mime.includes('word') || mime.includes('document')) return <FileText className="h-5 w-5 text-blue-500" />;
  if (mime.includes('excel') || mime.includes('sheet')) return <FileText className="h-5 w-5 text-emerald-500" />;
  return <File className="h-5 w-5 text-muted-foreground" />;
}

export function ArchivosPage() {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isDragOver, setIsDragOver] = useState(false);
  const qc = useQueryClient();
  const uploadRules = useUploadRules();

  const { data: archivos, isLoading } = useQuery({
    queryKey: ['archivos'],
    queryFn: () => apiClient.get<{ ok: boolean; data: ArchivoDigital[] }>('/archivos').then((r) => r.data.data),
  });

  const uploadMutation = useMutation({
    mutationFn: async (file: File) => {
      const form = new FormData();
      form.append('archivo', file);
      const { data } = await apiClient.post<{ ok: boolean; data: unknown }>('/archivos/upload', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['archivos'] });
      toast.success('Archivo subido correctamente');
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al subir el archivo';
      toast.error(msg);
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => apiClient.delete(`/archivos/${id}`),
    onSuccess: () => { qc.invalidateQueries({ queryKey: ['archivos'] }); toast.success('Archivo eliminado'); },
    onError: () => toast.error('No se pudo eliminar el archivo'),
  });

  const handleFiles = (files: FileList | null) => {
    if (!files) return;
    Array.from(files).forEach((file) => uploadMutation.mutate(file));
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragOver(false);
    handleFiles(e.dataTransfer.files);
  };

  const totalSize = (archivos ?? []).reduce((acc, a) => acc + (a.tamano ?? 0), 0);

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex items-center gap-3">
        <span className="icon-3d icon-3d-emerald hidden sm:flex h-11 w-11 shrink-0 items-center justify-center">
          <Upload className="h-5 w-5 text-white" />
        </span>
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-foreground flex items-center gap-2">
            <Upload className="h-5 w-5 sm:hidden text-primary" />
            Gestión de Archivos
          </h1>
          <p className="text-sm text-muted-foreground mt-0.5">
            Sube y gestiona los archivos digitales del sistema documental
          </p>
        </div>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
        {[
          { label: 'Total archivos',  value: archivos?.length ?? '—',         mono: false, accent: 'indigo',  icon: FileText },
          { label: 'Espacio usado',   value: formatBytes(totalSize),           mono: true,  accent: 'sky',     icon: CloudUpload },
          { label: 'Tipos admitidos', value: uploadRules.extensionesPermitidas.map((e) => e.toUpperCase()).join(', '), mono: false, accent: 'emerald', icon: File },
        ].map(({ label, value, mono, accent, icon: Icon }) => (
          <div key={label} className="kpi-premium px-4 py-3">
            <div className="flex items-center gap-2 mb-2">
              <span className={`icon-3d-sm icon-3d-${accent} flex h-7 w-7 shrink-0 items-center justify-center`}>
                <Icon className="h-3.5 w-3.5 text-white" />
              </span>
              <p className="text-xs text-muted-foreground">{label}</p>
            </div>
            <p className={`text-sm font-semibold text-foreground truncate ${mono ? 'font-mono' : ''}`}>{value}</p>
          </div>
        ))}
      </div>

      {/* Zona de carga */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Subir archivo</CardTitle>
          <CardDescription>
            Arrastra archivos o haz clic para seleccionar. Máx. {uploadRules.maxFileMB} MB por archivo.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div
            onDragOver={(e) => { e.preventDefault(); setIsDragOver(true); }}
            onDragLeave={() => setIsDragOver(false)}
            onDrop={handleDrop}
            onClick={() => fileInputRef.current?.click()}
            className={`relative flex flex-col items-center justify-center gap-4 rounded-xl border-2 border-dashed p-12 text-center cursor-pointer transition-all duration-200 ${
              isDragOver
                ? 'border-primary bg-primary/5 scale-[1.01]'
                : 'border-border hover:border-primary/50 hover:bg-muted/40'
            }`}
          >
            <span className={`icon-3d flex h-16 w-16 items-center justify-center transition-transform ${isDragOver ? 'icon-3d-indigo scale-105' : 'icon-3d-slate'}`}>
              <CloudUpload className="h-7 w-7 text-white" />
            </span>
            <div>
              <p className="text-sm font-semibold text-foreground">
                {isDragOver ? 'Suelta el archivo aquí' : 'Arrastra archivos o haz clic'}
              </p>
              <p className="text-xs text-muted-foreground mt-1">
                {uploadRules.extensionesPermitidas.map((e) => e.toUpperCase()).join(', ')} — máx. {uploadRules.maxFileMB} MB
              </p>
            </div>
            {uploadMutation.isPending && (
              <div className="absolute inset-0 flex items-center justify-center rounded-xl bg-background/80 backdrop-blur-sm">
                <div className="flex items-center gap-2 text-sm text-primary font-medium">
                  <svg className="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  Subiendo...
                </div>
              </div>
            )}
          </div>
          <input
            ref={fileInputRef}
            type="file"
            className="hidden"
            multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt"
            onChange={(e) => handleFiles(e.target.files)}
          />
        </CardContent>
      </Card>

      {/* Lista de archivos */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Archivos subidos</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          {isLoading ? (
            <div className="divide-y">
              {Array.from({ length: 4 }).map((_, i) => (
                <div key={i} className="flex items-center gap-4 px-6 py-4">
                  <Skeleton className="h-10 w-10 rounded-lg shrink-0" />
                  <div className="flex-1 space-y-2">
                    <Skeleton className="h-4 w-1/2" />
                    <Skeleton className="h-3 w-1/3" />
                  </div>
                  <Skeleton className="h-8 w-20" />
                </div>
              ))}
            </div>
          ) : (archivos ?? []).length === 0 ? (
            <EmptyState
              icon={Upload}
              title="Sin archivos"
              description="Sube archivos usando la zona de carga de arriba."
            />
          ) : (
            <div className="divide-y">
              {(archivos ?? []).map((archivo) => (
                <div key={archivo.id_archivo} className="flex items-center gap-4 px-6 py-4 hover:bg-muted/30 transition-colors">
                  <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-muted">
                    <FileIcon mime={archivo.tipo_mime} />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-foreground truncate">
                      {archivo.nombre_archivo ?? 'Archivo sin nombre'}
                    </p>
                    <div className="flex items-center gap-3 mt-0.5 text-xs text-muted-foreground flex-wrap">
                      <span>{formatBytes(archivo.tamano)}</span>
                      {archivo.tipo_mime && <Badge variant="secondary" className="text-xs">{archivo.tipo_mime.split('/')[1]}</Badge>}
                      <span>{formatFechaHora(archivo.fecha_subida)}</span>
                      {archivo.materia && <span className="truncate max-w-xs">· {archivo.materia}</span>}
                    </div>
                  </div>
                  <div className="flex items-center gap-2 shrink-0">
                    {archivo.url && (
                      <Button
                        variant="ghost" size="icon" className="h-8 w-8 text-muted-foreground hover:text-primary"
                        onClick={() => {
                          descargarArchivoAutenticado(archivo.url!, archivo.nombre_archivo ?? 'archivo')
                            .catch(() => toast.error('No se pudo descargar el archivo'));
                        }}
                      >
                        <Download className="h-4 w-4" />
                      </Button>
                    )}
                    <Button
                      variant="ghost"
                      size="icon"
                      className="h-8 w-8 text-muted-foreground hover:text-destructive"
                      onClick={() => deleteMutation.mutate(archivo.id_archivo)}
                      loading={deleteMutation.isPending}
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
