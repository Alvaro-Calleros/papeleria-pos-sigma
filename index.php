<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/auth_user.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Papelería Sigma</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/images/papeleria-sigma-logo.svg" alt="Papelería Sigma" style="height: 80px; width: auto;">
            </div>
            
            <div class="nav-item active">
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
                <h1 class="page-title">Punto de Venta</h1>
            </div>

            <div class="dashboard-grid">
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-barcode"></i>
                                Escanear Código
                            </h3>
                        </div>
                        <input 
                            type="text" 
                            class="coach-input" 
                            id="barcodeInput" 
                            placeholder="Escanee o escriba el código de barras..." 
                            autofocus>
                        <small style="display: block; margin-top: 12px; color: #6e7681; font-weight: 600;">
                            Presione Enter después de escanear
                        </small>
                    </div>

                    <div class="card" style="margin-top: 28px;">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-shopping-cart"></i>
                                Carrito de Venta
                            </h3>
                            <span class="view-link" id="itemsCount">0 items</span>
                        </div>

                        <table class="coach-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Código</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="carritoBody">
                                <tr style="background: transparent; border: none;">
                                    <td colspan="6" style="padding: 60px 16px; text-align: center; color: #8b949e; border-radius: 0; background: transparent;">
                                        <p style="margin: 0 0 8px 0; color: #c9d1d9; font-weight: 600; font-size: 16px;">El carrito está vacío</p>
                                        <small style="color: #8b949e;">Escanee productos para comenzar</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-receipt"></i>
                                Resumen de Venta
                            </h3>
                        </div>

                        <div class="summary-row">
                            <span class="label">Subtotal:</span>
                            <span id="subtotalDisplay">$0.00</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">IVA (16%):</span>
                            <span id="ivaDisplay">$0.00</span>
                        </div>
                        <div class="summary-row total">
                            <span id="totalDisplay">$0.00</span>
                        </div>

                        <button class="btn-primary" id="confirmarBtn" onclick="confirmarVenta()" disabled>
                            <i class="fas fa-check-circle"></i>
                            Confirmar Venta
                        </button>
                        <button class="btn-secondary" onclick="limpiarCarrito()">
                            <i class="fas fa-trash"></i>
                            Limpiar Carrito
                        </button>

                        <div id="loadingSpinner" class="d-none" style="margin-top: 24px; text-align: center;">
                            <div class="spinner-custom"></div>
                            <p style="color: #8b949e; margin-top: 12px; font-weight: 600;">Procesando...</p>
                        </div>
                    </div>

                    <div class="stats-grid" style="margin-top: 28px;">
                        <div class="stat-mini">
                            <div class="stat-icon">
                                <i class="fas fa-fire"></i>
                            </div>
                            <div class="stat-label">Ventas Hoy</div>
                            <div class="stat-value" id="ventasHoy">0</div>
                        </div>
                        <div class="stat-mini">
                            <div class="stat-icon">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="stat-label">Total</div>
                            <div class="stat-value" id="totalDia">$0.00</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="alertContainer" class="alert-container"></div>

    <!-- Modal de Confirmación Dark Pro -->
    <div id="confirmModal" class="modal-backdrop" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar Venta</h3>
                <button class="modal-close" onclick="closeConfirmModal()" title="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeConfirmModal()">Cancelar</button>
                <button class="btn btn-primary" id="confirmYesBtn" onclick="executePendingConfirm()">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación genérico -->
    <div class="modal-backdrop" id="confirmLogoutModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-sign-out-alt" style="color: #f85149;"></i>
                    <span>Cerrar Sesión</span>
                </h3>
                <button onclick="closeLogoutModal()" style="all: unset; cursor: pointer; font-size: 22px; color: #8b949e; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="margin: 0; color: #c9d1d9;">¿Estás seguro de que deseas cerrar sesión?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" onclick="closeLogoutModal()">Cancelar</button>
                <button class="btn btn-danger" type="button" onclick="confirmLogout()">Cerrar Sesión</button>
            </div>
        </div>
    </div>

    <script src="assets/js/pos.js"></script>
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
    </script>