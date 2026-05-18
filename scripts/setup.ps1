# SISDOC — Script de setup inicial
# Uso: .\scripts\setup.ps1

Write-Host "=======================================" -ForegroundColor Cyan
Write-Host "  SISDOC v2 — Setup Inicial" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan

# Backend deps
Write-Host "`n[1/4] Instalando dependencias backend..." -ForegroundColor Yellow
Set-Location backend
npm install
Write-Host "Backend deps OK" -ForegroundColor Green
Set-Location ..

# Frontend deps
Write-Host "`n[2/4] Instalando dependencias frontend..." -ForegroundColor Yellow
Set-Location frontend
npm install
Write-Host "Frontend deps OK" -ForegroundColor Green
Set-Location ..

# SQL Server
Write-Host "`n[3/4] Iniciando SQL Server en Docker..." -ForegroundColor Yellow
docker compose up -d sqlserver
Write-Host "Esperando a que SQL Server esté listo (30s)..." -ForegroundColor Yellow
Start-Sleep -Seconds 30
Write-Host "SQL Server OK" -ForegroundColor Green

# Prisma
Write-Host "`n[4/4] Configurando Prisma..." -ForegroundColor Yellow
Set-Location backend
$dbUrl = Get-Content .env | Select-String "DATABASE_URL" | ForEach-Object { $_ -replace 'DATABASE_URL=', '' -replace '"', '' }
Write-Host "Generando cliente Prisma..."
npm run prisma:generate
Write-Host "NOTA: Para sincronizar schema con DB existente, ejecuta: npm run prisma:pull" -ForegroundColor Yellow
Set-Location ..

Write-Host "`n=======================================" -ForegroundColor Cyan
Write-Host "  Setup completado!" -ForegroundColor Green
Write-Host "`n  Para iniciar el sistema:" -ForegroundColor White
Write-Host "  .\scripts\dev.ps1" -ForegroundColor White
Write-Host "=======================================" -ForegroundColor Cyan
