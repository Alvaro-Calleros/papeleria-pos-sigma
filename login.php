<?php
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Papelería Sigma</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-body">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/images/papeleria-sigma-logo.svg" alt="Papelería Sigma" class="login-logo">
            </div>

            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="coach-input" id="email" name="email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="coach-input" id="password" name="password" required>
                </div>

                <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>

                <button type="submit" class="btn-primary">Iniciar Sesión</button>
                
                <div style="margin-top: 20px; text-align: center; font-size: 15px; color: #555;">
                    Demo: <strong>admin@papeleria.com</strong> / <strong>admin123</strong>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const errorDiv = document.getElementById('errorMessage');
            
            try {
                const response = await fetch('actions/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'flex';
                }
            } catch (error) {
                errorDiv.textContent = 'Error de conexión';
                errorDiv.style.display = 'flex';
            }
        });
    </script>
</body>
</html>