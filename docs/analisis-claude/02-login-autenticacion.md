# 02 — Login y Autenticación

**Fecha de análisis:** 2026-05-18  
**Módulo analizado:** Sistema de autenticación legacy + propuesta moderna

---

## 1. Flujo de autenticación legacy

### 1.1 Diagrama de flujo

```
Usuario → autentificacion.php → login.php → frame_variables/control.php
              (formulario)         (POST)         (validación BD)
                                                        ↓
                                              SELECT usuario + clave
                                                  (texto plano)
                                                        ↓
                                            Sesión por POST oculto
                                                        ↓
                                               carga_tablas.php
                                            (tablas maestras en RAM)
                                                        ↓
                                              frame_menuvars.php
                                                 (menú principal)
```

### 1.2 Código de autenticación legacy

```php
// frame_variables/control.php (simplificado)
$usuario    = $_POST['usuario'];
$contrasena = $_POST['clave'];

$query  = "SELECT * FROM usuario WHERE usuario='$usuario' AND clave='$contrasena'";
$result = mssql_query($query, $cn);
$row    = mssql_fetch_array($result);

if ($row) {
    // Variables propagadas por formularios POST ocultos
    $cusuario      = $row['usuario'];
    $idusuario     = $row['id_usuario'];
    $idfuncionario = $row['id_funcionario'];
    $flujo_ok      = 8; // tipo de flujo normal
}
```

### 1.3 Propagación de sesión (mecanismo crítico)

El sistema legacy **no usa sesiones PHP** (`$_SESSION`). En su lugar, propaga la identidad del usuario mediante campos `<input type="hidden">` en formularios encadenados:

```html
<!-- Cada página incluye este bloque -->
<input type="hidden" name="cusuario"      value="<?php echo $cusuario; ?>">
<input type="hidden" name="idusuario"     value="<?php echo $idusuario; ?>">
<input type="hidden" name="idfuncionario" value="<?php echo $idfuncionario; ?>">
<input type="hidden" name="flujo_ok"      value="<?php echo $flujo_ok; ?>">
```

**Riesgo crítico:** Cualquier usuario puede manipular estos campos en el cliente y suplantar la identidad de otro.

---

## 2. Estructura de tablas de autenticación

### Tabla `usuario`

| Campo | Tipo | Descripción |
|---|---|---|
| `id_usuario` | INT PK | Identificador |
| `usuario` | VARCHAR | Nombre de login |
| `clave` | VARCHAR | Contraseña en **texto plano** |
| `id_funcionario` | INT FK | Enlace a datos del funcionario |

### Tabla `funcionario`

| Campo | Tipo | Descripción |
|---|---|---|
| `rut_fun` | VARCHAR PK | RUT del funcionario |
| `nombres_fun` | VARCHAR | Nombres |
| `ap_pat_fun` | VARCHAR | Apellido paterno |
| `ap_mat_fun` | VARCHAR | Apellido materno |
| `id_dependencia` | INT FK | Dependencia principal |
| `email_fun` | VARCHAR | Correo electrónico |
| `sexo_fun` | CHAR | Sexo |
| `marcacion_fun` | VARCHAR | Clave alternativa de marcación |

### Tabla `acceso`

| Campo | Tipo | Descripción |
|---|---|---|
| `id_usuario` | INT FK | Usuario |
| `id_dependencia` | INT FK | Dependencia autorizada |

La tabla `acceso` es la que define el **alcance de visibilidad**: un usuario puede ver documentos de múltiples dependencias según sus registros en esta tabla.

---

## 3. Perfiles y roles (detectados por comportamiento)

El sistema legacy no tiene una tabla explícita de roles. Los perfiles se infieren por:

1. **Flujo (`flujo_ok`):** Valor numérico que determina el menú mostrado
   - `8` = flujo normal (usuario estándar)
   - Otros valores = flujos especializados (OIRS, Gabinete, Electoral)

2. **Accesos a dependencias:** La tabla `acceso` determina qué ve cada usuario

3. **Menús condicionales:** `frame_menuvars.php` renderiza opciones según `flujo_ok`

---

## 4. Vulnerabilidades críticas detectadas

| # | Vulnerabilidad | Severidad | Descripción |
|---|---|---|---|
| 1 | SQL Injection | CRÍTICA | Consulta sin parametrizar: `WHERE usuario='$usuario'` |
| 2 | Contraseñas en texto plano | CRÍTICA | Sin hash (MD5, bcrypt, Argon2) |
| 3 | Sin sesiones seguras | ALTA | Propagación por POST oculto, manipulable |
| 4 | Sin CSRF | ALTA | Formularios sin tokens anti-CSRF |
| 5 | Sin rate limiting | MEDIA | Permite fuerza bruta ilimitada |
| 6 | Sin bloqueo de cuenta | MEDIA | Sin política de intentos fallidos |
| 7 | Credenciales hardcodeadas | ALTA | BD user/pass en conexion_bd.php |

