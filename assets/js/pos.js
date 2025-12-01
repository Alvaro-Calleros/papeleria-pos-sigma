// Papelería Sigma - POS Logic
let carrito = [];
let ventasHoy = 0;
let totalDia = 0;

// Al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    cargarEstadisticas();
    focusInput();
});

// Input de código de barras
document.getElementById('barcodeInput').addEventListener('keypress', async (e) => {
    if (e.key === 'Enter') {
        const codigo = e.target.value.trim();
        if (codigo) {
            await agregarProducto(codigo);
            e.target.value = '';
        }
    }
});

// Agregar producto al carrito
async function agregarProducto(codigo) {
    try {
        showLoading(true);
        
        const formData = new FormData();
        formData.append('codigo_barras', codigo);
        
        const response = await fetch('actions/ventas_add.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            carrito = data.carrito;
            actualizarCarrito();
            actualizarTotales(data.totales);
            showAlert('Producto agregado', 'success');
            playSound('success');
        } else {
            showAlert(data.message, 'danger');
            playSound('error');
        }
    } catch (error) {
        showAlert('Error de conexión', 'danger');
        console.error(error);
    } finally {
        showLoading(false);
        focusInput();
    }
}

// Actualizar vista del carrito
function actualizarCarrito() {
    const tbody = document.getElementById('carritoBody');
    
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
        document.getElementById('confirmarBtn').disabled = true;
        document.getElementById('itemsCount').textContent = '0 items';
        return;
    }
    
    let html = '';
    let itemsTotal = 0;
    
    carrito.forEach((item, index) => {
        const subtotal = item.precio_unitario * item.cantidad;
        itemsTotal += item.cantidad;
        
        html += `
            <tr class="fade-in-up">
                <td><strong>${item.nombre}</strong></td>
                <td><small class="text-muted">${item.codigo_barras}</small></td>
                <td class="text-end">$${parseFloat(item.precio_unitario).toFixed(2)}</td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="cambiarCantidad(${index}, -1)">-</button>
                        <strong>${item.cantidad}</strong>
                        <button class="btn btn-sm btn-outline-secondary" onclick="cambiarCantidad(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="text-end"><strong>$${subtotal.toFixed(2)}</strong></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger" onclick="eliminarItem(${index})">
                        🗑️
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('confirmarBtn').disabled = false;
    document.getElementById('itemsCount').textContent = `${itemsTotal} items`;
}

// Actualizar totales
function actualizarTotales(totales) {
    document.getElementById('subtotalDisplay').textContent = `$${totales.subtotal.toFixed(2)}`;
    document.getElementById('ivaDisplay').textContent = `$${totales.iva.toFixed(2)}`;
    document.getElementById('totalDisplay').textContent = `$${totales.total.toFixed(2)}`;
}

// Cambiar cantidad (botones +/-)
async function cambiarCantidad(index, cambio) {
    try {
        showLoading(true);

        const formData = new FormData();
        formData.append('index', index);
        formData.append('cambio', cambio);

        const response = await fetch('actions/ventas_update.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            carrito = data.carrito;
            actualizarCarrito();
            actualizarTotales(data.totales);
        } else {
            showAlert(data.message || 'No se pudo actualizar la cantidad', 'danger');
            playSound('error');
        }
    } catch (error) {
        showAlert('Error al actualizar la cantidad', 'danger');
        console.error(error);
    } finally {
        showLoading(false);
        focusInput();
    }
}

// Eliminar item del carrito
async function eliminarItem(index) {
    try {
        showLoading(true);

        const formData = new FormData();
        formData.append('index', index);

        const response = await fetch('actions/ventas_remove.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            carrito = data.carrito;
            actualizarCarrito();
            actualizarTotales(data.totales);
        } else {
            showAlert(data.message || 'No se pudo eliminar el producto', 'danger');
            playSound('error');
        }
    } catch (error) {
        showAlert('Error al eliminar producto', 'danger');
        console.error(error);
    } finally {
        showLoading(false);
        focusInput();
    }
}

// Confirmar venta
async function confirmarVenta() {
    if (carrito.length === 0) {
        showAlert('El carrito está vacío', 'warning');
        return;
    }
    
    if (!confirm('¿Confirmar esta venta?')) {
        return;
    }
    
    try {
        showLoading(true);
        document.getElementById('confirmarBtn').disabled = true;
        
        const response = await fetch('actions/ventas_confirm.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(`Venta confirmada. Folio: ${data.data.folio}`, 'success');
            playSound('success');
            
            // Actualizar estadísticas
            ventasHoy++;
            totalDia += data.data.total;
            document.getElementById('ventasHoy').textContent = ventasHoy;
            document.getElementById('totalDia').textContent = `$${totalDia.toFixed(2)}`;
            
            // Limpiar carrito
            carrito = [];
            actualizarCarrito();
            actualizarTotales({ subtotal: 0, iva: 0, total: 0, items_count: 0 });
            
            // Preguntar si quiere imprimir ticket
            if (confirm('¿Desea imprimir el ticket?')) {
                window.open(`ticket.php?venta_id=${data.data.venta_id}`, '_blank', 'width=400,height=600');
            }
        } else {
            showAlert(data.message, 'danger');
            playSound('error');
        }
    } catch (error) {
        showAlert('Error al procesar la venta', 'danger');
        console.error(error);
    } finally {
        showLoading(false);
        document.getElementById('confirmarBtn').disabled = false;
        focusInput();
    }
}

// Limpiar carrito
async function limpiarCarrito() {
    if (carrito.length === 0) return;
    
    if (!confirm('¿Seguro que desea limpiar el carrito?')) {
        return;
    }

    try {
        showLoading(true);

        const response = await fetch('actions/ventas_clear.php', {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            carrito = [];
            actualizarCarrito();
            actualizarTotales(data.totales);
            showAlert('Carrito limpiado', 'info');
        } else {
            showAlert(data.message || 'No se pudo limpiar el carrito', 'danger');
            playSound('error');
        }
    } catch (error) {
        showAlert('Error al limpiar el carrito', 'danger');
        console.error(error);
    } finally {
        showLoading(false);
        focusInput();
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

// Loading spinner
function showLoading(show) {
    const spinner = document.getElementById('loadingSpinner');
    if (show) {
        spinner.classList.remove('d-none');
    } else {
        spinner.classList.add('d-none');
    }
}

// Cargar estadísticas del día (simulado)
async function cargarEstadisticas() {
    // TODO: Implementar endpoint para obtener estadísticas reales
    // Por ahora solo inicializa en 0
    document.getElementById('ventasHoy').textContent = ventasHoy;
    document.getElementById('totalDia').textContent = `$${totalDia.toFixed(2)}`;
}

// Focus en input
function focusInput() {
    document.getElementById('barcodeInput').focus();
}

// Sonidos (opcional - requiere archivos de audio)
function playSound(type) {
    // Implementar si se desean sonidos
    // const audio = new Audio(`assets/sounds/${type}.mp3`);
    // audio.play();
}

// Logout
function logout() {
    if (confirm('¿Cerrar sesión?')) {
        fetch('actions/logout.php')
            .then(() => window.location.href = 'login.php')
            .catch(() => window.location.href = 'login.php');
    }
}

// Mantener input enfocado siempre
document.addEventListener('click', (e) => {
    if (e.target.id !== 'barcodeInput' && !e.target.closest('button')) {
        focusInput();
    }
});