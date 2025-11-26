<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode([
        'success' => false,
        'message' => 'No autenticado. Inicia sesión.'
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