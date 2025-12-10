<?php
require_once 'includes/config.php';
require_once 'includes/auth_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compras - Papelería Sigma</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Hamburger Menu Button -->
    <button class="hamburger-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="main-container">
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <img src="assets/images/papeleria-sigma-logo.svg" alt="Papelería Sigma" style="height: 80px; width: auto;">
            </div>
            <div class="nav-item" onclick="window.location.href='index.php'">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <?php if($_SESSION['rol'] === 'admin'): ?>
                <div class="nav-item" onclick="window.location.href='productos.php'">
                    <i class="fas fa-box"></i>
                    <span>Productos</span>
                </div>
                <div class="nav-item" onclick="window.location.href='reportes.php'">
                    <i class="fas fa-chart-line"></i>
                    <span>Reportes</span>
                </div>
                <div class="nav-item active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Compras</span>
                </div>
            <?php endif; ?>
            <div style="flex: 1;"></div>
            <div class="nav-item logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </div>
        </aside>

        <main class="content">
            <div class="header">
                <div class="user-header">
                    <span class="user-name">
                        <i class="fas fa-user-circle"></i>
                        <span id="userName"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                    </span>
                </div>
                <h1 class="page-title">Compras</h1>
            </div>

            <!-- Búsqueda y filtros -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-search"></i>
                        Buscar
                    </h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 16px; align-items: flex-end;">
                        <div class="form-group" style="margin: 0; flex: 1; min-width: 0;">
                            <label for="proveedorNombre" class="form-label">
                                Proveedor
                            </label>
                            <input type="text" class="coach-input" id="proveedorNombre" placeholder="Nombre de proveedor..." style="width: 100%; height: 45px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin: 0; flex: 1; min-width: 0;">
                            <label for="buscadorProducto" class="form-label">
                                Producto
                            </label>
                            <input type="text" class="coach-input" id="buscadorProducto" placeholder="Nombre o código..." style="width: 100%; height: 45px; box-sizing: border-box;" onkeypress="if(event.key==='Enter') Compras.buscarProductoCompra()">
                        </div>
                        <button class="btn-primary" onclick="Compras.buscarProductoCompra()" style="width: auto; padding: 10px 24px; font-size: 14px; margin-top: 24px; align-self: flex-end;">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                    </div>
                    <div id="resultadosProductos" style="margin-top: 16px;"></div>
                </div>
            </div>

            <!-- Carrito de compra -->
            <div class="card" style="margin-top: 28px;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Carrito de Compra
                    </h3>
                    <div style="display: flex; align-items: center; gap: 18px;">
                        <span style="font-size: 1.1em; font-weight: 700; color: #58a6ff;">Total: <span id="totalCompra">$0.00</span></span>
                        <button class="btn-primary" onclick="Compras.confirmarCompra()" style="width: auto; padding: 10px 20px; margin: 0; font-size: 13px;">
                            <i class="fas fa-check"></i>
                            Confirmar
                        </button>
                    </div>
                </div>
                <div class="card-body" style="padding: 0; overflow-x: auto;">
                    <table class="coach-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="text-align:center;">Cant.</th>
                                <th style="text-align:right;">Precio</th>
                                <th style="text-align:right;">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="carritoBody">
                            <tr><td colspan="5" style="text-align:center; padding:16px; color:#666;">Carrito vacío</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="alertContainer" class="alert-container"></div>
        </main>
    </div>

    <script src="assets/js/compras.js?v=<?php echo time(); ?>"></script>
</script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.classList.toggle('sidebar-open', sidebar.classList.contains('active'));
    }
</script>
</body>
</html>
