<?php
require_once '../includes/config.php';

// Destruir sesión
session_unset();
session_destroy();

// Redirigir a login
header('Location: ../login.php');
exit;
?>