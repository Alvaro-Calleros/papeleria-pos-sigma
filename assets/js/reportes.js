// Papelería Sigma - Reportes
let datosActuales = [];

// Generar reporte
async function generarReporte() {
    const tipo = document.getElementById('tipoReporte').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    
    if (tipo === 'ventas' && (!fechaInicio || !fechaFin)) {
        showAlert('Por favor seleccione las fechas', 'warning');
        return;
    }
    
    try {
        switch (tipo) {
            case 'ventas':
                await generarReporteVentas(fechaInicio, fechaFin);
                break;
            case 'compras':
                await generarReporteCompras(fechaInicio, fechaFin);
                break;
            case 'devoluciones':
                await generarReporteDevoluciones(fechaInicio, fechaFin);
                break;
            case 'compras':
                await generarReporteCompras(fechaInicio, fechaFin);
                break;
        }
    } catch (error) {
        showAlert('Error al generar reporte', 'danger');
        console.error(error);
    }
}

// Reporte de ventas
// Reporte de compras
// Ver detalles de compra
async function verDetallesCompra(folio) {
    const tbody = document.getElementById('detCompraBody');
    document.getElementById('detCompraFolio').textContent = folio;
    document.getElementById('detCompraProveedor').textContent = '-';
    document.getElementById('detCompraFecha').textContent = '-';
    document.getElementById('detCompraTotal').textContent = '-';
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:16px; color:#8b949e;">Cargando detalle...</td></tr>';
    toggleModal('modalDetalleCompra', true);
    try {
        const resp = await fetch(`actions/reportes_get.php?action=detalle_compra&folio=${folio}`);
        const res = await resp.json();
        if (!res.success) throw new Error(res.message || 'Backend pendiente');
        const { cabecera, detalle } = res.data || {};
        if (cabecera) {
            document.getElementById('detCompraProveedor').textContent = cabecera.proveedor || '-';
            document.getElementById('detCompraFecha').textContent = cabecera.fecha || '-';
            document.getElementById('detCompraTotal').textContent = formatMoney(cabecera.total);
        }
        const items = detalle || [];
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:16px; color:#8b949e;">Sin detalle recibido</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(item => `
            <tr>
                <td>${item.nombre || 'Producto'}</td>
                <td style="text-align:center;">${item.cantidad}</td>
                <td style="text-align:right;">${formatMoney(item.precio_unitario)}</td>
                <td style="text-align:right;">${formatMoney(item.subtotal)}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Error al cargar detalle de compra', err);
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:16px; color:#f85149;">No se pudo cargar el detalle</td></tr>';
    }
}

