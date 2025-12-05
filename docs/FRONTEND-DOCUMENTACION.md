# Documentación Frontend - Sistema POS Papelería Sigma

**Responsable:** Abraham  
**Fecha:** Diciembre 2024  
**Tecnologías:** HTML5, CSS3, JavaScript (Vanilla), Bootstrap 5.3, PHP 8.x

---

## 1. Estructura General del Proyecto

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

#### Modal de Creación/Edición:
- Formulario con:
  - Nombre del producto
  - Código de barras (único)
  - Descripción
  - Precio de compra
  - Precio de venta
  - Upload de imagen (JPG/PNG, máx 5MB)
  - Preview de imagen en tiempo real

**Funcionalidades JavaScript (`productos.js`):**

```javascript
// Funciones principales
- cargarProductos(page)    // Carga lista paginada desde BD
- renderProductos()        // Renderiza tabla
- renderPaginacion()       // Renderiza controles de paginación
- buscarProductos()        // Aplica búsqueda y filtros
- guardarProducto()        // Crea o actualiza producto
- editarProducto(id)       // Carga datos en modal para edición
- eliminarProducto(id)     // Soft delete (activo = 0)
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
  - Productos más vendidos
  - Inventario
  - Compras (en construcción)
- Rango de fechas (inicio - fin)

#### Tabla de Resultados:
- Headers dinámicos según tipo de reporte
- Datos cargados desde BD
- Resumen estadístico (cards superiores):
  - Total de ventas
  - Ingresos totales
  - Productos vendidos
  - Stock total

#### Acciones:
- **Exportar CSV:** Descarga archivo CSV con datos
- **Imprimir:** Vista optimizada para impresión A4

**Funcionalidades JavaScript (`reportes.js`):**

```javascript
// Funciones principales
- generarReporte()              // Genera reporte según tipo y fechas
- generarReporteVentas()        // Consulta ventas en rango
- generarReporteMasVendidos()   // Top productos vendidos
- generarReporteInventario()    // Estado actual de inventario
- exportarCSV()                 // Descarga datos en formato CSV
```

**Endpoints Conectados:**
- `GET actions/reportes_get.php?action=ventas_rango&start=X&end=Y`
- `GET actions/reportes_get.php?action=mas_vendidos`
- `GET actions/reportes_get.php?action=inventario`
- `GET actions/export_csv.php?tipo=X&fechaInicio=Y&fechaFin=Z` - Exportar CSV

**Tipos de Reportes:**

1. **Ventas:**
   - Folio, Fecha, Cajero, Total
   - Rango de fechas obligatorio
   
2. **Productos Más Vendidos:**
   - Nombre, Código, Cantidad vendida, Ingresos generados
   - Ordenado por cantidad descendente
   
3. **Inventario:**
   - ID, Nombre, Código, Stock, Precio, Valor total
   - Alerta de stock bajo (< 10 unidades)

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
| Endpoint                     | Método | Descripción              |
|------------------------------|--------|--------------------------|
| `actions/reportes_get.php`   | GET    | Generar reporte          |
| `actions/export_csv.php`     | GET    | Exportar CSV             |
| `actions/print_ticket.php`   | GET    | Datos para ticket        |

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

### 8.1 Funcionalidades Pendientes
- [ ] Módulo de compras (ingresar stock)
- [ ] Módulo de devoluciones
- [ ] Historial de ventas con búsqueda avanzada
- [ ] Dashboard con gráficas (Chart.js)
- [ ] Modo oscuro (dark mode)
- [ ] PWA (Progressive Web App) para uso offline

### 8.2 Optimizaciones Técnicas
- [ ] Implementar SPA con framework (React/Vue)
- [ ] Caché de productos en localStorage
- [ ] Lazy loading de imágenes
- [ ] Compresión de assets (minify CSS/JS)
- [ ] Service Workers para offline support

### 8.3 UX/UI
- [ ] Sonidos de feedback (beep al escanear)
- [ ] Animaciones más fluidas (Framer Motion)
- [ ] Temas personalizables
- [ ] Tooltips informativos
- [ ] Tutorial interactivo para nuevos usuarios

---

## 9. Mantenimiento

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
