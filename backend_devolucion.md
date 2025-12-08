# Documentación de Cambios - Sistema de Devoluciones

Se han realizado las siguientes modificaciones en el backend para soportar el flujo de devoluciones y mejorar los reportes:

## 1. Archivo `actions/reportes_get.php`

### `ventas_rango`

- **Cambio:** Se agregó el campo `venta_id` a la respuesta JSON.
- **Propósito:** Permitir al frontend identificar la venta por su ID interno además del folio.

### `detalle_venta`

- **Cambio:** Ahora acepta el parámetro `folio` (opcional).
- **Lógica:** Si se envía `folio` en lugar de `venta_id`, el sistema busca automáticamente el ID de la venta correspondiente.

### `devoluciones_rango` (NUEVO)

- **Descripción:** Endpoint para listar devoluciones en un rango de fechas.
- **Parámetros:** `start`, `end` (fechas YYYY-MM-DD).
- **Respuesta:** Lista de devoluciones incluyendo:
  - `folio` (de la devolución)
  - `venta_folio` (folio de la venta original)
  - `fecha`
  - `cajero`
  - `total`

### `detalle_devolucion` (NUEVO)

- **Descripción:** Endpoint para obtener el detalle completo de una devolución.
- **Parámetros:** `devolucion_id` O `folio` (de la devolución).
- **Respuesta:** Objeto con dos claves:
  - `cabecera`: Datos generales (folio, venta original, cajero, fecha, total).
  - `detalle`: Array de productos devueltos (nombre, cantidad, precio, subtotal).

### `detalle_venta_completa` (NUEVO)

- **Descripción:** Endpoint para obtener cabecera y detalle de una venta en una sola petición.
- **Parámetros:** `folio`.
- **Respuesta:** Objeto con `cabecera` y `detalle`.

---

## 2. Archivo `actions/devoluciones_confirm.php`

### Mejoras en la recepción de datos

- **Búsqueda por Folio:** Ahora acepta el parámetro `folio` para identificar la venta original si no se tiene el `venta_id`.

### Devolución Completa Automática

- **Lógica:** Si no se envía la lista de `productos` a devolver (o el array está vacío), el sistema asume que es una **devolución total**.
- **Acción:** Busca automáticamente todos los productos de la venta original y genera la devolución por el total de la venta.

### Restauración de Stock

- **Nota:** La restauración del stock en la tabla `existencias` se sigue manejando a través de **Triggers** en la base de datos (como estaba indicado en el código original), asegurando la integridad de los datos.
