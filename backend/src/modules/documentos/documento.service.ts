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
    numDocumento: dto.numDocumento,
    asunto: dto.asunto,
    idEstadoDocumento: 1,
    idProcedencia: dto.idProcedencia,
    idProcedenciaExterna: dto.idProcedenciaExterna,
    idDestino: dto.idDestino,
    idUsuario,
    idPrioridad: dto.idPrioridad,
    idExpediente: dto.idExpediente,
    fechaDocumento: dto.fechaDocumento ? new Date(dto.fechaDocumento) : undefined,
    observacion: dto.observacion,
  });

  if (dto.descriptores?.length) {
    await insertDescriptores(idDocumento, dto.descriptores);
  }

  await repo.insertHistorial({ idDocumento, idUsuario, accion: 'INGRESADO' });
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
    .input('idUsuarioOrigen', sql.Int, idUsuario)
    .input('idDependenciaDestino', sql.Int, dto.idDependenciaDestino)
    .input('idFuncionarioDestino', sql.Int, dto.idFuncionarioDestino ?? null)
    .input('observacion', sql.VarChar(500), dto.observacion ?? null)
    .query(`
      INSERT INTO tramite (id_documento, id_usuario_origen, id_dependencia_destino,
                           id_funcionario_destino, id_estado_tramite, fecha_derivacion, observacion)
      VALUES (@idDocumento, @idUsuarioOrigen, @idDependenciaDestino,
              @idFuncionarioDestino, 1, GETDATE(), @observacion)
    `);

  await repo.updateEstado(idDocumento, 3);
  await repo.insertHistorial({
    idDocumento,
    idUsuario,
    accion: 'DERIVADO',
    observacion: dto.observacion,
  });

  return obtenerDocumento(idDocumento);
}

// ── Helpers ──────────────────────────────────────────────────

async function insertDescriptores(idDocumento: number, descriptores: number[]): Promise<void> {
  const pool = await getPool();
  for (const idDescriptor of descriptores) {
    await pool
      .request()
      .input('idDocumento', sql.Int, idDocumento)
      .input('idDescriptor', sql.Int, idDescriptor)
      .query(`
        IF NOT EXISTS (SELECT 1 FROM descriptor_documento WHERE id_documento=@idDocumento AND id_descriptor=@idDescriptor)
          INSERT INTO descriptor_documento (id_documento, id_descriptor) VALUES (@idDocumento, @idDescriptor)
      `);
  }
}

function mapDocumento(row: repo.DocumentoRow) {
  return {
    idDocumento: row.id_documento,
    numDocumento: row.num_documento,
    asunto: row.asunto,
    tipoDocumento: {
      id: row.id_tipo_documento,
      descripcion: row.desc_tipo_documento,
    },
    estadoDocumento: {
      id: row.id_estado_documento,
      descripcion: row.desc_estado_documento,
    },
    prioridad: {
      id: row.id_prioridad,
      descripcion: row.desc_prioridad,
      color: row.color_prioridad,
    },
    procedencia: {
      id: row.id_procedencia,
      descripcion: row.desc_procedencia,
    },
    destino: {
      id: row.id_destino,
      descripcion: row.desc_destino,
    },
    ingresadoPor: {
      id: row.id_usuario,
      usuario: row.usuario,
      nombre: [row.nombres_fun, row.ap_pat_fun].filter(Boolean).join(' '),
    },
    fechaDocumento: row.fecha_documento,
    fechaIngreso: row.fecha_ingreso,
    fechaCierre: row.fecha_cierre,
    observacion: row.observacion,
  };
}
