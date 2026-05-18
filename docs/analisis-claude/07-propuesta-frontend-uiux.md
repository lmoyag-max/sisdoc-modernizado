# 07 — Propuesta Frontend UI/UX

**Fecha de análisis:** 2026-05-18  
**Stack propuesto:** React 18 + Vite + Tailwind CSS + shadcn/ui

---

## 1. Principios de diseño

| Principio | Descripción |
|---|---|
| **Claridad** | Jerarquía visual clara, sin ruido innecesario |
| **Eficiencia** | Acciones frecuentes en 1-2 clicks, atajos de teclado |
| **Consistencia** | Sistema de diseño unificado (tokens de color, espaciado, tipografía) |
| **Feedback** | Respuesta visual inmediata a cada acción del usuario |
| **Accesibilidad** | Contraste AA+, navegación por teclado, ARIA labels |
| **Responsividad** | Funcional desde 375px (móvil) hasta 2560px (4K) |

---

## 2. Sistema de diseño

### 2.1 Paleta de colores

```css
/* Modo claro */
--color-primary:     #1e40af;  /* Azul institucional profundo */
--color-primary-light: #3b82f6;
--color-secondary:   #0f766e;  /* Verde teal para acciones secundarias */
--color-background:  #f8fafc;  /* Fondo gris muy suave */
--color-surface:     #ffffff;  /* Tarjetas y paneles */
--color-border:      #e2e8f0;
--color-text:        #0f172a;
--color-text-muted:  #64748b;

/* Estados de documentos */
--color-nuevo:       #3b82f6;  /* Azul */
--color-derivado:    #f59e0b;  /* Ámbar */
--color-proceso:     #8b5cf6;  /* Violeta */
--color-cerrado:     #10b981;  /* Verde */
--color-urgente:     #ef4444;  /* Rojo */

/* Modo oscuro */
--color-background-dark: #0f172a;
--color-surface-dark:    #1e293b;
--color-border-dark:     #334155;
--color-text-dark:       #f1f5f9;
```

### 2.2 Tipografía

```css
--font-sans: 'Inter', system-ui, sans-serif;  /* Cuerpo y UI */
--font-mono: 'JetBrains Mono', monospace;     /* Números, códigos */

--text-xs:   0.75rem;   /* 12px — etiquetas */
--text-sm:   0.875rem;  /* 14px — secundario */
--text-base: 1rem;      /* 16px — base */
--text-lg:   1.125rem;  /* 18px — subtítulos */
--text-xl:   1.25rem;   /* 20px — títulos sección */
--text-2xl:  1.5rem;    /* 24px — títulos página */
--text-3xl:  1.875rem;  /* 30px — dashboard hero */
```

### 2.3 Espaciado y layout

```
Sidebar: 260px (colapsable a 64px en móvil)
Header:   64px fijo
Content:  Máximo 1400px centrado
Padding:  24px en desktop, 16px en tablet, 12px en móvil
Gap tarjetas: 16px / 24px
```

---

## 3. Layout principal

```
┌─────────────────────────────────────────────────────────┐
│  HEADER (64px) — Logo | Búsqueda global | User menu     │
├──────────┬──────────────────────────────────────────────┤
│          │                                              │
│ SIDEBAR  │           ÁREA DE CONTENIDO                 │
│  260px   │                                              │
│          │   [Breadcrumb]                               │
│ ▣ Dashboard        │   [Título de página + Acciones]     │
│ ▣ Documentos        │                                    │
│ ▣ Mis Trámites      │   [Contenido dinámico]             │
│ ▣ Expedientes       │                                    │
│ ▣ Búsqueda          │                                    │
│ ── Admin ──         │                                    │
│ ▣ Usuarios          │                                    │
│ ▣ Reportes          │                                    │
│          │                                              │
└──────────┴──────────────────────────────────────────────┘
```

---

## 4. Páginas y componentes clave

### 4.1 Página: Login

```
┌─────────────────────────────────────┐
│                                     │
│      [Logo institucional]           │
│      SISDOC                         │
│      Sistema de Gestión Documental  │
│                                     │
│  ┌─────────────────────────────┐    │
│  │  Usuario                    │    │
│  └─────────────────────────────┘    │
│  ┌─────────────────────────────┐    │
│  │  Contraseña            👁   │    │
│  └─────────────────────────────┘    │
│                                     │
│  [    Iniciar sesión    ]           │
│                                     │
│  Olvidé mi contraseña               │
└─────────────────────────────────────┘
```

**Características:**
- Fondo degradado azul institucional
- Tarjeta de login centrada con glassmorphism sutil
- Animación de entrada suave (fade + slide up)
- Toggle mostrar/ocultar contraseña
- Loading state en el botón
- Mensaje de error inline (sin alert del browser)
- Responsive: misma tarjeta en móvil

---

### 4.2 Página: Dashboard

