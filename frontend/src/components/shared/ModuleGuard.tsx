import { Navigate } from 'react-router-dom';
import { AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useModulos } from '@/hooks/useModulos';

interface Props {
  modulo: string;
  children: React.ReactNode;
}

export function ModuleGuard({ modulo, children }: Props) {
  const { puede } = useModulos();

  if (puede(modulo)) return <>{children}</>;

  return (
    <div className="flex flex-col items-center justify-center py-24 gap-4 text-center animate-fade-in">
      <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-destructive/10">
        <AlertCircle className="h-8 w-8 text-destructive/60" />
      </div>
      <div>
        <h2 className="text-lg font-bold text-foreground">Acceso denegado</h2>
        <p className="text-sm text-muted-foreground mt-1 max-w-sm">
          No tienes permisos para acceder a este módulo. Contacta al administrador.
        </p>
      </div>
      <Button variant="outline" onClick={() => window.history.back()}>Volver</Button>
    </div>
  );
}

// Guard de solo admin — para módulos administrativos críticos
export function AdminGuard({ children }: { children: React.ReactNode }) {
  const { isAdmin } = useModulos();
  if (isAdmin) return <>{children}</>;
  return <Navigate to="/dashboard" replace />;
}
