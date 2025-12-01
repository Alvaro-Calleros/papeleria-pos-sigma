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

$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$precio_compra = $_POST['precio_compra'] ?? 0;
$precio_venta = $_POST['precio_venta'] ?? 0;
$codigo_barras = $_POST['codigo_barras'] ?? '';

// Validaciones básicas
if (empty($nombre) || empty($codigo_barras) || $precio_compra <= 0 || $precio_venta <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos']);
    exit();
}

$conn = getConnection();
$conn->begin_transaction();

try {
    // Validar código de barras único
    $stmt = $conn->prepare("SELECT id FROM productos WHERE codigo_barras = ?");
    $stmt->bind_param('s', $codigo_barras);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("El código de barras ya existe");
    }
    $stmt->close();

    // Procesar imagen
    $imagen = null;
    $imagen_tipo = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];
        $allowed_types = ['image/jpeg', 'image/png'];
        
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Tipo de archivo no permitido. Solo JPG y PNG.");
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            throw new Exception("La imagen excede el tamaño máximo de 5MB");
        }

        $imagen = file_get_contents($file['tmp_name']);
        $imagen_tipo = $file['type'];
    }

    // Insertar producto
    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio_compra, precio_venta, codigo_barras, imagen, imagen_tipo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssddsss', $nombre, $descripcion, $precio_compra, $precio_venta, $codigo_barras, $imagen, $imagen_tipo);
    $stmt->execute();
    $producto_id = $conn->insert_id;
    $stmt->close();

    // Insertar existencias iniciales (0)
    $stmt = $conn->prepare("INSERT INTO existencias (producto_id, cantidad) VALUES (?, 0)");
    $stmt->bind_param('i', $producto_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Producto creado exitosamente', 'id' => $producto_id]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
?>
