<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_user.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El carrito está vacío']);
    exit();
}

$conn = getConnection();
$conn->begin_transaction();

try {
    // 1. Calcular totales
    $subtotal = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $subtotal += $item['precio_unitario'] * $item['cantidad'];
    }
    $iva = round($subtotal * 0.16, 2);
    $total = $subtotal + $iva;
    $usuario_id = $_SESSION['user_id'];

    // 2. Insertar Venta (Cabecera)
    // Vamos a usar un formato V-YYYYMMDD-ID (despues del insert) o simplemente V-TIMESTAMP.
    $folio = 'V-' . date('YmdHis') . '-' . rand(100, 999);

    $stmt = $conn->prepare("INSERT INTO ventas (folio, usuario_id, subtotal, iva, total, fecha) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param('siddd', $folio, $usuario_id, $subtotal, $iva, $total);
    $stmt->execute();
    $venta_id = $conn->insert_id;
    $stmt->close();

    // 3. Insertar Detalle
    $stmt_detalle = $conn->prepare("INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");

    foreach ($_SESSION['carrito'] as $item) {
        $item_subtotal = $item['precio_unitario'] * $item['cantidad'];
        $stmt_detalle->bind_param('iiidd', $venta_id, $item['producto_id'], $item['cantidad'], $item['precio_unitario'], $item_subtotal);
        $stmt_detalle->execute();
    }
    $stmt_detalle->close();

    // 4. Commit (Los triggers en DB se encargan del stock)
    $conn->commit();

    // 5. Limpiar carrito
    unset($_SESSION['carrito']);

    echo json_encode([
        'success' => true,
        'message' => 'Venta registrada exitosamente',
        'venta_id' => $venta_id,
        'folio' => $folio
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al procesar la venta: ' . $e->getMessage()]);
}

closeConnection($conn);
?>
