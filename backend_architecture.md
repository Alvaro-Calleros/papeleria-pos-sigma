# Arquitectura del Backend - Papelería POS Sigma

Este documento detalla la estructura de la API RESTful implementada para el sistema de Punto de Venta.

## Estructura General

El backend está construido en **PHP puro** sin frameworks, utilizando una arquitectura basada en **Actions** (endpoints individuales).

- **Base de Datos:** MySQL con `mysqli` y Prepared Statements.
- **Formato de Respuesta:** JSON `{ success: boolean, message: string, data: any }`.
- **Autenticación:** Basada en Sesiones PHP (`auth_user.php` para cajeros, `auth_admin.php` para administradores).

---

## Endpoints de Ventas (POS)

### 1. Agregar Producto al Carrito

- **Archivo:** `actions/ventas_add.php`
- **Método:** `POST`
- **Permisos:** Usuario Logueado
- **Parámetros:**
  - `codigo_barras` (string, requerido): Código del producto a agregar.
- **Validaciones:**
  - Producto debe existir y estar activo (`activo = 1`).
  - Debe haber stock suficiente (`stock > 0`).
  - No permite agregar más de lo disponible en stock.
- **Respuesta:** Estado actualizado del carrito y totales.

### 2. Obtener Carrito Actual

- **Archivo:** `actions/ventas_get.php`
- **Método:** `GET`
- **Permisos:** Usuario Logueado
- **Respuesta:** JSON con array de items en carrito y objeto de totales (subtotal, IVA, total).

### 3. Actualizar Cantidad de Item

- **Archivo:** `actions/ventas_update.php`
- **Método:** `POST`
- **Parámetros:**
  - `index` (int): Índice del item en el array del carrito.
  - `cambio` (int): Valor a sumar o restar (+1 o -1).
- **Validaciones:**
  - Verifica stock disponible antes de incrementar.
  - Si cantidad llega a 0, elimina el item.

### 4. Eliminar Item del Carrito

- **Archivo:** `actions/ventas_remove.php`
- **Método:** `POST`
- **Parámetros:** `index` (int).

### 5. Vaciar Carrito

- **Archivo:** `actions/ventas_clear.php`
- **Método:** `POST`

### 6. Confirmar Venta (Cobrar)

- **Archivo:** `actions/ventas_confirm.php`
- **Método:** `POST`
- **Permisos:** Usuario Logueado
- **Proceso:**
  1. Recalcula totales del servidor (seguridad).
  2. Inserta cabecera en tabla `ventas`.
  3. Inserta detalles en tabla `ventas_detalle`.
  4. **Nota:** El inventario se descuenta automáticamente mediante Triggers de MySQL (`trg_venta_detalle_after_insert`).
- **Respuesta:** `venta_id` y `folio` generado.

---

## Endpoints de Productos (Admin)

### 1. Listar Productos

- **Archivo:** `actions/productos_list.php`
- **Método:** `GET`
- **Parámetros:** `page`, `limit`, `search`, `activo`.
- **Respuesta:** Lista paginada de productos.

### 2. Crear Producto

- **Archivo:** `actions/productos_create.php`
- **Método:** `POST`
- **Parámetros:** `nombre`, `descripcion`, `precio_compra`, `precio_venta`, `codigo_barras`, `imagen` (file).
- **Validaciones:**
  - Código de barras único.
  - Imagen JPG/PNG máx 5MB.

### 3. Actualizar Producto

- **Archivo:** `actions/productos_update.php`
- **Método:** `POST`
- **Parámetros:** `id` + campos a editar.
- **Validaciones:**
  - Si cambia código de barras, verifica unicidad.

### 4. Eliminar Producto

- **Archivo:** `actions/productos_delete.php`
- **Método:** `POST`
- **Parámetros:** `id`.
- **Lógica:** Soft Delete (`UPDATE productos SET activo = 0`).

---

## Endpoints de Reportes

### 1. Obtener Datos de Reportes

- **Archivo:** `actions/reportes_get.php`
- **Método:** `GET`
- **Parámetro:** `action`
  - `ventas_rango`: Requiere `start` y `end` (fechas YYYY-MM-DD).
  - `mas_vendidos`: Top 10 productos.
  - `stock_bajo`: Productos con stock < 10.
  - `inventario`: Todos los productos activos con su stock.
  - `ventas_dia`: Totales del día actual.
