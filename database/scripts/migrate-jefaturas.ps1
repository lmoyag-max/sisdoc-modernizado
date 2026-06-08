# =====================================================================
# MIGRACIÓN JEFATURAS — SISDOC 2.0
# Fecha: 2026-06-08
# Decisiones aprobadas:
#   - 14 servicios duplicados → crear deps de Enfermería con " (Enfermería)"
#   - ~50 deps faltantes → crear todas en tabla dependencia (idempotente)
#   - Cargo genérico: "Director/a", "Subdirector/a", o "Jefe/a de Departamento"
#   - Subrogantes: cargo = "Subrogante"
#   - Licitaciones sin titular → sub1 pasa a ser titular
#   - IAAS RUT duplicado → importar igual, corregir desde UI después
#   - Todo activo (activo=1), sin fechas de vigencia (NULL)
#   - Jefes Turno A/B/C/D → EXCLUIDOS
# =====================================================================

$connStr = "Server=127.0.0.1,11433;Database=SISDOC;User Id=sa;Password=Adminhuap2026!;TrustServerCertificate=True;Encrypt=True;MultipleActiveResultSets=False;Connect Timeout=30"
$conn = New-Object System.Data.SqlClient.SqlConnection($connStr)
try { $conn.Open() } catch {
    # fallback: try Encrypt=False
    $connStr2 = "Server=127.0.0.1,11433;Database=SISDOC;User Id=sa;Password=Adminhuap2026!;Encrypt=False;Connect Timeout=30"
    $conn = New-Object System.Data.SqlClient.SqlConnection($connStr2)
    $conn.Open()
}
if ($conn.State -ne 'Open') { Write-Error "No se pudo conectar a la BD"; exit 1 }
Write-Host "Conectado a SISDOC"

function Exec-Scalar([string]$sql) {
    $cmd = $conn.CreateCommand(); $cmd.CommandText = $sql; return $cmd.ExecuteScalar()
}
function Exec-NonQuery([string]$sql) {
    $cmd = $conn.CreateCommand(); $cmd.CommandText = $sql; return $cmd.ExecuteNonQuery()
}
function Escape-Sql([string]$s) { return $s -replace "'", "''" }

# ─────────────────────────────────────────────────────────────────────
# BACKUP idempotente
# ─────────────────────────────────────────────────────────────────────
Exec-NonQuery "IF OBJECT_ID('dependencia_backup_20260608','U') IS NULL SELECT * INTO dependencia_backup_20260608 FROM dependencia" | Out-Null
Exec-NonQuery "IF OBJECT_ID('jefatura_backup_20260608','U') IS NULL SELECT * INTO jefatura_backup_20260608 FROM jefatura" | Out-Null
Write-Host "Backup asegurado: dependencia_backup_20260608 / jefatura_backup_20260608"

# ─────────────────────────────────────────────────────────────────────
# Helpers
# ─────────────────────────────────────────────────────────────────────
function Ensure-Dep([string]$name) {
    $esc = Escape-Sql $name
    $id  = Exec-Scalar "SELECT id_dependencia FROM dependencia WHERE LTRIM(RTRIM(desc_dependencia)) = '$esc'"
    if ($null -eq $id) {
        $id = Exec-Scalar "INSERT INTO dependencia (desc_dependencia) OUTPUT INSERTED.id_dependencia VALUES ('$esc')"
        Write-Host "  [NEW dep]  id=$id  '$name'"
    }
    return [int]$id
}

function Get-Cargo([string]$service) {
    if ($service -match '(?i)^\s*director')   { return "Director/a" }
    if ($service -match '(?i)^\s*subdirector') { return "Subdirector/a" }
    return "Jefe/a de Departamento"
}

