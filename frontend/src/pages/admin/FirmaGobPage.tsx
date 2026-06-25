import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  PenLine, RefreshCw, CheckCircle, XCircle, AlertTriangle,
  Wifi, WifiOff, Settings, History, Eye, EyeOff, Save, Loader2,
  BadgeCheck, Clock, ScrollText, Send, Copy, ExternalLink, RotateCw,
  Download, ChevronDown, ChevronRight, ShieldCheck, X,
} from 'lucide-react';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';
import { apiClient } from '@/lib/api/client';
import { useDebounce } from '@/hooks/useDebounce';
import { Button }      from '@/components/ui/button';
import { Badge }       from '@/components/ui/badge';
import { Label }       from '@/components/ui/label';
import {
  Card, CardContent, CardDescription, CardHeader, CardTitle,
} from '@/components/ui/card';

// ── Tipos ─────────────────────────────────────────────────────────
interface FirmaGobConfig {
  id:                       number;
  ambiente:                 string;
  url_api:                  string | null;
  entity:                   string | null;
  purpose:                  string | null;
  max_reintentos:           number;
  segundos_entre_reintentos: number;
  activo:                   boolean;
  fecha_update:             string;
  tiene_token:              boolean;
  tiene_jwt_secret:         boolean;
}

interface HistorialRow {
  id:                 number;
  id_documento:       number | null;
  correlativo_memo:   string | null;
  nombre_firmante:    string | null;
  tipo_firmante:      string | null;
  ambiente:           string | null;
  estado:             string;
  intentos_realizados: number;
  resultado:          string | null;
  fecha_creacion:     string;
  fecha_firma:        string | null;
}

type Ambiente = 'TEST' | 'PRODUCCION';

type ResultadoLog = 'Exitoso' | 'Advertencia' | 'Error';

interface NivelResultado {
  nivel:          number;
  ok:             boolean;
  resultado:      ResultadoLog;
  status?:        number;
  mensaje:        string;
  recomendacion?: string;
  idLog:          number | null;
}

interface TestConexionResult {
  ok:             boolean;
  ambiente:       string;
  niveles?:       NivelResultado[];
  nivel?:         number;
  status?:        number;
  mensaje?:       string;
  recomendacion?: string;
  ticketFirmaGov?: string | null;
  idLog?:         number | null;
}

interface LogRow {
  id:                  number;
  fecha_hora:          string;
  ambiente:            string | null;
  accion:              string;
  metodo_http:         string | null;
  endpoint:            string | null;
  codigo_respuesta:    number | null;
  resultado:           string;
  mensaje:             string | null;
  id_usuario:          number | null;
  usuario_nombre:      string | null;
  id_documento:        number | null;
  ticket_firmagov:     string | null;
  tiempo_respuesta_ms: number | null;
  recomendacion:       string | null;
  revisado:            boolean;
  id_historial:        number | null;
}

interface LogDetail extends LogRow {
  request_payload:    unknown;
  response_payload:   unknown;
  stack_trace:        string | null;
  fecha_revisado:     string | null;
  id_usuario_revisor: number | null;
}

const ESTADO_COLORS: Record<string, string> = {
  Firmado:      'border-emerald-300 text-emerald-700 bg-emerald-50',
  Enviado:      'border-blue-300 text-blue-700 bg-blue-50',
  Pendiente:    'border-slate-300 text-slate-600',
  Reintentando: 'border-amber-300 text-amber-700 bg-amber-50',
  Error:        'border-red-300 text-red-700 bg-red-50',
  Cancelado:    'border-border text-muted-foreground',
  Expirado:     'border-orange-300 text-orange-700 bg-orange-50',
};

const RESULTADO_COLORS: Record<string, string> = {
  Exitoso:     'border-emerald-300 text-emerald-700 bg-emerald-50',
  Advertencia: 'border-amber-300 text-amber-700 bg-amber-50',
  Error:       'border-red-300 text-red-700 bg-red-50',
};

const ACCION_LABELS: Record<string, string> = {
  'test-conexion-nivel1': 'Conectividad (Nivel 1)',
  'test-conexion-nivel2': 'Comportamiento endpoint (Nivel 2)',
  'test-conexion-nivel3': 'Validación de credenciales (Nivel 3)',
  'solicitar-firma':      'Solicitud de firma',
};

const NIVEL_LABELS: Record<number, string> = {
  1: 'Conectividad básica',
  2: 'Comportamiento del endpoint',
};

const inCls = 'w-full h-9 px-3 text-sm rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring';
const textAreaCls = `${inCls} h-auto py-2 resize-none`;

