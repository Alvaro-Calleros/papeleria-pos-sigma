<?php
require_once '../includes/config.php';
require_once '../includes/auth_user.php';

header('Content-Type: application/json');

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$carrito = $_SESSION['carrito'];
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
?>
