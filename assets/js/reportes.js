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
            case 'devoluciones':
                await generarReporteDevoluciones(fechaInicio, fechaFin);
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

async function verDetallesDevolucion(folio) {
    const tbody = document.getElementById('detDevBody');
    document.getElementById('detDevFolio').textContent = folio;
    document.getElementById('detDevFolioVenta').textContent = '-';
    document.getElementById('detDevCajero').textContent = '-';
    document.getElementById('detDevFecha').textContent = '-';
    document.getElementById('detDevTotal').textContent = '-';
    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Cargando detalle...</td></tr>';

    const modal = new bootstrap.Modal(document.getElementById('modalDetalleDevolucion'));
    modal.show();

    try {
        const resp = await fetch(`actions/reportes_get.php?action=detalle_devolucion&folio=${folio}`);
        const res = await resp.json();
        if (!res.success) throw new Error(res.message || 'Backend devoluciones pendiente');

        const { cabecera, detalle } = res.data || {};
        if (cabecera) {
            document.getElementById('detDevFolioVenta').textContent = cabecera.venta_folio || '-';
            document.getElementById('detDevCajero').textContent = cabecera.cajero || '-';
            document.getElementById('detDevFecha').textContent = cabecera.fecha || '-';
            if (cabecera.total !== undefined) {
                document.getElementById('detDevTotal').textContent = formatMoney(cabecera.total);
            }
        }

        const items = detalle || [];
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin detalle recibido (backend pendiente)</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(item => `
            <tr>
                <td>${item.nombre || 'Producto'}</td>
                <td class="text-center">${item.cantidad}</td>
                <td class="text-end">${formatMoney(item.precio_unitario)}</td>
                <td class="text-end">${formatMoney(item.subtotal)}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Error al cargar detalle de devolución', err);
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">No se pudo cargar el detalle (backend pendiente)</td></tr>';
    }
}

// Reporte de devoluciones
async function generarReporteDevoluciones(start, end) {
    const response = await fetch(`actions/reportes_get.php?action=devoluciones_rango&start=${start}&end=${end}`);
    const res = await response.json();
    
    if (!res.success) {
        throw new Error(res.message || 'Backend devoluciones pendiente');
    }
    
    const datos = res.data;
    datosActuales = datos;
    
    document.getElementById('tituloReporte').textContent = '🔄 Reporte de Devoluciones';
    document.getElementById('resumenStats').style.display = 'flex';
    document.getElementById('statVentas').textContent = datos.length;
    const totalDevoluciones = datos.reduce((sum, d) => sum + parseFloat(d.total), 0);
    document.getElementById('statIngresos').textContent = formatMoney(totalDevoluciones);
    document.getElementById('statProductos').textContent = '-';
    document.getElementById('statStock').textContent = '-';
    
    document.getElementById('headerReporte').innerHTML = `
        <th>Folio Devolución</th>
        <th>Folio Venta</th>
        <th>Fecha</th>
        <th>Cajero</th>
        <th class="text-end">Total</th>
        <th class="text-center">Acciones</th>
    `;
    
    let html = '';
    if (datos.length === 0) {
        html = '<tr><td colspan="6" class="text-center">No hay devoluciones en este rango</td></tr>';
    } else {
        datos.forEach(dev => {
            html += `
                <tr>
                    <td><strong>${dev.folio}</strong></td>
                    <td>${dev.venta_folio || '-'}</td>
                    <td>${dev.fecha}</td>
                    <td>${dev.cajero || '-'}</td>
                    <td class="text-end"><strong>${formatMoney(dev.total)}</strong></td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary" type="button" data-bs-toggle="dropdown">⋮</button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="verDetallesDevolucion('${dev.folio}'); return false;">📄 Ver Detalles</a></li>
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