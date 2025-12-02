// Papelería Sigma - Reportes
let datosActuales = [];

// Generar reporte
async function generarReporte() {
    const tipo = document.getElementById('tipoReporte').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    
    if (!fechaInicio || !fechaFin) {
        showAlert('Por favor seleccione las fechas', 'warning');
        return;
    }
    
    try {
        // TODO: Implementar endpoints reales
        // Por ahora, datos simulados según tipo
        
        switch (tipo) {
            case 'ventas':
                generarReporteVentas();
                break;
            case 'productos':
                generarReporteMasVendidos();
                break;
            case 'inventario':
                generarReporteInventario();
                break;
            case 'compras':
                generarReporteCompras();
                break;
        }
        
    } catch (error) {
        showAlert('Error al generar reporte', 'danger');
        console.error(error);
    }
}

// Reporte de ventas
function generarReporteVentas() {
    const datos = [
        { folio: 'V-00001', fecha: '2024-11-26 10:30', cajero: 'Juan Operador', items: 3, total: 100.00 },
        { folio: 'V-00002', fecha: '2024-11-26 11:45', cajero: 'Juan Operador', items: 5, total: 250.50 },
        { folio: 'V-00003', fecha: '2024-11-26 14:20', cajero: 'Admin Principal', items: 2, total: 75.00 }
    ];
    
    datosActuales = datos;
    
    // Actualizar título
    document.getElementById('tituloReporte').textContent = '📊 Reporte de Ventas';
    
    // Mostrar estadísticas
    document.getElementById('resumenStats').style.display = 'flex';
    document.getElementById('statVentas').textContent = datos.length;
    const totalIngresos = datos.reduce((sum, v) => sum + v.total, 0);
    document.getElementById('statIngresos').textContent = `$${totalIngresos.toFixed(2)}`;
    document.getElementById('statProductos').textContent = datos.reduce((sum, v) => sum + v.items, 0);
    
    // Headers de tabla
    document.getElementById('headerReporte').innerHTML = `
        <th>Folio</th>
        <th>Fecha</th>
        <th>Cajero</th>
        <th class="text-center">Items</th>
        <th class="text-end">Total</th>
    `;
    
    // Datos
    let html = '';
    datos.forEach(venta => {
        html += `
            <tr>
                <td><strong>${venta.folio}</strong></td>
                <td>${venta.fecha}</td>
                <td>${venta.cajero}</td>
                <td class="text-center">${venta.items}</td>
                <td class="text-end"><strong>$${venta.total.toFixed(2)}</strong></td>
            </tr>
        `;
    });
    
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} registros encontrados`;
}

// Reporte de productos más vendidos
function generarReporteMasVendidos() {
    const datos = [
        { producto: 'Cuaderno profesional 100 hojas', codigo: '7501234567890', cantidad: 45, ingresos: 1125.00 },
        { producto: 'Pluma azul BIC', codigo: '7501234567891', cantidad: 120, ingresos: 840.00 },
        { producto: 'Lápiz HB #2', codigo: '7501234567892', cantidad: 80, ingresos: 320.00 }
    ];
    
    datosActuales = datos;
    
    document.getElementById('tituloReporte').textContent = '🏆 Productos Más Vendidos';
    document.getElementById('resumenStats').style.display = 'none';
    
    document.getElementById('headerReporte').innerHTML = `
        <th>#</th>
        <th>Producto</th>
        <th>Código</th>
        <th class="text-center">Cantidad Vendida</th>
        <th class="text-end">Ingresos Generados</th>
    `;
    
    let html = '';
    datos.forEach((item, index) => {
        html += `
            <tr>
                <td><strong>${index + 1}</strong></td>
                <td>${item.producto}</td>
                <td><code>${item.codigo}</code></td>
                <td class="text-center"><strong>${item.cantidad}</strong></td>
                <td class="text-end text-success"><strong>$${item.ingresos.toFixed(2)}</strong></td>
            </tr>
        `;
    });
    
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} productos`;
}

