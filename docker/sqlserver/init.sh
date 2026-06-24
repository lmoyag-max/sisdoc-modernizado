#!/bin/bash
# Script de inicialización opcional de SQL Server — restaura SISDOC desde un
# backup .bak si el contenedor arranca con la base vacía.
# NO se invoca automáticamente en docker-compose.preprod.yml: el flujo de
# preproducción inicializa el esquema ejecutando database/scripts/*.sql
# (ver README_PREPROD.md), no restaurando un backup con datos reales.
# Útil solo si Operaciones decide partir desde un respaldo conocido.
#
# Variables requeridas (no hardcodear contraseñas en este archivo):
#   SA_PASS    contraseña del usuario sa (= MSSQL_SA_PASSWORD del compose)
#   BACKUP_PATH ruta del .bak dentro del contenedor (montado en /var/opt/mssql/backup)

SQLCMD="/opt/mssql-tools18/bin/sqlcmd"
SA_PASS="${SA_PASS:?Debe definir la variable de entorno SA_PASS antes de ejecutar este script}"
BACKUP_PATH="${BACKUP_PATH:-/var/opt/mssql/backup/SISDOC.bak}"

echo "[init] Esperando que SQL Server esté listo..."
for i in {1..30}; do
  $SQLCMD -S localhost -U sa -P "$SA_PASS" -C -Q "SELECT 1" > /dev/null 2>&1
  if [ $? -eq 0 ]; then
    echo "[init] SQL Server listo."
    break
  fi
  echo "[init] Intento $i/30 — esperando 3s..."
  sleep 3
done

# Verificar si SISDOC ya existe
DB_EXISTS=$($SQLCMD -S localhost -U sa -P "$SA_PASS" -C -W -s'|' \
  -Q "SET NOCOUNT ON; SELECT COUNT(*) FROM sys.databases WHERE name='SISDOC'" 2>/dev/null | grep -E "^[0-9]")

if [ "$DB_EXISTS" = "0" ] || [ -z "$DB_EXISTS" ]; then
  echo "[init] Base de datos SISDOC no encontrada. Restaurando desde $BACKUP_PATH..."
  $SQLCMD -S localhost -U sa -P "$SA_PASS" -C -Q "
    RESTORE DATABASE [SISDOC]
    FROM DISK = N'$BACKUP_PATH'
    WITH MOVE 'sisdoc_Data' TO '/var/opt/mssql/data/SISDOC.mdf',
         MOVE 'sisdoc_Log'  TO '/var/opt/mssql/data/SISDOC_log.ldf',
         REPLACE;
  " 2>&1
  echo "[init] Restauración completada."
else
  echo "[init] Base de datos SISDOC ya existe (OK)."
fi
