// Papelería Sigma - Reportes
let datosActuales = [];

// Generar reporte
async function generarReporte() {
    const tipo = document.getElementById('tipoReporte').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    
    // Para ventas se requieren fechas
    if (tipo === 'ventas' && (!fechaInicio || !fechaFin)) {
        showAlert('Por favor seleccione las fechas', 'warning');
        return;
    }
    
    try {
        switch (tipo) {
            case 'ventas':
                await generarReporteVentas(fechaInicio, fechaFin);
                break;
            case 'productos':
                await generarReporteMasVendidos();
                break;
            case 'inventario':
                await generarReporteInventario();
                break;
            case 'compras':
                showAlert('Reporte de compras en construcción', 'info');
                break;
        }
        
    } catch (error) {
        showAlert('Error al generar reporte', 'danger');
        console.error(error);
    }
}

// Reporte de ventas
async function generarReporteVentas(start, end) {
    const response = await fetch(`actions/reportes_get.php?action=ventas_rango&start=${start}&end=${end}`);
    const res = await response.json();
    
    if (!res.success) {
        throw new Error(res.message);
    }
    
    const datos = res.data;
    datosActuales = datos;
    
    // Actualizar título
    document.getElementById('tituloReporte').textContent = '📊 Reporte de Ventas';
    
    // Mostrar estadísticas
    document.getElementById('resumenStats').style.display = 'flex';
    document.getElementById('statVentas').textContent = datos.length;
    const totalIngresos = datos.reduce((sum, v) => sum + parseFloat(v.total), 0);
    document.getElementById('statIngresos').textContent = formatMoney(totalIngresos);
    // No tenemos items count en este query, lo omitimos o calculamos si el backend lo mandara
    document.getElementById('statProductos').textContent = '-';
    
    // Headers de tabla
    document.getElementById('headerReporte').innerHTML = `
        <th>Folio</th>
        <th>Fecha</th>
        <th>Cajero</th>
        <th class="text-end">Total</th>
        <th class="text-center">Acciones</th>
    `;
    
    // Datos
    let html = '';
    if (datos.length === 0) {
        html = '<tr><td colspan="5" class="text-center">No hay ventas en este rango</td></tr>';
    } else {
        datos.forEach(venta => {
            html += `
                <tr>
                    <td><strong>${venta.folio}</strong></td>
                    <td>${venta.fecha}</td>
                    <td>${venta.cajero}</td>
                    <td class="text-end"><strong>${formatMoney(venta.total)}</strong></td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary" type="button" data-bs-toggle="dropdown">
                                ⋮
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="abrirModalDevolucion('${venta.folio}', ${venta.total}); return false;">🔄 Devolución</a></li>
                                <li><a class="dropdown-item" href="#" onclick="verDetallesVenta('${venta.folio}'); return false;">📄 Ver Detalles</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} registros encontrados`;
}

// Reporte de productos más vendidos
async function generarReporteMasVendidos() {
    const response = await fetch('actions/reportes_get.php?action=mas_vendidos');
    const res = await response.json();
    
    if (!res.success) throw new Error(res.message);
    
    const datos = res.data;
    datosActuales = datos;
    
    document.getElementById('tituloReporte').textContent = '🏆 Productos Más Vendidos';
    document.getElementById('resumenStats').style.display = 'none';
    
    document.getElementById('headerReporte').innerHTML = `
        <th>Producto</th>
        <th>Código</th>
        <th class="text-center">Cantidad Vendida</th>
        <th class="text-end">Ingresos Generados</th>
    `;
    
    let html = '';
    if (datos.length === 0) {
        html = '<tr><td colspan="4" class="text-center">No hay datos disponibles</td></tr>';
    } else {
        datos.forEach(item => {
            html += `
                <tr>
                    <td>${item.nombre}</td>
                    <td><code>${item.codigo_barras}</code></td>
                    <td class="text-center"><strong>${item.total_vendido}</strong></td>
                    <td class="text-end text-success"><strong>${formatMoney(item.ingresos_generados)}</strong></td>
                </tr>
            `;
        });
    }
    
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} productos`;
}

// Reporte de inventario
async function generarReporteInventario() {
    const response = await fetch('actions/reportes_get.php?action=inventario');
    const res = await response.json();
    
    if (!res.success) throw new Error(res.message);
    
    const datos = res.data;
    datosActuales = datos;
    
    document.getElementById('tituloReporte').textContent = '📦 Inventario Actual';
    document.getElementById('resumenStats').style.display = 'flex';
    document.getElementById('statProductos').textContent = datos.length;
    document.getElementById('statStock').textContent = datos.reduce((sum, p) => sum + parseInt(p.stock), 0);
    // Valor inventario (costo * stock) - no viene en la vista v_productos_stock por defecto, 
    // pero tenemos precio_venta. Usaremos precio_venta como referencia o 0 si no hay costo.
    // La vista tiene precio_venta.
    const valorTotal = datos.reduce((sum, p) => sum + (parseFloat(p.precio_venta) * parseInt(p.stock)), 0);
    document.getElementById('statIngresos').textContent = formatMoney(valorTotal) + ' (Venta)';
    
    document.getElementById('headerReporte').innerHTML = `
        <th>Producto</th>
        <th>Código</th>
        <th class="text-center">Stock</th>
        <th class="text-end">Precio Venta</th>
        <th class="text-center">Estado</th>
    `;
    
    let html = '';
    datos.forEach(item => {
        const stock = parseInt(item.stock);
        const stockClass = stock < 10 ? 'text-danger' : '';
        const alerta = stock < 10 ? '<span class="badge bg-danger">⚠️ Bajo</span>' : '<span class="badge bg-success">✅ OK</span>';
        
        html += `
            <tr>
                <td>${item.nombre}</td>
                <td><code>${item.codigo_barras}</code></td>
                <td class="text-center ${stockClass}"><strong>${stock}</strong></td>
                <td class="text-end">${formatMoney(item.precio_venta)}</td>
                <td class="text-center">${alerta}</td>
            </tr>
        `;
    });
    
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} productos en inventario`;
}

// Exportar a CSV
function exportarCSV() {
    if (datosActuales.length === 0) {
        showAlert('No hay datos para exportar', 'warning');
        return;
    }
    
    const tipo = document.getElementById('tipoReporte').value;
    const fecha = new Date().toISOString().split('T')[0];
    const filename = `reporte_${tipo}_${fecha}.csv`;
    
    let csv = '\ufeff'; 
    const headers = Object.keys(datosActuales[0]);
    csv += headers.join(',') + '\n';
    
    datosActuales.forEach(row => {
        const values = headers.map(header => {
            const val = row[header];
            return typeof val === 'string' && val.includes(',') ? `"${val}"` : val;
        });
        csv += values.join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    
    showAlert(`Archivo ${filename} descargado`, 'success');
}

function formatMoney(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success-custom' : 
                      type === 'warning' ? 'alert-warning' : 'alert-danger-custom';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} fade-in-up`;
    alert.innerHTML = `<strong>${type === 'success' ? '✅' : type === 'warning' ? '⚠️' : '❌'}</strong> ${message}`;
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    setTimeout(() => alert.remove(), 3000);
}

// Abrir modal de devolución
function abrirModalDevolucion(folio, total) {
    document.getElementById('devFolio').value = folio;
    document.getElementById('devFolioDisplay').textContent = folio;
    document.getElementById('devTotalDisplay').textContent = formatMoney(total);
    document.getElementById('devDetalleBody').innerHTML = '<tr><td colspan="4" class="text-center text-muted">Cargando detalle...</td></tr>';
    cargarDetalleVenta(folio);
    
    const modal = new bootstrap.Modal(document.getElementById('modalDevolucion'));
    modal.show();
}

// Confirmar devolución (frontend only - needs backend)
function confirmarDevolucion() {
    const folio = document.getElementById('devFolio').value;
    
    // TODO: Backend call needed
    console.log('Devolución a procesar (se requiere backend):', { folio });
    showAlert('⚠️ Backend pendiente: La devolución no se puede procesar aún', 'warning');
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalDevolucion'));
    modal.hide();
}

// Ver detalles de venta (placeholder)
function verDetallesVenta(folio) {
    showAlert('Ver detalles: funcionalidad pendiente', 'info');
    console.log('Ver detalles de venta:', folio);
}

// Cargar detalle de venta para mostrar en el modal (requiere backend que acepte folio)
async function cargarDetalleVenta(folio) {
    const tbody = document.getElementById('devDetalleBody');
    try {
        const resp = await fetch(`actions/reportes_get.php?action=detalle_venta&folio=${folio}`);
        const res = await resp.json();
        if (!res.success) throw new Error(res.message || 'Sin respuesta');
        const detalles = res.data || [];
        if (!detalles.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin detalle recibido (backend pendiente)</td></tr>';
            return;
        }
        tbody.innerHTML = detalles.map(item => `
            <tr>
                <td>${item.nombre || 'Producto'}</td>
                <td class="text-center">${item.cantidad}</td>
                <td class="text-end">${formatMoney(item.precio_unitario)}</td>
                <td class="text-end">${formatMoney(item.subtotal)}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Error al cargar detalle de venta', err);
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">No se pudo cargar el detalle (backend pendiente)</td></tr>';
    }
}