#!/bin/bash
# DOC360 — Levantar el stack completo de preproducción con Docker
# Uso: ./scripts/start-preprod.sh

set -euo pipefail
cd "$(dirname "$0")/.."

if [ ! -f backend/.env ]; then
  echo "[ERROR] Falta backend/.env. Copia backend/.env.example y completa los valores reales." >&2
  exit 1
fi
if [ ! -f .env ]; then
  echo "[ERROR] Falta .env en la raíz (MSSQL_SA_PASSWORD). Copia .env.example y completa el valor." >&2
  exit 1
fi

mkdir -p uploads logs

echo "Construyendo y levantando contenedores..."
docker compose -f docker-compose.preprod.yml up -d --build

echo
echo "Esperando a que los servicios reporten healthy..."
for i in $(seq 1 30); do
  ESTADO=$(docker compose -f docker-compose.preprod.yml ps --format json 2>/dev/null | grep -c '"Health":"healthy"' || true)
  if [ "$ESTADO" -ge 2 ]; then
    echo "[OK] sqlserver y backend están healthy."
    break
  fi
  sleep 5
done

echo
echo "Estado de los contenedores:"
docker compose -f docker-compose.preprod.yml ps

echo
echo "Sistema disponible en: http://localhost"
echo "Health check backend:  http://localhost:3001/api/health"
echo "Logs:                  docker compose -f docker-compose.preprod.yml logs -f"
