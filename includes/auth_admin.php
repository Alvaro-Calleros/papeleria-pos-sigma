<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    die(json_encode([
        'success' => false,
        'message' => 'Acceso denegado. Requiere rol de administrador. Rol actual: ' . ($_SESSION['rol'] ?? 'null') . ', UserID: ' . ($_SESSION['user_id'] ?? 'null')
    ]));
}

// Verificar timeout de sesión
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    http_response_code(401);
    die(json_encode([
        'success' => false,
        'message' => 'Sesión expirada.'
    ]));
}

$_SESSION['last_activity'] = time();
?>