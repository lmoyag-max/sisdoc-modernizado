# 01 — Estructura del Sistema SISDOC

**Fecha de análisis:** 2026-05-18  
**Analista:** Claude Sonnet 4.6  
**Versión legacy:** Sistema de gestión documental PHP + SQL Server 2005

---

## 1. Árbol de directorios raíz

```
c:\sisdoc-modernizado\
├── legacy/                  ← Sistema original (NO MODIFICAR)
│   ├── sisdoc/              ← Raíz PHP del sistema
│   │   ├── adm/             ← Módulo administración de usuarios
│   │   ├── bienestar/       ← Módulo beneficios
│   │   ├── gabinete/        ← Módulo informes/gabinete
│   │   ├── sisdoc_alertas/  ← Sistema de alertas
│   │   ├── frame_variables/ ← Variables globales y conexión
│   │   ├── imagenes/        ← Assets visuales
│   │   ├── *.php            ← Módulo core (documentos, trámites)
│   │   └── *.asp            ← Fragmentos ASP clásico
├── backend/                 ← API Node.js (nuevo)
├── frontend/                ← React app (nuevo)
├── docs/                    ← Documentación de análisis
│   ├── analisis/            ← Análisis previo
│   └── analisis-claude/     ← Este análisis
├── database/                ← Backup SQL Server
├── scripts/                 ← Scripts utilitarios
└── docker-compose.yml       ← SQL Server 2022 en Docker
```

---

## 2. Módulos funcionales detectados en /legacy

### 2.1 Módulo Core (raíz `/legacy/sisdoc/`)

| Archivo | Función |
|---|---|
| `autentificacion.php` | Formulario de login |
| `login.php` | Procesamiento de autenticación |
| `conexion_bd.php` | Conexión principal SQL Server |
| `carga_tablas.php` | Carga de tablas maestras en sesión |
| `ingreso_docto2.php` | Ingreso de documentos con derivación |
| `ingreso_docto1.php` | Ingreso de documentos sin archivo físico |
| `derivar_con_docto.php` | Derivación de documentos |
| `derivar_multiple_con_docto.php` | Derivaciones múltiples simultáneas |
| `responder_con_docto.php` | Respuesta a derivaciones |
| `busca_docto.php` | Búsqueda de documentos |
| `tramites.php` | Seguimiento de trámites |
| `despachar.php` | Cierre/despacho de documentos |
| `modifica_cabecera2.php` | Edición de encabezados |
| `historial_docto.php` | Visualización de historial |
| `top.asp` | Cabecera de navegación ASP |

### 2.2 Módulo Administración (`/adm/`)

| Archivo | Función |
|---|---|
| `adm_ingreso_usuarios.php` | Formulario de creación de usuarios |
| `adm_graba_usuario.php` | Grabación en BD (doble: corporativo + SISDOC) |
| `adm_usuario_acceso.php` | Asignación de accesos por dependencia |
| `adm_usuario_dependencia.php` | Relación usuario-dependencia |
| `tramites.php` | Vista de trámites desde administración |

### 2.3 Módulo Oficina de Partes (`/legacy/sisdoc/`)

| Archivo | Función |
|---|---|
| `ingreso_ofpartes_k.php` | Ingreso vía oficina de partes |
| `modifica_docto_ofpartes.php` | Modificación de documentos |
| `timbraje_ofpartes.php` | Numeración y timbraje oficial |

### 2.4 Módulo OIRS (Oficina de Información, Reclamos y Sugerencias)

| Archivo | Función |
|---|---|
| `busca_docto_oirs.php` | Búsquedas especializadas OIRS |
| `frame_menuvars_oirs.php` | Menú OIRS |

### 2.5 Módulo Electoral (`/el/`)

| Archivo | Función |
|---|---|
| `busca_docto_el.php` | Búsqueda electoral |
| `guardar_docto_el.php` | Grabación de datos electorales |
| `tramites_el.php` | Trámites electorales |

### 2.6 Módulo Bienestar (`/bienestar/`)

