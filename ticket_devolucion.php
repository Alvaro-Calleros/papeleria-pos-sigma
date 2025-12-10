<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_user.php';

$devolucion_id = $_GET['id'] ?? null;

if (!$devolucion_id) {
    die('ID de devolución no especificado');
}

$conn = getConnection();

// Obtener datos de la devolución
$stmt = $conn->prepare("SELECT d.id, d.folio, d.fecha, d.total, v.folio as venta_folio, u.nombre as cajero
                        FROM devoluciones d
                        INNER JOIN ventas v ON d.venta_id = v.id
                        INNER JOIN usuarios u ON d.usuario_id = u.id
                        WHERE d.id = ?");
$stmt->bind_param('i', $devolucion_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeConnection($conn);
    die('Devolución no encontrada');
}

$devolucion = $result->fetch_assoc();
$stmt->close();

// Obtener detalles
$stmt = $conn->prepare("SELECT p.nombre as producto_nombre, dd.cantidad, dd.precio_unitario, dd.subtotal, p.codigo_barras
                        FROM devoluciones_detalle dd
                        INNER JOIN productos p ON dd.producto_id = p.id
                        WHERE dd.devolucion_id = ?");
$stmt->bind_param('i', $devolucion_id);
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

// Formatear total
$devolucion['total'] = (float)$devolucion['total'];

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Devolución - <?= $devolucion['folio'] ?></title>
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
            width: 40mm;
            height: auto;
            margin: 0 auto 2mm;
            display: block;
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
        <svg class="logo" viewBox="0 0 210 65" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="blueGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#58a6ff;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#1f6feb;stop-opacity:1" />
                </linearGradient>
            </defs>
            <g transform="translate(52, 32)">
                <circle cx="0" cy="0" r="18" fill="none" stroke="url(#blueGrad)" stroke-width="7" 
                        stroke-dasharray="99 12" stroke-dashoffset="0" stroke-linecap="round"/>
            </g>
            <text x="86" y="24" font-family="Courier New, Courier, monospace" 
                  font-size="16" font-weight="800" fill="#000" letter-spacing="0.2">
                Papelería
            </text>
            <text x="86" y="50" font-family="Courier New, Courier, monospace" 
                  font-size="25" font-weight="800" fill="url(#blueGrad)" letter-spacing="0.2">
                Sigma
            </text>
        </svg>
        <p>DEVOLUCIÓN DE PRODUCTOS</p>
        <p>Calle Ejemplo #123, Col. Centro</p>
        <p>Tel: (123) 456-7890</p>
    </div>
    
    <hr>
    
    <div class="info-venta">
        <div><strong>FOLIO DEV:</strong> <?= $devolucion['folio'] ?></div>
        <div><strong>FOLIO VENTA:</strong> <?= $devolucion['venta_folio'] ?></div>
        <div><strong>FECHA:</strong> <?= $devolucion['fecha'] ?></div>
        <div><strong>CAJERO:</strong> <?= $devolucion['cajero'] ?></div>
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
        <div class="total-final">
            <span>TOTAL DEVUELTO:</span>
            <span>$<?= number_format($devolucion['total'], 2) ?></span>
        </div>
    </div>
    
    <hr>
    
    <div class="footer">
        <p>Firma de conformidad</p>
        <br><br>
        <p>__________________________</p>
    </div>
    
    <!-- Botones (solo en pantalla) -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            Imprimir
        </button>
        <button class="btn-close" onclick="window.close()">
            Cerrar
        </button>
    </div>
</body>
</html>

