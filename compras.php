<?php
require_once 'includes/config.php';

// Validar sesión antes de cargar auth_admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/auth_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compras - Papelería Sigma</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
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
                <div class="nav-item active" onclick="window.location.href='compras.php'">
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

            <div class="dashboard-grid" style="grid-template-columns: 1fr; gap: 28px;">
                
                <!-- Card Proveedor -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-truck"></i>
                            Datos del Proveedor
                        </h3>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre del Proveedor</label>
                        <input type="text" id="proveedorNombre" class="form-control" placeholder="Escribe nombre de proveedor o selecciónalo luego">
                    </div>
                </div>

                <!-- Card Buscador -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-search"></i>
                            Buscar Producto
                        </h3>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Código o nombre del producto</label>
                        <div style="display:flex; gap:12px; align-items: flex-start;">
                            <input type="text" id="buscadorProducto" class="coach-input" placeholder="Código o nombre..." onkeypress="if(event.key==='Enter') Compras.buscarProductoCompra()">
                            <button class="btn-primary" style="margin-top:0; width: auto; padding: 12px 24px;" onclick="Compras.buscarProductoCompra()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    <div id="resultadosProductos" style="margin-top:16px;"></div>
                </div>

                <!-- Card Carrito -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-shopping-cart"></i>
                            Carrito de Compra
                        </h3>
                    </div>
                    
                    <div class="table-wrapper">
                        <table class="coach-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th style="text-align:center;">Cant.</th>
                                    <th style="text-align:right;">Precio Compra</th>
                                    <th style="text-align:right;">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="carritoBody">
                                <tr><td colspan="5" style="text-align:center; padding:32px; color:#8b949e;">Carrito vacío</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="summary-row total" style="justify-content: flex-end; gap: 20px;">
                        <span class="label">Total:</span>
                        <span id="totalCompra" style="color: #58a6ff;">$0.00</span>
                    </div>

                    <div style="display:flex; justify-content:flex-end; margin-top:24px;">
                        <button class="btn-primary" style="width: auto; padding-left: 32px; padding-right: 32px;" onclick="Compras.confirmarCompra()">
                            <i class="fas fa-check-circle"></i> Confirmar Compra
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="alertContainer" class="alert-container"></div>
        </main>
    </div>

    <!-- Modal de confirmación logout -->
    <div class="modal-backdrop" id="confirmLogoutModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-sign-out-alt" style="color: #f85149;"></i>
                    <span>Cerrar Sesión</span>
                </h3>
                <button onclick="closeLogoutModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="color: #c9d1d9;">¿Estás seguro de que deseas cerrar sesión?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeLogoutModal()">Cancelar</button>
                <button class="btn btn-danger" onclick="confirmLogout()">Cerrar Sesión</button>
            </div>
        </div>
    </div>

    <script src="assets/js/compras.js?v=<?php echo time(); ?>"></script>
    <script>
        function logout() {
            document.getElementById('confirmLogoutModal').style.display = 'flex';
        }

        function closeLogoutModal() {
            document.getElementById('confirmLogoutModal').style.display = 'none';
        }

        function confirmLogout() {
            window.location.href = 'actions/logout.php';
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    </script>
</body>
</html>
