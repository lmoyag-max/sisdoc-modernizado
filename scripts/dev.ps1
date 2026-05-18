# SISDOC — Script de desarrollo local
# Uso: .\scripts\dev.ps1

Write-Host "=======================================" -ForegroundColor Cyan
Write-Host "  SISDOC v2 — Entorno de Desarrollo" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan

# 1. Verificar Docker
Write-Host "`n[1/4] Verificando Docker..." -ForegroundColor Yellow
$dockerRunning = docker ps 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Docker no está corriendo. Abre Docker Desktop primero." -ForegroundColor Red
    exit 1
}
Write-Host "Docker OK" -ForegroundColor Green

# 2. SQL Server
Write-Host "`n[2/4] Iniciando SQL Server..." -ForegroundColor Yellow
docker compose up -d sqlserver
Start-Sleep -Seconds 5
Write-Host "SQL Server iniciado" -ForegroundColor Green

# 3. Backend
Write-Host "`n[3/4] Iniciando backend..." -ForegroundColor Yellow
Set-Location backend
if (-not (Test-Path "node_modules")) {
    Write-Host "Instalando dependencias backend..." -ForegroundColor Yellow
    npm install
}
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PWD'; npm run dev" -WindowStyle Normal
Set-Location ..
Write-Host "Backend corriendo en http://localhost:3001" -ForegroundColor Green

# 4. Frontend
Write-Host "`n[4/4] Iniciando frontend..." -ForegroundColor Yellow
Set-Location frontend
if (-not (Test-Path "node_modules")) {
    Write-Host "Instalando dependencias frontend..." -ForegroundColor Yellow
    npm install
}
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$PWD'; npm run dev" -WindowStyle Normal
Set-Location ..
Write-Host "Frontend corriendo en http://localhost:5173" -ForegroundColor Green

Write-Host "`n=======================================" -ForegroundColor Cyan
Write-Host "  Sistema iniciado correctamente!" -ForegroundColor Green
Write-Host "  Frontend:  http://localhost:5173" -ForegroundColor White
Write-Host "  Backend:   http://localhost:3001" -ForegroundColor White
Write-Host "  API Docs:  http://localhost:3001/api-docs" -ForegroundColor White
Write-Host "=======================================" -ForegroundColor Cyan
