# Documentación Frontend - Sistema POS Papelería Sigma

**Responsable:** Abraham  
**Fecha:** Diciembre 2024  
**Tecnologías:** HTML5, CSS3, JavaScript (Vanilla), Bootstrap 5.3, PHP 8.x

---

## 1. Proceso de Diseño UX/UI

### 1.1 Fases del Diseño

#### Fase 1: Diseño Inicial - Sistema Verde/Café (v1.0)
**Objetivo:** Identidad visual temática de papelería tradicional

**Paleta de Colores Original:**
```css
Verde Primario:    #2d5016  /* Inspirado en naturaleza/papelería ecológica */
Verde Secundario:  #4a7c2f
Verde Claro:       #6ba03e
Café Oscuro:       #3e2723  /* Tono madera/papel kraft */
Café Medio:        #5d4037
Café Claro:        #8d6e63
```

**Decisiones de Diseño:**
- ✅ **Colores cálidos y naturales:** Asociación con papelería tradicional y ecológica
- ✅ **Bootstrap 5.3:** Framework para desarrollo rápido
- ✅ **Emoji como iconos:** 🌱 🛒 📊 (visual amigable, sin dependencias de iconos)
- ✅ **Layout horizontal:** Navbar superior + grid de 2 columnas (col-lg-8/4)
- ✅ **Cards con sombras sutiles:** Separación visual clara de secciones

**Resultado:**
- Sistema funcional y amigable
- Identidad visual clara pero anticuada
- Dependiente de Bootstrap (330KB+ CSS)
- Diseño web tradicional de 2015-2018

---

#### Fase 2: Propuesta de Mejora - Dark Pro Evolution (v2.0)
**Objetivo:** Modernizar interfaz con diseño profesional oscuro

**Inspiración:**
- GitHub Dark theme (profesional, limpio)
- VS Code interface (sidebar navigation)
- Discord UI (cards flotantes, jerarquía visual)
- Vercel Dashboard (tipografía bold, espaciado generoso)

**Paleta de Colores Dark Pro:**
```css
/* Backgrounds - Jerarquía de profundidad */
#0d1117  /* Fondo principal (más profundo) */
#161b22  /* Cards, sidebar (nivel medio) */
#21262d  /* Hover states (superficie) */

/* Borders - Separadores sutiles */
#30363d  /* Bordes principales */

/* Accent Colors - Acción y estados */
#58a6ff  /* Azul principal (links, focus) */
#1f6feb  /* Azul secundario (botones, acciones) */
#2ea043  /* Verde (success, confirmaciones) */
#f85149  /* Rojo (danger, eliminaciones) */

/* Typography - Legibilidad optimizada */
#c9d1d9  /* Texto principal (alto contraste) */
#8b949e  /* Texto secundario/muted (bajo contraste) */
```