| Archivo | Función |
|---|---|
| `ingreso_beneficio.php` | Registro de beneficios |
| `ingreso_cargas.php` | Registro de cargas familiares |
| `respuesta.php` | Generación de respuestas |
| `imprime_respuesta.php` | Impresión de documentos |
| `verifica.php` | Validaciones |

### 2.7 Módulo Gabinete (`/gabinete/`)

| Archivo | Función |
|---|---|
| `informe_doc.php` | Informes por documento |
| `busca_doctogab.php` | Búsqueda desde gabinete |
| `frame_menugabinete.php` | Menú de gabinete |

### 2.8 Sistema de Alertas (`/sisdoc_alertas/`)

| Archivo | Función |
|---|---|
| `alertas.php` | Motor de alertas |
| `SP_busca_alertas_vigente.sql` | Procedimiento almacenado de alertas |

---

## 3. Tecnologías legacy detectadas

### 3.1 Stack original

| Capa | Tecnología | Estado |
|---|---|---|
| Servidor web | IIS / Apache (XAMPP) | Obsoleto |
| Lenguaje backend | PHP 5.x | Obsoleto (EOL 2018) |
| Extensión BD | `mssql_*` de PHP | Obsoleto (removido en PHP 7+) |
| Fragmentos frontend | ASP Clásico | Obsoleto |
| Base de datos | SQL Server 2005 | Obsoleto (EOL 2016) |
| JavaScript | Vanilla ES5 | Funcional pero antiguo |
| CSS | Inline + archivos básicos | Sin framework |
| Diálogos | `window.showModalDialog()` | Eliminado en Chrome 37+ |

### 3.2 Problemas de seguridad detectados

- SQL Injection: consultas concatenadas con variables directas sin sanitizar
- Sin hash de contraseñas: claves almacenadas en texto plano
- Sin CSRF protection
- Sin prepared statements
- Sesiones manejadas por formularios POST ocultos, no por PHP Sessions seguras
- Credenciales hardcodeadas en `conexion_bd.php`

---

## 4. Backend moderno actual (`/backend/`)

```
backend/
├── server.js        ← Entry point Express
├── package.json     ← Dependencias: express, cors, dotenv, mssql
├── .env             ← Credenciales (excluido de git)
└── node_modules/
```

**Endpoints operativos:**

| Endpoint | Método | Función |
|---|---|---|
| `/` | GET | Estado del sistema |
| `/api/health-db` | GET | Verificación de conexión BD |
| `/api/tablas` | GET | Lista de tablas del schema |
| `/api/procedimientos` | GET | Lista de stored procedures |
| `/api/buscar/:texto` | GET | Búsqueda en schema |

---

## 5. Frontend moderno actual (`/frontend/`)

```
frontend/
├── src/
│   ├── App.jsx      ← Componente principal (consume API backend)
│   ├── main.jsx     ← Entry point React
│   └── index.css    ← Estilos base
├── package.json     ← React + Vite + Axios
└── vite.config.js
```

**Estado:** Interfaz diagnóstica funcional, muestra tablas y estado de conexión BD.

---

## 6. Infraestructura Docker

```yaml
# docker-compose.yml
services:
  sqlserver:
    image: mcr.microsoft.com/mssql/server:2022-latest
    ports: 1433:1433
    volumes: ./database:/var/opt/mssql/backup
```

- Base de datos restaurada: `SISDOC`
- 47 tablas identificadas
- Stored Procedures migrados

---

## 7. Documentación previa en `/docs/analisis/`

| Archivo | Contenido |
|---|---|
| `01-mapa-base-datos.md` | Mapa de 47 tablas |
| `02-conexiones-legacy.txt` | Análisis de conexiones (10K líneas) |
| `03-login-legacy.txt` | Flujo de autenticación (25K líneas) |
| `04-procedimientos-usados-legacy.txt` | SP utilizados (5K líneas) |

---

## 8. Métricas del sistema legacy

- **Total de archivos PHP:** ~250+
- **Tablas en base de datos:** 47
- **Stored Procedures:** 6+ identificados
- **Módulos funcionales:** 8 principales
- **Versiones paralelas de archivos:** Múltiples (evidencia de desarrollo sin control de versiones)
- **Dependencias externas:** 0 (todo es código PHP nativo)
