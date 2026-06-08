import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  PenLine, RefreshCw, CheckCircle, XCircle, AlertTriangle,
  Wifi, WifiOff, Settings, History, Eye, EyeOff, Save, Loader2,
  BadgeCheck, Clock,
} from 'lucide-react';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';
import { apiClient } from '@/lib/api/client';
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

const ESTADO_COLORS: Record<string, string> = {
  Firmado:      'border-emerald-300 text-emerald-700 bg-emerald-50',
  Enviado:      'border-blue-300 text-blue-700 bg-blue-50',
  Pendiente:    'border-slate-300 text-slate-600',
  Reintentando: 'border-amber-300 text-amber-700 bg-amber-50',
  Error:        'border-red-300 text-red-700 bg-red-50',
  Cancelado:    'border-border text-muted-foreground',
  Expirado:     'border-orange-300 text-orange-700 bg-orange-50',
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
  const [testResult, setTestResult] = useState<{ ok: boolean; mensaje: string } | null>(null);

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
      const data = (res.data as { data: { ok: boolean; mensaje: string } }).data;
      setTestResult(data);
      if (data.ok) toast.success('Conexión exitosa');
      else toast.warning(data.mensaje);
    },
    onError: () => { toast.error('Error al probar conexión'); setTestResult({ ok: false, mensaje: 'Error de red' }); },
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
                <tr className="bg-muted/30 border-b border-border text-xs text-muted-foreground">
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

// ── Página principal ──────────────────────────────────────────────
export function FirmaGobPage() {
  const [activeTab, setActiveTab] = useState<'TEST' | 'PRODUCCION' | 'historial'>('TEST');
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
    { key: 'TEST'      as const, label: 'Ambiente TEST',       icon: Settings, active: configTest?.activo },
    { key: 'PRODUCCION'as const, label: 'Ambiente PRODUCCIÓN', icon: Settings, active: configProd?.activo },
    { key: 'historial' as const, label: 'Historial',           icon: History,  active: undefined },
  ];

  return (
    <div className="p-4 sm:p-6 space-y-6 max-w-4xl mx-auto">
      {/* Header */}
      <div className="flex flex-col gap-1">
        <div className="flex items-center gap-2.5">
          <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <PenLine className="h-5 w-5" />
          </div>
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
              </CardTitle>
              <CardDescription>
                {activeTab === 'historial'
                  ? 'Registro de todos los intentos de firma electrónica'
                  : 'Credenciales y parámetros del ambiente de firma electrónica'}
              </CardDescription>
            </div>
            {activeTab !== 'historial' && (
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
        </CardContent>
      </Card>
    </div>
  );
}
