<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_user.php'; // Permitir a usuarios ver lista (para ventas)

header('Content-Type: application/json; charset=utf-8');

// Capturar cualquier output no deseado
ob_start();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$activo = isset($_GET['activo']) ? $_GET['activo'] : null;
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
    // Si $activo es 'todos' o null, no agregamos filtro y se muestran todos

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
        $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio_compra, p.precio_venta, p.codigo_barras, p.activo, p.imagen, p.imagen_tipo, e.cantidad as stock 
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
        if (!empty($row['imagen'])) {
            $row['imagen'] = base64_encode($row['imagen']);
        }
        $productos[] = $row;
    }
    $stmt->close();

    ob_clean(); // Limpiar cualquier output previo
    echo json_encode([
        'success' => true,
        'data' => $productos,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $limit)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error al buscar productos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Error $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error fatal: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

closeConnection($conn);
ob_end_flush();
?>
