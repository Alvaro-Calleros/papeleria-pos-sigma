# Documentación de Cambios Backend

Este documento detalla los módulos implementados para la gestión de productos, compras y devoluciones en el sistema POS.

## 1. Gestión de Productos (CRUD)

Ubicación: `/actions/`

### `productos_create.php`

**Objetivo:** Registrar nuevos productos en la base de datos.

- **Entrada:** `nombre`, `descripcion`, `precio_compra`, `precio_venta`, `codigo_barras`, `imagen` (archivo).
- **Proceso:**
  1. Valida que los campos obligatorios no estén vacíos.
  2. Verifica que el `codigo_barras` sea único en la BD.
  3. Procesa la imagen (valida tipo JPG/PNG y tamaño < 5MB) y la convierte a binario (BLOB).
  4. Inserta el producto en la tabla `productos`.
  5. Inicializa el inventario en 0 en la tabla `existencias`.
  6. Usa transacciones para asegurar que se creen ambos registros o ninguno.

### `productos_update.php`

**Objetivo:** Modificar productos existentes.

- **Entrada:** `id` y campos a actualizar (incluyendo imagen opcional).
- **Proceso:**
  1. Valida que el producto exista.
  2. Si se cambia el código de barras, verifica que no choque con otro producto existente.
  3. Si se sube imagen nueva, actualiza el campo BLOB; si no, lo deja igual.
  4. Ejecuta `UPDATE` en la tabla `productos`.

### `productos_delete.php`

**Objetivo:** Eliminar productos de forma segura.

- **Entrada:** `id`.
- **Proceso:**
  1. No elimina el registro físicamente (para no romper historial de ventas).
  2. Ejecuta `UPDATE productos SET activo = 0`.
  3. Esto oculta el producto de las listas pero mantiene la integridad de datos.

### `productos_list.php`

**Objetivo:** Listar productos para el frontend.

- **Entrada:** `page`, `limit`, `search`.
- **Proceso:**
  1. Construye una consulta dinámica (`WHERE ... LIKE ...`) para búsqueda.
  2. Implementa paginación (`LIMIT` y `OFFSET`).
  3. Devuelve un JSON con la lista de productos y metadatos de paginación.

## 2. Compras (Entrada de Inventario)

### `compras_confirm.php`

**Objetivo:** Registrar compras a proveedores y aumentar stock.

- **Entrada:** Lista de productos y cantidades.
- **Proceso:**
  1. Genera un folio único consecutivo (`C-XXXXX`).
  2. Inserta la cabecera de la compra en la tabla `compras`.
  3. Itera sobre los productos:
     - Inserta detalle en `compras_detalle`.
     - **Aumenta** la cantidad en la tabla `existencias` (`cantidad = cantidad + ?`).
     - Opcionalmente actualiza el costo (`precio_compra`) del producto.
  4. Todo se envuelve en una transacción (`begin_transaction` / `commit`).

## 3. Devoluciones (Reingreso de Inventario)

### `devoluciones_confirm.php`

**Objetivo:** Procesar devoluciones de clientes.

- **Entrada:** `venta_id`, lista de productos a devolver.
- **Proceso:**
  1. Verifica que la venta original exista.
  2. Valida que los productos devueltos pertenezcan a esa venta.
  3. Genera folio de devolución (`D-XXXXX`).
  4. Registra la devolución en `devoluciones` y `devoluciones_detalle`.
  5. **Reingresa** los productos al stock (`UPDATE existencias SET cantidad = cantidad + ?`).

## Notas Técnicas

- **Seguridad:** Todos los endpoints verifican sesión activa (`auth_admin.php` o `auth_user.php`).
- **Base de Datos:** Se usa `mysqli` con **Prepared Statements** para prevenir inyección SQL.
- **Transacciones:** Las operaciones críticas usan `COMMIT` y `ROLLBACK` para evitar inconsistencias.
