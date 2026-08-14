# Guion de Demostración Ejecutiva — DOC360

**Duración estimada:** 15–20 minutos
**Audiencia:** Directivos del Hospital de Urgencia Asistencia Pública (HUAP)
**Ambiente:** `http://localhost:5173` (o la IP de red local del equipo presentador)
**Precondición:** backend y frontend corriendo (`docker compose up -d sqlserver`, backend y frontend con `npm run dev`), datos demo ya cargados (ver `INFORME_PREPARACION_DEMO_DOC360.md`).

> Todos los usuarios de esta demo son ficticios (`@demo.invalid`, contraseña `Demo2026`). Ningún nombre, documento ni dato corresponde a personas o hechos reales del hospital.

---

## 1. Inicio de sesión (1 min)

- **Usuario:** `admin` / (contraseña institucional real, no compartir en pantalla)
- **Qué mostrar:** pantalla de login con el branding institucional (logo y fondo configurables desde el propio sistema).
- **Mensaje:** "DOC360 reemplaza el sistema SISDOC de 2003 con una plataforma moderna, con roles y permisos diferenciados por servicio — lo que van a ver ahora es exactamente lo que vería un funcionario real, con datos ficticios para efectos de esta demostración."
- **Resultado en pantalla:** redirección automática al Dashboard tras login exitoso.

## 2. Presentación del dashboard (2 min)

- **Usuario:** `admin` (visión completa de todos los servicios).
- **Qué mostrar:** `/dashboard` — tarjetas de métricas (total documentos, por estado, urgentes, reservados), gráfico de evolución mensual, gráfico por servicio, actividad reciente.
- **Mensaje:** "En este momento el sistema tiene 158 documentos activos repartidos en 27 servicios distintos, con casi 5 meses de historial simulado — esto es lo que verían ustedes como panorama ejecutivo del flujo documental del hospital completo."
- **Resultado esperado:** gráficos poblados, ningún estado en cero, distribución visualmente repartida entre servicios (no concentrada en uno solo).

## 3. Explicación de indicadores (1–2 min)

- **Qué mostrar:** pasar el cursor sobre cada tarjeta/gráfico — "Documentos por estado" (Despachado/Recepcionado/Terminado), "Documentos urgentes" (14), "Documentos reservados" (4), "Compromisos vencidos vs. vigentes".
- **Mensaje:** "Cada uno de estos indicadores es clickeable y lleva al listado filtrado correspondiente — no es una foto estática, es la base de datos real en este instante."

## 4. Creación de un documento (2 min)

- **Usuario:** cerrar sesión, entrar como `imorales` / `Demo2026` (jefatura ficticia de **Servicio Clínico de Urgencia**).
- **Qué mostrar:** `Documentos → Nuevo documento`. Crear uno nuevo: tipo "Solicitud Anticipo/Transferencias" (o cualquiera del catálogo), materia libre p. ej. *"Solicitud de insumos para turno de fin de semana"*, destino **Abastecimiento**.
- **Mensaje:** "Cualquier funcionario autenticado puede generar un documento — el número correlativo se asigna de forma transaccional, sin riesgo de duplicados aunque dos personas lo hagan al mismo tiempo."
- **Resultado esperado:** documento creado, redirección al detalle, estado inicial "Despachado".

## 5. Despacho a otro servicio (1 min)

- Ya cubierto por la creación del punto 4 (todo documento nuevo queda despachado a su destino automáticamente). Mostrar en el detalle del documento el destino asignado (**Abastecimiento**) y el estado "Despachado".
- **Mensaje:** "El documento ya está en la bandeja de entrada de Abastecimiento — vamos a cambiar de usuario para mostrar cómo lo reciben."

## 6. Recepción (1–2 min)

