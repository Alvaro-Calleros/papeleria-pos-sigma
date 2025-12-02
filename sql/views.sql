

-- Borrar vistas si ya existen (evita error al volver a importar)
DROP VIEW IF EXISTS v_productos_stock;
DROP VIEW IF EXISTS v_ventas_resumen;
DROP VIEW IF EXISTS v_productos_mas_vendidos;

-- Vista: v_productos_stock
-- Muestra productos con su stock actual
CREATE VIEW v_productos_stock AS
SELECT 
    p.id,
    p.nombre,
    p.codigo_barras,
    p.precio_venta,
    e.cantidad AS stock,
    p.activo
FROM productos p
INNER JOIN existencias e 
        ON p.id = e.producto_id;

-- Vista: v_ventas_resumen
-- Resumen de ventas con total de items
CREATE VIEW v_ventas_resumen AS
SELECT 
    v.id,
    v.folio,
    u.nombre AS cajero,
    v.total,
    v.fecha,
    COUNT(vd.id) AS items_vendidos
FROM ventas v
INNER JOIN usuarios u 
        ON v.usuario_id = u.id
LEFT JOIN ventas_detalle vd 
        ON v.id = vd.venta_id
GROUP BY v.id;

-- Vista: v_productos_mas_vendidos
-- Productos ordenados por cantidad vendida
CREATE VIEW v_productos_mas_vendidos AS
SELECT 
    p.id,
    p.nombre,
    p.codigo_barras,
    SUM(vd.cantidad) AS total_vendido,
    SUM(vd.subtotal) AS ingresos_generados
FROM ventas_detalle vd
INNER JOIN productos p 
        ON vd.producto_id = p.id
GROUP BY p.id
ORDER BY total_vendido DESC;