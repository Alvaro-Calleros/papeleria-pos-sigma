<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_admin.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$conn = getConnection();

try {
    $data = [];

    switch ($action) {
        case 'ventas_rango':
            $start_date = $_GET['start'] ?? date('Y-m-d');
            $end_date = $_GET['end'] ?? date('Y-m-d');
            // Agregar horas para cubrir todo el día
            $start = $start_date . ' 00:00:00';
            $end = $end_date . ' 23:59:59';

            $stmt = $conn->prepare("SELECT v.folio, v.fecha, v.total, u.nombre as cajero 
                                  FROM ventas v 
                                  INNER JOIN usuarios u ON v.usuario_id = u.id 
                                  WHERE v.fecha BETWEEN ? AND ? 
                                  ORDER BY v.fecha DESC");
            $stmt->bind_param('ss', $start, $end);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            break;

        case 'stock_bajo':
            $result = $conn->query("SELECT * FROM v_productos_stock WHERE stock < 10 AND activo = 1");
            $data = $result->fetch_all(MYSQLI_ASSOC);
            break;

        case 'inventario':
            $result = $conn->query("SELECT * FROM v_productos_stock WHERE activo = 1");
            $data = $result->fetch_all(MYSQLI_ASSOC);
            break;

        case 'ventas_dia':
            $result = $conn->query("SELECT COUNT(*) as total_ventas, IFNULL(SUM(total), 0) as ingresos FROM ventas WHERE DATE(fecha) = CURDATE()");
            $data = $result->fetch_assoc();
            break;

        case 'mas_vendidos':
            // Limitamos a top 10
            $result = $conn->query("SELECT * FROM v_productos_mas_vendidos LIMIT 10");
            $data = $result->fetch_all(MYSQLI_ASSOC);
            break;
            
        case 'detalle_venta':
             $venta_id = $_GET['venta_id'] ?? 0;
             if ($venta_id > 0) {
                 $stmt = $conn->prepare("SELECT p.nombre, vd.cantidad, vd.precio_unitario, vd.subtotal 
                                       FROM ventas_detalle vd 
                                       INNER JOIN productos p ON vd.producto_id = p.id 
                                       WHERE vd.venta_id = ?");
                 $stmt->bind_param('i', $venta_id);
                 $stmt->execute();
                 $result = $stmt->get_result();
                 $data = $result->fetch_all(MYSQLI_ASSOC);
                 $stmt->close();
             }
             break;

        default:
            throw new Exception("Acción no válida");
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
?>
