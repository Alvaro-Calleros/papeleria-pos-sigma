-- Triggers para manejar la lógica de stock en la base de datos.
-- Se actualizan automáticamente las existencias cuando se registran ventas,
-- compras o devoluciones, permitiendo soportar ventas con múltiples productos
-- sin depender de la lógica del backend o frontend.
-- ELIMINAR SI ESTA LOGICA SE VA A IMPLEMENTAR DE OTRO MODO Q CRUCEN Y NO PERMITAN
-- FUNCIONAMIENTO.

DELIMITER $$

CREATE TRIGGER trg_venta_detalle_after_insert
AFTER INSERT ON ventas_detalle
FOR EACH ROW
BEGIN
    UPDATE existencias
    SET cantidad = cantidad - NEW.cantidad,
        fecha_actualizacion = NOW()
    WHERE producto_id = NEW.producto_id;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER trg_compra_detalle_after_insert
AFTER INSERT ON compras_detalle
FOR EACH ROW
BEGIN
    UPDATE existencias
    SET cantidad = cantidad + NEW.cantidad,
        fecha_actualizacion = NOW()
    WHERE producto_id = NEW.producto_id;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER trg_devolucion_detalle_after_insert
AFTER INSERT ON devoluciones_detalle
FOR EACH ROW
BEGIN
    UPDATE existencias
    SET cantidad = cantidad + NEW.cantidad,
        fecha_actualizacion = NOW()
    WHERE producto_id = NEW.producto_id;
END$$

DELIMITER ;