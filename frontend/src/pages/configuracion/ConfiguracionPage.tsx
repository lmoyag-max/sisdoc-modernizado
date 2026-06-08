import { useState, useRef, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  Settings, Upload, ImageIcon, Building2, Save,
  CheckCircle2, RefreshCw, Palette, Monitor, X, Type, Paperclip,
  Plus, Pencil, Search, Power, Database, ChevronDown,
  UserCheck, ImagePlus, ShieldCheck, ArrowRight,
} from 'lucide-react';
import { useDebounce } from '@/hooks/useDebounce';
import { apiClient } from '@/lib/api/client';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { cn, uploadUrl } from '@/lib/utils';

interface UploadRulesConfig {
  extensionesPermitidas: string[];
  maxFileMB:  number;
  maxTotalMB: number;
}

interface SistemaConfig {
  nombreSistema:        string;
  nombreInstitucion:    string;
  logoUrl:              string | null;
  backgroundUrl:        string | null;
  version:              string;
  // Textos del login
  loginNombreSistema:   string;
  loginSubtitulo:       string;
  loginTituloPrincipal: string;
  loginDescripcion:     string;
  loginCard1:           string;
  loginCard2:           string;
  loginCard3:           string;
  loginCard4:           string;
  loginFooter:          string;
  // Reglas de carga
  uploadRules?: UploadRulesConfig;
}

const TODAS_LAS_EXTENSIONES: { ext: string; label: string }[] = [
  { ext: 'pdf',  label: 'PDF'  },
  { ext: 'doc',  label: 'DOC'  },
  { ext: 'docx', label: 'DOCX' },
  { ext: 'xls',  label: 'XLS'  },
  { ext: 'xlsx', label: 'XLSX' },
  { ext: 'png',  label: 'PNG'  },
  { ext: 'jpg',  label: 'JPG'  },
  { ext: 'jpeg', label: 'JPEG' },
  { ext: 'webp', label: 'WEBP' },
  { ext: 'txt',  label: 'TXT'  },
];

const UPLOAD_RULES_DEFAULT: UploadRulesConfig = {
  extensionesPermitidas: ['pdf','doc','docx','xls','xlsx','png','jpg','jpeg','webp'],
  maxFileMB:  20,
  maxTotalMB: 60,
};

// ─── helpers de módulo ───────────────────────────────────────────────────────
const inputCls = 'w-full h-9 px-3 text-sm rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring';
const apiErr   = (e) =>
  e?.response?.data?.error ?? 'Error al guardar';

// ═══════════════════════════════════════════════════════════════════════════
// Mantenedor de Tipos de Documento — búsqueda-primero + tarjeta de detalle
// ═══════════════════════════════════════════════════════════════════════════

