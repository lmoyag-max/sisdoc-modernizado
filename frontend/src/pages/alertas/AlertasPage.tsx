import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Bell, Send, RefreshCw, CheckCircle2,
  XCircle, Clock, AlertTriangle, Mail, Settings2,
  FileText, Building2, ChevronDown, ChevronRight, Power,
  Plus, UserCheck, Info, FlaskConical,
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';
import {
  alertasApi, type DocumentoPendiente, type AlertaLog,
  type ResumenDestinatarios, type ResultadoServicio,
} from '@/lib/api/alertas.api';

// ── Helpers ──────────────────────────────────────────────────

function estadoBadge(estado: string) {
  if (estado === 'ok')         return <Badge className="bg-emerald-100 text-emerald-700 border-0">Enviado</Badge>;
  if (estado === 'error')      return <Badge className="bg-red-100 text-red-700 border-0">Error</Badge>;
  if (estado === 'sin_correo') return <Badge className="bg-amber-100 text-amber-700 border-0">Sin email</Badge>;
  if (estado === 'sin_docs')   return <Badge className="bg-slate-100 text-slate-600 border-0">Sin pendientes</Badge>;
  return <Badge variant="outline">{estado}</Badge>;
}

function compromisoBadge(idTipo: number) {
  if (idTipo === 3) return <Badge className="bg-red-100 text-red-700 border-0 text-[10px]">Urgente</Badge>;
  if (idTipo === 2) return <Badge className="bg-amber-100 text-amber-700 border-0 text-[10px]">Normal</Badge>;
  return <Badge className="bg-slate-100 text-slate-500 border-0 text-[10px]">Sin compromiso</Badge>;
}

