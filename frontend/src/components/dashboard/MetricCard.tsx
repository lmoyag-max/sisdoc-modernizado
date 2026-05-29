import { LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface MetricCardProps {
  title: string;
  value: number | string;
  icon: LucideIcon;
  description?: string;
  trend?: number;
  colorClass?: string;
  loading?: boolean;
  /** 'default' = icono derecha, valor grande (legacy layout).
   *  'compact' = icono izquierda, valor mediano (Dashboard / Reportes). */
  variant?: 'default' | 'compact';
}

export function MetricCard({
  title,
  value,
  icon: Icon,
  description,
  trend,
  colorClass = 'bg-primary/10 text-primary',
  loading,
  variant = 'default',
}: MetricCardProps) {

  if (loading) {
    return (
      <Card className="card-executive">
        <CardContent className={cn('p-5', variant === 'compact' && 'pt-5 pb-4 px-4')}>
          {variant === 'default' ? (
            <>
              <div className="flex items-center justify-between mb-4">
                <div className="h-3.5 w-24 animate-pulse rounded bg-muted" />
                <div className="h-10 w-10 animate-pulse rounded-xl bg-muted" />
              </div>
              <div className="h-8 w-20 animate-pulse rounded bg-muted mb-1.5" />
              <div className="h-2.5 w-32 animate-pulse rounded bg-muted" />
            </>
          ) : (
            <div className="flex items-center gap-3">
              <div className="h-10 w-10 animate-pulse rounded-xl bg-muted shrink-0" />
              <div className="space-y-2">
                <div className="h-5 w-14 animate-pulse rounded bg-muted" />
                <div className="h-2.5 w-20 animate-pulse rounded bg-muted" />
              </div>
            </div>
          )}
        </CardContent>
      </Card>
    );
  }

  if (variant === 'compact') {
    return (
      <Card className="card-executive group cursor-default">
        <CardContent className="pt-4 pb-4 px-4">
          <div className="flex items-center gap-3">
            <div className={cn(
              'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-105',
              colorClass,
            )}>
              <Icon className="h-4.5 w-4.5" aria-hidden="true" style={{ width: '1.1rem', height: '1.1rem' }} />
            </div>
            <div className="min-w-0">
              <p className="text-xl font-bold text-foreground tabular-nums animate-number leading-tight">
                {typeof value === 'number' ? value.toLocaleString('es-CL') : value}
              </p>
              <p className="text-xs font-medium text-muted-foreground leading-tight mt-0.5 truncate">{title}</p>
              {description && <p className="text-[10px] text-muted-foreground/60 leading-tight truncate">{description}</p>}
            </div>
          </div>
        </CardContent>
      </Card>
    );
  }

  // Default variant (Dashboard legacy + standalone)
  return (
    <Card className="card-executive group cursor-default">
      <CardContent className="p-5">
        <div className="flex items-start justify-between mb-3">
          <p className="text-sm font-medium text-muted-foreground">{title}</p>
          <div className={cn(
            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-105',
            colorClass,
          )}>
            <Icon className="h-5 w-5" aria-hidden="true" />
          </div>
        </div>
        <div className="flex items-end gap-2">
          <p className="text-3xl font-bold tracking-tight text-foreground tabular-nums animate-number">
            {typeof value === 'number' ? value.toLocaleString('es-CL') : value}
          </p>
          {trend !== undefined && (
            <span className={cn(
              'flex items-center text-xs font-semibold mb-1 px-1.5 py-0.5 rounded-md',
              trend >= 0
                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
            )}>
              {trend >= 0 ? '▲' : '▼'} {Math.abs(trend)}%
            </span>
          )}
        </div>
        {description && <p className="text-xs text-muted-foreground mt-1">{description}</p>}
      </CardContent>
    </Card>
  );
}
