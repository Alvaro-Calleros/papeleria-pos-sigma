# Frontend (UX-UI) - Papelería POS Sigma

Responsable: Abraham

## Páginas y flujo

- `index.php` (POS)
  - Carrito con múltiples productos.
  - Totales (subtotal, IVA 16%, total).
  - Acciones:
    - Agregar producto por código de barras (`Enter`).
    - Cambiar cantidades (+/-), eliminar ítems.
    - Confirmar venta: abre `ticket.php?venta_id=...`.
  - Endpoints conectados:
    - `actions/ventas_add.php` (POST)
    - `actions/ventas_update.php` (POST)
    - `actions/ventas_remove.php` (POST)
    - `actions/ventas_clear.php` (POST)
    - `actions/ventas_confirm.php` (POST)

- `productos.php` (Admin)
  - Tabla con búsqueda y paginación.
  - Modal para crear/editar con preview de imagen.
  - Acciones conectadas:
    - `actions/productos_list.php` (GET)
    - `actions/productos_get.php` (GET)
    - `actions/productos_create.php` (POST FormData)
    - `actions/productos_update.php` (POST FormData)
    - `actions/productos_delete.php` (POST)

- `ticket.php`
  - Ahora consume datos reales desde `actions/print_ticket.php?venta_id=...`.
  - Estilos de impresión 80×40mm listos (térmica).

- `reportes.php`
  - Filtros (tipo, fecha inicio/fin), tabla de resultados (simulada por ahora).
  - Exportar CSV: integrado a backend vía `actions/export_csv.php`.

## Endpoints añadidos/ajustados

- `actions/export_csv.php` (nuevo)
  - Auth: admin.
  - Query params: `tipo=[ventas|productos|inventario|compras]`, `fechaInicio`, `fechaFin`.
  - Genera y descarga CSV (UTF-8 BOM) con datos desde BD.

- `ticket.php`
  - Reemplaza datos simulados por `actions/print_ticket.php`.

## Cómo probar

1) Iniciar sesión (admin u operador) y entrar a `index.php`.
2) Escanear o escribir un código de barras de `seed.sql` y presionar Enter.
3) Ajustar cantidades y confirmar venta. Aceptar abrir el ticket.
4) En `reportes.php`, elegir tipo + rango de fechas y presionar "Exportar CSV".

## Notas y pendientes

- Reportes (tabla en pantalla) aún con datos simulados; una vez que Backend exponga endpoints de consulta, conectarlo en `assets/js/reportes.js`.
- Si cambia el esquema por mejora de multi-producto, validar que `ventas_*` y CSV sigan funcionando.
- CSS principal: `assets/css/style.css`.
