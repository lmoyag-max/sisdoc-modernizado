import { useState, useEffect } from 'react';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Eye, EyeOff, Lock, User, FileText, Shield, Clock, GitBranch } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn, uploadUrl } from '@/lib/utils';
import { authApi } from '@/lib/api/auth.api';
import { apiClient } from '@/lib/api/client';
import { useAuthStore } from '@/stores/auth.store';
import { toast } from 'sonner';

const loginSchema = z.object({
  usuario: z.string().min(1, 'El usuario es requerido'),
  clave: z.string().min(1, 'La contraseña es requerida'),
});
type LoginForm = z.infer<typeof loginSchema>;

const FEATURE_ICONS = [FileText, GitBranch, Shield, Clock];

export function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const setAuth = useAuthStore((s) => s.setAuth);
  const [showPassword,   setShowPassword]   = useState(false);
  const [bgUrl,          setBgUrl]          = useState<string | null>(null);
  const [logoUrl,        setLogoUrl]        = useState<string | null>(null);
  const [logoImgError,   setLogoImgError]   = useState(false);
  const [textos, setTextos] = useState({
    nombreSistema:        'SISDOC',
    subtitulo:            'Sistema de Gestión Documental',
    tituloPrincipal:      'Gestión documental',
    tituloPrincipalAcento:'moderna',
    descripcion:          'Plataforma enterprise para la gestión, seguimiento y trazabilidad de documentos institucionales.',
    card1: 'Gestión documental',
    card2: 'Flujo de derivaciones',
    card3: 'Trazabilidad completa',
    card4: 'Historial documental',
    footer: '© 2026 SISDOC v2.0 — Sistema institucional de gestión documental',
  });

  useEffect(() => {
    apiClient.get<{ ok: boolean; data: {
      backgroundUrl: string | null; logoUrl: string | null;
      loginNombreSistema?: string; loginSubtitulo?: string;
      loginTituloPrincipal?: string; loginDescripcion?: string;
      loginCard1?: string; loginCard2?: string; loginCard3?: string; loginCard4?: string;
      loginFooter?: string;
    } }>('/configuracion')
      .then((r) => {
        const d = r.data.data;
        if (d.backgroundUrl) setBgUrl(uploadUrl(d.backgroundUrl));
        if (d.logoUrl)       { setLogoUrl(uploadUrl(d.logoUrl)); setLogoImgError(false); }
        // Parsear título principal: la última palabra va en color acento
        const titulo = d.loginTituloPrincipal ?? 'Gestión documental moderna';
        const palabras = titulo.trim().split(' ');
        const acento = palabras.pop() ?? 'moderna';
        const base   = palabras.join(' ');
        setTextos({
          nombreSistema:         d.loginNombreSistema ?? 'SISDOC',
          subtitulo:             d.loginSubtitulo     ?? 'Sistema de Gestión Documental',
          tituloPrincipal:       base,
          tituloPrincipalAcento: acento,
          descripcion:           d.loginDescripcion   ?? 'Plataforma enterprise para la gestión, seguimiento y trazabilidad de documentos institucionales.',
          card1:                 d.loginCard1         ?? 'Gestión documental',
          card2:                 d.loginCard2         ?? 'Flujo de derivaciones',
          card3:                 d.loginCard3         ?? 'Trazabilidad completa',
          card4:                 d.loginCard4         ?? 'Historial documental',
          footer:                d.loginFooter        ?? '© 2026 SISDOC v2.0 — Sistema institucional de gestión documental',
        });
      })
      .catch(() => {/* usa valores por defecto */});
  }, []);

  const from = (location.state as { from?: { pathname: string } })?.from?.pathname ?? '/dashboard';

  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginForm) => {
    try {
      const result = await authApi.login(data.usuario, data.clave);
      setAuth(result.user, result.accessToken);
      navigate(from, { replace: true });
      toast.success(`Bienvenido, ${result.user.nombres ?? result.user.usuario}`);
    } catch (error: unknown) {
      const msg =
        (error as { response?: { data?: { error?: string } } })?.response?.data?.error
        ?? 'Error al iniciar sesión';
      toast.error(msg);
    }
  };

  return (
    <div className="min-h-screen flex">
      {/* Panel izquierdo — Brand */}
      <div className="hidden lg:flex lg:w-[480px] xl:w-[560px] flex-col relative overflow-hidden bg-slate-950">
        {/* Fondo configurable o gradiente por defecto */}
        <div className="absolute inset-0">
          {bgUrl ? (
            <>
              <img src={bgUrl} alt="" className="absolute inset-0 w-full h-full object-cover" />
              <div className="absolute inset-0 bg-gradient-to-br from-slate-950/80 via-slate-900/70 to-indigo-950/80" />
            </>
          ) : (
            <>
              <div className="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950" />
              <div className="absolute top-0 right-0 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl" />
              <div className="absolute bottom-0 left-0 w-80 h-80 bg-blue-600/15 rounded-full blur-3xl" />
              <div
                className="absolute inset-0 opacity-[0.03]"
                style={{
                  backgroundImage: 'linear-gradient(rgb(255,255,255) 1px, transparent 1px), linear-gradient(to right, rgb(255,255,255) 1px, transparent 1px)',
                  backgroundSize: '48px 48px',
                }}
              />
            </>
          )}
        </div>

        {/* Content */}
        <div className="relative flex flex-col h-full p-10">
          {/* Logo institucional o fallback SD */}
          <div className="flex items-center gap-3">
            {logoUrl && !logoImgError ? (
              <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 backdrop-blur-sm p-1.5 shadow-lg overflow-hidden">
                <img
                  src={logoUrl}
                  alt={textos.nombreSistema}
                  className="max-h-full max-w-full object-contain"
                  onError={() => setLogoImgError(true)}
                />
              </div>
            ) : (
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-900/50">
                SD
              </div>
            )}
            <div>
              <p className="text-white font-semibold text-sm tracking-wide">{textos.nombreSistema}</p>
              <p className="text-slate-400 text-xs">{textos.subtitulo}</p>
            </div>
          </div>

          {/* Hero */}
          <div className="flex-1 flex flex-col justify-center mt-12">
            <h1 className="text-4xl xl:text-5xl font-bold text-white leading-tight mb-4">
              {textos.tituloPrincipal}{' '}
              <span className="text-indigo-400">{textos.tituloPrincipalAcento}</span>
            </h1>
            <p className="text-slate-400 text-lg leading-relaxed mb-12">
              {textos.descripcion}
            </p>

            {/* Features */}
            <div className="grid grid-cols-2 gap-3">
              {[textos.card1, textos.card2, textos.card3, textos.card4].map((label, i) => {
                const Icon = FEATURE_ICONS[i];
                return (
                  <div
                    key={i}
                    className="flex items-center gap-3 rounded-xl bg-white/5 border border-white/10 px-4 py-3 backdrop-blur-sm"
                  >
                    <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-500/20">
                      <Icon className="h-4 w-4 text-indigo-400" />
                    </div>
                    <span className="text-sm text-slate-300 font-medium">{label}</span>
                  </div>
                );
              })}
            </div>
          </div>

          {/* Footer */}
          <div className="mt-8">
            <p className="text-slate-600 text-xs">{textos.footer}</p>
          </div>
        </div>
      </div>

      {/* Panel derecho — Formulario */}
      <div className="flex-1 flex items-center justify-center bg-background p-6 relative overflow-hidden">

        {/* Watermark — logo institucional detrás del formulario */}
        {logoUrl && (
          <div
            className="absolute inset-0 flex items-center justify-center pointer-events-none select-none"
            aria-hidden="true"
          >
            <img
              src={logoUrl}
              alt=""
              className="w-[80%] max-w-[420px] object-contain"
              style={{
                opacity: 0.05,
                filter: 'blur(1px)',
                mixBlendMode: 'multiply',
              }}
              onError={(e) => { e.currentTarget.style.display = 'none'; }}
            />
          </div>
        )}

        <div className="w-full max-w-md relative z-10">
          {/* Mobile logo */}
          <div className="flex items-center gap-3 mb-8 lg:hidden">
            {logoUrl && !logoImgError ? (
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted p-1.5 overflow-hidden">
                <img
                  src={logoUrl}
                  alt={textos.nombreSistema}
                  className="max-h-full max-w-full object-contain"
                  onError={() => setLogoImgError(true)}
                />
              </div>
            ) : (
              <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-sm">
                SD
              </div>
            )}
            <div>
              <p className="font-semibold text-foreground text-sm">{textos.nombreSistema}</p>
              <p className="text-muted-foreground text-xs">{textos.subtitulo}</p>
            </div>
          </div>

          <div className="mb-8">
            <h2 className="text-2xl font-bold text-foreground">Iniciar sesión</h2>
            <p className="text-muted-foreground mt-1 text-sm">
              Ingresa tus credenciales para acceder al sistema
            </p>
          </div>

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-5" noValidate>
            {/* Usuario */}
            <div className="space-y-2">
              <Label htmlFor="usuario">Usuario</Label>
              <div className="relative">
                <User className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  id="usuario"
                  type="text"
                  placeholder="Tu nombre de usuario"
                  className={cn('pl-9', errors.usuario && 'border-destructive focus-visible:ring-destructive')}
                  autoComplete="username"
                  autoFocus
                  {...register('usuario')}
                />
              </div>
              {errors.usuario && (
                <p className="text-xs text-destructive">{errors.usuario.message}</p>
              )}
            </div>

            {/* Contraseña */}
            <div className="space-y-2">
              <Label htmlFor="clave">Contraseña</Label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  id="clave"
                  type={showPassword ? 'text' : 'password'}
                  placeholder="Tu contraseña"
                  className={cn('pl-9 pr-10', errors.clave && 'border-destructive focus-visible:ring-destructive')}
                  autoComplete="current-password"
                  {...register('clave')}
                />
                <button
                  type="button"
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                  onClick={() => setShowPassword(!showPassword)}
                  tabIndex={-1}
                >
                  {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
              {errors.clave && (
                <p className="text-xs text-destructive">{errors.clave.message}</p>
              )}
            </div>

            {/* Olvidaste tu contraseña */}
            <div className="flex justify-end -mt-1">
              <Link
                to="/forgot-password"
                className="text-xs text-muted-foreground hover:text-primary transition-colors"
              >
                ¿Olvidaste tu contraseña?
              </Link>
            </div>

            {/* Submit */}
            <Button
              type="submit"
              className="w-full h-10 text-sm font-medium"
              loading={isSubmitting}
            >
              {isSubmitting ? 'Ingresando...' : 'Iniciar sesión'}
            </Button>
          </form>

          <p className="mt-6 text-center text-xs text-muted-foreground">
            ¿Problemas para acceder?{' '}
            <span className="text-primary cursor-pointer hover:underline">
              Contacta a soporte
            </span>
          </p>
        </div>
      </div>
    </div>
  );
}
