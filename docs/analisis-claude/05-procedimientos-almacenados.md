# 05 — Procedimientos Almacenados

**Fecha de análisis:** 2026-05-18  
**Motor:** SQL Server 2022 (Docker)

---

## 1. Procedimientos identificados en el código legacy

### 1.1 Resumen

| SP | Módulo | Función |
|---|---|---|
| `ingreso_usuario` | adm | Crea usuario en BD corporativa |
| `ingreso_usuario_funcionario` | adm | Crea usuario en SISDOC |
| `modifica_usuario_funcionario` | adm | Actualiza datos de usuario |
| `busca_doc_referencia` | core | Obtiene referencias de documento |
| `documento_referencia` | core | Alternativa para referencias |
| `SP_busca_alertas_vigente` | alertas | Genera listado de alertas vigentes |

---

## 2. Detalle de cada procedimiento

### 2.1 `ingreso_usuario`

**Módulo:** Administración de usuarios  
**Origen:** `adm/adm_graba_usuario.php`  
**Base de datos:** BD Corporativa (externa a SISDOC)

**Llamada desde PHP:**
```php
$query = "exec ingreso_usuario '$rut','$dv','$nombre','$apellido','$email',...'$op'";
$result = mssql_query($query, $cn_corporativo);
```

**Parámetros:**
- `@rut` — RUT del funcionario
- `@dv` — Dígito verificador
- `@nombre` — Nombres
- `@apellido` — Apellido paterno
- `@email` — Correo electrónico
- `@op` — Operación (I=insertar, M=modificar)

**Función:** Registra o actualiza al usuario en la base de datos corporativa institucional (sistema externo). Es el primer paso del proceso de alta de usuarios.

---

### 2.2 `ingreso_usuario_funcionario`

**Módulo:** Administración de usuarios  
**Origen:** `adm/adm_graba_usuario.php`  
**Base de datos:** SISDOC

**Llamada desde PHP:**
```php
$query = "exec ingreso_usuario_funcionario '$rut','$dv','$nombre','$apellido',...";
$result = mssql_query($query, $cn_sisdoc);
```

**Parámetros:**
- `@rut` — RUT del funcionario
- `@dv` — Dígito verificador
- `@nombres` — Nombres completos
- `@ap_pat` — Apellido paterno
- `@ap_mat` — Apellido materno
- `@email` — Correo
- `@id_dependencia` — Dependencia asignada
- `@sexo` — M/F
- `@usuario` — Login elegido
- `@clave` — Contraseña inicial

**Función:** Crea el registro en tablas `funcionario` y `usuario` dentro de SISDOC. Segundo paso del alta de usuario.

**Lógica interna inferida:**
```sql
-- Pseudocódigo del SP (reconstruido)
BEGIN TRANSACTION
  INSERT INTO funcionario (rut_fun, dv_fun, nombres_fun, ap_pat_fun, ap_mat_fun,
                           email_fun, id_dependencia, sexo_fun)
  VALUES (@rut, @dv, @nombres, @ap_pat, @ap_mat, @email, @id_dependencia, @sexo);

  SET @id_funcionario = SCOPE_IDENTITY();

  INSERT INTO usuario (usuario, clave, id_funcionario)
  VALUES (@usuario, @clave, @id_funcionario);
COMMIT;
```

---

### 2.3 `modifica_usuario_funcionario`

**Módulo:** Administración de usuarios  
**Origen:** `adm/adm_graba_usuario.php` (modo edición)

**Función:** Actualiza los datos de un funcionario y usuario existente.

**Lógica interna inferida:**
```sql
-- Pseudocódigo del SP (reconstruido)
UPDATE funcionario
SET nombres_fun = @nombres, ap_pat_fun = @ap_pat, ap_mat_fun = @ap_mat,
    email_fun = @email, id_dependencia = @id_dependencia
WHERE rut_fun = @rut;

UPDATE usuario
SET usuario = @usuario
WHERE id_funcionario = (SELECT id_funcionario FROM funcionario WHERE rut_fun = @rut);
```

---

### 2.4 `busca_doc_referencia`

**Módulo:** Core documental  
**Origen:** `ingreso_docto2.php`, otros archivos de ingreso

