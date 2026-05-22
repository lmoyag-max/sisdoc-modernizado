import { useState, useRef, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Settings, Upload, ImageIcon, Building2, Save,
  CheckCircle2, RefreshCw, Palette, Monitor, X, Type,
} from 'lucide-react';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { cn, uploadUrl } from '@/lib/utils';

interface SistemaConfig {
  nombreSistema:        string;
  nombreInstitucion:    string;
  logoUrl:              string | null;
  backgroundUrl:        string | null;
  version:              string;
  // Textos del login
  loginNombreSistema:   string;
  loginSubtitulo:       string;
  loginTituloPrincipal: string;
  loginDescripcion:     string;
  loginCard1:           string;
  loginCard2:           string;
  loginCard3:           string;
  loginCard4:           string;
  loginFooter:          string;
}

async function fetchConfig(): Promise<SistemaConfig> {
  const res = await apiClient.get<{ ok: boolean; data: SistemaConfig }>('/configuracion');
  return res.data.data;
}

function ImageUploadZone({
  label,
  sublabel,
  currentUrl,
  onUpload,
  isPending,
  accept = 'image/*',
  preview,
}: {
  label: string;
  sublabel: string;
  currentUrl: string | null;
  onUpload: (file: File) => void;
  isPending: boolean;
  accept?: string;
  preview?: string | null;
}) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [drag, setDrag] = useState(false);
  const displayUrl = preview ?? currentUrl;

  return (
    <div className="space-y-3">
      <div>
        <p className="text-sm font-medium text-foreground">{label}</p>
        <p className="text-xs text-muted-foreground">{sublabel}</p>
      </div>

      {displayUrl ? (
        <div className="relative rounded-xl overflow-hidden border border-border group">
          <img
            src={displayUrl.startsWith('blob:') || displayUrl.startsWith('data:') ? displayUrl : `${displayUrl}?t=${Date.now()}`}
            alt={label}
            className="w-full object-cover max-h-40"
            onError={(e) => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
          />
          <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
            <Button
              type="button"
              size="sm"
              variant="secondary"
              onClick={() => inputRef.current?.click()}
              disabled={isPending}
              className="gap-2"
            >
              <RefreshCw className="h-3.5 w-3.5" />
              Cambiar imagen
            </Button>
          </div>
        </div>
      ) : (
        <div
          onDragOver={(e) => { e.preventDefault(); setDrag(true); }}
          onDragLeave={() => setDrag(false)}
          onDrop={(e) => {
            e.preventDefault(); setDrag(false);
            const f = e.dataTransfer.files[0];
            if (f) onUpload(f);
          }}
          onClick={() => inputRef.current?.click()}
          className={cn(
            'flex flex-col items-center gap-3 p-10 rounded-xl border-2 border-dashed cursor-pointer transition-all duration-200',
            drag ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/50 hover:bg-muted/30'
          )}
        >
          <ImageIcon className={cn('h-8 w-8', drag ? 'text-primary' : 'text-muted-foreground')} />
          <div className="text-center">
            <p className="text-sm font-medium">{drag ? 'Suelta la imagen' : 'Arrastra o haz clic'}</p>
            <p className="text-xs text-muted-foreground mt-1">PNG, JPG, SVG, WEBP — recomendado &lt; 2 MB</p>
          </div>
        </div>
      )}

      <input
        ref={inputRef}
        type="file"
        accept={accept}
        className="hidden"
        onChange={(e) => {
          const f = e.target.files?.[0];
          if (f) onUpload(f);
          e.target.value = '';
        }}
      />

      {displayUrl && (
        <Button
          type="button"
          variant="outline"
          size="sm"
          className="gap-2 w-full"
          onClick={() => inputRef.current?.click()}
          disabled={isPending}
        >
          {isPending ? <RefreshCw className="h-3.5 w-3.5 animate-spin" /> : <Upload className="h-3.5 w-3.5" />}
          {isPending ? 'Subiendo...' : 'Subir nueva imagen'}
        </Button>
      )}
    </div>
  );
}

