<?php
// Configuración de conexión a BD
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'papeleria_db');

// Configuración de sesión
define('SESSION_TIMEOUT', 3600); // 1 hora

// Configuración de archivos
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Timezone
date_default_timezone_set('America/Mexico_City');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>