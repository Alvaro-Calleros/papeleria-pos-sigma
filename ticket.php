<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_user.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$venta_id = $_GET['venta_id'] ?? null;

if (!$venta_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de venta requerido']);
    exit();
}

$conn = getConnection();

try {
    // Obtener datos de la venta
    $stmt = $conn->prepare("SELECT v.*, u.nombre as cajero 
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
    
    // Obtener detalle de la venta
    $stmt = $conn->prepare("SELECT vd.*, p.nombre as producto_nombre, p.codigo_barras 
                            FROM ventas_detalle vd 
                            INNER JOIN productos p ON vd.producto_id = p.id 
                            WHERE vd.venta_id = ?");
    $stmt->bind_param('i', $venta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $detalle = [];
    while ($row = $result->fetch_assoc()) {
        $detalle[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'venta' => $venta,
            'detalle' => $detalle
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos: ' . $e->getMessage()
    ]);
}

closeConnection($conn);
?>