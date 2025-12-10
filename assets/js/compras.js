// Compras minimal JS
window.Compras = (function(){
    const carrito = [];

    function buscarProductoCompra(){
        const q = document.getElementById('buscadorProducto').value.trim();
        const resultados = document.getElementById('resultadosProductos');
        resultados.innerHTML = 'Buscando...';
        if (!q) { resultados.innerHTML = '<div style="color:#666;">Ingrese término de búsqueda</div>'; return; }

        const url = `actions/productos_list.php?search=${encodeURIComponent(q)}&activo=1`;
        console.log('Buscando productos en:', url);
        
        fetch(url)
            .then(async r => {
                console.log('Respuesta recibida, status:', r.status, r.statusText);
                const text = await r.text();
                console.log('Texto de respuesta (primeros 500 chars):', text.substring(0, 500));
                
                let res;
                try {
                    res = JSON.parse(text);
                    console.log('JSON parseado correctamente:', res);
                } catch (e) {
                    console.error('Error parseando JSON:', e);
                    console.error('Texto completo:', text);
                    resultados.innerHTML = '<div style="color:#f00;">Error: Respuesta inválida del servidor. Ver consola del navegador (F12) para más detalles.</div>';
                    return;
                }
                
                if (!res || !res.success) {
                    const errorMsg = res?.message || 'Error desconocido';
                    console.error('Error en respuesta:', res);
                    resultados.innerHTML = `<div style="color:#f00;">Error: ${escapeHtml(errorMsg)}</div>`;
                    return;
                }
                
                const productos = res.data || [];
                console.log('Productos encontrados:', productos.length);
                if (productos.length === 0) {
                    resultados.innerHTML = '<div style="color:#666;">No se encontraron productos</div>';
                    return;
                }
                
                resultados.innerHTML = productos.map(p => `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:8px; border-bottom:1px solid #30363d;">
                        <div><strong>${escapeHtml(p.nombre)}</strong><br><small>${escapeHtml(p.codigo_barras || '')}</small></div>
                        <div style="text-align:right;">
                            <div style="margin-bottom:6px;">${formatMoney(p.precio_compra || p.precio_venta)}</div>
                            <button class="btn-primary" style="width:auto; padding:8px 18px; font-size:14px; gap:8px;" onclick='Compras.agregarProductoCompra(${JSON.stringify({id: p.id, nombre: p.nombre, precio: p.precio_compra || p.precio_venta})})'>
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                `).join('');
            })
            .catch(err=>{
                console.error('Error de fetch:', err);
                resultados.innerHTML = '<div style="color:#f00;">Error de conexión: ' + escapeHtml(err.message) + '</div>';
            });
    }

    function agregarProductoCompra(product){
        // product = { id, nombre, precio }
        const existing = carrito.find(i => i.producto_id === product.id);
        if (existing) {
            existing.cantidad += 1;
        } else {
            carrito.push({ producto_id: product.id, nombre: product.nombre, cantidad: 1, precio_unitario: parseFloat(product.precio) || 0 });
        }
        renderCarrito();
    }

    function renderCarrito(){
        const tbody = document.getElementById('carritoBody');
        if (!carrito.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:16px; color:#666;">Carrito vacío</td></tr>';
            document.getElementById('totalCompra').textContent = formatMoney(0);
            return;
        }
        let html = '';
        let total = 0;
        carrito.forEach((item, idx) => {
            const subtotal = item.cantidad * item.precio_unitario;
            total += subtotal;
            html += `
                <tr>
                    <td style="font-weight:700; color:#c9d1d9;">${escapeHtml(item.nombre)}</td>
                    <td>
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="Compras.cambiarCantidad(${idx}, item.cantidad-1)">−</button>
                            <span class="qty-value">${item.cantidad}</span>
                            <button class="qty-btn" onclick="Compras.cambiarCantidad(${idx}, item.cantidad+1)">+</button>
                        </div>
                    </td>
                    <td style="text-align:right;">${formatMoney(item.precio_unitario)}</td>
                    <td style="text-align:right; font-weight:700;">${formatMoney(subtotal)}</td>
                    <td style="text-align:center;">
                        <button class="delete-btn" onclick="Compras.eliminarItem(${idx})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
        document.getElementById('totalCompra').textContent = formatMoney(total);
    }

    function cambiarCantidad(index, qty){
        qty = parseInt(qty) || 0;
        if (qty <= 0) return;
        if (carrito[index]) {
            carrito[index].cantidad = qty;
            renderCarrito();
        }
    }

    function eliminarItem(index){
        carrito.splice(index,1);
        renderCarrito();
    }

    async function confirmarCompra(){
        if (!carrito.length) { showAlert('El carrito está vacío', 'warning'); return; }
        const proveedorNombre = document.getElementById('proveedorNombre').value.trim();
        const productos = carrito.map(i=>({ producto_id: i.producto_id, cantidad: i.cantidad, precio_compra: i.precio_unitario }));

        try {
            const resp = await fetch('actions/compras_confirm.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ proveedor_nombre: proveedorNombre, productos })
            });
            const res = await resp.json();
            if (res.success) {
                showAlert('Compra registrada exitosamente. Folio: ' + (res.folio || ''), 'success');
                carrito.length = 0;
                document.getElementById('proveedorNombre').value = '';
                document.getElementById('buscadorProducto').value = '';
                document.getElementById('resultadosProductos').innerHTML = '';
                renderCarrito();
            } else {
                showAlert(res.message || 'Error al registrar compra', 'danger');
            }
        } catch (err) {
            console.error(err);
            showAlert('Error de conexión', 'danger');
        }
    }

    function formatMoney(n){
        return '$' + (Number(n) || 0).toFixed(2);
    }

    function escapeHtml(text){
        if (!text) return '';
        return String(text).replace(/[&<>"']/g, function(s){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[s]; });
    }

    function showAlert(message, type='success'){
        const alertContainer = document.getElementById('alertContainer');
        const typeConfig = {
            'success': { iconClass: 'fa-check-circle', color: '#2ea043', bg: '#0d1117' },
            'danger': { iconClass: 'fa-exclamation-triangle', color: '#f85149', bg: '#0d1117' },
            'warning': { iconClass: 'fa-exclamation-circle', color: '#d29922', bg: '#0d1117' },
            'info': { iconClass: 'fa-info-circle', color: '#58a6ff', bg: '#0d1117' }
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
            <i class="fas ${config.iconClass}" style="color: ${config.color}; font-size: 18px;"></i>
            <span style="flex: 1;">${message}</span>
        `;
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);
        setTimeout(() => {
            alert.style.animation = 'slideUp 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    }

    // public API
    return {
        buscarProductoCompra,
        agregarProductoCompra,
        renderCarrito,
        cambiarCantidad,
        eliminarItem,
        confirmarCompra,
        carrito // expose for debugging
    };
})();