```
┌─────────────────────────────────────────────────────┐
│  Buenos días, Juan.    [+ Nuevo Documento]           │
│  Lunes 18 de mayo, 2026                             │
├──────────┬──────────┬──────────┬───────────────────-┤
│   📄 245 │   ⏳ 18  │   ✅ 12  │   🚨 3              │
│  Total   │ Pendient │  Hoy     │  Urgentes           │
│  docs    │  es      │ cerrados │                     │
├──────────┴──────────┴──────────┴────────────────────┤
│                                                     │
│  [Actividad Reciente]        [Documentos Urgentes]  │
│  • Memo 2026-245  derivado   • Oficio 2026-198      │
│    hace 10 min               • Circular 2026-201    │
│  • Oficio 2026-244 ingresado • ...                  │
│    hace 25 min                                      │
│                                                     │
├─────────────────────────────────────────────────────┤
│              [Gráfico: Documentos por mes]          │
│              [Barras: Estado por dependencia]       │
└─────────────────────────────────────────────────────┘
```

**Componentes:**
- `MetricCard` — Tarjeta con número grande, icono, variación porcentual
- `ActividadReciente` — Lista con avatares, timestamps relativos
- `DocumentosUrgentes` — Lista priorizada con badges de color
- `DocumentosChart` — Gráfico de barras mensual (Recharts)
- `EstadoPorDependencia` — Gráfico de dona o barras apiladas

---

### 4.3 Página: Lista de Documentos

```
┌─────────────────────────────────────────────────────┐
│  Documentos                    [+ Nuevo Documento]  │
├─────────────────────────────────────────────────────┤
│  🔍 [Buscar documento, folio, asunto...]            │
│  [Tipo ▼] [Estado ▼] [Dependencia ▼] [Fecha ▼]    │
│                                    [Limpiar filtros]│
├─────────────────────────────────────────────────────┤
│  Folio     │ Asunto          │ Tipo    │ Estado │ ⋯ │
│ ─────────────────────────────────────────────────── │
│  2026-245  │ Solicitud de... │ Oficio  │ ● Nuevo│ ⋯ │
│  2026-244  │ Informe mensual │ Memo    │ ● Deriv│ ⋯ │
│  2026-243  │ Circular inform │ Circular│ ✓ Cerr │ ⋯ │
│            │                 │         │        │   │
├─────────────────────────────────────────────────────┤
│  Mostrando 1-20 de 245 documentos   [< 1 2 3 ... >]│
└─────────────────────────────────────────────────────┘
```

**Características:**
- Búsqueda instantánea (debounce 300ms)
- Filtros por tipo, estado, dependencia, rango de fechas
- Ordenamiento por columna (click en encabezado)
- Paginación con tamaño configurable (20/50/100)
- Click en fila → detalle del documento
- Menú contextual (⋯) → Derivar / Ver historial / Editar
- Badge de colores por estado
- Vista tarjeta alternativa (toggle)
- Exportar a Excel/PDF (fase futura)

---

### 4.4 Página: Detalle de Documento

```
┌─────────────────────────────────────────────────────┐
│  ← Volver    Oficio N° 2026-245                     │
│               [Derivar] [Editar] [Archivar]         │
├─────────────────┬───────────────────────────────────┤
│  DATOS BÁSICOS  │           HISTORIAL                │
│                 │                                   │
│  Tipo: Oficio   │  ● 18 may 10:32 — INGRESADO       │
│  Folio: 2026-245│    Por: Juan Pérez                 │
│  Asunto: ...    │                                   │
│  Procedencia:.. │  ● 18 may 11:15 — DERIVADO         │
│  Destino: ...   │    A: Unidad de RRHH               │
│  Prioridad: 🔴  │    Por: Juan Pérez                 │
│  Estado: Derivado                                   │
│                 │  ● 18 may 14:00 — RECEPCIONADO     │
│  ARCHIVOS       │    Por: María González             │
│  📎 oficio.pdf  │                                   │
│                 │                                   │
└─────────────────┴───────────────────────────────────┘
```

**Características:**
- Layout de dos columnas en desktop
- Timeline visual del historial (línea vertical con puntos)
- Preview de archivo PDF inline (si es PDF)
- Botón "Derivar" abre modal lateral (slide-over)
- Badges de prioridad con color

---

### 4.5 Modal: Nuevo Documento / Derivar

```
┌────────────────────────────────────┐
│  Nuevo Documento              [✕]  │
├────────────────────────────────────┤
│  Tipo de documento *               │
│  [Oficio ▼                    ]    │
│                                    │
│  N° / Folio del documento          │
│  [2026-                       ]    │
│                                    │
│  Asunto *                          │
│  [                            ]    │
│                                    │
│  Procedencia *                     │
│  ○ Interna [Dependencia ▼]         │
│  ○ Externa [Organización  ▼]       │
│                                    │
│  Destino *                         │
│  [Dependencia ▼] [Funcionario ▼]   │
│                                    │
│  Prioridad        Fecha documento  │
│  [Normal ▼]       [18/05/2026]     │
│                                    │
│  Descriptores                      │
│  [Administrativo ×] [Legal ×] [+]  │
│                                    │
│  Adjuntar archivo                  │
│  [ Arrastrar o click para subir ]  │
│                                    │
│  [Cancelar]     [Guardar Documento]│
└────────────────────────────────────┘
```

