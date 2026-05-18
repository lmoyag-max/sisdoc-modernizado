\# Proyecto SISDOC Modernizado



Este proyecto busca modernizar el sistema legacy SISDOC sin afectar producción.



\## Sistema original



\- Windows Server 2003

\- IIS / XAMPP

\- ASP clásico / posible PHP legacy

\- SQL Server 2005

\- Base de datos clonada actualmente en Docker con SQL Server moderno



\## Objetivo



Analizar el sistema legacy ubicado en `/legacy/sisdoc`, entender sus módulos, flujos, tablas, procedimientos almacenados y reglas de negocio, para reconstruir una nueva versión moderna.



\## Nuevo stack propuesto



\- Backend: Node.js

\- Frontend: React

\- Base de datos: SQL Server

\- API REST

\- Autenticación moderna

\- Trazabilidad documental



\## Reglas



1\. No modificar directamente el código legacy.

2\. Analizar primero la estructura de carpetas.

3\. Identificar archivos principales.

4\. Detectar conexiones a base de datos.

5\. Identificar módulos funcionales.

6\. Identificar consultas SQL embebidas.

7\. Crear código nuevo solo dentro de `/backend` y `/frontend`.

8\. Documentar hallazgos en `/docs`.

