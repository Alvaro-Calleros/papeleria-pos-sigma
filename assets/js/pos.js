// Papelería Sigma - POS Logic

document.addEventListener('DOMContentLoaded', () => {
    actualizarCarrito();
    actualizarStats();
    
    // Focus en input al cargar
    document.getElementById('barcodeInput').focus();
    
    // Listener para scanner
    document.getElementById('barcodeInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            agregarProducto();
        }
    });

    // Mantener focus en input (opcional, puede ser molesto si se quiere usar otros inputs)
    // document.addEventListener('click', (e) => {
    //     if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'INPUT') {
    //         document.getElementById('barcodeInput').focus();
    //     }
    // });
});

// Agregar producto al carrito
async function agregarProducto() {
    const input = document.getElementById('barcodeInput');
    const codigo = input.value.trim();
    
    if (!codigo) return;
    
    input.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('codigo_barras', codigo);
        
        const response = await fetch('actions/ventas_add.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderCarrito(data);
            input.value = '';
            showAlert('Producto agregado', 'success');
        } else {
            showAlert(data.message, 'danger');
            // Reproducir sonido de error si es posible
        }
        
    } catch (error) {
        console.error(error);
        showAlert('Error de conexión', 'danger');
    } finally {
        input.disabled = false;
        input.focus();
    }
}

// Obtener estado actual del carrito
async function actualizarCarrito() {
    try {
        const response = await fetch('actions/ventas_get.php');
        const data = await response.json();
        
        if (data.success) {
            renderCarrito(data);
        }
    } catch (error) {
        console.error(error);
    }
}

// Renderizar tabla y totales
function renderCarrito(data) {
    const tbody = document.getElementById('carritoBody');
    const carrito = data.carrito || [];
    const totales = data.totales || { items_count: 0, subtotal: 0, iva: 0, total: 0 };
    
    // Actualizar badges y totales
    document.getElementById('itemsCount').textContent = `${totales.items_count} items`;
    document.getElementById('subtotalDisplay').textContent = formatMoney(totales.subtotal);
    document.getElementById('ivaDisplay').textContent = formatMoney(totales.iva);
    document.getElementById('totalDisplay').textContent = formatMoney(totales.total);
    
    // Habilitar/Deshabilitar botón confirmar
    document.getElementById('confirmarBtn').disabled = carrito.length === 0;
    
    // Renderizar tabla
    if (carrito.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-5">
                    <div class="logo-emoji" style="font-size: 3rem; opacity: 0.3;">🌱</div>
                    <p class="mt-2">El carrito está vacío</p>
                    <small>Escanee productos para comenzar</small>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    carrito.forEach((item, index) => {
        html += `
            <tr class="fade-in-up">
                <td>
                    <div class="fw-bold">${item.nombre}</div>
                </td>
                <td><small class="text-muted">${item.codigo_barras}</small></td>
                <td class="text-end">${formatMoney(item.precio_unitario)}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, -1)">-</button>
                        <span class="btn btn-light disabled" style="width: 40px; color: #000;">${item.cantidad}</span>
                        <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="text-end fw-bold">${formatMoney(item.precio_unitario * item.cantidad)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="eliminarItem(${index})" title="Eliminar">
                        🗑️
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Cambiar cantidad (+/-)
async function cambiarCantidad(index, delta) {
    try {
        const formData = new FormData();
        formData.append('index', index);
        formData.append('cambio', delta);
        
        const response = await fetch('actions/ventas_update.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderCarrito(data);
        } else {
            showAlert(data.message, 'warning');
        }
    } catch (error) {
        console.error(error);
    }
}

// Eliminar item
async function eliminarItem(index) {
    try {
        const formData = new FormData();
        formData.append('index', index);
        
        const response = await fetch('actions/ventas_remove.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderCarrito(data);
        }
    } catch (error) {
        console.error(error);
    }
}

// Limpiar carrito
async function limpiarCarrito() {
    if (!confirm('¿Seguro que desea vaciar el carrito?')) return;
    
    try {
        const response = await fetch('actions/ventas_clear.php', { method: 'POST' });
        const data = await response.json();
        
        if (data.success) {
            renderCarrito(data);
            showAlert('Carrito vaciado', 'info');
            document.getElementById('barcodeInput').focus();
        }
    } catch (error) {
        console.error(error);
    }
}

// Confirmar venta
async function confirmarVenta() {
    const btn = document.getElementById('confirmarBtn');
    const spinner = document.getElementById('loadingSpinner');
    
    if (!confirm('¿Procesar venta?')) return;
    
    btn.disabled = true;
    spinner.classList.remove('d-none');
    
    try {
        const response = await fetch('actions/ventas_confirm.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(`Venta ${data.folio} registrada exitosamente`, 'success');
            
            // Limpiar UI
            actualizarCarrito(); // Esto traerá el carrito vacío
            actualizarStats();   // Actualizar contadores del día
            
            // Abrir ticket
            window.open(`ticket.php?venta_id=${data.venta_id}`, 'Ticket', 'width=400,height=600');
            
        } else {
            showAlert(data.message || 'Error al procesar venta', 'danger');
        }
        
    } catch (error) {
        console.error(error);
        showAlert('Error de conexión', 'danger');
    } finally {
        btn.disabled = false;
        spinner.classList.add('d-none');
        document.getElementById('barcodeInput').focus();
    }
}

// Actualizar estadísticas del día
async function actualizarStats() {
    try {
        const response = await fetch('actions/reportes_get.php?action=ventas_dia');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('ventasHoy').textContent = data.data.total_ventas || 0;
            document.getElementById('totalDia').textContent = formatMoney(data.data.ingresos || 0);
        }
    } catch (error) {
        console.error(error);
    }
}

// Helpers
function formatMoney(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

function showAlert(message, type) {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.innerHTML = '';
    container.appendChild(alert);
    
    // Auto cerrar después de 3s
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 150);
    }, 3000);
}

function logout() {
    if (confirm('¿Cerrar sesión?')) {
        fetch('actions/logout.php')
            .then(() => window.location.href = 'login.php');
    }
}
