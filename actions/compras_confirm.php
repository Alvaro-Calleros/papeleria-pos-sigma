<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_user.php'; // Permitir a usuarios registrar compras (o admin, según política)

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

$usuario_id = $_SESSION['user_id'];
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

    // Generar folio C-XXXXX
    $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(folio, 3) AS UNSIGNED)) as ultimo_folio FROM compras");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $nuevo_numero = ($row['ultimo_folio'] ?? 0) + 1;
    $folio = 'C-' . str_pad($nuevo_numero, 5, '0', STR_PAD_LEFT);
    $stmt->close();

    // Insertar compra
    $stmt = $conn->prepare("INSERT INTO compras (folio, usuario_id, total) VALUES (?, ?, ?)");
    $stmt->bind_param('sid', $folio, $usuario_id, $total);
    $stmt->execute();
    $compra_id = $conn->insert_id;
    $stmt->close();

    // Insertar detalles y actualizar stock
    foreach ($productos_validados as $item) {
        // Detalle
        $stmt = $conn->prepare("INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iiidd', $compra_id, $item['producto_id'], $item['cantidad'], $item['precio_unitario'], $item['subtotal']);
        $stmt->execute();
        $stmt->close();

        // Actualizar stock (incrementar)
        // También podríamos actualizar el precio_compra del producto con el nuevo precio, pero eso es política de negocio.
        // Por ahora solo actualizamos stock.
        $stmt = $conn->prepare("UPDATE existencias SET cantidad = cantidad + ? WHERE producto_id = ?");
        $stmt->bind_param('ii', $item['cantidad'], $item['producto_id']);
        $stmt->execute();
        $stmt->close();
        
        // Opcional: Actualizar precio de compra en tabla productos si cambió
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
