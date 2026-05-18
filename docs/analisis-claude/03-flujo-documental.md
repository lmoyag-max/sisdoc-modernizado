# 03 — Flujo Documental

**Fecha de análisis:** 2026-05-18  
**Módulo analizado:** Gestión de documentos, trámites, derivaciones y expedientes

---

## 1. Ciclo de vida de un documento

```
┌─────────────────────────────────────────────────────────────────┐
│                    CICLO VIDA DOCUMENTO SISDOC                  │
│                                                                 │
│  INGRESO          DERIVACIÓN         SEGUIMIENTO      CIERRE   │
│                                                                 │
│  ingreso_docto ──► derivar_con_docto ──► tramites ──► despachar│
│      ↓                  ↓                  ↓              ↓    │
│  tabla DOCUMENTO   tabla TRAMITE    historial_docto  estado=5  │
│  estado=1 (nuevo)  estado=3 (deriv) consulta estado  cerrado   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Estados del flujo

### 2.1 Estados del documento (`estado_documento`)

| ID | Descripción |
|---|---|
| 1 | Nuevo / Pendiente |
| 2 | Recepcionado |
| 3 | Derivado |
| 4 | En proceso |
| 5 | Despachado / Cerrado |

### 2.2 Estados del trámite (`estado_tramite`)

| ID | Descripción |
|---|---|
| 1 | Pendiente |
| 2 | En proceso |
| 3 | Completado |
| 4 | Rechazado |
| 5 | Archivado |

---

## 3. Fase 1: Ingreso de documentos

### 3.1 Archivos involucrados

| Archivo | Descripción |
|---|---|
| `ingreso_docto2.php` | Ingreso con derivación y archivo adjunto |
| `ingreso_docto1.php` | Ingreso sin archivo físico |
| `ingreso_ofpartes_k.php` | Ingreso vía oficina de partes |
| `ingreso_docto_oirs.php` | Ingreso OIRS |

### 3.2 Datos capturados en el ingreso

```
Datos del documento:
- Tipo de documento (FK tipo_documento)
- Descriptores / categorías (FK descriptor)
- Fecha del documento
- Número de documento (folio)
- Asunto / materia
- Prioridad (FK prioridad)
- Procedencia: interna (dependencia) o externa (dependencia_externa)
- Destino: dependencia destino + funcionario asignado
- Archivo digital adjunto (opcional)

Generado automáticamente:
- id_documento (PK autoincremental)
- id_estado_documento = 1 (nuevo)
- id_usuario (quién ingresó)
- fecha_ingreso (timestamp)
```

### 3.3 Consulta SQL de ingreso (simplificada)

```sql
INSERT INTO documento (
  id_tipo_documento, id_descriptor, fecha_documento,
  num_documento, asunto, id_prioridad,
  id_procedencia, id_destino, id_usuario,
  id_estado_documento, fecha_ingreso
) VALUES (
  @tipo, @descriptor, @fecha,
  @num, @asunto, @prioridad,
  @procedencia, @destino, @usuario,
  1, GETDATE()
);
```

---

## 4. Fase 2: Derivación

### 4.1 Archivos involucrados

| Archivo | Descripción |
|---|---|
| `derivar_con_docto.php` | Derivación simple con archivo |
| `derivar_multiple_con_docto.php` | Derivación a múltiples destinos |
| `responder_con_docto.php` | Responder una derivación recibida |

### 4.2 Lógica de derivación

```
1. Usuario selecciona documento (por id_documento)
2. Selecciona dependencia/funcionario destino
3. Opcionalmente adjunta archivo
4. Sistema:
   a. Crea registro en tabla TRAMITE
   b. Actualiza id_estado_documento = 3 (derivado)
   c. Registra en historial_documento
```

### 4.3 Consulta SQL de derivación

```sql
-- Crear trámite
INSERT INTO tramite (
  id_documento, id_usuario_origen, id_dependencia_destino,
  id_funcionario_destino, id_estado_tramite, fecha_derivacion
) VALUES (
  @id_doc, @usuario_origen, @dep_destino,
  @func_destino, 1, GETDATE()
);

-- Actualizar estado documento
UPDATE documento
SET id_estado_documento = 3
WHERE id_documento = @id_doc;

-- Registrar en historial
INSERT INTO historial_documento (
  id_documento, id_usuario, accion, fecha
) VALUES (
  @id_doc, @usuario, 'DERIVADO', GETDATE()
);
```

---

## 5. Fase 3: Búsqueda y seguimiento

### 5.1 Archivos involucrados

| Archivo | Descripción |
|---|---|
| `busca_docto.php` | Búsqueda general de documentos |
| `busca_docto_oirs.php` | Búsqueda módulo OIRS |
| `busca_doctogab.php` | Búsqueda desde gabinete |
| `tramites.php` | Vista de mis trámites activos |
| `historial_docto.php` | Historial completo de un documento |

### 5.2 Filtros de búsqueda disponibles

- Por número de documento
- Por tipo de documento
- Por descriptor/categoría
- Por rango de fechas
- Por estado
- Por dependencia de origen/destino
- Por funcionario asignado
- Por asunto (texto libre)

### 5.3 Consulta de seguimiento

```sql
SELECT 
  d.id_documento, d.num_documento, d.asunto,
  td.desc_tipo_documento,
  ed.desc_estado_documento,
  dep_orig.desc_dependencia AS origen,
  dep_dest.desc_dependencia AS destino,
  t.id_estado_tramite,
  et.desc_estado_tramite,
  f.nombres_fun + ' ' + f.ap_pat_fun AS funcionario_asignado,
  t.fecha_derivacion
