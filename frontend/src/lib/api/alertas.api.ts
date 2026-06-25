import { apiClient } from './client';

export interface AlertaConfig {
  id:        number;
  activo:    boolean;
  horarios:  string[];
  updatedAt: string;
}

export interface DocumentoPendiente {
  idDocumentoDestino: number | null;
  idDestino:          number;
  nombreServicio:     string;
  idDocumento:        number;
  numInterno:         number | null;
  materia:            string | null;
  tipoDocumento:      string | null;
  estadoDocumento:    string | null;
  idTipoCompromiso:   number;
  compromiso:         string;
  diasCompromiso:     number;
  diasTranscurridos:  number;
  fechaCreacion:      string | null;
  origenNombre:       string | null;
}

export interface ResumenDestinatarios {
  idDependencia:  number;
  nombreServicio: string;
  totalUsuarios:  number;
  usuarios:       { idUsuario: number; nombreCompleto: string; email: string }[];
}

export interface AlertaLog {
  id:                  number;
  idDependencia:       number;
  nombreServicio:      string;
  tipoEnvio:           string;
  idUsuarioDisparo:    number | null;
  fechaEnvio:          string;
  destinatarios:       string[] | null;
  documentosIncluidos: number;
  urgentesIncluidos:   number;
  estado:              string;
  mensajeError:        string | null;
}

export interface PaginacionMeta {
  total:        number;
  pagina:       number;
  porPagina:    number;
  totalPaginas: number;
}

export interface ResultadoEnvio {
  enviados:  number;
  errores:   number;
  sinDocs:   number;
  sinEmail:  number;
  detalles:  { servicio: string; estado: string; mensaje?: string }[];
}

export interface ResultadoServicio {
  estado:              string;
  mensaje:             string;
  documentosIncluidos: number;
  usuariosEncontrados: number;
  detalle?:            { nombre: string; email: string }[];
}

const BASE = '/alertas';

const unwrap = async <T>(promise: Promise<{ data: unknown }>): Promise<T> => {
  const { data } = await promise;
  return (data as { ok: boolean; data: T }).data;
};

export const alertasApi = {
  getConfig: () =>
    unwrap<AlertaConfig>(apiClient.get(`${BASE}/configuracion`)),

  updateConfig: (body: { activo: boolean; horarios: string[] }) =>
    unwrap<AlertaConfig>(apiClient.put(`${BASE}/configuracion`, body)),

  getPendientes: (idDependencia?: number) =>
    unwrap<DocumentoPendiente[]>(
      apiClient.get(`${BASE}/pendientes`, { params: idDependencia ? { idDependencia } : {} })
    ),

  getDestinatarios: (idDependencia?: number) =>
    unwrap<ResumenDestinatarios[]>(
      apiClient.get(`${BASE}/destinatarios`, { params: idDependencia ? { idDependencia } : {} })
    ),

  getLogs: (pagina = 1, porPagina = 20) =>
    apiClient
      .get<{ ok: boolean; data: AlertaLog[]; meta: PaginacionMeta }>(`${BASE}/logs`, { params: { pagina, porPagina } })
      .then((r) => ({ data: r.data.data, meta: r.data.meta })),

  enviarManual: (idDependencia: number) =>
    unwrap<ResultadoServicio>(apiClient.post(`${BASE}/enviar-manual`, { idDependencia })),

  enviarTodos: () =>
    unwrap<ResultadoEnvio>(apiClient.post(`${BASE}/enviar-todos`)),

  probarServicio: (idDependencia: number) =>
    unwrap<ResultadoServicio>(apiClient.post(`${BASE}/probar-servicio/${idDependencia}`)),
};