- **Usuario:** cerrar sesión, entrar como `sgodoy` / `Demo2026` (jefatura ficticia de **Abastecimiento**).
- **Qué mostrar:** `Bandeja` — el documento recién creado aparece ahí. Abrirlo y presionar "Recepcionar".
- **Mensaje:** "Cada servicio ve solamente los documentos que le corresponden — esto no es un buzón compartido, hay control de acceso real por dependencia."
- **Resultado esperado:** estado cambia a "Recepcionado", aparece en el detalle quién y cuándo lo recibió.

## 7. Derivación (1–2 min)

- **Mismo usuario** (`sgodoy`, Abastecimiento). En el detalle del documento recepcionado, usar "Derivar" hacia **Finanzas**.
- **Mensaje:** "Si el trámite requiere la intervención de otro servicio, se deriva sin perder el historial — el documento original nunca se duplica ni se pierde."
- **Resultado esperado:** nuevo movimiento de trazabilidad, documento vuelve a estado "Despachado" con nuevo destino.

## 8. Seguimiento de trazabilidad (2 min)

- **Qué mostrar:** en el mismo documento, pestaña/sección "Trazabilidad" o "Historial" — la línea de tiempo completa: creación → despacho → recepción → derivación, con fecha, hora, usuario y servicio de cada paso.
- **Mensaje:** "Esto es lo que en SISDOC 2003 era imposible de reconstruir de forma confiable — acá cada paso queda inmutable, con usuario, fecha y hora exactos."

## 9. Consulta de un documento urgente (1–2 min)

- **Usuario:** volver a `admin` (o `imorales`, Urgencia).
- **Qué mostrar:** buscar el documento **N° 21 — "Informe de contingencia asistencial — capacidad crítica de urgencia"** (proceso completo, marcado urgente, ya Terminado). Mostrar el badge/etiqueta "Urgente" y su trazabilidad completa (Urgencia → Dirección → Urgencia).
- **Mensaje:** "Los documentos urgentes se distinguen visualmente en todas las bandejas y reportes — este caso simula una contingencia real de capacidad crítica, resuelta y documentada de principio a fin."

## 10. Consulta de un documento reservado (2 min)

- **Usuario:** `csepulve` / `Demo2026` (jefatura ficticia de **Dirección**).
- **Qué mostrar:** documento **N° 144 — "Documento reservado de prueba para Dirección DEMO"**. Abrirlo y mostrar que aparece marcado como reservado.
- **Punto clave de seguridad:** cerrar sesión y entrar con un funcionario de **otro** servicio sin relación con el documento (p. ej. `vsandova` / `Demo2026`, Imagenología) e intentar buscarlo — **no debe aparecer** en sus resultados.
- **Mensaje:** "Los documentos reservados solo son visibles para quien los originó y para Dirección — esto fue auditado y corregido específicamente en julio de 2026 para HUAP."

## 11. Creación y firma de memorándum (3 min) — punto fuerte de la demo

- **Usuario:** `imorales` / `Demo2026` (Urgencia — tiene Firma Simple ya habilitada, pero **ningún memorándum creado aún**, así que el flujo se ve completo desde cero).
- **Qué mostrar:**
  1. `Documentos → Nuevo` → tipo **Memorándum**, materia libre (p. ej. *"Memorándum interno — coordinación de turno crítico"*), destino Dirección.
  2. Confirmar memorándum → el sistema asigna el correlativo `MEMO-2026-URG069-000001` (o el que corresponda) de forma transaccional.
  3. Mostrar la previsualización del PDF generado en el navegador (100% frontend, sin depender de un servidor externo).
- **Mensaje:** "El número de memorándum nunca se repite ni se salta, incluso si dos jefaturas confirman al mismo instante — está protegido con bloqueo transaccional a nivel de base de datos."

## 12. Firma Simple (2–3 min)

