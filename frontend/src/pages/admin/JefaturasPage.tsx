import { useState, useRef } from 'react';
import { useQuery, useMutation, useQueryClient, keepPreviousData } from '@tanstack/react-query';
import {
  Users, Plus, RefreshCw, ChevronDown, ChevronLeft, ChevronRight, Pencil, Trash2,
  Building2, Search, X, Upload, CheckCircle, AlertCircle,
  ShieldCheck, UserCheck, CalendarClock, KeyRound, Link2, Link2Off,
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
import { useDebounce } from '@/hooks/useDebounce';

// ── Tipos ─────────────────────────────────────────────────────────
type TipoJefatura = 'TITULAR' | 'SUBROGANTE' | 'SUBROGANTE_2';
type EstadoFirmaSimple = 'disponible' | 'sin_vincular' | 'usuario_inactivo' | 'sin_firma_timbre' | 'slot_inactivo';

interface FirmanteInfo {
  nombre:        string | null;
  cargo:         string | null;
  rut:           string | null;
  firmaTimbreUrl: string | null;
  activo:        boolean;
  vigenciaDesde: string | null;
  vigenciaHasta: string | null;
  usuarioVinculado: { idUsuario: number; usuario: string; activo: boolean } | null;
  estado:        EstadoFirmaSimple;
  motivo:        string | null;
}

interface CandidatoUsuario {
  idUsuario:       number;
  usuario:         string;
  email:           string | null;
  activo:          boolean;
  nombreCompleto:  string | null;
  descDependencia: string | null;
  vinculadoComo:   string | null;
}

interface Jefatura {
  id:            number;
  idDependencia: number;
  dependencia:   string | null;
  titular:       FirmanteInfo;
  subrogante:    FirmanteInfo;
  subrogante2:   FirmanteInfo;
}

interface CatItem { id: number; descripcion: string; }

interface PaginacionMeta {
  total:        number;
  pagina:       number;
  porPagina:    number;
  totalPaginas: number;
}

interface KpisJefaturas {
  total:       number;
  activos:     number;
  sinFirmante: number;
}

// Tamaño de página por defecto del listado — fácil de ajustar a futuro.
const POR_PAGINA = 20;

// ── Helpers ───────────────────────────────────────────────────────
function esFirmanteVigenteHoy(desde: string | null, hasta: string | null): boolean {
  const hoy = new Date(); hoy.setHours(0, 0, 0, 0);
  if (desde) { const d = new Date(desde); d.setHours(0, 0, 0, 0); if (hoy < d) return false; }
  if (hasta) { const h = new Date(hasta); h.setHours(23, 59, 59, 999); if (hoy > h) return false; }
  return true;
}

function resolverEstadoFirmante(f: FirmanteInfo): 'activo' | 'inactivo' | 'vencido' {
  if (!f.activo) return 'inactivo';
  if (!esFirmanteVigenteHoy(f.vigenciaDesde, f.vigenciaHasta)) return 'vencido';
  return 'activo';
}

const ESTADO_BADGE: Record<string, { label: string; cls: string }> = {
  activo:   { label: 'Activo',   cls: 'border-emerald-300 text-emerald-700 bg-emerald-50' },
  inactivo: { label: 'Inactivo', cls: 'border-border text-muted-foreground' },
  vencido:  { label: 'Vencido',  cls: 'border-amber-300 text-amber-700 bg-amber-50' },
};

const FIRMA_SIMPLE_BADGE: Record<EstadoFirmaSimple, { label: string; cls: string }> = {
  disponible:       { label: 'Firma Simple: habilitada',     cls: 'border-emerald-300 text-emerald-700 bg-emerald-50' },
  sin_vincular:     { label: 'Firma Simple: no habilitada',  cls: 'border-border text-muted-foreground' },
  usuario_inactivo: { label: 'Firma Simple: no habilitada',  cls: 'border-border text-muted-foreground' },
  sin_firma_timbre: { label: 'Firma Simple: no habilitada',  cls: 'border-border text-muted-foreground' },
  slot_inactivo:    { label: 'Firma Simple: no habilitada',  cls: 'border-border text-muted-foreground' },
};

const inCls = 'w-full h-9 px-3 text-sm rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring';

// ── Componente ImagenFirmante ─────────────────────────────────────
function ImagenFirmante({
  label, url, idJefatura, tipoImg, onUploaded,
}: {
  label: string;
  url: string | null;
  idJefatura: number;
  tipoImg: 'firma_timbre_titular' | 'firma_timbre_subrogante' | 'firma_timbre_subrogante_2';
  onUploaded: () => void;
}) {
  const inputRef = useRef<HTMLInputElement>(null);

  const uploadMut = useMutation({
    mutationFn: async (file: File) => {
      const form = new FormData();
      form.append('imagen', file);
      return apiClient.post(`/jefaturas/${idJefatura}/imagen?tipo=${tipoImg}`, form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    },
    onSuccess: () => { toast.success('Imagen actualizada'); onUploaded(); },
    onError:   () => toast.error('Error al subir imagen'),
  });

  const handleFile = (file: File) => {
    if (!['image/png', 'image/jpeg', 'image/webp'].includes(file.type)) {
      toast.error('Solo se aceptan PNG, JPG o WEBP');
      return;
    }
    if (file.size > 5 * 1024 * 1024) { toast.error('Máximo 5 MB'); return; }
    uploadMut.mutate(file);
  };

  return (
    <div className="space-y-2">
      <p className="text-xs font-medium text-muted-foreground">{label}</p>
      {url ? (
        <div className="relative rounded-lg border overflow-hidden group w-36 h-16 bg-muted/20">
          <img
            src={`${url}?t=${Date.now()}`}
            alt={label}
            className="w-full h-full object-contain"
            onError={(e) => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
          />
          <button
            type="button"
            onClick={() => inputRef.current?.click()}
            disabled={uploadMut.isPending}
            className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100 text-white text-xs font-medium"
            title="Cambiar imagen"
          >
            {uploadMut.isPending ? <RefreshCw className="h-4 w-4 animate-spin" /> : <Upload className="h-4 w-4" />}
          </button>
        </div>
      ) : (
        <button
          type="button"
          onClick={() => inputRef.current?.click()}
          disabled={uploadMut.isPending}
          className="flex items-center gap-2 h-9 px-3 text-xs rounded-lg border border-dashed border-input hover:border-primary/50 hover:bg-muted/30 transition-all text-muted-foreground"
        >
          {uploadMut.isPending ? <RefreshCw className="h-3.5 w-3.5 animate-spin" /> : <Upload className="h-3.5 w-3.5" />}
          Subir imagen
        </button>
      )}
      <input
        ref={inputRef}
        type="file"
        accept="image/png,image/jpeg,image/webp"
        className="hidden"
        onChange={(e) => { const f = e.target.files?.[0]; if (f) handleFile(f); e.target.value = ''; }}
      />
    </div>
  );
}

// ── Bloque de vínculo Firma Simple (badge + botones) ──────────────
function VincularUsuarioBlock({
  estado, motivo, usuarioVinculado, onVincular, onQuitar, quitando,
}: {
  estado: EstadoFirmaSimple;
  motivo: string | null;
  usuarioVinculado: { idUsuario: number; usuario: string; activo: boolean } | null;
  onVincular: () => void;
  onQuitar: () => void;
  quitando: boolean;
}) {
  const badge = FIRMA_SIMPLE_BADGE[estado];
  return (
    <div className="space-y-1.5">
      <p className="text-xs font-medium text-muted-foreground">Firma Simple DOC360</p>
      <Badge variant="outline" className={cn('text-xs', badge.cls)}>{badge.label}</Badge>
      {usuarioVinculado ? (
        <div className="flex items-center gap-2 flex-wrap">
          <p className="text-xs">
            Usuario DOC360: <span className="font-mono font-medium">{usuarioVinculado.usuario}</span>
            {!usuarioVinculado.activo && <span className="text-destructive ml-1">(inactivo)</span>}
          </p>
        </div>
      ) : motivo ? (
        <p className="text-xs text-muted-foreground italic">{motivo}</p>
      ) : null}
      <div className="flex gap-2">
        <button
          type="button"
          onClick={onVincular}
          className="flex items-center gap-1.5 h-7 px-2.5 text-xs rounded-lg border border-dashed border-input hover:border-primary/50 hover:bg-muted/30 transition-all text-muted-foreground"
        >
          <Link2 className="h-3 w-3" />
          {usuarioVinculado ? 'Cambiar usuario' : 'Vincular usuario DOC360'}
        </button>
        {usuarioVinculado && (
          <button
            type="button"
            onClick={onQuitar}
            disabled={quitando}
            className="flex items-center gap-1.5 h-7 px-2.5 text-xs rounded-lg border border-dashed border-input hover:border-destructive/50 hover:bg-destructive/5 transition-all text-muted-foreground"
          >
            {quitando ? <RefreshCw className="h-3 w-3 animate-spin" /> : <Link2Off className="h-3 w-3" />}
            Quitar vínculo
          </button>
        )}
      </div>
    </div>
  );
}

// ── Modal: vincular usuario DOC360 a un slot de firmante ──────────
function VincularUsuarioModal({
  idJefatura, tipo, tipoLabel, actual, onClose, onVinculado,
}: {
  idJefatura: number;
  tipo: TipoJefatura;
  tipoLabel: string;
  actual: { idUsuario: number; usuario: string } | null;
  onClose: () => void;
  onVinculado: () => void;
}) {
  const [q, setQ] = useState('');
  const debouncedQ = useDebounce(q, 250);

  const { data: candidatos = [], isFetching } = useQuery({
    queryKey: ['jefatura-usuarios-vinculables', idJefatura, debouncedQ],
    queryFn: async () => {
      const res = await apiClient.get<{ ok: boolean; data: CandidatoUsuario[] }>(`/jefaturas/${idJefatura}/usuarios-vinculables`, {
        params: { q: debouncedQ || undefined },
      });
      return res.data.data;
    },
  });

  const vincularMut = useMutation({
    mutationFn: (idUsuario: number) =>
      apiClient.patch(`/jefaturas/${idJefatura}/vincular-usuario`, { tipo, idUsuario }),
    onSuccess: () => { toast.success('Usuario vinculado'); onVinculado(); },
    onError: (e: unknown) => {
      toast.error((e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al vincular');
    },
  });

  return (
    <div
      className="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4"
      onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
    >
      <div className="modal-panel bg-background w-full max-w-md max-h-[80vh] overflow-y-auto">
        <div className="flex items-center justify-between px-5 py-4 border-b sticky top-0 bg-background z-10">
          <h3 className="font-semibold text-sm flex items-center gap-2">
            <KeyRound className="h-4 w-4 text-primary" />
            Vincular usuario DOC360 — {tipoLabel}
          </h3>
          <button onClick={onClose} className="text-muted-foreground hover:text-foreground transition-colors">
            <X className="h-4 w-4" />
          </button>
        </div>
        <div className="px-5 py-4 space-y-3">
          {actual && (
            <p className="text-xs text-muted-foreground">
              Vinculado actualmente a <span className="font-mono font-medium">{actual.usuario}</span>.
            </p>
          )}
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
            <input
              type="text"
              placeholder="Buscar por usuario o nombre..."
              value={q}
              onChange={(e) => setQ(e.target.value)}
              autoFocus
              className="w-full h-9 pl-9 pr-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
            />
          </div>
          <div className="rounded-lg border border-input overflow-y-auto max-h-72 divide-y divide-border/50">
            {isFetching ? (
              <p className="px-3 py-4 text-sm text-muted-foreground text-center">Buscando...</p>
            ) : candidatos.length === 0 ? (
              <p className="px-3 py-4 text-sm text-muted-foreground text-center">Sin resultados</p>
            ) : (
              candidatos.map((c) => (
                <button
                  key={c.idUsuario}
                  type="button"
                  disabled={!c.activo || vincularMut.isPending}
                  onClick={() => vincularMut.mutate(c.idUsuario)}
                  className="w-full px-3 py-2.5 text-left text-sm hover:bg-muted/50 transition-colors flex items-center justify-between gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <div className="min-w-0">
                    <p className="font-medium truncate">{c.nombreCompleto ?? c.usuario}</p>
                    <p className="text-xs text-muted-foreground truncate">
                      <span className="font-mono">{c.usuario}</span>
                      {c.descDependencia ? ` · ${c.descDependencia}` : ''}
                    </p>
                  </div>
                  {!c.activo && <Badge variant="outline" className="text-xs shrink-0">Inactivo</Badge>}
                  {c.vinculadoComo && <Badge variant="outline" className="text-xs shrink-0">Ya vinculado</Badge>}
                </button>
              ))
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

// ── Página principal ──────────────────────────────────────────────
export function JefaturasPage() {
  const qc = useQueryClient();
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [modalOpen,  setModalOpen]  = useState(false);
  const [editItem,   setEditItem]   = useState<Jefatura | null>(null);
  const [busqGlobal, setBusqGlobal] = useState('');
  const [busqDep,    setBusqDep]    = useState('');
  const [pagina,     setPagina]     = useState(1);
  const debouncedGlobal = useDebounce(busqGlobal, 200);
  const debouncedDep    = useDebounce(busqDep, 300);
  const [confirmDelete, setConfirmDelete] = useState<Jefatura | null>(null);
  const [vincularTarget, setVincularTarget] = useState<{
    idJefatura: number; tipo: TipoJefatura; tipoLabel: string; actual: { idUsuario: number; usuario: string } | null;
  } | null>(null);
  const [quitandoTipo, setQuitandoTipo] = useState<TipoJefatura | null>(null);

  const [form, setForm] = useState({
    idDependencia: '', nombreTitular: '', cargoTitular: '', rutTitular: '', activoTitular: true,
    vigDesde: '', vigHasta: '',
    nombreSubr: '', cargoSubr: '', rutSubr: '', activoSubr: false,
    vigDesdeSub: '', vigHastaSub: '',
    nombreSubr2: '', cargoSubr2: '', rutSubr2: '', activoSubr2: false,
    vigDesdeSub2: '', vigHastaSub2: '',
  });

  const { data: result, isFetching, refetch } = useQuery({
    queryKey: ['jefaturas', pagina, debouncedGlobal],
    queryFn: async () => {
      const res = await apiClient.get<{ ok: boolean; data: Jefatura[]; meta: PaginacionMeta; kpis: KpisJefaturas }>('/jefaturas', {
        params: { pagina, porPagina: POR_PAGINA, q: debouncedGlobal || undefined },
      });
      return res.data;
    },
    placeholderData: keepPreviousData,
    staleTime: 30_000,
  });

  const jefaturas = result?.data ?? [];
  const meta       = result?.meta;
  const kpis       = result?.kpis;

  const { data: dependencias = [] } = useQuery({
    queryKey: ['dependencias'],
    queryFn: async () => {
      const res = await apiClient.get<{ ok: boolean; data: CatItem[] }>('/catalogos/dependencias');
      return res.data.data;
    },
    staleTime: 5 * 60_000,
  });

  const saveMut = useMutation({
    mutationFn: (body: Record<string, unknown>) => apiClient.post('/jefaturas', body),
    onSuccess: () => {
      toast.success(editItem ? 'Jefatura actualizada' : 'Jefatura creada');
      qc.invalidateQueries({ queryKey: ['jefaturas'] });
      cerrarModal();
    },
    onError: (e: unknown) => {
      toast.error((e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al guardar');
    },
  });

  const deleteMut = useMutation({
    mutationFn: (id: number) => apiClient.delete(`/jefaturas/${id}`),
    onSuccess: () => {
      toast.success('Jefatura eliminada');
      qc.invalidateQueries({ queryKey: ['jefaturas'] });
      setConfirmDelete(null);
      setSelectedId(null);
    },
    onError: () => toast.error('Error al eliminar'),
  });

  const quitarVinculoMut = useMutation({
    mutationFn: ({ idJefatura, tipo }: { idJefatura: number; tipo: TipoJefatura }) =>
      apiClient.patch(`/jefaturas/${idJefatura}/vincular-usuario`, { tipo, idUsuario: null }),
    onMutate: ({ tipo }) => setQuitandoTipo(tipo),
    onSuccess: () => { toast.success('Vínculo quitado'); qc.invalidateQueries({ queryKey: ['jefaturas'] }); },
    onError: () => toast.error('Error al quitar el vínculo'),
    onSettled: () => setQuitandoTipo(null),
  });

  const setF = (k: keyof typeof form, v: unknown) => setForm((p) => ({ ...p, [k]: v }));

  const depsFiltradas = dependencias.filter((d) =>
    d.descripcion.toLowerCase().includes(debouncedDep.toLowerCase())
  );

  function abrirCrear() {
    setEditItem(null);
    setForm({
      idDependencia: '', nombreTitular: '', cargoTitular: '', rutTitular: '', activoTitular: true,
      vigDesde: '', vigHasta: '',
      nombreSubr: '', cargoSubr: '', rutSubr: '', activoSubr: false,
      vigDesdeSub: '', vigHastaSub: '',
      nombreSubr2: '', cargoSubr2: '', rutSubr2: '', activoSubr2: false,
      vigDesdeSub2: '', vigHastaSub2: '',
    });
    setModalOpen(true);
  }

  function abrirEditar(j: Jefatura) {
    setEditItem(j);
    setForm({
      idDependencia: String(j.idDependencia),
      nombreTitular: j.titular.nombre ?? '',
      cargoTitular:  j.titular.cargo  ?? '',
      rutTitular:    j.titular.rut    ?? '',
      activoTitular: j.titular.activo,
      vigDesde:  j.titular.vigenciaDesde ?? '',
      vigHasta:  j.titular.vigenciaHasta ?? '',
      nombreSubr:  j.subrogante.nombre ?? '',
      cargoSubr:   j.subrogante.cargo  ?? '',
      rutSubr:     j.subrogante.rut    ?? '',
      activoSubr:  j.subrogante.activo,
      vigDesdeSub: j.subrogante.vigenciaDesde ?? '',
      vigHastaSub: j.subrogante.vigenciaHasta ?? '',
      nombreSubr2:  j.subrogante2.nombre ?? '',
      cargoSubr2:   j.subrogante2.cargo  ?? '',
      rutSubr2:     j.subrogante2.rut    ?? '',
      activoSubr2:  j.subrogante2.activo,
      vigDesdeSub2: j.subrogante2.vigenciaDesde ?? '',
      vigHastaSub2: j.subrogante2.vigenciaHasta ?? '',
    });
    setModalOpen(true);
  }

  function cerrarModal() { setModalOpen(false); setEditItem(null); setBusqDep(''); }

  function handleGuardar() {
    if (!form.idDependencia) { toast.error('Selecciona un servicio'); return; }
    if (!form.nombreTitular.trim() || !form.cargoTitular.trim()) {
      toast.error('Nombre y cargo del titular son requeridos');
      return;
    }
    saveMut.mutate({
      idDependencia: Number(form.idDependencia),
      nombreTitular: form.nombreTitular.trim(),
      cargoTitular:  form.cargoTitular.trim(),
      rutTitular:    form.rutTitular.trim() || null,
      activoTitular: form.activoTitular,
      vigenciaDesde: form.vigDesde || undefined,
      vigenciaHasta: form.vigHasta || undefined,
      nombreSubrogante:  form.nombreSubr.trim() || undefined,
      cargoSubrogante:   form.cargoSubr.trim() || undefined,
      rutSubrogante:     form.rutSubr.trim() || null,
      activoSubrogante:  form.activoSubr,
      vigenciaDesdeSubr: form.vigDesdeSub || undefined,
      vigenciaHastaSubr: form.vigHastaSub || undefined,
      nombreSubrogante2:  form.nombreSubr2.trim() || undefined,
      cargoSubrogante2:   form.cargoSubr2.trim() || undefined,
      rutSubrogante2:     form.rutSubr2.trim() || null,
      activoSubrogante2:  form.activoSubr2,
      vigenciaDesdeSubr2: form.vigDesdeSub2 || undefined,
      vigenciaHastaSubr2: form.vigHastaSub2 || undefined,
    });
  }

  return (
    <div className="p-4 sm:p-6 space-y-6 max-w-5xl mx-auto">
      {/* Header */}
      <div className="flex flex-col gap-1">
        <div className="flex items-center gap-2.5">
          <span className="icon-3d icon-3d-indigo flex h-10 w-10 shrink-0 items-center justify-center">
            <UserCheck className="h-5 w-5 text-white" />
          </span>
          <div>
            <h1 className="text-xl font-semibold tracking-tight">Jefaturas y Subrogancias</h1>
            <p className="text-sm text-muted-foreground">
              Configura el firmante titular y subrogante por servicio para el Memorándum Institucional
            </p>
          </div>
        </div>
      </div>

      {/* KPI strip */}
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
        {[
          { label: 'Servicios configurados', value: kpis?.total ?? 0,       icon: Building2,   accent: 'indigo' },
          { label: 'Firmantes activos hoy',  value: kpis?.activos ?? 0,     icon: CheckCircle, accent: 'emerald' },
          { label: 'Sin firmante activo',    value: kpis?.sinFirmante ?? 0, icon: AlertCircle, accent: 'amber' },
        ].map(({ label, value, icon: Icon, accent }) => (
          <div key={label} className="kpi-premium px-4 py-3 flex items-center gap-3">
            <span className={cn('icon-3d-sm flex h-9 w-9 shrink-0 items-center justify-center', `icon-3d-${accent}`)}>
              <Icon className="h-4 w-4 text-white" />
            </span>
            <div>
              <p className="text-xl font-bold leading-tight">{value}</p>
              <p className="text-xs text-muted-foreground leading-tight">{label}</p>
            </div>
          </div>
        ))}
      </div>

      {/* Tabla */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between gap-3 flex-wrap">
            <div>
              <CardTitle className="text-base">Listado de Jefaturas</CardTitle>
              <CardDescription>Un registro por servicio/dependencia</CardDescription>
            </div>
            <div className="flex items-center gap-2">
              <Button variant="outline" size="sm" onClick={() => refetch()} disabled={isFetching} className="gap-1.5">
                <RefreshCw className={cn('h-3.5 w-3.5', isFetching && 'animate-spin')} />
                Refrescar
              </Button>
              <Button size="sm" className="gap-2" onClick={abrirCrear}>
                <Plus className="h-3.5 w-3.5" />Nuevo
              </Button>
            </div>
          </div>
          {/* Buscador */}
          <div className="relative mt-2">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
            <input
              type="text"
              placeholder="Buscar por servicio o firmante..."
              value={busqGlobal}
              onChange={(e) => { setBusqGlobal(e.target.value); setPagina(1); }}
              className="w-full h-9 pl-9 pr-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
            />
            {busqGlobal && (
              <button onClick={() => { setBusqGlobal(''); setPagina(1); }} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                <X className="h-3.5 w-3.5" />
              </button>
            )}
          </div>
        </CardHeader>

        <CardContent className="px-0 pb-0">
          {jefaturas.length === 0 ? (
            <div className="flex flex-col items-center py-14 gap-2 text-muted-foreground px-6">
              <ShieldCheck className="h-10 w-10 opacity-20" />
              <p className="text-sm font-medium">
                {busqGlobal ? `Sin resultados para "${busqGlobal}"` : 'Sin jefaturas configuradas'}
              </p>
              {!busqGlobal && (
                <p className="text-xs opacity-60">Crea un registro por cada servicio que genera memorándums</p>
              )}
            </div>
          ) : (
            <div className="divide-y divide-border">
              {jefaturas.map((j) => {
                const isOpen = selectedId === j.id;
                const estadoTit  = resolverEstadoFirmante(j.titular);
                const estadoSub  = j.subrogante.nombre  ? resolverEstadoFirmante(j.subrogante)  : null;
                const estadoSub2 = j.subrogante2.nombre ? resolverEstadoFirmante(j.subrogante2) : null;
                const firmanteEfectivo = estadoTit === 'activo'  ? 'titular'
                  : estadoSub  === 'activo' ? 'subrogante'
                  : estadoSub2 === 'activo' ? 'subrogante2'
                  : null;

                return (
                  <div key={j.id}>
                    {/* Fila colapsable */}
                    <button
                      type="button"
                      onClick={() => setSelectedId(isOpen ? null : j.id)}
                      className={cn(
                        'w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-muted/30 transition-colors',
                        isOpen && 'bg-primary/5',
                      )}
                    >
                      <div className="flex items-center gap-3 min-w-0">
                        <span className={cn(
                          'h-2.5 w-2.5 rounded-full shrink-0',
                          firmanteEfectivo === 'titular'    && 'bg-emerald-500',
                          firmanteEfectivo === 'subrogante' && 'bg-amber-500',
                          firmanteEfectivo === 'subrogante2' && 'bg-orange-400',
                          !firmanteEfectivo                 && 'bg-muted-foreground/30',
                        )} />
                        <div className="min-w-0">
                          <p className="font-medium text-sm truncate">
                            {j.dependencia ?? `Dependencia #${j.idDependencia}`}
                          </p>
                          <p className="text-xs text-muted-foreground truncate">
                            Titular: {j.titular.nombre ?? '—'}
                            {firmanteEfectivo === 'subrogante' && (
                              <span className="ml-2 text-amber-600 font-medium">(subrogante 1 efectivo)</span>
                            )}
                          {firmanteEfectivo === 'subrogante2' && (
                              <span className="ml-2 text-orange-600 font-medium">(subrogante 2 efectivo)</span>
                            )}
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2 shrink-0 ml-3">
                        {firmanteEfectivo === 'titular' && (
                          <Badge variant="outline" className="text-xs border-emerald-300 text-emerald-700 bg-emerald-50">Titular</Badge>
                        )}
                        {(firmanteEfectivo === 'subrogante' || firmanteEfectivo === 'subrogante2') && (
                          <Badge variant="outline" className="text-xs border-amber-300 text-amber-700 bg-amber-50">Subrogante</Badge>
                        )}
                        {!firmanteEfectivo && (
                          <Badge variant="outline" className="text-xs border-rose-300 text-rose-700 bg-rose-50">Sin firmante</Badge>
                        )}
                        <ChevronDown className={cn('h-4 w-4 text-muted-foreground transition-transform duration-200', isOpen && 'rotate-180')} />
                      </div>
                    </button>

                    {/* Detalle expandido */}
                    {isOpen && (
                      <div className="px-5 py-4 bg-muted/20 border-t border-border/50 space-y-4">
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
                          {/* Titular */}
                          <div className="space-y-2.5">
                            <div className="flex items-center gap-2 flex-wrap">
                              <p className="text-xs font-semibold uppercase tracking-wide text-foreground">Titular</p>
                              <Badge variant="outline" className={cn('text-xs', ESTADO_BADGE[estadoTit].cls)}>
                                {ESTADO_BADGE[estadoTit].label}
                              </Badge>
                              {firmanteEfectivo === 'titular' && (
                                <Badge variant="outline" className="text-xs border-emerald-300 text-emerald-700 bg-emerald-50">
                                  Firmante activo
                                </Badge>
                              )}
                            </div>
                            <p className="text-sm font-medium">{j.titular.nombre}</p>
                            <p className="text-xs text-muted-foreground">{j.titular.cargo}</p>
                            {j.titular.rut
                              ? <p className="text-xs text-emerald-600 font-mono">RUT: {j.titular.rut}</p>
                              : <p className="text-xs text-orange-500 italic">Sin RUT — requerido para FirmaGov</p>
                            }
                            {(j.titular.vigenciaDesde || j.titular.vigenciaHasta) && (
                              <p className="text-xs text-muted-foreground flex items-center gap-1">
                                <CalendarClock className="h-3 w-3" />
                                {j.titular.vigenciaDesde ?? '∞'} — {j.titular.vigenciaHasta ?? '∞'}
                              </p>
                            )}
                            <ImagenFirmante
                              label="Firma y timbre (PNG)"
                              url={j.titular.firmaTimbreUrl}
                              idJefatura={j.id}
                              tipoImg="firma_timbre_titular"
                              onUploaded={() => refetch()}
                            />
                            <VincularUsuarioBlock
                              estado={j.titular.estado}
                              motivo={j.titular.motivo}
                              usuarioVinculado={j.titular.usuarioVinculado}
                              quitando={quitandoTipo === 'TITULAR'}
                              onVincular={() => setVincularTarget({
                                idJefatura: j.id, tipo: 'TITULAR', tipoLabel: 'Titular',
                                actual: j.titular.usuarioVinculado,
                              })}
                              onQuitar={() => quitarVinculoMut.mutate({ idJefatura: j.id, tipo: 'TITULAR' })}
                            />
                          </div>

                          {/* Subrogante 1 */}
                          <div className="space-y-2.5">
                            <div className="flex items-center gap-2 flex-wrap">
                              <p className="text-xs font-semibold uppercase tracking-wide text-foreground">Subrogante 1</p>
                              {estadoSub && (
                                <Badge variant="outline" className={cn('text-xs', ESTADO_BADGE[estadoSub].cls)}>
                                  {ESTADO_BADGE[estadoSub].label}
                                </Badge>
                              )}
                              {firmanteEfectivo === 'subrogante' && (
                                <Badge variant="outline" className="text-xs border-amber-300 text-amber-700 bg-amber-50">
                                  Firmante efectivo
                                </Badge>
                              )}
                            </div>
                            {j.subrogante.nombre ? (
                              <>
                                <p className="text-sm font-medium">{j.subrogante.nombre}</p>
                                <p className="text-xs text-muted-foreground">{j.subrogante.cargo}</p>
                                {j.subrogante.rut
                                  ? <p className="text-xs text-emerald-600 font-mono">RUT: {j.subrogante.rut}</p>
                                  : <p className="text-xs text-orange-500 italic">Sin RUT</p>
                                }
                                {(j.subrogante.vigenciaDesde || j.subrogante.vigenciaHasta) && (
                                  <p className="text-xs text-muted-foreground flex items-center gap-1">
                                    <CalendarClock className="h-3 w-3" />
                                    {j.subrogante.vigenciaDesde ?? '∞'} — {j.subrogante.vigenciaHasta ?? '∞'}
                                  </p>
                                )}
                                <ImagenFirmante
                                  label="Firma y timbre (PNG)"
                                  url={j.subrogante.firmaTimbreUrl}
                                  idJefatura={j.id}
                                  tipoImg="firma_timbre_subrogante"
                                  onUploaded={() => refetch()}
                                />
                                <VincularUsuarioBlock
                                  estado={j.subrogante.estado}
                                  motivo={j.subrogante.motivo}
                                  usuarioVinculado={j.subrogante.usuarioVinculado}
                                  quitando={quitandoTipo === 'SUBROGANTE'}
                                  onVincular={() => setVincularTarget({
                                    idJefatura: j.id, tipo: 'SUBROGANTE', tipoLabel: 'Subrogante 1',
                                    actual: j.subrogante.usuarioVinculado,
                                  })}
                                  onQuitar={() => quitarVinculoMut.mutate({ idJefatura: j.id, tipo: 'SUBROGANTE' })}
                                />
                              </>
                            ) : (
                              <p className="text-xs text-muted-foreground italic">No configurado</p>
                            )}
                          </div>

                          {/* Subrogante 2 */}
                          <div className="space-y-2.5">
                            <div className="flex items-center gap-2 flex-wrap">
                              <p className="text-xs font-semibold uppercase tracking-wide text-foreground">Subrogante 2</p>
                              {estadoSub2 && (
                                <Badge variant="outline" className={cn('text-xs', ESTADO_BADGE[estadoSub2].cls)}>
                                  {ESTADO_BADGE[estadoSub2].label}
                                </Badge>
                              )}
                              {firmanteEfectivo === 'subrogante2' && (
                                <Badge variant="outline" className="text-xs border-orange-300 text-orange-700 bg-orange-50">
                                  Firmante efectivo
                                </Badge>
                              )}
                            </div>
                            {j.subrogante2.nombre ? (
                              <>
                                <p className="text-sm font-medium">{j.subrogante2.nombre}</p>
                                <p className="text-xs text-muted-foreground">{j.subrogante2.cargo}</p>
                                {j.subrogante2.rut
                                  ? <p className="text-xs text-emerald-600 font-mono">RUT: {j.subrogante2.rut}</p>
                                  : <p className="text-xs text-orange-500 italic">Sin RUT</p>
                                }
                                {(j.subrogante2.vigenciaDesde || j.subrogante2.vigenciaHasta) && (
                                  <p className="text-xs text-muted-foreground flex items-center gap-1">
                                    <CalendarClock className="h-3 w-3" />
                                    {j.subrogante2.vigenciaDesde ?? '∞'} — {j.subrogante2.vigenciaHasta ?? '∞'}
                                  </p>
                                )}
                                <ImagenFirmante
                                  label="Firma y timbre (PNG)"
                                  url={j.subrogante2.firmaTimbreUrl}
                                  idJefatura={j.id}
                                  tipoImg="firma_timbre_subrogante_2"
                                  onUploaded={() => refetch()}
                                />
                                <VincularUsuarioBlock
                                  estado={j.subrogante2.estado}
                                  motivo={j.subrogante2.motivo}
                                  usuarioVinculado={j.subrogante2.usuarioVinculado}
                                  quitando={quitandoTipo === 'SUBROGANTE_2'}
                                  onVincular={() => setVincularTarget({
                                    idJefatura: j.id, tipo: 'SUBROGANTE_2', tipoLabel: 'Subrogante 2',
                                    actual: j.subrogante2.usuarioVinculado,
                                  })}
                                  onQuitar={() => quitarVinculoMut.mutate({ idJefatura: j.id, tipo: 'SUBROGANTE_2' })}
                                />
                              </>
                            ) : (
                              <p className="text-xs text-muted-foreground italic">No configurado</p>
                            )}
                          </div>
                        </div>

                        {/* Acciones */}
                        <div className="flex justify-end gap-2 pt-1 border-t border-border/40">
                          <Button
                            size="sm" variant="outline"
                            className="h-7 text-xs gap-1.5 text-destructive hover:text-destructive"
                            onClick={() => setConfirmDelete(j)}
                          >
                            <Trash2 className="h-3 w-3" />Eliminar
                          </Button>
                          <Button size="sm" variant="outline" className="h-7 text-xs gap-1.5" onClick={() => abrirEditar(j)}>
                            <Pencil className="h-3 w-3" />Editar datos
                          </Button>
                        </div>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          )}

          {meta && (
            <div className="flex items-center justify-between px-5 py-2 border-t border-border/40">
              <p className="text-xs text-muted-foreground">
                Página {meta.pagina} de {Math.max(1, meta.totalPaginas)} · {meta.total} servicio{meta.total !== 1 ? 's' : ''}
              </p>
              {meta.totalPaginas > 1 && (
                <div className="flex gap-2">
                  <button type="button" className="pagination-pill" disabled={pagina <= 1} onClick={() => setPagina((p) => p - 1)} aria-label="Página anterior">
                    <ChevronLeft className="h-3.5 w-3.5" />
                  </button>
                  <button type="button" className="pagination-pill" disabled={pagina >= meta.totalPaginas} onClick={() => setPagina((p) => p + 1)} aria-label="Página siguiente">
                    <ChevronRight className="h-3.5 w-3.5" />
                  </button>
                </div>
              )}
            </div>
          )}
        </CardContent>
      </Card>

      {/* ── Modal crear/editar ──────────────────────────────────── */}
      {modalOpen && (
        <div
          className="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4"
          onClick={(e) => { if (e.target === e.currentTarget) cerrarModal(); }}
        >
          <div className="modal-panel bg-background w-full max-w-xl max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between px-5 py-4 border-b sticky top-0 bg-background z-10">
              <h3 className="font-semibold text-sm flex items-center gap-2">
                <Users className="h-4 w-4 text-primary" />
                {editItem ? 'Editar jefatura' : 'Nueva jefatura'}
              </h3>
              <button onClick={cerrarModal} className="text-muted-foreground hover:text-foreground transition-colors">
                <X className="h-4 w-4" />
              </button>
            </div>

            <div className="px-5 py-4 space-y-5">
              {/* Dependencia */}
              <div className="space-y-1.5">
                <Label>Servicio / Dependencia <span className="text-destructive text-xs">*</span></Label>
                {editItem ? (
                  <div className="h-9 px-3 flex items-center text-sm rounded-lg border border-input bg-muted/30 text-muted-foreground">
                    {editItem.dependencia ?? `Dependencia #${editItem.idDependencia}`}
                  </div>
                ) : form.idDependencia ? (
                  <div className="flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg bg-primary/5 border border-primary/30 text-sm">
                    <div className="flex items-center gap-2 min-w-0">
                      <Building2 className="h-3.5 w-3.5 text-primary shrink-0" />
                      <span className="font-medium truncate">
                        {dependencias.find((d) => d.id === Number(form.idDependencia))?.descripcion ?? 'Servicio seleccionado'}
                      </span>
                    </div>
                    <button
                      type="button"
                      onClick={() => { setF('idDependencia', ''); setBusqDep(''); }}
                      className="text-muted-foreground hover:text-foreground transition-colors shrink-0"
                    >
                      <X className="h-3.5 w-3.5" />
                    </button>
                  </div>
                ) : (
                  <div className="space-y-1">
                    <div className="relative">
                      <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                      <input
                        type="text"
                        placeholder="Escribe para buscar servicio..."
                        value={busqDep}
                        onChange={(e) => setBusqDep(e.target.value)}
                        autoFocus
                        className="w-full h-9 pl-9 pr-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                      />
                    </div>
                    <div className="rounded-lg border border-input overflow-y-auto max-h-44">
                      {depsFiltradas.length === 0 ? (
                        <p className="px-3 py-4 text-sm text-muted-foreground text-center">
                          {busqDep ? `Sin resultados para "${busqDep}"` : 'Escribe para filtrar los servicios'}
                        </p>
                      ) : (
                        depsFiltradas.map((d) => (
                          <button
                            key={d.id}
                            type="button"
                            onClick={() => { setF('idDependencia', String(d.id)); setBusqDep(''); }}
                            className="w-full px-3 py-2 text-left text-sm hover:bg-muted/50 transition-colors border-b border-border/40 last:border-0 flex items-center gap-2"
                          >
                            <Building2 className="h-3 w-3 text-muted-foreground shrink-0" />
                            {d.descripcion}
                          </button>
                        ))
                      )}
                    </div>
                  </div>
                )}
              </div>

              {/* Titular */}
              <div className="space-y-3">
                <p className="text-xs font-semibold uppercase tracking-wide border-b pb-1">Firmante Titular</p>
                <div className="grid grid-cols-2 gap-3">
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Nombre completo <span className="text-destructive text-xs">*</span></Label>
                    <input type="text" value={form.nombreTitular} onChange={(e) => setF('nombreTitular', e.target.value)} placeholder="Dr. Juan Pérez González" className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Cargo <span className="text-destructive text-xs">*</span></Label>
                    <input type="text" value={form.cargoTitular} onChange={(e) => setF('cargoTitular', e.target.value)} placeholder="Director del Servicio" className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm flex items-center gap-1">
                      RUT <span className="text-muted-foreground font-normal">(requerido para FirmaGov)</span>
                    </Label>
                    <input type="text" value={form.rutTitular} onChange={(e) => setF('rutTitular', e.target.value)} placeholder="12345678-9" className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia desde</Label>
                    <input type="date" value={form.vigDesde} onChange={(e) => setF('vigDesde', e.target.value)} className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia hasta</Label>
                    <input type="date" value={form.vigHasta} onChange={(e) => setF('vigHasta', e.target.value)} className={inCls} />
                  </div>
                  <div className="col-span-2">
                    <label className="flex items-center gap-2 cursor-pointer">
                      <input type="checkbox" checked={form.activoTitular} onChange={(e) => setF('activoTitular', e.target.checked)} className="h-4 w-4" />
                      <span className="text-sm">Titular activo</span>
                    </label>
                  </div>
                </div>
              </div>

              {/* Subrogante 1 */}
              <div className="space-y-3">
                <p className="text-xs font-semibold uppercase tracking-wide border-b pb-1">Firmante Subrogante 1 (opcional)</p>
                <div className="grid grid-cols-2 gap-3">
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Nombre completo</Label>
                    <input type="text" value={form.nombreSubr} onChange={(e) => setF('nombreSubr', e.target.value)} placeholder="Dra. María López" className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Cargo</Label>
                    <input type="text" value={form.cargoSubr} onChange={(e) => setF('cargoSubr', e.target.value)} placeholder="Subdirectora del Servicio" className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">RUT</Label>
                    <input type="text" value={form.rutSubr} onChange={(e) => setF('rutSubr', e.target.value)} placeholder="12345678-9" className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia desde</Label>
                    <input type="date" value={form.vigDesdeSub} onChange={(e) => setF('vigDesdeSub', e.target.value)} className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia hasta</Label>
                    <input type="date" value={form.vigHastaSub} onChange={(e) => setF('vigHastaSub', e.target.value)} className={inCls} />
                  </div>
                  <div className="col-span-2">
                    <label className="flex items-center gap-2 cursor-pointer">
                      <input type="checkbox" checked={form.activoSubr} onChange={(e) => setF('activoSubr', e.target.checked)} className="h-4 w-4" />
                      <span className="text-sm">Subrogante 1 activo</span>
                    </label>
                  </div>
                </div>
              </div>

              {/* Subrogante 2 */}
              <div className="space-y-3">
                <p className="text-xs font-semibold uppercase tracking-wide border-b pb-1">Firmante Subrogante 2 (opcional)</p>
                <div className="grid grid-cols-2 gap-3">
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Nombre completo</Label>
                    <input type="text" value={form.nombreSubr2} onChange={(e) => setF('nombreSubr2', e.target.value)} placeholder="Dr. Carlos Ramírez" className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Cargo</Label>
                    <input type="text" value={form.cargoSubr2} onChange={(e) => setF('cargoSubr2', e.target.value)} placeholder="Jefe de Turno" className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">RUT</Label>
                    <input type="text" value={form.rutSubr2} onChange={(e) => setF('rutSubr2', e.target.value)} placeholder="12345678-9" className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia desde</Label>
                    <input type="date" value={form.vigDesdeSub2} onChange={(e) => setF('vigDesdeSub2', e.target.value)} className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia hasta</Label>
                    <input type="date" value={form.vigHastaSub2} onChange={(e) => setF('vigHastaSub2', e.target.value)} className={inCls} />
                  </div>
                  <div className="col-span-2">
                    <label className="flex items-center gap-2 cursor-pointer">
                      <input type="checkbox" checked={form.activoSubr2} onChange={(e) => setF('activoSubr2', e.target.checked)} className="h-4 w-4" />
                      <span className="text-sm">Subrogante 2 activo</span>
                    </label>
                  </div>
                </div>
              </div>

              <p className="text-xs text-muted-foreground">
                Las imágenes de firma y timbre se suben desde la ficha del firmante después de guardar.
              </p>
            </div>

            <div className="flex justify-end gap-2 px-5 py-4 border-t sticky bottom-0 bg-background">
              <Button variant="outline" size="sm" onClick={cerrarModal} disabled={saveMut.isPending}>Cancelar</Button>
              <Button size="sm" onClick={handleGuardar} disabled={saveMut.isPending} className="gap-2">
                {saveMut.isPending && <RefreshCw className="h-3.5 w-3.5 animate-spin" />}
                {editItem ? 'Guardar cambios' : 'Crear jefatura'}
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* ── Modal confirmar eliminación ──────────────────────────── */}
      {confirmDelete && (
        <div
          className="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4"
          onClick={(e) => { if (e.target === e.currentTarget) setConfirmDelete(null); }}
        >
          <div className="modal-panel bg-background w-full max-w-sm p-6 space-y-4">
            <div className="flex items-center gap-3">
              <span className="icon-3d icon-3d-red flex h-10 w-10 shrink-0 items-center justify-center">
                <Trash2 className="h-5 w-5 text-white" />
              </span>
              <div>
                <p className="font-semibold text-sm">Eliminar jefatura</p>
                <p className="text-xs text-muted-foreground mt-0.5">Esta acción no se puede deshacer</p>
              </div>
            </div>
            <p className="text-sm text-muted-foreground">
              ¿Eliminar la jefatura de <strong>{confirmDelete.dependencia}</strong>? Se perderán los datos y las imágenes de firma asociadas.
            </p>
            <div className="flex justify-end gap-2 pt-2">
              <Button variant="outline" size="sm" onClick={() => setConfirmDelete(null)} disabled={deleteMut.isPending}>
                Cancelar
              </Button>
              <Button
                variant="destructive" size="sm"
                onClick={() => deleteMut.mutate(confirmDelete.id)}
                disabled={deleteMut.isPending}
                className="gap-2"
              >
                {deleteMut.isPending && <RefreshCw className="h-3.5 w-3.5 animate-spin" />}
                Eliminar
              </Button>
            </div>
          </div>
        </div>
      )}

      {/* ── Modal vincular usuario DOC360 ─────────────────────────── */}
      {vincularTarget && (
        <VincularUsuarioModal
          idJefatura={vincularTarget.idJefatura}
          tipo={vincularTarget.tipo}
          tipoLabel={vincularTarget.tipoLabel}
          actual={vincularTarget.actual}
          onClose={() => setVincularTarget(null)}
          onVinculado={() => { qc.invalidateQueries({ queryKey: ['jefaturas'] }); setVincularTarget(null); }}
        />
      )}
    </div>
  );
}