// ── Panel de configuración por ambiente ───────────────────────────
function ConfigPanel({ config }: { config: FirmaGobConfig }) {
  const qc = useQueryClient();
  const [form, setForm] = useState({
    urlApi:                  config.url_api                 ?? '',
    entity:                  config.entity                  ?? '',
    purpose:                 config.purpose                 ?? '',
    apiTokenKey:             '',
    jwtSecret:               '',
    maxReintentos:           String(config.max_reintentos),
    segundosEntreReintentos: String(config.segundos_entre_reintentos),
    activo:                  config.activo,
  });
  const [showToken, setShowToken]  = useState(false);
  const [showJwt,   setShowJwt]    = useState(false);
  const [testResult, setTestResult] = useState<TestConexionResult | null>(null);
  const [showNivel3, setShowNivel3] = useState(false);
  const [runPrueba,  setRunPrueba]  = useState('');
  const [confirmarNivel3, setConfirmarNivel3] = useState(false);
  const [nivel3Result, setNivel3Result] = useState<TestConexionResult | null>(null);

  const amb = config.ambiente as Ambiente;

  const saveMut = useMutation({
    mutationFn: () => apiClient.patch(`/firma-gob/config/${amb}`, {
      urlApi:                  form.urlApi                  || null,
      entity:                  form.entity                  || null,
      purpose:                 form.purpose                 || null,
      apiTokenKey:             form.apiTokenKey             || undefined,
      jwtSecret:               form.jwtSecret               || undefined,
      maxReintentos:           Number(form.maxReintentos),
      segundosEntreReintentos: Number(form.segundosEntreReintentos),
      activo:                  form.activo,
    }),
    onSuccess: () => {
      toast.success('Configuración guardada');
      qc.invalidateQueries({ queryKey: ['firma-gob-config'] });
      setForm((p) => ({ ...p, apiTokenKey: '', jwtSecret: '' }));
    },
    onError: () => toast.error('Error al guardar'),
  });

  const testMut = useMutation({
    mutationFn: () => apiClient.post('/firma-gob/test-conexion', { ambiente: amb }),
    onSuccess: (res) => {
      const data = (res.data as { data: TestConexionResult }).data;
      setTestResult(data);
      if (data.niveles) {
        const hayError = data.niveles.some((n) => n.resultado === 'Error');
        if (!hayError) toast.success('Diagnóstico completado');
        else toast.warning('Se detectaron problemas — revisa el detalle');
      } else if (data.ok) {
        toast.success('Conexión exitosa');
      } else {
        toast.warning(data.mensaje ?? 'No se pudo verificar la conexión');
      }
    },
    onError: () => { toast.error('Error al probar conexión'); setTestResult({ ok: false, ambiente: amb, mensaje: 'Error de red' }); },
  });

  const nivel3Mut = useMutation({
    mutationFn: () => apiClient.post('/firma-gob/test-conexion', {
      ambiente: amb, nivel: 3, runPrueba: runPrueba.trim(), confirmar: true,
    }),
    onSuccess: (res) => {
      const data = (res.data as { data: TestConexionResult }).data;
      setNivel3Result(data);
      if (data.ok) toast.success('Credenciales válidas — FirmaGov respondió correctamente');
      else toast.warning(data.mensaje ?? 'La validación no fue exitosa');
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al validar credenciales';
      toast.error(msg);
      setNivel3Result({ ok: false, ambiente: amb, mensaje: msg });
    },
  });

  const setF = (k: keyof typeof form, v: unknown) => setForm((p) => ({ ...p, [k]: v }));

  const isTest = amb === 'TEST';
  const accentColor = isTest ? 'blue' : 'violet';

  return (
    <div className="space-y-5">
      {/* Estado y toggle activo */}
      <div className={cn(
        'flex items-center justify-between p-3 rounded-lg border',
        form.activo
          ? `border-${accentColor}-200 bg-${accentColor}-50/50`
          : 'border-border bg-muted/20',
      )}>
        <div className="flex items-center gap-2">
          {form.activo
            ? <BadgeCheck className={`h-4 w-4 text-${accentColor}-600`} />
            : <XCircle className="h-4 w-4 text-muted-foreground" />}
          <span className="text-sm font-medium">
            {form.activo ? `Ambiente ${amb} activo` : `Ambiente ${amb} inactivo`}
          </span>
        </div>
        <label className="flex items-center gap-2 cursor-pointer">
          <input
            type="checkbox"
            checked={form.activo}
            onChange={(e) => setF('activo', e.target.checked)}
            className="h-4 w-4"
          />
          <span className="text-xs text-muted-foreground">Activar</span>
        </label>
      </div>

      {/* URL API */}
      <div className="space-y-1.5">
        <Label className="text-sm">URL del endpoint Firma.gob</Label>
        <input
          type="url"
          value={form.urlApi}
          onChange={(e) => setF('urlApi', e.target.value)}
          placeholder="https://api.firma.gob.cl/v1/..."
          className={inCls}
        />
        <p className="text-xs text-muted-foreground">
          {isTest ? 'Endpoint del ambiente de homologación (sandbox)' : 'Endpoint de producción (opera documentos reales)'}
        </p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {/* Entity (RUT institución) */}
        <div className="space-y-1.5">
          <Label className="text-sm">Entity (RUT institución)</Label>
          <input
            type="text"
            value={form.entity}
            onChange={(e) => setF('entity', e.target.value)}
            placeholder="12345678-9"
            className={inCls}
          />
        </div>
        {/* Purpose */}
        <div className="space-y-1.5">
          <Label className="text-sm">Purpose (propósito del proceso)</Label>
          <input
            type="text"
            value={form.purpose}
            onChange={(e) => setF('purpose', e.target.value)}
            placeholder="Firma Memorándum Institucional HUAP"
            className={inCls}
          />
        </div>
      </div>

      {/* Tokens */}
      <div className="space-y-3 p-3 rounded-lg border bg-muted/10">
        <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Credenciales</p>
        <div className="space-y-1.5">
          <Label className="text-sm">API Token Key</Label>
          <div className="relative">
            <input
              type={showToken ? 'text' : 'password'}
              value={form.apiTokenKey}
              onChange={(e) => setF('apiTokenKey', e.target.value)}
              placeholder={config.tiene_token ? '••••••••••• (ya configurado — dejar vacío para mantener)' : 'Ingresa el token de autenticación'}
              className={cn(inCls, 'pr-10')}
            />
            <button
              type="button"
              onClick={() => setShowToken(!showToken)}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
            >
              {showToken ? <EyeOff className="h-3.5 w-3.5" /> : <Eye className="h-3.5 w-3.5" />}
            </button>
          </div>
          {config.tiene_token && (
            <p className="text-xs text-emerald-600 flex items-center gap-1">
              <CheckCircle className="h-3 w-3" /> Token configurado
            </p>
          )}
        </div>
        <div className="space-y-1.5">
          <Label className="text-sm">JWT Secret</Label>
          <div className="relative">
            <input
              type={showJwt ? 'text' : 'password'}
              value={form.jwtSecret}
              onChange={(e) => setF('jwtSecret', e.target.value)}
              placeholder={config.tiene_jwt_secret ? '••••••••••• (ya configurado — dejar vacío para mantener)' : 'Ingresa el secreto JWT'}
              className={cn(inCls, 'pr-10')}
            />
            <button
              type="button"
              onClick={() => setShowJwt(!showJwt)}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
            >
              {showJwt ? <EyeOff className="h-3.5 w-3.5" /> : <Eye className="h-3.5 w-3.5" />}
            </button>
          </div>
          {config.tiene_jwt_secret && (
            <p className="text-xs text-emerald-600 flex items-center gap-1">
              <CheckCircle className="h-3 w-3" /> JWT Secret configurado
            </p>
          )}
        </div>
      </div>

      {/* Reintentos */}
      <div className="grid grid-cols-2 gap-4">
        <div className="space-y-1.5">
          <Label className="text-sm">Máximo de reintentos</Label>
          <input
            type="number"
            min={1} max={10}
            value={form.maxReintentos}
            onChange={(e) => setF('maxReintentos', e.target.value)}
            className={inCls}
          />
        </div>
        <div className="space-y-1.5">
          <Label className="text-sm">Segundos entre reintentos</Label>
          <input
            type="number"
            min={5} max={300}
            value={form.segundosEntreReintentos}
            onChange={(e) => setF('segundosEntreReintentos', e.target.value)}
            className={inCls}
          />
        </div>
      </div>

      {/* Test resultado */}
      {testResult && (
        <div className="space-y-2">
          {testResult.niveles ? (
            testResult.niveles.map((n) => (
              <div key={n.nivel} className={cn('flex items-start gap-2 p-3 rounded-lg border text-sm', RESULTADO_COLORS[n.resultado] ?? 'border-border')}>
                {n.resultado === 'Exitoso' && <CheckCircle className="h-4 w-4 shrink-0 mt-0.5" />}
                {n.resultado === 'Advertencia' && <AlertTriangle className="h-4 w-4 shrink-0 mt-0.5" />}
                {n.resultado === 'Error' && <XCircle className="h-4 w-4 shrink-0 mt-0.5" />}
                <div className="flex-1 min-w-0">
                  <p className="font-medium">
                    Nivel {n.nivel} — {NIVEL_LABELS[n.nivel] ?? ''}
                    {n.status != null && <span className="ml-1.5 font-mono text-xs opacity-70">HTTP {n.status}</span>}
                  </p>
                  <p>{n.mensaje}</p>
                  {n.recomendacion && <p className="text-xs mt-1 opacity-80">{n.recomendacion}</p>}
                </div>
              </div>
            ))
          ) : (
            <div className={cn(
              'flex items-center gap-2 p-3 rounded-lg border text-sm',
              testResult.ok
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : 'border-red-200 bg-red-50 text-red-700',
            )}>
              {testResult.ok
                ? <CheckCircle className="h-4 w-4 shrink-0" />
                : <XCircle className="h-4 w-4 shrink-0" />}
              {testResult.mensaje}
            </div>
          )}
        </div>
      )}

      {/* Validación de credenciales — solo TEST, acción explícita y separada */}
      {isTest && (
        <div className="p-3 rounded-lg border border-dashed border-blue-300 bg-blue-50/30 space-y-3">
          <div className="flex items-center justify-between gap-3">
            <div>
              <p className="text-sm font-medium flex items-center gap-1.5">
                <Send className="h-3.5 w-3.5" /> Validar credenciales (envío real)
              </p>
              <p className="text-xs text-muted-foreground">
                Envía una solicitud POST real a FirmaGov en TEST para confirmar que el token y el JWT secret son válidos.
              </p>
            </div>
            <Button variant="outline" size="sm" onClick={() => setShowNivel3((v) => !v)}>
              {showNivel3 ? 'Ocultar' : 'Validar'}
            </Button>
          </div>

          {showNivel3 && (
            <div className="space-y-3 pt-2 border-t border-blue-200/60">
              <div className="space-y-1.5">
                <Label className="text-sm">RUT de prueba</Label>
                <input
                  type="text"
                  value={runPrueba}
                  onChange={(e) => setRunPrueba(e.target.value)}
                  placeholder="12345678-9"
                  className={inCls}
                />
              </div>
              <div className="flex items-start gap-2 p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800">
                <AlertTriangle className="h-3.5 w-3.5 shrink-0 mt-0.5" />
                Esta acción enviará una solicitud real a FirmaGov en ambiente TEST y puede generar un registro/ticket en su sistema.
              </div>
              <label className="flex items-center gap-2 text-xs cursor-pointer">
                <input
                  type="checkbox"
                  checked={confirmarNivel3}
                  onChange={(e) => setConfirmarNivel3(e.target.checked)}
                  className="h-3.5 w-3.5"
                />
                Confirmo que deseo enviar esta prueba a FirmaGov (TEST)
              </label>

              {nivel3Result && (
                <div className={cn(
                  'flex items-start gap-2 p-3 rounded-lg border text-sm',
                  nivel3Result.ok
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-red-200 bg-red-50 text-red-700',
                )}>
                  {nivel3Result.ok
                    ? <CheckCircle className="h-4 w-4 shrink-0 mt-0.5" />
                    : <XCircle className="h-4 w-4 shrink-0 mt-0.5" />}
                  <div className="flex-1 min-w-0">
                    <p>{nivel3Result.mensaje}</p>
                    {nivel3Result.recomendacion && <p className="text-xs mt-1 opacity-80">{nivel3Result.recomendacion}</p>}
                    {nivel3Result.ticketFirmaGov && <p className="text-xs mt-1 font-mono">Ticket: {nivel3Result.ticketFirmaGov}</p>}
                  </div>
                </div>
              )}

              <Button
                size="sm"
                onClick={() => nivel3Mut.mutate()}
                disabled={nivel3Mut.isPending || !confirmarNivel3 || !runPrueba.trim()}
                className="gap-1.5"
              >
                {nivel3Mut.isPending ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Send className="h-3.5 w-3.5" />}
                Enviar prueba a FirmaGov
              </Button>
            </div>
          )}
        </div>
      )}

      {/* Acciones */}
      <div className="flex justify-between items-center pt-1 border-t border-border/40">
        <p className="text-xs text-muted-foreground">
          Última actualización: {config.fecha_update.substring(0, 16).replace('T', ' ')}
        </p>
        <div className="flex gap-2">
          <Button
            variant="outline" size="sm"
            onClick={() => testMut.mutate()}
            disabled={testMut.isPending || !form.urlApi}
            className="gap-1.5"
          >
            {testMut.isPending
              ? <Loader2 className="h-3.5 w-3.5 animate-spin" />
              : form.urlApi ? <Wifi className="h-3.5 w-3.5" /> : <WifiOff className="h-3.5 w-3.5" />}
            Probar conexión
          </Button>
          <Button
            size="sm"
            onClick={() => saveMut.mutate()}
            disabled={saveMut.isPending}
            className="gap-1.5"
          >
            {saveMut.isPending ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Save className="h-3.5 w-3.5" />}
            Guardar
          </Button>
        </div>
      </div>
    </div>
  );
}