function Upsert-Jefatura {
    param([int]$depId, [string]$nomTit, [string]$cargoTit,
          [string]$nomSub1 = '', [string]$nomSub2 = '')

    $tE   = Escape-Sql $nomTit
    $ctE  = Escape-Sql $cargoTit
    $s1   = if ($nomSub1) { "'" + (Escape-Sql $nomSub1) + "'" } else { "NULL" }
    $s2   = if ($nomSub2) { "'" + (Escape-Sql $nomSub2) + "'" } else { "NULL" }
    $cs1  = if ($nomSub1) { "'Subrogante'" } else { "NULL" }
    $cs2  = if ($nomSub2) { "'Subrogante'" } else { "NULL" }
    $a1   = if ($nomSub1) { 1 } else { 0 }
    $a2   = if ($nomSub2) { 1 } else { 0 }

    $sql = @"
IF EXISTS (SELECT 1 FROM jefatura WHERE id_dependencia = $depId)
  UPDATE jefatura SET
    nombre_titular     = '$tE',  cargo_titular    = '$ctE', activo_titular = 1,
    vigencia_desde_titular = NULL, vigencia_hasta_titular = NULL,
    nombre_subrogante  = $s1,    cargo_subrogante  = $cs1,  activo_subrogante  = $a1,
    vigencia_desde_sub = NULL, vigencia_hasta_sub = NULL,
    nombre_subrogante_2 = $s2,   cargo_subrogante_2 = $cs2, activo_subrogante_2 = $a2,
    vigencia_desde_sub_2 = NULL, vigencia_hasta_sub_2 = NULL,
    fecha_update = GETDATE(), id_usuario_modificacion = 532
  WHERE id_dependencia = $depId
ELSE
  INSERT INTO jefatura
    (id_dependencia, nombre_titular, cargo_titular, activo_titular,
     nombre_subrogante, cargo_subrogante, activo_subrogante,
     nombre_subrogante_2, cargo_subrogante_2, activo_subrogante_2,
     fecha_sistema, fecha_update, id_usuario_modificacion)
  VALUES
    ($depId, '$tE', '$ctE', 1,
     $s1, $cs1, $a1,
     $s2, $cs2, $a2,
     GETDATE(), GETDATE(), 532)
"@
    Exec-NonQuery $sql | Out-Null
}

