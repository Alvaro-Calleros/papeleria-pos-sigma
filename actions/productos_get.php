<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_admin.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
    exit();
}

$conn = getConnection();

try {
    $stmt = $conn->prepare("SELECT id, nombre, descripcion, precio_compra, precio_venta, codigo_barras, imagen, imagen_tipo FROM productos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        $stmt->close();
        closeConnection($conn);
        exit();
    }

    $producto = $result->fetch_assoc();
    if (!empty($producto['imagen'])) {
        $producto['imagen'] = base64_encode($producto['imagen']);
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'data' => $producto
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
