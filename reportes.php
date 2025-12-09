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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }

        /* Acciones en tabla (consistente con productos) */
        .action-group { display: flex; gap: 8px; justify-content: center; align-items: center; }
        .action-btn {
            all: unset;
            cursor: pointer;
            padding: 6px 12px;
            background: transparent;
            color: #58a6ff;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .action-btn:hover { background: #161b22; color: #79c0ff; }
        .action-btn[data-variant="danger"] { color: #f85149; }
        .action-btn[data-variant="danger"]:hover { background: #1f0f11; color: #ff7b72; }
        .action-btn i { pointer-events: none; }

        /* Input date/time icon color */
        input[type="date"]::before,
        input[type="time"]::before {
            color: #58a6ff;
        }

        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(0.8) brightness(1.2);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/images/papeleria-sigma-logo.svg" alt="Papelería Sigma" style="height: 80px; width: auto;">
            </div>
            <div class="nav-item" onclick="window.location.href='index.php'">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="nav-item" onclick="window.location.href='productos.php'">
                <i class="fas fa-box"></i>
                <span>Productos</span>
            </div>
            <div class="nav-item active">
                <i class="fas fa-chart-line"></i>
                <span>Reportes</span>
            </div>

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
                        <span><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                    </span>
                </div>
                <h1 class="page-title">Reportes</h1>
            </div>

            <div class="card" style="margin-bottom: 28px;">
                <div class="card-header">
                    <h3 class="card-title" style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-filter"></i>
                        <span>Filtros</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 16px; align-items: flex-end;">
                        <div class="form-group" style="margin: 0;">
                            <label for="tipoReporte" class="form-label">Tipo de Reporte</label>
                            <select class="coach-input" id="tipoReporte" style="height: 44px; box-sizing: border-box;">
                                <option value="ventas">Ventas</option>
                                <option value="devoluciones">Devoluciones</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="coach-input" id="fechaInicio" value="<?= date('Y-m-d', strtotime('-30 days')) ?>" style="height: 44px; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="fechaFin" class="form-label">Fecha Fin</label>
                            <input type="date" class="coach-input" id="fechaFin" value="<?= date('Y-m-d') ?>" style="height: 44px; box-sizing: border-box;">
                        </div>
                        <button class="btn-primary" onclick="generarReporte()" style="width: auto; padding: 12px 24px; font-size: 14px; align-self: flex-end;">
                            <i class="fas fa-search"></i>
                            Generar
                        </button>
                    </div>
                </div>
            </div>

            <div id="alertContainer" class="alert-container"></div>

            <div class="card" style="margin-bottom: 28px;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 class="card-title" id="tituloReporte" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-chart-bar"></i>
                        <span>Resultados del Reporte</span>
                    </h3>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn-secondary" onclick="exportarCSV()" style="width: auto; padding: 10px 16px;">
                            <i class="fas fa-file-csv"></i>
                            Exportar CSV
                        </button>
                        <button class="btn-secondary" onclick="window.print()" style="width: auto; padding: 10px 16px;">
                            <i class="fas fa-print"></i>
                            Imprimir
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="resumenStats" style="display: none; margin-bottom: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                        <div class="card" style="background: #161b22; border: 1px solid #30363d;">
                            <div class="card-body" style="display: flex; flex-direction: column; gap: 6px;">
                                <span style="color: #8b949e; font-size: 12px;">Total Registros</span>
                                <span id="statVentas" style="font-size: 24px; font-weight: 700;">0</span>
                            </div>
                        </div>
                        <div class="card" style="background: #161b22; border: 1px solid #30363d;">
                            <div class="card-body" style="display: flex; flex-direction: column; gap: 6px;">
                                <span style="color: #8b949e; font-size: 12px;">Monto Total</span>
                                <span id="statIngresos" style="font-size: 24px; font-weight: 700; color: #58a6ff;">$0.00</span>
                            </div>
                        </div>
                        <div class="card" style="background: #161b22; border: 1px solid #30363d;">
                            <div class="card-body" style="display: flex; flex-direction: column; gap: 6px;">
                                <span style="color: #8b949e; font-size: 12px;">Productos</span>
                                <span id="statProductos" style="font-size: 24px; font-weight: 700;">-</span>
                            </div>
                        </div>
                        <div class="card" style="background: #161b22; border: 1px solid #30363d;">
                            <div class="card-body" style="display: flex; flex-direction: column; gap: 6px;">
                                <span style="color: #8b949e; font-size: 12px;">Stock Total</span>
                                <span id="statStock" style="font-size: 24px; font-weight: 700;">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-wrapper" style="overflow-x: auto; border: 1px solid #30363d; border-radius: 10px;">
                        <table class="coach-table" id="tablaReporte">
                            <thead>
                                <tr id="headerReporte"></tr>
                            </thead>
                            <tbody id="bodyReporte">
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 40px 16px; color: #8b949e;">
                                        <i class="fas fa-chart-bar" style="font-size: 32px; opacity: 0.4;"></i>
                                        <p style="margin-top: 12px;">Selecciona filtros y genera el reporte</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
                    <small style="color: #8b949e;" id="infoRegistros">0 registros encontrados</small>
                    <div id="pagination" style="display: flex; gap: 8px; flex-wrap: wrap;"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Devolución -->
    <div class="modal-backdrop" id="modalDevolucion" style="display: none;">
        <div class="modal-content" style="max-width: 640px;">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-undo-alt" style="color: #58a6ff;"></i>
                    <span>Procesar Devolución</span>
                </h3>
                <button onclick="closeModal('modalDevolucion')" style="all: unset; cursor: pointer; font-size: 22px; color: #8b949e; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <input type="hidden" id="devFolio">
                <input type="hidden" id="devVentaId">

                <div class="form-group">
                    <label class="form-label">Folio de Venta</label>
                    <div style="color: #c9d1d9;" id="devFolioDisplay">-</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Total original</label>
                    <div style="color: #c9d1d9;" id="devTotalDisplay">-</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Productos en la venta</label>
                    <div class="table-wrapper" style="overflow-x: auto; border: 1px solid #30363d; border-radius: 8px;">
                        <table class="coach-table" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th style="text-align: center;">Cant.</th>
                                    <th style="text-align: right;">Precio</th>
                                    <th style="text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="devDetalleBody">
                                <tr><td colspan="4" style="text-align: center; padding: 16px; color: #8b949e;">Cargando detalle...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert" style="background: #161b22; border: 1px solid #30363d; color: #c9d1d9; padding: 12px 14px; border-radius: 8px;">
                    <small><strong>Nota:</strong> Esta devolución procesará todos los productos de la venta. El inventario se actualizará automáticamente.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" onclick="closeModal('modalDevolucion')">Cancelar</button>
                <button class="btn btn-danger" type="button" onclick="confirmarDevolucion()">Procesar Devolución</button>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Venta -->
    <div class="modal-backdrop" id="modalDetalleVenta" style="display: none;">
        <div class="modal-content form-modal">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-receipt" style="color: #58a6ff;"></i>
                    <span>Detalle de Venta</span>
                </h3>
                <button onclick="closeModal('modalDetalleVenta')" style="all: unset; cursor: pointer; font-size: 22px; color: #8b949e; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div><strong>Folio:</strong> <span id="detVentaFolio">-</span></div>
                    <div><strong>Cajero:</strong> <span id="detVentaCajero">-</span></div>
                    <div><strong>Fecha:</strong> <span id="detVentaFecha">-</span></div>
                    <div><strong>Total venta:</strong> <span id="detVentaTotal">-</span></div>
                </div>
                <div class="table-wrapper" style="overflow-x: auto; border: 1px solid #30363d; border-radius: 8px;">
                    <table class="coach-table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="text-align: center;">Cant.</th>
                                <th style="text-align: right;">Precio</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detVentaBody">
                            <tr><td colspan="4" style="text-align: center; padding: 16px; color: #8b949e;">Cargando detalle...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" onclick="closeModal('modalDetalleVenta')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Devolución -->
    <div class="modal-backdrop" id="modalDetalleDevolucion" style="display: none;">
        <div class="modal-content form-modal">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-clipboard-list" style="color: #58a6ff;"></i>
                    <span>Detalle de Devolución</span>
                </h3>
                <button onclick="closeModal('modalDetalleDevolucion')" style="all: unset; cursor: pointer; font-size: 22px; color: #8b949e; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div><strong>Folio devolución:</strong> <span id="detDevFolio">-</span></div>
                    <div><strong>Folio venta:</strong> <span id="detDevFolioVenta">-</span></div>
                    <div><strong>Cajero:</strong> <span id="detDevCajero">-</span></div>
                    <div><strong>Fecha:</strong> <span id="detDevFecha">-</span></div>
                    <div><strong>Total devuelto:</strong> <span id="detDevTotal">-</span></div>
                </div>
                <div class="table-wrapper" style="overflow-x: auto; border: 1px solid #30363d; border-radius: 8px;">
                    <table class="coach-table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="text-align: center;">Cant.</th>
                                <th style="text-align: right;">Precio</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detDevBody">
                            <tr><td colspan="4" style="text-align: center; padding: 16px; color: #8b949e;">Cargando detalle...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" onclick="closeModal('modalDetalleDevolucion')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de logout -->
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

    <script src="assets/js/reportes.js?v=<?php echo time(); ?>"></script>
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
</body>
</html>