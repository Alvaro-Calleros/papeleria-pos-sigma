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

unset($_SESSION['carrito']);

echo json_encode([
    'success' => true,
    'carrito' => [],
    'totales' => [
        'items_count' => 0,
        'subtotal' => 0,
        'iva' => 0,
        'total' => 0
    ]
]);
