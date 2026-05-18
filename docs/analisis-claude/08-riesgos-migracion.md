# 08 — Riesgos de Migración

**Fecha de análisis:** 2026-05-18  
**Estrategia:** Strangler Fig — migración gradual, sin cortar el sistema original

---

## 1. Mapa de riesgos

| ID | Categoría | Riesgo | Probabilidad | Impacto | Severidad |
|---|---|---|---|---|---|
| R01 | Datos | Corrupción de datos en migración | Baja | Crítico | Alta |
| R02 | Seguridad | SQL Injection en período de transición | Media | Crítico | Alta |
| R03 | Negocio | Pérdida de lógica de negocio implícita | Alta | Alto | Alta |
| R04 | Técnico | Incompatibilidad de SP entre PHP y Node | Media | Alto | Alta |
| R05 | Proceso | Resistencia al cambio de usuarios | Alta | Medio | Media |
| R06 | Técnico | Diferencias en manejo de fechas/tipos | Media | Medio | Media |
| R07 | Datos | Datos históricos sin migrar correctamente | Baja | Alto | Media |
| R08 | Técnico | Doble escritura fuera de sinc | Media | Alto | Alta |
| R09 | Negocio | Módulos sin documentar (reglas ocultas) | Alta | Alto | Alta |
| R10 | Infraestructura | Caída de Docker en producción | Baja | Crítico | Media |

---

## 2. Análisis detallado por riesgo

### R01 — Corrupción de datos en migración

**Descripción:** Al modificar la estructura de la BD para agregar columnas nuevas (ej: `clave_hash`), puede haber pérdida de datos existentes o conflictos de integridad.

**Mitigación:**
- Usar siempre `ALTER TABLE ADD COLUMN` (nunca DROP/RECREATE)
- Nuevas columnas NULLABLE para no romper INSERTs legacy
- Backup automático antes de cualquier ALTER
- Scripts de migración reversibles (con ROLLBACK documentado)
- Ambiente de staging con copia de la BD antes de aplicar en producción

**Protocolo:**
```sql
-- CORRECTO: agregar sin romper
ALTER TABLE usuario ADD clave_hash NVARCHAR(255) NULL;

-- PELIGROSO: nunca hacer esto en producción sin respaldo
-- ALTER TABLE usuario DROP COLUMN clave;
```

---

### R02 — SQL Injection en período de transición

**Descripción:** El sistema PHP legado tiene múltiples vectores de SQL Injection. Durante la coexistencia, ambos sistemas acceden a la misma BD. Un ataque exitoso al legacy afectaría también al nuevo sistema.

**Mitigación:**
- No exponer el legacy a Internet durante la migración (solo red interna)
- Agregar WAF (Web Application Firewall) frente al legacy
- Crear usuario de BD de solo lectura para el nuevo backend hasta que legacy esté fuera
- Sanitizar inputs en capa PHP aunque sea básico

---

### R03 — Pérdida de lógica de negocio implícita

**Descripción:** El sistema legacy tiene 20+ años de evolución. Muchas reglas de negocio están hardcodeadas en PHP y no están documentadas. Al reescribir, podemos omitir comportamientos críticos.

**Ejemplo de riesgo detectado:**
- El valor `flujo_ok = 8` determina el tipo de menú. No hay documentación de qué significa cada valor.
- El módulo `timbraje_ofpartes.php` probablemente genera números correlativos con lógica específica.
- Las alertas tienen criterios de tiempo (días hábiles) que dependen de la tabla `calendario`.

**Mitigación:**
- Análisis funcional por módulo antes de implementar (este documento es el inicio)
- Entrevistas con usuarios power del sistema actual
- Ejecutar ambos sistemas en paralelo y comparar resultados
- Casos de prueba basados en datos reales (anonimizados)
- Documentar cada módulo antes de reescribirlo

---

### R04 — Incompatibilidad de SP entre PHP y Node.js

**Descripción:** Los stored procedures fueron escritos para ser llamados desde `mssql_*` de PHP. Pueden tener comportamientos inesperados al llamarlos desde Node.js con el driver `mssql`.

**Diferencias conocidas:**
- Manejo de parámetros NULL
- Encoding de strings (Latin1 vs UTF-8)
- Retorno de múltiples resultsets
- Manejo de OUTPUT parameters

**Mitigación:**
- Probar cada SP desde Node.js antes de usarlo en producción
- Documentar parámetros y outputs esperados
- Escribir tests de integración para cada SP crítico

```javascript
// Prueba básica de SP desde Node.js
const result = await pool.request()
  .input('rut', sql.VarChar(12), '12345678')
  .execute('ingreso_usuario_funcionario');

console.assert(result.returnValue === 0, 'SP debe retornar 0 en éxito');
```

---

### R05 — Resistencia al cambio de usuarios

**Descripción:** Los funcionarios llevan años usando el sistema legacy. Un cambio brusco de interfaz puede generar rechazo, errores operativos y pérdida de productividad.

**Mitigación:**
- Migración gradual módulo por módulo (no big bang)
- Capacitación antes de lanzar cada módulo
- Período de doble operación (legacy + nuevo en paralelo)
- Feedback continuo durante la transición
- Diseño UX intuitivo que reduzca la curva de aprendizaje
- Mantener misma terminología (no cambiar nombres de conceptos)

---

### R06 — Diferencias en manejo de fechas y tipos

**Descripción:** SQL Server 2005 vs 2022 puede tener diferencias en tipos de datos, precisión de fechas y comportamiento de funciones.

