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
                <h1 class="page-title">Registrar Compra</h1>
            </div>

            <div class="card" style="max-width: 700px; margin: 0 auto;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-truck-loading"></i>
                        Datos de la compra
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="proveedorNombre" class="form-label">Proveedor (nombre):</label>
                        <input type="text" id="proveedorNombre" class="coach-input" placeholder="Escribe nombre de proveedor o selecciónalo luego" style="width:100%; margin-top:6px;">
                    </div>
                </div>
            </div>

            <div class="card" style="max-width: 700px; margin: 24px auto 0 auto;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-search"></i>
                        Buscar producto
                    </h3>
                </div>
                <div class="card-body">
                    <div style="display:flex; gap:8px; margin-bottom:12px;">
                        <input type="text" id="buscadorProducto" class="coach-input" placeholder="Código o nombre..." style="flex:1;" onkeypress="if(event.key==='Enter') Compras.buscarProductoCompra()">
                        <button class="btn-primary" onclick="Compras.buscarProductoCompra()"><i class="fas fa-search"></i> Buscar</button>
                    </div>
                    <div id="resultadosProductos"></div>
                </div>
            </div>

            <div class="card" style="max-width: 900px; margin: 24px auto 0 auto;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i>
                        Carrito de Compra
                    </h3>
                </div>
                <div class="card-body" style="padding:0;">
                    <div style="overflow-x:auto;">
                        <table class="coach-table" style="width:100%; border-collapse:collapse;">
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
                    <div style="display:flex; justify-content:flex-end; gap:12px; margin:18px 18px 0 0; align-items:center;">
                        <div><strong>Total:</strong> <span id="totalCompra">$0.00</span></div>
                        <button class="btn-primary" onclick="Compras.confirmarCompra()"><i class="fas fa-check"></i> Confirmar Compra</button>
                    </div>
                </div>
            </div>

            <div id="alertContainer" style="max-width:700px; margin:24px auto 0 auto;"></div>
        </main>
    </div>

    <script src="assets/js/compras.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
