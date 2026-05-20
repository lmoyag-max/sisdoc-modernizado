import { Component, ErrorInfo, ReactNode } from 'react';
import { AlertTriangle, RefreshCw, Home } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface Props { children: ReactNode }
interface State { error: Error | null }

export class AppErrorBoundary extends Component<Props, State> {
  state: State = { error: null };

  static getDerivedStateFromError(error: Error): State {
    return { error };
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error('[SISDOC Error]', error, info.componentStack);
  }

  render() {
    if (!this.state.error) return this.props.children;

    const isDev = import.meta.env.DEV;

    return (
      <div className="min-h-screen flex items-center justify-center p-6 bg-background">
        <div className="max-w-md w-full space-y-6 text-center animate-fade-in">
          <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-destructive/10 mx-auto">
            <AlertTriangle className="h-8 w-8 text-destructive" />
          </div>
          <div>
            <h1 className="text-xl font-bold text-foreground">Algo salió mal</h1>
            <p className="text-sm text-muted-foreground mt-2">
              Ocurrió un error inesperado. Puedes intentar recargar la página o volver al inicio.
            </p>
          </div>

          {isDev && (
            <div className="text-left rounded-lg bg-muted/50 border border-border p-4">
              <p className="text-xs font-mono text-destructive font-semibold mb-1">{this.state.error.name}</p>
              <p className="text-xs font-mono text-muted-foreground break-all">{this.state.error.message}</p>
            </div>
          )}

          <div className="flex gap-3 justify-center">
            <Button variant="outline" onClick={() => window.location.reload()} className="gap-2">
              <RefreshCw className="h-4 w-4" />
              Recargar
            </Button>
            <Button onClick={() => { this.setState({ error: null }); window.location.href = '/dashboard'; }} className="gap-2">
              <Home className="h-4 w-4" />
              Ir al inicio
            </Button>
          </div>
        </div>
      </div>
    );
  }
}

/** Versión inline para usar como errorElement en React Router */
export function RouteError() {
  return (
    <div className="min-h-[60vh] flex flex-col items-center justify-center gap-4 p-6 text-center animate-fade-in">
      <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive/10">
        <AlertTriangle className="h-7 w-7 text-destructive" />
      </div>
      <div>
        <h2 className="text-lg font-bold text-foreground">Error en esta sección</h2>
        <p className="text-sm text-muted-foreground mt-1">No se pudo cargar el contenido.</p>
      </div>
      <div className="flex gap-3">
        <Button variant="outline" onClick={() => window.history.back()} size="sm">Volver</Button>
        <Button onClick={() => window.location.reload()} size="sm" className="gap-2">
          <RefreshCw className="h-3.5 w-3.5" />Recargar
        </Button>
      </div>
    </div>
  );
}
