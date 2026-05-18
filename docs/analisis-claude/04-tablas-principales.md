# 04 — Tablas Principales de Base de Datos

**Fecha de análisis:** 2026-05-18  
**Motor:** SQL Server 2022 (Docker) — Base de datos: SISDOC  
**Total tablas identificadas:** 47

---

## 1. Mapa relacional de entidades core

```
┌──────────────┐       ┌─────────────┐       ┌──────────────────┐
│  funcionario │──────►│   usuario   │       │   dependencia    │
│  (personas)  │       │  (login)    │       │  (org. interna)  │
└──────────────┘       └──────┬──────┘       └────────┬─────────┘
                              │                        │
                         ┌────▼────┐                   │
                         │ acceso  │◄──────────────────┘
                         │(u-dep)  │
                         └────┬────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
       ┌──────────┐    ┌──────────┐    ┌──────────────┐
       │documento │    │expediente│    │  historial   │
       │(core)    │    │(agrupador│    │  _documento  │
       └──────┬───┘    └──────────┘    └──────────────┘
              │
    ┌─────────┼──────────┐
    ▼         ▼          ▼
┌───────┐ ┌──────────┐ ┌───────────────┐
│tramite│ │ archivo  │ │descriptor_    │
│(flujo)│ │_digital  │ │documento      │
└───────┘ └──────────┘ └───────────────┘
```

---

## 2. Tablas de identidad y acceso

### `usuario`
Tabla principal de autenticación.

| Campo | Tipo | PK/FK | Descripción |
|---|---|---|---|
| `id_usuario` | INT | PK | Identificador único |
| `usuario` | VARCHAR(50) | | Login del sistema |
| `clave` | VARCHAR(50) | | Contraseña (texto plano — RIESGO) |
| `id_funcionario` | INT | FK→funcionario | Datos personales |
| `activo` | BIT | | Estado del usuario |

### `funcionario`
Datos personales del personal institucional.

| Campo | Tipo | PK/FK | Descripción |
|---|---|---|---|
| `rut_fun` | VARCHAR(12) | PK | RUT del funcionario |
| `dv_fun` | CHAR(1) | | Dígito verificador |
| `nombres_fun` | VARCHAR(100) | | Nombres |
| `ap_pat_fun` | VARCHAR(60) | | Apellido paterno |
| `ap_mat_fun` | VARCHAR(60) | | Apellido materno |
| `id_dependencia` | INT | FK→dependencia | Dependencia principal |
| `email_fun` | VARCHAR(100) | | Correo electrónico |
| `sexo_fun` | CHAR(1) | | M/F |
| `marcacion_fun` | VARCHAR(20) | | Clave de marcación alternativa |

### `acceso`
Relación usuario ↔ dependencias autorizadas (determina visibilidad).

| Campo | Tipo | PK/FK | Descripción |
|---|---|---|---|
| `id_usuario` | INT | PK, FK→usuario | Usuario |
| `id_dependencia` | INT | PK, FK→dependencia | Dependencia accesible |

---

## 3. Tablas organizacionales

### `dependencia`
Unidades organizativas internas (departamentos, divisiones, unidades).

| Campo | Tipo | PK/FK | Descripción |
|---|---|---|---|
| `id_dependencia` | INT | PK | Identificador |
| `desc_dependencia` | VARCHAR(150) | | Nombre de la dependencia |
| `sigla_dependencia` | VARCHAR(20) | | Sigla corta |
| `activa` | BIT | | Estado |

### `dependencia_externa`
Organizaciones externas (remitentes o destinatarios externos).

| Campo | Tipo | PK/FK | Descripción |
|---|---|---|---|
| `id_dependencia_externa` | INT | PK | Identificador |
| `desc_dependencia_externa` | VARCHAR(200) | | Nombre |
| `tipo` | VARCHAR(50) | | Tipo de organización |

---

## 4. Tablas de documentos (core)

### `documento`
Tabla central del sistema. Cada fila es un documento ingresado.

| Campo | Tipo | PK/FK | Descripción |
|---|---|---|---|
| `id_documento` | INT | PK | Identificador único |
| `id_tipo_documento` | INT | FK→tipo_documento | Tipo |
| `num_documento` | VARCHAR(50) | | Número/folio del documento |
| `asunto` | VARCHAR(300) | | Materia/asunto |
| `id_estado_documento` | INT | FK→estado_documento | Estado actual |
| `id_procedencia` | INT | FK→dependencia | Origen interno |
| `id_procedencia_externa` | INT | FK→dependencia_externa | Origen externo |
| `id_destino` | INT | FK→dependencia | Destino |
| `id_usuario` | INT | FK→usuario | Quien ingresó |
| `id_prioridad` | INT | FK→prioridad | Nivel de urgencia |
| `id_expediente` | INT | FK→expediente | Expediente (nullable) |
| `fecha_documento` | DATE | | Fecha del documento físico |
| `fecha_ingreso` | DATETIME | | Fecha de ingreso al sistema |
| `fecha_cierre` | DATETIME | | Fecha de cierre (nullable) |
| `observacion` | TEXT | | Notas adicionales |

### `tramite`
Registra cada derivación/asignación de un documento.

