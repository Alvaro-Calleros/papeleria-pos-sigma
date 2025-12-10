// Compras minimal JS
window.Compras = (function(){
    const carrito = [];

    function buscarProductoCompra(){
        const q = document.getElementById('buscadorProducto').value.trim();
        const resultados = document.getElementById('resultadosProductos');
        resultados.innerHTML = '<div style="color:#8b949e; font-style:italic; padding: 8px 0;">Buscando...</div>';
        
        if (!q) { 
            resultados.innerHTML = '<div style="color:#8b949e; padding: 8px 0;">Ingrese término de búsqueda</div>'; 
            return; 
        }

        const url = `actions/productos_list.php?search=${encodeURIComponent(q)}&activo=1`;
        
        fetch(url)
            .then(async r => {
                const text = await r.text();
                let res;
                try {
                    res = JSON.parse(text);
                } catch (e) {
                    console.error('Error parseando JSON:', e);
                    resultados.innerHTML = '<div class="alert alert-danger">Error: Respuesta inválida del servidor</div>';
                    return;
                }
                
                if (!res || !res.success) {
                    const errorMsg = res?.message || 'Error desconocido';
                    resultados.innerHTML = `<div class="alert alert-danger">Error: ${escapeHtml(errorMsg)}</div>`;
                    return;
                }
                
                const productos = res.data || [];
                if (productos.length === 0) {
                    resultados.innerHTML = '<div style="color:#8b949e; padding: 8px 0;">No se encontraron productos</div>';
                    return;
                }
                
                resultados.innerHTML = productos.map(p => `
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; border-bottom:1px solid #30363d;">
                        <div>
                            <strong style="color:#c9d1d9;">${escapeHtml(p.nombre)}</strong>
                            <br>
                            <small style="color:#8b949e;">${escapeHtml(p.codigo_barras || '')}</small>
                        </div>
                        <div style="text-align:right;">
                            <div style="color:#58a6ff; font-weight:bold; margin-bottom:4px;">${formatMoney(p.precio_compra || p.precio_venta)}</div>
                            <button class="btn-secondary" style="padding: 4px 12px; width: auto; font-size: 13px;" 
                                onclick='Compras.agregarProductoCompra(${JSON.stringify({id: p.id, nombre: p.nombre, precio: p.precio_compra || p.precio_venta})})'>
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                `).join('');
            })
            .catch(err=>{
                console.error('Error de fetch:', err);
                resultados.innerHTML = `<div class="alert alert-danger">Error de conexión: ${escapeHtml(err.message)}</div>`;
            });
    }

    function agregarProductoCompra(product){
        const existing = carrito.find(i => i.producto_id === product.id);
        if (existing) {
            existing.cantidad += 1;
        } else {
            carrito.push({ producto_id: product.id, nombre: product.nombre, cantidad: 1, precio_unitario: parseFloat(product.precio) || 0 });
        }
        renderCarrito();
        showAlert('Producto agregado', 'success');
    }

    function renderCarrito(){
        const tbody = document.getElementById('carritoBody');
        if (!carrito.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:32px; color:#8b949e;">Carrito vacío</td></tr>';
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
                    <td style="vertical-align: middle;">${escapeHtml(item.nombre)}</td>
                    <td style="text-align:center; vertical-align: middle;">
                        <input type="number" min="1" value="${item.cantidad}" 
                            class="form-control"
                            style="width: 70px; padding: 4px 8px; text-align: center; display: inline-block;"
                            onchange="Compras.cambiarCantidad(${idx}, this.value)">
                    </td>
                    <td style="text-align:right; vertical-align: middle;">${formatMoney(item.precio_unitario)}</td>
                    <td style="text-align:right; vertical-align: middle; color: #c9d1d9; font-weight: 600;">${formatMoney(subtotal)}</td>
                    <td style="text-align:center; vertical-align: middle;">
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
        const container = document.getElementById('alertContainer');
        const el = document.createElement('div');
        el.className = `alert alert-${type}`;
        
        // Create inner content with flex
        const content = document.createElement('span');
        content.textContent = message;
        content.style.flex = '1';
        el.appendChild(content);

        const closeBtn = document.createElement('button');
        closeBtn.className = 'btn-close';
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        closeBtn.onclick = () => el.remove();
        el.appendChild(closeBtn);

        container.appendChild(el);
        setTimeout(()=> {
            if(el.parentElement) el.remove();
        }, 5000);
    }

    return {
        buscarProductoCompra,
        agregarProductoCompra,
        renderCarrito,
        cambiarCantidad,
        eliminarItem,
        confirmarCompra,
        carrito 
    };
})();
