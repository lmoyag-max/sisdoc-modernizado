#!/bin/bash
# DOC360 — Backup de la base de datos SISDOC
# Uso: ./scripts/backup-db.sh
# Requiere: MSSQL_SA_PASSWORD en el entorno, o MSSQL_SA_PASSWORD/DB_PASSWORD en backend/.env

set -euo pipefail
cd "$(dirname "$0")/.."

CONTAINER_NAME="${CONTAINER_NAME:-sisdoc_preprod_sqlserver}"
DEST_DIR="${DEST_DIR:-./database/backups}"

SA_PASS="${MSSQL_SA_PASSWORD:-}"
if [ -z "$SA_PASS" ] && [ -f backend/.env ]; then
  SA_PASS=$(grep -m1 '^MSSQL_SA_PASSWORD=' backend/.env | cut -d= -f2- || true)
  [ -z "$SA_PASS" ] && SA_PASS=$(grep -m1 '^DB_PASSWORD=' backend/.env | cut -d= -f2- || true)
fi
if [ -z "$SA_PASS" ]; then
  echo "[ERROR] No se pudo obtener la contraseña SA. Exporta MSSQL_SA_PASSWORD." >&2
  exit 1
fi

if [ "$(docker inspect -f '{{.State.Running}}' "$CONTAINER_NAME" 2>/dev/null)" != "true" ]; then
  echo "[ERROR] El contenedor '$CONTAINER_NAME' no está corriendo." >&2
  exit 1
fi

FECHA=$(date +%Y%m%d_%H%M%S)
ARCHIVO="SISDOC_backup_${FECHA}.bak"
RUTA_CONTENEDOR="/var/opt/mssql/backup/${ARCHIVO}"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Ejecutando BACKUP DATABASE en SQL Server..."
docker exec "$CONTAINER_NAME" /opt/mssql-tools18/bin/sqlcmd \
  -S localhost -U sa -P "$SA_PASS" -C \
  -Q "BACKUP DATABASE [SISDOC] TO DISK='${RUTA_CONTENEDOR}' WITH FORMAT, COMPRESSION, CHECKSUM, STATS=10;"

mkdir -p "$DEST_DIR"
echo "Copiando backup al host..."
docker cp "${CONTAINER_NAME}:${RUTA_CONTENEDOR}" "${DEST_DIR}/${ARCHIVO}"
docker exec "$CONTAINER_NAME" rm -f "$RUTA_CONTENEDOR" || true

TAMANO=$(du -h "${DEST_DIR}/${ARCHIVO}" | cut -f1)
echo "[OK] Backup guardado: ${DEST_DIR}/${ARCHIVO} (${TAMANO})"

# Limpieza de backups con más de 30 días
find "$DEST_DIR" -name 'SISDOC_backup_*.bak' -mtime +30 -print -delete
