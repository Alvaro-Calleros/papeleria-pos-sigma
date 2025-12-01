<?php
// Ejecutar este archivo SOLO UNA VEZ para generar los passwords encriptados
// Luego copiar los hash y actualizar seed.sql

$passwords = [
    'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
    'operador123' => password_hash('operador123', PASSWORD_DEFAULT)
];

echo "<pre>";
echo "Passwords generados:\n\n";
echo "admin123: {$passwords['admin123']}\n\n";
echo "operador123: {$passwords['operador123']}\n\n";
echo "Copia estos hash y actualiza el seed.sql en los INSERT de usuarios";
echo "</pre>";
?>