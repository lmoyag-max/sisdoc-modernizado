# DOC360 — Levantar el stack completo de preproducción con Docker
# Uso: .\scripts\start-preprod.ps1

$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

if (-not (Test-Path "backend\.env")) {
    Write-Host "[ERROR] Falta backend\.env. Copia backend\.env.example y completa los valores reales." -ForegroundColor Red
    exit 1
}
if (-not (Test-Path ".env")) {
    Write-Host "[ERROR] Falta .env en la raíz (MSSQL_SA_PASSWORD). Copia .env.example y completa el valor." -ForegroundColor Red
    exit 1
}

New-Item -ItemType Directory -Force -Path "uploads" | Out-Null
New-Item -ItemType Directory -Force -Path "logs" | Out-Null

Write-Host "Construyendo y levantando contenedores..." -ForegroundColor Cyan
docker compose -f docker-compose.preprod.yml up -d --build

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] docker compose up falló." -ForegroundColor Red
    exit 1
}

Write-Host "`nEsperando a que los servicios reporten healthy..." -ForegroundColor Yellow
Start-Sleep -Seconds 20

Write-Host "`nEstado de los contenedores:" -ForegroundColor Cyan
docker compose -f docker-compose.preprod.yml ps

Write-Host "`nSistema disponible en: http://localhost" -ForegroundColor Green
Write-Host "Health check backend:  http://localhost:3001/api/health" -ForegroundColor Green
Write-Host "Logs:                  docker compose -f docker-compose.preprod.yml logs -f" -ForegroundColor Green
