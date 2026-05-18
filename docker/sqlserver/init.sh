#!/bin/bash
# Script de inicialización de SQL Server
# Se ejecuta al arrancar el contenedor y restaura SISDOC si no existe

SQLCMD="/opt/mssql-tools18/bin/sqlcmd"
SA_PASS="Adminhuap2026!"
BACKUP="/var/opt/mssql/backup/respaldo anterior.bak"

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
  echo "[init] Base de datos SISDOC no encontrada. Restaurando desde backup..."
  $SQLCMD -S localhost -U sa -P "$SA_PASS" -C -Q "
    RESTORE DATABASE [SISDOC]
    FROM DISK = N'/var/opt/mssql/backup/respaldo anterior.bak'
    WITH MOVE 'sisdoc_Data' TO '/var/opt/mssql/data/SISDOC.mdf',
         MOVE 'sisdoc_Log'  TO '/var/opt/mssql/data/SISDOC_log.ldf',
         REPLACE;
  " 2>&1
  echo "[init] Restauración completada."
else
  echo "[init] Base de datos SISDOC ya existe (OK)."
fi
