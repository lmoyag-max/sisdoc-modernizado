import { FileText, AlignLeft, Hash } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

interface Props {
  referencia:     string;
  cuerpo:         string;
  cuerpoError:    string | null;
  onReferencia:   (v: string) => void;
  onCuerpo:       (v: string) => void;
  disabled?:      boolean;
}

export function MemorandumFields({
  referencia, cuerpo, cuerpoError,
  onReferencia, onCuerpo, disabled = false,
}: Props) {
  return (
    <Card className="border-blue-200 dark:border-blue-800/40 bg-blue-50/30 dark:bg-blue-900/5">
      <CardHeader className="pb-3">
        <CardTitle className="text-base flex items-center gap-2">
          <FileText className="h-4 w-4 text-blue-600" />
          Contenido del Memorándum
          <span className="ml-auto text-xs font-normal text-blue-600 dark:text-blue-400">
            Generado automáticamente por SysDoc
          </span>
        </CardTitle>
        <CardDescription>
          El sistema generará el PDF del memorándum con los datos a continuación.
          Podrás previsualizar antes de confirmar.
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        {/* Referencia (opcional) */}
        <div className="space-y-1.5">
          <Label className="flex items-center gap-1.5 text-sm">
            <Hash className="h-3.5 w-3.5 text-muted-foreground" />
            Referencia
            <span className="text-muted-foreground font-normal">(opcional)</span>
          </Label>
          <Input
            value={referencia}
            onChange={(e) => onReferencia(e.target.value)}
            placeholder="Ej: Resolución N°123, Oficio GAB-2026-001..."
            maxLength={250}
            disabled={disabled}
          />
          <p className="text-xs text-muted-foreground text-right">{referencia.length}/250</p>
        </div>

        {/* Cuerpo del memorándum */}
        <div className="space-y-1.5">
          <Label className="flex items-center gap-1.5 text-sm">
            <AlignLeft className="h-3.5 w-3.5 text-muted-foreground" />
            Cuerpo del Memorándum <span className="text-destructive">*</span>
          </Label>
          <textarea
            rows={8}
            value={cuerpo}
            onChange={(e) => onCuerpo(e.target.value)}
            placeholder={`Por medio del presente, me dirijo a usted con el objeto de informar...\n\nSin otro particular, saluda atentamente,`}
            disabled={disabled}
            className={cn(
              'flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm',
              'placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-y min-h-[120px]',
              cuerpoError && 'border-destructive',
              disabled && 'opacity-50 cursor-not-allowed'
            )}
          />
          {cuerpoError && (
            <p className="text-xs text-destructive flex items-center gap-1">
              {cuerpoError}
            </p>
          )}
          <p className="text-xs text-muted-foreground">
            {cuerpo.length > 0 ? `${cuerpo.length} caracteres` : 'El cuerpo es el texto principal del memorándum'}
          </p>
        </div>
      </CardContent>
    </Card>
  );
}
