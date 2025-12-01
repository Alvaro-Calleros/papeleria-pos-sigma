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
$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$precio_compra = $_POST['precio_compra'] ?? 0;
$precio_venta = $_POST['precio_venta'] ?? 0;
$codigo_barras = $_POST['codigo_barras'] ?? '';

if (!$id || empty($nombre) || empty($codigo_barras) || $precio_compra <= 0 || $precio_venta <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos']);
    exit();
}

$conn = getConnection();
$conn->begin_transaction();

try {
    // Validar que el producto existe
    $stmt = $conn->prepare("SELECT id FROM productos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Producto no encontrado");
    }
    $stmt->close();

    // Validar código de barras único (excluyendo el actual)
    $stmt = $conn->prepare("SELECT id FROM productos WHERE codigo_barras = ? AND id != ?");
    $stmt->bind_param('si', $codigo_barras, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("El código de barras ya está en uso por otro producto");
    }
    $stmt->close();

    // Procesar imagen (si se subió una nueva)
    $imagen_sql = "";
    $types = "ssddsi"; // nombre, descripcion, p_compra, p_venta, codigo, id
    $params = [$nombre, $descripcion, $precio_compra, $precio_venta, $codigo_barras, $id];

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];
        $allowed_types = ['image/jpeg', 'image/png'];
        
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Tipo de archivo no permitido. Solo JPG y PNG.");
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception("La imagen excede el tamaño máximo de 5MB");
        }

        $imagen_content = file_get_contents($file['tmp_name']);
        $imagen_tipo = $file['type'];
        
        $imagen_sql = ", imagen = ?, imagen_tipo = ?";
        $types = "ssddsssi"; // nombre, descripcion, p_compra, p_venta, codigo, img, img_type, id
        $params = [$nombre, $descripcion, $precio_compra, $precio_venta, $codigo_barras, $imagen_content, $imagen_tipo, $id];
    }

    // Actualizar producto
    $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio_compra = ?, precio_venta = ?, codigo_barras = ? $imagen_sql WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
?>
