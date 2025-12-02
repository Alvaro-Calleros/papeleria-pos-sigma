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
                        <button class="btn btn-sm btn-info" onclick="verDetalleVenta('${venta.folio}')">👁️</button>
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