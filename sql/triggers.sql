-- Triggers para manejar la lógica de stock en la base de datos.
-- Se actualizan automáticamente las existencias cuando se registran ventas,
-- compras o devoluciones, permitiendo soportar ventas con múltiples productos
-- sin depender de la lógica del backend o frontend.
-- 
-- Este archivo contiene TODOS los triggers del sistema.
-- Se puede ejecutar múltiples veces de forma segura (idempotente).

USE `papeleria_db`;

-- ============================================================================
-- TRIGGER: Actualizar stock al registrar una venta
-- ============================================================================
DROP TRIGGER IF EXISTS trg_venta_detalle_after_insert;

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

-- ============================================================================
-- TRIGGER: Actualizar stock al registrar una compra
-- ============================================================================
DROP TRIGGER IF EXISTS trg_compra_detalle_after_insert;

DELIMITER $$

CREATE TRIGGER trg_compra_detalle_after_insert
AFTER INSERT ON compras_detalle
FOR EACH ROW
BEGIN
    -- Insertar o actualizar existencias
    -- Si no existe el registro, lo crea con la cantidad comprada
    -- Si existe, incrementa la cantidad
    INSERT INTO existencias (producto_id, cantidad, fecha_actualizacion)
    VALUES (NEW.producto_id, NEW.cantidad, NOW())
    ON DUPLICATE KEY UPDATE
        cantidad = cantidad + NEW.cantidad,
        fecha_actualizacion = NOW();
END$$

DELIMITER ;

-- ============================================================================
-- TRIGGER: Revertir stock al eliminar una línea de compra
-- ============================================================================
DROP TRIGGER IF EXISTS trg_compra_detalle_after_delete;

DELIMITER $$

CREATE TRIGGER trg_compra_detalle_after_delete
AFTER DELETE ON compras_detalle
FOR EACH ROW
BEGIN
    -- Al eliminar una línea de compra, revertimos el aumento de stock
    UPDATE existencias
    SET cantidad = cantidad - OLD.cantidad,
        fecha_actualizacion = NOW()
    WHERE producto_id = OLD.producto_id;
END$$

DELIMITER ;

-- ============================================================================
-- TRIGGER: Actualizar stock al modificar una línea de compra
-- ============================================================================
DROP TRIGGER IF EXISTS trg_compra_detalle_after_update;

DELIMITER $$

CREATE TRIGGER trg_compra_detalle_after_update
AFTER UPDATE ON compras_detalle
FOR EACH ROW
BEGIN
    -- Si cambia el producto, revertimos en el antiguo y aplicamos al nuevo
    IF NEW.producto_id <> OLD.producto_id THEN
        UPDATE existencias
        SET cantidad = cantidad - OLD.cantidad,
            fecha_actualizacion = NOW()
        WHERE producto_id = OLD.producto_id;

        UPDATE existencias
        SET cantidad = cantidad + NEW.cantidad,
            fecha_actualizacion = NOW()
        WHERE producto_id = NEW.producto_id;
    ELSE
        -- Si solo cambió la cantidad, aplicar la diferencia
        UPDATE existencias
        SET cantidad = cantidad + (NEW.cantidad - OLD.cantidad),
            fecha_actualizacion = NOW()
        WHERE producto_id = NEW.producto_id;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- TRIGGER: Actualizar stock al registrar una devolución
-- ============================================================================
DROP TRIGGER IF EXISTS trg_devolucion_detalle_after_insert;

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