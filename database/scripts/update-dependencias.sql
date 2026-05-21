-- ============================================================
-- SISDOC: Reemplazo completo de tabla dependencia
-- Backup previo + limpieza + inserción ordenada A-Z
-- ============================================================
USE SISDOC;
GO

-- 1. Backup de la tabla actual
IF OBJECT_ID('dependencia_bak_2026', 'U') IS NOT NULL DROP TABLE dependencia_bak_2026;
SELECT * INTO dependencia_bak_2026 FROM dependencia;
PRINT 'Backup creado en dependencia_bak_2026 (' + CAST(@@ROWCOUNT AS VARCHAR) + ' filas)';
GO

-- 2. Limpiar tabla y resetear IDENTITY
DELETE FROM dependencia;
DBCC CHECKIDENT ('dependencia', RESEED, 0);
PRINT 'Tabla dependencia vaciada y IDENTITY reseteado';
GO

-- 3. Insertar nuevas dependencias ordenadas A-Z
INSERT INTO dependencia (desc_dependencia, vigencia) VALUES
('Abastecimiento',                                    'S'),
('Administración de Contratos',                       'S'),
('Admisión y Recaudación',                            'S'),
('Adquisiciones',                                     'S'),
('Alimentación',                                      'S'),
('Alta Asistida',                                     'S'),
('Analistas de Personal',                             'S'),
('Apoyo Anatomía Patológica',                         'S'),
('Apoyo Anestesia',                                   'S'),
('Apoyo Endoscopia',                                  'S'),
('Apoyo Farmacia',                                    'S'),
('Apoyo Imagenología',                                'S'),
('Apoyo Laboratorio clínico',                         'S'),
('Apoyo Medicina Transfusional',                      'S'),
('Apoyo Pabellón',                                    'S'),
('Archivo',                                           'S'),
('Aseo Hospitalario y Ropa Clínica',                  'S'),
('Auditoria',                                         'S'),
('Biblioteca',                                        'S'),
('Bienestar',                                         'S'),
('Bodega',                                            'S'),
('Calidad de Vida',                                   'S'),
('Calidad Percibida',                                 'S'),
('Capacitación',                                      'S'),
('Centro de Simulacion',                              'S'),
('Clínica Asistencial HUAP',                          'S'),
('Cobranza',                                          'S'),
('Comite de Etica Asistencial',                       'S'),
('Comunicaciones y Relaciones publicas',               'S'),
('Contabilidad',                                      'S'),
('Convenios y Licitaciones',                          'S'),
('Dirección',                                         'S'),
('Estadística (Información para la Gestion Clínica)', 'S'),
('Esterilización',                                    'S'),
('Finanzas',                                          'S'),
('Formación, Investigación y Docencia',               'S'),
('Gestion de Calidad y Seguridad del Paciente',       'S'),
('Gestión de la Demanda',                             'S'),
('Gestión de Pacientes',                              'S'),
('Gestion y Desarrollo de Personas',                  'S'),
('GRD',                                               'S'),
('Honorarios',                                        'S'),
('I.A.A.S',                                          'S'),
('Inventario',                                        'S'),
('Jardín y Centro Escolar',                           'S'),
('Jurídica',                                          'S'),
('Licitaciones',                                      'S'),
('Mantención de Infraestructuras',                    'S'),
('Mantenimiento de Equipos Industriales',             'S'),
('Mantenimiento de Equipos Médicos',                  'S'),
('Medicina Física y Rehabilitación',                  'S'),
('Nutrición Enteral',                                 'S'),
('Oficina Custodia de especias',                      'S'),
('Oficina de Partes',                                 'S'),
('Planificación y Desarrollo',                        'S'),
('Prevención de Riesgos',                             'S'),
('Procuramiento de Órganos',                          'S'),
('Psicotrauma',                                       'S'),
('Reclutamiento y Selección',                         'S'),
('Recursos Físicos',                                  'S'),
('Rehabilitación y Gestión Funcional',                'S'),
('Remuneraciones',                                    'S'),
('Servicio Clinico Cirugia Hombre',                   'S'),
('Servicio Clinico de Cirugia Mujer',                 'S'),
('Servicio Clinico de Medicina Hombre',               'S'),
('Servicio Clinico de Medicina Mujer',                'S'),
('Servicio Clínico de Traumatología',                 'S'),
('Servicio Clínico de UPC',                           'S'),
('Servicio Clínico de Urgencia',                      'S'),
('Servicio Clínico Médico Quirúrgico Inderenciado',   'S'),
('Servicio Clínico Quemados',                         'S'),
('Servicio Clinico UTI',                              'S'),
('Servicio Dental de Urgencia',                       'S'),
('Servicio Social',                                   'S'),
('Subdirección Gestión Administrativa y Financiera',  'S'),
('Subdirección Gestión Clínica',                      'S'),
('Subdirección Gestion del Cuidado',                  'S'),
('Tecnologías De la Información',                     'S'),
('Tesorería',                                         'S'),
('Unidad de Angiografía',                             'S'),
('Unidad de Apoyo Kinesioterapia',                    'S'),
('Unidad de Ausentismo',                              'S'),
('Unidad de Desarrollo Organizacional',               'S'),
('Unidad de Ges',                                     'S'),
('Unidad de Neurología',                              'S'),
('Unidad de Proyectos',                               'S'),
('Unidad de Responsabilidad Administrativa',          'S'),
('Unidad de Respuesta Oportuna a Contraloría',        'S'),
('Unidad de Sumarios',                                'S'),
('Unidad Gestion de Convenios',                       'S'),
('UST',                                               'S');

PRINT 'Dependencias insertadas: ' + CAST(@@ROWCOUNT AS VARCHAR);
GO

-- 4. Verificación final
SELECT id_dependencia AS id, desc_dependencia AS dependencia
FROM dependencia
ORDER BY desc_dependencia;
GO
