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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-custom navbar-light">
        <div class="container-fluid">
            <span class="navbar-brand">
                <span class="logo-emoji">🌱</span>
                Papelería Sigma
            </span>
            <div class="d-flex align-items-center gap-3">
                <?php if($_SESSION['rol'] === 'admin'): ?>
                    <a href="productos.php" class="btn btn-sm btn-outline-dark">Productos</a>
                    <a href="reportes.php" class="btn btn-sm btn-outline-dark">Reportes</a>
                <?php endif; ?>
                <span class="navbar-text">
                    <strong>Cajero:</strong> <?= htmlspecialchars($_SESSION['nombre']) ?>
                </span>
                <button class="btn btn-logout btn-sm" onclick="logout()">
                    Cerrar Sesión
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Panel Izquierdo: Input y Carrito -->
            <div class="col-lg-8">
                <!-- Input de código de barras -->
                <div class="card mb-3 fade-in-up">
                    <div class="card-body">
                        <label for="barcodeInput" class="form-label fw-bold">
                            🔍 Escanear Código de Barras
                        </label>
                        <input 
                            type="text" 
                            class="form-control barcode-input" 
                            id="barcodeInput" 
                            placeholder="Escanee o escriba el código de barras..." 
                            autofocus>
                        <small class="text-muted">Presione Enter después de escanear</small>
                    </div>
                </div>

                <!-- Mensajes -->
                <div id="alertContainer"></div>

                <!-- Tabla de Carrito -->
                <div class="card fade-in-up">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">🛒 Carrito de Venta</h5>
                        <span class="badge badge-custom" id="itemsCount">0 items</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Código</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="carritoBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <div class="logo-emoji" style="font-size: 3rem; opacity: 0.3;">🌱</div>
                                            <p class="mt-2">El carrito está vacío</p>
                                            <small>Escanee productos para comenzar</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Totales -->
            <div class="col-lg-4">
                <div class="card totales-card fade-in-up">
                    <div class="card-body">
                        <h4 class="mb-4">💰 Resumen</h4>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal:</span>
                            <strong id="subtotalDisplay">$0.00</strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">IVA (16%):</span>
                            <strong id="ivaDisplay">$0.00</strong>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="d-flex justify-content-between mb-4">
                            <span class="h4">Total:</span>
                            <strong class="h4 total-display" id="totalDisplay">$0.00</strong>
                        </div>

                        <button 
                            class="btn btn-success-custom w-100 mb-2" 
                            id="confirmarBtn" 
                            onclick="confirmarVenta()"
                            disabled>
                            ✅ Confirmar Venta
                        </button>
                        
                        <button 
                            class="btn btn-danger-custom w-100 btn-sm" 
                            onclick="limpiarCarrito()">
                            🗑️ Limpiar Carrito
                        </button>

                        <div id="loadingSpinner" class="d-none">
                            <div class="spinner-custom"></div>
                            <p class="text-center text-muted">Procesando...</p>
                        </div>
                    </div>
                </div>

                <!-- Mini estadísticas -->
                <div class="card mt-3 fade-in-up">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">📊 Sesión Actual</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <small>Ventas realizadas:</small>
                            <strong id="ventasHoy">0</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small>Total del día:</small>
                            <strong class="text-success" id="totalDia">$0.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/pos.js"></script>
</body>
</html>