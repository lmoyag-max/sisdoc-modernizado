import { useState } from 'react';
import { useQuery, useMutation, useQueryClient, keepPreviousData } from '@tanstack/react-query';
import {
  BookOpen, Plus, Search, X, Save, RefreshCw, ChevronLeft, ChevronRight,
  Pencil, Trash2, Eye, User, Calendar, FileText, AlertTriangle, CheckCircle2, Archive, Eraser,
} from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { EmptyState } from '@/components/shared/EmptyState';
import {
  libroReferenciasApi, type Referencia, type FiltrosReferencia, type CrearReferenciaDto,
} from '@/lib/api/libroReferencias.api';
import { useModulos } from '@/hooks/useModulos';
import { useDebounce } from '@/hooks/useDebounce';
import { formatFechaHora, formatFecha, cn, truncate } from '@/lib/utils';
import { toast } from 'sonner';

const hoyISO = () => new Date().toISOString().slice(0, 10);

// ── Modal de crear/editar ────────────────────────────────────
function ReferenciaModal({
  referencia, onClose, onSaved,
}: {
  referencia: Referencia | 'nuevo';
  onClose: () => void;
  onSaved: () => void;
}) {
  const isEdit = referencia !== 'nuevo';
  const [form, setForm] = useState<CrearReferenciaDto>({
    nombreInteresado: isEdit ? referencia.nombreInteresado : '',
    tipoTramite:      isEdit ? referencia.tipoTramite      : '',
    fechaDocumento:   isEdit ? referencia.fechaDocumento.slice(0, 10) : hoyISO(),
    fechaRecepcion:   isEdit ? referencia.fechaRecepcion.slice(0, 10) : hoyISO(),
    observaciones:    isEdit ? (referencia.observaciones ?? '') : '',
  });
  // Tras crear con éxito, se muestra el código generado antes de cerrar.
  const [creado, setCreado] = useState<Referencia | null>(null);

  const mutation = useMutation({
    mutationFn: async () => {
      if (isEdit) return libroReferenciasApi.actualizar(referencia.id, form);
      return libroReferenciasApi.crear(form);
    },
    onSuccess: (data) => {
      onSaved();
      if (isEdit) {
        toast.success('Referencia actualizada');
        onClose();
      } else {
        setCreado(data);
      }
    },
    onError: (e: unknown) => {
      const data = (e as { response?: { data?: { error?: string; details?: Record<string, string[]> } } })?.response?.data;
      const detalles = data?.details ? Object.values(data.details).flat().join(' ') : '';
      toast.error(detalles || data?.error || 'Error al guardar');
    },
  });

  const valido = form.nombreInteresado.trim() && form.tipoTramite.trim() && form.fechaDocumento && form.fechaRecepcion;

  const registrarOtro = () => {
    setCreado(null);
    setForm({ nombreInteresado: '', tipoTramite: '', fechaDocumento: hoyISO(), fechaRecepcion: hoyISO(), observaciones: '' });
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 modal-overlay" onClick={creado ? onClose : onClose} />
      <div className="modal-panel relative w-full max-w-lg overflow-y-auto max-h-[90vh]">

        {creado ? (
          // ── Confirmación con el código destacado ────────────
          <div className="p-8 text-center space-y-5 animate-fade-in">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 dark:bg-emerald-900/30">
              <CheckCircle2 className="h-7 w-7 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div>
              <p className="text-xs text-muted-foreground uppercase tracking-wide mb-1">Referencia registrada</p>
              <p className="text-3xl font-bold tracking-tight text-foreground tabular-nums">{creado.codigo}</p>
              <p className="text-sm text-muted-foreground mt-2">{creado.nombreInteresado} · {creado.tipoTramite}</p>
            </div>
            <div className="flex justify-center gap-2 pt-2">
              <Button variant="outline" onClick={registrarOtro}>Registrar otro</Button>
              <Button onClick={onClose} className="btn-premium border-0 text-primary-foreground">Cerrar</Button>
            </div>
          </div>
        ) : (
          <>
            {/* Header */}
            <div className="flex items-center justify-between px-6 py-4 border-b border-border sticky top-0 bg-card z-10">
              <h2 className="text-base font-semibold">{isEdit ? 'Editar referencia' : 'Nuevo registro'}</h2>
              <Button variant="ghost" size="icon" onClick={onClose}><X className="h-4 w-4" /></Button>
            </div>

            <div className="p-6 space-y-5">
              {isEdit && (
                <div className="grid grid-cols-2 gap-4 p-3 rounded-lg border border-border bg-muted/30 text-sm">
                  <div>
                    <p className="text-xs text-muted-foreground">N° correlativo</p>
                    <p className="font-mono font-semibold text-foreground">{referencia.codigo}</p>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground">Registrado por</p>
                    <p className="font-medium text-foreground truncate">{referencia.usuarioCreador.nombre}</p>
                  </div>
                </div>
              )}

              <div className="space-y-1.5">
                <Label>Nombre del funcionario o interesado *</Label>
                <Input
                  value={form.nombreInteresado}
                  onChange={(e) => setForm((f) => ({ ...f, nombreInteresado: e.target.value.slice(0, 150) }))}
                  placeholder="Nombre completo"
                  maxLength={150}
                />
              </div>

              <div className="space-y-1.5">
                <Label>Tipo de trámite *</Label>
                <Input
                  value={form.tipoTramite}
                  onChange={(e) => setForm((f) => ({ ...f, tipoTramite: e.target.value.slice(0, 150) }))}
                  placeholder="Ej: Solicitud de permiso sin goce de remuneraciones"
                  maxLength={150}
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1.5">
                  <Label>Fecha del documento *</Label>
                  <Input
                    type="date"
                    value={form.fechaDocumento}
                    onChange={(e) => setForm((f) => ({ ...f, fechaDocumento: e.target.value }))}
                  />
                </div>
                <div className="space-y-1.5">
                  <Label>Fecha de recepción *</Label>
                  <Input
                    type="date"
                    value={form.fechaRecepcion}
                    onChange={(e) => setForm((f) => ({ ...f, fechaRecepcion: e.target.value }))}
                  />
                </div>
              </div>

              <div className="space-y-1.5">
                <Label>Observaciones</Label>
                <textarea
                  value={form.observaciones}
                  onChange={(e) => setForm((f) => ({ ...f, observaciones: e.target.value.slice(0, 1000) }))}
                  placeholder="Opcional"
                  rows={3}
                  maxLength={1000}
                  className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none"
                />
                <p className="text-[11px] text-muted-foreground text-right">{(form.observaciones ?? '').length}/1000</p>
              </div>
            </div>

            {/* Footer */}
            <div className="flex justify-end gap-2 px-6 pb-6">
              <Button variant="outline" onClick={onClose}>Cancelar</Button>
              <Button
                onClick={() => mutation.mutate()}
                disabled={mutation.isPending || !valido}
                className="gap-2 btn-premium border-0 text-primary-foreground"
              >
                {mutation.isPending ? <RefreshCw className="h-3.5 w-3.5 animate-spin" /> : <Save className="h-3.5 w-3.5" />}
                {isEdit ? 'Guardar cambios' : 'Registrar'}
              </Button>
            </div>
          </>
        )}
      </div>
    </div>
  );
}