**Características:**
- Validación en tiempo real (React Hook Form + Zod)
- Autocompletado de dependencias y funcionarios
- Drag & drop para archivos (con preview)
- Multi-select para descriptores con tags
- Botón de guardar con loading state
- Cierre con confirmación si hay cambios

---

## 5. Componentes del sistema de diseño

### 5.1 Badge de estado

```jsx
// Colores según estado del documento
const estadoConfig = {
  1: { label: 'Nuevo',      color: 'blue'   },
  2: { label: 'Recepcionado', color: 'teal' },
  3: { label: 'Derivado',   color: 'amber'  },
  4: { label: 'En proceso', color: 'violet' },
  5: { label: 'Cerrado',    color: 'green'  },
};

function EstadoBadge({ idEstado }) {
  const config = estadoConfig[idEstado];
  return (
    <span className={`badge badge-${config.color}`}>
      {config.label}
    </span>
  );
}
```

### 5.2 MetricCard (Dashboard)

```jsx
function MetricCard({ titulo, valor, icono, variacion, color }) {
  return (
    <div className="card">
      <div className="flex items-center justify-between">
        <span className="text-muted">{titulo}</span>
        <div className={`icon-wrapper bg-${color}-100`}>{icono}</div>
      </div>
      <div className="text-3xl font-bold mt-2">{valor}</div>
      {variacion && (
        <div className="text-sm text-muted mt-1">
          {variacion > 0 ? '↑' : '↓'} {Math.abs(variacion)}% vs semana anterior
        </div>
      )}
    </div>
  );
}
```

---

## 6. Animaciones y micro-interacciones

| Interacción | Animación |
|---|---|
| Entrada de página | Fade in + slide up (200ms) |
| Hover en tarjeta | Shadow elevada + escala 1.01 (150ms) |
| Click en botón | Scale down 0.97 + ripple |
| Toast de éxito | Slide in desde arriba derecha |
| Modal | Overlay fade + panel slide |
| Skeleton loading | Shimmer gradient animado |
| Filtros aplicados | Badge animado, tabla re-render suave |
| Sidebar collapse | Slide left con smooth resize |

---

## 7. Responsive design

### Breakpoints (Tailwind)

| Breakpoint | Tamaño | Layout |
|---|---|---|
| `sm` | 640px | Móvil grande |
| `md` | 768px | Tablet |
| `lg` | 1024px | Desktop pequeño |
| `xl` | 1280px | Desktop estándar |
| `2xl` | 1536px | Desktop grande |

### Adaptaciones móviles

- Sidebar → cajón oculto con hamburger menu
- Tablas → scroll horizontal o vista de tarjetas
- Modales → full screen en móvil
- Botones → touch-friendly (mínimo 44px de tap target)
- Dashboard → 1 columna en lugar de grid

---

## 8. Modo oscuro

Implementado con `next-themes` + clases Tailwind `dark:`.

- Toggle en el header (sol/luna)
- Persiste en localStorage
- Detecta preferencia del sistema (`prefers-color-scheme`)
- Todos los componentes con variantes dark definidas

---

## 9. Accesibilidad

- Contraste mínimo AA (4.5:1 para texto normal)
- Focus ring visible en todos los elementos interactivos
- ARIA labels en iconos sin texto
- Roles semánticos correctos (`role="main"`, `role="navigation"`)
- Skip link "Ir al contenido principal"
- Mensajes de error asociados con `aria-describedby`

---

## 10. Checklist de implementación frontend

### Configuración inicial
- [ ] Instalar Tailwind CSS + shadcn/ui
- [ ] Configurar tema (colores, tipografía)
- [ ] Instalar Inter font (Google Fonts o local)
- [ ] Configurar next-themes (modo oscuro)
- [ ] Configurar React Router v6
- [ ] Configurar TanStack Query
- [ ] Configurar Zustand store
- [ ] Configurar Axios client con interceptors

### Layout y navegación
- [ ] Componente Layout (Sidebar + Header + Content)
- [ ] Sidebar con navegación y collapse
- [ ] Header con búsqueda global y menú usuario
- [ ] Sistema de rutas protegidas (PrivateRoute)
- [ ] Breadcrumb dinámico

### Páginas core
- [ ] Login
- [ ] Dashboard con métricas
- [ ] Lista de documentos con filtros
- [ ] Detalle de documento
- [ ] Formulario nuevo documento
- [ ] Mis trámites
- [ ] Historial de documento
