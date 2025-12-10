<?php
require_once 'includes/config.php';
require_once 'includes/auth_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compras - Papelería Sigma</title>
</head>
<body>
    <main style="padding:16px; max-width:1100px; margin:0 auto;">
        <h2>Registrar Compra</h2>

        <section style="margin-top:12px;">
            <label>Proveedor (nombre):</label>
            <input type="text" id="proveedorNombre" placeholder="Escribe nombre de proveedor o selecciónalo luego" style="width:100%; padding:8px; margin-top:6px;">
        </section>

        <section style="margin-top:18px;">
            <label>Buscar producto (código o nombre):</label>
            <div style="display:flex; gap:8px; margin-top:6px;">
                <input type="text" id="buscadorProducto" placeholder="Código o nombre..." style="flex:1; padding:8px;" onkeypress="if(event.key==='Enter') Compras.buscarProductoCompra()">
                <button onclick="Compras.buscarProductoCompra()">Buscar</button>
            </div>
            <div id="resultadosProductos" style="margin-top:12px;"></div>
        </section>

        <section style="margin-top:18px;">
            <h3>Carrito de Compra</h3>
            <div style="overflow-x:auto;">
                <table border="0" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align:center;">Cant.</th>
                            <th style="text-align:right;">Precio</th>
                            <th style="text-align:right;">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="carritoBody">
                        <tr><td colspan="5" style="text-align:center; padding:16px; color:#666;">Carrito vacío</td></tr>
                    </tbody>
                </table>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:12px; align-items:center;">
                <div><strong>Total:</strong> <span id="totalCompra">$0.00</span></div>
                <button onclick="Compras.confirmarCompra()">Confirmar Compra</button>
            </div>
        </section>

        <div id="alertContainer" style="margin-top:18px;"></div>
    </main>

    <script src="assets/js/compras.js?v=<?php echo time(); ?>"></script>
</body>
</html>
