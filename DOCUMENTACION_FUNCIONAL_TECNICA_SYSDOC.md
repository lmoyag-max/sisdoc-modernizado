# DOCUMENTACIÓN FUNCIONAL Y TÉCNICA — SISDOC / AssistDoc

**Sistema de Gestión Documental Hospitalaria — HUAP**
**Hospital Universitario Asociado de Puebla**
**Versión:** 2.0.0
**Fecha:** Mayo 2026

---

## Índice

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Concepto Funcional del Sistema](#2-concepto-funcional-del-sistema)
3. [Arquitectura General](#3-arquitectura-general)
4. [Reglas de Negocio](#4-reglas-de-negocio)
5. [Configuraciones del Sistema](#5-configuraciones-del-sistema)
6. [Roles y Permisos](#6-roles-y-permisos)
7. [Acciones por Módulo y Rol](#7-acciones-por-módulo-y-rol)
8. [Flujo Funcional Principal](#8-flujo-funcional-principal)
9. [Seguridad y Auditoría](#9-seguridad-y-auditoría)
10. [Estado Actual del Proyecto](#10-estado-actual-del-proyecto)
11. [Estructura del Repositorio](#11-estructura-del-repositorio)
12. [Recomendaciones Técnicas](#12-recomendaciones-técnicas)

---

## 1. Resumen Ejecutivo

### 1.1 ¿Qué es SISDOC / AssistDoc?

SISDOC (Sistema de Gestión Documental) es una plataforma web institucional de gestión documental hospitalaria desarrollada para el **Hospital Universitario Asociado de Puebla (HUAP)**. El sistema está orientado a digitalizar, centralizar y auditar el ciclo completo de vida de los documentos internos: desde su ingreso, derivación entre servicios, recepción, tramitación y cierre, hasta la consulta histórica y reportes.

El nombre comercial alternativo **AssistDoc** se utiliza como nombre de marca del producto modernizado.

### 1.2 ¿Qué problema resuelve?

El sistema legacy (SISDOC v1) operaba sobre tecnología obsoleta: **Windows Server 2003 / ASP clásico / SQL Server 2005**. Sus principales problemas eran:

- Ausencia de trazabilidad completa por documento
- Sin control de roles ni permisos granulares
- Sin derivación multi-destino
- Sin adjuntos digitales asociados al documento
- Sin alertas ni notificaciones por correo
- Sin auditoría de acciones
- Sin recuperación de contraseñas
- Interfaz no responsiva ni accesible

### 1.3 ¿Qué reemplaza del sistema legacy?

| Funcionalidad Legacy | Equivalente Moderno |
|---|---|
| Bandeja de entrada simple | Bandeja multi-destino con estados |
| Registro manual en papel/Excel | Ingreso digital con numeración automática |
| Sin derivaciones formales | Flujo de despacho/recepción/derivación/cierre |
| Sin archivos adjuntos | Upload de archivos con trazabilidad |
| Sin alertas | Scheduler de alertas por correo configurable |
| Sin roles definidos | Roles con módulos asignables por administrador |
| Base de datos SQL Server 2005 | SQL Server 2022 en Docker |

### 1.4 Valor que entrega al hospital

- **Trazabilidad garantizada:** cada acción sobre un documento genera un registro histórico inmutable en la tabla `tramite`.
- **Control de acceso por servicio:** cada funcionario ve solo los documentos de su servicio (o todos, si tiene el flag `todos_servicios=true`).
- **Notificaciones automáticas:** alertas por correo a los servicios con documentos pendientes, en horarios configurables.
- **Auditoría completa:** login, cambios de contraseña, exportaciones CSV y eliminaciones quedan registradas.
- **Continuidad con datos legacy:** los 19.373 expedientes y documentos históricos migrados están disponibles en el sistema moderno.

---

## 2. Concepto Funcional del Sistema

### 2.1 Gestión Documental

El sistema gestiona documentos institucionales que circulan entre las dependencias del hospital. Cada documento tiene una materia (asunto), un tipo documental, un estado y un historial de trámites. Los documentos pueden ser **digitales** (archivo en PDF/Word/Excel/imagen) o **físicos** (papel, registrado con soporte tipo `F`).

Los documentos no se eliminan por defecto — solo el administrador puede realizar una eliminación lógica (`soft delete`). Todo movimiento queda registrado en la tabla `tramite`.

### 2.2 Ingreso de Documentos (Nuevo Documento)

El módulo **Nuevo Documento** permite a cualquier usuario autenticado registrar un nuevo documento en el sistema. Al crear un documento:

- Se asigna automáticamente un **número interno** (`MAX(num_interno)+1`) y un número oficial.
- El **estado inicial** es siempre **Despachado (2)**.
- El **origen** (procedencia) se asigna automáticamente desde la dependencia del usuario autenticado.
- Se puede seleccionar **uno o múltiples servicios destino**.
- Se puede adjuntar **uno o más archivos** al momento de la creación.
- Campos especiales disponibles para Oficina de Partes:
  - **Soporte Físico** (`tipoSoporte='F'`): marca el documento como físico/papel.
  - **Reservado** (`reservado=true`): fuerza el destino a Dirección (id=32), sin posibilidad de alterar el destino desde el frontend.

### 2.3 Bandeja de Entrada

Muestra los documentos que han sido despachados hacia el servicio del usuario. Incluye filtros por estado y paginación. Permite recepcionar directamente desde la lista.

### 2.4 Documentos Enviados

Lista los documentos originados por el usuario o su servicio. Permite ver el estado actual de cada documento enviado.

### 2.5 Mis Trámites

Vista del historial de trámites del usuario. Permite ver los documentos en los que el usuario ha participado como origen, destino o ejecutor de alguna acción.

### 2.6 Trazabilidad

Visualización tipo **timeline** del recorrido completo de un documento: cada despacho, recepción, derivación, cierre y adjunto de archivo queda registrado con fecha, usuario y servicio. La trazabilidad es inmutable: no se editan registros, solo se agregan.

### 2.7 Búsqueda Global

Permite buscar documentos, trámites y funcionarios en una sola consulta, con filtrado por tipo (`documentos`, `tramites`, `funcionarios`, `todos`) y paginación.

### 2.8 Archivos Adjuntos

El módulo **Archivos** permite subir, listar, previsualizar y descargar archivos digitales asociados a documentos. También permite subir archivos sin asociarlos (comportamiento legacy). Cada adjunto genera un evento de trazabilidad (estado de trámite `7 = Archivo adjuntado`).

### 2.9 Reportes

Módulo de métricas con:
- **Dashboard:** totales de documentos, pendientes, trámites, gráfico por estado, actividad de los últimos 6 meses, distribución por tipo.
- **Actividad reciente:** últimos 15 movimientos del sistema.
- **Exportar CSV:** descarga con BOM para compatibilidad con Excel; filtra por rango de fechas y respeta el alcance por servicio del usuario.

### 2.10 Configuración

Módulo de administración del sistema con las siguientes secciones:

| Sección | Descripción |
|---|---|
| Identidad del sistema | Nombre del sistema y de la institución |
| Textos del login | Título, subtítulo, descripción y tarjetas de la pantalla de login |
| Logo institucional | Imagen PNG/JPG/JPEG/WEBP (máx 5 MB) |
| Fondo del login | Imagen de fondo de la pantalla de acceso |
| Reglas de carga | Extensiones permitidas, tamaño máximo por archivo y cuota total |
| Alertas | Activar/desactivar scheduler, configurar horarios de envío, envío manual |

---

## 3. Arquitectura General

### 3.1 Frontend

| Elemento | Tecnología |
|---|---|
| Framework | React 18 |
| Lenguaje | TypeScript 5.7 |
| Bundler | Vite 6 |
| Estilos | TailwindCSS 3 + shadcn/ui (Radix UI) |
| Data fetching | TanStack Query v5 |
| Estado global | Zustand v5 |
| Router | React Router v6 |
| Formularios | react-hook-form + zodResolver |
| Gráficos | Recharts |
| Notificaciones | Sonner (toasts) |
| HTTP | Axios con interceptores de auto-refresh JWT |
| Iconos | Lucide React |

El frontend corre en **http://localhost:5173** en desarrollo. La SPA protege todas las rutas internas con `ProtectedRoute` (redirige a `/login` si no hay token) y con `ModuleGuard` (redirige si el usuario no tiene el módulo asignado en su JWT).

### 3.2 Backend

| Elemento | Tecnología |
|---|---|
| Runtime | Node.js 20 |
| Lenguaje | TypeScript 5.7 |
| Framework | Express 4 |
| ORM/Driver DB | mssql v12.5.4 (queries directas, sin ORM) |
| Autenticación | JWT (access 15 min) + Refresh Token (7 días, httpOnly cookie) |
| Passwords | bcrypt 12 rounds + fallback texto plano (migración gradual) |
| Validación | Zod |
| Upload archivos | Multer (diskStorage) |
| Logs | Winston + daily rotate |
| Email | Nodemailer |
| Dev hot-reload | tsx watch |

El backend corre en **http://localhost:3001** en desarrollo. La API está prefijada con `/api/v1`. Swagger disponible en `/api-docs`.

### 3.3 Base de Datos

| Elemento | Valor |
|---|---|
| Motor | SQL Server 2022 |
| Contenedor | `sisdoc_sqlserver` (Docker) |
| Base de datos | `SISDOC` |
| Puerto host | `11433` (1433 reservado por Windows/Hyper-V) |
| Volumen persistente | `sisdoc_sqlserver_data` → `/var/opt/mssql/data` |

#### Tablas principales del sistema legacy (usadas en producción):

| Tabla | Descripción |
|---|---|
| `documento` | Registro central del documento |
| `tramite` | Historial de movimientos (PK: `id_seguimiento`) |
| `archivo_digital` | Archivos adjuntos a documentos |
| `funcionario` | Datos del funcionario |
| `usuario` | Credenciales y perfil del usuario |
| `dependencia` | Servicios/unidades del hospital |
| `tipo_documento` | Catálogo de tipos de documento |
| `estado_documento` | Catálogo de estados del documento |
| `estado_tramite` | Catálogo de estados del trámite |
| `tipo_compromiso` | Catálogo de compromisos (sin/normal/urgente) |
| `tipo_distribucion` | Catálogo de tipos de distribución |
| `expediente` | Expedientes agrupadores de documentos |

#### Tablas nuevas creadas en este proyecto:

| Tabla | Descripción |
|---|---|
| `rol` | Roles del sistema (codigo, nombre, activo) |
| `usuario_rol` | Relación usuario-rol (N:M) |
| `rol_modulo` | Módulos habilitados por rol |
| `refresh_token` | Tokens de refresh JWT activos |
| `password_reset_tokens` | Tokens de recuperación de contraseña |
| `auditoria` | Log de acciones del sistema |
| `auditoria_reset` | Log específico de recuperación de contraseña |
| `alerta_config` | Configuración del scheduler de alertas |
| `alerta_log` | Log de alertas enviadas por correo |
| `documento_destino` | Destinos múltiples por documento |

### 3.4 Autenticación

El sistema implementa autenticación JWT de dos tokens:

1. **Access Token:** JWT firmado con `JWT_SECRET`, duración 15 minutos. Se envía en el header `Authorization: Bearer <token>`.
2. **Refresh Token:** JWT firmado con `JWT_REFRESH_SECRET`, duración 7 días. Se almacena en una httpOnly cookie y también en la tabla `refresh_token` en BD.

**Flujo:**
1. Login → backend emite access token + refresh token.
2. El frontend guarda el access token en Zustand (memoria, no localStorage).
3. Cada request adjunta el access token en el header.
4. Si la API responde 401 (token expirado), el interceptor de Axios intenta automáticamente un refresh usando la cookie httpOnly.
5. Si el refresh también falla, el usuario es desconectado y redirigido al login.

**El JWT incluye en el payload:**
- `sub`: id_usuario
- `usuario`: nombre de usuario
- `idDependencia`: servicio del usuario
- `todosServicios`: flag de acceso global
- `roles[]`: lista de roles
- `modulos[]`: lista de módulos permitidos

En cada refresh, los roles y módulos se re-consultan desde la BD, permitiendo que cambios de permisos tomen efecto sin re-login del usuario.

### 3.5 API REST

Todos los endpoints protegidos requieren el middleware `requireAuth`. Algunas rutas requieren además `requireRole(...)` o `requireModule(...)`.

**Formato de respuesta estándar:**
```json
{ "ok": true, "data": {...} }
{ "ok": false, "error": "mensaje de error" }
```

**Formato paginado:**
```json
{
  "ok": true,
  "data": [...],
  "meta": { "total": 100, "pagina": 1, "porPagina": 20, "totalPaginas": 5 }
}
```

### 3.6 Uploads / Archivos

- **Directorio:** `backend/uploads/` (no está en git).
- **Config institucional:** `backend/uploads/config/` (logo, fondo, sistema.json).
- **Naming:** `${timestamp_8_chars}.${ext}` — ej: `87328552.pdf` (máx 12 chars). Razón: columnas `varchar(50)` en tablas legacy.
- **Servido como estático:** `GET /uploads/{filename}` — Express sirve los archivos directamente.
- **En producción:** nginx sirve `/uploads` desde el volumen Docker.

**Extensiones permitidas por el sistema (hardcap multer):**
`pdf`, `doc`, `docx`, `xls`, `xlsx`, `png`, `jpg`, `jpeg`, `webp`, `txt`

**Extensiones excluidas intencionalmente:**
- `.svg` — puede contener `<script>` → riesgo de XSS almacenado.
- `.zip` — riesgo de malware y zip-slip.

### 3.7 Configuración del Sistema

La configuración del sistema se almacena en `backend/uploads/config/sistema.json`. Este archivo contiene parámetros configurables desde la UI de administración:

```json
{
  "nombreSistema": "SISDOC",
  "nombreInstitucion": "HUAP",
  "loginNombreSistema": "SISDOC",
  "loginSubtitulo": "Sistema de Gestión Documental",
  "loginTituloPrincipal": "Gestión documental moderna",
  "loginDescripcion": "...",
  "loginCard1": "Gestión documental",
  "loginCard2": "Flujo de derivaciones",
  "loginCard3": "Trazabilidad completa",
  "loginCard4": "Historial documental",
  "loginFooter": "© 2026 SISDOC v2.0 ...",
  "uploadExtensionesPermitidas": ["pdf", "doc", "docx", ...],
  "uploadMaxFileMB": 20,
  "uploadMaxTotalMB": 60
}
```

Si el archivo no existe o está corrupto, el sistema usa valores por defecto y no falla.

### 3.8 Docker

| Servicio | Perfil | Puerto |
|---|---|---|
| `sqlserver` | siempre | `127.0.0.1:11433:1433` |
| `backend` | `prod` | `3001` |
| `nginx` | `prod` | `80/443` |

**Desarrollo:** solo se levanta el contenedor `sqlserver`. Backend y frontend corren como procesos locales.

**Producción:** `docker compose --profile prod up -d --build`

---

## 4. Reglas de Negocio

### 4.1 Creación de Documentos

| Regla | Detalle |
|---|---|
| Estado inicial | Siempre **Despachado (2)** al crear |
| Origen automático | Se asigna desde la dependencia del usuario autenticado; el frontend no puede sobreescribir esto |
| Número interno | Se calcula como `MAX(num_interno) + 1` automáticamente |
| Número oficial | Se calcula como `MAX(num_oficial) + 1` automáticamente |
| Multi-destino | Se admite un array `destinos[]` con 1 a 20 servicios; se crea un `tramite` y un `documento_destino` por cada uno |
| Soporte físico | `tipoSoporte='F'` registra el documento como físico; se anota el prefijo `[SOPORTE:FÍSICO]` en las observaciones del trámite |
| Documento reservado | `reservado=true` fuerza el destino a **Dirección** (id=32) y anota `[RESERVADO→DIRECCIÓN]`; campo restringido a `of.partes` y `admin` |
| Materia | Requerida, máximo 250 caracteres |
| Observaciones | Opcional, se truncan a 250 caracteres en el trámite |

### 4.2 Flujo Documental — Estados del Documento

| ID | Estado | Descripción |
|---|---|---|
| 1 | Generado | Documento creado, aún no despachado (estado transitorio) |
| 2 | Despachado | En tránsito hacia el servicio destino |
| 3 | Recepcionado | Al menos un destino confirmó recepción |
| 4 | Terminado | Todos los destinos cerraron el trámite |
| 5 | (reservado) | Pendiente de validar en BD |

### 4.3 Flujo Documental — Estados del Trámite

| ID | Estado | Descripción |
|---|---|---|
| 1 | Generado | Trámite registrado, pendiente de despacho |
| 2 | Despachado | Enviado al servicio destino |
| 3 | Recepcionado | Confirmado por el servicio destino |
| 4 | Derivado | Redirigido a otro servicio |
| 5 | Cerrado | Trámite concluido |
| 7 | Archivo adjuntado | Evento de trazabilidad al adjuntar un archivo |

### 4.4 Derivación

| Regla | Detalle |
|---|---|
| Roles habilitados | `admin`, `of.partes`, `supervisores` |
| Prerrequisito | El documento no debe estar en estado **Terminado (4)** |
| Efecto | Inserta un nuevo trámite (estado 4=Derivado); el documento vuelve a estado **Despachado (2)** |
| Trazabilidad | Cada derivación queda en `tramite` con procedencia y destino |

### 4.5 Derivación — Tipos de Destinatario

| Código | Significado |
|---|---|
| `D` | Dependencia interna (servicio del hospital) |
| `E` | Destinatario externo |

Pendiente de validar: el acceso de usuarios con `verExternos=true` para ver documentos de tipo `E`.

### 4.6 Despacho y Re-despacho

| Acción | Roles | Comportamiento |
|---|---|---|
| Despacho inicial | `admin`, `of.partes`, `supervisores`, `funcionario` | Crea el trámite con estado 2; actualiza el estado del documento a 2 |
| Re-despacho | `admin`, `of.partes`, `supervisores`, `funcionario` | Inserta un nuevo trámite (no modifica el anterior); preserva trazabilidad íntegra |

### 4.7 Recepción

- **Roles habilitados:** `admin`, `of.partes`, `supervisores`, `funcionario`
- Inserta un nuevo trámite (estado 3=Recepcionado). No modifica el trámite de despacho — la trazabilidad es acumulativa.
- El documento pasa a estado **Recepcionado (3)**.
- **Multi-destino:** se puede recepcionar por destino individual (`recepcionar-destino`). Si al menos un destino recepciona, el documento pasa a estado 3.

### 4.8 Cierre (Terminar)

- **Roles habilitados:** `admin`, `of.partes`, `supervisores`, `funcionario`
- **Prerrequisito obligatorio:** el documento debe estar en estado **Recepcionado (3)**. Si no está en ese estado, la operación falla con HTTP 400.
- Inserta un nuevo trámite (estado 5=Cerrado).
- El documento pasa a estado **Terminado (4)**.
- **Multi-destino:** se puede cerrar por destino individual (`terminar-destino`). El documento pasa a estado Terminado (4) solo cuando **todos** los destinos están cerrados.

### 4.9 Reapertura

- **Roles habilitados:** solo `admin` y `supervisores`. **of.partes y funcionario NO pueden reabrir.**
- **Prerrequisito:** el documento debe estar en estado **Terminado (4)**.
- Inserta un nuevo trámite (estado 3=Recepcionado), conservando toda la trazabilidad previa.
- El documento vuelve a estado **Recepcionado (3)**.
- Las observaciones del motivo de reapertura son obligatorias.

### 4.10 Eliminación de Documentos

- Solo **administradores** pueden eliminar documentos.
- La eliminación es **lógica** (`soft delete`) — el registro permanece en BD con una marca de eliminado.

### 4.11 Alertas y Notificaciones por Correo

| Regla | Detalle |
|---|---|
| Destinatarios dinámicos | Se obtienen de los usuarios del sistema que tengan email válido registrado en su dependencia |
| Email requerido | Solo reciben alertas los usuarios con `usuario.email` no nulo, no vacío y con formato válido |
| Deduplicación | Si hay múltiples usuarios con el mismo email en un servicio, se envía una sola copia |
| Documentos pendientes | Son los documentos en estado 1/2/4 (tramite) en un servicio dado, excluyendo documentos Terminados (4) |
| Frecuencia | Configurable: entre 1 y 4 horarios diarios (formato HH:MM) |
| Anti-duplicado | El scheduler verifica si ya se envió una alerta automática en la ventana ±25 minutos del horario programado |
| Envío manual | Solo administradores pueden disparar envíos manuales desde la UI |
| Log | Cada envío (exitoso o fallido) queda registrado en `alerta_log` |

**Tipos de compromiso en alertas:**

| ID | Compromiso | Visual en email |
|---|---|---|
| 1 | Sin compromiso | Badge gris |
| 2 | Normal | Badge amarillo |
| 3 | Urgente | Badge rojo (fila destacada) |

### 4.12 Validaciones de Archivos

**Capa 1 — Multer hardcap (`.env`):**
- `MAX_FILE_SIZE` en bytes (por defecto 20 MB = 20.971.520 bytes)
- Este es el límite absoluto; ninguna configuración de UI puede superarlo.

**Capa 2 — Reglas configurables (`sistema.json`):**
- `uploadExtensionesPermitidas`: array de extensiones válidas (subconjunto del conjunto global).
- `uploadMaxFileMB`: máximo por archivo individual (1–100 MB, nunca supera el hardcap).
- `uploadMaxTotalMB`: cuota total por operación de carga múltiple (>= `uploadMaxFileMB`).

**Capa 3 — Control de acceso en upload:**
- Si el documento está en estado **Terminado (4)**, no se pueden adjuntar archivos.
- Solo pueden adjuntar: `admin`, `of.partes`, usuarios con `todos_servicios=true`, el creador del documento, o usuarios cuya dependencia figure en la trazabilidad del documento.

**Capa 4 — Frontend:**
- El hook `useUploadRules()` lee las reglas desde `/api/v1/configuracion` y las aplica antes del envío.
- Si el archivo supera el límite individual: toast de error, no se agrega a la lista.
- Si la cuota total se excede: barra roja + botón de envío deshabilitado.

### 4.13 Control de Acceso a Documentos

| Perfil de usuario | Documentos visibles |
|---|---|
| `admin` | Todos los documentos del sistema |
| `todos_servicios=true` | Todos los documentos del sistema |
| Dependencia específica | Solo documentos donde su servicio figura como procedencia o destino en `tramite` |
| Sin dependencia | No ve ningún documento (filtro `1=0`) |

### 4.14 Restricciones por Rol en Módulos

Los módulos visibles en el frontend son controlados por el JWT (`modulos[]`). Si el usuario no tiene el módulo habilitado, el componente `ModuleGuard` bloquea la ruta. Las validaciones se replican en el backend con el middleware `requireModule()`.

### 4.15 Unicidad de Usuarios

- El campo `usuario` (nombre de usuario) es único en la tabla `usuario` (máx 10 chars).
- El campo `email` es único por usuario (si se registra).
- No se puede eliminar el único administrador activo del sistema.
- Un usuario no puede eliminarse a sí mismo.

### 4.16 Recuperación de Contraseña

- Disponible solo para usuarios con email registrado.
- Genera un token seguro (32 bytes, SHA-256 hash almacenado en BD, nunca el raw token).
- Token válido por `RESET_TOKEN_EXPIRES_MINUTES` (por defecto 30 minutos).
- El token se usa una sola vez — al restablecer, se invalida junto con todos los refresh tokens activos del usuario.
- La respuesta al solicitar reset es siempre genérica, sin revelar si el email existe o no.
- Rate limit: 5 solicitudes por IP cada 15 minutos (20 en desarrollo).
- El correo incluye advertencia de que se debe estar en la red del hospital.

### 4.17 Auditoría de Acciones

Las siguientes acciones quedan registradas en la tabla `auditoria`:

| Acción | Cuándo se registra |
|---|---|
| `LOGIN_EXITOSO` | Al iniciar sesión correctamente |
| `LOGIN_FALLIDO` | Al fallar credenciales (usuario no encontrado o contraseña incorrecta) |
| `USUARIO_CREADO` | Al crear un nuevo usuario |
| `USUARIO_ELIMINADO` | Al eliminar un usuario |
| `CONTRASENA_CAMBIADA` | Al cambiar la contraseña de un usuario |
| `EXPORTAR_CSV` | Al descargar un reporte CSV |

Las acciones de recuperación de contraseña se registran en la tabla `auditoria_reset` con detalle de IP y user-agent.

---

## 5. Configuraciones del Sistema

### 5.1 Parámetros Configurables desde la UI

| Parámetro | Endpoint | Descripción | Restricción |
|---|---|---|---|
| Nombre del sistema | `PATCH /configuracion` | Texto que aparece en el header y sidebar | Solo admin |
| Nombre de la institución | `PATCH /configuracion` | Nombre del hospital | Solo admin |
| Logo institucional | `POST /configuracion/logo` | Imagen PNG/JPG/JPEG/WEBP, máx 5 MB | Solo admin |
| Fondo del login | `POST /configuracion/background` | Imagen de fondo, máx 5 MB | Solo admin |
| Textos del login | `PATCH /configuracion` | Título, subtítulo, descripción, tarjetas, pie | Solo admin |
| Extensiones permitidas | `PATCH /configuracion/upload-rules` | Array de extensiones habilitadas para upload | Solo admin |
| Tamaño máximo por archivo | `PATCH /configuracion/upload-rules` | MB, entre 1 y 100 (nunca supera hardcap .env) | Solo admin |
| Cuota total de carga | `PATCH /configuracion/upload-rules` | MB, debe ser >= tamaño máximo por archivo | Solo admin |
| Scheduler de alertas activo | `PUT /alertas/configuracion` | Activa o desactiva el envío automático | Solo admin |
| Horarios de alertas | `PUT /alertas/configuracion` | 1 a 4 horarios en formato HH:MM | Solo admin |

### 5.2 Parámetros de Variables de Entorno (`backend/.env`)

| Variable | Descripción | Valor por defecto |
|---|---|---|
| `NODE_ENV` | Entorno de ejecución | `development` |
| `PORT` | Puerto del backend | `3001` |
| `DB_USER` | Usuario de SQL Server | `sa` |
| `DB_PASSWORD` | Contraseña de SQL Server | — |
| `DB_SERVER` | Host de SQL Server | `localhost` |
| `DB_PORT` | Puerto de SQL Server (host) | `11433` |
| `DB_DATABASE` | Nombre de la base de datos | `SISDOC` |
| `JWT_SECRET` | Clave de firma del access token (mín 32 chars) | — |
| `JWT_REFRESH_SECRET` | Clave de firma del refresh token (mín 32 chars) | — |
| `JWT_EXPIRES_IN` | Duración del access token | `15m` |
| `JWT_REFRESH_EXPIRES_IN` | Duración del refresh token | `7d` |
| `CORS_ORIGIN` | Origen permitido en CORS | `http://localhost:5173` |
| `UPLOAD_DIR` | Directorio de uploads | `./uploads` |
| `MAX_FILE_SIZE` | Hardcap de tamaño de archivo (bytes) | `20971520` (20 MB) |
| `SMTP_HOST` | Servidor SMTP para correos | `` (vacío = modo consola) |
| `SMTP_PORT` | Puerto SMTP | `587` |
| `SMTP_SECURE` | Usar TLS en SMTP | `false` |
| `SMTP_USER` | Usuario SMTP | `` |
| `SMTP_PASS` | Contraseña SMTP | `` |
| `SMTP_FROM` | Remitente de correos | `SISDOC <noreply@sisdoc.cl>` |
| `FRONTEND_URL` | URL base del frontend (usada en links de correos) | `http://localhost:5173` |
| `RESET_TOKEN_EXPIRES_MINUTES` | Minutos de validez del token de reset | `30` |

### 5.3 Catálogos (Pendiente de validar en BD)

Los siguientes catálogos están en la base de datos y se exponen vía `/api/v1/catalogos`:

| Catálogo | Endpoint | Descripción |
|---|---|---|
| Tipos de documento | `GET /catalogos/tipos-documento` | Tipos documentales registrados en `tipo_documento` |
| Estados de documento | `GET /catalogos/estados-documento` | Estados en `estado_documento` |
| Dependencias / Servicios | `GET /catalogos/dependencias` | Servicios del hospital en `dependencia` |

Los catálogos de **tipos de compromiso**, **tipos de distribución** y **estados de trámite** son fijos en el código actualmente. Modificarlos requiere cambio en BD.

### 5.4 Gestión de Roles

Los roles son completamente gestionables desde el módulo **Admin → Roles** (solo admin):

- Crear roles con código, nombre y selección de módulos.
- Modificar nombre, módulos habilitados y estado activo/inactivo de un rol.
- Si un rol tiene usuarios asignados y se "elimina", queda **desactivado** (no se borra).
- Si no tiene usuarios asignados, se borra físicamente.

**Módulos disponibles para asignación:**

| Código | Etiqueta | Grupo |
|---|---|---|
| `dashboard` | Dashboard | Operativo |
| `documentos` | Documentos | Operativo |
| `bandeja` | Bandeja Entrada | Operativo |
| `enviados` | Enviados | Operativo |
| `tramites` | Mis Trámites | Operativo |
| `trazabilidad` | Trazabilidad | Operativo |
| `busqueda` | Búsqueda | Operativo |
| `archivos` | Archivos | Operativo |
| `usuarios` | Usuarios | Administración |
| `reportes` | Reportes | Administración |
| `roles` | Roles | Administración |
| `configuracion` | Configuración | Administración |
| `alertas` | Alertas | Administración |

---

## 6. Roles y Permisos

### 6.1 Roles del Sistema

| Rol | Código | Descripción | Acceso global a documentos |
|---|---|---|---|
| Administrador | `admin` | Control total del sistema | Sí (todos los servicios) |
| Oficina de Partes | `of.partes` | Ingreso y gestión de documentos institucionales | Configurable |
| Supervisor | `supervisores` | Puede derivar y reabrir documentos, pero no gestionar usuarios | Configurable |
| Funcionario | `funcionario` | Rol base de cualquier usuario del hospital | Solo su servicio |

> El rol `admin` siempre tiene acceso a todos los módulos, sin importar la configuración de `rol_modulo`.

### 6.2 Tabla de Permisos por Rol

| Acción | admin | of.partes | supervisores | funcionario |
|---|---|---|---|---|
| **Documentos** |
| Crear documento | Sí | Sí | Sí | Sí |
| Despachar documento | Sí | Sí | Sí | Sí |
| Re-despachar documento | Sí | Sí | Sí | Sí |
| Recepcionar documento | Sí | Sí | Sí | Sí |
| Derivar documento | Sí | Sí | Sí | No |
| Terminar documento | Sí | Sí | Sí | Sí (solo si estado=3) |
| Reabrir documento | Sí | No | Sí | No |
| Eliminar documento | Sí | No | No | No |
| Crear con soporte físico | Sí | Sí | No | No |
| Crear documento reservado | Sí | Sí | No | No |
| **Archivos** |
| Subir archivos | Sí | Sí | Sí | Sí (condicionado) |
| Descargar / previsualizar | Sí | Sí | Sí | Sí |
| Eliminar archivos | Sí | Sí (su servicio) | Sí (su servicio) | Sí (su servicio) |
| **Administración** |
| Gestionar usuarios | Sí | No | No | No |
| Gestionar roles | Sí | No | No | No |
| Configuración del sistema | Sí | No | No | No |
| Gestionar alertas | Sí | No | No | No |
| **Reportes** |
| Ver dashboard | Sí | Sí (su servicio) | Sí (su servicio) | Sí (su servicio) |
| Exportar CSV | Sí (global) | Sí (su servicio) | Sí (su servicio) | Sí (su servicio) |
| Ver actividad reciente | Sí | Sí | Sí | Sí |

> **Nota:** Las restricciones "su servicio" aplican cuando `todos_servicios=false` en el registro del usuario. Un funcionario con `todos_servicios=true` tiene visibilidad global aunque su rol sea `funcionario`.

### 6.3 Control de Módulos por Rol (Configuración por defecto)

El administrador puede personalizar qué módulos ve cada rol desde **Admin → Roles**. Los valores aquí indicados son los que se asignan al crear usuarios con ese rol como predeterminado:

| Módulo | admin | of.partes (típico) | supervisores (típico) | funcionario (típico) |
|---|---|---|---|---|
| dashboard | Sí | Sí | Sí | Sí |
| documentos | Sí | Sí | Sí | Sí |
| bandeja | Sí | Sí | Sí | Sí |
| enviados | Sí | Sí | Sí | Sí |
| tramites | Sí | Sí | Sí | Sí |
| trazabilidad | Sí | Sí | Sí | Sí |
| busqueda | Sí | Sí | Sí | Sí |
| archivos | Sí | Sí | Sí | Sí |
| usuarios | Sí | No | No | No |
| reportes | Sí | Configurable | Configurable | Configurable |
| roles | Sí | No | No | No |
| configuracion | Sí | No | No | No |
| alertas | Sí | No | No | No |

> Los módulos asignados a roles no-admin son completamente configurables desde la UI de administración.

---

## 7. Acciones por Módulo y Rol

### 7.1 Módulo: Dashboard

| Acción | Todos los roles |
|---|---|
| Ver métricas totales | Sí (filtradas por servicio si no es admin/todos_servicios) |
| Ver gráfico por estado | Sí |
| Ver gráfico por mes (6 meses) | Sí |
| Ver distribución por tipo | Sí |
| Ver actividad reciente | Sí |
| Ver total de archivos digitales | Solo admin / todos_servicios |
| Ver total de usuarios | Solo admin / todos_servicios |

### 7.2 Módulo: Documentos

| Acción | admin | of.partes | supervisores | funcionario |
|---|---|---|---|---|
| Listar documentos (filtros) | Todos (alcance según servicio) |
| Buscar por número | Sí | Sí | Sí | Sí |
| Ver detalle de documento | Sí | Sí | Sí | Sí |
| Ver historial/trazabilidad | Sí | Sí | Sí | Sí |
| Crear nuevo documento | Sí | Sí | Sí | Sí |
| Despachar | Sí | Sí | Sí | Sí |
| Recepcionar | Sí | Sí | Sí | Sí |
| Derivar | Sí | Sí | Sí | No |
| Terminar | Sí | Sí | Sí | Sí |
| Reabrir | Sí | No | Sí | No |
| Eliminar | Sí | No | No | No |
| Adjuntar archivos | Sí | Sí | Sí | Sí (condicionado) |
| Crear soporte físico | Sí | Sí | No | No |
| Crear documento reservado | Sí | Sí | No | No |

### 7.3 Módulo: Bandeja de Entrada

| Acción | Todos los roles con módulo |
|---|---|
| Ver documentos despachados al servicio | Sí |
| Filtrar por estado | Sí |
| Recepcionar desde lista | Sí |
| Ir al detalle del documento | Sí |

### 7.4 Módulo: Enviados

| Acción | Todos los roles con módulo |
|---|---|
| Ver documentos originados por el servicio | Sí |
| Ver estado actual de cada enviado | Sí |
| Ir al detalle | Sí |

### 7.5 Módulo: Mis Trámites

| Acción | Todos los roles con módulo |
|---|---|
| Ver trámites del usuario | Sí |
| Ver documentos asociados a cada trámite | Sí |
| Ir al detalle del documento | Sí |

### 7.6 Módulo: Trazabilidad

| Acción | Todos los roles con módulo |
|---|---|
| Buscar documentos | Sí |
| Ver timeline de trazabilidad | Sí |
| Ver cada paso del recorrido | Sí |

### 7.7 Módulo: Búsqueda

| Acción | Todos los roles con módulo |
|---|---|
| Búsqueda global (documentos, trámites, funcionarios) | Sí |
| Filtrar por tipo de resultado | Sí |
| Paginación de resultados | Sí |
| Ir al detalle del resultado | Sí |

### 7.8 Módulo: Archivos

| Acción | admin | of.partes | supervisores | funcionario |
|---|---|---|---|---|
| Ver lista de archivos | Sí | Sí | Sí | Sí |
| Subir archivo (libre) | Sí | Sí | Sí | Sí |
| Subir archivo a documento | Sí | Sí | Sí | Sí (condicionado) |
| Previsualizar archivo | Sí | Sí | Sí | Sí |
| Descargar archivo | Sí | Sí | Sí | Sí |
| Eliminar archivo | Sí | Sí (su servicio) | Sí (su servicio) | Sí (su servicio) |

### 7.9 Módulo: Reportes

| Acción | admin | Otros roles con módulo |
|---|---|---|
| Ver dashboard completo | Sí (global) | Sí (su servicio) |
| Ver actividad reciente | Sí (global) | Sí (su servicio) |
| Exportar CSV | Sí (global) | Sí (su servicio) |
| Filtrar exportación por fechas | Sí | Sí |

### 7.10 Módulo: Admin — Usuarios

| Acción | admin |
|---|---|
| Listar usuarios | Sí |
| Buscar usuarios | Sí |
| Ver detalle de usuario | Sí |
| Crear usuario | Sí |
| Modificar datos del usuario | Sí |
| Cambiar contraseña | Sí |
| Asignar roles | Sí |
| Asignar servicio/dependencia | Sí |
| Registrar / modificar email | Sí |
| Activar/desactivar todos_servicios | Sí |
| Eliminar usuario | Sí (no puede eliminarse a sí mismo ni al último admin) |

### 7.11 Módulo: Admin — Roles

| Acción | admin |
|---|---|
| Listar roles con módulos | Sí |
| Ver detalle de rol | Sí |
| Crear nuevo rol | Sí |
| Modificar nombre de rol | Sí |
| Asignar módulos al rol | Sí |
| Activar / desactivar rol | Sí |
| Eliminar rol (si no tiene usuarios) | Sí |

### 7.12 Módulo: Admin — Configuración

| Acción | admin |
|---|---|
| Ver configuración actual | Sí |
| Cambiar nombre del sistema/institución | Sí |
| Subir logo institucional | Sí |
| Subir fondo del login | Sí |
| Editar textos del login | Sí |
| Configurar reglas de carga | Sí |

### 7.13 Módulo: Admin — Alertas

| Acción | admin |
|---|---|
| Ver configuración del scheduler | Sí |
| Activar / desactivar scheduler | Sí |
| Configurar horarios (1–4 por día) | Sí |
| Ver documentos pendientes por servicio | Sí |
| Ver destinatarios por servicio | Sí |
| Enviar alerta manual a un servicio | Sí |
| Enviar alertas a todos los servicios | Sí |
| Probar envío a un servicio específico | Sí |
| Ver log de alertas enviadas | Sí |

---

## 8. Flujo Funcional Principal

### 8.1 Flujo Completo — Ingreso y Gestión de un Documento

```
1. INGRESO
   └─ Usuario autenticado accede a "Nuevo Documento"
   └─ Completa: materia, tipo documental, fecha, observaciones
   └─ Selecciona 1 o múltiples servicios destino
   └─ Opcionalmente adjunta archivos
   └─ Hace clic en "Registrar y Despachar"

2. CREACIÓN EN BACKEND
   └─ Backend asigna: num_interno, num_oficial, estado=Despachado (2)
   └─ Backend asigna procedencia = dependencia del usuario autenticado
   └─ Se crea 1 registro en `documento`
   └─ Se crea 1 registro en `tramite` por cada destino (estado=2)
   └─ Se crea 1 registro en `documento_destino` por cada destino
   └─ Se suben los archivos adjuntos (si los hay)
   └─ Cada archivo genera un evento de trazabilidad (estado=7)

3. NOTIFICACIÓN AUTOMÁTICA (si scheduler activo)
   └─ El scheduler verifica en los horarios configurados si hay pendientes
   └─ Envía correo HTML a los usuarios con email del servicio destino
   └─ Registra el envío en `alerta_log`

4. RECEPCIÓN
   └─ Usuario del servicio destino accede a "Bandeja de Entrada"
   └─ Ve el documento en estado Despachado
   └─ Hace clic en "Recepcionar"
   └─ Backend inserta nuevo `tramite` (estado=3)
   └─ Documento pasa a estado Recepcionado (3)
   └─ Trazabilidad registra la recepción con fecha y usuario

5. GESTIÓN DEL TRÁMITE
   └─ El servicio gesiona el documento (adjunta archivos, agrega observaciones)
   └─ Si debe redirigir → "Derivar" (roles: admin, of.partes, supervisores)
      └─ Backend inserta tramite (estado=4=Derivado)
      └─ Documento vuelve a estado Despachado (2)
      └─ El nuevo destino puede recepcionar

6. CIERRE
   └─ Usuario (cualquier rol con módulo) hace clic en "Terminar"
   └─ Prerrequisito: documento debe estar en estado Recepcionado (3)
   └─ Backend inserta tramite (estado=5=Cerrado)
   └─ Si todos los destinos cerraron → documento pasa a estado Terminado (4)

7. CONSULTA POSTERIOR
   └─ Módulo Trazabilidad: timeline completo del recorrido
   └─ Módulo Búsqueda: búsqueda por materia, número, funcionario
   └─ Módulo Documentos: historial con filtros
   └─ Módulo Reportes: métricas y exportación CSV

8. REAPERTURA (si aplica)
   └─ Solo admin o supervisores pueden reabrir documentos Terminados
   └─ Backend inserta tramite (estado=3=Recepcionado)
   └─ Documento vuelve a estado Recepcionado (3)
   └─ La trazabilidad previa se conserva íntegra
```

### 8.2 Flujo de Recuperación de Contraseña

```
1. Usuario accede a "/forgot-password"
2. Ingresa su correo electrónico
3. Backend busca el usuario por email (sin revelar si existe o no)
4. Si existe → genera token SHA-256 (32 bytes), expira en 30 min
5. Envía correo HTML con enlace al frontend
6. Usuario hace clic en el enlace → accede a "/reset-password?token=..."
7. Frontend valida el token antes de mostrar el formulario
8. Usuario ingresa nueva contraseña (mín 4, máx 10 chars)
9. Backend hashea con bcrypt (12 rounds), actualiza en BD
10. Invalida el token y todos los refresh tokens activos del usuario
11. Usuario debe iniciar sesión nuevamente
```

---

## 9. Seguridad y Auditoría

### 9.1 Autenticación y Sesión

| Mecanismo | Implementación |
|---|---|
| Passwords | bcrypt 12 rounds. Migración gradual desde texto plano legacy (se hashea en el primer login) |
| Access Token | JWT firmado con secreto de mínimo 32 caracteres; expira en 15 minutos |
| Refresh Token | JWT de 7 días; almacenado en httpOnly cookie + tabla `refresh_token`; revocable explícitamente |
| Auto-refresh | Interceptor de Axios renueva el access token transparentemente |
| Logout | Revoca el refresh token en BD; el usuario debe re-autenticarse |
| Cambio de contraseña | Revoca todos los refresh tokens activos del usuario |

### 9.2 Control de Acceso

| Capa | Mecanismo |
|---|---|
| Autenticación global | Middleware `requireAuth` en todos los routers protegidos |
| Control por rol | Middleware `requireRole('admin', 'of.partes', ...)` por endpoint |
| Control por módulo | Middleware `requireModule('modulo')` + `ModuleGuard` en frontend |
| Acceso por servicio | Flag `todosServicios` + filtro por `idDependencia` en queries SQL |
| Acceso a archivos | Verificación de propiedad en upload y delete (servicio en trazabilidad) |

### 9.3 Protección contra Ataques Comunes

| Riesgo | Mitigación |
|---|---|
| XSS en archivos | SVG excluido de extensiones permitidas (puede contener `<script>`) |
| Zip-slip | ZIP excluido de extensiones permitidas |
| Brute force en reset | Rate limit: 5 solicitudes por IP cada 15 min |
| SQL Injection | Queries parametrizadas con mssql (sin concatenación de strings en SQL) |
| Token leakage | Access token en memoria (Zustand), refresh token en httpOnly cookie |
| Enumeración de correos | Respuesta genérica en forgot-password sin revelar si el email existe |

### 9.4 Validaciones

| Capa | Herramienta | Qué valida |
|---|---|---|
| Frontend (form) | react-hook-form + zod | Campos obligatorios, longitudes, formatos |
| Frontend (upload) | useUploadRules() | Extensión y tamaño antes del envío |
| Backend (body) | Middleware `validate(schema)` + zod | Schema de cada endpoint |
| Backend (query) | zod con `.transform()` | Parámetros de filtrado y paginación |
| Backend (file) | multer fileFilter + limits | Extensiones y tamaño máximo |
| Backend (negocio) | Lógica en services | Estados, permisos, unicidad |

### 9.5 Auditoría Registrada

| Tabla | Eventos |
|---|---|
| `auditoria` | LOGIN_EXITOSO, LOGIN_FALLIDO, USUARIO_CREADO, USUARIO_ELIMINADO, CONTRASENA_CAMBIADA, EXPORTAR_CSV |
| `auditoria_reset` | FORGOT_EMAIL_NO_ENCONTRADO, FORGOT_CORREO_ENVIADO, FORGOT_CORREO_FALLO, RESET_TOKEN_INVALIDO, RESET_CONTRASENA_CAMBIADA |
| `alerta_log` | Cada envío de alerta por correo (ok, error, sin_docs, sin_correo) |

---

## 10. Estado Actual del Proyecto

### 10.1 Módulos Operativos ✅

| Módulo | Estado |
|---|---|
| Login con JWT + refresh automático | Operativo |
| Recuperación de contraseña por email | Operativo |
| Dashboard con métricas reales y gráficos | Operativo |
| Documentos: listado, detalle, crear | Operativo |
| Despachar / Re-despachar documentos | Operativo |
| Recepcionar documentos | Operativo |
| Derivar documentos | Operativo |
| Terminar (cerrar) documentos | Operativo |
| Reabrir documentos | Operativo |
| Trazabilidad: timeline completo | Operativo |
| Multi-destino en creación y gestión | Operativo |
| Documento físico/papel (soporte=F) | Operativo |
| Documento reservado (destino forzado a Dirección) | Operativo |
| Bandeja de entrada con paginación | Operativo |
| Enviados | Operativo |
| Mis Trámites | Operativo |
| Búsqueda global | Operativo |
| Archivos: upload, listado, descarga, previsualización | Operativo |
| Carga múltiple de archivos | Operativo |
| Expedientes: listado + crear + documentos | Operativo |
| Usuarios: CRUD + roles + email | Operativo |
| Roles: CRUD + módulos | Operativo |
| Reportes: métricas, gráficos, exportar CSV | Operativo |
| Configuración: logo, fondo, nombres | Operativo |
| Configuración: textos del login | Operativo |
| Configuración: reglas de carga | Operativo |
| Alertas: scheduler + horarios + envío manual | Operativo |
| Auditoría de acciones | Operativo |

### 10.2 Módulos / Funcionalidades en Desarrollo o Pendientes

| Funcionalidad | Estado |
|---|---|
| Notificaciones en tiempo real (WebSocket) | Pendiente |
| Modo oscuro | Pendiente |
| Branding dinámico (logo en sidebar desde config) | Pendiente |
| Export a PDF en reportes | Pendiente |
| Tests automatizados (Jest/Vitest) | Pendiente |
| Expedientes: vinculación de documentos a expedientes desde detalle | Pendiente de validar |
| Vista de expedientes legacy (19.373 registros) | Pendiente de validar UX |

### 10.3 Funcionalidades que Requieren Validación

| Funcionalidad | Observación |
|---|---|
| Destinatario tipo `E` (externo) | El campo `tipoDestinatario='E'` existe en el schema pero el flujo de visibilidad para externos requiere validación con usuarios reales |
| Catálogos de tipo_compromiso y tipo_distribucion | Los IDs son fijos en el código; si la BD tiene valores distintos puede haber inconsistencias |
| Estado de documento `5` | Aparece en el filtro de reportes como excluido, pero no tiene descripción explícita en el código |
| Correo SMTP en producción | Requiere configuración de servidor SMTP real (SMTP_HOST, SMTP_USER, SMTP_PASS) |
| Scheduler de alertas en producción | Necesita que `FRONTEND_URL` apunte a la URL real del sistema |

### 10.4 Riesgos Técnicos

| Riesgo | Descripción | Mitigación |
|---|---|---|
| Columnas varchar(50) en BD legacy | `archivo_digital.archivo` y `archivo_digital.ruta` tienen límite de 50 chars | Filenames cortos (12 chars): `${8_chars_timestamp}.${ext}` |
| Texto plano en clave legacy | Usuarios sin `clave_hash` usan comparación directa | Migración gradual automática en cada login |
| Sin volumen persistente en desarrollo | Si el contenedor Docker se recrea, se pierde la BD | Volumen `sisdoc_sqlserver_data` mapeado en docker-compose |
| SMTP no configurado | Sin SMTP_HOST, los correos se muestran solo en consola | Modo desarrollo intencionalmente tolerante; en prod configurar SMTP |
| Puerto 1433 reservado en Windows | Hyper-V reserva el puerto 1433 en Windows | Se usa el puerto 11433 del host mapeado a 1433 del contenedor |
| mssql v12 sin tipos TypeScript | La librería mssql v12 no incluye `.d.ts` | Declaraciones manuales en `src/types/mssql.d.ts` |

### 10.5 Mejoras Futuras Recomendadas

1. Implementar WebSockets para notificaciones en tiempo real (nuevo documento en bandeja).
2. Agregar suite de tests (Vitest en frontend, Jest/Supertest en backend).
3. Mostrar el logo institucional en el sidebar (ya está almacenado, falta conectarlo al store).
4. Export a PDF desde el módulo de reportes.
5. Soporte de modo oscuro en la UI.
6. Paginación en el módulo de expedientes para mejorar rendimiento con 19.000+ registros.
7. Migración completa de `clave` (texto plano) a `clave_hash` para todos los usuarios.
8. Implementar refresh rotativo de refresh tokens para mayor seguridad.
9. Agregar CAPTCHA o 2FA opcional en el login para ambientes de producción.
10. Configurar un proceso de backup automático de la BD y del directorio `uploads/`.

---

## 11. Estructura del Repositorio

```
sisdoc-modernizado/
│
├── backend/                          # Servidor Node.js + TypeScript
│   ├── src/
│   │   ├── app.ts                    # Express app, CORS, middlewares, rutas
│   │   ├── server.ts                 # Entry point; bind 0.0.0.0:3001
│   │   ├── config/
│   │   │   ├── database.ts           # Pool mssql, getPool()
│   │   │   ├── env.ts                # Variables de entorno validadas con Zod
│   │   │   └── swagger.ts            # Documentación OpenAPI/Swagger
│   │   ├── middleware/
│   │   │   ├── auth.middleware.ts    # requireAuth, requireRole, requireModule
│   │   │   ├── validate.middleware.ts # Validación Zod de body/query
│   │   │   ├── error.middleware.ts   # Manejador global de errores
│   │   │   └── logger.middleware.ts  # Log de requests HTTP
│   │   ├── modules/
│   │   │   ├── alertas/              # Scheduler, envío manual, configuración
│   │   │   ├── archivos/             # Upload multer, listado, download, preview
│   │   │   ├── auth/                 # Login, refresh, logout, /me, password-reset
│   │   │   ├── busqueda/             # Búsqueda global (docs, trámites, func.)
│   │   │   ├── catalogos/            # Tipos doc, estados, dependencias
│   │   │   ├── configuracion/        # Logo, fondo login, nombres, upload-rules
│   │   │   ├── documentos/           # CRUD + despachar/recepcionar/derivar/terminar
│   │   │   ├── expedientes/          # CRUD expedientes + vinculación docs
│   │   │   ├── reportes/             # Dashboard + actividad + exportar CSV
│   │   │   ├── roles/                # CRUD roles + módulos
│   │   │   ├── tramites/             # Bandeja + historial trámites
│   │   │   └── usuarios/             # CRUD usuarios + roles + auditoría
│   │   ├── shared/
│   │   │   ├── services/
│   │   │   │   ├── alertas.scheduler.ts  # Scheduler con setInterval (60s)
│   │   │   │   └── email.service.ts      # nodemailer, plantillas HTML
│   │   │   ├── types/
│   │   │   │   └── api.types.ts          # AuthenticatedRequest, JwtPayload
│   │   │   └── utils/
│   │   │       ├── auditoria.ts          # logAuditoria()
│   │   │       ├── logger.ts             # Winston logger
│   │   │       └── response.ts           # sendSuccess, sendError, sendPaginated
│   │   └── types/
│   │       └── mssql.d.ts                # Tipos TypeScript manuales para mssql v12
│   ├── uploads/                       # Archivos subidos (NO en git)
│   │   └── config/                    # Logo, fondo, sistema.json
│   ├── .env                           # Variables de entorno (NO en git)
│   ├── tsconfig.json
│   └── package.json
│
├── frontend/                          # SPA React 18 + TypeScript
│   ├── src/
│   │   ├── App.tsx                    # Root con QueryClientProvider + RouterProvider
│   │   ├── main.tsx                   # Entry point (importa App.tsx explícitamente)
│   │   ├── app/
│   │   │   ├── router.tsx             # createBrowserRouter con todas las rutas
│   │   │   └── providers.tsx          # Providers de contexto
│   │   ├── components/
│   │   │   ├── layout/                # Layout, Sidebar, Header
│   │   │   ├── ui/                    # Componentes shadcn/ui (Button, Card, Badge...)
│   │   │   ├── shared/                # ProtectedRoute, ModuleGuard, EmptyState...
│   │   │   └── documentos/            # Modales de adjuntar y nómina
│   │   ├── pages/
│   │   │   ├── auth/                  # Login, ForgotPassword, ResetPassword
│   │   │   ├── dashboard/             # Dashboard con métricas
│   │   │   ├── documentos/            # Listado, Detalle, Nuevo
│   │   │   ├── bandeja/               # Bandeja de entrada
│   │   │   ├── enviados/              # Documentos enviados
│   │   │   ├── tramites/              # Mis trámites
│   │   │   ├── trazabilidad/          # Timeline de trazabilidad
│   │   │   ├── busqueda/              # Búsqueda global
│   │   │   ├── archivos/              # Gestión de archivos
│   │   │   ├── reportes/              # Métricas + CSV
│   │   │   ├── alertas/               # Configuración de alertas
│   │   │   └── admin/                 # Usuarios, Roles, Configuración
│   │   ├── lib/
│   │   │   ├── api/                   # Clientes Axios por módulo
│   │   │   ├── config/                # Branding config
│   │   │   └── utils/                 # cn(), formatFechaHora(), nomina.generator
│   │   ├── stores/
│   │   │   └── auth.store.ts          # Zustand: user, accessToken, setAuth, logout
│   │   ├── hooks/
│   │   │   ├── useDebounce.ts
│   │   │   ├── useModulos.ts          # Hook de módulos habilitados del usuario
│   │   │   ├── useRole.ts             # Hook de permisos por rol
│   │   │   └── useUploadRules.ts      # Hook de reglas de carga configurables
│   │   └── styles/
│   │       └── globals.css            # Paleta CSS vars + sidebar-gradient + skeleton
│   └── package.json
│
├── database/
│   └── scripts/
│       ├── 01-backup-docs.sql         # Backup de documentos legacy
│       ├── 02-clean-and-seed.sql      # Limpieza y datos de prueba
│       ├── 03-optimize-indexes.sql    # Índices para rendimiento
│       └── 04-create-admin-user.sql   # Creación del usuario admin inicial
│
├── legacy/                            # Sistema original (ASP clásico)
│   └── ...                            # NUNCA MODIFICAR — solo lectura histórica
│
├── docker-compose.yml                 # SQL Server (dev) + backend + nginx (prod)
├── CLAUDE.md                          # Guía técnica completa para el equipo
├── README.md                          # Introducción rápida al proyecto
└── DOCUMENTACION_FUNCIONAL_TECNICA_SYSDOC.md  # Este documento
```

---

## 12. Recomendaciones Técnicas

### 12.1 Código Legacy

- **Mantener `/legacy` intacto y separado.** El código del sistema original (ASP clásico) está preservado en `/legacy` únicamente como referencia histórica. No debe modificarse ni mezclarse con el código activo.
- **No copiar patrones del legacy al código moderno.** El sistema legacy usa concatenación directa en SQL, sin bcrypt y sin JWT — ninguno de esos patrones debe reintroducirse.

### 12.2 Antes de Pasar a Producción

| Verificación | Detalle |
|---|---|
| Configurar SMTP real | Sin SMTP_HOST configurado los correos solo aparecen en consola del servidor |
| Configurar FRONTEND_URL | Los links en correos de alerta y reset apuntan a esta URL |
| Cambiar JWT_SECRET y JWT_REFRESH_SECRET | Usar secretos de al menos 64 caracteres aleatorios |
| Cambiar contraseña de BD (DB_PASSWORD) | No usar la contraseña de desarrollo |
| Verificar CORS_ORIGIN | Debe apuntar al dominio real del frontend |
| Crear volumen de uploads persistente | El directorio `uploads/` debe sobrevivir recreaciones del contenedor |
| Validar roles por usuario | Verificar que cada usuario tiene los roles y módulos correctos |
| Validar emails de usuarios | Los usuarios que deben recibir alertas necesitan email registrado |
| Probar flujo de alertas | Enviar alerta de prueba desde el módulo Admin → Alertas |
| Probar recuperación de contraseña | Validar que el correo llega y el enlace funciona en la red del hospital |

### 12.3 Documentación y Mantenimiento

- **Mantener CLAUDE.md actualizado** con cada cambio técnico significativo: nuevas columnas en BD, cambios de schema, módulos nuevos.
- **Documentar reglas de negocio nuevas** en este documento o en el CLAUDE.md antes de implementarlas en código.
- **Registrar errores conocidos** con su solución en el CLAUDE.md para evitar diagnósticos repetidos.
- **No hardcodear IDs de catálogos** en el código de negocio cuando sea evitable (ej: el ID de Dirección = 32 para documentos reservados es un riesgo si la BD cambia).

### 12.4 Seguridad

- **Auditar accesos periódicamente** revisando la tabla `auditoria` en BD — especialmente `LOGIN_FALLIDO` en masa.
- **Rotar secretos JWT** en caso de sospecha de compromiso; todos los usuarios deberán re-autenticarse.
- **No exponer el puerto 11433 del SQL Server** fuera de la red del hospital.
- **No subir `.env` al repositorio** — está en `.gitignore`. Usar un gestor de secretos en producción.
- **Validar permisos antes de cada release:** recorrer la tabla de permisos de la sección 6.2 y probar con cada rol.

### 12.5 Base de Datos

- **Hacer backup periódico del volumen Docker** `sisdoc_sqlserver_data` hacia almacenamiento externo.
- **No modificar columnas críticas del schema legacy** (`num_interno`, `materia`, `tramite.id_seguimiento`, etc.) sin validar el impacto en las queries del backend.
- **No borrar la tabla `tramite`** — es el núcleo de toda la trazabilidad del sistema.
- **Si se agregan columnas nuevas** al schema legacy, actualizar el CLAUDE.md en la sección "Columnas reales".

---

*Documento generado a partir del análisis del código fuente del repositorio `sisdoc-modernizado`.*
*Versión del sistema: 2.0.0 — Mayo 2026*
*Hospital Universitario Asociado de Puebla — HUAP*
