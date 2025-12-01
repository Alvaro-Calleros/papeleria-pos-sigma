<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_user.php';

header('Content-Type: application/json');

$venta_id = $_GET['venta_id'] ?? null;

if (!$venta_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de venta requerido']);
    exit();
}

$conn = getConnection();

try {
    // Obtener datos de la venta
    $stmt = $conn->prepare("SELECT v.id, v.folio, u.nombre as cajero, v.subtotal, v.iva, v.total, v.fecha 
                            FROM ventas v 
                            INNER JOIN usuarios u ON v.usuario_id = u.id 
                            WHERE v.id = ?");
    $stmt->bind_param('i', $venta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Venta no encontrada");
    }
    
    $venta = $result->fetch_assoc();
    $stmt->close();
    
    // Obtener detalles
    $stmt = $conn->prepare("SELECT p.nombre as producto_nombre, vd.cantidad, vd.precio_unitario, vd.subtotal, p.codigo_barras 
                            FROM ventas_detalle vd 
                            INNER JOIN productos p ON vd.producto_id = p.id 
                            WHERE vd.venta_id = ?");
    $stmt->bind_param('i', $venta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $detalle = [];
    while ($row = $result->fetch_assoc()) {
        $detalle[] = [
            'producto_nombre' => $row['producto_nombre'],
            'cantidad' => (int)$row['cantidad'],
            'precio_unitario' => (float)$row['precio_unitario'],
            'subtotal' => (float)$row['subtotal'],
            'codigo_barras' => $row['codigo_barras']
        ];
    }
    $stmt->close();
    
    // Formatear números
    $venta['subtotal'] = (float)$venta['subtotal'];
    $venta['iva'] = (float)$venta['iva'];
    $venta['total'] = (float)$venta['total'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'venta' => $venta,
            'detalle' => $detalle
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
?>
