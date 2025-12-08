<?php
require_once 'includes/config.php';
require_once 'includes/auth_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Papelería Sigma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
        
        @page {
            size: A4;
            margin: 20mm;
        }
        
        .print-header {
            display: none;
        }
        
        @media print {
            .print-header {
                display: block;
                text-align: center;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-custom navbar-light no-print">
        <div class="container-fluid">
            <span class="navbar-brand">
                <span class="logo-emoji">🌱</span>
                Papelería Sigma - Reportes
            </span>
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="btn btn-sm btn-outline-dark">POS</a>
                <a href="productos.php" class="btn btn-sm btn-outline-dark">Productos</a>
                <span class="navbar-text">Admin: <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <button class="btn btn-logout btn-sm" onclick="logout()">
                    Cerrar Sesión
                </button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header impreso -->
        <div class="print-header">
            <h2>🌱 Papelería Sigma</h2>
            <p>Reporte generado el <?= date('d/m/Y H:i') ?></p>
            <hr>
        </div>

        <!-- Filtros -->
        <div class="card mb-4 fade-in-up no-print">
            <div class="card-header">
                <h5 class="mb-0">📊 Filtros de Reporte</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="tipoReporte" class="form-label">Tipo de Reporte</label>
                        <select class="form-select" id="tipoReporte">
                            <option value="ventas">Ventas</option>
                            <option value="productos">Productos más vendidos</option>
                            <option value="inventario">Inventario</option>
                            <option value="compras">Compras</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fechaInicio" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="fechaFin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fechaFin" value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary-custom w-100" onclick="generarReporte()">
                            🔍 Generar Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <div id="alertContainer" class="no-print"></div>

        <!-- Resultados -->
        <div class="card fade-in-up">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="tituloReporte">Resultados del Reporte</h5>
                <div class="no-print">
                    <button class="btn btn-success btn-sm" onclick="exportarCSV()">
                        📥 Exportar CSV
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="window.print()">
                        🖨️ Imprimir
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Resumen rápido -->
                <div class="row mb-4" id="resumenStats" style="display: none;">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-1">Total Ventas</h6>
                                <h3 class="mb-0" id="statVentas">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-1">Ingresos</h6>
                                <h3 class="mb-0 text-success" id="statIngresos">$0.00</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-1">Productos</h6>
                                <h3 class="mb-0" id="statProductos">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-1">Stock Total</h6>
                                <h3 class="mb-0" id="statStock">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de resultados -->
                <div class="table-responsive">
                    <table class="table table-custom" id="tablaReporte">
                        <thead>
                            <tr id="headerReporte">
                                <!-- Se llena dinámicamente -->
                            </tr>
                        </thead>
                        <tbody id="bodyReporte">
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <div class="logo-emoji" style="font-size: 3rem; opacity: 0.3;">📊</div>
                                    <p class="mt-2">Seleccione los filtros y haga clic en "Generar Reporte"</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer no-print">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="infoRegistros">0 registros encontrados</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="pagination">
                            <!-- Paginación dinámica -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function logout() {
            if (confirm('¿Cerrar sesión?')) {
                fetch('actions/logout.php')
                    .then(() => window.location.href = 'login.php');
            }
        }
    </script>
    <script src="assets/js/reportes.js"></script>
</body>
</html>