// Reporte de inventario
function generarReporteInventario() {
    const datos = [
        { id: 1, producto: 'Cuaderno profesional 100 hojas', codigo: '7501234567890', stock: 50, precio: 25.00, valor: 1250.00 },
        { id: 2, producto: 'Pluma azul BIC', codigo: '7501234567891', stock: 100, precio: 7.00, valor: 700.00 },
        { id: 3, producto: 'Lápiz HB #2', codigo: '7501234567892', stock: 150, precio: 4.00, valor: 600.00 },
        { id: 4, producto: 'Borrador blanco', codigo: '7501234567893', stock: 5, precio: 5.00, valor: 25.00 }
    ];
    
    datosActuales = datos;
    
    document.getElementById('tituloReporte').textContent = '📦 Inventario Actual';
    document.getElementById('resumenStats').style.display = 'flex';
    document.getElementById('statProductos').textContent = datos.length;
    document.getElementById('statStock').textContent = datos.reduce((sum, p) => sum + p.stock, 0);
    const valorTotal = datos.reduce((sum, p) => sum + p.valor, 0);
    document.getElementById('statIngresos').textContent = `$${valorTotal.toFixed(2)}`;
    
    document.getElementById('headerReporte').innerHTML = `
        <th>ID</th>
        <th>Producto</th>
        <th>Código</th>
        <th class="text-center">Stock</th>
        <th class="text-end">Precio</th>
        <th class="text-end">Valor Inventario</th>
        <th class="text-center">Estado</th>
    `;
    
    let html = '';
    datos.forEach(item => {
        const stockClass = item.stock < 10 ? 'text-danger' : '';
        const alerta = item.stock < 10 ? '<span class="badge bg-danger">⚠️ Bajo</span>' : '<span class="badge bg-success">✅ OK</span>';
        
        html += `
            <tr>
                <td>${item.id}</td>
                <td>${item.producto}</td>
                <td><code>${item.codigo}</code></td>
                <td class="text-center ${stockClass}"><strong>${item.stock}</strong></td>
                <td class="text-end">$${item.precio.toFixed(2)}</td>
                <td class="text-end">$${item.valor.toFixed(2)}</td>
                <td class="text-center">${alerta}</td>
            </tr>
        `;
    });
    
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} productos en inventario`;
}

// Reporte de compras
function generarReporteCompras() {
    const datos = [
        { folio: 'C-00001', fecha: '2024-11-15 09:00', proveedor: 'Distribuidora ABC', items: 2, total: 500.00 },
        { folio: 'C-00002', fecha: '2024-11-20 10:30', proveedor: 'Papelería Mayorista', items: 5, total: 1200.00 }
    ];
    
    datosActuales = datos;
    
    document.getElementById('tituloReporte').textContent = '📋 Reporte de Compras';
    document.getElementById('resumenStats').style.display = 'flex';
    document.getElementById('statVentas').textContent = datos.length;
    const totalCompras = datos.reduce((sum, c) => sum + c.total, 0);
    document.getElementById('statIngresos').textContent = `$${totalCompras.toFixed(2)}`;
    
    document.getElementById('headerReporte').innerHTML = `
        <th>Folio</th>
        <th>Fecha</th>
        <th>Proveedor</th>
        <th class="text-center">Items</th>
        <th class="text-end">Total</th>
    `;
    
    let html = '';
    datos.forEach(compra => {
        html += `
            <tr>
                <td><strong>${compra.folio}</strong></td>
                <td>${compra.fecha}</td>
                <td>${compra.proveedor}</td>
                <td class="text-center">${compra.items}</td>
                <td class="text-end"><strong>$${compra.total.toFixed(2)}</strong></td>
            </tr>
        `;
    });
    
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} compras registradas`;
}

// Exportar a CSV (via backend)
async function exportarCSV() {
    const tipo = document.getElementById('tipoReporte').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;

    if (!fechaInicio || !fechaFin) {
        showAlert('Seleccione rango de fechas', 'warning');
        return;
    }

    try {
        showAlert('Generando CSV...', 'success');
        const params = new URLSearchParams({ tipo, fechaInicio, fechaFin });
        const response = await fetch(`actions/export_csv.php?${params.toString()}`, {
            method: 'GET'
        });

        if (!response.ok) {
            showAlert('No se pudo generar el CSV', 'danger');
            return;
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        const fecha = new Date().toISOString().split('T')[0];
        a.download = `reporte_${tipo}_${fecha}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);

        showAlert('CSV descargado', 'success');
    } catch (error) {
        console.error(error);
        showAlert('Error al descargar CSV', 'danger');
    }
}

// Mostrar alertas
function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success-custom' : 
                      type === 'warning' ? 'alert-warning' : 'alert-danger-custom';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} fade-in-up`;
    alert.innerHTML = `
        <strong>${type === 'success' ? '✅' : type === 'warning' ? '⚠️' : '❌'}</strong> ${message}
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