USE papeleria_db;

-- Insertar usuarios de prueba
-- Password para ambos: "admin123" y "operador123"
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Admin Principal', 'admin@papeleria.com', '$2y$10$pBAyb8V.CMv1.ZiGqqcq0eiUf4ikEswZyTQWZTv.XJIv2gzbY7NzG', 'admin'), -- hashs regenerados
('Juan Operador', 'operador@papeleria.com', '$2y$10$ROCsaULmbZjqF1rfpr8CZeic0uGOtOW5HCKMZYpShOz/FVhcV9/D2', 'operador');

-- Insertar productos de prueba
INSERT INTO productos (nombre, descripcion, precio_compra, precio_venta, codigo_barras) VALUES
('Cuaderno profesional 100 hojas', 'Cuaderno rayado tamaño profesional', 15.00, 25.00, '7501234567890'),
('Pluma azul BIC', 'Pluma de tinta azul punto medio', 3.50, 7.00, '7501234567891'),
('Lápiz HB #2', 'Lápiz de grafito HB #2', 2.00, 4.00, '7501234567892'),
('Borrador blanco', 'Borrador de migajón blanco', 2.50, 5.00, '7501234567893'),
('Sacapuntas metálico', 'Sacapuntas de metal resistente', 5.00, 10.00, '7501234567894'),
('Tijeras escolares', 'Tijeras punta roma 15cm', 12.00, 22.00, '7501234567895'),
('Pegamento blanco 250ml', 'Pegamento escolar no tóxico', 8.00, 15.00, '7501234567896'),
('Marcador permanente negro', 'Marcador de tinta permanente', 6.00, 12.00, '7501234567897'),
('Folder tamaño carta', 'Folder de plástico colores surtidos', 3.00, 6.00, '7501234567898'),
('Regla 30cm', 'Regla de plástico transparente', 4.00, 8.00, '7501234567899');

-- Insertar existencias iniciales
INSERT INTO existencias (producto_id, cantidad) VALUES
(1, 50),
(2, 100),
(3, 150),
(4, 80),
(5, 60),
(6, 40),
(7, 35),
(8, 70),
(9, 120),
(10, 90);

-- Insertar una venta de ejemplo (folio: V-00001)
INSERT INTO ventas (folio, usuario_id, subtotal, iva, total, fecha) VALUES
('V-00001', 2, 86.21, 13.79, 100.00, '2024-11-20 10:30:00');

-- Detalle de la venta ejemplo
INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 2, 25.00, 50.00),
(1, 2, 3, 7.00, 21.00),
(1, 4, 3, 5.00, 15.00);

-- Actualizar existencias después de la venta (esto normalmente lo hace el trigger o la transacción)
UPDATE existencias SET cantidad = cantidad - 2 WHERE producto_id = 1;
UPDATE existencias SET cantidad = cantidad - 3 WHERE producto_id = 2;
UPDATE existencias SET cantidad = cantidad - 3 WHERE producto_id = 4;

-- Insertar una compra de ejemplo (folio: C-00001)
INSERT INTO compras (folio, usuario_id, total, fecha) VALUES
('C-00001', 1, 500.00, '2024-11-15 09:00:00');

-- Detalle de la compra
INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 20, 15.00, 300.00),
(1, 7, 25, 8.00, 200.00);