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
    echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
    exit();
}

$index = isset($_POST['index']) ? (int)$_POST['index'] : null;
$cambio = isset($_POST['cambio']) ? (int)$_POST['cambio'] : 0;

if ($index === null || $cambio === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit();
}

if (!array_key_exists($index, $_SESSION['carrito'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ítem de carrito no encontrado']);
    exit();
}

$carrito = &$_SESSION['carrito'];

$item = &$carrito[$index];
$nuevaCantidad = $item['cantidad'] + $cambio;

if ($cambio > 0) {
    $conn = getConnection();

    $producto_id = $item['producto_id'];
    $stmt = $conn->prepare("SELECT e.cantidad as stock FROM productos p INNER JOIN existencias e ON p.id = e.producto_id WHERE p.id = ? AND p.activo = 1");
    $stmt->bind_param('i', $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado o inactivo']);
        $stmt->close();
        closeConnection($conn);
        exit();
    }

    $row = $result->fetch_assoc();
    $stock = (int)$row['stock'];
    $stmt->close();
    closeConnection($conn);

    if ($nuevaCantidad > $stock) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No hay más stock disponible',
            'stock' => $stock
        ]);
        exit();
    }
}

if ($nuevaCantidad <= 0) {
    unset($carrito[$index]);
    $carrito = array_values($carrito);
} else {
    $item['cantidad'] = $nuevaCantidad;
}

$subtotal = 0;
$items_count = 0;

foreach ($carrito as $c) {
    $subtotal += $c['precio_unitario'] * $c['cantidad'];
    $items_count += $c['cantidad'];
}

$iva = round($subtotal * 0.16, 2);
$total = $subtotal + $iva;

echo json_encode([
    'success' => true,
    'carrito' => $carrito,
    'totales' => [
        'items_count' => $items_count,
        'subtotal' => $subtotal,
        'iva' => $iva,
        'total' => $total
    ]
]);