- **Continuación directa del punto 11**, mismo usuario (`imorales`).
- **Qué mostrar:** botón "Firmar" → modal de Firma Simple → reingresar la contraseña propia (`Demo2026`) como mecanismo de re-autenticación → confirmar → el sistema genera un código de verificación único → subir/generar el PDF final con el sello → el documento queda despachado automáticamente.
- **Mensaje:** "Esto es Firma Simple DOC360 — el mecanismo interno de firma del hospital. No reemplaza la Firma Electrónica Avanzada del Estado, pero dota de trazabilidad legal interna (código de verificación, hash del documento, IP, fecha y hora) a cualquier memorándum, sin depender de un proveedor externo."
- **Resultado esperado:** memorándum pasa a estado "Despachado", trazabilidad muestra el evento de firma con el nombre del firmante y el código de verificación.

## 13. Alertas y compromisos (1–2 min)

- **Usuario:** `admin`.
- **Qué mostrar:** `Reportes` o `Dashboard` → indicador de compromisos vencidos vs. vigentes (91 vencidos / 28 vigentes en el dataset demo).
- **Mensaje:** "Cada documento puede llevar un plazo de compromiso — el sistema calcula automáticamente cuáles están vencidos. El módulo de Alertas (`/admin/alertas`) envía estos avisos por correo de forma automática y programable; en este ambiente de demostración no se cargó historial de envíos para no generar correos reales, pero la configuración está disponible para mostrarla conceptualmente."

## 14. Búsqueda avanzada (1–2 min)

- **Qué mostrar:** `/busqueda` — buscar por texto libre (p. ej. "protocolo"), y por número de documento exacto. Mostrar filtros por tipo, estado y rango de fechas.
- **Mensaje:** "La búsqueda respeta las mismas reglas de visibilidad por servicio que ya vimos con el documento reservado — nadie encuentra por buscador lo que no podría ver por bandeja."

## 15. Reportes (2 min)

- **Usuario:** `admin`.
- **Qué mostrar:** `/reportes` — gráficos de documentos por mes, por tipo, por servicio, tiempos de respuesta. Exportar a CSV.
- **Mensaje:** "Estos reportes hoy reflejan casi 5 meses de actividad simulada — en producción, esta sería la vista que usarían ustedes para evaluar carga de trabajo y cumplimiento por servicio, exportable a Excel en un clic."

## 16. Auditoría y trazabilidad institucional (1–2 min)

- **Qué mostrar:** volver al documento **N° 5 — "Observación de auditoría — seguimiento de compromiso de mejora en gestión de stock"** (proceso completo Auditoría → Farmacia → Auditoría, Terminado, con adjunto).
- **Mensaje:** "DOC360 no solo gestiona documentos — permite a Auditoría hacer seguimiento formal de compromisos de mejora con otros servicios, con evidencia adjunta y cierre documentado. Cada acción sensible del sistema (cambios de contraseña, creación de usuarios, validaciones de firma) además queda registrada en un log de auditoría interno a nivel de base de datos."

## 17. Cierre ejecutivo (1–2 min)

- **Qué mostrar:** volver al Dashboard (`admin`).
- **Mensaje de cierre:** "Lo que acaban de ver — creación, derivación, recepción, cierre, memorándums firmados digitalmente, documentos reservados con control real de acceso, y reportes ejecutivos — corre hoy sobre un ambiente de desarrollo local con datos 100% ficticios. La arquitectura (Node.js, React, SQL Server) está lista para producción; los próximos pasos son la configuración de SMTP para alertas reales y, opcionalmente, la integración con FirmaGOB para firma electrónica avanzada del Estado. DOC360 reemplaza 20 años de un sistema en ASP clásico por una plataforma trazable, auditable y segura."

---

## Notas para el presentador

- Todas las credenciales de usuarios ficticios están en `backend/scripts/demo/resumen_generacion.json`.
- Si algo fallara en vivo, el N° de documento de respaldo para cada punto está indicado explícitamente arriba — no dependas de la memoria para encontrarlos.
- Evita entrar a `/admin/alertas` salvo para explicarlo conceptualmente (ver punto 13).
- Si te preguntan por datos reales de pacientes o funcionarios: **este ambiente no contiene ni ha contenido en ningún momento información clínica ni personal real** — es una demostración funcional pura.
