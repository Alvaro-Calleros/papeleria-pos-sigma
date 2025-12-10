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
        case 'compras_rango':
            $start_date = $_GET['start'] ?? date('Y-m-d');
            $end_date = $_GET['end'] ?? date('Y-m-d');
            $start = $start_date . ' 00:00:00';
            $end = $end_date . ' 23:59:59';

            $stmt = $conn->prepare("SELECT c.id, c.folio, c.fecha, c.total, 
                                    COALESCE(p.nombre, 'Sin proveedor') as proveedor, 
                                    u.nombre as usuario
                                    FROM compras c 
                                    LEFT JOIN proveedores p ON c.proveedor_id = p.id
                                    INNER JOIN usuarios u ON c.creado_por = u.id 
                                    WHERE c.fecha BETWEEN ? AND ? 
                                    ORDER BY c.fecha DESC");
            $stmt->bind_param('ss', $start, $end);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Calcular productos comprados en el rango
            $stmt = $conn->prepare("SELECT IFNULL(SUM(cd.cantidad), 0) as productos_comprados
                                   FROM compras_detalle cd
                                   INNER JOIN compras c ON cd.compra_id = c.id
                                   WHERE c.fecha BETWEEN ? AND ?");
            $stmt->bind_param('ss', $start, $end);
            $stmt->execute();
            $prodComprados = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            echo json_encode([
                'success' => true, 
                'data' => $data,
                'stats' => [
                    'productos_comprados' => intval($prodComprados['productos_comprados'])
                ]
            ]);
            break;

        case 'detalle_compra':
            $compra_id = intval($_GET['compra_id'] ?? 0);
            
            if ($compra_id > 0) {
                $meta = [];
                // Cabecera
                $stmt = $conn->prepare("SELECT c.folio, c.fecha, c.total, 
                                        COALESCE(p.nombre, 'Sin proveedor') as proveedor, 
                                        u.nombre as usuario
                                        FROM compras c
                                        LEFT JOIN proveedores p ON c.proveedor_id = p.id
                                        INNER JOIN usuarios u ON c.creado_por = u.id
                                        WHERE c.id = ? LIMIT 1");
                $stmt->bind_param('i', $compra_id);
                $stmt->execute();
                $meta = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                // Detalles
                $stmt = $conn->prepare("SELECT cd.producto_id, p.nombre, cd.cantidad, cd.precio_unitario, cd.subtotal 
                                       FROM compras_detalle cd 
                                       INNER JOIN productos p ON cd.producto_id = p.id 
                                       WHERE cd.compra_id = ?");
                $stmt->bind_param('i', $compra_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                echo json_encode(['success' => true, 'data' => $data, 'meta' => $meta]);
            } else {
                throw new Exception('Compra no encontrada');
            }
            break;

        default:
            throw new Exception("Acción no válida");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
?>