function fmtFecha(iso: string | null) {
  if (!iso) return '—';
  return new Date(iso).toLocaleString('es-CL', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

// ── Panel: Configuración del scheduler ───────────────────────

function ConfigPanel() {
  const qc = useQueryClient();
  const { data: config, isLoading } = useQuery({
    queryKey: ['alertas-config'],
    queryFn:  alertasApi.getConfig,
  });

  const [horarioInput, setHorarioInput] = useState('');
  const horarios = config?.horarios ?? [];

  const save = useMutation({
    mutationFn: (body: { activo: boolean; horarios: string[] }) => alertasApi.updateConfig(body),
    onSuccess:  () => { toast.success('Configuración guardada'); qc.invalidateQueries({ queryKey: ['alertas-config'] }); },
    onError:    () => toast.error('Error al guardar configuración'),
  });

  const addHorario = () => {
    const h = horarioInput.trim();
    if (!/^\d{2}:\d{2}$/.test(h)) { toast.error('Formato inválido — usa HH:MM'); return; }
    if (horarios.includes(h))     { toast.error('Ese horario ya existe'); return; }
    if (horarios.length >= 4)     { toast.error('Máximo 4 horarios'); return; }
    save.mutate({ activo: config?.activo ?? true, horarios: [...horarios, h].sort() });
    setHorarioInput('');
  };

  const removeHorario = (h: string) => {
    if (horarios.length <= 1) { toast.error('Debe haber al menos un horario'); return; }
    save.mutate({ activo: config?.activo ?? true, horarios: horarios.filter((x) => x !== h) });
  };

  const toggleActivo = () => {
    if (!config) return;
    save.mutate({ activo: !config.activo, horarios: config.horarios });
  };

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Settings2 className="h-4 w-4 text-primary" />
            <CardTitle className="text-base">Configuración del scheduler</CardTitle>
          </div>
          {!isLoading && config && (
            <Button
              variant={config.activo ? 'default' : 'outline'}
              size="sm"
              className={cn('gap-2', config.activo ? 'bg-emerald-600 hover:bg-emerald-700' : '')}
              onClick={toggleActivo}
              disabled={save.isPending}
            >
              <Power className="h-3.5 w-3.5" />
              {config.activo ? 'Activado' : 'Desactivado'}
            </Button>
          )}
        </div>
        <CardDescription>El scheduler revisa cada 60 s y envía en los horarios configurados</CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div>
          <p className="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-2">
            Horarios de envío automático
          </p>
          <div className="flex flex-wrap gap-2 mb-3">
            {isLoading
              ? [1, 2].map((i) => <div key={i} className="h-7 w-16 rounded-full bg-muted animate-pulse" />)
              : horarios.map((h) => (
                  <span key={h} className="inline-flex items-center gap-1.5 bg-primary/10 text-primary text-sm font-mono font-medium px-3 py-1 rounded-full">
                    <Clock className="h-3 w-3" />
                    {h}
                    <button
                      onClick={() => removeHorario(h)}
                      className="ml-0.5 hover:text-destructive transition-colors"
                      title="Eliminar horario"
                    >
                      <XCircle className="h-3.5 w-3.5" />
                    </button>
                  </span>
                ))}
          </div>
          <div className="flex gap-2">
            <Input
              className="w-28 font-mono"
              placeholder="HH:MM"
              value={horarioInput}
              onChange={(e) => setHorarioInput(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && addHorario()}
              maxLength={5}
            />
            <Button size="sm" variant="outline" onClick={addHorario} disabled={save.isPending} className="gap-2">
              <Plus className="h-3.5 w-3.5" />
              Agregar
            </Button>
          </div>
          <p className="text-xs text-muted-foreground mt-2">
            Ventana de ±25 min — no reenvía si ya envió en ese slot hoy. Máximo 4 horarios.
          </p>
        </div>
      </CardContent>
    </Card>
  );
}

// ── Panel: Destinatarios automáticos ─────────────────────────
// Informativo, solo lectura. Muestra usuarios del sistema con email por servicio.

function DestinatariosPanel({ serviciosConDocs }: { serviciosConDocs: Set<number> }) {
  const [expanded, setExpanded] = useState<Record<number, boolean>>({});

  const { data: destinatarios = [], isLoading, refetch, isFetching } = useQuery({
    queryKey: ['alertas-destinatarios'],
    queryFn:  () => alertasApi.getDestinatarios(),
  });

  const toggle = (id: number) => setExpanded((p) => ({ ...p, [id]: !p[id] }));

  // Servicios con docs pendientes pero sin usuarios configurados con email
  const servicesSinEmail = [...serviciosConDocs].filter(
    (id) => !destinatarios.some((d) => d.idDependencia === id)
  );

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <UserCheck className="h-4 w-4 text-primary" />
            <CardTitle className="text-base">Destinatarios automáticos</CardTitle>
          </div>
          <Button size="sm" variant="outline" onClick={() => refetch()} disabled={isFetching} className="gap-2">
            <RefreshCw className={cn('h-3.5 w-3.5', isFetching && 'animate-spin')} />
            Actualizar
          </Button>
        </div>
        <CardDescription>
          Usuarios del sistema con correo electrónico configurado, agrupados por servicio.
          Las alertas se envían automáticamente a estos destinatarios.
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-3">

        {/* Aviso informativo */}
        <div className="flex items-start gap-2.5 rounded-lg bg-blue-50 border border-blue-100 p-3">
          <Info className="h-4 w-4 text-blue-500 shrink-0 mt-0.5" />
          <p className="text-xs text-blue-700">
            Los destinatarios se obtienen dinámicamente desde los usuarios del sistema.
            Para agregar o quitar destinatarios, gestiona el correo de los usuarios en{' '}
            <a href="/admin/usuarios" className="font-semibold underline-offset-2 hover:underline">
              Administración → Usuarios
            </a>.
          </p>
        </div>

        {/* Servicios con docs pero sin email configurado */}
        {servicesSinEmail.length > 0 && (
          <div className="flex items-start gap-2.5 rounded-lg bg-amber-50 border border-amber-200 p-3">
            <AlertTriangle className="h-4 w-4 text-amber-500 shrink-0 mt-0.5" />
            <div>
              <p className="text-xs font-medium text-amber-800 mb-1">
                Servicios con documentos pendientes sin destinatarios configurados:
              </p>
              <p className="text-xs text-amber-700">
                Estos servicios no recibirán alertas hasta que sus usuarios tengan correo asignado.
              </p>
            </div>
          </div>
        )}

        {/* Lista por servicio */}
        {isLoading
          ? [1, 2, 3].map((i) => <div key={i} className="h-12 rounded-lg bg-muted animate-pulse" />)
          : destinatarios.length === 0
            ? (
              <div className="flex flex-col items-center gap-2 py-8 text-muted-foreground">
                <Mail className="h-8 w-8 opacity-30" />
                <p className="text-sm">Ningún usuario tiene correo electrónico configurado</p>
                <a href="/admin/usuarios">
                  <Button size="sm" variant="outline" className="gap-2 mt-1">
                    <UserCheck className="h-3.5 w-3.5" />Configurar en Usuarios
                  </Button>
                </a>
              </div>
            )
            : destinatarios.map((srv: ResumenDestinatarios) => {
              const open      = expanded[srv.idDependencia] ?? false;
              const tieneDocs = serviciosConDocs.has(srv.idDependencia);
              return (
                <div key={srv.idDependencia} className="rounded-lg border border-border overflow-hidden">
                  <button
                    className="w-full flex items-center gap-3 px-4 py-3 bg-muted/20 hover:bg-muted/40 transition-colors text-left"
                    onClick={() => toggle(srv.idDependencia)}
                  >
                    {open
                      ? <ChevronDown className="h-4 w-4 text-muted-foreground shrink-0" />
                      : <ChevronRight className="h-4 w-4 text-muted-foreground shrink-0" />}
                    <Building2 className="h-4 w-4 text-muted-foreground shrink-0" />
                    <span className="text-sm font-medium flex-1 text-left">{srv.nombreServicio}</span>
                    <div className="flex items-center gap-2 shrink-0">
                      {tieneDocs && (
                        <Badge className="bg-amber-100 text-amber-700 border-0 text-[10px]">
                          Con pendientes
                        </Badge>
                      )}
                      <Badge variant="outline" className="text-[10px]">
                        {srv.totalUsuarios} usuario(s)
                      </Badge>
                    </div>
                  </button>
                  {open && (
                    <div className="divide-y divide-border bg-background">
                      {srv.usuarios.map((u) => (
                        <div key={u.idUsuario} className="flex items-center gap-3 px-4 py-2.5">
                          <UserCheck className="h-3.5 w-3.5 text-emerald-500 shrink-0" />
                          <span className="text-xs font-medium flex-1">{u.nombreCompleto || `Usuario ${u.idUsuario}`}</span>
                          <span className="text-xs font-mono text-muted-foreground">{u.email}</span>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              );
            })
        }
      </CardContent>
    </Card>
  );
}

// ── Panel: Documentos pendientes por servicio ─────────────────

function PendientesPanel({
  onEnviarServicio,
  onProbarServicio,
}: {
  onEnviarServicio: (idDep: number, nombre: string) => void;
  onProbarServicio: (idDep: number, nombre: string) => void;
}) {
  const { data: pendientes = [], isLoading, refetch, isFetching } = useQuery({
    queryKey: ['alertas-pendientes'],
    queryFn:  () => alertasApi.getPendientes(),
  });

  const [expanded, setExpanded] = useState<Record<number, boolean>>({});

  type Grupo = { idDestino: number; nombreServicio: string; docs: DocumentoPendiente[] };
  const grupos = pendientes.reduce<Grupo[]>((acc, d) => {
    const g = acc.find((x) => x.idDestino === d.idDestino);
    if (g) g.docs.push(d);
    else acc.push({ idDestino: d.idDestino, nombreServicio: d.nombreServicio, docs: [d] });
    return acc;
  }, []);

  const toggle = (id: number) => setExpanded((p) => ({ ...p, [id]: !p[id] }));

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <FileText className="h-4 w-4 text-primary" />
            <CardTitle className="text-base">Documentos pendientes por servicio</CardTitle>
          </div>
          <Button size="sm" variant="outline" onClick={() => refetch()} disabled={isFetching} className="gap-2">
            <RefreshCw className={cn('h-3.5 w-3.5', isFetching && 'animate-spin')} />
            Actualizar
          </Button>
        </div>
        <CardDescription>
          {pendientes.length > 0
            ? `${pendientes.length} documento(s) pendiente(s) en ${grupos.length} servicio(s)`
            : 'No hay documentos pendientes de gestión'}
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-3">
        {isLoading
          ? [1, 2].map((i) => <div key={i} className="h-16 rounded-lg bg-muted animate-pulse" />)
          : grupos.length === 0
            ? (
              <div className="flex flex-col items-center gap-2 py-8 text-muted-foreground">
                <CheckCircle2 className="h-8 w-8 text-emerald-400 opacity-60" />
                <p className="text-sm">Todos los documentos están gestionados</p>
              </div>
            )
            : grupos.map((g) => {
              const urgentes = g.docs.filter((d) => d.idTipoCompromiso === 3).length;
              const open     = expanded[g.idDestino] ?? false;
              return (
                <div key={g.idDestino} className="rounded-lg border border-border overflow-hidden">
                  <div
                    className="flex items-center gap-3 px-4 py-3 bg-muted/30 cursor-pointer hover:bg-muted/50 transition-colors"
                    onClick={() => toggle(g.idDestino)}
                  >
                    {open
                      ? <ChevronDown className="h-4 w-4 text-muted-foreground shrink-0" />
                      : <ChevronRight className="h-4 w-4 text-muted-foreground shrink-0" />}
                    <Building2 className="h-4 w-4 text-muted-foreground shrink-0" />
                    <span className="text-sm font-medium flex-1">{g.nombreServicio}</span>
                    <div className="flex items-center gap-2">
                      {urgentes > 0 && (
                        <Badge className="bg-red-100 text-red-700 border-0 text-[10px] gap-1">
                          <AlertTriangle className="h-2.5 w-2.5" />
                          {urgentes} urgente(s)
                        </Badge>
                      )}
                      <Badge variant="outline" className="text-[10px]">{g.docs.length} doc(s)</Badge>
                      <Button
                        size="sm"
                        variant="outline"
                        className="h-7 px-2.5 gap-1.5 text-xs"
                        onClick={(e) => { e.stopPropagation(); onEnviarServicio(g.idDestino, g.nombreServicio); }}
                        title="Enviar alerta a este servicio"
                      >
                        <Send className="h-3 w-3" />
                        Enviar
                      </Button>
                      <Button
                        size="sm"
                        variant="ghost"
                        className="h-7 px-2 gap-1 text-xs text-muted-foreground hover:text-foreground"
                        onClick={(e) => { e.stopPropagation(); onProbarServicio(g.idDestino, g.nombreServicio); }}
                        title="Probar envío y ver resultado detallado"
                      >
                        <FlaskConical className="h-3 w-3" />
                        Probar
                      </Button>
                    </div>
                  </div>
                  {open && (
                    <div className="overflow-x-auto">
                      <table className="w-full text-xs">
                        <thead>
                          <tr className="table-head-modern">
                            <th className="px-4 py-2 text-left font-medium text-muted-foreground">N°</th>
                            <th className="px-4 py-2 text-left font-medium text-muted-foreground">Materia</th>
                            <th className="px-4 py-2 text-left font-medium text-muted-foreground">Tipo</th>
                            <th className="px-4 py-2 text-left font-medium text-muted-foreground">Origen</th>
                            <th className="px-4 py-2 text-center font-medium text-muted-foreground">Compromiso</th>
                            <th className="px-4 py-2 text-center font-medium text-muted-foreground">Días</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                          {g.docs.map((d) => (
                            <tr
                              key={`${d.idDocumento}-${d.idDocumentoDestino}`}
                              className={cn('hover:bg-muted/20', d.idTipoCompromiso === 3 && 'bg-red-50/50')}
                            >
                              <td className="px-4 py-2 font-mono font-semibold text-foreground">
                                <a href={`/documentos/${d.idDocumento}`} className="hover:text-primary underline-offset-2 hover:underline">
                                  {d.numInterno ?? d.idDocumento}
                                </a>
                              </td>
                              <td className="px-4 py-2 text-foreground max-w-[200px] truncate">{d.materia ?? '—'}</td>
                              <td className="px-4 py-2 text-muted-foreground">{d.tipoDocumento ?? '—'}</td>
                              <td className="px-4 py-2 text-muted-foreground">{d.origenNombre ?? '—'}</td>
                              <td className="px-4 py-2 text-center">{compromisoBadge(d.idTipoCompromiso)}</td>
                              <td className="px-4 py-2 text-center text-muted-foreground">{d.diasTranscurridos}d</td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  )}
                </div>
              );
            })
        }
      </CardContent>
    </Card>
  );
}

// ── Panel: historial de envíos ────────────────────────────────

function LogsPanel() {
  const { data: logs = [], isLoading, refetch, isFetching } = useQuery({
    queryKey: ['alertas-logs'],
    queryFn:  () => alertasApi.getLogs(1, 50),
  });

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Clock className="h-4 w-4 text-primary" />
            <CardTitle className="text-base">Historial de envíos</CardTitle>
          </div>
          <Button size="sm" variant="outline" onClick={() => refetch()} disabled={isFetching} className="gap-2">
            <RefreshCw className={cn('h-3.5 w-3.5', isFetching && 'animate-spin')} />
            Actualizar
          </Button>
        </div>
        <CardDescription>Últimos 50 registros de alertas enviadas (automáticas y manuales)</CardDescription>
      </CardHeader>
      <CardContent>
        {isLoading
          ? [1, 2, 3, 4].map((i) => <div key={i} className="h-10 mb-2 rounded bg-muted animate-pulse" />)
          : logs.length === 0
            ? (
              <div className="flex flex-col items-center gap-2 py-8 text-muted-foreground">
                <Clock className="h-8 w-8 opacity-30" />
                <p className="text-sm">No hay registros de envío</p>
              </div>
            )
            : (
              <div className="overflow-x-auto">
                <table className="w-full text-xs">
                  <thead>
                    <tr className="table-head-modern">
                      <th className="px-3 py-2 text-left font-medium text-muted-foreground">Fecha</th>
                      <th className="px-3 py-2 text-left font-medium text-muted-foreground">Servicio</th>
                      <th className="px-3 py-2 text-center font-medium text-muted-foreground">Tipo</th>
                      <th className="px-3 py-2 text-center font-medium text-muted-foreground">Estado</th>
                      <th className="px-3 py-2 text-center font-medium text-muted-foreground">Docs</th>
                      <th className="px-3 py-2 text-center font-medium text-muted-foreground">Urgentes</th>
                      <th className="px-3 py-2 text-left font-medium text-muted-foreground">Destinatarios</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-border">
                    {logs.map((log: AlertaLog) => (
                      <tr key={log.id} className="hover:bg-muted/20">
                        <td className="px-3 py-2 text-muted-foreground whitespace-nowrap">{fmtFecha(log.fechaEnvio)}</td>
                        <td className="px-3 py-2 font-medium max-w-[160px] truncate">{log.nombreServicio}</td>
                        <td className="px-3 py-2 text-center">
                          <Badge variant="outline" className="text-[10px]">
                            {log.tipoEnvio === 'auto' ? 'Auto' : 'Manual'}
                          </Badge>
                        </td>
                        <td className="px-3 py-2 text-center">{estadoBadge(log.estado)}</td>
                        <td className="px-3 py-2 text-center font-semibold">{log.documentosIncluidos}</td>
                        <td className="px-3 py-2 text-center">
                          {log.urgentesIncluidos > 0
                            ? <span className="text-red-600 font-semibold">{log.urgentesIncluidos}</span>
                            : <span className="text-muted-foreground">0</span>}
                        </td>
                        <td className="px-3 py-2 text-muted-foreground max-w-[180px] truncate">
                          {log.destinatarios?.join(', ') ?? (log.mensajeError ?? '—')}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )
        }
      </CardContent>
    </Card>
  );
}

// ── Componente: resultado de prueba ───────────────────────────

function ResultadoPrueba({ resultado, servicio }: { resultado: ResultadoServicio; servicio: string }) {
  const ok = resultado.estado === 'ok';
  return (
    <div className={cn(
      'rounded-lg border p-4 space-y-3',
      ok ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'
    )}>
      <div className="flex items-center gap-2">
        {ok
          ? <CheckCircle2 className="h-4 w-4 text-emerald-600 shrink-0" />
          : <AlertTriangle className="h-4 w-4 text-amber-600 shrink-0" />}
        <p className="text-sm font-medium">{servicio}: {resultado.mensaje}</p>
      </div>
      <div className="flex flex-wrap gap-3 text-xs text-muted-foreground">
        <span><strong className="text-foreground">{resultado.documentosIncluidos}</strong> documento(s) incluidos</span>
        <span><strong className="text-foreground">{resultado.usuariosEncontrados}</strong> usuario(s) encontrados</span>
      </div>
      {resultado.detalle && resultado.detalle.length > 0 && (
        <div className="space-y-1">
          <p className="text-xs font-medium text-muted-foreground">Destinatarios encontrados:</p>
          {resultado.detalle.map((d, i) => (
            <div key={i} className="flex items-center gap-2 text-xs">
              <UserCheck className="h-3 w-3 text-emerald-500 shrink-0" />
              <span className="font-medium">{d.nombre}</span>
              <span className="text-muted-foreground font-mono">{d.email}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

// ── Página principal ──────────────────────────────────────────

export function AlertasPage() {
  const qc = useQueryClient();
  const [resultadoPrueba, setResultadoPrueba] = useState<{ resultado: ResultadoServicio; servicio: string } | null>(null);

  // Pre-carga pendientes para el panel de destinatarios (saber qué servicios tienen docs)
  const { data: pendientesAll = [] } = useQuery({
    queryKey: ['alertas-pendientes'],
    queryFn:  () => alertasApi.getPendientes(),
  });
  const serviciosConDocs = new Set(pendientesAll.map((d) => d.idDestino));

  const invalidarLogs = () => qc.invalidateQueries({ queryKey: ['alertas-logs'] });

  const enviarManual = useMutation({
    mutationFn: (idDependencia: number) => alertasApi.enviarManual(idDependencia),
    onSuccess: (r) => {
      if (r.estado === 'ok')
        toast.success(`Alerta enviada — ${r.documentosIncluidos} documento(s) a ${r.usuariosEncontrados} usuario(s)`);
      else if (r.estado === 'sin_correo')
        toast.warning('Sin usuarios con correo configurado para este servicio');
      else if (r.estado === 'sin_docs')
        toast.info('No hay documentos pendientes para ese servicio');
      else
        toast.error(`Error al enviar: ${r.mensaje}`);
      invalidarLogs();
    },
    onError: () => toast.error('Error al enviar la alerta'),
  });

  const probarServicio = useMutation({
    mutationFn: ({ idDependencia, nombre }: { idDependencia: number; nombre: string }) =>
      alertasApi.probarServicio(idDependencia).then((r) => ({ r, nombre })),
    onSuccess: ({ r, nombre }) => {
      setResultadoPrueba({ resultado: r, servicio: nombre });
      invalidarLogs();
    },
    onError: () => toast.error('Error al ejecutar la prueba'),
  });

  const enviarTodos = useMutation({
    mutationFn: alertasApi.enviarTodos,
    onSuccess: (r) => {
      toast.success(`Proceso completado: ${r.enviados} enviados, ${r.errores} errores, ${r.sinEmail} sin email`);
      invalidarLogs();
    },
    onError: () => toast.error('Error al enviar alertas'),
  });

  const handleEnviarServicio = (idDep: number, nombre: string) => {
    toast.info(`Enviando alerta a ${nombre}…`);
    enviarManual.mutate(idDep);
    setResultadoPrueba(null);
  };

  const handleProbarServicio = (idDep: number, nombre: string) => {
    toast.info(`Probando envío a ${nombre}…`);
    probarServicio.mutate({ idDependencia: idDep, nombre });
  };

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center gap-4">
        <div className="flex items-center gap-3 flex-1">
          <span className="icon-3d icon-3d-red flex h-10 w-10 shrink-0 items-center justify-center">
            <Bell className="h-5 w-5 text-white" />
          </span>
          <div>
            <h1 className="text-2xl font-bold text-foreground">Alertas por correo</h1>
            <p className="text-sm text-muted-foreground">
              Envíos automáticos a usuarios del sistema con documentos pendientes
            </p>
          </div>
        </div>
        <Button
          onClick={() => { enviarTodos.mutate(); setResultadoPrueba(null); }}
          disabled={enviarTodos.isPending}
          className="btn-premium gap-2 shrink-0 border-0 text-primary-foreground"
        >
          {enviarTodos.isPending
            ? <RefreshCw className="h-4 w-4 animate-spin" />
            : <Send className="h-4 w-4" />}
          Enviar alertas a todos
        </Button>
      </div>

      {/* Resultado proceso "enviar todos" */}
      {enviarTodos.isSuccess && enviarTodos.data && (
        <Card className="border-emerald-200 bg-emerald-50">
          <CardContent className="pt-4">
            <div className="flex items-start gap-3">
              <CheckCircle2 className="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" />
              <div className="space-y-2 flex-1">
                <p className="text-sm font-medium text-emerald-800">Proceso completado</p>
                <div className="flex flex-wrap gap-2">
                  {enviarTodos.data.detalles.map((d, i) => (
                    <span key={i} className={cn(
                      'inline-flex items-center gap-1.5 text-xs px-2 py-1 rounded-full font-medium',
                      d.estado === 'ok'         ? 'bg-emerald-100 text-emerald-700' :
                      d.estado === 'error'      ? 'bg-red-100 text-red-700' :
                      d.estado === 'sin_correo' ? 'bg-amber-100 text-amber-700' :
                      'bg-slate-100 text-slate-600'
                    )}>
                      {d.servicio}: {d.estado}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Resultado de prueba individual */}
      {resultadoPrueba && (
        <ResultadoPrueba resultado={resultadoPrueba.resultado} servicio={resultadoPrueba.servicio} />
      )}

      <Separator />

      {/* Configuración + Destinatarios (lado a lado) */}
      <div className="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <ConfigPanel />
        <DestinatariosPanel serviciosConDocs={serviciosConDocs} />
      </div>

      {/* Pendientes */}
      <PendientesPanel
        onEnviarServicio={handleEnviarServicio}
        onProbarServicio={handleProbarServicio}
      />

      {/* Logs */}
      <LogsPanel />
    </div>
  );
}
