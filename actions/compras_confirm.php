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

$input = json_decode(file_get_contents('php://input'), true);
$productos = $input['productos'] ?? [];

if (empty($productos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Lista de productos vacía']);
    exit();
}

$creado_por = $_SESSION['user_id'];
$proveedor_id = $input['proveedor_id'] ?? null;
$proveedor_nombre = trim($input['proveedor_nombre'] ?? '');

$conn = getConnection();
$conn->begin_transaction();

try {
    $total = 0;
    $productos_validados = [];

    // Validar productos y calcular total
    foreach ($productos as $item) {
        $producto_id = $item['producto_id'];
        $cantidad = $item['cantidad'];
        $precio_compra = $item['precio_compra']; // Precio al que se compró en este momento

        if ($cantidad <= 0) {
            throw new Exception("Cantidad inválida para producto ID $producto_id");
        }

        // Verificar que existe
        $stmt = $conn->prepare("SELECT id FROM productos WHERE id = ?");
        $stmt->bind_param('i', $producto_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Producto ID $producto_id no encontrado");
        }
        $stmt->close();

        $subtotal = $cantidad * $precio_compra;
        $total += $subtotal;

        $productos_validados[] = [
            'producto_id' => $producto_id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio_compra,
            'subtotal' => $subtotal
        ];
    }

    // Si no se envió proveedor_id pero sí proveedor_nombre, crear proveedor
    if (empty($proveedor_id) && $proveedor_nombre !== '') {
        $stmt = $conn->prepare("INSERT INTO proveedores (nombre) VALUES (?)");
        $stmt->bind_param('s', $proveedor_nombre);
        $stmt->execute();
        $proveedor_id = $conn->insert_id;
        $stmt->close();
    }

    // Generar folio C-XXXXX
    $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(folio, 3) AS UNSIGNED)) as ultimo_folio FROM compras WHERE folio LIKE 'C-%'");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $nuevo_numero = ($row['ultimo_folio'] ?? 0) + 1;
    $folio = 'C-' . str_pad($nuevo_numero, 5, '0', STR_PAD_LEFT);
    $stmt->close();

    // Insertar compra (manejar proveedor_id null)
    if ($proveedor_id !== null) {
        $stmt = $conn->prepare("INSERT INTO compras (folio, proveedor_id, creado_por, total) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('siid', $folio, $proveedor_id, $creado_por, $total);
    } else {
        $stmt = $conn->prepare("INSERT INTO compras (folio, proveedor_id, creado_por, total) VALUES (?, NULL, ?, ?)");
        $stmt->bind_param('sid', $folio, $creado_por, $total);
    }
    $stmt->execute();
    $compra_id = $conn->insert_id;
    $stmt->close();

    // Verificar si el trigger está instalado
    $trigger_exists = false;
    $check_trigger = $conn->query("SHOW TRIGGERS WHERE `Trigger` = 'trg_compra_detalle_after_insert'");
    if ($check_trigger && $check_trigger->num_rows > 0) {
        $trigger_exists = true;
    }

    // Insertar detalles y actualizar stock
    foreach ($productos_validados as $item) {
        // Insertar detalle de compra (el trigger actualizará automáticamente el stock si está instalado)
        $stmt = $conn->prepare("INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iiidd', $compra_id, $item['producto_id'], $item['cantidad'], $item['precio_unitario'], $item['subtotal']);
        $stmt->execute();
        $stmt->close();

        // Actualizar stock manualmente solo si el trigger NO está instalado
        // Si el trigger está instalado, él se encargará de actualizar el stock
        if (!$trigger_exists) {
            $stmt = $conn->prepare("INSERT INTO existencias (producto_id, cantidad, fecha_actualizacion) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE cantidad = cantidad + ?, fecha_actualizacion = NOW()");
            $stmt->bind_param('iii', $item['producto_id'], $item['cantidad'], $item['cantidad']);
            $stmt->execute();
            $stmt->close();
        }

        // Actualizar precio de compra en tabla productos
        $stmt = $conn->prepare("UPDATE productos SET precio_compra = ? WHERE id = ?");
        $stmt->bind_param('di', $item['precio_unitario'], $item['producto_id']);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Compra registrada exitosamente',
        'folio' => $folio
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
?>