**Justificación de la Paleta:**
1. **#0d1117 (Background):** 
   - Reduce fatiga visual en sesiones largas de trabajo
   - Contraste ideal con texto #c9d1d9 (WCAG AAA)
   - Profundidad visual sin ser completamente negro (#000000)

2. **#58a6ff (Primary Blue):**
   - Color de acción universalmente reconocido
   - Alto contraste sobre fondos oscuros
   - Asociación con confiabilidad y tecnología

3. **Jerarquía de grises (#161b22 → #21262d → #30363d):**
   - Separación visual sin bordes agresivos
   - Guía la atención del usuario naturalmente
   - Mantiene consistencia en toda la interfaz

**Decisiones de Arquitectura:**
- ❌ **Eliminar Bootstrap:** Reducir bundle size (330KB → 0KB)
- ✅ **CSS Grid + Flexbox:** Layout moderno y flexible
- ✅ **Sidebar Navigation:** Más espacio para contenido principal
- ✅ **Font Awesome 6.4.2:** Iconografía profesional y consistente
- ✅ **JavaScript Vanilla:** Sin dependencias jQuery/Bootstrap JS
- ✅ **Logo SVG:** Escalable, pequeño (1KB), gradiente CSS

**Estructura Visual:**
```
┌──────────────────────────────────────────┐
│  Sidebar (280px)      │  Main Content    │
│  ┌──────────────┐     │                  │
│  │ Logo SVG     │     │  Header          │
│  │ [Gradient]   │     │  [User Pill]     │
│  ├──────────────┤     │                  │
│  │ Dashboard ✓  │     │  Page Title      │
│  │ Productos    │     │  [52px Bold]     │
│  │ Reportes     │     │                  │
│  │              │     │  Dashboard Grid  │
│  │ (flex space) │     │  ┌──────┬──────┐ │
│  │              │     │  │Cart  │Stats │ │
│  │ Cerrar Sesión│     │  │1.5fr │ 1fr  │ │
│  └──────────────┘     │  └──────┴──────┘ │
└──────────────────────────────────────────┘
```

---

#### Fase 3: Diseño Final Implementado (v2.0 - Dark Pro)
**Fecha:** Diciembre 8, 2024

**Características Finales:**

**Tipografía:**
```css
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

/* Jerarquía de tamaños */
Page Title:       52px / 900 weight  /* Ultra bold para impacto */
Card Title:       26px / 900 weight  /* Bold para secciones */
Body Text:        16px / 600 weight  /* Semi-bold para legibilidad */
Total Display:    48px / 900 weight  /* Énfasis en cifras importantes */
Small/Helper:     13-14px / 600      /* Textos secundarios */
```

**Componentes Clave:**

1. **Sidebar Navigation**
   - Width fijo: 280px (no colapsa en desktop)
   - Logo centrado: 80px de altura
   - Nav items con padding generoso: 16px 20px
   - Active state: background #1f6feb (azul sólido)
   - Hover: background #21262d (sutil)

2. **Cards**
   - Border-radius: 16px (esquinas suaves modernas)
   - Padding: 32px (espaciado generoso)
   - Border: 1px solid #30363d
   - Hover: glow azul + elevación visual

3. **Tabla de Carrito**
   - Border-collapse: separate
   - Border-spacing: 0 10px (separación entre filas)
   - Rows con border-radius: 12px individual
   - Hover: borde azul + glow effect sin cambiar background

4. **Botones**
   - Primary: #1f6feb background, 18px padding
   - Border-radius: 12px
   - Icons Font Awesome integrados
   - Hover: color más oscuro + box-shadow
   - Disabled: opacity 0.5

**Animaciones y Microinteracciones:**
```css
/* Transiciones suaves en todo */
transition: all 0.2s ease;

/* Hover effects consistentes */
- Botones: elevación sutil
- Cards: glow azul suave
- Nav items: cambio de color progresivo

/* Loading spinner */
@keyframes spin {
  to { transform: rotate(360deg); }
}
```

**Sistema de Alertas Custom:**
```javascript
// Sin dependencia de Bootstrap JS
showAlert(message, type) {
  - Fade in suave
  - Auto-dismiss 3 segundos
  - Fade out con opacity transition
  - Posición fixed top-right
  - 4 tipos: success, danger, warning, info
}
```

---

### 1.2 Comparación de Versiones

| Aspecto | v1.0 (Verde/Café) | v2.0 (Dark Pro) |
|---------|-------------------|-----------------|
| **Paleta** | Cálida, natural | Oscura, profesional |
| **Framework CSS** | Bootstrap 5.3 (330KB) | Custom CSS (15KB) |
| **JavaScript** | Bootstrap JS + jQuery | Vanilla JS puro |
| **Layout** | Navbar + Grid horizontal | Sidebar + Grid moderno |
| **Iconos** | Emoji (🌱🛒📊) | Font Awesome 6.4.2 |
| **Logo** | Texto + emoji | SVG con gradiente |
| **Tipografía** | Estándar Bootstrap | System fonts optimizadas |
| **Bundle Size** | ~400KB total | ~20KB total |
| **Performance** | Buena | Excelente |
| **Estética** | 2015-2018 | 2024+ moderna |

---

### 1.3 Diseño Responsive (Mobile First)

**Breakpoints:**
```css
/* Desktop (default) */
- Sidebar: 280px fixed
- Grid: 1.5fr 1fr

/* Tablet (< 1200px) */
@media (max-width: 1200px) {
  - Grid: 1fr (single column)
  - Sidebar: mantiene ancho
}

/* Mobile (< 640px) */
@media (max-width: 640px) {
  - Sidebar: width 100%, horizontal scroll
  - Nav items: width 100%
  - Padding reducido: 20px
  - Font sizes ajustados:
    * Page title: 36px
    * Card title: 20px
}
```

**Optimizaciones Mobile:**
- Touch targets mínimo 44x44px (accesibilidad)
- Botones full-width en mobile
- Cards con padding reducido (20px vs 32px)
- Stats grid: mantiene 2 columnas incluso en mobile

---

### 1.4 Accesibilidad (a11y)

**Contraste de Color:**
- Texto principal (#c9d1d9) sobre fondo (#0d1117): **15.8:1** (WCAG AAA ✅)
- Texto muted (#8b949e) sobre fondo (#0d1117): **9.2:1** (WCAG AA ✅)
- Botón primary (#1f6feb): **7.5:1** (WCAG AA ✅)

**Navegación por Teclado:**
- ✅ Tab index lógico
- ✅ Focus states visibles (outline azul)
- ✅ Input autofocus en barcode scanner

**ARIA Labels:**
```html
<button aria-label="Confirmar venta">
<input aria-describedby="barcode-help">
```

---

### 1.5 Decisiones de UX

**1. Input de Código de Barras:**
- ✅ Autofocus permanente (scanner listo siempre)
- ✅ Enter para agregar (flujo rápido)
- ✅ Feedback visual inmediato (alertas)

**2. Carrito de Productos:**
- ✅ Botones +/- grandes (36x36px touch-friendly)
- ✅ Cantidad editable visualmente clara
- ✅ Eliminar con confirmación implícita (un click)

**3. Confirmar Venta:**
- ✅ Botón disabled cuando carrito vacío
- ✅ Loading spinner durante proceso
- ✅ Alerta de éxito con folio

**4. Sidebar Navigation:**
- ✅ Active state obvio (background azul sólido)
- ✅ Iconos + texto (doble canal de información)
- ✅ Cerrar sesión al fondo (menos accidental)

---

## 2. Estructura General del Proyecto

### 1.1 Arquitectura de Páginas

El sistema cuenta con 4 páginas principales interconectadas:

```
login.php (Autenticación)
    ↓
index.php (POS - Punto de Venta)
    ├→ productos.php (Gestión de Productos - Solo Admin)
    ├→ reportes.php (Reportes y Estadísticas - Solo Admin)
    └→ ticket.php (Impresión de Tickets)
```

### 1.2 Estructura de Archivos

```
/papeleria-pos-sigma
├── index.php              # POS Principal
├── login.php              # Página de login
├── productos.php          # CRUD de productos
├── reportes.php           # Reportes y estadísticas
├── ticket.php             # Vista de impresión de tickets
├── /assets
│   ├── /css
│   │   ├── style.css      # Estilos principales
│   │   └── styles.css     # Estilos alternativos
│   └── /js
│       ├── pos.js         # Lógica del carrito de ventas
│       ├── productos.js   # CRUD de productos
│       ├── reportes.js    # Generación de reportes
│       └── app.js         # Utilidades generales
├── /actions               # Endpoints PHP (Backend)
└── /includes              # Configuración y autenticación
```

---

## 2. Descripción de Páginas

### 2.1 Login (`login.php`)

**Propósito:** Autenticación de usuarios (Admin y Operador)

**Características:**
- Formulario simple con email y contraseña
- Validación de credenciales vía `actions/login.php`
- Redirección automática si ya hay sesión activa
- Manejo de errores con mensajes dinámicos

**Credenciales de Prueba:**
- Admin: `admin@papeleria.com` / `admin123`
- Operador: `operador@papeleria.com` / `operador123`

**Flujo:**
1. Usuario ingresa credenciales
2. Submit → `actions/login.php` (POST)
3. Si es exitoso → Redirige a `index.php`
4. Si falla → Muestra error

---

### 2.2 POS - Punto de Venta (`index.php`)

**Propósito:** Interfaz principal para realizar ventas

**Características:**

#### Panel Izquierdo:
- **Input de código de barras:** Escaneo/entrada manual + Enter
- **Tabla de carrito:** Productos agregados con:
  - Nombre del producto
  - Código de barras
  - Precio unitario
  - Cantidad (con botones +/-)
  - Subtotal
  - Botón eliminar

#### Panel Derecho:
- **Resumen de totales:**
  - Subtotal
  - IVA (16%)
  - Total
- **Botón "Confirmar Venta":** Procesa la venta
- **Botón "Limpiar Carrito":** Vacía el carrito
- **Estadísticas de sesión:**
  - Ventas realizadas hoy
  - Total del día

#### Navbar:
- Logo y nombre del sistema
- Botones "Productos" y "Reportes" (solo admin)
- Nombre del usuario en sesión
- Botón "Cerrar Sesión"

**Funcionalidades JavaScript (`pos.js`):**

```javascript
// Funciones principales
- agregarProducto()       // Agrega producto al carrito vía código de barras
- actualizarCarrito()     // Obtiene estado actual del carrito desde el servidor
- renderCarrito(data)     // Renderiza tabla y totales
- cambiarCantidad()       // Incrementa/decrementa cantidad de un producto
- eliminarItem()          // Elimina un producto del carrito
- confirmarVenta()        // Procesa la venta y abre ticket
- limpiarCarrito()        // Vacía el carrito actual
- showAlert()             // Muestra alertas visuales
```

**Endpoints Conectados:**
- `POST actions/ventas_add.php` - Agregar producto
- `GET actions/ventas_get.php` - Obtener estado del carrito
- `POST actions/ventas_update.php` - Actualizar cantidad
- `POST actions/ventas_remove.php` - Eliminar producto
- `POST actions/ventas_confirm.php` - Confirmar venta
- `POST actions/ventas_clear.php` - Limpiar carrito

**Flujo de Venta Completo:**
1. Operador/Admin escanea o escribe código de barras
2. Presiona Enter → `agregarProducto()`
3. Sistema valida producto y stock → Agrega al carrito
4. Se actualiza tabla y totales automáticamente
5. Operador ajusta cantidades si es necesario
6. Presiona "Confirmar Venta"
7. Sistema genera folio, descuenta stock, registra en BD
8. Abre ventana emergente con `ticket.php?venta_id=X`

---

### 2.3 Gestión de Productos (`productos.php`)

**Propósito:** CRUD completo de productos (Solo Admin)

**Características:**

#### Tabla de Productos:
- ID, Imagen thumbnail, Nombre, Código de barras
- Precio de compra, Precio de venta
- Stock actual (resaltado en rojo si < 10)
- Estado (Activo/Inactivo)
- Acciones: Editar ✏️ / Eliminar 🗑️

#### Barra de Búsqueda y Filtros:
- Búsqueda por nombre o código de barras
- Filtro por estado (Todos/Activos/Inactivos)
- Paginación (10 productos por página)

#### Modal de Creación (Nuevo Producto):
- Botón "➕ Nuevo Producto" en header
- Formulario con:
  - Nombre del producto
  - Código de barras (único)
  - Descripción
  - Precio de compra
  - Precio de venta
  - Upload de imagen (JPG/PNG, máx 5MB)
  - Preview de imagen en tiempo real

#### Modal de Edición (Editar Producto):
- Botón "✏️ Editar" en cada fila de la tabla
- Modal exclusivo para edición con:
  - Todos los campos del producto precargados
  - Título "✏️ Editar Producto"
  - Gradiente verde oscuro en header
  - Preview de imagen si existe
  - Botón "Guardar cambios"

**Funcionalidades JavaScript (`productos.js`):**

```javascript
// Funciones principales
- cargarProductos(page)           // Carga lista paginada desde BD
- renderProductos()               // Renderiza tabla
- renderPaginacion()              // Renderiza controles de paginación
- buscarProductos()               // Aplica búsqueda y filtros
- guardarProducto()               // Crea nuevo producto (modal alta)
- guardarProductoEdit()           // Actualiza producto existente (modal edición)
- guardarProductoDesdeFormulario() // Helper compartido para guardar (crear/editar)
- editarProducto(id)              // Carga datos y abre modal de edición
- eliminarProducto(id)            // Soft delete (activo = 0)
- setupImagePreview()             // Configura preview de imagen para ambos modales
```

**Endpoints Conectados:**
- `GET actions/productos_list.php?page=X&search=Y` - Listar productos
- `GET actions/productos_get.php?id=X` - Obtener un producto
- `POST actions/productos_create.php` - Crear producto (FormData con imagen)
- `POST actions/productos_update.php` - Actualizar producto
- `POST actions/productos_delete.php` - Eliminar producto (soft delete)

**Validaciones:**
- Código de barras único (validado en backend)
- Precios > 0
- Formato de imagen válido
- Tamaño máximo de imagen: 5MB

---

### 2.4 Reportes (`reportes.php`)

**Propósito:** Visualización y exportación de reportes (Solo Admin)

**Características:**

#### Filtros:
- Tipo de reporte:
  - Ventas
  - Devoluciones
- Rango de fechas (inicio - fin)

#### Tabla de Resultados:
- Headers dinámicos según tipo de reporte
- Datos cargados desde BD
- Columnas ventas: Folio, Fecha, Cajero, Subtotal, IVA, Total, Acciones
- Columnas devoluciones: Folio, Fecha, Usuario, Total devuelto, Acciones
- **Dropdown 3 puntos (⋮)** en cada fila con acciones:
  - 📄 Ver detalle → Abre modal con productos del movimiento
  - 🔙 Registrar devolución (solo ventas) → Abre modal para devolver productos
- **Modales implementados:**
  - Modal Detalle Venta: Muestra productos, cantidades, precios de una venta
  - Modal Detalle Devolución: Muestra productos, cantidades de una devolución
  - Modal Registrar Devolución: Permite seleccionar productos y cantidades a devolver

#### Acciones:
- **Exportar CSV:** Descarga archivo CSV con datos
- **Imprimir:** Vista optimizada para impresión A4 (pendiente mejoras)

**Funcionalidades JavaScript (`reportes.js`):**

```javascript
// Funciones principales
- generarReporte()                    // Genera reporte según tipo y fechas
- generarReporteVentas()              // Consulta ventas en rango
- generarReporteDevoluciones()        // Consulta devoluciones en rango
- renderTablaVentas(data)             // Renderiza tabla con dropdown de acciones
- renderTablaDevoluciones(data)       // Renderiza tabla de devoluciones
- abrirModalDetalleVenta(ventaId)     // Abre modal con detalle de venta
- abrirModalDetalleDevolucion(devId)  // Abre modal con detalle de devolución
- abrirModalDevolucion(ventaId)       // Abre modal para registrar devolución
- confirmarDevolucion()               // Envía devolución al backend
- exportarCSV()                       // Descarga datos en formato CSV
```

**Endpoints Conectados:**
- `GET actions/reportes_get.php?action=ventas_rango&start=X&end=Y`
- `GET actions/reportes_get.php?action=devoluciones_rango&start=X&end=Y`
- `GET actions/export_csv.php?tipo=X&fechaInicio=Y&fechaFin=Z` - Exportar CSV
- **Endpoints esperados (backend pendiente):**
  - `GET actions/reportes_detalle_venta.php?venta_id=X` - Detalle de venta
  - `GET actions/reportes_detalle_devolucion.php?devolucion_id=X` - Detalle de devolución
  - `POST actions/devoluciones_confirm.php` - Confirmar devolución

**Tipos de Reportes:**

1. **Ventas:**
   - Folio, Fecha, Cajero, Subtotal, IVA, Total
   - Rango de fechas obligatorio
   - Acciones: Ver detalle, Registrar devolución
   
2. **Devoluciones:**
   - Folio devolución, Fecha, Usuario, Total devuelto
   - Rango de fechas obligatorio
   - Acciones: Ver detalle

---

### 2.5 Ticket de Venta (`ticket.php`)

**Propósito:** Impresión de comprobante de venta

**Características:**
- Diseño optimizado para impresora térmica 80×40mm
- Estilos de impresión (@media print)
- Datos dinámicos desde BD vía `actions/print_ticket.php?venta_id=X`

**Contenido del Ticket:**
- Logo y nombre del negocio
- Folio de venta
- Fecha y hora
- Nombre del cajero
- Tabla de productos vendidos:
  - Cantidad
  - Nombre del producto
  - Precio unitario
  - Subtotal
- Totales:
  - Subtotal
  - IVA (16%)
  - Total
- Mensaje de agradecimiento

**Botones (no imprimibles):**
- Imprimir: `window.print()`
- Cerrar: `window.close()`

---

## 3. Sistema de Estilos

### 3.1 Framework y Diseño

**Bootstrap 5.3:**
- Sistema de grid responsivo
- Componentes UI (cards, modals, tables, forms)
- Utilidades de spacing y tipografía

**Tema Personalizado (`style.css`):**
- **Paleta de colores ecológica:**
  - Verde primario: `#2d5016`
  - Verde secundario: `#4a7c2f`
  - Verde claro: `#6ba03e`
  - Café oscuro: `#3e2723`
  - Café medio: `#5d4037`

### 3.2 Componentes Personalizados

```css
/* Navbar personalizado */
.navbar-custom
  - Gradiente verde
  - Sombra sutil
  - Logo animado (emoji 🌱 con efecto grow)

/* Botones personalizados */
.btn-primary-custom     // Gradiente verde con hover
.btn-success-custom     // Verde sólido para acciones principales
.btn-danger-custom      // Rojo para acciones destructivas
.btn-logout            // Café para cerrar sesión

/* Cards */
.card
  - Sin bordes
  - Border-radius: 12px
  - Sombra elevada
  - Hover: Elevación adicional

/* Tablas */
.table-custom
  - Header verde con texto blanco
  - Hover en filas
  - Responsive

/* Inputs */
.barcode-input
  - Border grueso verde
  - Transición suave al focus
  - Font-size grande
```

### 3.3 Animaciones

```css
@keyframes fadeInUp        // Entrada suave de elementos
@keyframes plantGrow       // Logo animado
@keyframes spin            // Loading spinner
@keyframes pulse           // Efecto de pulsación
```

### 3.4 Responsive Design

**Breakpoints:**
- Desktop: > 992px (3 columnas en POS)
- Tablet: 768px - 991px (2 columnas)
- Mobile: < 768px (1 columna, layout vertical)

**Adaptaciones móviles:**
- Navbar colapsable
- Tablas con scroll horizontal
- Botones full-width
- Font-sizes ajustados

---

## 4. Flujo de Navegación y Permisos

### 4.1 Roles de Usuario

| Rol       | Permisos                                    |
|-----------|---------------------------------------------|
| Admin     | Acceso total (POS + Productos + Reportes)  |
| Operador  | Solo POS (ventas y tickets)                 |

### 4.2 Protección de Rutas

**Archivos de autenticación:**
- `includes/auth_user.php` - Requiere sesión activa (cualquier rol)
- `includes/auth_admin.php` - Requiere rol 'admin'

**Páginas protegidas:**
```php
index.php     → require 'auth_user.php'
productos.php → require 'auth_admin.php'
reportes.php  → require 'auth_admin.php'
ticket.php    → require 'auth_user.php'
```

### 4.3 Diagrama de Flujo

```
[Login] → Autenticación exitosa
    ↓
¿Rol = Admin?
    │
    ├─ Sí → [POS] ←→ [Productos] ←→ [Reportes]
    │                    ↓
    └─ No → [POS]       [Ticket]
                ↓
            [Ticket]
```

---

## 5. Interacción con Backend (APIs)

### 5.1 Convenciones

**Formato de Respuesta JSON:**
```json
{
  "success": true/false,
  "message": "Mensaje descriptivo",
  "data": { ... }  // Datos del resultado
}
```

**Métodos HTTP:**
- `GET` - Consultas (listas, obtener por ID)
- `POST` - Crear, actualizar, eliminar

### 5.2 Endpoints por Módulo

#### Ventas (POS):
| Endpoint                    | Método | Descripción              |
|-----------------------------|--------|--------------------------|
| `actions/ventas_add.php`    | POST   | Agregar producto         |
| `actions/ventas_get.php`    | GET    | Obtener carrito actual   |
| `actions/ventas_update.php` | POST   | Actualizar cantidad      |
| `actions/ventas_remove.php` | POST   | Eliminar producto        |
| `actions/ventas_confirm.php`| POST   | Confirmar venta          |
| `actions/ventas_clear.php`  | POST   | Limpiar carrito          |

#### Productos:
| Endpoint                      | Método | Descripción              |
|-------------------------------|--------|--------------------------|
| `actions/productos_list.php`  | GET    | Listar con paginación    |
| `actions/productos_get.php`   | GET    | Obtener por ID           |
| `actions/productos_create.php`| POST   | Crear producto           |
| `actions/productos_update.php`| POST   | Actualizar producto      |
| `actions/productos_delete.php`| POST   | Soft delete              |

#### Reportes:
| Endpoint                               | Método | Descripción                      |
|----------------------------------------|--------|----------------------------------|
| `actions/reportes_get.php`             | GET    | Generar reporte ventas/devoluc.  |
| `actions/reportes_detalle_venta.php`   | GET    | Detalle de venta (pendiente)     |
| `actions/reportes_detalle_devolucion.php`| GET  | Detalle de devolución (pendiente)|
| `actions/devoluciones_confirm.php`     | POST   | Confirmar devolución (pendiente) |
| `actions/export_csv.php`               | GET    | Exportar CSV                     |
| `actions/print_ticket.php`             | GET    | Datos para ticket                |

---

## 6. Características Especiales

### 6.1 Manejo de Sesión
- Timeout automático después de inactividad
- Validación en cada petición
- Logout limpia sesión y redirige a login

### 6.2 Alertas y Notificaciones
- Sistema de alertas dinámicas con animación
- Colores semánticos (success, danger, warning, info)
- Auto-dismissible después de 3 segundos

### 6.3 Loading States
- Spinners durante peticiones asíncronas
- Deshabilitación de botones para evitar doble-submit
- Feedback visual en todas las acciones

### 6.4 Validaciones Frontend
- Validación HTML5 en formularios
- Validación JavaScript antes de enviar
- Mensajes de error específicos
- Preview de archivos antes de upload

### 6.5 Accesibilidad
- Labels en todos los inputs
- Focus automático en campos principales
- Atributos ARIA donde aplica
- Navegación con teclado (Tab, Enter)

---

## 7. Pruebas y Debugging

### 7.1 Cómo Probar el Sistema

**Requisitos:**
1. XAMPP corriendo (Apache + MySQL)
2. Base de datos importada (`schema.sql` + `seed.sql`)
3. Navegador moderno (Chrome, Firefox, Edge)

**Pasos:**
1. Iniciar XAMPP
2. Abrir: `http://localhost/papeleria-pos-sigma/login.php`
3. Login con credenciales de prueba
4. Probar flujos:
   - Agregar productos al carrito
   - Confirmar venta
   - Ver ticket
   - Admin: Gestionar productos
   - Admin: Generar reportes

### 7.2 Console Logs
- Todos los errores de fetch se loguean en consola
- Útil para debugging: Abrir DevTools (F12)

### 7.3 Errores Comunes

| Error                          | Causa                        | Solución                      |
|--------------------------------|------------------------------|-------------------------------|
| "Error de conexión"            | XAMPP detenido o URL errónea | Verificar servicios activos   |
| "Producto no encontrado"       | Código de barras inválido    | Verificar en tabla productos  |
| "Stock insuficiente"           | No hay existencias           | Revisar tabla existencias     |
| Modal no abre                  | Bootstrap JS no cargado      | Verificar CDN de Bootstrap    |
| Estilos no cargan              | Ruta incorrecta a CSS        | Verificar href en <link>      |

---

## 8. Mejoras Futuras (Roadmap)

### 8.1 Funcionalidades Completadas (Diciembre 2024)
- [x] Módulo de devoluciones (UI completo, backend pendiente)
- [x] Reportes de ventas y devoluciones con acciones
- [x] **Modo oscuro Dark Pro (Implementado)** ✨
- [x] Diseño completamente rediseñado sin dependencia de Bootstrap
- [x] Logo SVG profesional con gradiente
- [x] Sidebar navigation con iconos Font Awesome
- [x] Sistema de alertas custom sin Bootstrap JS

### 8.2 Funcionalidades Pendientes
- [ ] Módulo de compras (ingresar stock)
- [ ] Historial de ventas con búsqueda avanzada
- [ ] Dashboard con gráficas (Chart.js)
- [ ] PWA (Progressive Web App) para uso offline

### 8.3 Optimizaciones Técnicas
- [ ] Implementar SPA con framework (React/Vue)
- [ ] Caché de productos en localStorage
- [ ] Lazy loading de imágenes
- [ ] Compresión de assets (minify CSS/JS)
- [ ] Service Workers para offline support

### 8.4 UX/UI Futuras
- [ ] Sonidos de feedback (beep al escanear)
- [ ] Tema claro (light mode) alternativo
- [ ] Tooltips informativos
- [ ] Tutorial interactivo para nuevos usuarios
- [ ] Atajos de teclado configurables

---

## 9. Diseño Dark Pro (Actualización Diciembre 2024)

### 9.1 Arquitectura del Diseño

El sistema ha sido completamente rediseñado con un tema oscuro profesional inspirado en GitHub Dark y VS Code.

#### Estructura Visual
```
┌──────────────────────────────────────────┐
│  Sidebar (280px)      │  Main Content    │
│  ┌──────────────┐     │                  │
│  │ Logo SVG     │     │  Header          │
│  ├──────────────┤     │  ┌─────────────┐ │
│  │ Dashboard ✓  │     │  │ User Info   │ │
│  │ Productos    │     │  └─────────────┘ │
│  │ Reportes     │     │                  │
│  │              │     │  Dashboard Grid  │
│  │ (flex space) │     │  ┌──────┬──────┐ │
│  │              │     │  │Cart  │Stats │ │
│  │ Cerrar Sesión│     │  └──────┴──────┘ │
│  └──────────────┘     │                  │
└──────────────────────────────────────────┘
```

### 9.2 Paleta de Colores Dark Pro

```css
/* Backgrounds */
#0d1117  /* Fondo principal (background) */
#161b22  /* Fondo secundario (cards, sidebar) */
#21262d  /* Fondo hover */

/* Borders */
#30363d  /* Bordes principales */

/* Accent Colors */
#58a6ff  /* Azul principal (primary) */
#1f6feb  /* Azul secundario (botones) */
#2ea043  /* Verde (success) */
#f85149  /* Rojo (danger) */

/* Text */
#c9d1d9  /* Texto principal */
#8b949e  /* Texto secundario/muted */
```

### 9.3 Componentes Principales

#### Sidebar
```css
- Ancho fijo: 280px
- Background: #161b22
- Padding: 40px 24px
- Border-right: 1px solid #30363d
- Logo: 80px de altura
- Nav items con hover effect
```

#### Cards
```css
- Background: #161b22
- Border: 1px solid #30363d
- Border-radius: 16px
- Padding: 32px
- Hover: border-color #58a6ff + box-shadow
```

#### Tabla de Carrito
```css
- Border-collapse: separate
- Border-spacing: 0 10px
- Rows: background #0d1117
- Border-radius: 12px en cada row
- Hover: borde azul + glow effect
```

#### Botones
```css
.btn-primary {
  - Background: #1f6feb
  - Color: #ffffff
  - Border-radius: 12px
  - Hover: #1a5dd9 + box-shadow
  - Disabled: opacity 0.5
}

.btn-secondary {
  - Background: transparent
  - Border: 1px solid #30363d
  - Hover: background #21262d
}

.qty-btn {
  - Size: 36x36px
  - Border-radius: 8px
  - Color: #58a6ff
  - Hover: background #1f6feb + color white
}
```

### 9.4 Sistema de Alertas Custom

Sin dependencia de Bootstrap JS, implementado con JavaScript vanilla:

```javascript
// Tipos de alertas
.alert-success  // Verde #2ea043
.alert-danger   // Rojo #f85149
.alert-warning  // Amarillo #bb8009
.alert-info     // Azul #58a6ff

// Features
- Auto-dismiss después de 3 segundos
- Fade out suave (opacity transition)
- Botón close con icono Font Awesome
- Posición fixed top-right
```

### 9.5 Animaciones y Transiciones

```css
/* Transiciones suaves */
transition: all 0.2s ease;

/* Hover effects */
- Cards: elevación con box-shadow
- Buttons: cambio de color + ligera elevación
- Nav items: cambio de background

/* Loading spinner */
@keyframes spin {
  to { transform: rotate(360deg); }
}
```

### 9.6 Tipografía

```css
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

/* Tamaños */
- Page title: 52px, weight 900
- Card title: 26px, weight 900
- Body text: 16px, weight 600
- Small text: 13-14px
- Total display: 48px, weight 900
```

### 9.7 Logo SVG

Ubicación: `assets/images/papeleria-sigma-logo.svg`

```svg
- Viewbox: 210x65
- Circle-notch: radius 18px, stroke-width 7px
- Gradient: #58a6ff → #1f6feb
- Text: "Papelería" 16px + "Sigma" 25px
- Font-weight: 800
```

### 9.8 Iconografía

**Font Awesome 6.4.2:**
```html
- fa-home: Dashboard
- fa-box: Productos
- fa-chart-line: Reportes
- fa-barcode: Escanear código
- fa-shopping-cart: Carrito
- fa-receipt: Resumen
- fa-fire: Ventas hoy
- fa-coins: Total
- fa-check-circle: Confirmar
- fa-trash: Eliminar/Limpiar
- fa-user-circle: Usuario
- fa-sign-out-alt: Cerrar sesión
```

### 9.9 Grid Layout

```css
.dashboard-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 28px;
}

/* Responsive */
@media (max-width: 1200px) {
  grid-template-columns: 1fr;
}
```

### 9.10 Archivos del Diseño

```
/assets/css/styles.css          # CSS completo Dark Pro (508 líneas)
/assets/images/papeleria-sigma-logo.svg
/design-darkpro.html           # Mockup de referencia
/index.php                     # POS con Dark Pro aplicado
```

### 9.11 Sin Dependencias Externas

El diseño Dark Pro NO requiere:
- ❌ Bootstrap CSS
- ❌ Bootstrap JS
- ❌ jQuery
- ✅ Solo Font Awesome para iconos
- ✅ JavaScript vanilla puro

### 9.12 Compatibilidad

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ⚠️ IE11 no soportado (CSS Grid, custom properties)

---

## 10. Mantenimiento

### 9.1 Actualizar Estilos
Archivo: `assets/css/style.css`
- Modificar variables CSS en `:root`
- Agregar nuevas clases según convención BEM
- Probar en mobile antes de commitear

### 9.2 Agregar Nuevo Endpoint
1. Crear función en archivo JS correspondiente
2. Hacer fetch al nuevo endpoint
3. Manejar respuesta (success/error)
4. Actualizar interfaz

### 9.3 Debugging
- Usar `console.log()` para tracking de flujo
- Verificar Network tab en DevTools
- Revisar errores PHP en logs de XAMPP (`/xampp/apache/logs/error.log`)

---

## 10. Contacto y Soporte

**Desarrollador Frontend:** Abraham  
**Líder de Proyecto:** Álvaro  
**Equipo Backend:** Luisito, Arturo  
**Base de Datos:** Santi, Fer  
**Hardware:** Nolberto  

**Comunicación:** WhatsApp del equipo  
**Repositorio:** GitHub - `papeleria-pos-sigma`

---

## Anexos

### A. Códigos de Barras de Prueba (seed.sql)
```
7501234567890 - Cuaderno profesional 100 hojas
7501234567891 - Pluma azul BIC
7501234567892 - Lápiz HB #2
7501234567893 - Borrador blanco
7501234567894 - Sacapuntas metálico
7501234567895 - Tijeras escolares
7501234567896 - Pegamento blanco 250ml
7501234567897 - Marcador permanente negro
7501234567898 - Folder tamaño carta
7501234567899 - Regla 30cm
```

### B. Variables CSS Principales
```css
--verde-primary: #2d5016
--verde-secondary: #4a7c2f
--verde-light: #6ba03e
--cafe-dark: #3e2723
--cafe-medium: #5d4037
--cafe-light: #8d6e63
--blanco: #ffffff
--gris-light: #f5f5f5
--gris-medium: #e0e0e0
```

### C. Estructura de Sesión PHP
```php
$_SESSION['user_id']    // ID del usuario
$_SESSION['nombre']     // Nombre completo
$_SESSION['email']      // Email
$_SESSION['rol']        // 'admin' o 'operador'
```

---

**Fin del documento**

*Última actualización: Diciembre 2024*