// ── Modal de eliminación (exclusivo Superadministrador) ──────
function EliminarModal({ referencia, onClose, onDeleted }: { referencia: Referencia; onClose: () => void; onDeleted: () => void }) {
  const [motivo, setMotivo] = useState('');

  const mutation = useMutation({
    mutationFn: () => libroReferenciasApi.eliminar(referencia.id, motivo),
    onSuccess: () => {
      toast.success(`Referencia ${referencia.codigo} eliminada`);
      onDeleted();
      onClose();
    },
    onError: (e: unknown) => {
      const msg = (e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'No se pudo eliminar';
      toast.error(msg);
    },
  });

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 modal-overlay" onClick={onClose} />
      <div className="modal-panel relative w-full max-w-md">
        <div className="flex items-center gap-3 px-5 py-4 border-b border-border">
          <span className="icon-3d icon-3d-red flex h-9 w-9 shrink-0 items-center justify-center">
            <AlertTriangle className="h-4 w-4 text-white" />
          </span>
          <div>
            <p className="text-sm font-semibold text-foreground">Eliminar referencia</p>
            <p className="text-xs text-muted-foreground">Esta acción es exclusiva del Superadministrador</p>
          </div>
        </div>

        <div className="p-5 space-y-4">
          <div className="rounded-lg border border-border bg-muted/30 p-3 text-sm space-y-1">
            <p className="font-mono font-semibold text-foreground">{referencia.codigo}</p>
            <p className="text-muted-foreground">{referencia.nombreInteresado} · {referencia.tipoTramite}</p>
          </div>

          <p className="text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-lg px-3 py-2">
            El número de <strong>{referencia.codigo}</strong> quedará disponible y podrá asignarse a un nuevo
            registro. Esta referencia quedará marcada como eliminada, conservando todos sus datos originales y
            el código con el que fue emitida — seguirá disponible para auditoría, pero dejará de aparecer en el listado.
          </p>

          <div className="space-y-1.5">
            <Label>Motivo de eliminación *</Label>
            <textarea
              value={motivo}
              onChange={(e) => setMotivo(e.target.value.slice(0, 500))}
              placeholder="Explica por qué se elimina este registro (mínimo 5 caracteres)"
              rows={3}
              className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none"
            />
          </div>
        </div>

        <div className="flex justify-end gap-2 px-5 pb-5">
          <Button variant="outline" onClick={onClose}>Cancelar</Button>
          <Button
            variant="destructive"
            onClick={() => mutation.mutate()}
            disabled={mutation.isPending || motivo.trim().length < 5}
            className="gap-2"
          >
            {mutation.isPending ? <RefreshCw className="h-3.5 w-3.5 animate-spin" /> : <Trash2 className="h-3.5 w-3.5" />}
            Eliminar
          </Button>
        </div>
      </div>
    </div>
  );
}

