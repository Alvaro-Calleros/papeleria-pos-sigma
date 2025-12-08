// Papelería Sigma - Productos Admin
let currentPage = 1;

// Cargar productos al inicio
document.addEventListener('DOMContentLoaded', () => {
    cargarProductos();

    // Preview de imagen (altas y edición)
    setupImagePreview('imagen', 'imagePreview', 'previewContainer');
    setupImagePreview('imagenEdit', 'imagePreviewEdit', 'previewContainerEdit');
});

function setupImagePreview(inputId, previewId, containerId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const container = document.getElementById(containerId);

    if (!input || !preview || !container) {
        return;
    }

    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                preview.src = event.target.result;
                container.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            container.style.display = 'none';
            preview.src = '';
        }
    });
}

// Cargar productos
async function cargarProductos(page = 1) {
    try {
        currentPage = page;
        const search = document.getElementById('searchInput').value.trim();
        const activo = document.getElementById('filterActivo').value;

        const params = new URLSearchParams();
        params.append('page', page);
        params.append('limit', 10);

        if (search) {
            params.append('search', search);
        }

        if (activo && activo !== 'todos') {
            params.append('activo', activo);
        }

        const tbody = document.getElementById('productosBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-custom"></div>
                    <p class="text-muted">Cargando productos...</p>
                </td>
            </tr>
        `;

        const response = await fetch(`actions/productos_list.php?${params.toString()}`);
        const data = await response.json();

        if (!data.success) {
            showAlert(data.message || 'Error al cargar productos', 'danger');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <p class="mt-2">No se pudieron cargar los productos</p>
                    </td>
                </tr>
            `;
            return;
        }

        renderProductos(data.data || []);
        renderPaginacion(data.pagination);

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

// Renderizar paginación
function renderPaginacion(pagination) {
    const paginationEl = document.getElementById('pagination');

    if (!pagination || pagination.total_pages <= 1) {
        paginationEl.innerHTML = '';
        return;
    }

    const { page, total_pages } = pagination;
    let html = '';

    const createPageItem = (p, label, disabled = false, active = false) => {
        const disabledClass = disabled ? ' disabled' : '';
        const activeClass = active ? ' active' : '';
        const onclick = !disabled ? `onclick="cargarProductos(${p}); return false;"` : 'tabindex="-1" aria-disabled="true"';
        return `
            <li class="page-item${disabledClass}${activeClass}">
                <a class="page-link" href="#" ${onclick}>${label}</a>
            </li>
        `;
    };

    const prevPage = page - 1;
    html += createPageItem(prevPage, '&laquo;', page <= 1, false);

    for (let p = 1; p <= total_pages; p++) {
        html += createPageItem(p, p, false, p === page);
    }

    const nextPage = page + 1;
    html += createPageItem(nextPage, '&raquo;', page >= total_pages, false);

    paginationEl.innerHTML = html;
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

// Guardar nuevo producto (modal de alta)
async function guardarProducto() {
    const form = document.getElementById('formProducto');
    await guardarProductoDesdeFormulario(form, {
        closeModalId: 'modalProducto',
        previewContainerId: 'previewContainer'
    });
}

// Guardar producto editado (modal de edición)
async function guardarProductoEdit() {
    const form = document.getElementById('formEditarProducto');
    const id = document.getElementById('productoIdEdit').value;

    if (!id) {
        showAlert('Selecciona un producto para editar', 'danger');
        return;
    }

    await guardarProductoDesdeFormulario(form, {
        closeModalId: 'modalEditarProducto',
        previewContainerId: 'previewContainerEdit'
    });
}

async function guardarProductoDesdeFormulario(form, options = {}) {
    const { closeModalId = null, previewContainerId = null } = options;

    if (!form || !form.checkValidity()) {
        form?.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const id = form.querySelector('input[name="id"]').value;

    try {
        const url = id ? 'actions/productos_update.php' : 'actions/productos_create.php';
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!data.success) {
            showAlert(data.message || 'Error al guardar producto', 'danger');
            return;
        }

        showAlert(data.message || (id ? 'Producto actualizado' : 'Producto creado exitosamente'), 'success');

        if (closeModalId) {
            const modalEl = document.getElementById(closeModalId);
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal?.hide();
        }

        if (previewContainerId) {
            const previewContainer = document.getElementById(previewContainerId);
            if (previewContainer) {
                previewContainer.style.display = 'none';
            }
        }

        cargarProductos(currentPage);

    } catch (error) {
        showAlert('Error al guardar producto', 'danger');
        console.error(error);
    }
}

// Editar producto -> abre modal específico
async function editarProducto(id) {
    try {
        const response = await fetch(`actions/productos_get.php?id=${id}`);
        const data = await response.json();

        if (!data.success) {
            showAlert(data.message || 'Error al cargar producto', 'danger');
            return;
        }

        const producto = data.data;

        document.getElementById('productoIdEdit').value = producto.id;
        document.getElementById('nombreEdit').value = producto.nombre;
        document.getElementById('descripcionEdit').value = producto.descripcion || '';
        document.getElementById('codigo_barras_edit').value = producto.codigo_barras;
        document.getElementById('precio_compra_edit').value = producto.precio_compra;
        document.getElementById('precio_venta_edit').value = producto.precio_venta;

        const previewContainerEdit = document.getElementById('previewContainerEdit');
        if (previewContainerEdit) {
            previewContainerEdit.style.display = 'none';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
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
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch('actions/productos_delete.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!data.success) {
            showAlert(data.message || 'Error al eliminar producto', 'danger');
            return;
        }

        showAlert(data.message || 'Producto eliminado', 'success');
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
document.getElementById('modalProducto').addEventListener('show.bs.modal', () => {
    nuevoProducto();
});