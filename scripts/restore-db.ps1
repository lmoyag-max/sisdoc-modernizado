# SISDOC — Restaurar base de datos SISDOC desde backup
# Uso: .\scripts\restore-db.ps1
# Ejecutar cuando el contenedor SQL Server es nuevo o la BD se perdió

Write-Host "=======================================" -ForegroundColor Cyan
Write-Host "  SISDOC — Restaurar base de datos" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan

# Verificar que el contenedor esté corriendo
$container = docker ps --filter "name=sisdoc_sqlserver" --format "{{.Status}}" 2>&1
if (-not $container -or $container -notlike "*Up*") {
    Write-Host "ERROR: Contenedor sisdoc_sqlserver no está corriendo." -ForegroundColor Red
    Write-Host "Inicialo con: docker compose up -d sqlserver" -ForegroundColor Yellow
    exit 1
}

Write-Host "`nEsperando que SQL Server esté listo..." -ForegroundColor Yellow
$maxRetries = 20
for ($i = 1; $i -le $maxRetries; $i++) {
    $result = docker exec sisdoc_sqlserver bash -c "/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'Adminhuap2026!' -C -Q 'SELECT 1'" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "SQL Server listo." -ForegroundColor Green
        break
    }
    Write-Host "  Intento $i/$maxRetries..."
    Start-Sleep -Seconds 5
}

# Verificar si SISDOC ya existe
Write-Host "`nVerificando si SISDOC existe..." -ForegroundColor Yellow
$dbCheck = docker exec sisdoc_sqlserver bash -c "/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'Adminhuap2026!' -C -W -h-1 -Q `"SET NOCOUNT ON; SELECT name FROM sys.databases WHERE name='SISDOC'`"" 2>&1
if ($dbCheck -match "SISDOC") {
    Write-Host "La base de datos SISDOC ya existe." -ForegroundColor Green
    Write-Host "Para forzar restauración, agrega -Force al comando." -ForegroundColor Yellow
    if ($args -notcontains "-Force") { exit 0 }
}

# Restaurar
Write-Host "`nRestaurando base de datos SISDOC..." -ForegroundColor Yellow
$restore = docker exec sisdoc_sqlserver bash -c @"
/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P 'Adminhuap2026!' -C -Q "
RESTORE DATABASE [SISDOC]
FROM DISK = N'/var/opt/mssql/backup/respaldo anterior.bak'
WITH MOVE 'sisdoc_Data' TO '/var/opt/mssql/data/SISDOC.mdf',
     MOVE 'sisdoc_Log'  TO '/var/opt/mssql/data/SISDOC_log.ldf',
     REPLACE, STATS = 10;
SELECT 'OK' AS resultado;
"
"@

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