// ── Modal de eliminación definitiva (nivel 2 — exclusivo Superadministrador) ──
// Endpoint y componente separados de EliminarModal a propósito: son dos
// acciones distintas (lógica vs. física) y no deben confundirse ni en el
// backend ni en la interfaz.
function EliminarDefinitivoModal({
  referencia, onClose, onDeleted,
}: {
  referencia: Referencia;
  onClose: () => void;
  onDeleted: () => void;
}) {
  const [motivo, setMotivo] = useState('');
  const [confirmacion, setConfirmacion] = useState('');

  const mutation = useMutation({
    mutationFn: () => libroReferenciasApi.eliminarDefinitivo(referencia.id, motivo, confirmacion),
    onSuccess: () => {
      toast.success(`Referencia ${referencia.codigo} eliminada definitivamente`);
      onDeleted();
      onClose();
    },
    onError: (e: unknown) => {
      const msg = (e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'No se pudo eliminar definitivamente';
      toast.error(msg);
    },
  });

  const valido = motivo.trim().length >= 5 && confirmacion === 'ELIMINAR';

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 modal-overlay" onClick={onClose} />
      <div className="modal-panel relative w-full max-w-md">
        <div className="flex items-center gap-3 px-5 py-4 border-b border-border">
          <span className="icon-3d icon-3d-red flex h-9 w-9 shrink-0 items-center justify-center">
            <Eraser className="h-4 w-4 text-white" />
          </span>
          <div>
            <p className="text-sm font-semibold text-foreground">Eliminar definitivamente</p>
            <p className="text-xs text-muted-foreground">Esta acción es exclusiva del Superadministrador</p>
          </div>
        </div>

        <div className="p-5 space-y-4">
          <div className="rounded-lg border border-border bg-muted/30 p-3 text-sm space-y-1.5">
            <p className="font-mono font-semibold text-foreground">{referencia.codigo}</p>
            <p className="text-foreground">{referencia.nombreInteresado} · {referencia.tipoTramite}</p>
            <div className="grid grid-cols-2 gap-2 pt-1 text-xs text-muted-foreground">
              <p>Recepción: {formatFecha(referencia.fechaRecepcion)}</p>
              {referencia.eliminacion?.fecha && <p>Eliminada: {formatFecha(referencia.eliminacion.fecha)}</p>}
            </div>
          </div>

          <div className="text-xs text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg px-3 py-2 space-y-1">
            <p>Esta referencia desaparecerá también de la vista de eliminados.</p>
            <p className="font-semibold">Esta acción no podrá deshacerse.</p>
          </div>

          <div className="space-y-1.5">
            <Label>Motivo de eliminación definitiva *</Label>
            <textarea
              value={motivo}
              onChange={(e) => setMotivo(e.target.value.slice(0, 500))}
              placeholder="Explica por qué se elimina definitivamente este registro (mínimo 5 caracteres)"
              rows={3}
              className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring resize-none"
            />
          </div>

          <div className="space-y-1.5">
            <Label>Escribe <span className="font-mono font-semibold">ELIMINAR</span> para confirmar *</Label>
            <Input
              value={confirmacion}
              onChange={(e) => setConfirmacion(e.target.value)}
              placeholder="ELIMINAR"
              className="font-mono"
              autoComplete="off"
            />
          </div>
        </div>

        <div className="flex justify-end gap-2 px-5 pb-5">
          <Button variant="outline" onClick={onClose}>Cancelar</Button>
          <Button
            variant="destructive"
            onClick={() => mutation.mutate()}
            disabled={mutation.isPending || !valido}
            className="gap-2"
          >
            {mutation.isPending ? <RefreshCw className="h-3.5 w-3.5 animate-spin" /> : <Eraser className="h-3.5 w-3.5" />}
            Eliminar definitivamente
          </Button>
        </div>
      </div>
    </div>
  );
}

