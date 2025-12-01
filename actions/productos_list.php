<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_user.php'; // Permitir a usuarios ver lista (para ventas)

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$activo = isset($_GET['activo']) ? $_GET['activo'] : '1';
$offset = ($page - 1) * $limit;

$conn = getConnection();

try {
    $where_clause = "WHERE 1=1";
    $params = [];
    $types = "";

    if ($activo === '1') {
        $where_clause .= " AND p.activo = 1";
    } elseif ($activo === '0') {
        $where_clause .= " AND p.activo = 0";
    }

    if (!empty($search)) {
        $where_clause .= " AND (p.nombre LIKE ? OR p.codigo_barras LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }

    // Contar total
    $count_sql = "SELECT COUNT(*) as total FROM productos p $where_clause";
    $stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total_items = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Obtener datos
    $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio_compra, p.precio_venta, p.codigo_barras, p.activo, e.cantidad as stock 
            FROM productos p 
            LEFT JOIN existencias e ON p.id = e.producto_id 
            $where_clause 
            ORDER BY p.nombre ASC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        // No enviamos la imagen BLOB completa en la lista para no hacerla pesada
        // Se podría hacer un endpoint separado para obtener la imagen por ID
        $productos[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'data' => $productos,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $limit)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeConnection($conn);
?>
