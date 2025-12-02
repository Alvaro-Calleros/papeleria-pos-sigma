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

$input = json_decode(file_get_contents('php://input'), true);
$venta_id = $input['venta_id'] ?? null;
$productos_devolver = $input['productos'] ?? []; // [{producto_id, cantidad}]

if (!$venta_id || empty($productos_devolver)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$usuario_id = $_SESSION['user_id'];
$conn = getConnection();
$conn->begin_transaction();

try {
    // Verificar venta original
    $stmt = $conn->prepare("SELECT id FROM ventas WHERE id = ?");
    $stmt->bind_param('i', $venta_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception("Venta no encontrada");
    }
    $stmt->close();

    $total_devolucion = 0;
    $items_procesados = [];

    foreach ($productos_devolver as $item) {
        $producto_id = $item['producto_id'];
        $cantidad_dev = $item['cantidad'];

        if ($cantidad_dev <= 0) continue;

        // Verificar cantidad vendida originalmente
        $stmt = $conn->prepare("SELECT cantidad, precio_unitario FROM ventas_detalle WHERE venta_id = ? AND producto_id = ?");
        $stmt->bind_param('ii', $venta_id, $producto_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 0) {
            throw new Exception("El producto ID $producto_id no pertenece a esta venta");
        }
        
        $detalle_venta = $res->fetch_assoc();
        $cantidad_vendida = $detalle_venta['cantidad'];
        $precio_unitario = $detalle_venta['precio_unitario'];
        $stmt->close();

        // Verificar devoluciones previas de este producto en esta venta
        // (Para no devolver más de lo vendido si se hacen devoluciones parciales múltiples)
        // Esto requeriría una consulta más compleja sumando devoluciones anteriores.
        // Por simplicidad y tiempo, asumiremos validación básica contra lo vendido en ESTA transacción,
        // pero idealmente se debe sumar lo ya devuelto.
        
        // Sumar devoluciones previas
        $stmt = $conn->prepare("
            SELECT SUM(dd.cantidad) as total_devuelto 
            FROM devoluciones_detalle dd 
            INNER JOIN devoluciones d ON dd.devolucion_id = d.id 
            WHERE d.venta_id = ? AND dd.producto_id = ?
        ");
        $stmt->bind_param('ii', $venta_id, $producto_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $ya_devuelto = $row['total_devuelto'] ?? 0;
        $stmt->close();

        if (($ya_devuelto + $cantidad_dev) > $cantidad_vendida) {
            throw new Exception("No se puede devolver esa cantidad. Vendido: $cantidad_vendida, Ya devuelto: $ya_devuelto");
        }

        $subtotal = $cantidad_dev * $precio_unitario;
        $total_devolucion += $subtotal;

        $items_procesados[] = [
            'producto_id' => $producto_id,
            'cantidad' => $cantidad_dev,
            'precio_unitario' => $precio_unitario,
            'subtotal' => $subtotal
        ];
    }

    if (empty($items_procesados)) {
        throw new Exception("No hay productos válidos para devolver");
    }

    // Generar folio D-XXXXX
    $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(folio, 3) AS UNSIGNED)) as ultimo_folio FROM devoluciones");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $nuevo_numero = ($row['ultimo_folio'] ?? 0) + 1;
    $folio = 'D-' . str_pad($nuevo_numero, 5, '0', STR_PAD_LEFT);
    $stmt->close();

    // Insertar devolución
    $stmt = $conn->prepare("INSERT INTO devoluciones (venta_id, folio, usuario_id, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isid', $venta_id, $folio, $usuario_id, $total_devolucion);
    $stmt->execute();
    $devolucion_id = $conn->insert_id;
    $stmt->close();

    // Insertar detalles y regresar stock
    foreach ($items_procesados as $item) {
        $stmt = $conn->prepare("INSERT INTO devoluciones_detalle (devolucion_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iiidd', $devolucion_id, $item['producto_id'], $item['cantidad'], $item['precio_unitario'], $item['subtotal']);
        $stmt->execute();
        $stmt->close();

        // Incrementar stock - MANEJADO POR TRIGGER
        // $stmt = $conn->prepare("UPDATE existencias SET cantidad = cantidad + ? WHERE producto_id = ?");
        // $stmt->bind_param('ii', $item['cantidad'], $item['producto_id']);
        // $stmt->execute();
        // $stmt->close();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Devolución registrada exitosamente',
        'folio' => $folio
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
?>