# ─────────────────────────────────────────────────────────────────────
# Mapping: línea CSV (2–131) → dep_id a usar
#   existingId: usar dep existente con ese id
#   newName:    crear/buscar dep con ese nombre
#   skipRow:    no migrar esta fila
# ─────────────────────────────────────────────────────────────────────
$M = New-Object 'System.Collections.Generic.Dictionary[int,hashtable]'
$M.Add(2,   @{ existingId = 32 })   # Director → Dirección
$M.Add(3,   @{ existingId = 54 })   # Oficina de Partes
$M.Add(4,   @{ existingId = 18 })   # Auditoria
$M.Add(5,   @{ existingId = 41 })   # Análisis Clínico GRD → GRD
$M.Add(6,   @{ existingId = 29 })   # Comunicaciones y Relaciones Públicas
$M.Add(7,   @{ existingId = 23 })   # Calidad Percibida
$M.Add(8,   @{ existingId = 43 })   # IAAS → I.A.A.S
$M.Add(9,   @{ existingId = 37 })   # Calidad y Seguridad del Paciente
$M.Add(10,  @{ existingId = 46 })   # Asesoría Jurídica → Jurídica
$M.Add(11,  @{ newName = "Salud del Trabajador" })
$M.Add(12,  @{ existingId = 86 })   # Proyectos → Unidad de Proyectos
$M.Add(13,  @{ existingId = 55 })   # Depto. Planificación y C. Gestión → Planificación y Desarrollo
$M.Add(14,  @{ newName = "Control de Gestión" })
$M.Add(15,  @{ existingId = 33 })   # Estadística
$M.Add(16,  @{ newName = "Participación Ciudadana" })
$M.Add(17,  @{ existingId = 36 })   # Depto. Formación, Investigación y Docencia
$M.Add(18,  @{ newName = "Relación Asistencial Docente y Formación" })
$M.Add(19,  @{ newName = "Investigación" })
$M.Add(20,  @{ existingId = 19 })   # Biblioteca
$M.Add(21,  @{ existingId = 25 })   # Simulación y Docencia Continua → Centro de Simulacion
$M.Add(22,  @{ existingId = 76 })   # Subdirector/a de Gestión Clínica
$M.Add(23,  @{ newName = "Epidemiología Clínica" })
$M.Add(24,  @{ existingId = 38 })   # Depto. Gestión de la Demanda
$M.Add(25,  @{ existingId = 39 })   # Gestión de Pacientes
$M.Add(26,  @{ existingId = 74 })   # Servicio Social de Pacientes → Servicio Social
$M.Add(27,  @{ newName = "Hospitalización Domiciliaria" })
$M.Add(28,  @{ existingId = 84 })   # GES → Unidad de Ges
$M.Add(29,  @{ existingId = 90 })   # Gestión de Convenios Clínicos
$M.Add(30,  @{ existingId = 61 })   # Rehabilitación y Gestión Funcional
$M.Add(31,  @{ existingId = 8  })   # Anatomía Patológica → Apoyo Anatomía Patológica
$M.Add(32,  @{ newName = "Banco de Sangre" })
$M.Add(33,  @{ existingId = 12 })   # Imagenología → Apoyo Imagenología
$M.Add(34,  @{ existingId = 57 })   # Procuramiento de Órganos y Tejidos
$M.Add(35,  @{ existingId = 13 })   # Laboratorio Clínico → Apoyo Laboratorio clínico
$M.Add(36,  @{ newName = "Farmacia Clínica" })
$M.Add(37,  @{ existingId = 11 })   # Farmacia → Apoyo Farmacia
$M.Add(38,  @{ existingId = 52 })   # Nutrición → Nutrición Enteral
$M.Add(39,  @{ existingId = 69 })   # Emergencia Hospitalaria (médico)
$M.Add(40,  @{ existingId = 73 })   # Urgencia Dental y Cirugia Maxilofacial (médico)
$M.Add(41,  @{ newName = "UCI - MQ" })
$M.Add(42,  @{ existingId = 71 })   # Quemados (médico)
$M.Add(43,  @{ existingId = 72 })   # UTI - MQ (médico)
$M.Add(44,  @{ newName = "Cuidados Medios MQ" })
$M.Add(45,  @{ existingId = 26 })   # Clínica HUAP (médico)
$M.Add(46,  @{ newName = "Pabellón y Anestesia" })
$M.Add(47,  @{ existingId = 67 })   # Traumatología
$M.Add(48,  @{ existingId = 80 })   # Angiografía (médico)
$M.Add(49,  @{ newName = "Neurocirugía" })
$M.Add(50,  @{ newName = "Urología" })
$M.Add(51,  @{ newName = "Cirugía Diferida" })
$M.Add(52,  @{ newName = "Cirugía" })
$M.Add(53,  @{ existingId = 10 })   # Endoscopía (médico) → Apoyo Endoscopia
$M.Add(54,  @{ newName = "Oncología" })
$M.Add(55,  @{ newName = "Psiquiatría y Psicología de Enlace" })
$M.Add(56,  @{ newName = "Infectología" })
$M.Add(57,  @{ newName = "Medicina Paliativa" })
$M.Add(58,  @{ newName = "Nefrología" })
$M.Add(59,  @{ newName = "Cardiología" })
$M.Add(60,  @{ existingId = 85 })   # Neurología (médico) → Unidad de Neurología
$M.Add(61,  @{ existingId = 51 })   # Fisiatría → Medicina Física y Rehabilitación
$M.Add(62,  @{ existingId = 77 })   # Subdirector/a de Gestión del Cuidado
$M.Add(63,  @{ newName = "Departamento de Gestión en Procesos Clínicos - Asistenciales" })
$M.Add(64,  @{ newName = "Buenas Prácticas Clínicas" })
$M.Add(65,  @{ newName = "Accesos Vasculares" })
$M.Add(66,  @{ newName = "Gestión en Insumos Clínicos y Equipamiento MQ" })
$M.Add(67,  @{ existingId = 17 })   # Distribución y Gestión de Ropa clínica → Aseo Hosp. y Ropa Clínica
$M.Add(68,  @{ skipRow = $true })   # Jefe Turno A
$M.Add(69,  @{ skipRow = $true })   # Jefe Turno B
$M.Add(70,  @{ skipRow = $true })   # Jefe Turno C
$M.Add(71,  @{ skipRow = $true })   # Jefe Turno D
$M.Add(72,  @{ newName = "Emergencia Hospitalaria (Enfermería)" })
$M.Add(73,  @{ newName = "Urgencia Dental y Cirugia Maxilofacial (Enfermería)" })
$M.Add(74,  @{ newName = "UCI - MQ (Enfermería)" })
$M.Add(75,  @{ newName = "Quemados (Enfermería)" })
$M.Add(76,  @{ newName = "UTI - MQ (Enfermería)" })
$M.Add(77,  @{ newName = "Cuidados Medios MQ 3°" })
$M.Add(78,  @{ newName = "Cuidados Medios MQ 4°" })
$M.Add(79,  @{ newName = "Cuidados Medios MQ 6°" })
$M.Add(80,  @{ newName = "Clínica HUAP (Enfermería)" })
$M.Add(81,  @{ newName = "Hospitalización Domiciliaria (Enfermería)" })
$M.Add(82,  @{ existingId = 15 })   # Pabellón (enfermería) → Apoyo Pabellón
$M.Add(83,  @{ newName = "Angiografía (Enfermería)" })
$M.Add(84,  @{ existingId = 34 })   # Esterilización
$M.Add(85,  @{ newName = "Cirugía - Urología - Neurocirugía" })
$M.Add(86,  @{ newName = "Endoscopía (Enfermería)" })
$M.Add(87,  @{ newName = "Control Post Alta" })
$M.Add(88,  @{ newName = "Psiquiatría y Psicología de Enlace (Enfermería)" })
$M.Add(89,  @{ newName = "Medicina Paliativa (Enfermería)" })
$M.Add(90,  @{ newName = "Nefrología (Enfermería)" })
$M.Add(91,  @{ newName = "Cardiología (Enfermería)" })
$M.Add(92,  @{ newName = "Neurología (Enfermería)" })
$M.Add(93,  @{ existingId = 75 })   # Subdirector/a de Gestión Administrativa y Financiera
$M.Add(94,  @{ newName = "Control de Presupuesto" })
$M.Add(95,  @{ existingId = 3  })   # Admisión → Admisión y Recaudación
$M.Add(96,  @{ existingId = 35 })   # Departamento de Finanzas → Finanzas
$M.Add(97,  @{ existingId = 30 })   # Contabilidad
$M.Add(98,  @{ existingId = 79 })   # Tesorería
$M.Add(99,  @{ existingId = 27 })   # Cobranzas → Cobranza
$M.Add(100, @{ newName = "Análisis Financiero" })
$M.Add(101, @{ existingId = 1  })   # Departamento de Abastecimiento → Abastecimiento
$M.Add(102, @{ existingId = 47 })   # Licitaciones (special: sub1→titular)
$M.Add(103, @{ existingId = 2  })   # Gestión de Contratos → Administración de Contratos
$M.Add(104, @{ existingId = 4  })   # Adquisiciones
$M.Add(105, @{ newName = "Aprovisionamiento y Distribución" })
$M.Add(106, @{ existingId = 78 })   # Departamento de TI → Tecnologías De la Información
$M.Add(107, @{ newName = "Operación y Soporte" })
$M.Add(108, @{ newName = "Desarrollo Tecnológico" })
$M.Add(109, @{ existingId = 60 })   # Departamento de Recursos Físicos
$M.Add(110, @{ existingId = 50 })   # Equipos Médicos → Mantenimiento de Equipos Médicos
$M.Add(111, @{ existingId = 49 })   # Equipos Industriales → Mantenimiento de Equipos Industriales
$M.Add(112, @{ existingId = 48 })   # Infraestructura → Mantención de Infraestructuras
$M.Add(113, @{ newName = "Departamento de Servicios Generales" })
$M.Add(114, @{ newName = "Operaciones" })
$M.Add(115, @{ newName = "Servicios Básicos" })
$M.Add(116, @{ existingId = 44 })   # Inventario de Bienes → Inventario
$M.Add(117, @{ existingId = 40 })   # Subdirector/a Gestión y Desarrollo Personas
$M.Add(118, @{ newName = "Gestión Procesos y Mejora Continua" })
$M.Add(119, @{ newName = "Departamento Gestión de las Personas" })
$M.Add(120, @{ existingId = 62 })   # Remuneraciones
$M.Add(121, @{ existingId = 42 })   # Honorarios
$M.Add(122, @{ newName = "Administración de Personas" })
$M.Add(123, @{ newName = "Control de la Jornada" })
$M.Add(124, @{ existingId = 22 })   # Departamento Calidad de Vida → Calidad de Vida
$M.Add(125, @{ existingId = 56 })   # Prevención de Riesgos
$M.Add(126, @{ newName = "Sala Cuna y Cuidados Infantiles" })
$M.Add(127, @{ existingId = 20 })   # Bienestar al Personal → Bienestar
$M.Add(128, @{ newName = "Departamento Gestión del Cambio" })
$M.Add(129, @{ existingId = 83 })   # Desarrollo Organizacional → Unidad de Desarrollo Organizacional
$M.Add(130, @{ existingId = 24 })   # Capacitación
$M.Add(131, @{ existingId = 59 })   # Reclutamiento y Selección

