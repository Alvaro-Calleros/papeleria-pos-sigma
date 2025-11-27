<?php
require_once 'includes/config.php';
require_once 'includes/auth_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Papelería Sigma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand">
                <span class="logo-emoji">🌱</span>
                Papelería Sigma - Productos
            </span>
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="btn btn-sm btn-outline-light">POS</a>
                <a href="reportes.php" class="btn btn-sm btn-outline-light">Reportes</a>
                <span class="navbar-text">Admin: <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <button class="btn btn-logout btn-sm" onclick="logout()">Salir</button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📦 Gestión de Productos</h2>
            <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalProducto">
                ➕ Nuevo Producto
            </button>
        </div>

        <!-- Búsqueda y filtros -->
        <div class="card mb-4 fade-in-up">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="🔍 Buscar por nombre o código de barras...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterActivo">
                            <option value="todos">Todos</option>
                            <option value="1" selected>Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary-custom w-100" onclick="buscarProductos()">
                            Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <div id="alertContainer"></div>

        <!-- Tabla de productos -->
        <div class="card fade-in-up">
            <div class="card-header">
                <h5 class="mb-0">Listado de Productos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Código Barras</th>
                                <th class="text-end">P. Compra</th>
                                <th class="text-end">P. Venta</th>
                                <th class="text-center">Stock</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productosBody">
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="spinner-custom"></div>
                                    <p class="text-muted">Cargando productos...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0" id="pagination">
                        <!-- Se llena dinámicamente -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal Producto -->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--verde-light), var(--verde-secondary)); color: white;">
                    <h5 class="modal-title" id="modalTitle">➕ Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formProducto">
                        <input type="hidden" id="productoId" name="id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="codigo_barras" class="form-label">Código de Barras *</label>
                                <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" required>
                            </div>
                            
                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="precio_compra" class="form-label">Precio Compra *</label>
                                <input type="number" class="form-control" id="precio_compra" name="precio_compra" 
                                       step="0.01" min="0" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="precio_venta" class="form-label">Precio Venta *</label>
                                <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                       step="0.01" min="0" required>
                            </div>
                            
                            <div class="col-12">
                                <label for="imagen" class="form-label">Imagen del Producto</label>
                                <input type="file" class="form-control" id="imagen" name="imagen" 
                                       accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted">Máximo 5MB. Formatos: JPG, PNG</small>
                            </div>
                            
                            <div class="col-12" id="previewContainer" style="display: none;">
                                <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-custom" onclick="guardarProducto()">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/productos.js"></script>
</body>
</html>