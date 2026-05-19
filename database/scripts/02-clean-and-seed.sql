-- ============================================================
-- SCRIPT 02: Limpieza y datos de prueba SISDOC
-- Elimina documentos masivos, deja solo 15 docs de prueba
-- NO elimina: usuarios, dependencias, catálogos, configuración
-- ============================================================

USE SISDOC;
SET NOCOUNT ON;

PRINT 'Iniciando limpieza de datos masivos...';

-- 1. Eliminar registros dependientes (FK)
DELETE FROM archivo_digital;
DELETE FROM tramite;
DELETE FROM descriptor_documento;
DELETE FROM documento;

PRINT 'Documentos y registros relacionados eliminados.';

-- 2. Obtener IDs reales para datos de prueba
DECLARE @idUsuario1 INT, @idUsuario2 INT, @idDep1 INT, @idDep2 INT, @idDep3 INT;
DECLARE @idTipo1 INT, @idTipo2 INT, @idTipo3 INT;
DECLARE @idEstReg INT, @idEstDes INT, @idEstRec INT;

SELECT TOP 1 @idUsuario1 = id_usuario FROM usuario ORDER BY id_usuario;
SELECT TOP 1 @idUsuario2 = id_usuario FROM usuario WHERE id_usuario > @idUsuario1 ORDER BY id_usuario;
SELECT TOP 1 @idDep1 = id_dependencia FROM dependencia ORDER BY id_dependencia;
SELECT TOP 1 @idDep2 = id_dependencia FROM dependencia WHERE id_dependencia > @idDep1 ORDER BY id_dependencia;
SELECT TOP 1 @idDep3 = id_dependencia FROM dependencia WHERE id_dependencia > @idDep2 ORDER BY id_dependencia;
SELECT TOP 1 @idTipo1 = id_tipo_documento FROM tipo_documento ORDER BY id_tipo_documento;
SELECT TOP 1 @idTipo2 = id_tipo_documento FROM tipo_documento WHERE id_tipo_documento > @idTipo1 ORDER BY id_tipo_documento;
SELECT TOP 1 @idTipo3 = id_tipo_documento FROM tipo_documento WHERE id_tipo_documento > @idTipo2 ORDER BY id_tipo_documento;
SELECT TOP 1 @idEstReg = id_estado_documento FROM estado_documento WHERE desc_estado_documento LIKE '%Registr%';
SELECT TOP 1 @idEstDes = id_estado_documento FROM estado_documento WHERE desc_estado_documento LIKE '%Despach%';
SELECT TOP 1 @idEstRec = id_estado_documento FROM estado_documento WHERE desc_estado_documento LIKE '%Recepci%';