// Abrir modal de devolución de compra
async function abrirModalDevolucionCompra(folio) {
    document.getElementById('devCompraFolio').value = folio;
    document.getElementById('devCompraFolioDisplay').textContent = folio;
    document.getElementById('devCompraTotalDisplay').textContent = '-';
    const tbody = document.getElementById('devCompraDetalleBody');
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:16px; color:#8b949e;">Cargando detalle...</td></tr>';
    toggleModal('modalDevolucionCompra', true);
    try {
        const resp = await fetch(`actions/reportes_get.php?action=detalle_compra&folio=${folio}`);
        const res = await resp.json();
        if (!res.success) throw new Error(res.message || 'Backend pendiente');
        const { cabecera, detalle } = res.data || {};
        if (cabecera && cabecera.total !== undefined) {
            document.getElementById('devCompraTotalDisplay').textContent = formatMoney(cabecera.total);
        }
        const items = detalle || [];
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:16px; color:#8b949e;">Sin detalle recibido</td></tr>';
            return;
        }
        tbody.innerHTML = items.map((item, idx) => `
            <tr data-product-id="${item.producto_id || idx}">
                <td>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" checked style="cursor: pointer;">
                        <span>${item.nombre || 'Producto'}</span>
                    </label>
                </td>
                <td style="text-align:center;">${item.cantidad}</td>
                <td style="text-align:right;">${formatMoney(item.precio_unitario)}</td>
                <td style="text-align:right;">${formatMoney(item.subtotal)}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Error al cargar detalle de compra', err);
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:16px; color:#f85149;">No se pudo cargar el detalle</td></tr>';
    }
}
async function generarReporteCompras(start, end) {
    const response = await fetch(`actions/reportes_get.php?action=compras_rango&start=${start}&end=${end}`);
    const res = await response.json();
    if (!res.success) {
        throw new Error(res.message);
    }
    const datos = res.data;
    datosActuales = datos;
    document.getElementById('tituloReporte').innerHTML = '<i class="fas fa-shopping-cart"></i><span>Reporte de Compras</span>';
    document.getElementById('resumenStats').style.display = 'grid';
    document.getElementById('statVentas').textContent = datos.length;
    const totalCompras = datos.reduce((sum, c) => sum + parseFloat(c.total), 0);
    document.getElementById('statIngresos').textContent = formatMoney(totalCompras);
    // Ocultar tarjetas de productos y stock en compras
    document.querySelector('[id="statProductos"]').parentElement.style.display = 'none';
    document.querySelector('[id="statStock"]').parentElement.style.display = 'none';
    document.getElementById('headerReporte').innerHTML = `
        <th>Folio</th>
        <th>Fecha</th>
        <th>Proveedor</th>
        <th style="text-align:right;">Total</th>
        <th style="text-align:center;">Acciones</th>
    `;
    let html = '';
    if (datos.length === 0) {
        html = '<tr><td colspan="5" style="text-align:center; padding: 24px; color: #8b949e;">No hay compras en este rango</td></tr>';
    } else {
        datos.forEach(compra => {
            html += `
                <tr>
                    <td><strong>${compra.folio}</strong></td>
                    <td>${compra.fecha}</td>
                    <td>${compra.proveedor || '-'}</td>
                    <td style="text-align:right;"><strong>${formatMoney(compra.total)}</strong></td>
                    <td style="text-align:center;">
                        <div class="action-group">
                            <button class="action-btn" title="Ver detalles" onclick="verDetallesCompra('${compra.folio}')">
                                <i class="fas fa-file-invoice"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} registros encontrados`;
}
async function generarReporteVentas(start, end) {
    const response = await fetch(`actions/reportes_get.php?action=ventas_rango&start=${start}&end=${end}`);
    const res = await response.json();
    
    if (!res.success) {
        throw new Error(res.message);
    }
    
    const datos = res.data;
    const stats = res.stats || { productos_vendidos: 0, stock_total: 0 };
    datosActuales = datos;
    
    document.getElementById('tituloReporte').innerHTML = '<i class="fas fa-chart-line"></i><span>Reporte de Ventas</span>';
    
    document.getElementById('resumenStats').style.display = 'grid';
    document.getElementById('statVentas').textContent = datos.length;
    const totalIngresos = datos.reduce((sum, v) => sum + parseFloat(v.total), 0);
    document.getElementById('statIngresos').textContent = formatMoney(totalIngresos);
    document.getElementById('statProductos').textContent = stats.productos_vendidos;
    document.getElementById('statStock').textContent = stats.stock_total;
    document.getElementById('headerReporte').innerHTML = `
        <th>Folio</th>
        <th>Fecha</th>
        <th>Cajero</th>
        <th style="text-align:right;">Total</th>
        <th style="text-align:center;">Acciones</th>
    `;
    
    let html = '';
    if (datos.length === 0) {
        html = '<tr><td colspan="5" style="text-align:center; padding: 24px; color: #8b949e;">No hay ventas en este rango</td></tr>';
    } else {
        datos.forEach(venta => {
            html += `
                <tr>
                    <td><strong>${venta.folio}</strong></td>
                    <td>${venta.fecha}</td>
                    <td>${venta.cajero}</td>
                    <td style="text-align:right;"><strong>${formatMoney(venta.total)}</strong></td>
                    <td style="text-align:center;">
                        <div class="action-group">
                            <button class="action-btn" title="Ver detalles" onclick="verDetallesVenta(${venta.id}, '${venta.folio}')">
                                <i class="fas fa-receipt"></i>
                            </button>
                            <button class="action-btn" data-variant="danger" title="Procesar devolución" onclick="abrirModalDevolucion(${venta.id}, '${venta.folio}', ${venta.total})">
                                <i class="fas fa-undo-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} registros encontrados`;
}

