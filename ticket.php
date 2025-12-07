<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_user.php';

$venta_id = $_GET['venta_id'] ?? null;

if (!$venta_id) {
    die('ID de venta no especificado');
}
// Obtener datos reales desde la base de datos (misma lógica que actions/print_ticket.php)
$conn = getConnection();

// Obtener datos de la venta
$stmt = $conn->prepare("SELECT v.id, v.folio, u.nombre as cajero, v.subtotal, v.iva, v.total, v.fecha 
                        FROM ventas v 
                        INNER JOIN usuarios u ON v.usuario_id = u.id 
                        WHERE v.id = ?");
$stmt->bind_param('i', $venta_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeConnection($conn);
    die('Venta no encontrada');
}

$venta = $result->fetch_assoc();
$stmt->close();

// Obtener detalles de la venta
$stmt = $conn->prepare("SELECT p.nombre as producto_nombre, vd.cantidad, vd.precio_unitario, vd.subtotal, p.codigo_barras 
                        FROM ventas_detalle vd 
                        INNER JOIN productos p ON vd.producto_id = p.id 
                        WHERE vd.venta_id = ?");
$stmt->bind_param('i', $venta_id);
$stmt->execute();
$result = $stmt->get_result();

$detalle = [];
while ($row = $result->fetch_assoc()) {
    $detalle[] = [
        'producto_nombre' => $row['producto_nombre'],
        'cantidad' => (int)$row['cantidad'],
        'precio_unitario' => (float)$row['precio_unitario'],
        'subtotal' => (float)$row['subtotal'],
        'codigo_barras' => $row['codigo_barras']
    ];
}
$stmt->close();

// Formatear montos como floats
$venta['subtotal'] = (float)$venta['subtotal'];
$venta['iva'] = (float)$venta['iva'];
$venta['total'] = (float)$venta['total'];

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - <?= $venta['folio'] ?></title>
    <style>
        @media print {
            body { 
                margin: 0; 
                padding: 0;
            }
            .no-print { 
                display: none !important; 
            }
            @page {
                size: 80mm 40mm;
                margin: 0;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            width: 80mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 9px;
            padding: 3mm;
            background-color: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 3mm;
        }
        
        .header h1 {
            font-size: 14px;
            margin: 0;
            font-weight: bold;
        }
        
        .header .logo {
            font-size: 20px;
        }
        
        .header p {
            margin: 1px 0;
            font-size: 8px;
        }
        
        hr {
            border: none;
            border-top: 1px dashed #333;
            margin: 2mm 0;
        }
        
        .info-venta {
            font-size: 9px;
            margin-bottom: 2mm;
        }
        
        .info-venta div {
            margin: 1px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        
        table thead th {
            text-align: left;
            padding-bottom: 2px;
            border-bottom: 1px solid #333;
        }
        
        table tbody td {
            padding: 2px 0;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totales {
            margin-top: 2mm;
            font-size: 9px;
        }
        
        .totales div {
            display: flex;
            justify-content: space-between;
            margin: 1px 0;
        }
        
        .totales .total-final {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2mm;
            padding-top: 2mm;
            border-top: 1px solid #333;
        }
        
        .footer {
            text-align: center;
            margin-top: 3mm;
            font-size: 8px;
        }
        
        .no-print {
            margin-top: 10mm;
            text-align: center;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 8px;
        }
        
        .no-print button {
            margin: 5px;
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-print {
            background-color: #4a7c2f;
            color: white;
        }
        
        .btn-close {
            background-color: #8d6e63;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🌱</div>
        <h1>PAPELERÍA SIGMA</h1>
        <p>Calle Ejemplo #123, Col. Centro</p>
        <p>Tel: (123) 456-7890</p>
        <p>RFC: PSI123456ABC</p>
    </div>
    
    <hr>
    
    <div class="info-venta">
        <div><strong>FOLIO:</strong> <?= $venta['folio'] ?></div>
        <div><strong>FECHA:</strong> <?= $venta['fecha'] ?></div>
        <div><strong>CAJERO:</strong> <?= $venta['cajero'] ?></div>
    </div>
    
    <hr>
    
    <table>
        <thead>
            <tr>
                <th>CANT</th>
                <th>PRODUCTO</th>
                <th class="text-right">PRECIO</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalle as $item): ?>
            <tr>
                <td><?= $item['cantidad'] ?></td>
                <td><?= $item['producto_nombre'] ?></td>
                <td class="text-right">$<?= number_format($item['precio_unitario'], 2) ?></td>
                <td class="text-right">$<?= number_format($item['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <hr>
    
    <div class="totales">
        <div>
            <span>SUBTOTAL:</span>
            <span>$<?= number_format($venta['subtotal'], 2) ?></span>
        </div>
        <div>
            <span>IVA (16%):</span>
            <span>$<?= number_format($venta['iva'], 2) ?></span>
        </div>
        <div class="total-final">
            <span>TOTAL:</span>
            <span>$<?= number_format($venta['total'], 2) ?></span>
        </div>
    </div>
    
    <hr>
    
    <div class="footer">
        <p><strong>¡GRACIAS POR SU COMPRA!</strong></p>
        <p>Conserve su ticket</p>
        <p>🌱 Comprometidos con el medio ambiente 🌱</p>
    </div>
    
    <!-- Botones (solo en pantalla) -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Imprimir Ticket
        </button>
        <button class="btn-close" onclick="window.close()">
            ✖️ Cerrar
        </button>
    </div>
</body>
</html>