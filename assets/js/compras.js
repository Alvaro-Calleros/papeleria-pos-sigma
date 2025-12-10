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
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:8px; border-bottom:1px solid #eee;">
                        <div><strong>${escapeHtml(p.nombre)}</strong><br><small>${escapeHtml(p.codigo_barras || '')}</small></div>
                        <div style="text-align:right;">
                            <div>${formatMoney(p.precio_compra || p.precio_venta)}</div>
                            <button onclick='Compras.agregarProductoCompra(${JSON.stringify({id: p.id, nombre: p.nombre, precio: p.precio_compra || p.precio_venta})})'>Agregar</button>
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
                    <td>${escapeHtml(item.nombre)}</td>
                    <td style="text-align:center;"><input type="number" min="1" value="${item.cantidad}" onchange="Compras.cambiarCantidad(${idx}, this.value)" style="width:64px;"></td>
                    <td style="text-align:right;">${formatMoney(item.precio_unitario)}</td>
                    <td style="text-align:right;">${formatMoney(subtotal)}</td>
                    <td style="text-align:center;"><button onclick="Compras.eliminarItem(${idx})">Eliminar</button></td>
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
        const container = document.getElementById('alertContainer');
        const el = document.createElement('div');
        el.style.padding = '10px';
        el.style.border = '1px solid #ddd';
        el.style.marginTop = '8px';
        el.textContent = message;
        container.appendChild(el);
        setTimeout(()=> el.remove(), 4000);
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
