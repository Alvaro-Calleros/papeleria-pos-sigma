<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$id = $_POST['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
    exit();
}

$conn = getConnection();

try {
    // Soft delete
    $stmt = $conn->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Producto no encontrado o ya inactivo");
    }
    
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
?>
