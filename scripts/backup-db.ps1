# backup-db.ps1 — Backup semanal automatico de la BD SISDOC
# Uso manual:  .\scripts\backup-db.ps1
# Programado:  Task Scheduler — ver instrucciones al final del script

param(
    [string]$DestinoDirHost = "C:\sisdoc-modernizado\database\backups",
    [string]$ContainerName  = "sisdoc_sqlserver",
    [string]$SaPassword     = $env:MSSQL_SA_PASSWORD
)

$fecha     = Get-Date -Format "yyyyMMdd_HHmmss"
$archivo   = "SISDOC_backup_$fecha.bak"
$rutaContenedor = "/var/opt/mssql/backup/$archivo"

Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] Iniciando backup SISDOC..." -ForegroundColor Cyan

# Leer SA password del .env si no viene por parametro ni variable de entorno
if (-not $SaPassword) {
    $envFile = "C:\sisdoc-modernizado\backend\.env"
    if (Test-Path $envFile) {
        $match = Select-String -Path $envFile -Pattern '^MSSQL_SA_PASSWORD=(.+)$'
        if (-not $match) {
            # Intentar con DB_PASSWORD como fallback (misma en desarrollo)
            $match = Select-String -Path $envFile -Pattern '^DB_PASSWORD=(.+)$'
        }
        if ($match) {
            $SaPassword = $match.Matches[0].Groups[1].Value.Trim()
        }
    }
}

if (-not $SaPassword) {
    Write-Host "[ERROR] No se pudo obtener la contrasena SA. Pasa -SaPassword o define MSSQL_SA_PASSWORD." -ForegroundColor Red
    exit 1
}

# Verificar que el contenedor esta corriendo
$running = docker inspect --format '{{.State.Running}}' $ContainerName 2>$null
if ($running -ne 'true') {
    Write-Host "[ERROR] El contenedor '$ContainerName' no esta corriendo." -ForegroundColor Red
    exit 1
}

# Ejecutar backup dentro del contenedor
Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Ejecutando BACKUP DATABASE en SQL Server..."
$query = "BACKUP DATABASE [SISDOC] TO DISK='$rutaContenedor' WITH FORMAT, COMPRESSION, CHECKSUM, STATS=10;"
docker exec $ContainerName /opt/mssql-tools18/bin/sqlcmd `
    -S localhost -U sa -P $SaPassword -C -Q $query

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] El backup fallo (exit code $LASTEXITCODE)." -ForegroundColor Red
    exit 1
}

Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Backup SQL completado. Copiando al host..."

# Crear directorio de destino si no existe
if (-not (Test-Path $DestinoDirHost)) {
    New-Item -ItemType Directory -Force -Path $DestinoDirHost | Out-Null
}

# Copiar desde el contenedor al host
docker cp "${ContainerName}:${rutaContenedor}" "$DestinoDirHost\$archivo"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] No se pudo copiar el backup al host." -ForegroundColor Red
    exit 1
}

$tamano = [math]::Round((Get-Item "$DestinoDirHost\$archivo").Length / 1MB, 1)
Write-Host "[OK] Backup guardado: $DestinoDirHost\$archivo ($tamano MB)" -ForegroundColor Green

# Limpiar backups con mas de 30 dias
Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Limpiando backups con mas de 30 dias..."
$antiguos = Get-ChildItem "$DestinoDirHost\SISDOC_backup_*.bak" |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) }
foreach ($f in $antiguos) {
    Remove-Item $f.FullName -Force
    Write-Host "  Eliminado: $($f.Name)"
}

# Tambien limpiar el backup del contenedor (el volumen es para backups temporales)
docker exec $ContainerName rm -f $rutaContenedor 2>$null

Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] Backup completado exitosamente." -ForegroundColor Green

<#
PROGRAMAR EN TASK SCHEDULER (ejecutar una sola vez como Administrador):

$action  = New-ScheduledTaskAction -Execute "powershell.exe" `
    -Argument "-NonInteractive -ExecutionPolicy Bypass -File C:\sisdoc-modernizado\scripts\backup-db.ps1"
$trigger = New-ScheduledTaskTrigger -Weekly -DaysOfWeek Sunday -At "02:00AM"
$settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -RunOnlyIfNetworkAvailable:$false
Register-ScheduledTask -TaskName "SISDOC - Backup Semanal BD" `
    -Action $action -Trigger $trigger -Settings $settings `
    -RunLevel Highest -Force
#>
