import * as repo from './documento.repository';
import { getPool, sql } from '../../config/database';
import { CrearDocumentoDto, DerivarDocumentoDto, FiltrosDocumentoDto } from './documento.schema';
import { buildPaginationMeta } from '../../shared/utils/response';

export async function listarDocumentos(filtros: FiltrosDocumentoDto) {
  const rows = await repo.findMany(filtros);
  const total = rows[0]?.total ?? 0;
  const meta = buildPaginationMeta(total, filtros.pagina, filtros.porPagina);
  const data = rows.map(mapDocumento);
  return { data, meta };
}

export async function obtenerDocumento(idDocumento: number) {
  const row = await repo.findById(idDocumento);
  if (!row) throw { statusCode: 404, message: 'Documento no encontrado' };
  return mapDocumento(row);
}

export async function obtenerHistorial(idDocumento: number) {
  return repo.findHistorial(idDocumento);
}

export async function crearDocumento(dto: CrearDocumentoDto, idUsuario: number) {
  const idDocumento = await repo.insert({
    idTipoDocumento: dto.idTipoDocumento,
    materia: dto.materia,
    idEstadoDocumento: dto.idEstadoDocumento ?? 1,
    idUsuario,
    fechaDocumento: dto.fechaDocumento ? new Date(dto.fechaDocumento) : undefined,
    observaciones: dto.observaciones,
  });

  return obtenerDocumento(idDocumento);
}

export async function derivarDocumento(
  idDocumento: number,
  dto: DerivarDocumentoDto,
  idUsuario: number,
) {
  const doc = await repo.findById(idDocumento);
  if (!doc) throw { statusCode: 404, message: 'Documento no encontrado' };

  const pool = await getPool();
  await pool
    .request()
    .input('idDocumento', sql.Int, idDocumento)
    .input('idUsuario', sql.Int, idUsuario)
    .input('idDestino', sql.Int, dto.idDependenciaDestino)
    .input('observacion', sql.VarChar(500), dto.observacion ?? null)
    .query(`
      INSERT INTO tramite (id_documento, id_usuario, id_destino,
                           id_estado_tramite, fecha_sistema, observaciones)
      VALUES (@idDocumento, @idUsuario, @idDestino, 1, GETDATE(), @observacion)
    `);

  await repo.updateEstado(idDocumento, 3);
  return obtenerDocumento(idDocumento);
}

function mapDocumento(row: repo.DocumentoRow) {
  return {
    idDocumento: row.id_documento,
    numDocumento: row.num_oficial ?? row.num_interno,
    materia: row.materia,
    asunto: row.materia,
    tipoDocumento: {
      id: row.id_tipo_documento,
      descripcion: row.desc_tipo_documento,
    },
    estadoDocumento: {
      id: row.id_estado_documento,
      descripcion: row.desc_estado_documento,
    },
    ingresadoPor: {
      id: row.id_usuario,
      usuario: row.usuario,
      nombre: [row.nombres, row.apellidos].filter(Boolean).join(' '),
    },
    fechaDocumento: row.fecha_documento,
    fechaIngreso: row.fecha_sistema,
    fechaCierre: null,
    observacion: null,
  };
}