// ── Página principal ─────────────────────────────────────────
export function LibroReferenciasPage() {
  const { isAdmin } = useModulos();
  const qc = useQueryClient();

  const [search, setSearch]           = useState('');
  const debouncedSearch                = useDebounce(search, 300);
  const [pagina, setPagina]           = useState(1);
  const [verEliminados, setVerEliminados] = useState(false);
  const [modal, setModal]             = useState<'nuevo' | Referencia | null>(null);
  const [aEliminar, setAEliminar]     = useState<Referencia | null>(null);
  const [aEliminarDefinitivo, setAEliminarDefinitivo] = useState<Referencia | null>(null);
  const [detalle, setDetalle]         = useState<Referencia | null>(null);

  const filtros: FiltrosReferencia = { q: debouncedSearch || undefined, pagina, porPagina: 15, orden: 'correlativo_desc' };

  const { data, isLoading, isFetching } = useQuery({
    queryKey: verEliminados ? ['libro-referencias-eliminados', filtros] : ['libro-referencias', filtros],
    queryFn:  () => (verEliminados ? libroReferenciasApi.listarEliminados(filtros) : libroReferenciasApi.listar(filtros)),
    enabled:  !verEliminados || isAdmin,
    placeholderData: keepPreviousData,
    staleTime: 15_000,
  });

  const { data: metricas } = useQuery({
    queryKey: ['libro-referencias-metricas'],
    queryFn:  libroReferenciasApi.metricas,
    staleTime: 30_000,
  });

  const invalidar = () => {
    qc.invalidateQueries({ queryKey: ['libro-referencias'] });
    qc.invalidateQueries({ queryKey: ['libro-referencias-eliminados'] });
    qc.invalidateQueries({ queryKey: ['libro-referencias-metricas'] });
  };

  const referencias = data?.data ?? [];
  const meta = data?.meta;

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-3">
          <span className="icon-3d icon-3d-sky flex h-11 w-11 shrink-0 items-center justify-center">
            <BookOpen className="h-5 w-5 text-white" />
          </span>
          <div>
            <h1 className="text-xl sm:text-2xl font-bold text-foreground">Libro de Referencias</h1>
            <p className="text-sm text-muted-foreground mt-0.5">
              Registro correlativo de documentos y trámites de funcionarios e interesados — Oficina de Partes
            </p>
          </div>
        </div>
        {!verEliminados && (
          <Button onClick={() => setModal('nuevo')} className="btn-premium gap-2 shrink-0 border-0 text-primary-foreground">
            <Plus className="h-4 w-4" />Nuevo registro
          </Button>
        )}
      </div>

      {/* Tarjetas de resumen */}
      <div className={cn('grid gap-4 grid-cols-2', isAdmin ? 'sm:grid-cols-4' : 'sm:grid-cols-3')}>
        <MetricCard label="Referencias del año" value={metricas?.referenciasAnioActual} icon={FileText} accent="indigo" />
        <MetricCard label="Registradas hoy" value={metricas?.registradasHoy} icon={Calendar} accent="amber" />
        <MetricCard label="Registradas este mes" value={metricas?.registradasMes} icon={Calendar} accent="emerald" />
        {isAdmin && (
          <button type="button" onClick={() => { setVerEliminados((v) => !v); setPagina(1); }} className="text-left">
            <MetricCard
              label="Eliminados"
              value={metricas?.eliminadas}
              icon={Archive}
              accent="red"
              active={verEliminados}
            />
          </button>
        )}
      </div>

      {verEliminados && (
        <div className="flex items-center justify-between rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/20 px-4 py-2.5">
          <p className="text-sm text-red-700 dark:text-red-400">
            Viendo registros <strong>eliminados</strong> — exclusivo Superadministrador, solo para fines de auditoría.
          </p>
          <Button variant="ghost" size="sm" onClick={() => { setVerEliminados(false); setPagina(1); }} className="text-xs shrink-0">
            Volver a vigentes
          </Button>
        </div>
      )}

      {/* Buscador */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col sm:flex-row gap-3">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Buscar por correlativo, nombre o tipo de trámite..."
                className="pl-9"
                value={search}
                onChange={(e) => { setSearch(e.target.value); setPagina(1); }}
              />
            </div>
            {search && (
              <Button variant="ghost" size="sm" onClick={() => setSearch('')} className="text-xs shrink-0">
                Limpiar
              </Button>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Tabla */}
      <Card className="overflow-hidden">
        <div className="overflow-x-auto">
          <div className="min-w-[900px]">
            <div className="table-head-modern px-6 py-3.5">
              <div className="grid grid-cols-12 gap-4">
                <div className="col-span-2">Correlativo</div>
                <div className="col-span-2">Usuario</div>
                <div className="col-span-2">Interesado</div>
                <div className="col-span-2">Tipo de trámite</div>
                <div className="col-span-1">F. documento</div>
                <div className="col-span-1">F. recepción</div>
                <div className="col-span-1">Observaciones</div>
                <div className="col-span-1 text-right">Acciones</div>
              </div>
            </div>

            <CardContent className="p-0">
              {isLoading ? (
                <div className="divide-y">
                  {Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="grid grid-cols-12 gap-4 px-6 py-4 items-center">
                      <Skeleton className="col-span-2 h-4" />
                      <Skeleton className="col-span-2 h-4" />
                      <Skeleton className="col-span-2 h-4" />
                      <Skeleton className="col-span-2 h-4" />
                      <Skeleton className="col-span-1 h-4" />
                      <Skeleton className="col-span-1 h-4" />
                      <Skeleton className="col-span-2 h-4" />
                    </div>
                  ))}
                </div>
              ) : referencias.length === 0 ? (
                <EmptyState
                  icon={verEliminados ? Archive : BookOpen}
                  title={verEliminados ? 'Sin registros eliminados' : 'Sin registros'}
                  description={
                    search
                      ? 'No se encontraron referencias con los filtros seleccionados.'
                      : verEliminados
                        ? 'No hay referencias eliminadas.'
                        : 'Aún no hay referencias registradas. Crea la primera con "Nuevo registro".'
                  }
                  action={search ? <Button variant="outline" onClick={() => setSearch('')}>Limpiar búsqueda</Button> : undefined}
                />
              ) : (
                <div className={cn('divide-y', isFetching && 'opacity-60 transition-opacity')}>
                  {referencias.map((r) => (
                    <div key={r.id} className="grid grid-cols-12 gap-4 px-6 py-3.5 items-center hover:bg-muted/30 transition-colors group">
                      <div className="col-span-2">
                        <span className="text-xs font-mono font-semibold text-foreground">{r.codigo}</span>
                      </div>
                      <div className="col-span-2 min-w-0 flex items-center gap-1.5">
                        <User className="h-3 w-3 text-muted-foreground shrink-0" />
                        <span className="text-xs text-muted-foreground truncate">{r.usuarioCreador.nombre}</span>
                      </div>
                      <div className="col-span-2 min-w-0">
                        <p className="text-sm font-medium text-foreground truncate">{r.nombreInteresado}</p>
                      </div>
                      <div className="col-span-2 min-w-0">
                        <p className="text-xs text-muted-foreground truncate">{r.tipoTramite}</p>
                      </div>
                      <div className="col-span-1">
                        <span className="text-xs text-muted-foreground">{formatFecha(r.fechaDocumento)}</span>
                      </div>
                      <div className="col-span-1">
                        <span className="text-xs text-muted-foreground">{formatFecha(r.fechaRecepcion)}</span>
                      </div>
                      <div className="col-span-1 min-w-0">
                        <span className="text-xs text-muted-foreground truncate block" title={r.observaciones ?? ''}>
                          {r.observaciones ? truncate(r.observaciones, 30) : '—'}
                        </span>
                      </div>
                      <div className="col-span-1 flex items-center justify-end gap-1">
                        <Button variant="ghost" size="icon" className="h-7 w-7" title="Ver detalle" onClick={() => setDetalle(r)}>
                          <Eye className="h-3.5 w-3.5" />
                        </Button>
                        {!verEliminados && (
                          <Button variant="ghost" size="icon" className="h-7 w-7" title="Editar" onClick={() => setModal(r)}>
                            <Pencil className="h-3.5 w-3.5" />
                          </Button>
                        )}
                        {isAdmin && !verEliminados && (
                          <Button
                            variant="ghost" size="icon"
                            className="h-7 w-7 text-muted-foreground hover:text-destructive"
                            title="Eliminar"
                            onClick={() => setAEliminar(r)}
                          >
                            <Trash2 className="h-3.5 w-3.5" />
                          </Button>
                        )}
                        {isAdmin && verEliminados && (
                          <Button
                            variant="ghost" size="icon"
                            className="h-7 w-7 text-muted-foreground hover:text-destructive"
                            title="Eliminar definitivamente"
                            onClick={() => setAEliminarDefinitivo(r)}
                          >
                            <Eraser className="h-3.5 w-3.5" />
                          </Button>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </div>
        </div>

        {meta && meta.totalPaginas > 1 && (
          <div className="flex items-center justify-between px-6 py-4 border-t bg-muted/10">
            <p className="text-xs text-muted-foreground">
              Mostrando {(meta.pagina - 1) * meta.porPagina + 1}–{Math.min(meta.pagina * meta.porPagina, meta.total)} de {meta.total.toLocaleString('es-CL')}
            </p>
            <div className="flex items-center gap-2">
              <button type="button" className="pagination-pill" disabled={meta.pagina <= 1} onClick={() => setPagina((p) => p - 1)} aria-label="Página anterior"><ChevronLeft className="h-3.5 w-3.5" /></button>
              <span className="text-xs text-muted-foreground px-2 tabular-nums">{meta.pagina} / {meta.totalPaginas}</span>
              <button type="button" className="pagination-pill" disabled={meta.pagina >= meta.totalPaginas} onClick={() => setPagina((p) => p + 1)} aria-label="Página siguiente"><ChevronRight className="h-3.5 w-3.5" /></button>
            </div>
          </div>
        )}
      </Card>

      {modal && <ReferenciaModal referencia={modal} onClose={() => setModal(null)} onSaved={invalidar} />}
      {aEliminar && <EliminarModal referencia={aEliminar} onClose={() => setAEliminar(null)} onDeleted={invalidar} />}
      {aEliminarDefinitivo && (
        <EliminarDefinitivoModal referencia={aEliminarDefinitivo} onClose={() => setAEliminarDefinitivo(null)} onDeleted={invalidar} />
      )}
      {detalle && <DetalleModal referencia={detalle} onClose={() => setDetalle(null)} />}
    </div>
  );
}

function MetricCard({
  label, value, icon: Icon, accent, active,
}: {
  label: string;
  value: number | undefined;
  icon: React.ComponentType<{ className?: string }>;
  accent: 'indigo' | 'amber' | 'emerald' | 'red';
  active?: boolean;
}) {
  return (
    <div className={cn(
      'kpi-premium p-4 sm:p-5 rounded-2xl border transition-colors',
      active ? 'border-red-300 dark:border-red-900 ring-2 ring-red-200 dark:ring-red-900/50' : 'border-transparent',
    )}>
      <div className={cn('icon-3d h-10 w-10 shrink-0 mb-3', `icon-3d-${accent}`)}>
        <Icon className="h-4.5 w-4.5 text-white relative z-10" />
      </div>
      <div className="text-2xl font-bold tracking-tight text-foreground tabular-nums">
        {value != null ? value.toLocaleString('es-CL') : <Skeleton className="h-7 w-10" />}
      </div>
      <p className="text-xs text-muted-foreground mt-1 font-medium">{label}</p>
    </div>
  );
}

function DetalleModal({ referencia, onClose }: { referencia: Referencia; onClose: () => void }) {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 modal-overlay" onClick={onClose} />
      <div className="modal-panel relative w-full max-w-lg overflow-y-auto max-h-[90vh]">
        <div className="flex items-center justify-between px-6 py-4 border-b border-border sticky top-0 bg-card z-10">
          <div>
            <h2 className="text-base font-semibold font-mono">{referencia.codigo}</h2>
            {referencia.condicion === 'ELIMINADO' && <Badge variant="secondary" className="mt-1 bg-red-100 text-red-700 dark:bg-red-950/30 dark:text-red-400 border-transparent">Eliminado</Badge>}
          </div>
          <Button variant="ghost" size="icon" onClick={onClose}><X className="h-4 w-4" /></Button>
        </div>
        <div className="p-6 space-y-4 text-sm">
          <DetalleRow label="Registrado por" value={referencia.usuarioCreador.nombre} />
          <DetalleRow label="Nombre del funcionario o interesado" value={referencia.nombreInteresado} />
          <DetalleRow label="Tipo de trámite" value={referencia.tipoTramite} />
          <div className="grid grid-cols-2 gap-4">
            <DetalleRow label="Fecha del documento" value={formatFecha(referencia.fechaDocumento)} />
            <DetalleRow label="Fecha de recepción" value={formatFecha(referencia.fechaRecepcion)} />
          </div>
          <DetalleRow label="Observaciones" value={referencia.observaciones || '—'} multiline />
          <DetalleRow label="Registrado el" value={formatFechaHora(referencia.fechaCreacion)} />
          {referencia.eliminacion && (
            <div className="rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/20 p-3 space-y-1">
              <p className="text-xs font-semibold text-red-700 dark:text-red-400">Eliminado</p>
              <p className="text-xs text-red-600 dark:text-red-400">Por {referencia.eliminacion.usuario} — {referencia.eliminacion.fecha ? formatFechaHora(referencia.eliminacion.fecha) : ''}</p>
              <p className="text-xs text-red-600 dark:text-red-400">Motivo: {referencia.eliminacion.motivo}</p>
            </div>
          )}
        </div>
        <div className="flex justify-end px-6 pb-6">
          <Button variant="outline" onClick={onClose}>Cerrar</Button>
        </div>
      </div>
    </div>
  );
}

function DetalleRow({ label, value, multiline }: { label: string; value: string; multiline?: boolean }) {
  return (
    <div>
      <p className="text-xs text-muted-foreground">{label}</p>
      <p className={cn('text-foreground font-medium', multiline && 'whitespace-pre-wrap')}>{value}</p>
    </div>
  );
}
