import { LucideIcon, TrendingUp, TrendingDown } from 'lucide-react';
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
  /** 'default' = icono derecha, valor grande (Dashboard).
   *  'compact' = icono izquierda, valor mediano (Reportes). */
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
      <Card>
        <CardContent className={cn('p-6', variant === 'compact' && 'pt-5 pb-4')}>
          {variant === 'default' ? (
            <>
              <div className="flex items-center justify-between mb-4">
                <div className="h-4 w-24 animate-pulse rounded bg-muted" />
                <div className="h-10 w-10 animate-pulse rounded-xl bg-muted" />
              </div>
              <div className="h-8 w-20 animate-pulse rounded bg-muted mb-2" />
              <div className="h-3 w-32 animate-pulse rounded bg-muted" />
            </>
          ) : (
            <div className="flex items-center gap-4">
              <div className="h-11 w-11 animate-pulse rounded-xl bg-muted shrink-0" />
              <div className="space-y-2">
                <div className="h-6 w-16 animate-pulse rounded bg-muted" />
                <div className="h-3 w-24 animate-pulse rounded bg-muted" />
              </div>
            </div>
          )}
        </CardContent>
      </Card>
    );
  }

  if (variant === 'compact') {
    return (
      <Card className="card-hover">
        <CardContent className="pt-5 pb-4 px-5">
          <div className="flex items-center gap-4">
            <div className={cn('flex h-11 w-11 shrink-0 items-center justify-center rounded-xl', colorClass)}>
              <Icon className="h-5 w-5" aria-hidden="true" />
            </div>
            <div>
              <p className="text-2xl font-bold text-foreground">{value}</p>
              <p className="text-sm text-muted-foreground">{title}</p>
              {description && (
                <p className="text-xs text-muted-foreground/70 mt-0.5">{description}</p>
              )}
            </div>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className="hover:shadow-md transition-shadow duration-200">
      <CardContent className="p-6">
        <div className="flex items-center justify-between mb-4">
          <p className="text-sm font-medium text-muted-foreground">{title}</p>
          <div className={cn('flex h-10 w-10 items-center justify-center rounded-xl', colorClass)}>
            <Icon className="h-5 w-5" aria-hidden="true" />
          </div>
        </div>
        <div className="flex items-end gap-2">
          <p className="text-3xl font-bold tracking-tight text-foreground">{value}</p>
          {trend !== undefined && (
            <span className={cn('flex items-center text-xs font-medium mb-1', trend >= 0 ? 'text-emerald-600' : 'text-red-500')}>
              {trend >= 0
                ? <TrendingUp className="h-3 w-3 mr-0.5" aria-hidden="true" />
                : <TrendingDown className="h-3 w-3 mr-0.5" aria-hidden="true" />
              }
              <span>{Math.abs(trend)}%</span>
            </span>
          )}
        </div>
        {description && <p className="text-xs text-muted-foreground mt-1">{description}</p>}
      </CardContent>
    </Card>
  );
}