**Función:** Retorna documentos que pueden ser referenciados al ingresar un nuevo documento (vinculación de documentos relacionados).

**Llamada:**
```php
$query  = "exec busca_doc_referencia '$texto_busqueda'";
$result = mssql_query($query);
```

**Output esperado:**
```
id_documento | num_documento | asunto | fecha_ingreso | desc_tipo_documento
```

---

### 2.5 `documento_referencia`

**Módulo:** Core documental  
**Función:** Alternativa o versión evolucionada de `busca_doc_referencia`. Probablemente con parámetros adicionales de filtro.

---

### 2.6 `SP_busca_alertas_vigente`

**Módulo:** Sistema de alertas (`/sisdoc_alertas/`)  
**Origen:** `sisdoc_alertas/alertas.php`  
**Archivo SQL:** `sisdoc_alertas/SP_busca_alertas_vigente.sql`

**Función:** Recupera todas las alertas activas y vigentes para el usuario o dependencia activos.

**Parámetros probables:**
- `@id_usuario`
- `@id_dependencia`
- `@fecha_actual`

**Criterios de alerta:**
- Documentos sin respuesta después de N días hábiles
- Trámites vencidos o próximos a vencer
- Documentos derivados sin recepción confirmada

---

## 3. Consultas SQL embebidas críticas (no como SP)

El sistema legacy usa mayoritariamente SQL embebido en PHP. Las más críticas:

### 3.1 Autenticación

```sql
-- PELIGROSA: sin preparar, vulnerable a SQL Injection
SELECT * FROM usuario WHERE usuario='$usuario' AND clave='$contrasena'
```

### 3.2 Listado de documentos por dependencia

```sql
SELECT d.id_documento, d.num_documento, d.asunto,
       td.desc_tipo_documento, ed.desc_estado_documento,
       dep.desc_dependencia, f.nombres_fun
FROM documento d
JOIN tipo_documento td   ON d.id_tipo_documento = td.id_tipo_documento
JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
JOIN dependencia dep      ON d.id_procedencia = dep.id_dependencia
JOIN tramite t            ON d.id_documento = t.id_documento
JOIN funcionario f        ON t.id_funcionario_destino = f.id_funcionario
WHERE t.id_dependencia_destino = '$id_dep'
  AND d.id_estado_documento != 5
ORDER BY d.fecha_ingreso DESC
```

### 3.3 Ingreso de documento

```sql
INSERT INTO documento (id_tipo_documento, num_documento, asunto,
                       id_estado_documento, id_procedencia, id_destino,
                       id_usuario, id_prioridad, fecha_documento, fecha_ingreso)
VALUES ('$tipo','$num','$asunto',1,'$procedencia','$destino',
        '$usuario','$prioridad','$fecha_doc',GETDATE())
```

---

## 4. Propuesta de stored procedures modernos

Se recomienda crear SP en SQL Server para operaciones complejas. Ventajas:
- Plan de ejecución cacheado (rendimiento)
- Lógica de negocio centralizada
- Más fácil de auditar y mantener

### 4.1 SP propuesto: `sp_BuscarDocumentos`