# ─────────────────────────────────────────────────────────────────────
# Leer CSV
# ─────────────────────────────────────────────────────────────────────
$rawCsv = Get-Content "C:\Users\Arturo Moya Gonzalez\Desktop\jefaturas.csv" -Encoding UTF8
$rows = @()
for ($i = 1; $i -lt $rawCsv.Count; $i++) {
    $line = $rawCsv[$i]
    if ($line -match '^;+$' -or [string]::IsNullOrWhiteSpace($line)) { continue }
    $cols = $line -split ';'
    $servicio = $cols[0].Trim()
    if ([string]::IsNullOrWhiteSpace($servicio)) { continue }
    $rows += [PSCustomObject]@{
        Num     = $i + 1   # línea del archivo (header = línea 1)
        Servicio = $servicio
        Titular  = if ($cols.Count -gt 1) { $cols[1].Trim() } else { '' }
        Sub1     = if ($cols.Count -gt 3) { $cols[3].Trim() } else { '' }
        Sub2     = if ($cols.Count -gt 5) { $cols[5].Trim() } else { '' }
    }
}
Write-Host "CSV cargado: $($rows.Count) filas de datos"

# ─────────────────────────────────────────────────────────────────────
# Migración principal
# ─────────────────────────────────────────────────────────────────────
$inserted  = 0
$updated   = 0
$skipped   = 0
$newDeps   = 0
$errors    = 0

