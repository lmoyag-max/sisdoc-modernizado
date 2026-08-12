import { useQuery } from '@tanstack/react-query';
import { FileEdit, Send, Inbox, CheckCircle2, type LucideIcon } from 'lucide-react';
import { catalogosApi } from '@/lib/api/catalogos.api';

// ── Fuente única de verdad: id_estado_documento → presentación visual ──
//
// La semántica de cada id está confirmada contra las transiciones reales del backend
// (backend/src/modules/documentos/documento.repository.ts: recepcionarDocumentoAtomic/
// recepcionarDestinoAtomic asignan id=3; terminarDocumentoAtomic/terminarDestinoAtomic
// asignan id=4). No existe un id=5 para documento.id_estado_documento en ningún punto
// del backend — solo 4 estados reales, sin una etapa "En Proceso" separada.
//
// El TEXTO (label) mostrado al usuario nunca se hardcodea aquí: siempre se toma del
// catálogo real `estado_documento` vía GET /catalogos/estados (useEstadosDocumento).
// Este archivo solo define icono/color/orden — antes esa relación estaba duplicada e
// inconsistente en 3 lugares (DashboardPage, DocumentosPage, y el pie chart implícito).

export interface EstadoDocumentoMeta {
  id: number;
  icon: LucideIcon;
  accent: 'indigo' | 'sky' | 'amber' | 'emerald' | 'slate';
  badgeVariant: 'info' | 'warning' | 'success' | 'secondary';
}

const ESTADO_DOCUMENTO_META: Record<number, EstadoDocumentoMeta> = {
  1: { id: 1, icon: FileEdit,     accent: 'indigo',  badgeVariant: 'info' },
  2: { id: 2, icon: Send,         accent: 'sky',      badgeVariant: 'warning' },
  3: { id: 3, icon: Inbox,        accent: 'amber',    badgeVariant: 'secondary' },
  4: { id: 4, icon: CheckCircle2, accent: 'emerald',  badgeVariant: 'success' },
};

const ESTADO_DOCUMENTO_FALLBACK: EstadoDocumentoMeta = {
  id: 0, icon: FileEdit, accent: 'slate', badgeVariant: 'secondary',
};

// Orden real del flujo de negocio — usado por el pipeline "Flujo Documental".
export const ORDEN_PIPELINE_ESTADOS = [1, 2, 3, 4] as const;

// Último recurso mientras el catálogo carga o si la petición falla — siempre se
// reemplaza por el texto real de la BD en cuanto GET /catalogos/estados responde.
const LABEL_FALLBACK: Record<number, string> = {
  1: 'Registrado',
  2: 'Despachado',
  3: 'Recepcionado',
  4: 'Terminado',
};

export function getEstadoMeta(id: number | null | undefined): EstadoDocumentoMeta {
  if (id == null) return ESTADO_DOCUMENTO_FALLBACK;
  return ESTADO_DOCUMENTO_META[id] ?? ESTADO_DOCUMENTO_FALLBACK;
}

export interface EstadoDocumentoDisplay extends EstadoDocumentoMeta {
  label: string;
}

export function useEstadosDocumento() {
  const { data: catalogo, isLoading } = useQuery({
    queryKey: ['catalogos', 'estados-documento'],
    queryFn: catalogosApi.estados,
    staleTime: 10 * 60 * 1000,
  });

  const porId = new Map((catalogo ?? []).map((c) => [c.id, c.descripcion]));

  const getLabel = (id: number | null | undefined): string => {
    if (id == null) return 'Sin estado';
    return porId.get(id) ?? LABEL_FALLBACK[id] ?? `Estado ${id}`;
  };

  const estadosPipeline: EstadoDocumentoDisplay[] = ORDEN_PIPELINE_ESTADOS.map((id) => ({
    ...getEstadoMeta(id),
    label: getLabel(id),
  }));

  return { estadosPipeline, getLabel, loading: isLoading };
}
