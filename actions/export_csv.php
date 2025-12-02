<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_admin.php';

// Parámetros
$tipo = $_GET['tipo'] ?? 'ventas';
$fechaInicio = $_GET['fechaInicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fechaFin = $_GET['fechaFin'] ?? date('Y-m-d');

$permitidos = ['ventas', 'productos', 'inventario', 'compras'];
if (!in_array($tipo, $permitidos, true)) {
    http_response_code(400);
    echo 'Tipo de reporte no válido';
    exit();
}

$conn = getConnection();

// Forzar descarga CSV
$filename = sprintf('reporte_%s_%s.csv', $tipo, date('Y-m-d'));
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Abrir salida estándar
$out = fopen('php://output', 'w');

// BOM para UTF-8 (evitar problemas con acentos en Excel)
fwrite($out, "\xEF\xBB\xBF");

try {
    switch ($tipo) {
        case 'ventas':
            // Headers
            fputcsv($out, ['Folio', 'Fecha', 'Cajero', 'Items', 'Total']);

            $sql = "SELECT v.folio, DATE_FORMAT(v.fecha, '%Y-%m-%d %H:%i') as fecha,
                           u.nombre AS cajero, COUNT(vd.id) AS items, v.total
                    FROM ventas v
                    INNER JOIN usuarios u ON v.usuario_id = u.id
                    LEFT JOIN ventas_detalle vd ON vd.venta_id = v.id
                    WHERE DATE(v.fecha) BETWEEN ? AND ?
                    GROUP BY v.id
                    ORDER BY v.fecha DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $fechaInicio, $fechaFin);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                fputcsv($out, [$row['folio'], $row['fecha'], $row['cajero'], (int)$row['items'], number_format((float)$row['total'], 2, '.', '')]);
            }
            $stmt->close();
            break;

        case 'productos': // Más vendidos
            fputcsv($out, ['Producto', 'Código', 'Cantidad Vendida', 'Ingresos Generados']);
            $sql = "SELECT p.nombre AS producto, p.codigo_barras AS codigo,
                           COALESCE(SUM(vd.cantidad),0) AS cantidad,
                           COALESCE(SUM(vd.subtotal),0) AS ingresos
                    FROM productos p
                    INNER JOIN ventas_detalle vd ON vd.producto_id = p.id
                    INNER JOIN ventas v ON v.id = vd.venta_id
                    WHERE DATE(v.fecha) BETWEEN ? AND ?
                    GROUP BY p.id
                    ORDER BY cantidad DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $fechaInicio, $fechaFin);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                fputcsv($out, [
                    $row['producto'],
                    $row['codigo'],
                    (int)$row['cantidad'],
                    number_format((float)$row['ingresos'], 2, '.', '')
                ]);
            }
            $stmt->close();
            break;

        case 'inventario':
            fputcsv($out, ['ID', 'Producto', 'Código', 'Stock', 'Precio', 'Valor Inventario']);
            $sql = "SELECT p.id, p.nombre AS producto, p.codigo_barras AS codigo,
                           COALESCE(e.cantidad,0) AS stock, p.precio_venta AS precio,
                           (COALESCE(e.cantidad,0) * p.precio_venta) AS valor
                    FROM productos p
                    LEFT JOIN existencias e ON e.producto_id = p.id
                    WHERE p.activo = 1
                    ORDER BY p.nombre ASC";
            $res = $conn->query($sql);
            while ($row = $res->fetch_assoc()) {
                fputcsv($out, [
                    (int)$row['id'],
                    $row['producto'],
                    $row['codigo'],
                    (int)$row['stock'],
                    number_format((float)$row['precio'], 2, '.', ''),
                    number_format((float)$row['valor'], 2, '.', '')
                ]);
            }
            break;

        case 'compras':
            fputcsv($out, ['Folio', 'Fecha', 'Usuario', 'Items', 'Total']);
            $sql = "SELECT c.folio, DATE_FORMAT(c.fecha, '%Y-%m-%d %H:%i') as fecha,
                           u.nombre AS usuario, COUNT(cd.id) AS items, c.total
                    FROM compras c
                    INNER JOIN usuarios u ON c.usuario_id = u.id
                    LEFT JOIN compras_detalle cd ON cd.compra_id = c.id
                    WHERE DATE(c.fecha) BETWEEN ? AND ?
                    GROUP BY c.id
                    ORDER BY c.fecha DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $fechaInicio, $fechaFin);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                fputcsv($out, [$row['folio'], $row['fecha'], $row['usuario'], (int)$row['items'], number_format((float)$row['total'], 2, '.', '')]);
            }
            $stmt->close();
            break;
    }
} catch (Throwable $e) {
    // En caso de error, escribir una línea con el mensaje
    fputcsv($out, ['Error', $e->getMessage()]);
}

fclose($out);
closeConnection($conn);
exit();
?>