---

## 5. Propuesta de autenticación moderna

### 5.1 Stack recomendado

```
Frontend React → POST /api/auth/login → Backend Node.js
                                              ↓
                                    Validar usuario en BD
                                    bcrypt.compare(password, hash)
                                              ↓
                                    Generar JWT (access + refresh)
                                              ↓
                                    Retornar tokens al cliente
                                              ↓
                              Frontend almacena en httpOnly cookie
                                              ↓
                            Cada request incluye JWT en Authorization header
```

### 5.2 Implementación Node.js propuesta

```javascript
// backend/src/auth/authController.js
const bcrypt   = require('bcrypt');
const jwt      = require('jsonwebtoken');

async function login(req, res) {
  const { usuario, clave } = req.body;

  // Prepared statement - sin SQL injection
  const result = await pool.request()
    .input('usuario', sql.VarChar, usuario)
    .query('SELECT * FROM usuario WHERE usuario = @usuario');

  if (!result.recordset.length) {
    return res.status(401).json({ error: 'Credenciales inválidas' });
  }

  const user = result.recordset[0];
  const valid = await bcrypt.compare(clave, user.clave_hash);

  if (!valid) return res.status(401).json({ error: 'Credenciales inválidas' });

  const token = jwt.sign(
    { id: user.id_usuario, funcionario: user.id_funcionario },
    process.env.JWT_SECRET,
    { expiresIn: '8h' }
  );

  res.json({ token, usuario: user.usuario });
}
```

### 5.3 Middleware de autenticación

```javascript
// backend/src/middleware/auth.js
function requireAuth(req, res, next) {
  const token = req.headers.authorization?.split(' ')[1];
  if (!token) return res.status(401).json({ error: 'No autorizado' });

  try {
    req.user = jwt.verify(token, process.env.JWT_SECRET);
    next();
  } catch {
    res.status(401).json({ error: 'Token inválido o expirado' });
  }
}
```

### 5.4 Estrategia de migración de contraseñas

Para no forzar reset masivo de contraseñas:

1. En primer login exitoso con contraseña legacy (texto plano), hashear y guardar el nuevo hash
2. Agregar columna `clave_hash` (nullable) a la tabla `usuario`
3. Si `clave_hash` existe → verificar con bcrypt
4. Si no existe → verificar texto plano → hacer hash → guardar
5. Una vez migrados todos los usuarios, deprecar la columna `clave`

---

## 6. Sistema de roles y permisos propuesto

### 6.1 Roles recomendados

| Rol | Código | Descripción |
|---|---|---|
| Administrador | `admin` | Gestión completa del sistema |
| Jefe de Dependencia | `jefe` | Acceso a su dependencia + aprobaciones |
| Funcionario | `funcionario` | Operación estándar |
| OIRS | `oirs` | Módulo OIRS especializado |
| Oficina de Partes | `ofpartes` | Ingreso y timbraje |
| Gabinete | `gabinete` | Vista informes y estadísticas |
| Solo lectura | `lectura` | Consulta sin modificar |

### 6.2 Tabla propuesta `rol`

```sql
CREATE TABLE rol (
  id_rol    INT PRIMARY KEY IDENTITY,
  codigo    VARCHAR(50) UNIQUE NOT NULL,
  nombre    VARCHAR(100) NOT NULL,
  activo    BIT DEFAULT 1
);

CREATE TABLE usuario_rol (
  id_usuario INT NOT NULL,
  id_rol     INT NOT NULL,
  PRIMARY KEY (id_usuario, id_rol),
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
  FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);
```

---

## 7. Checklist de implementación

- [ ] Instalar dependencias: `bcrypt`, `jsonwebtoken`
- [ ] Crear endpoint `POST /api/auth/login`
- [ ] Crear endpoint `POST /api/auth/logout`
- [ ] Crear endpoint `GET /api/auth/me`
- [ ] Crear endpoint `POST /api/auth/refresh`
- [ ] Middleware `requireAuth` para rutas protegidas
- [ ] Middleware `requireRole` para control de acceso por rol
- [ ] Migración gradual de contraseñas (legacy → bcrypt)
- [ ] Crear tabla `rol` y `usuario_rol`
- [ ] Implementar contexto de autenticación en React (`AuthContext`)
- [ ] Crear página de login moderna
- [ ] Proteger rutas React con `PrivateRoute`