| Campo | Tipo | PK/FK | Descripción |
|---|---|---|---|
| `id_tramite` | INT | PK | Identificador |
| `id_documento` | INT | FK→documento | Documento derivado |
| `id_usuario_origen` | INT | FK→usuario | Quien derivó |
| `id_dependencia_destino` | INT | FK→dependencia | Destino |
| `id_funcionario_destino` | INT | FK→funcionario | Funcionario asignado |
| `id_estado_tramite` | INT | FK→estado_tramite | Estado actual |
| `fecha_derivacion` | DATETIME | | Cuándo se derivó |
| `fecha_cierre` | DATETIME | | Cuándo se cerró (nullable) |
| `observacion` | VARCHAR(500) | | Notas de la derivación |

### `historial_documento`
Log inmutable de todos los eventos de un documento.

| Campo | Tipo | PK/FK | Descripción |
|---|---|---|---|
| `id_historial` | INT | PK | Identificador |
| `id_documento` | INT | FK→documento | Documento |
| `id_usuario` | INT | FK→usuario | Actor |
| `accion` | VARCHAR(50) | | Tipo de acción |
| `observacion` | VARCHAR(500) | | Detalle |
| `fecha` | DATETIME | | Timestamp del evento |

---

## 5. Tablas de catálogos

### `tipo_documento`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_tipo_documento` | INT PK | Identificador |
| `desc_tipo_documento` | VARCHAR(100) | Descripción (Oficio, Memo, Circular…) |
| `activo` | BIT | Vigente |

### `estado_documento`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_estado_documento` | INT PK | Identificador |
| `desc_estado_documento` | VARCHAR(50) | Descripción |

### `estado_tramite`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_estado_tramite` | INT PK | Identificador |
| `desc_estado_tramite` | VARCHAR(50) | Descripción |

### `prioridad`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_prioridad` | INT PK | Identificador |
| `desc_prioridad` | VARCHAR(50) | Normal / Urgente / Muy Urgente |
| `color` | VARCHAR(10) | Color visual para la UI |

### `descriptor`
Categorías temáticas para clasificar documentos.

| Campo | Tipo | Descripción |
|---|---|---|
| `id_descriptor` | INT PK | Identificador |
| `desc_descriptor` | VARCHAR(100) | Nombre de la categoría |
| `activo` | BIT | Vigente |

### `descriptor_documento`
Relación N:N entre documento y descriptores.

| Campo | Tipo | Descripción |
|---|---|---|
| `id_documento` | INT FK | Documento |
| `id_descriptor` | INT FK | Descriptor |

---

## 6. Tablas de gestión de archivos

### `archivo_digital`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_archivo` | INT PK | Identificador |
| `id_documento` | INT FK | Documento vinculado |
| `nombre_archivo` | VARCHAR(200) | Nombre original |
| `ruta_archivo` | VARCHAR(500) | Ruta en filesystem |
| `tipo_mime` | VARCHAR(100) | Tipo de contenido |
| `tamano` | INT | Tamaño en bytes |
| `fecha_subida` | DATETIME | Timestamp |

---

## 7. Tablas de expedientes y compromisos

### `expediente`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_expediente` | INT PK | Identificador |
| `num_expediente` | VARCHAR(50) | Número único |
| `asunto_expediente` | VARCHAR(300) | Descripción del caso |
| `id_estado` | INT | Estado |
| `fecha_apertura` | DATE | Inicio |
| `fecha_cierre` | DATE | Cierre |

### `tipo_compromiso`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_tipo_compromiso` | INT PK | Identificador |
| `desc_tipo_compromiso` | VARCHAR(100) | Descripción |

### `estado_compromiso`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_estado_compromiso` | INT PK | Identificador |
| `desc_estado_compromiso` | VARCHAR(50) | Descripción |

---

## 8. Tablas auxiliares

### `tipo_distribucion`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_tipo_distribucion` | INT PK | Identificador |
| `desc_tipo_distribucion` | VARCHAR(100) | Descripción |

### `coordinadores`
| Campo | Tipo | Descripción |
|---|---|---|
| `id_coordinador` | INT PK | Identificador |
| `id_funcionario` | INT FK | Coordinador |
| `id_dependencia` | INT FK | Dependencia coordinada |

### `calendario`
| Campo | Tipo | Descripción |
|---|---|---|
| `fecha` | DATE PK | Fecha |
| `tipo_dia` | VARCHAR(20) | Hábil / Feriado / Fin de semana |

---

## 9. Índices recomendados para rendimiento

```sql
-- Búsqueda de documentos por estado
CREATE INDEX IX_documento_estado ON documento(id_estado_documento);

-- Documentos por dependencia
CREATE INDEX IX_documento_destino ON documento(id_destino);

-- Trámites activos por funcionario
CREATE INDEX IX_tramite_funcionario ON tramite(id_funcionario_destino, id_estado_tramite);

-- Historial por documento
CREATE INDEX IX_historial_documento ON historial_documento(id_documento, fecha DESC);

-- Búsqueda por número de documento
CREATE INDEX IX_documento_numero ON documento(num_documento);

-- Búsqueda full-text (opcional)
-- CREATE FULLTEXT INDEX ON documento(asunto, observacion);
```

---

## 10. Tablas candidatas a creación nueva

Para la arquitectura moderna se propone agregar:

| Tabla nueva | Propósito |
|---|---|
| `rol` | Roles del sistema (admin, funcionario, oirs…) |
| `usuario_rol` | Relación N:N usuario-rol |
| `notificacion` | Alertas y notificaciones por usuario |
| `refresh_token` | Tokens JWT de refresco para auth segura |
| `audit_log` | Log de auditoría de acciones sensibles |
| `configuracion` | Parámetros configurables del sistema |
