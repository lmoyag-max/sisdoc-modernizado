import { Link } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { FileQuestion } from 'lucide-react';

export function NotFoundPage() {
  return (
    <div className="flex flex-col items-center justify-center min-h-screen gap-6 text-center p-6">
      <div className="flex h-20 w-20 items-center justify-center rounded-2xl bg-muted">
        <FileQuestion className="h-10 w-10 text-muted-foreground" />
      </div>
      <div>
        <h1 className="text-4xl font-bold text-foreground">404</h1>
        <p className="text-xl font-semibold text-foreground mt-1">Página no encontrada</p>
        <p className="text-muted-foreground mt-2 max-w-sm">
          La página que buscas no existe o fue movida.
        </p>
      </div>
      <Link to="/dashboard">
        <Button>Volver al dashboard</Button>
      </Link>
    </div>
  );
}