-- 3. Insertar 15 documentos de prueba realistas
INSERT INTO documento (id_tipo_documento, id_estado_documento, id_usuario, num_interno, num_oficial, materia, fecha_documento, fecha_sistema)
VALUES
(@idTipo1, @idEstReg, @idUsuario1, 1, 'OF-2026-001', 'Solicitud de equipamiento médico para Unidad de Emergencias', '2026-01-10', '2026-01-10 09:00:00'),
(@idTipo2, @idEstDes, @idUsuario1, 2, 'RES-2026-002', 'Resolución de contratación directa de insumos críticos', '2026-01-15', '2026-01-15 10:30:00'),
(@idTipo1, @idEstReg, @idUsuario2, 3, 'OF-2026-003', 'Informe mensual de actividad asistencial — enero 2026', '2026-02-01', '2026-02-01 08:00:00'),
(@idTipo3, @idEstRec, @idUsuario1, 4, 'MEM-2026-004', 'Memorándum sobre actualización de protocolos COVID', '2026-02-05', '2026-02-05 11:00:00'),
(@idTipo2, @idEstReg, @idUsuario2, 5, 'RES-2026-005', 'Resolución de designación de jefe subrogante Dpto. Cirugía', '2026-02-10', '2026-02-10 09:15:00'),
(@idTipo1, @idEstDes, @idUsuario1, 6, 'OF-2026-006', 'Oficio a MINSAL sobre requerimiento presupuestario 2026', '2026-02-20', '2026-02-20 14:00:00'),
(@idTipo3, @idEstReg, @idUsuario2, 7, 'MEM-2026-007', 'Memoria anual de gestión hospitalaria 2025', '2026-03-01', '2026-03-01 10:00:00'),
(@idTipo1, @idEstRec, @idUsuario1, 8, 'OF-2026-008', 'Solicitud de capacitación en bioseguridad para personal nuevo', '2026-03-10', '2026-03-10 09:30:00'),
(@idTipo2, @idEstReg, @idUsuario2, 9, 'RES-2026-009', 'Resolución aprobación de licencia médica prolongada', '2026-03-15', '2026-03-15 11:00:00'),
(@idTipo3, @idEstDes, @idUsuario1, 10, 'MEM-2026-010', 'Memorándum de cierre semestral — sistema informático', '2026-04-01', '2026-04-01 08:30:00'),
(@idTipo1, @idEstReg, @idUsuario2, 11, 'OF-2026-011', 'Oficio convenio interinstitucional con Universidad de Chile', '2026-04-10', '2026-04-10 13:00:00'),
(@idTipo2, @idEstRec, @idUsuario1, 12, 'RES-2026-012', 'Resolución de licitación pública — mantenimiento equipos', '2026-04-20', '2026-04-20 10:00:00'),
(@idTipo3, @idEstReg, @idUsuario2, 13, 'MEM-2026-013', 'Informe de calidad y seguridad del paciente Q1 2026', '2026-05-01', '2026-05-01 09:00:00'),
(@idTipo1, @idEstDes, @idUsuario1, 14, 'OF-2026-014', 'Solicitud urgente renovación licencias software médico', '2026-05-10', '2026-05-10 14:30:00'),
(@idTipo2, @idEstReg, @idUsuario2, 15, 'RES-2026-015', 'Resolución de aprobación plan estratégico digital 2026-2028', '2026-05-15', '2026-05-15 08:00:00');

PRINT '15 documentos de prueba insertados correctamente.';

-- 4. Insertar trámites de prueba
DECLARE @doc1 INT, @doc2 INT, @doc3 INT, @doc4 INT, @doc5 INT;
SELECT TOP 1 @doc1 = id_documento FROM documento ORDER BY id_documento;
SELECT TOP 1 @doc2 = id_documento FROM documento WHERE id_documento > @doc1 ORDER BY id_documento;
SELECT TOP 1 @doc3 = id_documento FROM documento WHERE id_documento > @doc2 ORDER BY id_documento;
SELECT TOP 1 @doc4 = id_documento FROM documento WHERE id_documento > @doc3 ORDER BY id_documento;
SELECT TOP 1 @doc5 = id_documento FROM documento WHERE id_documento > @doc4 ORDER BY id_documento;

INSERT INTO tramite (id_documento, id_usuario, id_destino, id_estado_tramite, fecha_sistema, observaciones)
VALUES
(@doc1, @idUsuario1, @idDep2, 1, '2026-01-10 09:30:00', 'Requiere revisión urgente por jefatura'),
(@doc2, @idUsuario2, @idDep3, 2, '2026-01-15 11:00:00', 'En proceso de firma'),
(@doc3, @idUsuario1, @idDep1, 1, '2026-02-01 08:30:00', 'Pendiente de revisión'),
(@doc4, @idUsuario2, @idDep2, 3, '2026-02-05 12:00:00', 'Aprobado y despachado'),
(@doc5, @idUsuario1, @idDep3, 1, '2026-02-10 09:30:00', 'Asignado para tramitación inmediata');

PRINT '5 trámites de prueba insertados.';
SELECT 'Limpieza completada' AS estado, COUNT(*) AS documentos FROM documento;
