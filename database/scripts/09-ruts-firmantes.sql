-- ============================================================
-- SCRIPT 09: RUTs de firmantes extraídos del documento oficial
-- SISDOC 2.0 — 2026-06-08
-- Fuente: PDF "JEFATURA.pdf" — nómina oficial de firmantes HUAP
-- Match: por id_jefatura (verificado contra nombre_titular)
-- ============================================================

USE SISDOC;
GO

-- Deshabilitar conteo de filas para output limpio
SET NOCOUNT ON;

-- ── Dirección ──────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='14048954-9', rut_subrogante='9541604-7', rut_subrogante_2='15309396-2' WHERE id_jefatura=4;
-- ── Oficina de Partes ──────────────────────────────────────────────
UPDATE jefatura SET rut_titular='15640910-3', rut_subrogante='12854210-8', rut_subrogante_2='16149156-K' WHERE id_jefatura=5;
-- ── Auditoria ─────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='8855435-3', rut_subrogante='16472995-8', rut_subrogante_2='12011374-7' WHERE id_jefatura=6;
-- ── GRD ───────────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='7438846-9', rut_subrogante='8313068-7', rut_subrogante_2='10551281-3' WHERE id_jefatura=7;
-- ── Comunicaciones y Relaciones Públicas ──────────────────────────
UPDATE jefatura SET rut_titular='16839302-4', rut_subrogante='19331816-9', rut_subrogante_2='19132577-K' WHERE id_jefatura=8;
-- ── Calidad Percibida ─────────────────────────────────────────────
UPDATE jefatura SET rut_titular='12458277-6', rut_subrogante='19556958-4', rut_subrogante_2='19782541-3' WHERE id_jefatura=9;
-- ── IAAS ──────────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='16693257-2', rut_subrogante='16693257-2' WHERE id_jefatura=10;
-- ── Calidad y Seguridad del Paciente ──────────────────────────────
UPDATE jefatura SET rut_titular='13234690-9', rut_subrogante='15125187-0', rut_subrogante_2='18560396-2' WHERE id_jefatura=11;
-- ── Jurídica ──────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='13480257-K', rut_subrogante='9423505-7', rut_subrogante_2='18458915-K' WHERE id_jefatura=12;
-- ── Salud del Trabajador ──────────────────────────────────────────
UPDATE jefatura SET rut_titular='16367199-9', rut_subrogante='17922562-K' WHERE id_jefatura=13;
-- ── Unidad de Proyectos ───────────────────────────────────────────
UPDATE jefatura SET rut_titular='15344163-4', rut_subrogante='14403766-9', rut_subrogante_2='17954513-6' WHERE id_jefatura=14;
-- ── Planificación y Desarrollo ────────────────────────────────────
UPDATE jefatura SET rut_titular='15023619-3', rut_subrogante='12011028-4', rut_subrogante_2='13139558-2' WHERE id_jefatura=15;
-- ── Control de Gestión ────────────────────────────────────────────
UPDATE jefatura SET rut_titular='15023619-3', rut_subrogante='13139558-2', rut_subrogante_2='18330951-K' WHERE id_jefatura=16;
-- ── Estadística ───────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='12011028-4', rut_subrogante='12013129-K', rut_subrogante_2='13549216-7' WHERE id_jefatura=17;
-- ── Participación Ciudadana ───────────────────────────────────────
UPDATE jefatura SET rut_titular='13421270-5', rut_subrogante='16172940-K' WHERE id_jefatura=18;
-- ── Formación, Investigación y Docencia ──────────────────────────
UPDATE jefatura SET rut_titular='14046693-K', rut_subrogante='17208308-0' WHERE id_jefatura=19;
-- ── Relación Asistencial Docente y Formación ─────────────────────
UPDATE jefatura SET rut_titular='17208308-0', rut_subrogante='14046693-K' WHERE id_jefatura=20;
-- ── Investigación ─────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='12122367-8', rut_subrogante='14016749-5' WHERE id_jefatura=21;
-- ── Biblioteca ────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='16240726-0', rut_subrogante='14046693-K' WHERE id_jefatura=22;
-- ── Centro de Simulación ─────────────────────────────────────────
UPDATE jefatura SET rut_titular='16717977-0', rut_subrogante='14046693-K' WHERE id_jefatura=23;
-- ── Subdirección Gestión Clínica ──────────────────────────────────
UPDATE jefatura SET rut_titular='14048954-9', rut_subrogante='15853558-0', rut_subrogante_2='16840052-7' WHERE id_jefatura=24;
-- ── Epidemiología Clínica ─────────────────────────────────────────
UPDATE jefatura SET rut_titular='18103768-7', rut_subrogante='8041302-5' WHERE id_jefatura=25;
-- ── Gestión de la Demanda ─────────────────────────────────────────
UPDATE jefatura SET rut_titular='16840052-7', rut_subrogante='15853558-0' WHERE id_jefatura=26;
-- ── Gestión de Pacientes ──────────────────────────────────────────
UPDATE jefatura SET rut_titular='15853558-0', rut_subrogante='16840052-7' WHERE id_jefatura=27;
-- ── Servicio Social ───────────────────────────────────────────────
UPDATE jefatura SET rut_titular='15788512-K', rut_subrogante='16840052-7' WHERE id_jefatura=28;
-- ── Hospitalización Domiciliaria ─────────────────────────────────
UPDATE jefatura SET rut_titular='18485203-9', rut_subrogante='14713404-5' WHERE id_jefatura=29;
-- ── Unidad de GES ────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='17266808-9', rut_subrogante='16840052-7' WHERE id_jefatura=30;
-- ── Unidad Gestión de Convenios ───────────────────────────────────
UPDATE jefatura SET rut_titular='15380244-0', rut_subrogante='16840052-7' WHERE id_jefatura=31;
-- ── Rehabilitación y Gestión Funcional ───────────────────────────
UPDATE jefatura SET rut_titular='16634358-5', rut_subrogante='15156951-K', rut_subrogante_2='15347680-2' WHERE id_jefatura=32;
-- ── Apoyo Anatomía Patológica ─────────────────────────────────────
UPDATE jefatura SET rut_titular='12038633-6', rut_subrogante='15501104-1' WHERE id_jefatura=33;
-- ── Banco de Sangre ───────────────────────────────────────────────
UPDATE jefatura SET rut_titular='16008482-0', rut_subrogante='18953088-9', rut_subrogante_2='18217906-K' WHERE id_jefatura=34;
-- ── Apoyo Imagenología ────────────────────────────────────────────
UPDATE jefatura SET rut_titular='14226472-2', rut_subrogante='15336460-5', rut_subrogante_2='10006521-5' WHERE id_jefatura=35;
-- ── Procuramiento de Órganos ──────────────────────────────────────
UPDATE jefatura SET rut_titular='19189058-2', rut_subrogante='18738899-6' WHERE id_jefatura=36;
-- ── Apoyo Laboratorio Clínico ─────────────────────────────────────
UPDATE jefatura SET rut_titular='15501104-1', rut_subrogante='16008130-9', rut_subrogante_2='17387320-4' WHERE id_jefatura=37;
-- ── Farmacia Clínica ──────────────────────────────────────────────
UPDATE jefatura SET rut_titular='17266570-5', rut_subrogante='17285996-8', rut_subrogante_2='19671224-0' WHERE id_jefatura=38;
-- ── Apoyo Farmacia ────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='16752358-7', rut_subrogante='18277741-2' WHERE id_jefatura=39;
-- ── Nutrición Enteral ─────────────────────────────────────────────
UPDATE jefatura SET rut_titular='16152005-5', rut_subrogante='16508514-0', rut_subrogante_2='17266176-9' WHERE id_jefatura=40;
-- ── Servicio Clínico de Urgencia ──────────────────────────────────
UPDATE jefatura SET rut_titular='17169305-5', rut_subrogante='17403272-6', rut_subrogante_2='13752126-1' WHERE id_jefatura=41;
-- ── Servicio Dental de Urgencia ───────────────────────────────────
UPDATE jefatura SET rut_titular='9668034-1', rut_subrogante='7976010-2' WHERE id_jefatura=42;
-- ── UCI - MQ ──────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='16960965-9', rut_subrogante='18104692-9' WHERE id_jefatura=43;
-- ── Servicio Clínico Quemados ─────────────────────────────────────
UPDATE jefatura SET rut_titular='15372357-5', rut_subrogante='17786748-9' WHERE id_jefatura=44;
-- ── Servicio Clínico UTI ──────────────────────────────────────────
UPDATE jefatura SET rut_titular='17812089-1', rut_subrogante='17723360-9' WHERE id_jefatura=45;
-- ── Cuidados Medios MQ ────────────────────────────────────────────
UPDATE jefatura SET rut_titular='19036885-8', rut_subrogante='16478779-6' WHERE id_jefatura=46;
-- ── Clínica Asistencial HUAP ──────────────────────────────────────
UPDATE jefatura SET rut_titular='19135040-5', rut_subrogante='19360669-5' WHERE id_jefatura=47;
-- ── Pabellón y Anestesia ──────────────────────────────────────────
UPDATE jefatura SET rut_titular='17190825-6', rut_subrogante='16360125-7' WHERE id_jefatura=48;
-- ── Servicio Clínico de Traumatología ────────────────────────────
UPDATE jefatura SET rut_titular='10489815-7', rut_subrogante='21898203-4', rut_subrogante_2='13904198-4' WHERE id_jefatura=49;
-- ── Unidad de Angiografía ─────────────────────────────────────────
UPDATE jefatura SET rut_titular='15379812-5', rut_subrogante='14663244-0' WHERE id_jefatura=50;
-- ── Neurocirugía ──────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='16608990-5', rut_subrogante='6247706-7', rut_subrogante_2='7806583-4' WHERE id_jefatura=51;
-- ── Urología ──────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='17990987-1', rut_subrogante='14048954-9' WHERE id_jefatura=52;
-- ── Cirugía Diferida ──────────────────────────────────────────────
UPDATE jefatura SET rut_titular='17314964-6', rut_subrogante='14048954-9' WHERE id_jefatura=53;
-- ── Cirugía ───────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='14430133-1', rut_subrogante='17981763-2', rut_subrogante_2='17043981-3' WHERE id_jefatura=54;
-- ── Apoyo Endoscopia ──────────────────────────────────────────────
UPDATE jefatura SET rut_titular='14430133-1', rut_subrogante='17981763-2', rut_subrogante_2='17043981-3' WHERE id_jefatura=55;
-- ── Oncología (titular = Director en su reemplazo, sub sin RUT en PDF)
UPDATE jefatura SET rut_titular='14048954-9' WHERE id_jefatura=56;
-- ── Psiquiatría y Psicología de Enlace ───────────────────────────
UPDATE jefatura SET rut_titular='16558314-0', rut_subrogante='18769308-K' WHERE id_jefatura=57;
-- ── Infectología ──────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='14048954-9', rut_subrogante='15372357-5' WHERE id_jefatura=58;
-- ── Medicina Paliativa ────────────────────────────────────────────
UPDATE jefatura SET rut_titular='19201843-9', rut_subrogante='16098047-8' WHERE id_jefatura=59;
-- ── Nefrología ────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='14048954-9', rut_subrogante='15853558-0' WHERE id_jefatura=60;
-- ── Cardiología ───────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='16212049-2', rut_subrogante='18285113-2' WHERE id_jefatura=61;
-- ── Unidad de Neurología ──────────────────────────────────────────
UPDATE jefatura SET rut_titular='16893512-9', rut_subrogante='10010629-9', rut_subrogante_2='17875775-K' WHERE id_jefatura=62;
-- ── Medicina Física y Rehabilitación ─────────────────────────────
UPDATE jefatura SET rut_titular='18925116-5', rut_subrogante='17035725-6' WHERE id_jefatura=63;
-- ── Subdirección Gestión del Cuidado ─────────────────────────────
UPDATE jefatura SET rut_titular='15385131-K', rut_subrogante='16616200-9', rut_subrogante_2='9658638-8' WHERE id_jefatura=64;
-- ── Buenas Prácticas Clínicas ─────────────────────────────────────
UPDATE jefatura SET rut_titular='17793714-2', rut_subrogante='5266719-4' WHERE id_jefatura=66;
-- ── Accesos Vasculares ────────────────────────────────────────────
UPDATE jefatura SET rut_titular='13992557-2', rut_subrogante='13954288-6' WHERE id_jefatura=67;
-- ── Gestión en Insumos Clínicos y Equipamiento MQ ────────────────
UPDATE jefatura SET rut_titular='13768875-1', rut_subrogante='18119215-1' WHERE id_jefatura=68;
-- ── Aseo Hospitalario y Ropa Clínica ─────────────────────────────
UPDATE jefatura SET rut_titular='8408639-8', rut_subrogante='15353614-7' WHERE id_jefatura=69;
-- ── Emergencia Hospitalaria (Enfermería) ──────────────────────────
UPDATE jefatura SET rut_titular='13322809-8', rut_subrogante='15978979-9', rut_subrogante_2='15464760-0' WHERE id_jefatura=70;
-- ── Urgencia Dental y Cirugía Maxilofacial (Enfermería) ───────────
UPDATE jefatura SET rut_titular='13322809-9', rut_subrogante='19307905-9' WHERE id_jefatura=71;
-- ── UCI - MQ (Enfermería) ─────────────────────────────────────────
UPDATE jefatura SET rut_titular='17332586-K', rut_subrogante='17382013-5', rut_subrogante_2='17673980-0' WHERE id_jefatura=72;
-- ── Quemados (Enfermería) ─────────────────────────────────────────
UPDATE jefatura SET rut_titular='15368352-2', rut_subrogante='16579628-4', rut_subrogante_2='17312692-1' WHERE id_jefatura=73;
-- ── UTI - MQ (Enfermería) ─────────────────────────────────────────
UPDATE jefatura SET rut_titular='17673980-0', rut_subrogante='18767324-0', rut_subrogante_2='18169957-4' WHERE id_jefatura=74;
-- ── Cuidados Medios MQ 3° ────────────────────────────────────────
UPDATE jefatura SET rut_titular='15641842-0', rut_subrogante='17203578-7' WHERE id_jefatura=75;
-- ── Cuidados Medios MQ 4° ────────────────────────────────────────
UPDATE jefatura SET rut_titular='15342681-3', rut_subrogante='17190854-K', rut_subrogante_2='15641842-0' WHERE id_jefatura=76;
-- ── Cuidados Medios MQ 6° ────────────────────────────────────────
UPDATE jefatura SET rut_titular='17203578-7', rut_subrogante='15976997-6', rut_subrogante_2='15368352-2' WHERE id_jefatura=77;
-- ── Clínica HUAP (Enfermería) ─────────────────────────────────────
UPDATE jefatura SET rut_titular='14739810-7', rut_subrogante='15316465-7' WHERE id_jefatura=78;
-- ── Hospitalización Domiciliaria (Enfermería) ─────────────────────
UPDATE jefatura SET rut_titular='16616200-9', rut_subrogante='15385131-K' WHERE id_jefatura=79;
-- ── Apoyo Pabellón ────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='13502690-5', rut_subrogante='15353614-7', rut_subrogante_2='15721744-5' WHERE id_jefatura=80;
-- ── Angiografía (Enfermería) ──────────────────────────────────────
UPDATE jefatura SET rut_titular='9658638-8', rut_subrogante='17707612-0', rut_subrogante_2='17248214-7' WHERE id_jefatura=81;
-- ── Esterilización ────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='15353614-7', rut_subrogante='16171603-0', rut_subrogante_2='16251025-8' WHERE id_jefatura=82;
-- ── Cirugía - Urología - Neurocirugía ────────────────────────────
UPDATE jefatura SET rut_titular='16628770-7', rut_subrogante='19245062-4' WHERE id_jefatura=83;
-- ── Endoscopía (Enfermería) ───────────────────────────────────────
UPDATE jefatura SET rut_titular='16171603-0', rut_subrogante='15353614-7' WHERE id_jefatura=84;
-- ── Control Post Alta ─────────────────────────────────────────────
UPDATE jefatura SET rut_titular='19245062-4', rut_subrogante='18920975-4' WHERE id_jefatura=85;
-- ── Psiquiatría y Psicología de Enlace (Enfermería) ──────────────
UPDATE jefatura SET rut_titular='16543773-K', rut_subrogante='15385131-K', rut_subrogante_2='20910015-0' WHERE id_jefatura=86;
-- ── Medicina Paliativa (Enfermería) ───────────────────────────────
UPDATE jefatura SET rut_titular='15385131-K', rut_subrogante='13954288-6' WHERE id_jefatura=87;
-- ── Nefrología (Enfermería) ───────────────────────────────────────
UPDATE jefatura SET rut_titular='13954288-6', rut_subrogante='13992557-2' WHERE id_jefatura=88;
-- ── Cardiología (Enfermería) ──────────────────────────────────────
UPDATE jefatura SET rut_titular='16462561-3', rut_subrogante='18635736-1' WHERE id_jefatura=89;
-- ── Neurología (Enfermería) ───────────────────────────────────────
UPDATE jefatura SET rut_titular='13078656-1', rut_subrogante='19151390-8' WHERE id_jefatura=90;
-- ── Subdirección Gestión Administrativa y Financiera ─────────────
UPDATE jefatura SET rut_titular='9541604-7', rut_subrogante='10538370-3', rut_subrogante_2='9657519-K' WHERE id_jefatura=91;
-- ── Control de Presupuesto ────────────────────────────────────────
UPDATE jefatura SET rut_titular='9276681-0', rut_subrogante='9541604-7', rut_subrogante_2='10538370-3' WHERE id_jefatura=92;
-- ── Admisión y Recaudación ────────────────────────────────────────
UPDATE jefatura SET rut_titular='16412219-0', rut_subrogante='12491047-1', rut_subrogante_2='13839317-8' WHERE id_jefatura=93;
-- ── Finanzas ──────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='9657519-K', rut_subrogante='18082484-7', rut_subrogante_2='13497112-6' WHERE id_jefatura=94;
-- ── Contabilidad ──────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='10193966-9', rut_subrogante='13497112-6' WHERE id_jefatura=95;
-- ── Tesorería ─────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='18082484-7', rut_subrogante='18340036-3' WHERE id_jefatura=96;
-- ── Cobranza ──────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='14514299-7', rut_subrogante='12499536-1' WHERE id_jefatura=97;
-- ── Análisis Financiero ───────────────────────────────────────────
UPDATE jefatura SET rut_titular='9657519-K', rut_subrogante='18082484-7' WHERE id_jefatura=98;
-- ── Abastecimiento ────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='15762009-6', rut_subrogante='12615931-5', rut_subrogante_2='16042815-5' WHERE id_jefatura=99;
-- ── Licitaciones (titular en BD = Esteban Ahumada Díaz) ──────────
UPDATE jefatura SET rut_titular='18737215-1' WHERE id_jefatura=100;
-- ── Administración de Contratos ───────────────────────────────────
UPDATE jefatura SET rut_titular='16042815-5', rut_subrogante='16243201-K' WHERE id_jefatura=101;
-- ── Adquisiciones ─────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='12615931-5', rut_subrogante='10419502-4' WHERE id_jefatura=102;
-- ── Aprovisionamiento y Distribución ─────────────────────────────
UPDATE jefatura SET rut_titular='10673135-7', rut_subrogante='22887399-3', rut_subrogante_2='18061010-3' WHERE id_jefatura=103;
-- ── Tecnologías de la Información ────────────────────────────────
UPDATE jefatura SET rut_titular='16127425-9', rut_subrogante='13712920-5', rut_subrogante_2='15620695-4' WHERE id_jefatura=3;
-- ── Operación y Soporte ───────────────────────────────────────────
UPDATE jefatura SET rut_titular='15620695-4', rut_subrogante='15504560-4' WHERE id_jefatura=104;
-- ── Desarrollo Tecnológico ────────────────────────────────────────
UPDATE jefatura SET rut_titular='13712920-5', rut_subrogante='11425616-1' WHERE id_jefatura=105;
-- ── Recursos Físicos ──────────────────────────────────────────────
UPDATE jefatura SET rut_titular='17168380-7', rut_subrogante='13152044-1', rut_subrogante_2='19111012-9' WHERE id_jefatura=106;
-- ── Mantenimiento de Equipos Médicos ─────────────────────────────
UPDATE jefatura SET rut_titular='19111012-9', rut_subrogante='17168380-7' WHERE id_jefatura=107;
-- ── Mantenimiento de Equipos Industriales ────────────────────────
UPDATE jefatura SET rut_titular='16629383-9', rut_subrogante='17029455-6', rut_subrogante_2='17168380-7' WHERE id_jefatura=108;
-- ── Mantención de Infraestructuras ───────────────────────────────
UPDATE jefatura SET rut_titular='13152044-1', rut_subrogante='15930270-9', rut_subrogante_2='17168380-7' WHERE id_jefatura=109;
-- ── Departamento de Servicios Generales ──────────────────────────
UPDATE jefatura SET rut_titular='10538370-3', rut_subrogante='9276681-0' WHERE id_jefatura=110;
-- ── Operaciones ───────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='9276681-0', rut_subrogante='10538370-3' WHERE id_jefatura=111;
-- ── Servicios Básicos ─────────────────────────────────────────────
UPDATE jefatura SET rut_titular='10538370-3', rut_subrogante='9276681-0' WHERE id_jefatura=112;
-- ── Inventario ────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='13682169-5', rut_subrogante='18098336-8' WHERE id_jefatura=113;
-- ── Gestión y Desarrollo de Personas ─────────────────────────────
UPDATE jefatura SET rut_titular='15309396-2', rut_subrogante='12217147-7', rut_subrogante_2='12070057-K' WHERE id_jefatura=114;
-- ── Gestión Procesos y Mejora Continua ───────────────────────────
UPDATE jefatura SET rut_titular='19251333-2', rut_subrogante='15309396-2', rut_subrogante_2='12217147-7' WHERE id_jefatura=115;
-- ── Departamento Gestión de las Personas ─────────────────────────
UPDATE jefatura SET rut_titular='12217147-7', rut_subrogante='15309396-2' WHERE id_jefatura=116;
-- ── Remuneraciones ────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='10806785-3' WHERE id_jefatura=117;
-- ── Honorarios ────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='18555791-K', rut_subrogante='12217147-7' WHERE id_jefatura=118;
-- ── Administración de Personas ───────────────────────────────────
UPDATE jefatura SET rut_titular='12217147-7' WHERE id_jefatura=119;
-- ── Control de la Jornada ────────────────────────────────────────
UPDATE jefatura SET rut_titular='12217147-7', rut_subrogante='15309396-2' WHERE id_jefatura=120;
-- ── Calidad de Vida ───────────────────────────────────────────────
UPDATE jefatura SET rut_titular='12217147-7', rut_subrogante='14045405-2' WHERE id_jefatura=121;
-- ── Prevención de Riesgos ─────────────────────────────────────────
UPDATE jefatura SET rut_titular='15631655-5', rut_subrogante='11643920-4' WHERE id_jefatura=122;
-- ── Sala Cuna y Cuidados Infantiles ───────────────────────────────
UPDATE jefatura SET rut_titular='13694210-7', rut_subrogante='19248432-4' WHERE id_jefatura=123;
-- ── Bienestar ─────────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='14045405-2', rut_subrogante='9484480-0' WHERE id_jefatura=124;
-- ── Departamento Gestión del Cambio ──────────────────────────────
UPDATE jefatura SET rut_titular='15309396-2', rut_subrogante='12070057-K', rut_subrogante_2='12217147-7' WHERE id_jefatura=125;
-- ── Unidad de Desarrollo Organizacional ──────────────────────────
UPDATE jefatura SET rut_titular='11653819-9', rut_subrogante='15309396-2' WHERE id_jefatura=126;
-- ── Capacitación ─────────────────────────────────────────────────
UPDATE jefatura SET rut_titular='12070057-K', rut_subrogante='15786677-K' WHERE id_jefatura=127;
-- ── Reclutamiento y Selección ─────────────────────────────────────
UPDATE jefatura SET rut_titular='15309396-2', rut_subrogante='12217147-7' WHERE id_jefatura=128;
-- ── Gestión en Procesos Clínicos - Asistenciales ─────────────────
UPDATE jefatura SET rut_titular='15385131-K', rut_subrogante='16616200-9' WHERE id_jefatura=129;

GO

-- ── Verificación final ────────────────────────────────────────────
SELECT
  COUNT(*) AS total_jefaturas,
  SUM(CASE WHEN rut_titular     IS NOT NULL THEN 1 ELSE 0 END) AS con_rut_titular,
  SUM(CASE WHEN rut_subrogante  IS NOT NULL AND nombre_subrogante  IS NOT NULL THEN 1 ELSE 0 END) AS sub_con_rut,
  SUM(CASE WHEN rut_titular     IS NULL THEN 1 ELSE 0 END) AS sin_rut_titular
FROM jefatura;
GO

PRINT '=== Script 09 completado — RUTs cargados desde nómina oficial ===';