export function ConfiguracionPage() {
  const qc = useQueryClient();
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [bgPreview, setBgPreview] = useState<string | null>(null);
  const [nombreSistema,     setNombreSistema]     = useState('');
  const [nombreInstitucion, setNombreInstitucion] = useState('');

  // Textos del login
  const [loginTextos, setLoginTextos] = useState({
    loginNombreSistema:   '',
    loginSubtitulo:       '',
    loginTituloPrincipal: '',
    loginDescripcion:     '',
    loginCard1:           '',
    loginCard2:           '',
    loginCard3:           '',
    loginCard4:           '',
    loginFooter:          '',
  });

  const { data: config, isLoading } = useQuery({
    queryKey: ['configuracion'],
    queryFn: fetchConfig,
  });

  useEffect(() => {
    if (config) {
      setNombreSistema(config.nombreSistema);
      setNombreInstitucion(config.nombreInstitucion);
      setLoginTextos({
        loginNombreSistema:   config.loginNombreSistema   ?? '',
        loginSubtitulo:       config.loginSubtitulo       ?? '',
        loginTituloPrincipal: config.loginTituloPrincipal ?? '',
        loginDescripcion:     config.loginDescripcion     ?? '',
        loginCard1:           config.loginCard1           ?? '',
        loginCard2:           config.loginCard2           ?? '',
        loginCard3:           config.loginCard3           ?? '',
        loginCard4:           config.loginCard4           ?? '',
        loginFooter:          config.loginFooter          ?? '',
      });
    }
  }, [config]);

  const uploadLogo = useMutation({
    mutationFn: async (file: File) => {
      const form = new FormData();
      form.append('archivo', file);
      const res = await apiClient.post('/configuracion/logo', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return res.data;
    },
    onSuccess: () => {
      toast.success('Logo actualizado');
      setLogoPreview(null);
      qc.invalidateQueries({ queryKey: ['configuracion'] });
    },
    onError: () => toast.error('Error al subir el logo'),
  });

  const uploadBackground = useMutation({
    mutationFn: async (file: File) => {
      const form = new FormData();
      form.append('archivo', file);
      const res = await apiClient.post('/configuracion/background', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return res.data;
    },
    onSuccess: () => {
      toast.success('Fondo de pantalla de login actualizado');
      setBgPreview(null);
      qc.invalidateQueries({ queryKey: ['configuracion'] });
    },
    onError: () => toast.error('Error al subir la imagen de fondo'),
  });

  const saveConfig = useMutation({
    mutationFn: async () => {
      const res = await apiClient.patch('/configuracion', { nombreSistema, nombreInstitucion });
      return res.data;
    },
    onSuccess: () => {
      toast.success('Configuración guardada');
      qc.invalidateQueries({ queryKey: ['configuracion'] });
    },
    onError: () => toast.error('Error al guardar la configuración'),
  });

  const saveLoginTextos = useMutation({
    mutationFn: async () => {
      const res = await apiClient.patch('/configuracion', loginTextos);
      return res.data;
    },
    onSuccess: () => {
      toast.success('Textos del login guardados');
      qc.invalidateQueries({ queryKey: ['configuracion'] });
    },
    onError: () => toast.error('Error al guardar los textos del login'),
  });

  const handleLogoFile = (file: File) => {
    setLogoPreview(URL.createObjectURL(file));
    uploadLogo.mutate(file);
  };

  const handleBgFile = (file: File) => {
    setBgPreview(URL.createObjectURL(file));
    uploadBackground.mutate(file);
  };

  return (
    <div className="space-y-6 max-w-4xl mx-auto animate-fade-in">
      {/* Header */}
      <div className="flex items-center gap-3">
        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
          <Settings className="h-5 w-5 text-primary" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-foreground">Configuración del Sistema</h1>
          <p className="text-sm text-muted-foreground">Personaliza la apariencia e identidad del sistema</p>
        </div>
        {config && (
          <Badge variant="outline" className="ml-auto gap-1.5">
            <CheckCircle2 className="h-3 w-3 text-emerald-500" />
            v{config.version}
          </Badge>
        )}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Identidad institucional */}
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2">
              <Building2 className="h-4 w-4 text-primary" />
              Identidad Institucional
            </CardTitle>
            <CardDescription>Nombre del sistema e institución que aparecen en la interfaz</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="nombreSistema">Nombre del sistema</Label>
                <Input
                  id="nombreSistema"
                  value={isLoading ? '' : nombreSistema}
                  onChange={(e) => setNombreSistema(e.target.value)}
                  placeholder="SISDOC"
                  disabled={isLoading}
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="nombreInstitucion">Nombre de la institución</Label>
                <Input
                  id="nombreInstitucion"
                  value={isLoading ? '' : nombreInstitucion}
                  onChange={(e) => setNombreInstitucion(e.target.value)}
                  placeholder="HUAP"
                  disabled={isLoading}
                />
              </div>
            </div>
            <div className="flex justify-end">
              <Button
                onClick={() => saveConfig.mutate()}
                disabled={isLoading || saveConfig.isPending}
                className="gap-2"
                size="sm"
              >
                {saveConfig.isPending
                  ? <RefreshCw className="h-3.5 w-3.5 animate-spin" />
                  : <Save className="h-3.5 w-3.5" />}
                Guardar cambios
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Logo del sistema */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2">
              <Palette className="h-4 w-4 text-primary" />
              Logo del Sistema
            </CardTitle>
            <CardDescription>Se muestra en el sidebar y encabezados</CardDescription>
          </CardHeader>
          <CardContent>
            <ImageUploadZone
              label="Logo institucional"
              sublabel="Fondo transparente — PNG o SVG recomendado"
              currentUrl={uploadUrl(config?.logoUrl)}
              preview={logoPreview}
              onUpload={handleLogoFile}
              isPending={uploadLogo.isPending}
              accept="image/png,image/svg+xml,image/jpeg,image/webp"
            />
          </CardContent>
        </Card>

        {/* Fondo login */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2">
              <Monitor className="h-4 w-4 text-primary" />
              Fondo de Pantalla de Login
            </CardTitle>
            <CardDescription>Imagen que aparece en el panel izquierdo del login</CardDescription>
          </CardHeader>
          <CardContent>
            <ImageUploadZone
              label="Imagen de fondo"
              sublabel="Mínimo 1280×800 px — JPG o PNG recomendado"
              currentUrl={uploadUrl(config?.backgroundUrl)}
              preview={bgPreview}
              onUpload={handleBgFile}
              isPending={uploadBackground.isPending}
              accept="image/jpeg,image/png,image/webp"
            />
          </CardContent>
        </Card>
      </div>

      {/* Textos del Login */}
      <Card className="lg:col-span-2">
        <CardHeader>
          <CardTitle className="text-base flex items-center gap-2">
            <Type className="h-4 w-4 text-primary" />
            Textos del Login
          </CardTitle>
          <CardDescription>Personaliza los textos que aparecen en el panel izquierdo de la pantalla de inicio de sesión</CardDescription>
        </CardHeader>
        <CardContent className="space-y-5">

          {/* Nombre sistema + subtítulo */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div className="space-y-1.5">
              <Label>Nombre del sistema <span className="text-muted-foreground font-normal">(encabezado)</span></Label>
              <Input
                value={isLoading ? '' : loginTextos.loginNombreSistema}
                onChange={(e) => setLoginTextos((p) => ({ ...p, loginNombreSistema: e.target.value }))}
                placeholder="SISDOC"
                disabled={isLoading}
              />
            </div>
            <div className="space-y-1.5">
              <Label>Subtítulo del sistema</Label>
              <Input
                value={isLoading ? '' : loginTextos.loginSubtitulo}
                onChange={(e) => setLoginTextos((p) => ({ ...p, loginSubtitulo: e.target.value }))}
                placeholder="Sistema de Gestión Documental"
                disabled={isLoading}
              />
            </div>
          </div>

          {/* Título principal + descripción */}
          <div className="space-y-1.5">
            <Label>Título principal</Label>
            <Input
              value={isLoading ? '' : loginTextos.loginTituloPrincipal}
              onChange={(e) => setLoginTextos((p) => ({ ...p, loginTituloPrincipal: e.target.value }))}
              placeholder="Gestión documental moderna"
              disabled={isLoading}
            />
          </div>
          <div className="space-y-1.5">
            <Label>Descripción principal</Label>
            <textarea
              value={isLoading ? '' : loginTextos.loginDescripcion}
              onChange={(e) => setLoginTextos((p) => ({ ...p, loginDescripcion: e.target.value }))}
              placeholder="Plataforma enterprise para la gestión, seguimiento y trazabilidad…"
              rows={3}
              disabled={isLoading}
              className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground resize-none focus:outline-none focus:ring-2 focus:ring-ring disabled:opacity-50"
            />
          </div>

          {/* Tarjetas */}
          <div>
            <Label className="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-3 block">
              Tarjetas informativas (4 íconos)
            </Label>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              {(['loginCard1', 'loginCard2', 'loginCard3', 'loginCard4'] as const).map((key, i) => (
                <div key={key} className="space-y-1.5">
                  <Label className="text-xs text-muted-foreground">Tarjeta {i + 1}</Label>
                  <Input
                    value={isLoading ? '' : loginTextos[key]}
                    onChange={(e) => setLoginTextos((p) => ({ ...p, [key]: e.target.value }))}
                    placeholder={['Gestión documental', 'Flujo de derivaciones', 'Trazabilidad completa', 'Historial documental'][i]}
                    disabled={isLoading}
                  />
                </div>
              ))}
            </div>
          </div>

          {/* Footer */}
          <div className="space-y-1.5">
            <Label>Texto de pie de página</Label>
            <Input
              value={isLoading ? '' : loginTextos.loginFooter}
              onChange={(e) => setLoginTextos((p) => ({ ...p, loginFooter: e.target.value }))}
              placeholder="© 2026 SISDOC v2.0 — Sistema institucional de gestión documental"
              disabled={isLoading}
            />
          </div>

          <div className="flex justify-end pt-1">
            <Button
              onClick={() => saveLoginTextos.mutate()}
              disabled={isLoading || saveLoginTextos.isPending}
              className="gap-2"
              size="sm"
            >
              {saveLoginTextos.isPending
                ? <RefreshCw className="h-3.5 w-3.5 animate-spin" />
                : <Save className="h-3.5 w-3.5" />}
              Guardar textos del login
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Info del sistema */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base text-muted-foreground">Información del sistema</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            {[
              { label: 'Versión API', value: config?.version ?? '—' },
              { label: 'Backend', value: 'Node.js + Express' },
              { label: 'Frontend', value: 'React 18 + Vite 6' },
              { label: 'Base de datos', value: 'SQL Server 2022' },
            ].map(({ label, value }) => (
              <div key={label} className="space-y-1">
                <p className="text-xs text-muted-foreground font-medium uppercase tracking-wide">{label}</p>
                <p className="text-foreground font-medium">{value}</p>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