foreach ($row in $rows) {
    $lineNum = $row.Num

    if (-not $M.ContainsKey($lineNum)) {
        Write-Host "  [WARN] linea ${lineNum} sin mapping: '$($row.Servicio)'"
        $skipped++
        continue
    }

    $mapping = $M[$lineNum]

    if ($mapping.ContainsKey('skipRow')) {
        Write-Host "  [SKIP] linea ${lineNum}: '$($row.Servicio)'"
        $skipped++
        continue
    }

    # Resolver dep_id
    [int]$depId = 0
    if ($mapping.ContainsKey('existingId')) {
        $depId = [int]$mapping['existingId']
    } else {
        $depId = Ensure-Dep $mapping['newName']
        $newDeps++
    }

    # Resolver titular / subrogantes
    $nomTit  = $row.Titular.Trim()
    $nomSub1 = $row.Sub1.Trim()
    $nomSub2 = $row.Sub2.Trim()

    # Caso especial: Licitaciones sin titular (línea 102)
    if ([string]::IsNullOrWhiteSpace($nomTit)) {
        if (-not [string]::IsNullOrWhiteSpace($nomSub1)) {
            $nomTit  = $nomSub1
            $nomSub1 = ''
            $nomSub2 = ''
            Write-Host "  [INFO] linea ${lineNum} '$($row.Servicio)': sub1->titular = '$nomTit'"
        } else {
            Write-Host "  [SKIP] linea ${lineNum} '$($row.Servicio)': sin titular ni sub1"
            $skipped++
            continue
        }
    }

    $cargoTit = Get-Cargo $row.Servicio

    # Verificar si existe para contar insert vs update
    $preExists = Exec-Scalar "SELECT COUNT(*) FROM jefatura WHERE id_dependencia = $depId"

    try {
        Upsert-Jefatura -depId $depId -nomTit $nomTit -cargoTit $cargoTit -nomSub1 $nomSub1 -nomSub2 $nomSub2

        if ([int]$preExists -gt 0) {
            Write-Host "  [UPD]  dep=$depId '$($row.Servicio)' T='$nomTit'"
            $updated++
        } else {
            Write-Host "  [INS]  dep=$depId '$($row.Servicio)' T='$nomTit'"
            $inserted++
        }
    } catch {
        Write-Host "  [ERR]  dep=$depId '$($row.Servicio)': $($_.Exception.Message)"
        $errors++
    }
}

$conn.Close()

Write-Host ""
Write-Host "============================================="
Write-Host "RESUMEN DE MIGRACION"
Write-Host "============================================="
Write-Host "  Nuevas dependencias creadas : $newDeps"
Write-Host "  Jefaturas INSERTADAS        : $inserted"
Write-Host "  Jefaturas ACTUALIZADAS      : $updated"
Write-Host "  Filas omitidas              : $skipped"
Write-Host "  Errores                     : $errors"
Write-Host "  TOTAL procesado             : $($inserted + $updated + $skipped)"
Write-Host "============================================="
