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

// Renderizar tabla Dark Pro
function renderProductos(productos) {
    const tbody = document.getElementById('productosBody');
    
    if (productos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" style="text-align: center; padding: 60px 20px; color: #8b949e;">
                    <i class="fas fa-box-open" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                    <p style="margin-top: 16px;">No se encontraron productos</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    productos.forEach(producto => {
        const stockColor = producto.stock < 10 ? '#f85149' : '#c9d1d9';
        const estadoBadge = producto.activo === 1 
            ? '<span style="background: #2ea043; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Activo</span>' 
            : '<span style="background: #484f58; color: #8b949e; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Inactivo</span>';
        
        html += `
            <tr>
                <td>${producto.id}</td>
                <td>
                    ${producto.imagen 
                        ? `<img src="data:image/jpeg;base64,${producto.imagen}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #30363d;">` 
                        : '<div style="width: 50px; height: 50px; background: #161b22; border: 1px solid #30363d; border-radius: 6px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-box" style="color: #484f58;"></i></div>'}
                </td>
                <td><strong>${producto.nombre}</strong></td>
                <td><code style="background: #161b22; padding: 4px 8px; border-radius: 4px; font-size: 13px;">${producto.codigo_barras}</code></td>
                <td style="text-align: right;">$${parseFloat(producto.precio_compra).toFixed(2)}</td>
                <td style="text-align: right;">$${parseFloat(producto.precio_venta).toFixed(2)}</td>
                <td style="text-align: center; color: ${stockColor};"><strong>${producto.stock}</strong></td>
                <td>${estadoBadge}</td>
                <td style="text-align: center;">
                    <button onclick="editarProducto(${producto.id})" title="Editar" style="all: unset; cursor: pointer; padding: 6px 12px; background: transparent; color: #58a6ff; border-radius: 6px; transition: all 0.2s; margin-right: 4px;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="confirmarEliminar(${producto.id})" title="Eliminar" style="all: unset; cursor: pointer; padding: 6px 12px; background: transparent; color: #f85149; border-radius: 6px; transition: all 0.2s;">
                        <i class="fas fa-trash"></i>
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
    paginationEl.innerHTML = '';

    const createPageButton = (pageNum, label, disabled, active) => {
        const btn = document.createElement('button');
        btn.textContent = label;
        btn.style.cssText = `
            all: unset;
            cursor: ${disabled ? 'not-allowed' : 'pointer'};
            padding: 8px 12px;
            background: ${active ? '#58a6ff' : '#161b22'};
            color: ${active ? '#0d1117' : (disabled ? '#484f58' : '#c9d1d9')};
            border: 1px solid ${active ? '#58a6ff' : '#30363d'};
            border-radius: 6px;
            font-size: 14px;
            font-weight: ${active ? '600' : '500'};
            transition: all 0.2s;
            min-width: 36px;
            text-align: center;
            opacity: ${disabled ? '0.5' : '1'};
        `;
        
        if (!disabled && !active) {
            btn.onmouseover = () => {
                btn.style.background = '#21262d';
                btn.style.borderColor = '#58a6ff';
            };
            btn.onmouseout = () => {
                btn.style.background = '#161b22';
                btn.style.borderColor = '#30363d';
            };
            btn.onclick = () => cargarProductos(pageNum);
        }
        
        return btn;
    };

    const prevPage = page - 1;
    paginationEl.appendChild(createPageButton(prevPage, '«', page <= 1, false));

    for (let p = 1; p <= total_pages; p++) {
        paginationEl.appendChild(createPageButton(p, p, false, p === page));
    }

    const nextPage = page + 1;
    paginationEl.appendChild(createPageButton(nextPage, '»', page >= total_pages, false));
}

// Buscar productos
function buscarProductos() {
    cargarProductos(1);
}

// Nuevo producto
function nuevoProducto() {
    document.getElementById('formProducto').reset();
    document.getElementById('productoId').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Producto';
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
            if (modalEl) {
                modalEl.style.display = 'none';
            }
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

        openModalEditarProducto();

    } catch (error) {
        showAlert('Error al cargar producto', 'danger');
        console.error(error);
    }
}

// Confirmar eliminación con modal
function confirmarEliminar(id) {
    showConfirmModal('¿Seguro que desea eliminar este producto?', async () => {
        await eliminarProducto(id);
    });
}

// Eliminar producto
async function eliminarProducto(id) {
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

// Modal de confirmación
let pendingConfirmCallback = null;

function showConfirmModal(message, callback) {
    pendingConfirmCallback = callback;
    document.getElementById('confirmModalMessage').textContent = message;
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeConfirmModal() {
    pendingConfirmCallback = null;
    document.getElementById('confirmModal').style.display = 'none';
}

async function executePendingConfirm() {
    if (pendingConfirmCallback) {
        const callback = pendingConfirmCallback;
        closeConfirmModal();
        await callback();
    }
}

// Mostrar alertas Dark Pro
function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    
    const typeConfig = {
        'success': { icon: '\uf058', color: '#2ea043', bg: '#0d1117' },
        'danger': { icon: '\uf06a', color: '#f85149', bg: '#0d1117' },
        'warning': { icon: '\uf071', color: '#d29922', bg: '#0d1117' },
        'info': { icon: '\uf05a', color: '#58a6ff', bg: '#0d1117' }
    };
    
    const config = typeConfig[type] || typeConfig['info'];
    
    const alert = document.createElement('div');
    alert.className = 'alert';
    alert.style.cssText = `
        background: ${config.bg};
        border: 1px solid #30363d;
        border-left: 4px solid ${config.color};
        color: #c9d1d9;
        padding: 16px 20px;
        border-radius: 6px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideDown 0.3s ease;
    `;
    
    alert.innerHTML = `
        <i class="fas" style="color: ${config.color}; font-size: 18px;"></i>
        <span style="flex: 1;">${message}</span>
    `;
    
    // Insertar ícono usando ::before en el primer i
    const iconElement = alert.querySelector('i');
    iconElement.style.setProperty('font-family', 'Font Awesome 6 Free');
    iconElement.style.setProperty('font-weight', '900');
    iconElement.textContent = String.fromCharCode(parseInt(config.icon.replace('\\u', '0x')));
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}