// ── Panel de historial ────────────────────────────────────────────
function HistorialPanel() {
  const [filtroEstado,   setFiltroEstado]   = useState('');
  const [filtroAmbiente, setFiltroAmbiente] = useState('');
  const [pagina,         setPagina]         = useState(1);

  const { data, isFetching, refetch } = useQuery({
    queryKey: ['firma-gob-historial', pagina, filtroEstado, filtroAmbiente],
    queryFn: async () => {
      const params = new URLSearchParams({ pagina: String(pagina) });
      if (filtroEstado)   params.set('estado',   filtroEstado);
      if (filtroAmbiente) params.set('ambiente',  filtroAmbiente);
      const res = await apiClient.get<{
        ok: boolean;
        data: HistorialRow[];
        meta: { total: number; pagina: number; porPagina: number; totalPaginas: number };
      }>(`/firma-gob/historial?${params}`);
      return res.data;
    },
    staleTime: 15_000,
  });

  const rows      = data?.data ?? [];
  const meta      = data?.meta;
  const totalPags = meta?.totalPaginas ?? 1;

  return (
    <div className="space-y-4">
      {/* Filtros */}
      <div className="flex flex-wrap gap-3">
        <select
          value={filtroEstado}
          onChange={(e) => { setFiltroEstado(e.target.value); setPagina(1); }}
          className="h-9 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
        >
          <option value="">Todos los estados</option>
          {['Pendiente', 'Enviado', 'Firmado', 'Error', 'Reintentando', 'Cancelado', 'Expirado'].map((e) => (
            <option key={e} value={e}>{e}</option>
          ))}
        </select>
        <select
          value={filtroAmbiente}
          onChange={(e) => { setFiltroAmbiente(e.target.value); setPagina(1); }}
          className="h-9 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
        >
          <option value="">Todos los ambientes</option>
          <option value="TEST">TEST</option>
          <option value="PRODUCCION">PRODUCCIÓN</option>
        </select>
        <Button variant="outline" size="sm" onClick={() => refetch()} disabled={isFetching} className="gap-1.5">
          <RefreshCw className={cn('h-3.5 w-3.5', isFetching && 'animate-spin')} />
        </Button>
        {meta && (
          <p className="text-xs text-muted-foreground self-center ml-auto">{meta.total} registro{meta.total !== 1 ? 's' : ''}</p>
        )}
      </div>

      {/* Tabla */}
      {rows.length === 0 ? (
        <div className="flex flex-col items-center py-14 gap-2 text-muted-foreground">
          <History className="h-10 w-10 opacity-20" />
          <p className="text-sm font-medium">Sin registros de integración</p>
          <p className="text-xs opacity-60">Los intentos de firma electrónica aparecerán aquí</p>
        </div>
      ) : (
        <div className="rounded-xl border overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="table-head-modern">
                  <th className="px-4 py-2.5 text-left font-semibold">#</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Correlativo</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Firmante</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Ambiente</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Estado</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Intentos</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Fecha</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-border">
                {rows.map((r) => (
                  <tr key={r.id} className="hover:bg-muted/20 transition-colors">
                    <td className="px-4 py-2.5 text-muted-foreground text-xs">{r.id}</td>
                    <td className="px-4 py-2.5">
                      <span className="font-mono text-xs">{r.correlativo_memo ?? `doc #${r.id_documento}`}</span>
                    </td>
                    <td className="px-4 py-2.5">
                      <div className="text-xs">
                        <p className="font-medium truncate max-w-36">{r.nombre_firmante ?? '—'}</p>
                        {r.tipo_firmante && (
                          <p className="text-muted-foreground">{r.tipo_firmante}</p>
                        )}
                      </div>
                    </td>
                    <td className="px-4 py-2.5">
                      <Badge variant="outline" className={cn('text-xs', r.ambiente === 'PRODUCCION' ? 'border-violet-300 text-violet-700' : 'border-blue-300 text-blue-700')}>
                        {r.ambiente ?? '—'}
                      </Badge>
                    </td>
                    <td className="px-4 py-2.5">
                      <Badge variant="outline" className={cn('text-xs', ESTADO_COLORS[r.estado] ?? 'border-border text-muted-foreground')}>
                        {r.estado}
                      </Badge>
                    </td>
                    <td className="px-4 py-2.5 text-center text-xs">{r.intentos_realizados}</td>
                    <td className="px-4 py-2.5">
                      <div className="text-xs text-muted-foreground flex items-center gap-1">
                        <Clock className="h-3 w-3 shrink-0" />
                        {r.fecha_creacion.substring(0, 16).replace('T', ' ')}
                      </div>
                      {r.fecha_firma && (
                        <div className="text-xs text-emerald-600 flex items-center gap-1 mt-0.5">
                          <CheckCircle className="h-3 w-3 shrink-0" />
                          {r.fecha_firma.substring(0, 16).replace('T', ' ')}
                        </div>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Paginación */}
      {totalPags > 1 && (
        <div className="flex items-center justify-center gap-2 pt-2">
          <Button variant="outline" size="sm" disabled={pagina <= 1} onClick={() => setPagina((p) => p - 1)}>Anterior</Button>
          <span className="text-sm text-muted-foreground">{pagina} / {totalPags}</span>
          <Button variant="outline" size="sm" disabled={pagina >= totalPags} onClick={() => setPagina((p) => p + 1)}>Siguiente</Button>
        </div>
      )}
    </div>
  );
}

// ── Panel de logs técnicos ─────────────────────────────────────────
function LogsPanel() {
  const [filtroAmbiente, setFiltroAmbiente] = useState('');
  const [filtroAccion,   setFiltroAccion]   = useState('');
  const [filtroCodigo,   setFiltroCodigo]   = useState('');
  const [filtroUsuario,  setFiltroUsuario]  = useState('');
  const [fechaDesde,     setFechaDesde]     = useState('');
  const [fechaHasta,     setFechaHasta]     = useState('');
  const [soloErrores,    setSoloErrores]    = useState(false);
  const [pagina,         setPagina]         = useState(1);
  const [logSeleccionado, setLogSeleccionado] = useState<number | null>(null);
  const [exportando, setExportando] = useState(false);

  const usuarioDebounced = useDebounce(filtroUsuario, 400);

  const buildParams = () => {
    const params = new URLSearchParams();
    if (filtroAmbiente)   params.set('ambiente', filtroAmbiente);
    if (filtroAccion)     params.set('accion', filtroAccion);
    if (filtroCodigo)     params.set('codigoRespuesta', filtroCodigo);
    if (usuarioDebounced) params.set('usuario', usuarioDebounced);
    if (fechaDesde)       params.set('fechaDesde', fechaDesde);
    if (fechaHasta)       params.set('fechaHasta', fechaHasta);
    if (soloErrores)      params.set('soloErrores', 'true');
    return params;
  };

  const { data, isFetching, refetch } = useQuery({
    queryKey: ['firma-gob-logs', pagina, filtroAmbiente, filtroAccion, filtroCodigo, usuarioDebounced, fechaDesde, fechaHasta, soloErrores],
    queryFn: async () => {
      const params = buildParams();
      params.set('pagina', String(pagina));
      const res = await apiClient.get<{
        ok: boolean;
        data: LogRow[];
        meta: { total: number; pagina: number; porPagina: number; totalPaginas: number };
      }>(`/firma-gob/logs?${params}`);
      return res.data;
    },
    staleTime: 15_000,
  });

  const rows      = data?.data ?? [];
  const meta      = data?.meta;
  const totalPags = meta?.totalPaginas ?? 1;

  const handleExportar = async () => {
    try {
      setExportando(true);
      const params = buildParams();
      const response = await apiClient.get(`/firma-gob/logs/exportar?${params}`, { responseType: 'blob' });
      const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' });
      const url  = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `firma_gob_logs_${Date.now()}.csv`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch {
      toast.error('Error al exportar CSV');
    } finally {
      setExportando(false);
    }
  };

  return (
    <div className="space-y-4">
      {/* Filtros */}
      <div className="flex flex-wrap gap-3 items-center">
        <select
          value={filtroAmbiente}
          onChange={(e) => { setFiltroAmbiente(e.target.value); setPagina(1); }}
          className="h-9 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
        >
          <option value="">Todos los ambientes</option>
          <option value="TEST">TEST</option>
          <option value="PRODUCCION">PRODUCCIÓN</option>
        </select>
        <select
          value={filtroAccion}
          onChange={(e) => { setFiltroAccion(e.target.value); setPagina(1); }}
          className="h-9 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
        >
          <option value="">Todos los eventos</option>
          {Object.entries(ACCION_LABELS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
        </select>
        <input
          type="number"
          placeholder="Código HTTP"
          value={filtroCodigo}
          onChange={(e) => { setFiltroCodigo(e.target.value); setPagina(1); }}
          className="h-9 w-28 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
        />
        <input
          type="text"
          placeholder="Usuario"
          value={filtroUsuario}
          onChange={(e) => { setFiltroUsuario(e.target.value); setPagina(1); }}
          className="h-9 w-32 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
        />
        <input
          type="date"
          value={fechaDesde}
          onChange={(e) => { setFechaDesde(e.target.value); setPagina(1); }}
          className="h-9 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
        />
        <input
          type="date"
          value={fechaHasta}
          onChange={(e) => { setFechaHasta(e.target.value); setPagina(1); }}
          className="h-9 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
        />
        <label className="flex items-center gap-1.5 text-xs cursor-pointer">
          <input
            type="checkbox"
            checked={soloErrores}
            onChange={(e) => { setSoloErrores(e.target.checked); setPagina(1); }}
            className="h-3.5 w-3.5"
          />
          Solo errores
        </label>
        <Button variant="outline" size="sm" onClick={() => refetch()} disabled={isFetching} className="gap-1.5">
          <RefreshCw className={cn('h-3.5 w-3.5', isFetching && 'animate-spin')} />
        </Button>
        <Button variant="outline" size="sm" onClick={handleExportar} disabled={exportando} className="gap-1.5 ml-auto">
          {exportando ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Download className="h-3.5 w-3.5" />}
          Exportar
        </Button>
      </div>
      {meta && <p className="text-xs text-muted-foreground">{meta.total} registro{meta.total !== 1 ? 's' : ''}</p>}

      {/* Tabla */}
      {rows.length === 0 ? (
        <div className="flex flex-col items-center py-14 gap-2 text-muted-foreground">
          <ScrollText className="h-10 w-10 opacity-20" />
          <p className="text-sm font-medium">Sin logs técnicos</p>
          <p className="text-xs opacity-60">Los diagnósticos y solicitudes de firma aparecerán aquí</p>
        </div>
      ) : (
        <div className="rounded-xl border overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="table-head-modern">
                  <th className="px-4 py-2.5 text-left font-semibold">Fecha/Hora</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Ambiente</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Evento</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Código</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Resultado</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Mensaje</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Usuario</th>
                  <th className="px-4 py-2.5 text-left font-semibold">Doc/Ticket</th>
                  <th className="px-4 py-2.5 text-left font-semibold"></th>
                </tr>
              </thead>
              <tbody className="divide-y divide-border">
                {rows.map((r) => (
                  <tr key={r.id} className="hover:bg-muted/20 transition-colors">
                    <td className="px-4 py-2.5 text-xs text-muted-foreground whitespace-nowrap">
                      {r.fecha_hora.substring(0, 16).replace('T', ' ')}
                    </td>
                    <td className="px-4 py-2.5">
                      <Badge variant="outline" className={cn('text-xs', r.ambiente === 'PRODUCCION' ? 'border-violet-300 text-violet-700' : 'border-blue-300 text-blue-700')}>
                        {r.ambiente ?? '—'}
                      </Badge>
                    </td>
                    <td className="px-4 py-2.5 text-xs">{ACCION_LABELS[r.accion] ?? r.accion}</td>
                    <td className="px-4 py-2.5 text-xs font-mono">{r.codigo_respuesta ?? '—'}</td>
                    <td className="px-4 py-2.5">
                      <Badge variant="outline" className={cn('text-xs', RESULTADO_COLORS[r.resultado] ?? 'border-border text-muted-foreground')}>
                        {r.resultado}
                      </Badge>
                      {r.revisado && <CheckCircle className="inline h-3 w-3 ml-1 text-emerald-500" />}
                    </td>
                    <td className="px-4 py-2.5 text-xs max-w-60 truncate" title={r.mensaje ?? ''}>{r.mensaje ?? '—'}</td>
                    <td className="px-4 py-2.5 text-xs">{r.usuario_nombre ?? '—'}</td>
                    <td className="px-4 py-2.5 text-xs font-mono">
                      {r.id_documento ? `doc #${r.id_documento}` : (r.ticket_firmagov ?? '—')}
                    </td>
                    <td className="px-4 py-2.5">
                      <Button variant="ghost" size="sm" onClick={() => setLogSeleccionado(r.id)}>Ver detalle</Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Paginación */}
      {totalPags > 1 && (
        <div className="flex items-center justify-center gap-2 pt-2">
          <Button variant="outline" size="sm" disabled={pagina <= 1} onClick={() => setPagina((p) => p - 1)}>Anterior</Button>
          <span className="text-sm text-muted-foreground">{pagina} / {totalPags}</span>
          <Button variant="outline" size="sm" disabled={pagina >= totalPags} onClick={() => setPagina((p) => p + 1)}>Siguiente</Button>
        </div>
      )}

      {logSeleccionado != null && (
        <LogDetailModal logId={logSeleccionado} onClose={() => setLogSeleccionado(null)} />
      )}
    </div>
  );
}

// ── Modal de detalle de log técnico ────────────────────────────────
function LogDetailModal({ logId, onClose }: { logId: number; onClose: () => void }) {
  const qc = useQueryClient();
  const navigate = useNavigate();
  const [showStack, setShowStack] = useState(false);

  const { data, isLoading } = useQuery({
    queryKey: ['firma-gob-log-detail', logId],
    queryFn: async () => {
      const res = await apiClient.get<{ ok: boolean; data: LogDetail }>(`/firma-gob/logs/${logId}`);
      return res.data.data;
    },
  });

  const revisadoMut = useMutation({
    mutationFn: (revisado: boolean) => apiClient.patch(`/firma-gob/logs/${logId}/revisado`, { revisado }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['firma-gob-log-detail', logId] });
      qc.invalidateQueries({ queryKey: ['firma-gob-logs'] });
      toast.success('Estado actualizado');
    },
    onError: () => toast.error('Error al actualizar'),
  });

  const reintentarMut = useMutation({
    mutationFn: () => apiClient.post(`/firma-gob/logs/${logId}/reintentar`, {}),
    onSuccess: (res) => {
      const data = (res.data as { data: { ok: boolean } }).data;
      qc.invalidateQueries({ queryKey: ['firma-gob-logs'] });
      if (data.ok) toast.success('Diagnóstico reintentado correctamente');
      else toast.warning('El reintento mostró problemas — revisa el nuevo registro en la lista');
    },
    onError: (err: unknown) => {
      const msg = (err as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al reintentar';
      toast.error(msg);
    },
  });

  const handleCopiar = () => {
    if (!data) return;
    navigator.clipboard.writeText(JSON.stringify(data, null, 2));
    toast.success('Detalle copiado al portapapeles');
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div className="absolute inset-0 modal-overlay" onClick={onClose} aria-hidden="true" />
      <div
        className="modal-panel relative w-full max-w-2xl max-h-[85vh] overflow-y-auto"
        role="dialog" aria-modal="true" aria-label="Detalle del log técnico"
      >
        {/* Header */}
        <div className="flex items-center gap-3 px-5 py-4 border-b border-border sticky top-0 bg-card z-10">
          <span className="icon-3d-sm icon-3d-slate flex h-9 w-9 shrink-0 items-center justify-center">
            <ScrollText className="h-4 w-4 text-white" />
          </span>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-semibold text-foreground">Detalle del log técnico #{logId}</p>
            {data && (
              <p className="text-xs text-muted-foreground">
                {ACCION_LABELS[data.accion] ?? data.accion} · {data.fecha_hora.substring(0, 16).replace('T', ' ')}
              </p>
            )}
          </div>
          <Button variant="ghost" size="icon" className="h-8 w-8 shrink-0" onClick={onClose} aria-label="Cerrar">
            <X className="h-4 w-4" />
          </Button>
        </div>

        {/* Body */}
        <div className="p-5 space-y-4">
          {isLoading || !data ? (
            <p className="text-sm text-muted-foreground">Cargando...</p>
          ) : (
            <>
              {/* Resumen */}
              <div className="grid grid-cols-2 gap-3 text-sm">
                <div>
                  <p className="text-xs text-muted-foreground">Ambiente</p>
                  <p>{data.ambiente ?? '—'}</p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Resultado</p>
                  <Badge variant="outline" className={cn('text-xs', RESULTADO_COLORS[data.resultado] ?? '')}>{data.resultado}</Badge>
                </div>
                <div className="col-span-2">
                  <p className="text-xs text-muted-foreground">Método / Endpoint</p>
                  <p className="font-mono text-xs break-all">{data.metodo_http} {data.endpoint}</p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Código HTTP</p>
                  <p>{data.codigo_respuesta ?? '—'}</p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Tiempo de respuesta</p>
                  <p>{data.tiempo_respuesta_ms != null ? `${data.tiempo_respuesta_ms} ms` : '—'}</p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">Usuario</p>
                  <p>{data.usuario_nombre ?? '—'}</p>
                </div>
                {data.id_documento != null && (
                  <div>
                    <p className="text-xs text-muted-foreground">Documento</p>
                    <p>#{data.id_documento}</p>
                  </div>
                )}
                {data.ticket_firmagov && (
                  <div>
                    <p className="text-xs text-muted-foreground">Ticket FirmaGov</p>
                    <p className="font-mono">{data.ticket_firmagov}</p>
                  </div>
                )}
              </div>

              {/* Mensaje y recomendación */}
              <div className="space-y-1">
                <p className="text-sm">{data.mensaje}</p>
                {data.recomendacion && <p className="text-xs text-muted-foreground">{data.recomendacion}</p>}
              </div>

              {/* Token completo (JWT) — extraído de la solicitud enviada */}
              {data.request_payload != null && typeof (data.request_payload as Record<string, unknown>).token === 'string' && (
                <div className="space-y-1">
                  <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Token utilizado</p>
                  <textarea
                    readOnly
                    value={(data.request_payload as Record<string, unknown>).token as string}
                    rows={3}
                    className="w-full text-xs font-mono bg-muted/30 rounded-lg p-3 overflow-x-auto resize-none border border-border"
                    onFocus={(e) => e.currentTarget.select()}
                  />
                  <Button
                    variant="outline" size="sm" className="gap-1.5"
                    onClick={() => {
                      navigator.clipboard.writeText((data.request_payload as Record<string, unknown>).token as string);
                      toast.success('Token copiado al portapapeles');
                    }}
                  >
                    <Copy className="h-3.5 w-3.5" /> Copiar Token
                  </Button>
                </div>
              )}

              {/* Request/Response payloads */}
              {data.request_payload != null && (
                <div className="space-y-1">
                  <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Solicitud enviada</p>
                  <pre className="text-xs bg-muted/30 rounded-lg p-3 overflow-x-auto max-h-48 overflow-y-auto">
                    {JSON.stringify(data.request_payload, null, 2)}
                  </pre>
                </div>
              )}
              {data.response_payload != null && (
                <div className="space-y-1">
                  <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Respuesta recibida</p>
                  <pre className="text-xs bg-muted/30 rounded-lg p-3 overflow-x-auto max-h-48 overflow-y-auto">
                    {JSON.stringify(data.response_payload, null, 2)}
                  </pre>
                </div>
              )}

              {/* Stack trace colapsable */}
              {data.stack_trace && (
                <div className="space-y-1">
                  <button
                    type="button"
                    onClick={() => setShowStack((v) => !v)}
                    className="flex items-center gap-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground hover:text-foreground"
                  >
                    {showStack ? <ChevronDown className="h-3.5 w-3.5" /> : <ChevronRight className="h-3.5 w-3.5" />}
                    Stack trace
                  </button>
                  {showStack && (
                    <pre className="text-xs bg-muted/30 rounded-lg p-3 overflow-x-auto max-h-48 overflow-y-auto">{data.stack_trace}</pre>
                  )}
                </div>
              )}

              {/* Revisado */}
              {data.revisado && data.fecha_revisado && (
                <p className="text-xs text-emerald-600 flex items-center gap-1">
                  <CheckCircle className="h-3 w-3" /> Revisado el {data.fecha_revisado.substring(0, 16).replace('T', ' ')}
                </p>
              )}
            </>
          )}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-between gap-2 px-5 py-4 border-t border-border sticky bottom-0 bg-card flex-wrap">
          <div className="flex gap-2">
            <Button variant="ghost" size="sm" onClick={handleCopiar} disabled={!data} className="gap-1.5">
              <Copy className="h-3.5 w-3.5" /> Copiar detalle
            </Button>
            {data && (
              <Button variant="outline" size="sm" onClick={() => revisadoMut.mutate(!data.revisado)} disabled={revisadoMut.isPending} className="gap-1.5">
                {revisadoMut.isPending ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <ShieldCheck className="h-3.5 w-3.5" />}
                {data.revisado ? 'Marcar pendiente' : 'Marcar revisado'}
              </Button>
            )}
          </div>
          <div className="flex gap-2">
            {data?.accion === 'solicitar-firma' && data.id_documento != null && (
              <Button
                variant="outline" size="sm" className="gap-1.5"
                onClick={() => { onClose(); navigate(`/documentos/${data.id_documento}`); }}
              >
                <ExternalLink className="h-3.5 w-3.5" /> Ir al documento
              </Button>
            )}
            {(data?.accion === 'test-conexion-nivel1' || data?.accion === 'test-conexion-nivel2') && (
              <Button size="sm" onClick={() => reintentarMut.mutate()} disabled={reintentarMut.isPending} className="gap-1.5">
                {reintentarMut.isPending ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <RotateCw className="h-3.5 w-3.5" />}
                Reintentar
              </Button>
            )}
            {data?.accion === 'test-conexion-nivel3' && (
              <p className="text-xs text-muted-foreground self-center">
                Para repetir, use "Validar credenciales" en Configuración
              </p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Página principal ──────────────────────────────────────────────
export function FirmaGobPage() {
  const [activeTab, setActiveTab] = useState<'TEST' | 'PRODUCCION' | 'historial' | 'logs'>('TEST');
  const qc = useQueryClient();

  const { data: configs = [], isFetching } = useQuery({
    queryKey: ['firma-gob-config'],
    queryFn: async () => {
      const res = await apiClient.get<{ ok: boolean; data: FirmaGobConfig[] }>('/firma-gob/config');
      return res.data.data;
    },
    staleTime: 60_000,
  });

  const configTest = configs.find((c) => c.ambiente === 'TEST');
  const configProd = configs.find((c) => c.ambiente === 'PRODUCCION');

  const tabs = [
    { key: 'TEST'      as const, label: 'Ambiente TEST',       icon: Settings,    active: configTest?.activo },
    { key: 'PRODUCCION'as const, label: 'Ambiente PRODUCCIÓN', icon: Settings,    active: configProd?.activo },
    { key: 'historial' as const, label: 'Historial',           icon: History,     active: undefined },
    { key: 'logs'      as const, label: 'Logs técnicos',       icon: ScrollText,  active: undefined },
  ];

  return (
    <div className="p-4 sm:p-6 space-y-6 max-w-4xl mx-auto">
      {/* Header */}
      <div className="flex flex-col gap-1">
        <div className="flex items-center gap-2.5">
          <span className="icon-3d icon-3d-slate flex h-10 w-10 shrink-0 items-center justify-center">
            <PenLine className="h-5 w-5 text-white" />
          </span>
          <div>
            <h1 className="text-xl font-semibold tracking-tight">Integración Firma.gob</h1>
            <p className="text-sm text-muted-foreground">
              Configuración y monitoreo del servicio de firma electrónica avanzada del Estado
            </p>
          </div>
        </div>
      </div>

      {/* Banner informativo */}
      <div className="flex gap-3 p-4 rounded-xl border border-amber-200 bg-amber-50/50">
        <AlertTriangle className="h-5 w-5 text-amber-600 shrink-0 mt-0.5" />
        <div className="space-y-1">
          <p className="text-sm font-medium text-amber-800">Módulo en preparación</p>
          <p className="text-xs text-amber-700">
            La arquitectura de integración con Firma.gob está lista. La firma electrónica avanzada requiere
            credenciales activas en el portal del MINCIENCIA. Configura y prueba el ambiente TEST antes de
            activar PRODUCCIÓN.
          </p>
        </div>
      </div>

      {/* Estado de ambientes */}
      <div className="grid grid-cols-2 gap-3">
        {[
          { amb: 'TEST',       cfg: configTest, color: 'blue'   },
          { amb: 'PRODUCCIÓN', cfg: configProd, color: 'violet' },
        ].map(({ amb, cfg }) => (
          <div key={amb} className={cn(
            'rounded-xl border p-4 flex items-center gap-3',
            cfg?.activo ? 'border-emerald-200 bg-emerald-50/50' : 'border-border bg-muted/10',
          )}>
            {cfg?.activo
              ? <Wifi className="h-5 w-5 text-emerald-600 shrink-0" />
              : <WifiOff className="h-5 w-5 text-muted-foreground shrink-0" />}
            <div>
              <p className="text-sm font-semibold">{amb}</p>
              <p className="text-xs text-muted-foreground">{cfg?.activo ? 'Activo' : 'Inactivo'}</p>
              {cfg?.url_api && (
                <p className="text-xs text-muted-foreground truncate max-w-[160px]">{cfg.url_api}</p>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* Tabs */}
      <div className="border-b border-border">
        <nav className="flex gap-0 -mb-px">
          {tabs.map(({ key, label, icon: Icon, active }) => (
            <button
              key={key}
              type="button"
              onClick={() => setActiveTab(key)}
              className={cn(
                'flex items-center gap-1.5 px-4 py-2.5 text-sm border-b-2 transition-colors whitespace-nowrap',
                activeTab === key
                  ? 'border-primary text-primary font-medium'
                  : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border',
              )}
            >
              <Icon className="h-3.5 w-3.5" />
              {label}
              {active !== undefined && (
                <span className={cn(
                  'ml-0.5 h-1.5 w-1.5 rounded-full',
                  active ? 'bg-emerald-500' : 'bg-muted-foreground/40',
                )} />
              )}
            </button>
          ))}
        </nav>
      </div>

      {/* Contenido del tab activo */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <div>
              <CardTitle className="text-base">
                {activeTab === 'TEST'       && 'Configuración — Ambiente TEST'}
                {activeTab === 'PRODUCCION' && 'Configuración — Ambiente PRODUCCIÓN'}
                {activeTab === 'historial'  && 'Historial de Integración'}
                {activeTab === 'logs'       && 'Logs técnicos'}
              </CardTitle>
              <CardDescription>
                {activeTab === 'historial' && 'Registro de todos los intentos de firma electrónica'}
                {activeTab === 'logs'      && 'Diagnósticos de conexión, validaciones de credenciales y solicitudes de firma'}
                {(activeTab === 'TEST' || activeTab === 'PRODUCCION') && 'Credenciales y parámetros del ambiente de firma electrónica'}
              </CardDescription>
            </div>
            {(activeTab === 'TEST' || activeTab === 'PRODUCCION') && (
              <Button variant="ghost" size="icon" onClick={() => qc.invalidateQueries({ queryKey: ['firma-gob-config'] })} disabled={isFetching}>
                <RefreshCw className={cn('h-4 w-4', isFetching && 'animate-spin')} />
              </Button>
            )}
          </div>
        </CardHeader>
        <CardContent>
          {activeTab === 'TEST'       && (configTest ? <ConfigPanel config={configTest} /> : <p className="text-sm text-muted-foreground">Cargando...</p>)}
          {activeTab === 'PRODUCCION' && (configProd ? <ConfigPanel config={configProd} /> : <p className="text-sm text-muted-foreground">Cargando...</p>)}
          {activeTab === 'historial'  && <HistorialPanel />}
          {activeTab === 'logs'       && <LogsPanel />}
        </CardContent>
      </Card>
    </div>
  );
}
