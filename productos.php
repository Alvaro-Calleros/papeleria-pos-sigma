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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
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
            <?php if($_SESSION['rol'] === 'admin'): ?>
                <div class="nav-item active">
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
            <div class="header" style="align-items: center;">
                <div style="display: flex; align-items: center; gap: 24px; order: -1;">
                    <h1 class="page-title" style="margin: 0;">Productos</h1>
                    <button class="btn-primary" onclick="openModalProducto()" style="width: auto; padding: 12px 24px; margin-top: 0; font-size: 14px;">
                        <i class="fas fa-plus"></i>
                        Nuevo
                    </button>
                </div>
                <div class="user-header">
                    <span class="user-name">
                        <i class="fas fa-user-circle"></i>
                        <span id="userName"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                    </span>
                </div>
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
                        <div class="form-group" style="margin: 0;">
                            <label for="searchInput" class="form-label">
                                Producto
                            </label>
                            <input type="text" class="form-control" id="searchInput" 
                                   placeholder="Nombre o código...">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="filterActivo" class="form-label">
                                Estado
                            </label>
                            <select class="form-control" id="filterActivo">
                                <option value="todos">Todos</option>
                                <option value="1" selected>Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>
                        <button class="btn-primary" onclick="buscarProductos()" style="width: auto; padding: 10px 24px; font-size: 14px; margin-top: 0;">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de productos -->
            <div class="card" style="margin-top: 28px;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Listado
                    </h3>
                </div>
                <div class="card-body" style="padding: 0; overflow-x: auto;">
                    <table class="coach-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th style="width: 80px;">Imagen</th>
                                <th>Nombre</th>
                                <th style="width: 140px;">Código</th>
                                <th style="width: 100px; text-align: right;">P. Compra</th>
                                <th style="width: 100px; text-align: right;">P. Venta</th>
                                <th style="width: 80px; text-align: center;">Stock</th>
                                <th style="width: 100px;">Estado</th>
                                <th style="width: 160px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productosBody">
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 60px 20px;">
                                    <div class="spinner-custom"></div>
                                    <p style="color: #8b949e; margin-top: 16px;">Cargando productos...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer" style="display: flex; justify-content: center;">
                    <div id="pagination" style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 1000; max-width: 400px;"></div>

    <!-- Modal Producto -->
    <div class="modal-backdrop" id="modalProducto" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-plus-circle" style="color: #58a6ff;"></i>
                    <span id="modalTitle">Nuevo Producto</span>
                </h3>
                <button onclick="closeModalProducto()" style="all: unset; cursor: pointer; font-size: 24px; color: #8b949e; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="formProducto">
                    <input type="hidden" id="productoId" name="id">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="codigo_barras" class="form-label">Código de Barras *</label>
                            <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="precio_compra" class="form-label">Precio Compra *</label>
                            <input type="number" class="form-control" id="precio_compra" name="precio_compra" 
                                   step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="precio_venta" class="form-label">Precio Venta *</label>
                            <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen" class="form-label">Imagen del Producto</label>
                        <input type="file" class="form-control" id="imagen" name="imagen" 
                               accept="image/jpeg,image/png,image/jpg">
                        <small style="color: #8b949e; font-size: 12px;">Máximo 5MB. Formatos: JPG, PNG</small>
                    </div>
                    
                    <div id="previewContainer" style="display: none; margin-top: 16px;">
                        <img id="imagePreview" src="" alt="Preview" style="max-height: 200px; border-radius: 8px; border: 1px solid #30363d;">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="closeModalProducto()" style="all: unset; cursor: pointer; padding: 10px 24px; background: transparent; color: #c9d1d9; border: 1px solid #30363d; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 140px; height: 44px; flex-shrink: 0; box-shadow: inset 0 0 0 1px #30363d;">
                    Cancelar
                </button>
                <button onclick="guardarProducto()" style="all: unset; cursor: pointer; padding: 10px 24px; background: #58a6ff; color: #0d1117; border-radius: 6px; font-size: 14px; font-weight: 600; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 140px; height: 44px; flex-shrink: 0; box-shadow: inset 0 0 0 1px #58a6ff;">
                    Guardar
                </button>
            </div>
        </div>
        </div>
    </div>

    <!-- Modal Editar Producto -->
    <div class="modal-backdrop" id="modalEditarProducto" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-edit" style="color: #58a6ff;"></i>
                    <span>Editar Producto</span>
                </h3>
                <button onclick="closeModalEditarProducto()" style="all: unset; cursor: pointer; font-size: 24px; color: #8b949e; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarProducto">
                    <input type="hidden" id="productoIdEdit" name="id">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="nombreEdit" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombreEdit" name="nombre" required>
                        </div>

                        <div class="form-group">
                            <label for="codigo_barras_edit" class="form-label">Código de Barras *</label>
                            <input type="text" class="form-control" id="codigo_barras_edit" name="codigo_barras" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcionEdit" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcionEdit" name="descripcion" rows="3"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="precio_compra_edit" class="form-label">Precio Compra *</label>
                            <input type="number" class="form-control" id="precio_compra_edit" name="precio_compra" step="0.01" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="precio_venta_edit" class="form-label">Precio Venta *</label>
                            <input type="number" class="form-control" id="precio_venta_edit" name="precio_venta" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="imagenEdit" class="form-label">Imagen del Producto</label>
                        <input type="file" class="form-control" id="imagenEdit" name="imagen" accept="image/jpeg,image/png,image/jpg">
                        <small style="color: #8b949e; font-size: 12px;">Máximo 5MB. Formatos: JPG, PNG</small>
                    </div>

                    <div id="previewContainerEdit" style="display: none; margin-top: 16px;">
                        <img id="imagePreviewEdit" src="" alt="Preview" style="max-height: 200px; border-radius: 8px; border: 1px solid #30363d;">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="closeModalEditarProducto()" style="all: unset; cursor: pointer; padding: 10px 24px; background: transparent; color: #c9d1d9; border: 1px solid #30363d; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 140px; height: 44px; flex-shrink: 0; box-shadow: inset 0 0 0 1px #30363d;">
                    Cancelar
                </button>
                <button onclick="guardarProductoEdit()" style="all: unset; cursor: pointer; padding: 10px 24px; background: #58a6ff; color: #0d1117; border-radius: 6px; font-size: 14px; font-weight: 600; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 140px; height: 44px; flex-shrink: 0; box-shadow: inset 0 0 0 1px #58a6ff;">
                    Guardar cambios
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación -->
    <div class="modal-backdrop" id="confirmModal" style="display: none;">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle" style="color: #d29922;"></i>
                    <span>Confirmar acción</span>
                </h3>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage" style="margin: 0; color: #c9d1d9; font-size: 15px; line-height: 1.6;"></p>
            </div>
            <div class="modal-footer">
                <button onclick="closeConfirmModal()" style="all: unset; cursor: pointer; padding: 10px 24px; background: transparent; color: #c9d1d9; border: 1px solid #30363d; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 140px; height: 44px; flex-shrink: 0; box-shadow: inset 0 0 0 1px #30363d;">
                    Cancelar
                </button>
                <button onclick="executePendingConfirm()" style="all: unset; cursor: pointer; padding: 10px 24px; background: #f85149; color: #fff; border-radius: 6px; font-size: 14px; font-weight: 600; transition: all 0.2s; display: flex; align-items: center; justify-content: center; width: 140px; height: 44px; flex-shrink: 0; box-shadow: inset 0 0 0 1px #f85149;">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/productos.js"></script>
    <script>
        function logout() {
            window.location.href = 'actions/logout.php';
        }

        function openModalProducto() {
            document.getElementById('modalProducto').style.display = 'flex';
            document.getElementById('formProducto').reset();
            document.getElementById('productoId').value = '';
            document.getElementById('modalTitle').textContent = 'Nuevo Producto';
            document.getElementById('previewContainer').style.display = 'none';
        }

        function closeModalProducto() {
            document.getElementById('modalProducto').style.display = 'none';
        }

        function closeModalEditarProducto() {
            document.getElementById('modalEditarProducto').style.display = 'none';
        }

        function openModalEditarProducto() {
            document.getElementById('modalEditarProducto').style.display = 'flex';
        }
    </script>
</body>
</html>