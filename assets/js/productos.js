// Papelería Sigma - Productos Admin
let currentPage = 1;
let currentSearch = '';

// Cargar productos al inicio
document.addEventListener('DOMContentLoaded', () => {
    cargarProductos();
    
    // Preview de imagen
    document.getElementById('imagen').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('previewContainer').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
});

// Cargar productos
async function cargarProductos(page = 1) {
    try {
        const search = document.getElementById('searchInput').value;
        const activo = document.getElementById('filterActivo').value;
        
        // TODO: Implementar endpoint productos_list.php
        // Por ahora, mostrar datos simulados
        
        const productosSimulados = [
            {
                id: 1,
                nombre: 'Cuaderno profesional 100 hojas',
                codigo_barras: '7501234567890',
                precio_compra: 15.00,
                precio_venta: 25.00,
                stock: 50,
                activo: 1,
                imagen: null
            },
            {
                id: 2,
                nombre: 'Pluma azul BIC',
                codigo_barras: '7501234567891',
                precio_compra: 3.50,
                precio_venta: 7.00,
                stock: 100,
                activo: 1,
                imagen: null
            },
            {
                id: 3,
                nombre: 'Lápiz HB #2',
                codigo_barras: '7501234567892',
                precio_compra: 2.00,
                precio_venta: 4.00,
                stock: 150,
                activo: 1,
                imagen: null
            }
        ];
        
        renderProductos(productosSimulados);
        
    } catch (error) {
        showAlert('Error al cargar productos', 'danger');
        console.error(error);
    }
}

// Renderizar tabla
function renderProductos(productos) {
    const tbody = document.getElementById('productosBody');
    
    if (productos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted py-5">
                    <div class="logo-emoji" style="font-size: 3rem; opacity: 0.3;">📦</div>
                    <p class="mt-2">No se encontraron productos</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    productos.forEach(producto => {
        const stockClass = producto.stock < 10 ? 'text-danger' : '';
        const estadoBadge = producto.activo === 1 
            ? '<span class="badge bg-success">Activo</span>' 
            : '<span class="badge bg-secondary">Inactivo</span>';
        
        html += `
            <tr class="fade-in-up">
                <td>${producto.id}</td>
                <td>
                    ${producto.imagen 
                        ? `<img src="data:image/jpeg;base64,${producto.imagen}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">` 
                        : '<div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">📦</div>'}
                </td>
                <td><strong>${producto.nombre}</strong></td>
                <td><code>${producto.codigo_barras}</code></td>
                <td class="text-end">$${parseFloat(producto.precio_compra).toFixed(2)}</td>
                <td class="text-end">$${parseFloat(producto.precio_venta).toFixed(2)}</td>
                <td class="text-center ${stockClass}"><strong>${producto.stock}</strong></td>
                <td>${estadoBadge}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary-custom me-1" onclick="editarProducto(${producto.id})" title="Editar">
                        ✏️
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${producto.id})" title="Eliminar">
                        🗑️
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Buscar productos
function buscarProductos() {
    cargarProductos(1);
}

// Nuevo producto
function nuevoProducto() {
    document.getElementById('formProducto').reset();
    document.getElementById('productoId').value = '';
    document.getElementById('modalTitle').textContent = '➕ Nuevo Producto';
    document.getElementById('previewContainer').style.display = 'none';
}

// Guardar producto
async function guardarProducto() {
    const form = document.getElementById('formProducto');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const id = document.getElementById('productoId').value;
    
    try {
        // TODO: Implementar endpoint productos_create.php o productos_update.php
        console.log('Guardar producto:', Object.fromEntries(formData));
        
        showAlert(id ? 'Producto actualizado' : 'Producto creado exitosamente', 'success');
        
        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalProducto'));
        modal.hide();
        
        // Recargar lista
        cargarProductos(currentPage);
        
    } catch (error) {
        showAlert('Error al guardar producto', 'danger');
        console.error(error);
    }
}

// Editar producto
async function editarProducto(id) {
    try {
        // TODO: Implementar endpoint para obtener producto por ID
        console.log('Editar producto:', id);
        
        // Datos simulados
        const producto = {
            id: id,
            nombre: 'Cuaderno profesional 100 hojas',
            descripcion: 'Cuaderno rayado',
            codigo_barras: '7501234567890',
            precio_compra: 15.00,
            precio_venta: 25.00
        };
        
        // Llenar form
        document.getElementById('productoId').value = producto.id;
        document.getElementById('nombre').value = producto.nombre;
        document.getElementById('descripcion').value = producto.descripcion || '';
        document.getElementById('codigo_barras').value = producto.codigo_barras;
        document.getElementById('precio_compra').value = producto.precio_compra;
        document.getElementById('precio_venta').value = producto.precio_venta;
        
        document.getElementById('modalTitle').textContent = '✏️ Editar Producto';
        
        // Abrir modal
        const modal = new bootstrap.Modal(document.getElementById('modalProducto'));
        modal.show();
        
    } catch (error) {
        showAlert('Error al cargar producto', 'danger');
        console.error(error);
    }
}

// Eliminar producto
async function eliminarProducto(id) {
    if (!confirm('¿Seguro que desea eliminar este producto?')) {
        return;
    }
    
    try {
        // TODO: Implementar endpoint productos_delete.php
        console.log('Eliminar producto:', id);
        
        showAlert('Producto eliminado', 'success');
        cargarProductos(currentPage);
        
    } catch (error) {
        showAlert('Error al eliminar producto', 'danger');
        console.error(error);
    }
}

// Mostrar alertas
function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success-custom' : 'alert-danger-custom';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} fade-in-up`;
    alert.innerHTML = `
        <strong>${type === 'success' ? '✅' : '⚠️'}</strong> ${message}
    `;
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

// Logout
function logout() {
    if (confirm('¿Cerrar sesión?')) {
        fetch('actions/logout.php')
            .then(() => window.location.href = 'login.php')
            .catch(() => window.location.href = 'login.php');
    }
}

// Al abrir el modal, limpiar form
document.getElementById('modalProducto').addEventListener('show.bs.modal', (e) => {
    if (!e.relatedTarget) {
        nuevoProducto();
    }
});