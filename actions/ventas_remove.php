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

if ($index === null || !array_key_exists($index, $_SESSION['carrito'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ítem de carrito no encontrado']);
    exit();
}

$carrito = &$_SESSION['carrito'];

unset($carrito[$index]);
$carrito = array_values($carrito);

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