**Riesgos específicos:**
- `GETDATE()` vs `SYSDATETIME()` (diferente precisión)
- Tipos `TEXT`/`NTEXT` obsoletos en SQL Server 2022
- Collation differences (Latin1_General vs Modern_Spanish)
- Formato de fechas en PHP vs JavaScript

**Mitigación:**
- Usar siempre `DATETIME2` para nuevas columnas
- Normalizar fechas en UTC en el backend, formatear en frontend
- Verificar collation de la BD restaurada
- Tests de fechas en casos límite (fin de año, feriados)

---

### R07 — Datos históricos sin migrar correctamente

**Descripción:** El historial documental de años puede tener inconsistencias, registros huérfanos (FKs rotas) o datos en formatos legacy.

**Mitigación:**
- Script de validación de integridad referencial antes de comenzar
- Limpieza de datos en ambiente de staging
- No migrar datos históricamente inconsistentes sin análisis previo

```sql
-- Verificar FKs rotas (ejemplo)
SELECT d.id_documento
FROM documento d
LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento
WHERE td.id_tipo_documento IS NULL;
```

---

### R08 — Doble escritura fuera de sincronía

**Descripción:** Durante la transición, tanto el sistema legacy como el nuevo pueden estar escribiendo en la misma BD. Esto puede generar conflictos de numeración, estados inconsistentes o deadlocks.

**Mitigación:**
- Preferir que el nuevo sistema sea primero solo lectura
- Cuando el nuevo sistema escribe, el legacy solo lee ese módulo
- Nunca dos sistemas escribiendo el mismo registro simultáneamente
- Usar transacciones con niveles de aislamiento adecuados
- Monitorear deadlocks con SQL Server Profiler

**Estrategia de transición por módulo:**
```
1. Nuevo sistema: solo lectura del módulo X
2. Nuevo sistema: escritura del módulo X + legacy como fallback
3. Legacy: desactivado para módulo X
4. Nuevo sistema: único para módulo X
```

---

### R09 — Módulos sin documentar

**Descripción:** Se detectaron módulos especializados (OIRS, Gabinete, Electoral, Bienestar) con lógica propia que no está documentada. Reescribirlos sin entender las reglas puede generar errores críticos.

**Módulos de riesgo alto:**
- `timbraje_ofpartes.php` — generación de números correlativos
- `sisdoc_alertas/` — lógica de días hábiles y vencimientos
- `gabinete/` — reportes específicos del gabinete directivo
- `bienestar/` — lógica de cargas familiares y beneficios

**Mitigación:**
- Documentar cada módulo antes de tocar código
- Ejecutar el legacy y observar comportamiento
- Entrevistar al usuario administrador del sistema
- Dejar estos módulos para fases posteriores de la migración

---

### R10 — Caída de Docker en producción

**Descripción:** SQL Server corre en Docker. Si el contenedor se cae o el volumen de datos se corrompe, hay riesgo de pérdida total de datos.

**Mitigación:**
- Configurar backups automáticos de SQL Server
- Volume con persistencia explícita (no bind mount)
- Health checks en docker-compose
- Script de restore documentado y probado regularmente
- En producción: considerar SQL Server nativo o Azure SQL en lugar de Docker

```yaml
# docker-compose.yml recomendado
services:
  sqlserver:
    healthcheck:
      test: ["CMD", "/opt/mssql-tools/bin/sqlcmd",
             "-S", "localhost", "-U", "sa",
             "-P", "${SA_PASSWORD}", "-Q", "SELECT 1"]
      interval: 30s
      timeout: 10s
      retries: 3
    restart: unless-stopped
```

---

## 3. Reglas de oro para la migración

1. **Nunca modificar /legacy** — El sistema original es la fuente de verdad
2. **Siempre hacer backup** antes de cualquier cambio en BD
3. **Un módulo a la vez** — No intentar migrar todo simultáneamente
4. **Documentar primero** — Entender antes de reescribir
5. **Tests antes de deploy** — Cada endpoint con test de integración
6. **Rollback siempre disponible** — Cada cambio debe poder revertirse
7. **Usuarios como aliados** — Involucrarlos en el proceso, no sorprenderlos
8. **Monitoreo continuo** — Logs, alertas y métricas desde el día 1

---

## 4. Checklist de seguridad pre-deployment

```
ANTES DE CADA DEPLOYMENT:
[ ] Backup de BD completado y verificado
[ ] Tests unitarios y de integración pasando
[ ] Variables de entorno revisadas (sin credenciales hardcodeadas)
[ ] CORS configurado correctamente (solo orígenes autorizados)
[ ] Rate limiting activo en endpoints de autenticación
[ ] Headers de seguridad HTTP configurados
[ ] Logs de errores sin exponer stack traces en producción
[ ] Contraseñas de BD rotadas del default
[ ] Puertos no necesarios cerrados (solo 3001 y 3000 expuestos)
[ ] Archivos .env excluidos del repositorio (.gitignore)
```

---

## 5. Plan de contingencia

Si algo sale mal durante la migración:

1. **Revertir al legacy** — El sistema PHP original sigue operativo
2. **Restaurar BD desde backup** — Script documentado en `/scripts/restore.sql`
3. **Desactivar nuevo sistema** — Sin interrumpir el legacy
4. **Post-mortem** — Documentar qué falló y cómo se previene

**Tiempo de recuperación objetivo (RTO):** < 30 minutos  
**Punto de recuperación objetivo (RPO):** < 24 horas (backup diario mínimo)