```sql
CREATE PROCEDURE sp_BuscarDocumentos
  @texto           NVARCHAR(200) = NULL,
  @id_tipo         INT           = NULL,
  @id_estado       INT           = NULL,
  @id_dependencia  INT           = NULL,
  @fecha_desde     DATE          = NULL,
  @fecha_hasta     DATE          = NULL,
  @pagina          INT           = 1,
  @tam_pagina      INT           = 20
AS
BEGIN
  SET NOCOUNT ON;

  DECLARE @offset INT = (@pagina - 1) * @tam_pagina;

  SELECT
    d.id_documento, d.num_documento, d.asunto,
    td.desc_tipo_documento,
    ed.desc_estado_documento,
    dep.desc_dependencia AS origen,
    p.desc_prioridad,
    d.fecha_ingreso,
    d.fecha_cierre,
    COUNT(*) OVER() AS total_registros
  FROM documento d
  JOIN tipo_documento td   ON d.id_tipo_documento = td.id_tipo_documento
  JOIN estado_documento ed ON d.id_estado_documento = ed.id_estado_documento
  JOIN dependencia dep      ON d.id_procedencia = dep.id_dependencia
  LEFT JOIN prioridad p     ON d.id_prioridad = p.id_prioridad
  WHERE
    (@texto IS NULL OR d.asunto LIKE '%' + @texto + '%'
                    OR d.num_documento LIKE '%' + @texto + '%')
    AND (@id_tipo IS NULL OR d.id_tipo_documento = @id_tipo)
    AND (@id_estado IS NULL OR d.id_estado_documento = @id_estado)
    AND (@id_dependencia IS NULL OR d.id_destino = @id_dependencia)
    AND (@fecha_desde IS NULL OR d.fecha_ingreso >= @fecha_desde)
    AND (@fecha_hasta IS NULL OR d.fecha_ingreso <= @fecha_hasta)
  ORDER BY d.fecha_ingreso DESC
  OFFSET @offset ROWS FETCH NEXT @tam_pagina ROWS ONLY;
END;
```

### 4.2 SP propuesto: `sp_IngresarDocumento`

```sql
CREATE PROCEDURE sp_IngresarDocumento
  @id_tipo_documento  INT,
  @num_documento      VARCHAR(50),
  @asunto             VARCHAR(300),
  @id_procedencia     INT,
  @id_destino         INT,
  @id_usuario         INT,
  @id_prioridad       INT,
  @fecha_documento    DATE,
  @observacion        VARCHAR(500) = NULL,
  @id_documento_nuevo INT OUTPUT
AS
BEGIN
  SET NOCOUNT ON;
  BEGIN TRANSACTION;

  INSERT INTO documento (
    id_tipo_documento, num_documento, asunto,
    id_estado_documento, id_procedencia, id_destino,
    id_usuario, id_prioridad, fecha_documento,
    fecha_ingreso, observacion
  ) VALUES (
    @id_tipo_documento, @num_documento, @asunto,
    1, @id_procedencia, @id_destino,
    @id_usuario, @id_prioridad, @fecha_documento,
    GETDATE(), @observacion
  );

  SET @id_documento_nuevo = SCOPE_IDENTITY();

  INSERT INTO historial_documento (id_documento, id_usuario, accion, fecha)
  VALUES (@id_documento_nuevo, @id_usuario, 'INGRESADO', GETDATE());

  COMMIT;
END;
```

### 4.3 SP propuesto: `sp_DerivarDocumento`

```sql
CREATE PROCEDURE sp_DerivarDocumento
  @id_documento          INT,
  @id_usuario_origen     INT,
  @id_dependencia_destino INT,
  @id_funcionario_destino INT,
  @observacion           VARCHAR(500) = NULL
AS
BEGIN
  SET NOCOUNT ON;
  BEGIN TRANSACTION;

  INSERT INTO tramite (
    id_documento, id_usuario_origen, id_dependencia_destino,
    id_funcionario_destino, id_estado_tramite, fecha_derivacion, observacion
  ) VALUES (
    @id_documento, @id_usuario_origen, @id_dependencia_destino,
    @id_funcionario_destino, 1, GETDATE(), @observacion
  );

  UPDATE documento
  SET id_estado_documento = 3  -- Derivado
  WHERE id_documento = @id_documento;

  INSERT INTO historial_documento (id_documento, id_usuario, accion, observacion, fecha)
  VALUES (@id_documento, @id_usuario_origen, 'DERIVADO', @observacion, GETDATE());

  COMMIT;
END;
```

---

## 5. Estrategia de migración de consultas SQL

| Fase | Acción | Detalle |
|---|---|---|
| 1 | Auditar todas las consultas SQL en PHP | Identificar todas las queries embebidas |
| 2 | Clasificar por complejidad | Simple (SELECT básico) vs compleja (JOINs múltiples) |
| 3 | Convertir a prepared statements en Node.js | Usar `mssql` con `.input()` |
| 4 | Crear SP para operaciones complejas | Búsquedas paginadas, reportes |
| 5 | Agregar índices donde falten | Analizar query plans |
| 6 | Implementar caché en capa Node.js | Para catálogos estáticos |
