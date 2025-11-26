<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email y contraseña requeridos']);
    exit();
}

$conn = getConnection();

$stmt = $conn->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
    $stmt->close();
    closeConnection($conn);
    exit();
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
    $stmt->close();
    closeConnection($conn);
    exit();
}

// Login exitoso - regenerar session ID
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['nombre'] = $user['nombre'];
$_SESSION['email'] = $user['email'];
$_SESSION['rol'] = $user['rol'];
$_SESSION['last_activity'] = time();

echo json_encode([
    'success' => true,
    'message' => 'Login exitoso',
    'user' => [
        'nombre' => $user['nombre'],
        'rol' => $user['rol']
    ]
]);

$stmt->close();
closeConnection($conn);
?>