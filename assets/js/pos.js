// Papelería Sigma - POS Logic

// Variable global para controlar requests en progreso
let requestEnProgreso = false;

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
    
    // PROTECCIÓN: Evitar doble submit
    if (requestEnProgreso) {
        console.warn('⚠️ Hay una petición en progreso, esperando...');
        return;
    }
    
    console.log('Agregando producto con código:', codigo);
    requestEnProgreso = true;
    input.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('codigo_barras', codigo);
        
        const response = await fetch('actions/ventas_add.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Respuesta de ventas_add:', data);
        
        if (data.success) {
            // LOG DETALLADO: Ver qué viene del backend
            console.log('=== CARRITO RECIBIDO DEL BACKEND ===');
            console.table(data.carrito); // Muestra tabla bonita en consola
            console.log('Carrito actualizado:', data.carrito);
            
            renderCarrito(data);
            input.value = '';
            showAlert('✓ Producto agregado al carrito', 'success');
        } else {
            showAlert(data.message || 'No se pudo agregar el producto', 'danger');
        }
        
    } catch (error) {
        console.error('Error en agregarProducto:', error);
        showAlert('Error de conexión con el servidor', 'danger');
    } finally {
        // Esperar 300ms antes de permitir otra petición
        setTimeout(() => {
            requestEnProgreso = false;
        }, 300);
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
    
    // VALIDACIÓN: Verificar que data tenga la estructura correcta
    if (!data || typeof data !== 'object') {
        console.error('Error: data inválida en renderCarrito', data);
        return;
    }
    
    const carrito = Array.isArray(data.carrito) ? data.carrito : [];
    const totales = data.totales || { items_count: 0, subtotal: 0, iva: 0, total: 0 };
    
    // Log para debugging (quitar en producción)
    console.log('Renderizando carrito:', { 
        productos: carrito.length, 
        items_totales: totales.items_count,
        total: totales.total 
    });
    
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
            <tr style="background: transparent; border: none;">
                <td colspan="6" style="padding: 60px 16px; text-align: center; color: #8b949e; border-radius: 0; background: transparent;">
                    <p style="margin: 0 0 8px 0; color: #c9d1d9; font-weight: 600; font-size: 16px;">El carrito está vacío</p>
                    <small style="color: #8b949e;">Escanee productos para comenzar</small>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    carrito.forEach((item, index) => {
        // VALIDACIÓN: Verificar que cada item tenga los campos necesarios
        if (!item.producto_id || !item.nombre || !item.precio_unitario || !item.cantidad) {
            console.error('❌ Item inválido en carrito:', item);
            return; // Skip este item
        }
        
        // Log detallado de cada producto
        console.log(`  [${index}] ${item.nombre} x${item.cantidad} = $${(item.precio_unitario * item.cantidad).toFixed(2)}`);
        
        // Calcular subtotal del item
        const subtotalItem = parseFloat(item.precio_unitario) * parseInt(item.cantidad);
        
        html += `
            <tr class="fade-in-up">
                <td>
                    <div class="fw-bold">${escapeHtml(item.nombre)}</div>
                </td>
                <td><small class="text-muted">${escapeHtml(item.codigo_barras)}</small></td>
                <td class="text-end">${formatMoney(item.precio_unitario)}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, -1)">-</button>
                        <span class="btn btn-light disabled" style="width: 50px; color: #000; font-weight: bold;">${item.cantidad}</span>
                        <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="text-end fw-bold">${formatMoney(subtotalItem)}</td>
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
    console.log('Cambiando cantidad:', { index, delta });
    
    try {
        const formData = new FormData();
        formData.append('index', index);
        formData.append('cambio', delta);
        
        const response = await fetch('actions/ventas_update.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Respuesta de ventas_update:', data);
        
        if (data.success) {
            renderCarrito(data);
            // Mostrar feedback sutil sin molestar
            if (delta > 0) {
                console.log('✓ Cantidad incrementada');
            } else {
                console.log('✓ Cantidad decrementada');
            }
        } else {
            showAlert(data.message || 'No se pudo actualizar la cantidad', 'warning');
        }
    } catch (error) {
        console.error('Error en cambiarCantidad:', error);
        showAlert('Error de conexión al actualizar cantidad', 'danger');
    }
}

// Eliminar item
async function eliminarItem(index) {
    console.log('Eliminando item con index:', index);
    
    try {
        const formData = new FormData();
        formData.append('index', index);
        
        const response = await fetch('actions/ventas_remove.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Respuesta de ventas_remove:', data);
        
        if (data.success) {
            renderCarrito(data);
            showAlert('Producto eliminado del carrito', 'info');
        } else {
            showAlert(data.message || 'No se pudo eliminar el producto', 'danger');
        }
    } catch (error) {
        console.error('Error en eliminarItem:', error);
        showAlert('Error de conexión al eliminar producto', 'danger');
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
    const num = parseFloat(amount);
    // Validar que sea un número válido
    if (isNaN(num)) {
        console.warn('formatMoney recibió valor inválido:', amount);
        return '$0.00';
    }
    return '$' + num.toFixed(2);
}

// Escapar HTML para prevenir XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showAlert(message, type) {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <span>${message}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.innerHTML = '';
    container.appendChild(alert);
    
    // Auto cerrar después de 3s
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

function logout() {
    if (confirm('¿Cerrar sesión?')) {
        fetch('actions/logout.php')
            .then(() => window.location.href = 'login.php');
    }
}