// Reporte de compras
async function generarReporteCompras(start, end) {
    const response = await fetch(`actions/compras_get.php?action=compras_rango&start=${start}&end=${end}`);
    const res = await response.json();
    
    if (!res.success) {
        throw new Error(res.message || 'Error en backend de compras');
    }
    
    const datos = res.data;
    const stats = res.stats || { productos_comprados: 0 };
    datosActuales = datos;
    
    document.getElementById('tituloReporte').innerHTML = '<i class="fas fa-shopping-cart"></i><span>Reporte de Compras</span>';
    
    document.getElementById('resumenStats').style.display = 'grid';
    document.getElementById('statVentas').textContent = datos.length; // Reusing label container but updated context in header
    
    // Changing labels for Compras context
    const cards = document.querySelectorAll('#resumenStats .card-body span:first-child');
    if(cards[0]) cards[0].textContent = 'Total Compras';
    if(cards[1]) cards[1].textContent = 'Gasto Total';
    if(cards[2]) cards[2].textContent = 'Prod. Comprados';
    if(cards[3]) cards[3].parentElement.parentElement.style.display = 'none'; // Hide stock for compras report

    const totalGastos = datos.reduce((sum, c) => sum + parseFloat(c.total), 0);
    document.getElementById('statIngresos').textContent = formatMoney(totalGastos);
    document.getElementById('statProductos').textContent = stats.productos_comprados;
    
    document.getElementById('headerReporte').innerHTML = `
        <th>Folio</th>
        <th>Proveedor</th>
        <th>Fecha</th>
        <th>Usuario</th>
        <th style="text-align:right;">Total</th>
        <th style="text-align:center;">Acciones</th>
    `;
    
    let html = '';
    if (datos.length === 0) {
        html = '<tr><td colspan="6" style="text-align:center; padding: 24px; color: #8b949e;">No hay compras en este rango</td></tr>';
    } else {
        datos.forEach(compra => {
            html += `
                <tr>
                    <td><strong>${compra.folio}</strong></td>
                    <td>${compra.proveedor}</td>
                    <td>${compra.fecha}</td>
                    <td>${compra.usuario}</td>
                    <td style="text-align:right;"><strong>${formatMoney(compra.total)}</strong></td>
                    <td style="text-align:center;">
                        <div class="action-group">
                            <button class="action-btn" title="Ver detalles" onclick="verDetallesCompra(${compra.id}, '${compra.folio}')">
                                <i class="fas fa-receipt"></i>
                            </button>
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

function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    const palette = {
        success: { icon: 'fa-circle-check', color: '#3fb950' },
        warning: { icon: 'fa-triangle-exclamation', color: '#e3b341' },
        danger: { icon: 'fa-circle-xmark', color: '#f85149' }
    };
    const tone = palette[type] || palette.success;

    // Eliminar cualquier icono HTML del mensaje para evitar doble icono
    const cleanMessage = String(message).replace(/<i[^>]*><\/i>/gi, '').replace(/<i[^>]*>/gi, '').replace(/<\/i>/gi, '');

    alert.className = `alert alert-${type}`;
    alert.style.border = `1px solid ${tone.color}33`;
    alert.innerHTML = `
        <span style="display:flex; align-items:center; gap:10px;">
            <i class="fas ${tone.icon}" style="color:${tone.color};"></i>
            <span>${cleanMessage}</span>
        </span>
    `;

    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    setTimeout(() => alert.remove(), 3200);
}

// Abrir modal de devolución
function abrirModalDevolucion(ventaId, folio, total) {
    document.getElementById('devVentaId').value = ventaId;
    document.getElementById('devFolio').value = folio;
    document.getElementById('devFolioDisplay').textContent = folio;
    document.getElementById('devTotalDisplay').textContent = formatMoney(total);
    document.getElementById('devDetalleBody').innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#8b949e;">Cargando detalle...</td></tr>';
    cargarDetalleVenta(ventaId);
    toggleModal('modalDevolucion', true);
}

// Confirmar devolución
async function confirmarDevolucion() {
    const folio = document.getElementById('devFolio').value;
    const ventaId = document.getElementById('devVentaId').value;
    
    // Recopilar productos seleccionados para devolver
    const productosSeleccionados = [];
    document.querySelectorAll('#devDetalleBody tr').forEach(row => {
        const checkbox = row.querySelector('input[type="checkbox"]');
        if (checkbox && checkbox.checked) {
            const nombreProducto = row.cells[0]?.textContent || '';
            const cantidad = parseInt(row.cells[1]?.textContent || 0);
            const precioUnitario = parseFloat(row.cells[2]?.textContent?.replace('$', '') || 0);
            const productId = row.dataset.productId;
            
            if (productId && cantidad > 0) {
                productosSeleccionados.push({
                    producto_id: parseInt(productId),
                    cantidad: cantidad
                });
            }
        }
    });
    
    if (productosSeleccionados.length === 0) {
        showAlert('Por favor selecciona al menos un producto para devolver', 'warning');
        return;
    }
    
    try {
        const response = await fetch('actions/devoluciones_confirm.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                venta_id: parseInt(ventaId),
                folio: folio,
                productos: productosSeleccionados
            })
        });
        
        const res = await response.json();
        
        if (res.success) {
            showAlert(`Devolución ${res.folio} registrada exitosamente`, 'success');
            toggleModal('modalDevolucion', false);
            // Recargar el reporte actual
            generarReporte();
        } else {
            showAlert(res.message || 'Error al procesar la devolución', 'danger');
        }
    } catch (error) {
        console.error('Error al procesar devolución:', error);
        showAlert('Error al conectar con el servidor', 'danger');
    }
}

// Ver detalles de venta
async function verDetallesVenta(ventaId, folio) {
    const tbody = document.getElementById('detVentaBody');
    document.getElementById('detVentaFolio').textContent = folio;
    document.getElementById('detVentaCajero').textContent = '-';
    document.getElementById('detVentaFecha').textContent = '-';
    document.getElementById('detVentaTotal').textContent = '-';
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#8b949e;">Cargando detalle...</td></tr>';

    toggleModal('modalDetalleVenta', true);

    try {
        const resp = await fetch(`actions/reportes_get.php?action=detalle_venta&venta_id=${ventaId}`);
        const res = await resp.json();
        if (!res.success) throw new Error(res.message || 'Backend pendiente');

        const detalle = res.data || [];
        const cabecera = res.meta || {};

        if (cabecera.cajero) document.getElementById('detVentaCajero').textContent = cabecera.cajero;
        if (cabecera.fecha) document.getElementById('detVentaFecha').textContent = cabecera.fecha;
        if (cabecera.total) document.getElementById('detVentaTotal').textContent = formatMoney(cabecera.total);

        const items = detalle || [];
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#8b949e;">Sin detalle recibido</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(item => `
            <tr>
                <td>${item.nombre || 'Producto'}</td>
                <td style="text-align:center;">${item.cantidad}</td>
                <td style="text-align:right;">${formatMoney(item.precio_unitario)}</td>
                <td style="text-align:right;">${formatMoney(item.subtotal)}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Error al cargar detalle de venta', err);
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#f85149;">No se pudo cargar el detalle</td></tr>';
    }
}

// Ver detalles de Compra
async function verDetallesCompra(compraId, folio) {
    const tbody = document.getElementById('detCompraBody');
    document.getElementById('detCompraFolio').textContent = folio;
    document.getElementById('detCompraProveedor').textContent = '-';
    document.getElementById('detCompraUsuario').textContent = '-';
    document.getElementById('detCompraFecha').textContent = '-';
    document.getElementById('detCompraTotal').textContent = '-';
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#8b949e;">Cargando detalle...</td></tr>';

    toggleModal('modalDetalleCompra', true);

    try {
        const resp = await fetch(`actions/compras_get.php?action=detalle_compra&compra_id=${compraId}`);
        const res = await resp.json();
        if (!res.success) throw new Error(res.message || 'Backend pendiente');

        const detalle = res.data || [];
        const cabecera = res.meta || {};

        if (cabecera.proveedor) document.getElementById('detCompraProveedor').textContent = cabecera.proveedor;
        if (cabecera.usuario) document.getElementById('detCompraUsuario').textContent = cabecera.usuario;
        if (cabecera.fecha) document.getElementById('detCompraFecha').textContent = cabecera.fecha;
        if (cabecera.total) document.getElementById('detCompraTotal').textContent = formatMoney(cabecera.total);

        const items = detalle || [];
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#8b949e;">Sin detalle recibido</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(item => `
            <tr>
                <td>${item.nombre || 'Producto'}</td>
                <td style="text-align:center;">${item.cantidad}</td>
                <td style="text-align:right;">${formatMoney(item.precio_unitario)}</td>
                <td style="text-align:right;">${formatMoney(item.subtotal)}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Error al cargar detalle de compra', err);
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#f85149;">No se pudo cargar el detalle</td></tr>';
    }
}

// Cargar detalle de venta para mostrar en el modal (requiere backend que acepte folio)
async function cargarDetalleVenta(ventaId) {
    const tbody = document.getElementById('devDetalleBody');
    try {
        const resp = await fetch(`actions/reportes_get.php?action=detalle_venta&venta_id=${ventaId}`);
        const res = await resp.json();
        if (!res.success) throw new Error(res.message || 'Sin respuesta');
        const detalles = res.data || [];
        if (!detalles.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:12px; color:#8b949e;">Sin detalle recibido</td></tr>';
            return;
        }
        tbody.innerHTML = detalles.map((item, idx) => `
            <tr data-product-id="${item.producto_id || idx}">
                <td>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" checked style="cursor: pointer;">
                        <span>${item.nombre || 'Producto'}</span>
                    </label>
                </td>
                <td style="text-align:center;">${item.cantidad}</td>
                <td style="text-align:right;">${formatMoney(item.precio_unitario)}</td>
                <td style="text-align:right;">${formatMoney(item.subtotal)}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Error al cargar detalle de venta', err);
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:12px; color:#f85149;">No se pudo cargar el detalle</td></tr>';
    }
}

async function verDetallesDevolucion(id, folio) {
    const tbody = document.getElementById('detDevBody');
    document.getElementById('detDevFolio').textContent = folio;
    document.getElementById('detDevFolioVenta').textContent = '-';
    document.getElementById('detDevCajero').textContent = '-';
    document.getElementById('detDevFecha').textContent = '-';
    document.getElementById('detDevTotal').textContent = '-';
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#8b949e;">Cargando detalle...</td></tr>';
    
    // Configurar botón imprimir
    const btnPrint = document.getElementById('btnImprimirDevolucion');
    if(btnPrint) {
        btnPrint.onclick = () => window.open(`ticket_devolucion.php?id=${id}`, '_blank', 'width=400,height=600');
    }

    toggleModal('modalDetalleDevolucion', true);

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
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#8b949e;">Sin detalle recibido</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(item => `
            <tr>
                <td>${item.nombre || 'Producto'}</td>
                <td style="text-align:center;">${item.cantidad}</td>
                <td style="text-align:right;">${formatMoney(item.precio_unitario)}</td>
                <td style="text-align:right;">${formatMoney(item.subtotal)}</td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Error al cargar detalle de devolución', err);
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:12px; color:#f85149;">No se pudo cargar el detalle</td></tr>';
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
    
    document.getElementById('tituloReporte').innerHTML = '<i class="fas fa-undo"></i><span>Reporte de Devoluciones</span>';
    document.getElementById('resumenStats').style.display = 'grid';
    document.getElementById('statVentas').textContent = datos.length;
    
    const cards = document.querySelectorAll('#resumenStats .card-body span:first-child');
    if(cards[0]) cards[0].textContent = 'Total Registros';
    if(cards[1]) cards[1].textContent = 'Monto Total';
    if(cards[2]) cards[2].textContent = 'Productos';
    if(cards[3]) cards[3].parentElement.parentElement.style.display = 'block';

    const totalDevoluciones = datos.reduce((sum, d) => sum + parseFloat(d.total), 0);
    document.getElementById('statIngresos').textContent = formatMoney(totalDevoluciones);
    // Ocultar tarjetas de productos y stock en devoluciones
    document.querySelector('[id="statProductos"]').parentElement.style.display = 'none';
    document.querySelector('[id="statStock"]').parentElement.style.display = 'none';
        // Mostrar tarjetas de productos y stock solo en ventas
        document.querySelector('[id="statProductos"]').parentElement.style.display = '';
        document.querySelector('[id="statStock"]').parentElement.style.display = '';
    
    document.getElementById('headerReporte').innerHTML = `
        <th>Folio Devolución</th>
        <th>Folio Venta</th>
        <th>Fecha</th>
        <th>Cajero</th>
        <th style="text-align:right;">Total</th>
        <th style="text-align:center;">Acciones</th>
    `;
    
    let html = '';
    if (datos.length === 0) {
        html = '<tr><td colspan="6" style="text-align:center; padding: 24px; color:#8b949e;">No hay devoluciones en este rango</td></tr>';
    } else {
        datos.forEach(dev => {
            html += `
                <tr>
                    <td><strong>${dev.folio}</strong></td>
                    <td>${dev.venta_folio || '-'}</td>
                    <td>${dev.fecha}</td>
                    <td>${dev.cajero || '-'}</td>
                    <td style="text-align:right;"><strong>${formatMoney(dev.total)}</strong></td>
                    <td style="text-align:center;">
                        <div class="action-group">
                            <button class="action-btn" title="Ver detalles" onclick="verDetallesDevolucion(${dev.id}, '${dev.folio}')">
                                <i class="fas fa-receipt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    document.getElementById('bodyReporte').innerHTML = html;
    document.getElementById('infoRegistros').textContent = `${datos.length} registros encontrados`;
}

function toggleModal(id, show = true) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = show ? 'flex' : 'none';
}

function closeModal(id) {
    toggleModal(id, false);
}
