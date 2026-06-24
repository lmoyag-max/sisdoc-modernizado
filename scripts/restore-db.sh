#!/bin/bash
# DOC360 — Restaurar la base de datos SISDOC desde un backup .bak
# Uso: ./scripts/restore-db.sh [/var/opt/mssql/backup/archivo.bak] [--force]
# El .bak debe estar dentro de ./database (montado en /var/opt/mssql/backup)

set -euo pipefail
cd "$(dirname "$0")/.."

CONTAINER_NAME="${CONTAINER_NAME:-sisdoc_preprod_sqlserver}"
BACKUP_PATH="${1:-/var/opt/mssql/backup/SISDOC.bak}"
FORCE=false
[ "${2:-}" = "--force" ] && FORCE=true

SA_PASS="${MSSQL_SA_PASSWORD:-}"
if [ -z "$SA_PASS" ] && [ -f backend/.env ]; then
  SA_PASS=$(grep -m1 '^MSSQL_SA_PASSWORD=' backend/.env | cut -d= -f2- || true)
  [ -z "$SA_PASS" ] && SA_PASS=$(grep -m1 '^DB_PASSWORD=' backend/.env | cut -d= -f2- || true)
fi
if [ -z "$SA_PASS" ]; then
  echo "[ERROR] No se pudo obtener la contraseña SA. Exporta MSSQL_SA_PASSWORD." >&2
  exit 1
fi

SQLCMD="docker exec $CONTAINER_NAME /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P $SA_PASS -C"

echo "Esperando que SQL Server esté listo..."
for i in $(seq 1 20); do
  if $SQLCMD -Q "SELECT 1" > /dev/null 2>&1; then
    echo "SQL Server listo."
    break
  fi
  echo "  Intento $i/20..."
  sleep 5
done

EXISTE=$($SQLCMD -W -h-1 -Q "SET NOCOUNT ON; SELECT name FROM sys.databases WHERE name='SISDOC'" 2>/dev/null | grep -c SISDOC || true)
if [ "$EXISTE" != "0" ] && [ "$FORCE" != "true" ]; then
  echo "La base de datos SISDOC ya existe. Usa --force para sobrescribir."
  exit 0
fi

echo "Restaurando SISDOC desde ${BACKUP_PATH}..."
$SQLCMD -Q "
RESTORE DATABASE [SISDOC]
FROM DISK = N'${BACKUP_PATH}'
WITH MOVE 'sisdoc_Data' TO '/var/opt/mssql/data/SISDOC.mdf',
     MOVE 'sisdoc_Log'  TO '/var/opt/mssql/data/SISDOC_log.ldf',
     REPLACE, STATS = 10;
"

echo "[OK] Base de datos SISDOC restaurada. Reinicia el backend si estaba corriendo."
