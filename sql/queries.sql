-- Ventas por rango de fechas
SELECT v.folio, v.fecha, v.total, u.nombre as cajero
FROM ventas v
INNER JOIN usuarios u ON v.usuario_id = u.id
WHERE v.fecha BETWEEN ? AND ?
ORDER BY v.fecha DESC;

-- Productos con stock bajo (menos de 10)
SELECT * FROM v_productos_stock
WHERE stock < 10 AND activo = 1;

-- Total de ventas del día
SELECT COUNT(*) as total_ventas, 
       SUM(total) as ingresos
FROM ventas
WHERE DATE(fecha) = CURDATE();