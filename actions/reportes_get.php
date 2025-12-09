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
            $start = $start_date . ' 00:00:00';
            $end = $end_date . ' 23:59:59';

            $stmt = $conn->prepare("SELECT v.id, v.folio, v.fecha, v.total, u.nombre as cajero 
                                  FROM ventas v 
                                  INNER JOIN usuarios u ON v.usuario_id = u.id 
                                  WHERE v.fecha BETWEEN ? AND ? 
                                  ORDER BY v.fecha DESC");
            $stmt->bind_param('ss', $start, $end);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Calcular productos vendidos en el rango
            $stmt = $conn->prepare("SELECT IFNULL(SUM(vd.cantidad), 0) as productos_vendidos
                                   FROM ventas_detalle vd
                                   INNER JOIN ventas v ON vd.venta_id = v.id
                                   WHERE v.fecha BETWEEN ? AND ?");
            $stmt->bind_param('ss', $start, $end);
            $stmt->execute();
            $prodVendidos = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Stock total actual
            $stmtStock = $conn->query("SELECT IFNULL(SUM(e.cantidad), 0) as stock_total FROM existencias e WHERE e.producto_id IN (SELECT id FROM productos WHERE activo = 1)");
            $stockInfo = $stmtStock->fetch_assoc();

            // Retornar datos + estadísticas
            echo json_encode([
                'success' => true, 
                'data' => $data,
                'stats' => [
                    'productos_vendidos' => intval($prodVendidos['productos_vendidos']),
                    'stock_total' => intval($stockInfo['stock_total'])
                ]
            ]);
            closeConnection($conn);
            return;

        case 'devoluciones_rango':
            $start_date = $_GET['start'] ?? date('Y-m-d');
            $end_date = $_GET['end'] ?? date('Y-m-d');
            $start = $start_date . ' 00:00:00';
            $end = $end_date . ' 23:59:59';

            $stmt = $conn->prepare("SELECT d.folio, d.fecha, d.total, v.folio AS venta_folio, u.nombre AS cajero
                                   FROM devoluciones d
                                   LEFT JOIN ventas v ON d.venta_id = v.id
                                   LEFT JOIN usuarios u ON d.usuario_id = u.id
                                   WHERE d.fecha BETWEEN ? AND ?
                                   ORDER BY d.fecha DESC");
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
            $venta_id = intval($_GET['venta_id'] ?? 0);
            $folio = $_GET['folio'] ?? '';

            if ($venta_id === 0 && $folio !== '') {
                $stmt = $conn->prepare("SELECT id FROM ventas WHERE folio = ? LIMIT 1");
                $stmt->bind_param('s', $folio);
                $stmt->execute();
                $resVenta = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $venta_id = $resVenta['id'] ?? 0;
            }

            if ($venta_id > 0) {
                $meta = [];
                $stmt = $conn->prepare("SELECT v.folio, v.fecha, v.total, u.nombre AS cajero
                                       FROM ventas v
                                       INNER JOIN usuarios u ON v.usuario_id = u.id
                                       WHERE v.id = ? LIMIT 1");
                $stmt->bind_param('i', $venta_id);
                $stmt->execute();
                $meta = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $stmt = $conn->prepare("SELECT p.nombre, vd.cantidad, vd.precio_unitario, vd.subtotal 
                                       FROM ventas_detalle vd 
                                       INNER JOIN productos p ON vd.producto_id = p.id 
                                       WHERE vd.venta_id = ?");
                $stmt->bind_param('i', $venta_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                echo json_encode(['success' => true, 'data' => $data, 'meta' => $meta]);
                closeConnection($conn);
                return;
            } else {
                throw new Exception('Venta no encontrada');
            }
            break;

        case 'detalle_devolucion':
            $folio = $_GET['folio'] ?? '';
            if ($folio === '') {
                throw new Exception('Folio de devolución requerido');
            }

            $stmt = $conn->prepare("SELECT d.folio, d.fecha, d.total, v.folio AS venta_folio, u.nombre AS cajero
                                   FROM devoluciones d
                                   LEFT JOIN ventas v ON d.venta_id = v.id
                                   LEFT JOIN usuarios u ON d.usuario_id = u.id
                                   WHERE d.folio = ? LIMIT 1");
            $stmt->bind_param('s', $folio);
            $stmt->execute();
            $cabecera = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$cabecera) {
                throw new Exception('Devolución no encontrada');
            }

            $stmt = $conn->prepare("SELECT p.nombre, dd.cantidad, dd.precio_unitario, dd.subtotal
                                   FROM devoluciones_detalle dd
                                   INNER JOIN productos p ON dd.producto_id = p.id
                                   WHERE dd.devolucion_id = (SELECT id FROM devoluciones WHERE folio = ? LIMIT 1)");
            $stmt->bind_param('s', $folio);
            $stmt->execute();
            $result = $stmt->get_result();
            $detalle = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            echo json_encode(['success' => true, 'data' => ['cabecera' => $cabecera, 'detalle' => $detalle]]);
            closeConnection($conn);
            return;

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
