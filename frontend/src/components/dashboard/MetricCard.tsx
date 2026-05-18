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
}

export function MetricCard({ title, value, icon: Icon, description, trend, colorClass = 'bg-primary/10 text-primary', loading }: MetricCardProps) {
  if (loading) {
    return (
      <Card>
        <CardContent className="p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="h-4 w-24 animate-pulse rounded bg-muted" />
            <div className="h-10 w-10 animate-pulse rounded-xl bg-muted" />
          </div>
          <div className="h-8 w-20 animate-pulse rounded bg-muted mb-2" />
          <div className="h-3 w-32 animate-pulse rounded bg-muted" />
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
            <Icon className="h-5 w-5" />
          </div>
        </div>
        <div className="flex items-end gap-2">
          <p className="text-3xl font-bold tracking-tight text-foreground">{value}</p>
          {trend !== undefined && (
            <span className={cn('flex items-center text-xs font-medium mb-1', trend >= 0 ? 'text-emerald-600' : 'text-red-500')}>
              {trend >= 0 ? <TrendingUp className="h-3 w-3 mr-0.5" /> : <TrendingDown className="h-3 w-3 mr-0.5" />}
              {Math.abs(trend)}%
            </span>
          )}
        </div>
        {description && <p className="text-xs text-muted-foreground mt-1">{description}</p>}
      </CardContent>
    </Card>
  );
}
