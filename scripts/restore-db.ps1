# SISDOC — Restaurar base de datos SISDOC desde backup
# Uso: .\scripts\restore-db.ps1
# Ejecutar cuando el contenedor SQL Server es nuevo o la BD se perdió

param(
    [string]$ContainerName = "sisdoc_sqlserver",
    [string]$SaPassword    = $env:MSSQL_SA_PASSWORD,
    [string]$BackupPath    = "/var/opt/mssql/backup/respaldo anterior.bak",
    [switch]$Force
)

Write-Host "=======================================" -ForegroundColor Cyan
Write-Host "  SISDOC — Restaurar base de datos" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan

# Leer SA password del .env si no viene por parámetro ni variable de entorno
if (-not $SaPassword) {
    $envFile = Join-Path $PSScriptRoot "..\backend\.env"
    if (Test-Path $envFile) {
        $match = Select-String -Path $envFile -Pattern '^MSSQL_SA_PASSWORD=(.+)$'
        if (-not $match) { $match = Select-String -Path $envFile -Pattern '^DB_PASSWORD=(.+)$' }
        if ($match) { $SaPassword = $match.Matches[0].Groups[1].Value.Trim() }
    }
}
if (-not $SaPassword) {
    Write-Host "ERROR: No se pudo obtener la contraseña SA. Pasa -SaPassword o define `$env:MSSQL_SA_PASSWORD." -ForegroundColor Red
    exit 1
}

# Verificar que el contenedor esté corriendo
$container = docker ps --filter "name=$ContainerName" --format "{{.Status}}" 2>&1
if (-not $container -or $container -notlike "*Up*") {
    Write-Host "ERROR: Contenedor $ContainerName no está corriendo." -ForegroundColor Red
    Write-Host "Inicialo con: docker compose up -d sqlserver" -ForegroundColor Yellow
    exit 1
}

Write-Host "`nEsperando que SQL Server esté listo..." -ForegroundColor Yellow
$maxRetries = 20
for ($i = 1; $i -le $maxRetries; $i++) {
    docker exec $ContainerName bash -c "/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P '$SaPassword' -C -Q 'SELECT 1'" 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "SQL Server listo." -ForegroundColor Green
        break
    }
    Write-Host "  Intento $i/$maxRetries..."
    Start-Sleep -Seconds 5
}

# Verificar si SISDOC ya existe
Write-Host "`nVerificando si SISDOC existe..." -ForegroundColor Yellow
$dbCheck = docker exec $ContainerName bash -c "/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P '$SaPassword' -C -W -h-1 -Q `"SET NOCOUNT ON; SELECT name FROM sys.databases WHERE name='SISDOC'`"" 2>&1
if ($dbCheck -match "SISDOC") {
    Write-Host "La base de datos SISDOC ya existe." -ForegroundColor Green
    Write-Host "Para forzar restauración, agrega -Force al comando." -ForegroundColor Yellow
    if (-not $Force) { exit 0 }
}

# Restaurar
Write-Host "`nRestaurando base de datos SISDOC desde $BackupPath..." -ForegroundColor Yellow
$restore = docker exec $ContainerName bash -c "/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P '$SaPassword' -C -Q `"RESTORE DATABASE [SISDOC] FROM DISK = N'$BackupPath' WITH MOVE 'sisdoc_Data' TO '/var/opt/mssql/data/SISDOC.mdf', MOVE 'sisdoc_Log' TO '/var/opt/mssql/data/SISDOC_log.ldf', REPLACE, STATS = 10; SELECT 'OK' AS resultado;`"" 2>&1

if ($restore -match "RESTORE DATABASE successfully" -or $restore -match "OK") {
    Write-Host "Base de datos SISDOC restaurada exitosamente." -ForegroundColor Green
} else {
    Write-Host "Error en la restauración:" -ForegroundColor Red
    Write-Host $restore
    exit 1
}

Write-Host "`n=======================================" -ForegroundColor Cyan
Write-Host "  BD restaurada. Sistema listo." -ForegroundColor Green
Write-Host "  Reinicia el backend si estaba corriendo." -ForegroundColor White
Write-Host "=======================================" -ForegroundColor Cyan
