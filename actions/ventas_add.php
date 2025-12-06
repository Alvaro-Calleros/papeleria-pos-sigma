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

$codigo_barras = $_POST['codigo_barras'] ?? '';

if (empty($codigo_barras)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Código de barras requerido']);
    exit();
}

$conn = getConnection();

// Buscar producto por código de barras
$stmt = $conn->prepare("SELECT p.id, p.nombre, p.precio_venta, p.codigo_barras, e.cantidad as stock 
                        FROM productos p 
                        INNER JOIN existencias e ON p.id = e.producto_id 
                        WHERE p.codigo_barras = ? AND p.activo = 1");
$stmt->bind_param('s', $codigo_barras);
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
$stmt->close();
closeConnection($conn);

// Verificar stock
if ($producto['stock'] <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Producto sin stock disponible']);
    exit();
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Buscar si el producto ya está en el carrito
$encontrado = false;
foreach ($_SESSION['carrito'] as &$item) {
    if ($item['producto_id'] == $producto['id']) {
        // Verificar que no exceda el stock
        if ($item['cantidad'] + 1 > $producto['stock']) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'No hay más stock disponible',
                'stock' => $producto['stock']
            ]);
            exit();
        }
        $item['cantidad']++;
        $encontrado = true;
        break;
    }
}
unset($item); // Romper la referencia para evitar bugs en el siguiente foreach

// Si no está en el carrito, agregarlo
if (!$encontrado) {
    $_SESSION['carrito'][] = [
        'producto_id' => $producto['id'],
        'nombre' => $producto['nombre'],
        'precio_unitario' => $producto['precio_venta'],
        'cantidad' => 1,
        'codigo_barras' => $producto['codigo_barras']
    ];
}

// Calcular totales del carrito
$subtotal = 0;
$items_count = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio_unitario'] * $item['cantidad'];
    $items_count += $item['cantidad'];
}

$iva = round($subtotal * 0.16, 2);
$total = $subtotal + $iva;

echo json_encode([
    'success' => true,
    'message' => 'Producto agregado al carrito',
    'carrito' => $_SESSION['carrito'],
    'totales' => [
        'items_count' => $items_count,
        'subtotal' => $subtotal,
        'iva' => $iva,
        'total' => $total
    ]
]);
?>