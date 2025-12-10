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

            $stmt = $conn->prepare("SELECT d.id, d.folio, d.fecha, d.total, v.folio AS venta_folio, u.nombre AS cajero
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

                $stmt = $conn->prepare("SELECT vd.producto_id, p.nombre, vd.cantidad, vd.precio_unitario, vd.subtotal 
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

        case 'detalle_devolucion':
            $devolucion_id = $_GET['devolucion_id'] ?? 0;
            $folio = $_GET['folio'] ?? '';

            if ($devolucion_id == 0 && !empty($folio)) {
                $stmt = $conn->prepare("SELECT id FROM devoluciones WHERE folio = ?");
                $stmt->bind_param('s', $folio);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $devolucion_id = $row['id'];
                }
                $stmt->close();
            }

            if ($devolucion_id > 0) {
                // Cabecera
                $stmt = $conn->prepare("SELECT d.folio, v.folio as venta_folio, u.nombre as cajero, d.fecha, d.total 
                                      FROM devoluciones d 
                                      INNER JOIN ventas v ON d.venta_id = v.id 
                                      INNER JOIN usuarios u ON d.usuario_id = u.id 
                                      WHERE d.id = ?");
                $stmt->bind_param('i', $devolucion_id);
                $stmt->execute();
                $cabecera = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                // Detalle
                $stmt = $conn->prepare("SELECT p.nombre, dd.cantidad, dd.precio_unitario, dd.subtotal 
                                      FROM devoluciones_detalle dd 
                                      INNER JOIN productos p ON dd.producto_id = p.id 
                                      WHERE dd.devolucion_id = ?");
                $stmt->bind_param('i', $devolucion_id);
                $stmt->execute();
                $detalle = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                $data = ['cabecera' => $cabecera, 'detalle' => $detalle];
            }
            break;

        case 'detalle_venta_completa':
            $folio = $_GET['folio'] ?? '';
            
            if (!empty($folio)) {
                // Cabecera
                $stmt = $conn->prepare("SELECT v.id, v.folio, u.nombre as cajero, v.fecha, v.total 
                                      FROM ventas v 
                                      INNER JOIN usuarios u ON v.usuario_id = u.id 
                                      WHERE v.folio = ?");
                $stmt->bind_param('s', $folio);
                $stmt->execute();
                $res = $stmt->get_result();
                $cabecera = $res->fetch_assoc();
                $stmt->close();

                if ($cabecera) {
                    $venta_id = $cabecera['id'];
                    // Detalle
                    $stmt = $conn->prepare("SELECT p.nombre, vd.cantidad, vd.precio_unitario, vd.subtotal 
                                          FROM ventas_detalle vd 
                                          INNER JOIN productos p ON vd.producto_id = p.id 
                                          WHERE vd.venta_id = ?");
                    $stmt->bind_param('i', $venta_id);
                    $stmt->execute();
                    $detalle = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();

                    $data = ['cabecera' => $cabecera, 'detalle' => $detalle];
                }
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
