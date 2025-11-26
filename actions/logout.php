<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

// Destruir sesión
session_unset();
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Sesión cerrada exitosamente'
]);
?>