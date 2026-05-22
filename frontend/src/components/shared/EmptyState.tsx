import { FileX } from 'lucide-react';
import { cn } from '@/lib/utils';

interface EmptyStateProps {
  title?: string;
  description?: string;
  icon?: React.ComponentType<{ className?: string }>;
  action?: React.ReactNode;
  className?: string;
  size?: 'sm' | 'md' | 'lg';
}

export function EmptyState({
  title = 'Sin resultados',
  description = 'No se encontraron registros que coincidan con tu búsqueda.',
  icon: Icon = FileX,
  action,
  className,
  size = 'md',
}: EmptyStateProps) {
  const iconSize = size === 'sm' ? 'h-5 w-5' : size === 'lg' ? 'h-9 w-9' : 'h-7 w-7';
  const containerSize = size === 'sm' ? 'h-12 w-12' : size === 'lg' ? 'h-20 w-20' : 'h-16 w-16';
  const py = size === 'sm' ? 'py-10' : size === 'lg' ? 'py-24' : 'py-16';

  return (
    <div className={cn('flex flex-col items-center justify-center text-center animate-fade-in', py, className)}>
      <div className={cn(
        'relative flex shrink-0 items-center justify-center rounded-2xl mb-4',
        containerSize,
        'bg-gradient-to-br from-muted to-muted/40',
      )}>
        {/* Glow sutil detrás del icono */}
        <div className="absolute inset-0 rounded-2xl opacity-40"
          style={{ background: 'radial-gradient(circle, hsl(var(--primary) / 0.15) 0%, transparent 70%)' }}
        />
        <Icon className={cn(iconSize, 'text-muted-foreground relative')} />
      </div>
      <h3 className="text-sm font-semibold text-foreground mb-1">{title}</h3>
      <p className="text-xs text-muted-foreground max-w-[280px] leading-relaxed">{description}</p>
      {action && <div className="mt-5">{action}</div>}
    </div>
  );
}