function MantenedorTiposDocumento() {
  const qc = useQueryClient();
  const [busqueda,    setBusqueda]    = useState('');
  const [selectedId,  setSelectedId]  = useState(null);
  const [modalCrear,  setModalCrear]  = useState(false);
  const [descNuevo,   setDescNuevo]   = useState('');
  const [modalEditar, setModalEditar] = useState(null);
  const [descEditar,  setDescEditar]  = useState('');
  const [confirmVig,  setConfirmVig]  = useState(null);

  const debouncedQ = useDebounce(busqueda, 300);

  const { data: resultados = [], isFetching } = useQuery({
    queryKey:  ['admin-tipos-documento', debouncedQ],
    queryFn:   async () => {
      const res = await apiClient.get(
        `/configuracion/tipos-documento?q=${encodeURIComponent(debouncedQ)}`
      );
      return res.data.data;
    },
    enabled:         debouncedQ.length > 0,
    staleTime:       30_000,
    placeholderData: [],
  });

  useEffect(() => {
    if (selectedId != null && resultados.length > 0 && !resultados.some((t) => t.id === selectedId)) {
      setSelectedId(null);
    }
  }, [resultados, selectedId]);

  const createMut = useMutation({
    mutationFn: (d) => apiClient.post('/configuracion/tipos-documento', { descripcion: d }),
    onSuccess:  () => { toast.success('Tipo de documento creado'); qc.invalidateQueries({ queryKey: ['admin-tipos-documento'] }); setModalCrear(false); setDescNuevo(''); },
    onError:    (e) => toast.error(apiErr(e)),
  });

  const editMut = useMutation({
    mutationFn: ({ id, d }) =>
      apiClient.put(`/configuracion/tipos-documento/${id}`, { descripcion: d }),
    onSuccess: () => { toast.success('Tipo actualizado'); qc.invalidateQueries({ queryKey: ['admin-tipos-documento'] }); setModalEditar(null); },
    onError:   (e) => toast.error(apiErr(e)),
  });

  const vigMut = useMutation({
    mutationFn: ({ id, vigencia }) =>
      apiClient.patch(`/configuracion/tipos-documento/${id}/vigencia`, { vigencia }),
    onSuccess: (_d, vars) => {
      toast.success(vars.vigencia === 'S' ? 'Tipo activado' : 'Tipo desactivado');
      qc.invalidateQueries({ queryKey: ['admin-tipos-documento'] });
      setConfirmVig(null); setSelectedId(null);
    },
    onError: () => toast.error('Error al cambiar estado'),
  });

  return (
    <>
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between gap-3">
            <div className="flex items-center gap-2">
              <Database className="h-4 w-4 text-primary" />
              <div>
                <CardTitle className="text-base">Tipos de Documento</CardTitle>
                <CardDescription>Busca y administra los tipos de documento del sistema</CardDescription>
              </div>
            </div>
            <Button size="sm" className="gap-2 shrink-0" onClick={() => { setDescNuevo(''); setModalCrear(true); }}>
              <Plus className="h-3.5 w-3.5" />Nuevo tipo
            </Button>
          </div>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
            {isFetching && <RefreshCw className="absolute right-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground animate-spin pointer-events-none" />}
            <input
              type="text" placeholder="Escribe para buscar tipos de documento..."
              value={busqueda}
              onChange={(e) => { setBusqueda(e.target.value); setSelectedId(null); }}
              className="w-full h-9 pl-9 pr-9 text-sm rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
            />
          </div>

          {!debouncedQ && (
            <div className="flex flex-col items-center py-12 gap-2 text-muted-foreground select-none">
              <Search className="h-8 w-8 opacity-20" />
              <p className="text-sm font-medium">Escribe para buscar</p>
              <p className="text-xs opacity-60">Busca por nombre o parte del nombre</p>
            </div>
          )}

          {debouncedQ && !isFetching && resultados.length === 0 && (
            <p className="py-8 text-center text-sm text-muted-foreground">
              Sin resultados para "<span className="font-medium">{debouncedQ}</span>"
            </p>
          )}

          {resultados.length > 0 && (
            <div className="space-y-1.5">
              <p className="text-xs text-muted-foreground px-0.5">
                {resultados.length} resultado{resultados.length !== 1 ? 's' : ''}
                {' · '}{resultados.filter((t) => t.vigencia !== 'N').length} activo{resultados.filter((t) => t.vigencia !== 'N').length !== 1 ? 's' : ''}
              </p>
              <div className="divide-y divide-border rounded-xl border overflow-hidden">
                {resultados.map((t) => {
                  const activo     = t.vigencia !== 'N';
                  const isSelected = selectedId === t.id;
                  return (
                    <div key={t.id}>
                      <button
                        type="button"
                        className={cn(
                          'w-full flex items-center justify-between px-4 py-3 text-left transition-colors hover:bg-muted/30',
                          isSelected && 'bg-primary/5',
                          !activo && 'opacity-55'
                        )}
                        onClick={() => setSelectedId(isSelected ? null : t.id)}
                      >
                        <div className="flex items-center gap-3 min-w-0">
                          <span className={cn('h-2 w-2 rounded-full shrink-0', activo ? 'bg-emerald-500' : 'bg-muted-foreground/30')} />
                          <span className="font-medium text-sm truncate">{t.descripcion}</span>
                          <span className="text-xs text-muted-foreground font-mono shrink-0">#{t.id}</span>
                        </div>
                        <ChevronDown className={cn('h-4 w-4 text-muted-foreground shrink-0 transition-transform duration-200 ml-2', isSelected && 'rotate-180')} />
                      </button>

                      {isSelected && (
                        <div className="px-5 py-4 bg-muted/20 border-t border-border/50 space-y-3">
                          <div className="grid grid-cols-3 gap-3 text-sm">
                            <div>
                              <p className="text-xs text-muted-foreground mb-0.5">ID</p>
                              <p className="font-mono font-semibold">{t.id}</p>
                            </div>
                            <div className="col-span-2">
                              <p className="text-xs text-muted-foreground mb-0.5">Estado</p>
                              <Badge variant="outline" className={cn('text-xs', activo ? 'border-emerald-300 text-emerald-600' : 'border-border text-muted-foreground')}>
                                {activo ? 'Activo' : 'Inactivo'}
                              </Badge>
                            </div>
                            <div className="col-span-3">
                              <p className="text-xs text-muted-foreground mb-0.5">Descripción</p>
                              <p className="font-medium text-sm">{t.descripcion}</p>
                            </div>
                          </div>
                          <div className="flex justify-end gap-2 pt-1 border-t border-border/40">
                            <Button size="sm" variant="outline" className="h-7 text-xs gap-1.5"
                              onClick={() => { setDescEditar(t.descripcion); setModalEditar(t); }}>
                              <Pencil className="h-3 w-3" />Editar
                            </Button>
                            <Button size="sm" variant="ghost"
                              className={cn('h-7 text-xs gap-1.5', activo
                                ? 'text-amber-600 hover:text-amber-700 hover:bg-amber-50'
                                : 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50')}
                              onClick={() => setConfirmVig(t)}>
                              <Power className="h-3 w-3" />{activo ? 'Desactivar' : 'Activar'}
                            </Button>
                          </div>
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            </div>
          )}
          <p className="text-xs text-muted-foreground">Los tipos de documento no se eliminan — solo se desactivan para preservar la trazabilidad histórica.</p>
        </CardContent>
      </Card>

      {modalCrear && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={(e) => { if (e.target === e.currentTarget) { setModalCrear(false); setDescNuevo(''); } }}>
          <div className="bg-background rounded-xl border shadow-xl w-full max-w-md">
            <div className="flex items-center justify-between px-5 py-4 border-b">
              <h3 className="font-semibold text-sm">Nuevo tipo de documento</h3>
              <button onClick={() => { setModalCrear(false); setDescNuevo(''); }} className="text-muted-foreground hover:text-foreground transition-colors"><X className="h-4 w-4" /></button>
            </div>
            <div className="px-5 py-4 space-y-3">
              <div className="space-y-1.5">
                <Label>Descripción <span className="text-muted-foreground font-normal text-xs">(máx. 60 caracteres)</span></Label>
                <input type="text" maxLength={60} value={descNuevo}
                  onChange={(e) => setDescNuevo(e.target.value)}
                  onKeyDown={(e) => { if (e.key === 'Enter' && descNuevo.trim()) createMut.mutate(descNuevo.trim()); }}
                  placeholder="Ej: Ordinario, Oficio, Carta..." className={inputCls} autoFocus />
                <p className="text-xs text-muted-foreground text-right">{descNuevo.length}/60</p>
              </div>
            </div>
            <div className="flex justify-end gap-2 px-5 py-4 border-t">
              <Button variant="outline" size="sm" onClick={() => { setModalCrear(false); setDescNuevo(''); }} disabled={createMut.isPending}>Cancelar</Button>
              <Button size="sm" onClick={() => createMut.mutate(descNuevo.trim())} disabled={createMut.isPending || !descNuevo.trim()} className="gap-2">
                {createMut.isPending && <RefreshCw className="h-3.5 w-3.5 animate-spin" />}Crear tipo
              </Button>
            </div>
          </div>
        </div>
      )}

      {modalEditar && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={(e) => { if (e.target === e.currentTarget) setModalEditar(null); }}>
          <div className="bg-background rounded-xl border shadow-xl w-full max-w-md">
            <div className="flex items-center justify-between px-5 py-4 border-b">
              <h3 className="font-semibold text-sm">Editar tipo #{modalEditar.id}</h3>
              <button onClick={() => setModalEditar(null)} className="text-muted-foreground hover:text-foreground transition-colors"><X className="h-4 w-4" /></button>
            </div>
            <div className="px-5 py-4 space-y-3">
              <div className="space-y-1.5">
                <Label>Descripción <span className="text-muted-foreground font-normal text-xs">(máx. 60 caracteres)</span></Label>
                <input type="text" maxLength={60} value={descEditar}
                  onChange={(e) => setDescEditar(e.target.value)}
                  onKeyDown={(e) => { if (e.key === 'Enter' && descEditar.trim()) editMut.mutate({ id: modalEditar.id, d: descEditar.trim() }); }}
                  className={inputCls} autoFocus />
                <p className="text-xs text-muted-foreground text-right">{descEditar.length}/60</p>
              </div>
            </div>
            <div className="flex justify-end gap-2 px-5 py-4 border-t">
              <Button variant="outline" size="sm" onClick={() => setModalEditar(null)} disabled={editMut.isPending}>Cancelar</Button>
              <Button size="sm" onClick={() => editMut.mutate({ id: modalEditar.id, d: descEditar.trim() })} disabled={editMut.isPending || !descEditar.trim()} className="gap-2">
                {editMut.isPending && <RefreshCw className="h-3.5 w-3.5 animate-spin" />}Guardar cambios
              </Button>
            </div>
          </div>
        </div>
      )}

      {confirmVig && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={(e) => { if (e.target === e.currentTarget) setConfirmVig(null); }}>
          <div className="bg-background rounded-xl border shadow-xl w-full max-w-sm">
            <div className="px-5 py-5 space-y-3">
              <div className="flex items-center gap-3">
                <div className={cn('flex h-10 w-10 items-center justify-center rounded-xl shrink-0',
                  confirmVig.vigencia !== 'N' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600')}>
                  <Power className="h-5 w-5" />
                </div>
                <div>
                  <p className="font-semibold text-sm">{confirmVig.vigencia !== 'N' ? 'Desactivar tipo' : 'Activar tipo'}</p>
                  <p className="text-xs text-muted-foreground mt-0.5">{confirmVig.descripcion}</p>
                </div>
              </div>
              <p className="text-sm text-muted-foreground">
                {confirmVig.vigencia !== 'N'
                  ? 'El tipo dejará de aparecer en el formulario de nuevo documento. Los documentos históricos no se verán afectados.'
                  : 'El tipo volverá a estar disponible en los formularios del sistema.'}
              </p>
            </div>
            <div className="flex justify-end gap-2 px-5 py-4 border-t">
              <Button variant="outline" size="sm" onClick={() => setConfirmVig(null)} disabled={vigMut.isPending}>Cancelar</Button>
              <Button size="sm" variant={confirmVig.vigencia !== 'N' ? 'destructive' : 'default'}
                onClick={() => vigMut.mutate({ id: confirmVig.id, vigencia: confirmVig.vigencia !== 'N' ? 'N' : 'S' })}
                disabled={vigMut.isPending} className="gap-2">
                {vigMut.isPending && <RefreshCw className="h-3.5 w-3.5 animate-spin" />}
                {confirmVig.vigencia !== 'N' ? 'Sí, desactivar' : 'Sí, activar'}
              </Button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

// ═══════════════════════════════════════════════════════════════════════════
// Mantenedor de Dependencias / Servicios — búsqueda-primero + tarjeta
// ═══════════════════════════════════════════════════════════════════════════

const DEP_FORM_EMPTY = { descripcion: '', codigo: '', codInterno: '', ofpartes: 'N', codigoNuevo: '' };

function depFormInputs(form, setF) {
  return (
    <>
      <div className="space-y-1.5">
        <Label>Nombre del servicio <span className="text-destructive text-xs">*</span></Label>
        <input type="text" maxLength={60} value={form.descripcion}
          onChange={(e) => setF('descripcion', e.target.value)}
          placeholder="Ej: Abastecimiento, Adquisiciones..." className={inputCls} autoFocus />
        <p className="text-xs text-muted-foreground text-right">{form.descripcion.length}/60</p>
      </div>
      <div className="grid grid-cols-2 gap-3">
        <div className="space-y-1.5">
          <Label className="text-sm">Código <span className="text-muted-foreground font-normal text-xs">(máx. 6)</span></Label>
          <input type="text" maxLength={6} value={form.codigo}
            onChange={(e) => setF('codigo', e.target.value)} placeholder="ABC" className={cn(inputCls, 'font-mono')} />
        </div>
        <div className="space-y-1.5">
          <Label className="text-sm">Código nuevo <span className="text-muted-foreground font-normal text-xs">(máx. 6)</span></Label>
          <input type="text" maxLength={6} value={form.codigoNuevo}
            onChange={(e) => setF('codigoNuevo', e.target.value)} placeholder="XYZ" className={cn(inputCls, 'font-mono')} />
        </div>
      </div>
      <div className="grid grid-cols-2 gap-3">
        <div className="space-y-1.5">
          <Label className="text-sm">Código interno</Label>
          <input type="number" value={form.codInterno}
            onChange={(e) => setF('codInterno', e.target.value)} placeholder="0" className={inputCls} />
        </div>
        <div className="space-y-1.5">
          <Label className="text-sm">Oficina de partes</Label>
          <select value={form.ofpartes} onChange={(e) => setF('ofpartes', e.target.value)}
            className="w-full h-9 px-3 text-sm rounded-lg border border-input bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring">
            <option value="N">No</option>
            <option value="S">Sí</option>
          </select>
        </div>
      </div>
    </>
  );
}

function MantenedorDependencias() {
  const qc = useQueryClient();
  const [busqueda,    setBusqueda]    = useState('');
  const [selectedId,  setSelectedId]  = useState(null);
  const [modalCrear,  setModalCrear]  = useState(false);
  const [formCrear,   setFormCrear]   = useState(DEP_FORM_EMPTY);
  const [modalEditar, setModalEditar] = useState(null);
  const [formEditar,  setFormEditar]  = useState(DEP_FORM_EMPTY);
  const [confirmVig,  setConfirmVig]  = useState(null);

  const debouncedQ = useDebounce(busqueda, 300);

  const { data: resultados = [], isFetching } = useQuery({
    queryKey:  ['admin-dependencias', debouncedQ],
    queryFn:   async () => {
      const res = await apiClient.get(
        `/configuracion/dependencias?q=${encodeURIComponent(debouncedQ)}`
      );
      return res.data.data;
    },
    enabled:         debouncedQ.length > 0,
    staleTime:       30_000,
    placeholderData: [],
  });

  useEffect(() => {
    if (selectedId != null && resultados.length > 0 && !resultados.some((d) => d.id === selectedId)) {
      setSelectedId(null);
    }
  }, [resultados, selectedId]);

  const createMut = useMutation({
    mutationFn: (f) => apiClient.post('/configuracion/dependencias', {
      descripcion: f.descripcion, codigo: f.codigo || null,
      codInterno: f.codInterno ? Number(f.codInterno) : null,
      ofpartes: f.ofpartes, codigoNuevo: f.codigoNuevo || null,
    }),
    onSuccess: () => { toast.success('Servicio creado'); qc.invalidateQueries({ queryKey: ['admin-dependencias'] }); setModalCrear(false); setFormCrear(DEP_FORM_EMPTY); },
    onError:   (e) => toast.error(apiErr(e)),
  });

  const editMut = useMutation({
    mutationFn: ({ id, f }) =>
      apiClient.put(`/configuracion/dependencias/${id}`, {
        descripcion: f.descripcion, codigo: f.codigo || null,
        codInterno: f.codInterno ? Number(f.codInterno) : null,
        ofpartes: f.ofpartes, codigoNuevo: f.codigoNuevo || null,
      }),
    onSuccess: () => { toast.success('Servicio actualizado'); qc.invalidateQueries({ queryKey: ['admin-dependencias'] }); setModalEditar(null); },
    onError:   (e) => toast.error(apiErr(e)),
  });

  const vigMut = useMutation({
    mutationFn: ({ id, vigencia }) =>
      apiClient.patch(`/configuracion/dependencias/${id}/vigencia`, { vigencia }),
    onSuccess: (_d, vars) => {
      toast.success(vars.vigencia === 'S' ? 'Servicio activado' : 'Servicio desactivado');
      qc.invalidateQueries({ queryKey: ['admin-dependencias'] });
      setConfirmVig(null); setSelectedId(null);
    },
    onError: () => toast.error('Error al cambiar vigencia'),
  });

  const abrirEditar = (dep) => {
    setFormEditar({
      descripcion: dep.descripcion, codigo: dep.codigo ?? '',
      codInterno: dep.codInterno != null ? String(dep.codInterno) : '',
      ofpartes: dep.ofpartes === 'S' ? 'S' : 'N', codigoNuevo: dep.codigoNuevo ?? '',
    });
    setModalEditar(dep);
  };

  const setFC = (k, v) => setFormCrear((p) => ({ ...p, [k]: v }));
  const setFE = (k, v) => setFormEditar((p) => ({ ...p, [k]: v }));

  return (
    <>
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between gap-3">
            <div className="flex items-center gap-2">
              <Building2 className="h-4 w-4 text-primary" />
              <div>
                <CardTitle className="text-base">Servicios / Dependencias</CardTitle>
                <CardDescription>Busca y administra los servicios del sistema</CardDescription>
              </div>
            </div>
            <Button size="sm" className="gap-2 shrink-0" onClick={() => { setFormCrear(DEP_FORM_EMPTY); setModalCrear(true); }}>
              <Plus className="h-3.5 w-3.5" />Nuevo servicio
            </Button>
          </div>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
            {isFetching && <RefreshCw className="absolute right-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground animate-spin pointer-events-none" />}
            <input
              type="text" placeholder="Escribe para buscar servicios o dependencias..."
              value={busqueda}
              onChange={(e) => { setBusqueda(e.target.value); setSelectedId(null); }}
              className="w-full h-9 pl-9 pr-9 text-sm rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
            />
          </div>

          {!debouncedQ && (
            <div className="flex flex-col items-center py-12 gap-2 text-muted-foreground select-none">
              <Search className="h-8 w-8 opacity-20" />
              <p className="text-sm font-medium">Escribe para buscar</p>
              <p className="text-xs opacity-60">Busca por nombre o código</p>
            </div>
          )}

          {debouncedQ && !isFetching && resultados.length === 0 && (
            <p className="py-8 text-center text-sm text-muted-foreground">
              Sin resultados para "<span className="font-medium">{debouncedQ}</span>"
            </p>
          )}

          {resultados.length > 0 && (
            <div className="space-y-1.5">
              <p className="text-xs text-muted-foreground px-0.5">
                {resultados.length} resultado{resultados.length !== 1 ? 's' : ''}
                {' · '}{resultados.filter((d) => d.vigencia === 'S').length} activo{resultados.filter((d) => d.vigencia === 'S').length !== 1 ? 's' : ''}
              </p>
              <div className="divide-y divide-border rounded-xl border overflow-hidden">
                {resultados.map((dep) => {
                  const activo     = dep.vigencia === 'S';
                  const isSelected = selectedId === dep.id;
                  return (
                    <div key={dep.id}>
                      <button
                        type="button"
                        className={cn(
                          'w-full flex items-center justify-between px-4 py-3 text-left transition-colors hover:bg-muted/30',
                          isSelected && 'bg-primary/5',
                          !activo && 'opacity-55'
                        )}
                        onClick={() => setSelectedId(isSelected ? null : dep.id)}
                      >
                        <div className="flex items-center gap-3 min-w-0">
                          <span className={cn('h-2 w-2 rounded-full shrink-0', activo ? 'bg-emerald-500' : 'bg-muted-foreground/30')} />
                          <span className="font-medium text-sm truncate">{dep.descripcion}</span>
                          <span className="text-xs text-muted-foreground font-mono shrink-0">#{dep.id}</span>
                        </div>
                        <div className="flex items-center gap-2 shrink-0 ml-2">
                          {dep.codigo && <span className="text-xs font-mono text-muted-foreground hidden sm:inline">{dep.codigo}</span>}
                          <ChevronDown className={cn('h-4 w-4 text-muted-foreground transition-transform duration-200', isSelected && 'rotate-180')} />
                        </div>
                      </button>

                      {isSelected && (
                        <div className="px-5 py-4 bg-muted/20 border-t border-border/50 space-y-3">
                          <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                            <div>
                              <p className="text-xs text-muted-foreground mb-0.5">ID</p>
                              <p className="font-mono font-semibold">{dep.id}</p>
                            </div>
                            <div>
                              <p className="text-xs text-muted-foreground mb-0.5">Código</p>
                              <p className="font-mono">{dep.codigo || '—'}</p>
                            </div>
                            <div>
                              <p className="text-xs text-muted-foreground mb-0.5">Cód. Interno</p>
                              <p className="font-mono">{dep.codInterno ?? '—'}</p>
                            </div>
                            <div>
                              <p className="text-xs text-muted-foreground mb-0.5">Oficina de Partes</p>
                              <p>{dep.ofpartes === 'S' ? 'Sí' : 'No'}</p>
                            </div>
                            <div>
                              <p className="text-xs text-muted-foreground mb-0.5">Estado</p>
                              <Badge variant="outline" className={cn('text-xs', activo ? 'border-emerald-300 text-emerald-600' : 'border-border text-muted-foreground')}>
                                {activo ? 'Activo' : 'Inactivo'}
                              </Badge>
                            </div>
                            {dep.codigoNuevo && (
                              <div>
                                <p className="text-xs text-muted-foreground mb-0.5">Código nuevo</p>
                                <p className="font-mono">{dep.codigoNuevo}</p>
                              </div>
                            )}
                          </div>
                          <div className="flex justify-end gap-2 pt-1 border-t border-border/40">
                            <Button size="sm" variant="outline" className="h-7 text-xs gap-1.5" onClick={() => abrirEditar(dep)}>
                              <Pencil className="h-3 w-3" />Editar
                            </Button>
                            <Button size="sm" variant="ghost"
                              className={cn('h-7 text-xs gap-1.5', activo
                                ? 'text-amber-600 hover:text-amber-700 hover:bg-amber-50'
                                : 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50')}
                              onClick={() => setConfirmVig(dep)}>
                              <Power className="h-3 w-3" />{activo ? 'Desactivar' : 'Activar'}
                            </Button>
                          </div>
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            </div>
          )}
          <p className="text-xs text-muted-foreground">Los servicios no se eliminan físicamente — solo se desactivan para preservar la trazabilidad histórica.</p>
        </CardContent>
      </Card>

      {modalCrear && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={(e) => { if (e.target === e.currentTarget) { setModalCrear(false); setFormCrear(DEP_FORM_EMPTY); } }}>
          <div className="bg-background rounded-xl border shadow-xl w-full max-w-lg">
            <div className="flex items-center justify-between px-5 py-4 border-b">
              <h3 className="font-semibold text-sm">Nuevo servicio / dependencia</h3>
              <button onClick={() => { setModalCrear(false); setFormCrear(DEP_FORM_EMPTY); }} className="text-muted-foreground hover:text-foreground transition-colors"><X className="h-4 w-4" /></button>
            </div>
            <div className="px-5 py-4 space-y-4">{depFormInputs(formCrear, setFC)}</div>
            <div className="flex justify-end gap-2 px-5 py-4 border-t">
              <Button variant="outline" size="sm" onClick={() => { setModalCrear(false); setFormCrear(DEP_FORM_EMPTY); }} disabled={createMut.isPending}>Cancelar</Button>
              <Button size="sm" onClick={() => createMut.mutate(formCrear)} disabled={createMut.isPending || !formCrear.descripcion.trim()} className="gap-2">
                {createMut.isPending && <RefreshCw className="h-3.5 w-3.5 animate-spin" />}Crear servicio
              </Button>
            </div>
          </div>
        </div>
      )}

      {modalEditar && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={(e) => { if (e.target === e.currentTarget) setModalEditar(null); }}>
          <div className="bg-background rounded-xl border shadow-xl w-full max-w-lg">
            <div className="flex items-center justify-between px-5 py-4 border-b">
              <h3 className="font-semibold text-sm">Editar servicio #{modalEditar.id}</h3>
              <button onClick={() => setModalEditar(null)} className="text-muted-foreground hover:text-foreground transition-colors"><X className="h-4 w-4" /></button>
            </div>
            <div className="px-5 py-4 space-y-4">{depFormInputs(formEditar, setFE)}</div>
            <div className="flex justify-end gap-2 px-5 py-4 border-t">
              <Button variant="outline" size="sm" onClick={() => setModalEditar(null)} disabled={editMut.isPending}>Cancelar</Button>
              <Button size="sm" onClick={() => editMut.mutate({ id: modalEditar.id, f: formEditar })} disabled={editMut.isPending || !formEditar.descripcion.trim()} className="gap-2">
                {editMut.isPending && <RefreshCw className="h-3.5 w-3.5 animate-spin" />}Guardar cambios
              </Button>
            </div>
          </div>
        </div>
      )}

      {confirmVig && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={(e) => { if (e.target === e.currentTarget) setConfirmVig(null); }}>
          <div className="bg-background rounded-xl border shadow-xl w-full max-w-sm">
            <div className="px-5 py-5 space-y-3">
              <div className="flex items-center gap-3">
                <div className={cn('flex h-10 w-10 items-center justify-center rounded-xl shrink-0',
                  confirmVig.vigencia === 'S' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600')}>
                  <Power className="h-5 w-5" />
                </div>
                <div>
                  <p className="font-semibold text-sm">{confirmVig.vigencia === 'S' ? 'Desactivar servicio' : 'Activar servicio'}</p>
                  <p className="text-xs text-muted-foreground mt-0.5">{confirmVig.descripcion}</p>
                </div>
              </div>
              <p className="text-sm text-muted-foreground">
                {confirmVig.vigencia === 'S'
                  ? 'El servicio dejará de aparecer en formularios. Los documentos históricos no se verán afectados.'
                  : 'El servicio volverá a estar disponible en los formularios del sistema.'}
              </p>
            </div>
            <div className="flex justify-end gap-2 px-5 py-4 border-t">
              <Button variant="outline" size="sm" onClick={() => setConfirmVig(null)} disabled={vigMut.isPending}>Cancelar</Button>
              <Button size="sm" variant={confirmVig.vigencia === 'S' ? 'destructive' : 'default'}
                onClick={() => vigMut.mutate({ id: confirmVig.id, vigencia: confirmVig.vigencia === 'S' ? 'N' : 'S' })}
                disabled={vigMut.isPending} className="gap-2">
                {vigMut.isPending && <RefreshCw className="h-3.5 w-3.5 animate-spin" />}
                {confirmVig.vigencia === 'S' ? 'Sí, desactivar' : 'Sí, activar'}
              </Button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}


// ═══════════════════════════════════════════════════════════════════════════
// Mantenedor de Firmantes para Memorándum
// ═══════════════════════════════════════════════════════════════════════════

interface CatItem { id: number; descripcion: string }

interface FirmanteAPI {
  id: number;
  idDependencia: number;
  dependencia:   string | null;
  titular: {
    nombre: string; cargo: string;
    firmaTimbreUrl: string | null;   // imagen combinada firma+timbre
    activo: boolean; vigenciaDesde: string | null; vigenciaHasta: string | null;
  };
  subrogante: {
    nombre: string | null; cargo: string | null;
    firmaTimbreUrl: string | null;   // imagen combinada firma+timbre
    activo: boolean; vigenciaDesde: string | null; vigenciaHasta: string | null;
  };
}

function ImagenFirmante({
  label, url, idFirmante, tipoImg, onUploaded, disabled,
}: {
  label: string; url: string | null; idFirmante: number;
  tipoImg: string; onUploaded: () => void; disabled?: boolean;
}) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [uploading, setUploading] = useState(false);

  async function handleFile(file: File) {
    setUploading(true);
    try {
      const form = new FormData();
      form.append('imagen', file);
      await apiClient.post(`/memorandum/firmantes/${idFirmante}/imagen?tipo=${tipoImg}`, form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      toast.success(`${label} guardada`);
      onUploaded();
    } catch {
      toast.error(`Error al subir ${label}`);
    } finally {
      setUploading(false);
    }
  }

  return (
    <div className="space-y-1.5">
      <p className="text-xs font-medium text-muted-foreground">{label}</p>
      {url ? (
        <div className="relative group w-24 h-14 rounded-lg border border-border overflow-hidden bg-muted/20">
          <img src={`${url}?t=${Date.now()}`} alt={label} className="w-full h-full object-contain p-1" />
          <button
            type="button"
            disabled={disabled || uploading}
            onClick={() => inputRef.current?.click()}
            className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100"
          >
            <RefreshCw className="h-4 w-4 text-white" />
          </button>
        </div>
      ) : (
        <button
          type="button"
          disabled={disabled || uploading}
          onClick={() => inputRef.current?.click()}
          className="flex flex-col items-center justify-center gap-1 w-24 h-14 rounded-lg border-2 border-dashed border-border hover:border-primary/50 transition-colors text-muted-foreground disabled:opacity-50"
        >
          {uploading ? <RefreshCw className="h-4 w-4 animate-spin" /> : <ImagePlus className="h-4 w-4" />}
          <span className="text-[10px]">{uploading ? 'Subiendo…' : 'Subir'}</span>
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

function MantenedorFirmantes() {
  const qc = useQueryClient();
  const [selectedId, setSelectedId]   = useState<number | null>(null);
  const [modalOpen,  setModalOpen]    = useState(false);
  const [editItem,   setEditItem]     = useState<FirmanteAPI | null>(null);
  const [busqDep,    setBusqDep]      = useState('');
  const debouncedDep = useDebounce(busqDep, 300);

  // Formulario
  const [form, setForm] = useState({
    idDependencia: '', nombreTitular: '', cargoTitular: '', activoTitular: true,
    vigDesde: '', vigHasta: '',
    nombreSubr: '', cargoSubr: '', activoSubr: false,
    vigDesdeSub: '', vigHastaSub: '',
  });

  const { data: firmantes = [], isFetching, refetch } = useQuery({
    queryKey: ['memo-firmantes'],
    queryFn: async () => {
      const res = await apiClient.get<{ ok: boolean; data: FirmanteAPI[] }>('/memorandum/firmantes');
      return res.data.data;
    },
    staleTime: 30_000,
  });

  const { data: dependencias = [] } = useQuery({
    queryKey: ['dependencias'],
    queryFn: async () => {
      const res = await apiClient.get<{ ok: boolean; data: CatItem[] }>('/catalogos/dependencias');
      return res.data.data;
    },
    staleTime: 5 * 60_000,
  });

  const depsFiltradas = dependencias.filter((d) =>
    d.descripcion.toLowerCase().includes(debouncedDep.toLowerCase())
  );

  const saveMut = useMutation({
    mutationFn: (body: Record<string, unknown>) =>
      apiClient.post('/memorandum/firmantes', body),
    onSuccess: () => {
      toast.success(editItem ? 'Firmante actualizado' : 'Firmante creado');
      qc.invalidateQueries({ queryKey: ['memo-firmantes'] });
      cerrarModal();
    },
    onError: (e: unknown) => {
      toast.error((e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al guardar');
    },
  });

  function abrirCrear() {
    setEditItem(null);
    setForm({ idDependencia: '', nombreTitular: '', cargoTitular: '', activoTitular: true, vigDesde: '', vigHasta: '', nombreSubr: '', cargoSubr: '', activoSubr: false, vigDesdeSub: '', vigHastaSub: '' });
    setModalOpen(true);
  }

  function abrirEditar(f: FirmanteAPI) {
    setEditItem(f);
    setForm({
      idDependencia: String(f.idDependencia),
      nombreTitular: f.titular.nombre, cargoTitular: f.titular.cargo,
      activoTitular: f.titular.activo, vigDesde: f.titular.vigenciaDesde ?? '', vigHasta: f.titular.vigenciaHasta ?? '',
      nombreSubr: f.subrogante.nombre ?? '', cargoSubr: f.subrogante.cargo ?? '',
      activoSubr: f.subrogante.activo, vigDesdeSub: f.subrogante.vigenciaDesde ?? '', vigHastaSub: f.subrogante.vigenciaHasta ?? '',
    });
    setModalOpen(true);
  }

  function cerrarModal() { setModalOpen(false); setEditItem(null); setBusqDep(''); }

  function handleGuardar() {
    if (!form.idDependencia) { toast.error('Selecciona una dependencia'); return; }
    if (!form.nombreTitular.trim() || !form.cargoTitular.trim()) { toast.error('Nombre y cargo del titular son requeridos'); return; }
    saveMut.mutate({
      idDependencia: Number(form.idDependencia),
      nombreTitular: form.nombreTitular.trim(), cargoTitular: form.cargoTitular.trim(),
      activoTitular: form.activoTitular,
      vigenciaDesde: form.vigDesde || undefined, vigenciaHasta: form.vigHasta || undefined,
      nombreSubrogante: form.nombreSubr.trim() || undefined, cargoSubrogante: form.cargoSubr.trim() || undefined,
      activoSubrogante: form.activoSubr,
      vigenciaDesdeSubr: form.vigDesdeSub || undefined, vigenciaHastaSubr: form.vigHastaSub || undefined,
    });
  }

  const setF = (k: keyof typeof form, v: unknown) => setForm((p) => ({ ...p, [k]: v }));
  const inCls = 'w-full h-9 px-3 text-sm rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring';

  return (
    <>
      <Card className="border-blue-200 dark:border-blue-800/40">
        <CardHeader>
          <div className="flex items-center justify-between gap-3">
            <div className="flex items-center gap-2">
              <UserCheck className="h-4 w-4 text-blue-600" />
              <div>
                <CardTitle className="text-base">Firmantes para Memorándum</CardTitle>
                <CardDescription>
                  Configura el firmante titular y subrogante por servicio. Se usa automáticamente al generar un Memorándum Institucional.
                </CardDescription>
              </div>
            </div>
            <Button size="sm" className="gap-2 shrink-0" onClick={abrirCrear}>
              <Plus className="h-3.5 w-3.5" />Nuevo firmante
            </Button>
          </div>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex justify-end">
            <Button variant="outline" size="sm" onClick={() => refetch()} disabled={isFetching} className="gap-1.5">
              <RefreshCw className={cn('h-3.5 w-3.5', isFetching && 'animate-spin')} />Refrescar
            </Button>
          </div>

          {firmantes.length === 0 ? (
            <div className="flex flex-col items-center py-10 gap-2 text-muted-foreground">
              <ShieldCheck className="h-8 w-8 opacity-20" />
              <p className="text-sm font-medium">Sin firmantes configurados</p>
              <p className="text-xs opacity-60">Agrega un firmante por cada servicio que genera memorándums</p>
            </div>
          ) : (
            <div className="divide-y divide-border rounded-xl border overflow-hidden">
              {firmantes.map((f) => {
                const isSelected = selectedId === f.id;
                return (
                  <div key={f.id}>
                    <button
                      type="button"
                      onClick={() => setSelectedId(isSelected ? null : f.id)}
                      className={cn('w-full flex items-center justify-between px-4 py-3 text-left hover:bg-muted/30 transition-colors', isSelected && 'bg-primary/5')}
                    >
                      <div className="flex items-center gap-3 min-w-0">
                        <span className={cn('h-2 w-2 rounded-full shrink-0', f.titular.activo ? 'bg-emerald-500' : 'bg-muted-foreground/30')} />
                        <span className="font-medium text-sm truncate">{f.dependencia ?? `Dependencia #${f.idDependencia}`}</span>
                        <span className="text-xs text-muted-foreground shrink-0">{f.titular.nombre}</span>
                      </div>
                      <ChevronDown className={cn('h-4 w-4 text-muted-foreground shrink-0 transition-transform duration-200 ml-2', isSelected && 'rotate-180')} />
                    </button>

                    {isSelected && (
                      <div className="px-5 py-4 bg-muted/20 border-t border-border/50 space-y-4">
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                          {/* Titular */}
                          <div className="space-y-2">
                            <p className="text-xs font-semibold text-foreground uppercase tracking-wide">Titular</p>
                            <p className="text-sm font-medium">{f.titular.nombre}</p>
                            <p className="text-xs text-muted-foreground">{f.titular.cargo}</p>
                            <Badge variant="outline" className={cn('text-xs', f.titular.activo ? 'border-emerald-300 text-emerald-600' : 'border-border text-muted-foreground')}>
                              {f.titular.activo ? 'Activo' : 'Inactivo'}
                            </Badge>
                            <div>
                              <ImagenFirmante label="Firma y timbre" url={f.titular.firmaTimbreUrl} idFirmante={f.id} tipoImg="firma_timbre_titular" onUploaded={() => refetch()} />
                            </div>
                          </div>
                          {/* Subrogante */}
                          <div className="space-y-2">
                            <p className="text-xs font-semibold text-foreground uppercase tracking-wide">Subrogante</p>
                            {f.subrogante.nombre ? (
                              <>
                                <p className="text-sm font-medium">{f.subrogante.nombre}</p>
                                <p className="text-xs text-muted-foreground">{f.subrogante.cargo}</p>
                                <Badge variant="outline" className={cn('text-xs', f.subrogante.activo ? 'border-emerald-300 text-emerald-600' : 'border-border text-muted-foreground')}>
                                  {f.subrogante.activo ? 'Activo' : 'Inactivo'}
                                </Badge>
                                <div>
                                  <ImagenFirmante label="Firma y timbre" url={f.subrogante.firmaTimbreUrl} idFirmante={f.id} tipoImg="firma_timbre_subrogante" onUploaded={() => refetch()} />
                                </div>
                              </>
                            ) : (
                              <p className="text-xs text-muted-foreground">No configurado</p>
                            )}
                          </div>
                        </div>
                        <div className="flex justify-end pt-1 border-t border-border/40">
                          <Button size="sm" variant="outline" className="h-7 text-xs gap-1.5" onClick={() => abrirEditar(f)}>
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
        </CardContent>
      </Card>

      {/* Modal crear/editar firmante */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={(e) => { if (e.target === e.currentTarget) cerrarModal(); }}>
          <div className="bg-background rounded-xl border shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between px-5 py-4 border-b sticky top-0 bg-background z-10">
              <h3 className="font-semibold text-sm">{editItem ? 'Editar firmante' : 'Nuevo firmante'}</h3>
              <button onClick={cerrarModal} className="text-muted-foreground hover:text-foreground transition-colors"><X className="h-4 w-4" /></button>
            </div>
            <div className="px-5 py-4 space-y-5">
              {/* Dependencia */}
              <div className="space-y-1.5">
                <Label>Servicio / Dependencia <span className="text-destructive text-xs">*</span></Label>

                {editItem ? (
                  /* Editar: solo muestra, no se puede cambiar */
                  <div className="h-9 px-3 flex items-center text-sm rounded-lg border border-input bg-muted/30 text-muted-foreground">
                    {editItem.dependencia ?? `Dependencia #${editItem.idDependencia}`}
                  </div>
                ) : form.idDependencia ? (
                  /* Dependencia ya seleccionada — chip con opción de limpiar */
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
                      title="Cambiar servicio"
                    >
                      <X className="h-3.5 w-3.5" />
                    </button>
                  </div>
                ) : (
                  /* Sin selección — buscador + lista filtrada */
                  <div className="space-y-1">
                    <div className="relative">
                      <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                      <input
                        type="text"
                        placeholder="Escribe para buscar servicio..."
                        value={busqDep}
                        onChange={(e) => setBusqDep(e.target.value)}
                        autoFocus
                        className="w-full h-9 pl-9 pr-3 text-sm rounded-lg border border-input bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
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
                    <p className="text-xs text-muted-foreground">{depsFiltradas.length} servicio{depsFiltradas.length !== 1 ? 's' : ''} disponible{depsFiltradas.length !== 1 ? 's' : ''}</p>
                  </div>
                )}
              </div>

              {/* Titular */}
              <div className="space-y-3">
                <p className="text-xs font-semibold text-foreground uppercase tracking-wide border-b pb-1">Firmante Titular</p>
                <div className="grid grid-cols-2 gap-3">
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Nombre completo <span className="text-destructive text-xs">*</span></Label>
                    <input type="text" value={form.nombreTitular} onChange={(e) => setF('nombreTitular', e.target.value)} placeholder="Dr. Juan Pérez González" className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Cargo <span className="text-destructive text-xs">*</span></Label>
                    <input type="text" value={form.cargoTitular} onChange={(e) => setF('cargoTitular', e.target.value)} placeholder="Director de Servicio" className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia desde</Label>
                    <input type="date" value={form.vigDesde} onChange={(e) => setF('vigDesde', e.target.value)} className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia hasta</Label>
                    <input type="date" value={form.vigHasta} onChange={(e) => setF('vigHasta', e.target.value)} className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <label className="flex items-center gap-2 cursor-pointer">
                      <input type="checkbox" checked={form.activoTitular} onChange={(e) => setF('activoTitular', e.target.checked)} className="h-4 w-4" />
                      <span className="text-sm">Titular activo</span>
                    </label>
                  </div>
                </div>
              </div>

              {/* Subrogante */}
              <div className="space-y-3">
                <p className="text-xs font-semibold text-foreground uppercase tracking-wide border-b pb-1">Firmante Subrogante (opcional)</p>
                <div className="grid grid-cols-2 gap-3">
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Nombre completo</Label>
                    <input type="text" value={form.nombreSubr} onChange={(e) => setF('nombreSubr', e.target.value)} placeholder="Dra. María López" className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <Label className="text-sm">Cargo</Label>
                    <input type="text" value={form.cargoSubr} onChange={(e) => setF('cargoSubr', e.target.value)} placeholder="Subdirectora" className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia desde</Label>
                    <input type="date" value={form.vigDesdeSub} onChange={(e) => setF('vigDesdeSub', e.target.value)} className={inCls} />
                  </div>
                  <div className="space-y-1.5">
                    <Label className="text-sm">Vigencia hasta</Label>
                    <input type="date" value={form.vigHastaSub} onChange={(e) => setF('vigHastaSub', e.target.value)} className={inCls} />
                  </div>
                  <div className="space-y-1.5 col-span-2">
                    <label className="flex items-center gap-2 cursor-pointer">
                      <input type="checkbox" checked={form.activoSubr} onChange={(e) => setF('activoSubr', e.target.checked)} className="h-4 w-4" />
                      <span className="text-sm">Subrogante activo</span>
                    </label>
                  </div>
                </div>
              </div>

              <p className="text-xs text-muted-foreground">
                Las imágenes de firma y timbre se suben desde la tarjeta del firmante después de guardarlo.
              </p>
            </div>
            <div className="flex justify-end gap-2 px-5 py-4 border-t sticky bottom-0 bg-background">
              <Button variant="outline" size="sm" onClick={cerrarModal} disabled={saveMut.isPending}>Cancelar</Button>
              <Button size="sm" onClick={handleGuardar} disabled={saveMut.isPending} className="gap-2">
                {saveMut.isPending && <RefreshCw className="h-3.5 w-3.5 animate-spin" />}
                {editItem ? 'Guardar cambios' : 'Crear firmante'}
              </Button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

// ───────────────────────────────────────────────────────────────────────────

async function fetchConfig(): Promise<SistemaConfig> {
  const res = await apiClient.get<{ ok: boolean; data: SistemaConfig }>('/configuracion');
  return res.data.data;
}

function ImageUploadZone({
  label,
  sublabel,
  currentUrl,
  onUpload,
  isPending,
  accept = 'image/*',
  preview,
}: {
  label: string;
  sublabel: string;
  currentUrl: string | null;
  onUpload: (file: File) => void;
  isPending: boolean;
  accept?: string;
  preview?: string | null;
}) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [drag, setDrag] = useState(false);
  const displayUrl = preview ?? currentUrl;

  return (
    <div className="space-y-3">
      <div>
        <p className="text-sm font-medium text-foreground">{label}</p>
        <p className="text-xs text-muted-foreground">{sublabel}</p>
      </div>

      {displayUrl ? (
        <div className="relative rounded-xl overflow-hidden border border-border group">
          <img
            src={displayUrl.startsWith('blob:') || displayUrl.startsWith('data:') ? displayUrl : `${displayUrl}?t=${Date.now()}`}
            alt={label}
            className="w-full object-cover max-h-40"
            onError={(e) => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
          />
          <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
            <Button
              type="button"
              size="sm"
              variant="secondary"
              onClick={() => inputRef.current?.click()}
              disabled={isPending}
              className="gap-2"
            >
              <RefreshCw className="h-3.5 w-3.5" />
              Cambiar imagen
            </Button>
          </div>
        </div>
      ) : (
        <div
          onDragOver={(e) => { e.preventDefault(); setDrag(true); }}
          onDragLeave={() => setDrag(false)}
          onDrop={(e) => {
            e.preventDefault(); setDrag(false);
            const f = e.dataTransfer.files[0];
            if (f) onUpload(f);
          }}
          onClick={() => inputRef.current?.click()}
          className={cn(
            'flex flex-col items-center gap-3 p-10 rounded-xl border-2 border-dashed cursor-pointer transition-all duration-200',
            drag ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/50 hover:bg-muted/30'
          )}
        >
          <ImageIcon className={cn('h-8 w-8', drag ? 'text-primary' : 'text-muted-foreground')} />
          <div className="text-center">
            <p className="text-sm font-medium">{drag ? 'Suelta la imagen' : 'Arrastra o haz clic'}</p>
            <p className="text-xs text-muted-foreground mt-1">PNG, JPG, SVG, WEBP — recomendado &lt; 2 MB</p>
          </div>
        </div>
      )}

      <input
        ref={inputRef}
        type="file"
        accept={accept}
        className="hidden"
        onChange={(e) => {
          const f = e.target.files?.[0];
          if (f) onUpload(f);
          e.target.value = '';
        }}
      />

      {displayUrl && (
        <Button
          type="button"
          variant="outline"
          size="sm"
          className="gap-2 w-full"
          onClick={() => inputRef.current?.click()}
          disabled={isPending}
        >
          {isPending ? <RefreshCw className="h-3.5 w-3.5 animate-spin" /> : <Upload className="h-3.5 w-3.5" />}
          {isPending ? 'Subiendo...' : 'Subir nueva imagen'}
        </Button>
      )}
    </div>
  );
}

export function ConfiguracionPage() {
  const qc = useQueryClient();
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [bgPreview, setBgPreview] = useState<string | null>(null);
  const [nombreSistema,     setNombreSistema]     = useState('');
  const [nombreInstitucion, setNombreInstitucion] = useState('');

  // Reglas de carga de archivos
  const [uploadRules, setUploadRules] = useState<UploadRulesConfig>(UPLOAD_RULES_DEFAULT);

  // Textos del login
  const [loginTextos, setLoginTextos] = useState({
    loginNombreSistema:   '',
    loginSubtitulo:       '',
    loginTituloPrincipal: '',
    loginDescripcion:     '',
    loginCard1:           '',
    loginCard2:           '',
    loginCard3:           '',
    loginCard4:           '',
    loginFooter:          '',
  });

  const { data: config, isLoading } = useQuery({
    queryKey: ['configuracion'],
    queryFn: fetchConfig,
  });

  useEffect(() => {
    if (config) {
      setNombreSistema(config.nombreSistema);
      setNombreInstitucion(config.nombreInstitucion);
      setLoginTextos({
        loginNombreSistema:   config.loginNombreSistema   ?? '',
        loginSubtitulo:       config.loginSubtitulo       ?? '',
        loginTituloPrincipal: config.loginTituloPrincipal ?? '',
        loginDescripcion:     config.loginDescripcion     ?? '',
        loginCard1:           config.loginCard1           ?? '',
        loginCard2:           config.loginCard2           ?? '',
        loginCard3:           config.loginCard3           ?? '',
        loginCard4:           config.loginCard4           ?? '',
        loginFooter:          config.loginFooter          ?? '',
      });
      if (config.uploadRules) {
        setUploadRules({
          extensionesPermitidas: config.uploadRules.extensionesPermitidas ?? UPLOAD_RULES_DEFAULT.extensionesPermitidas,
          maxFileMB:  config.uploadRules.maxFileMB  ?? UPLOAD_RULES_DEFAULT.maxFileMB,
          maxTotalMB: config.uploadRules.maxTotalMB ?? UPLOAD_RULES_DEFAULT.maxTotalMB,
        });
      }
    }
  }, [config]);

  const uploadLogo = useMutation({
    mutationFn: async (file: File) => {
      const form = new FormData();
      form.append('archivo', file);
      const res = await apiClient.post('/configuracion/logo', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return res.data;
    },
    onSuccess: () => {
      toast.success('Logo actualizado');
      setLogoPreview(null);
      qc.invalidateQueries({ queryKey: ['configuracion'] });
    },
    onError: () => toast.error('Error al subir el logo'),
  });

  const uploadBackground = useMutation({
    mutationFn: async (file: File) => {
      const form = new FormData();
      form.append('archivo', file);
      const res = await apiClient.post('/configuracion/background', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      return res.data;
    },
    onSuccess: () => {
      toast.success('Fondo de pantalla de login actualizado');
      setBgPreview(null);
      qc.invalidateQueries({ queryKey: ['configuracion'] });
    },
    onError: () => toast.error('Error al subir la imagen de fondo'),
  });

  const saveConfig = useMutation({
    mutationFn: async () => {
      const res = await apiClient.patch('/configuracion', { nombreSistema, nombreInstitucion });
      return res.data;
    },
    onSuccess: () => {
      toast.success('Configuración guardada');
      qc.invalidateQueries({ queryKey: ['configuracion'] });
    },
    onError: () => toast.error('Error al guardar la configuración'),
  });

  const saveLoginTextos = useMutation({
    mutationFn: async () => {
      const res = await apiClient.patch('/configuracion', loginTextos);
      return res.data;
    },
    onSuccess: () => {
      toast.success('Textos del login guardados');
      qc.invalidateQueries({ queryKey: ['configuracion'] });
    },
    onError: () => toast.error('Error al guardar los textos del login'),
  });

  const saveUploadRules = useMutation({
    mutationFn: async () => {
      const res = await apiClient.patch('/configuracion/upload-rules', {
        extensionesPermitidas: uploadRules.extensionesPermitidas,
        maxFileMB:  uploadRules.maxFileMB,
        maxTotalMB: uploadRules.maxTotalMB,
      });
      return res.data;
    },
    onSuccess: () => {
      toast.success('Reglas de carga actualizadas');
      qc.invalidateQueries({ queryKey: ['configuracion'] });
      qc.invalidateQueries({ queryKey: ['configuracion-upload-rules'] });
    },
    onError: (e: unknown) => {
      const msg = (e as { response?: { data?: { error?: string } } })?.response?.data?.error ?? 'Error al guardar las reglas de carga';
      toast.error(msg);
    },
  });

  const toggleExtension = (ext: string) => {
    setUploadRules((prev) => {
      const isSelected = prev.extensionesPermitidas.includes(ext);
      if (isSelected && prev.extensionesPermitidas.length <= 1) return prev; // al menos 1 debe quedar
      return {
        ...prev,
        extensionesPermitidas: isSelected
          ? prev.extensionesPermitidas.filter((e) => e !== ext)
          : [...prev.extensionesPermitidas, ext],
      };
    });
  };

  const uploadRulesValid =
    uploadRules.maxFileMB >= 1 &&
    uploadRules.maxFileMB <= 100 &&
    uploadRules.maxTotalMB >= uploadRules.maxFileMB;

  const handleLogoFile = (file: File) => {
    setLogoPreview(URL.createObjectURL(file));
    uploadLogo.mutate(file);
  };

  const handleBgFile = (file: File) => {
    setBgPreview(URL.createObjectURL(file));
    uploadBackground.mutate(file);
  };

  return (
    <div className="space-y-6 max-w-4xl mx-auto animate-fade-in">
      {/* Header */}
      <div className="flex items-center gap-3">
        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
          <Settings className="h-5 w-5 text-primary" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-foreground">Configuración del Sistema</h1>
          <p className="text-sm text-muted-foreground">Personaliza la apariencia e identidad del sistema</p>
        </div>
        {config && (
          <Badge variant="outline" className="ml-auto gap-1.5">
            <CheckCircle2 className="h-3 w-3 text-emerald-500" />
            v{config.version}
          </Badge>
        )}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Identidad institucional */}
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2">
              <Building2 className="h-4 w-4 text-primary" />
              Identidad Institucional
            </CardTitle>
            <CardDescription>Nombre del sistema e institución que aparecen en la interfaz</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="nombreSistema">Nombre del sistema</Label>
                <Input
                  id="nombreSistema"
                  value={isLoading ? '' : nombreSistema}
                  onChange={(e) => setNombreSistema(e.target.value)}
                  placeholder="SISDOC"
                  disabled={isLoading}
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="nombreInstitucion">Nombre de la institución</Label>
                <Input
                  id="nombreInstitucion"
                  value={isLoading ? '' : nombreInstitucion}
                  onChange={(e) => setNombreInstitucion(e.target.value)}
                  placeholder="HUAP"
                  disabled={isLoading}
                />
              </div>
            </div>
            <div className="flex justify-end">
              <Button
                onClick={() => saveConfig.mutate()}
                disabled={isLoading || saveConfig.isPending}
                className="gap-2"
                size="sm"
              >
                {saveConfig.isPending
                  ? <RefreshCw className="h-3.5 w-3.5 animate-spin" />
                  : <Save className="h-3.5 w-3.5" />}
                Guardar cambios
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Logo del sistema */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2">
              <Palette className="h-4 w-4 text-primary" />
              Logo del Sistema
            </CardTitle>
            <CardDescription>Se muestra en el sidebar y encabezados</CardDescription>
          </CardHeader>
          <CardContent>
            <ImageUploadZone
              label="Logo institucional"
              sublabel="Fondo transparente — PNG o SVG recomendado"
              currentUrl={uploadUrl(config?.logoUrl)}
              preview={logoPreview}
              onUpload={handleLogoFile}
              isPending={uploadLogo.isPending}
              accept="image/png,image/svg+xml,image/jpeg,image/webp"
            />
          </CardContent>
        </Card>

        {/* Fondo login */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2">
              <Monitor className="h-4 w-4 text-primary" />
              Fondo de Pantalla de Login
            </CardTitle>
            <CardDescription>Imagen que aparece en el panel izquierdo del login</CardDescription>
          </CardHeader>
          <CardContent>
            <ImageUploadZone
              label="Imagen de fondo"
              sublabel="Mínimo 1280×800 px — JPG o PNG recomendado"
              currentUrl={uploadUrl(config?.backgroundUrl)}
              preview={bgPreview}
              onUpload={handleBgFile}
              isPending={uploadBackground.isPending}
              accept="image/jpeg,image/png,image/webp"
            />
          </CardContent>
        </Card>
      </div>

      {/* Textos del Login */}
      <Card className="lg:col-span-2">
        <CardHeader>
          <CardTitle className="text-base flex items-center gap-2">
            <Type className="h-4 w-4 text-primary" />
            Textos del Login
          </CardTitle>
          <CardDescription>Personaliza los textos que aparecen en el panel izquierdo de la pantalla de inicio de sesión</CardDescription>
        </CardHeader>
        <CardContent className="space-y-5">

          {/* Nombre sistema + subtítulo */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div className="space-y-1.5">
              <Label>Nombre del sistema <span className="text-muted-foreground font-normal">(encabezado)</span></Label>
              <Input
                value={isLoading ? '' : loginTextos.loginNombreSistema}
                onChange={(e) => setLoginTextos((p) => ({ ...p, loginNombreSistema: e.target.value }))}
                placeholder="SISDOC"
                disabled={isLoading}
              />
            </div>
            <div className="space-y-1.5">
              <Label>Subtítulo del sistema</Label>
              <Input
                value={isLoading ? '' : loginTextos.loginSubtitulo}
                onChange={(e) => setLoginTextos((p) => ({ ...p, loginSubtitulo: e.target.value }))}
                placeholder="Sistema de Gestión Documental"
                disabled={isLoading}
              />
            </div>
          </div>

          {/* Título principal + descripción */}
          <div className="space-y-1.5">
            <Label>Título principal</Label>
            <Input
              value={isLoading ? '' : loginTextos.loginTituloPrincipal}
              onChange={(e) => setLoginTextos((p) => ({ ...p, loginTituloPrincipal: e.target.value }))}
              placeholder="Gestión documental moderna"
              disabled={isLoading}
            />
          </div>
          <div className="space-y-1.5">
            <Label>Descripción principal</Label>
            <textarea
              value={isLoading ? '' : loginTextos.loginDescripcion}
              onChange={(e) => setLoginTextos((p) => ({ ...p, loginDescripcion: e.target.value }))}
              placeholder="Plataforma enterprise para la gestión, seguimiento y trazabilidad…"
              rows={3}
              disabled={isLoading}
              className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground resize-none focus:outline-none focus:ring-2 focus:ring-ring disabled:opacity-50"
            />
          </div>

          {/* Tarjetas */}
          <div>
            <Label className="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-3 block">
              Tarjetas informativas (4 íconos)
            </Label>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              {(['loginCard1', 'loginCard2', 'loginCard3', 'loginCard4'] as const).map((key, i) => (
                <div key={key} className="space-y-1.5">
                  <Label className="text-xs text-muted-foreground">Tarjeta {i + 1}</Label>
                  <Input
                    value={isLoading ? '' : loginTextos[key]}
                    onChange={(e) => setLoginTextos((p) => ({ ...p, [key]: e.target.value }))}
                    placeholder={['Gestión documental', 'Flujo de derivaciones', 'Trazabilidad completa', 'Historial documental'][i]}
                    disabled={isLoading}
                  />
                </div>
              ))}
            </div>
          </div>

          {/* Footer */}
          <div className="space-y-1.5">
            <Label>Texto de pie de página</Label>
            <Input
              value={isLoading ? '' : loginTextos.loginFooter}
              onChange={(e) => setLoginTextos((p) => ({ ...p, loginFooter: e.target.value }))}
              placeholder="© 2026 SISDOC v2.0 — Sistema institucional de gestión documental"
              disabled={isLoading}
            />
          </div>

          <div className="flex justify-end pt-1">
            <Button
              onClick={() => saveLoginTextos.mutate()}
              disabled={isLoading || saveLoginTextos.isPending}
              className="gap-2"
              size="sm"
            >
              {saveLoginTextos.isPending
                ? <RefreshCw className="h-3.5 w-3.5 animate-spin" />
                : <Save className="h-3.5 w-3.5" />}
              Guardar textos del login
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Reglas de carga de archivos */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base flex items-center gap-2">
            <Paperclip className="h-4 w-4 text-primary" />
            Reglas de carga de archivos
          </CardTitle>
          <CardDescription>
            Define qué tipos de archivos se pueden subir y los límites de tamaño. Estas reglas se aplican
            en todos los flujos de carga del sistema (creación de documento, adjuntar archivo, etc.).
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          {/* Extensiones permitidas */}
          <div className="space-y-3">
            <p className="text-sm font-medium text-foreground">Extensiones permitidas</p>
            <p className="text-xs text-muted-foreground">
              Marca las extensiones que los usuarios podrán subir. Al menos una debe estar activa.
            </p>
            <div className="flex flex-wrap gap-2">
              {TODAS_LAS_EXTENSIONES.map(({ ext, label }) => {
                const activo = uploadRules.extensionesPermitidas.includes(ext);
                return (
                  <button
                    key={ext}
                    type="button"
                    onClick={() => toggleExtension(ext)}
                    disabled={isLoading}
                    className={cn(
                      'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-medium transition-all',
                      activo
                        ? 'bg-primary/10 border-primary/40 text-primary'
                        : 'bg-muted/30 border-border text-muted-foreground hover:border-primary/30'
                    )}
                    aria-pressed={activo}
                  >
                    <span className={cn(
                      'h-3 w-3 rounded-sm border flex items-center justify-center shrink-0',
                      activo ? 'bg-primary border-primary' : 'border-muted-foreground/40'
                    )}>
                      {activo && <X className="h-2 w-2 text-primary-foreground" strokeWidth={3} />}
                    </span>
                    {label}
                  </button>
                );
              })}
            </div>
            {uploadRules.extensionesPermitidas.length === 0 && (
              <p className="text-xs text-destructive">Debes permitir al menos una extensión.</p>
            )}
          </div>

          <Separator />

          {/* Límites de tamaño */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="maxFileMB">Peso máximo por archivo (MB)</Label>
              <div className="flex items-center gap-2">
                <Input
                  id="maxFileMB"
                  type="number"
                  min={1}
                  max={100}
                  value={uploadRules.maxFileMB}
                  onChange={(e) => setUploadRules((p) => ({ ...p, maxFileMB: Number(e.target.value) }))}
                  className="w-28"
                  disabled={isLoading}
                />
                <span className="text-sm text-muted-foreground">MB por archivo</span>
              </div>
              {(uploadRules.maxFileMB < 1 || uploadRules.maxFileMB > 100) && (
                <p className="text-xs text-destructive">Debe estar entre 1 y 100 MB.</p>
              )}
            </div>
            <div className="space-y-2">
              <Label htmlFor="maxTotalMB">Peso máximo total por carga (MB)</Label>
              <div className="flex items-center gap-2">
                <Input
                  id="maxTotalMB"
                  type="number"
                  min={uploadRules.maxFileMB}
                  value={uploadRules.maxTotalMB}
                  onChange={(e) => setUploadRules((p) => ({ ...p, maxTotalMB: Number(e.target.value) }))}
                  className="w-28"
                  disabled={isLoading}
                />
                <span className="text-sm text-muted-foreground">MB suma total</span>
              </div>
              {uploadRules.maxTotalMB < uploadRules.maxFileMB && (
                <p className="text-xs text-destructive">
                  Debe ser mayor o igual al peso por archivo ({uploadRules.maxFileMB} MB).
                </p>
              )}
            </div>
          </div>

          <div className="flex items-center justify-between pt-1">
            <p className="text-xs text-muted-foreground">
              {uploadRules.extensionesPermitidas.length} extensión{uploadRules.extensionesPermitidas.length !== 1 ? 'es' : ''} activa{uploadRules.extensionesPermitidas.length !== 1 ? 's' : ''}
              {' · '}máx. {uploadRules.maxFileMB} MB/archivo · cuota total {uploadRules.maxTotalMB} MB
            </p>
            <Button
              onClick={() => saveUploadRules.mutate()}
              disabled={isLoading || saveUploadRules.isPending || !uploadRulesValid}
              className="gap-2"
              size="sm"
            >
              {saveUploadRules.isPending
                ? <RefreshCw className="h-3.5 w-3.5 animate-spin" />
                : <Save className="h-3.5 w-3.5" />}
              Guardar reglas
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* ── Mantenedores administrativos ─────────────────────────── */}
      <Separator />
      <div>
        <h2 className="text-base font-semibold text-foreground">Mantenedores de catálogos</h2>
        <p className="text-sm text-muted-foreground mt-0.5">Gestión de los catálogos base del sistema. Solo administradores y oficina de partes.</p>
      </div>

      <MantenedorTiposDocumento />
      <MantenedorDependencias />

      {/* Firmantes → módulo dedicado */}
      <Card className="border-blue-200 dark:border-blue-800/40">
        <CardHeader>
          <div className="flex items-center gap-2">
            <UserCheck className="h-4 w-4 text-blue-600" />
            <div>
              <CardTitle className="text-base">Jefaturas y Subrogancias</CardTitle>
              <CardDescription>
                Configura el firmante titular y subrogante por servicio para el Memorándum Institucional
              </CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-between gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50/50">
            <p className="text-sm text-blue-800">
              La gestión de jefaturas se trasladó a su propio módulo en{' '}
              <strong>Administración → Jefaturas</strong>.
            </p>
            <Link to="/admin/jefaturas">
              <Button size="sm" variant="outline" className="gap-1.5 shrink-0 border-blue-300 text-blue-700 hover:bg-blue-100">
                Ir a Jefaturas
                <ArrowRight className="h-3.5 w-3.5" />
              </Button>
            </Link>
          </div>
        </CardContent>
      </Card>

      {/* Info del sistema — siempre al final */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base text-muted-foreground">Información del sistema</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
            {[
              { label: 'Versión API',    value: config?.version ?? '—' },
              { label: 'Backend',        value: 'Node.js + Express' },
              { label: 'Frontend',       value: 'React 18 + Vite 6' },
              { label: 'Base de datos',  value: 'SQL Server 2022' },
            ].map(({ label, value }) => (
              <div key={label} className="rounded-xl border bg-muted/20 px-4 py-3 card-executive">
                <p className="text-xs text-muted-foreground font-medium uppercase tracking-wide">{label}</p>
                <p className="text-foreground font-semibold mt-0.5">{value}</p>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
