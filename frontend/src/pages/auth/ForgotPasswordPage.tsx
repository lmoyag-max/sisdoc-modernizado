import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { ArrowLeft, Mail, CheckCircle2, Loader2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { apiClient } from '@/lib/api/client';
import { cn } from '@/lib/utils';

const schema = z.object({
  email: z.string().email('Ingresa un correo electrónico válido').max(100),
});
type FormData = z.infer<typeof schema>;

export function ForgotPasswordPage() {
  const [sent, setSent] = useState(false);
  const [serverMsg, setServerMsg] = useState('');

  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm<FormData>({
    resolver: zodResolver(schema),
  });

  const onSubmit = async (data: FormData) => {
    try {
      const res = await apiClient.post<{ ok: boolean; message: string }>(
        '/auth/forgot-password',
        { email: data.email },
      );
      setServerMsg(res.data.message ?? 'Solicitud enviada.');
      setSent(true);
    } catch (e: unknown) {
      const msg = (e as { response?: { data?: { error?: string } } })?.response?.data?.error;
      setServerMsg(msg ?? 'Ocurrió un error. Intenta nuevamente.');
      setSent(true); // Mostramos el mensaje genérico igual (no revelar si el correo existe)
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-background p-6">
      <div className="w-full max-w-md">

        {/* Logo mobile */}
        <div className="flex items-center gap-3 mb-8">
          <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-sm">
            SD
          </div>
          <div>
            <p className="font-semibold text-foreground text-sm">SISDOC</p>
            <p className="text-muted-foreground text-xs">Sistema de Gestión Documental</p>
          </div>
        </div>

        {sent ? (
          /* Estado: correo enviado */
          <div className="text-center space-y-4">
            <div className="flex justify-center">
              <div className="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                <CheckCircle2 className="h-8 w-8 text-emerald-600 dark:text-emerald-400" />
              </div>
            </div>
            <h2 className="text-xl font-bold text-foreground">Revisa tu correo</h2>
            <p className="text-sm text-muted-foreground leading-relaxed">
              {serverMsg || 'Si el correo existe en el sistema, recibirás un enlace de recuperación en breve.'}
            </p>
            <p className="text-xs text-muted-foreground">
              El enlace expira en <strong>30 minutos</strong>. Revisa también tu carpeta de spam.
            </p>
            <Link to="/login">
              <Button variant="outline" className="mt-4 gap-2">
                <ArrowLeft className="h-4 w-4" />
                Volver al inicio de sesión
              </Button>
            </Link>
          </div>
        ) : (
          /* Formulario */
          <>
            <div className="mb-8">
              <h2 className="text-2xl font-bold text-foreground">Recuperar contraseña</h2>
              <p className="text-muted-foreground mt-1 text-sm">
                Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
              </p>
            </div>

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-5" noValidate>
              <div className="space-y-2">
                <Label htmlFor="email">Correo electrónico</Label>
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="email"
                    type="email"
                    placeholder="tu@correo.com"
                    autoComplete="email"
                    autoFocus
                    className={cn('pl-9', errors.email && 'border-destructive focus-visible:ring-destructive')}
                    {...register('email')}
                  />
                </div>
                {errors.email && (
                  <p className="text-xs text-destructive">{errors.email.message}</p>
                )}
              </div>

              <Button type="submit" className="w-full h-10 gap-2" disabled={isSubmitting}>
                {isSubmitting
                  ? <><Loader2 className="h-4 w-4 animate-spin" />Enviando…</>
                  : <><Mail className="h-4 w-4" />Enviar enlace de recuperación</>
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
