import { useState, useEffect } from 'react';
import { Link, useSearchParams, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { ArrowLeft, Lock, Eye, EyeOff, CheckCircle2, XCircle, Loader2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { apiClient } from '@/lib/api/client';
import { cn } from '@/lib/utils';

const schema = z.object({
  nuevaClave: z
    .string()
    .min(4, 'Mínimo 4 caracteres')
    .max(10, 'Máximo 10 caracteres'),
  confirmar: z.string(),
}).refine((d) => d.nuevaClave === d.confirmar, {
  message: 'Las contraseñas no coinciden',
  path: ['confirmar'],
});
type FormData = z.infer<typeof schema>;

type TokenState = 'validating' | 'valid' | 'invalid';

export function ResetPasswordPage() {
  const [searchParams]   = useSearchParams();
  const navigate         = useNavigate();
  const token            = searchParams.get('token') ?? '';

  const [tokenState, setTokenState] = useState<TokenState>('validating');
  const [showPwd, setShowPwd]       = useState(false);
  const [showConf, setShowConf]     = useState(false);
  const [done, setDone]             = useState(false);
  const [serverError, setServerError] = useState('');

  const { register, handleSubmit, watch, formState: { errors, isSubmitting } } = useForm<FormData>({
    resolver: zodResolver(schema),
  });

  // Validar el token al montar el componente
  useEffect(() => {
    if (!token) { setTokenState('invalid'); return; }

    apiClient.get(`/auth/validate-reset-token?token=${encodeURIComponent(token)}`)
      .then(() => setTokenState('valid'))
      .catch(() => setTokenState('invalid'));
  }, [token]);

  const onSubmit = async (data: FormData) => {
    setServerError('');
    try {
      await apiClient.post('/auth/reset-password', {
        token,
        nuevaClave: data.nuevaClave,
      });
      setDone(true);
      // Redirigir al login después de 3 segundos
      setTimeout(() => navigate('/login', { replace: true }), 3000);
    } catch (e: unknown) {
      const msg = (e as { response?: { data?: { error?: string } } })?.response?.data?.error;
      setServerError(msg ?? 'Ocurrió un error. Solicita un nuevo enlace de recuperación.');
    }
  };

  const nuevaClave = watch('nuevaClave', '');

  return (
    <div className="min-h-screen flex items-center justify-center bg-background p-6">
      <div className="w-full max-w-md">

        {/* Logo */}
        <div className="flex items-center gap-3 mb-8">
          <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-sm">
            SD
          </div>
          <div>
            <p className="font-semibold text-foreground text-sm">SISDOC</p>
            <p className="text-muted-foreground text-xs">Sistema de Gestión Documental</p>
          </div>
        </div>

        {/* Estado: validando token */}
        {tokenState === 'validating' && (
          <div className="flex flex-col items-center gap-4 py-12">
            <Loader2 className="h-8 w-8 animate-spin text-primary" />
            <p className="text-sm text-muted-foreground">Verificando enlace…</p>
          </div>
        )}

        {/* Estado: token inválido */}
        {tokenState === 'invalid' && (
          <div className="text-center space-y-4">
            <div className="flex justify-center">
              <div className="flex h-16 w-16 items-center justify-center rounded-full bg-destructive/10">
                <XCircle className="h-8 w-8 text-destructive" />
              </div>
            </div>
            <h2 className="text-xl font-bold text-foreground">Enlace inválido o expirado</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              Este enlace de recuperación ya no es válido. Puede haber expirado o ya fue utilizado.
            </p>
            <div className="flex flex-col gap-2 pt-2">
              <Link to="/forgot-password">
                <Button className="w-full">Solicitar nuevo enlace</Button>
              </Link>
              <Link to="/login">
                <Button variant="outline" className="w-full gap-2">
                  <ArrowLeft className="h-4 w-4" />
                  Volver al inicio de sesión
                </Button>
              </Link>
            </div>
          </div>
        )}

        {/* Estado: contraseña cambiada exitosamente */}
        {done && (
          <div className="text-center space-y-4">
            <div className="flex justify-center">
              <div className="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                <CheckCircle2 className="h-8 w-8 text-emerald-600 dark:text-emerald-400" />
              </div>
            </div>
            <h2 className="text-xl font-bold text-foreground">¡Contraseña actualizada!</h2>
            <p className="text-sm text-muted-foreground">
              Tu contraseña fue cambiada exitosamente. Serás redirigido al inicio de sesión en unos segundos.
            </p>
            <Link to="/login">
              <Button className="mt-2 gap-2">
                <ArrowLeft className="h-4 w-4" />
                Ir al inicio de sesión
              </Button>
            </Link>
          </div>
        )}

        {/* Estado: formulario activo */}
        {tokenState === 'valid' && !done && (
          <>
            <div className="mb-8">
              <h2 className="text-2xl font-bold text-foreground">Nueva contraseña</h2>
              <p className="text-muted-foreground mt-1 text-sm">
                Elige una contraseña segura para tu cuenta.
              </p>
            </div>

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-5" noValidate>

              {/* Nueva contraseña */}
              <div className="space-y-2">
                <Label htmlFor="nuevaClave">Nueva contraseña</Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="nuevaClave"
                    type={showPwd ? 'text' : 'password'}
                    placeholder="Mínimo 4 caracteres"
                    autoFocus
                    autoComplete="new-password"
                    maxLength={10}
                    className={cn('pl-9 pr-10', errors.nuevaClave && 'border-destructive focus-visible:ring-destructive')}
                    {...register('nuevaClave')}
                  />
                  <button
                    type="button"
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    onClick={() => setShowPwd((v) => !v)}
                    tabIndex={-1}
                  >
                    {showPwd ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                  </button>
                </div>
                {errors.nuevaClave && (
                  <p className="text-xs text-destructive">{errors.nuevaClave.message}</p>
                )}
                {/* Indicador de fortaleza visual */}
                {nuevaClave && (
                  <div className="flex gap-1 mt-1">
                    {[4, 6, 8].map((len) => (
                      <div
                        key={len}
                        className={cn(
                          'h-1 flex-1 rounded-full transition-colors',
                          nuevaClave.length >= len ? 'bg-emerald-500' : 'bg-muted'
                        )}
                      />
                    ))}
                    <p className="text-[10px] text-muted-foreground ml-1 self-center">
                      {nuevaClave.length < 4 ? 'Muy corta' : nuevaClave.length < 6 ? 'Aceptable' : nuevaClave.length < 8 ? 'Buena' : 'Fuerte'}
                    </p>
                  </div>
                )}
              </div>

              {/* Confirmar contraseña */}
              <div className="space-y-2">
                <Label htmlFor="confirmar">Confirmar contraseña</Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="confirmar"
                    type={showConf ? 'text' : 'password'}
                    placeholder="Repite la contraseña"
                    autoComplete="new-password"
                    maxLength={10}
                    className={cn('pl-9 pr-10', errors.confirmar && 'border-destructive focus-visible:ring-destructive')}
                    {...register('confirmar')}
                  />
                  <button
                    type="button"
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                    onClick={() => setShowConf((v) => !v)}
                    tabIndex={-1}
                  >
                    {showConf ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                  </button>
                </div>
                {errors.confirmar && (
                  <p className="text-xs text-destructive">{errors.confirmar.message}</p>
                )}
              </div>

              {/* Error del servidor */}
              {serverError && (
                <div className="flex items-start gap-2 rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2.5">
                  <XCircle className="h-4 w-4 text-destructive shrink-0 mt-0.5" />
                  <p className="text-sm text-destructive">{serverError}</p>
                </div>
              )}

              <Button type="submit" className="w-full h-10 gap-2" disabled={isSubmitting}>
                {isSubmitting
                  ? <><Loader2 className="h-4 w-4 animate-spin" />Actualizando…</>
                  : <><Lock className="h-4 w-4" />Cambiar contraseña</>
                }
              </Button>
            </form>

            <div className="mt-6 text-center">
              <Link
                to="/login"
                className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors"
              >
                <ArrowLeft className="h-3.5 w-3.5" />
                Volver al inicio de sesión
              </Link>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
