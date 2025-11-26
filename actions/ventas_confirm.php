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

// Obtener carrito de sesión
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
    exit();
}

$carrito = $_SESSION['carrito'];
$usuario_id = $_SESSION['user_id'];

$conn = getConnection();
$conn->begin_transaction();

try {
    // Calcular totales
    $subtotal = 0;
    $productos_validados = [];
    
    foreach ($carrito as $item) {
        $producto_id = $item['producto_id'];
        $cantidad = $item['cantidad'];
        
        // Validar stock disponible
        $stmt = $conn->prepare("SELECT p.id, p.precio_venta, e.cantidad as stock 
                                FROM productos p 
                                INNER JOIN existencias e ON p.id = e.producto_id 
                                WHERE p.id = ? AND p.activo = 1 FOR UPDATE");
        $stmt->bind_param('i', $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Producto ID $producto_id no encontrado o inactivo");
        }
        
        $producto = $result->fetch_assoc();
        
        if ($producto['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para producto ID $producto_id. Disponible: {$producto['stock']}, Solicitado: $cantidad");
        }
        
        $item_subtotal = $producto['precio_venta'] * $cantidad;
        $subtotal += $item_subtotal;
        
        $productos_validados[] = [
            'producto_id' => $producto_id,
            'cantidad' => $cantidad,
            'precio_unitario' => $producto['precio_venta'],
            'subtotal' => $item_subtotal
        ];
        
        $stmt->close();
    }
    
    // Calcular IVA y total
    $iva = round($subtotal * 0.16, 2);
    $total = $subtotal + $iva;
    
    // Generar folio único
    $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(folio, 3) AS UNSIGNED)) as ultimo_folio FROM ventas");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $nuevo_numero = ($row['ultimo_folio'] ?? 0) + 1;
    $folio = 'V-' . str_pad($nuevo_numero, 5, '0', STR_PAD_LEFT);
    $stmt->close();
    
    // Insertar venta
    $stmt = $conn->prepare("INSERT INTO ventas (folio, usuario_id, subtotal, iva, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('siddd', $folio, $usuario_id, $subtotal, $iva, $total);
    $stmt->execute();
    $venta_id = $conn->insert_id;
    $stmt->close();
    
    // Insertar detalle y actualizar existencias
    foreach ($productos_validados as $item) {
        // Insertar detalle
        $stmt = $conn->prepare("INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_unitario, subtotal) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iiidd', $venta_id, $item['producto_id'], $item['cantidad'], 
                         $item['precio_unitario'], $item['subtotal']);
        $stmt->execute();
        $stmt->close();
        
        // Actualizar existencias
        $stmt = $conn->prepare("UPDATE existencias SET cantidad = cantidad - ? WHERE producto_id = ?");
        $stmt->bind_param('ii', $item['cantidad'], $item['producto_id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Commit transacción
    $conn->commit();
    
    // Limpiar carrito
    unset($_SESSION['carrito']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Venta registrada exitosamente',
        'data' => [
            'folio' => $folio,
            'venta_id' => $venta_id,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar venta: ' . $e->getMessage()
    ]);
}

closeConnection($conn);
?>