FROM documento d
JOIN tipo_documento td  ON d.id_tipo_documento = td.id_tipo_documento
JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
JOIN dependencia dep_orig ON d.id_procedencia = dep_orig.id_dependencia
LEFT JOIN tramite t ON d.id_documento = t.id_documento
LEFT JOIN estado_tramite et ON t.id_estado_tramite = et.id_estado_tramite
LEFT JOIN funcionario f ON t.id_funcionario_destino = f.id_funcionario
LEFT JOIN dependencia dep_dest ON t.id_dependencia_destino = dep_dest.id_dependencia
WHERE d.id_estado_documento != 5  -- Excluir cerrados
ORDER BY d.fecha_ingreso DESC;
```

---

## 6. Fase 4: Cierre y despacho

### 6.1 Archivos involucrados

| Archivo | Descripción |
|---|---|
| `despachar.php` | Marcar documento como despachado |
| `cierra_tramite.php` | Cerrar trámite específico |
| `timbraje_ofpartes.php` | Timbraje y numeración oficial |

### 6.2 Proceso de cierre

```sql
UPDATE documento
SET id_estado_documento = 5,
    fecha_cierre = GETDATE()
WHERE id_documento = @id_doc;

UPDATE tramite
SET id_estado_tramite = 3,
    fecha_cierre = GETDATE()
WHERE id_tramite = @id_tramite;

INSERT INTO historial_documento (id_documento, id_usuario, accion, fecha)
VALUES (@id_doc, @usuario, 'CERRADO', GETDATE());
```

---

## 7. Gestión de archivos digitales

### 7.1 Tabla `archivo_digital`

| Campo | Descripción |
|---|---|
| `id_archivo` | PK |
| `id_documento` | FK al documento |
| `nombre_archivo` | Nombre original del archivo |
| `ruta_archivo` | Ruta física en el servidor |
| `tipo_mime` | Tipo de contenido |
| `fecha_subida` | Timestamp |

### 7.2 Comportamiento actual

- Archivos almacenados directamente en filesystem del servidor
- Sin gestión de versiones de archivos
- Sin preview de documentos en el navegador
- Sin conversión a PDF automática

---

## 8. Expedientes

### 8.1 Tabla `expediente`

Los expedientes agrupan múltiples documentos en un caso o asunto mayor.

| Campo | Descripción |
|---|---|
| `id_expediente` | PK |
| `num_expediente` | Número identificador |
| `asunto_expediente` | Descripción del caso |
| `id_estado` | Estado del expediente |
| `fecha_apertura` | Inicio |
| `fecha_cierre` | Cierre (nullable) |

### 8.2 Relación expediente-documento

Un expediente puede contener N documentos. Los documentos pueden pertenecer a un expediente (opcional).

---

## 9. Historial documental

### 9.1 Tabla `historial_documento`

Registro de todos los eventos de un documento:

| Campo | Descripción |
|---|---|
| `id_historial` | PK |
| `id_documento` | FK |
| `id_usuario` | Quién realizó la acción |
| `accion` | Descripción de la acción |
| `fecha` | Timestamp del evento |
| `observacion` | Notas adicionales |

### 9.2 Acciones registradas

- INGRESADO
- DERIVADO
- RECEPCIONADO
- RESPONDIDO
- REASIGNADO
- CERRADO
- MODIFICADO
- ARCHIVADO

---

## 10. Flujo documental moderno propuesto

```
                    ┌──────────────────────────────┐
                    │     NUEVA ARQUITECTURA        │
                    │                              │
                    │  React SPA ──► API REST      │
                    │                 Node.js       │
                    │                  ↓            │
                    │              SQL Server       │
                    └──────────────────────────────┘

ENDPOINTS PROPUESTOS:

POST   /api/documentos              → Crear documento
GET    /api/documentos              → Listar documentos (con filtros)
GET    /api/documentos/:id          → Obtener documento
PATCH  /api/documentos/:id          → Actualizar estado/datos
DELETE /api/documentos/:id          → Archivar (soft delete)

POST   /api/documentos/:id/derivar  → Crear derivación
GET    /api/documentos/:id/tramites → Trámites del documento
GET    /api/documentos/:id/historial→ Historial completo

POST   /api/expedientes             → Crear expediente
GET    /api/expedientes             → Listar expedientes
POST   /api/expedientes/:id/documentos → Agregar documento a expediente

POST   /api/archivos                → Subir archivo (multipart)
GET    /api/archivos/:id            → Descargar archivo

GET    /api/busqueda?q=...          → Búsqueda full-text
```

---

## 11. Prioridades de implementación

| Prioridad | Módulo | Esfuerzo |
|---|---|---|
| 1 | API CRUD documentos | Alto |
| 2 | Flujo de derivación API | Alto |
| 3 | Búsqueda y filtros | Medio |
| 4 | Historial y trazabilidad | Medio |
| 5 | Gestión de archivos digitales | Alto |
| 6 | Expedientes | Medio |
| 7 | Dashboard y métricas | Bajo |
| 8 | Alertas y notificaciones | Medio |
