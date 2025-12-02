# Documentación Técnica: Base de Datos (Papelería POS)

**Versión:** 1.0  
**Motor:** MySQL (InnoDB)  
**Base de Datos:** `papeleria_db`  

## 1. Resumen de Arquitectura
La base de datos sigue un modelo relacional normalizado para soportar transacciones de **Venta**, **Compra** y **Devolución**. 

### Puntos Clave:
* **Separación Cabecera-Detalle:** Las transacciones (Ventas/Compras) se dividen en dos tablas: una para los datos generales (fecha, total, usuario) y otra para el desglose de productos (items individuales).
* **Automatización de Stock:** El control de inventario se maneja mediante **Triggers** de base de datos, eliminando la necesidad de cálculos aritméticos en el Backend (PHP).
* **Seguridad:** Las contraseñas se almacenan encriptadas (`VARCHAR(255)`).

---

## 2. Diccionario de Datos

### A. Catálogos y Usuarios

#### `usuarios`
Almacena el personal con acceso al sistema.
| Columna | Tipo | Descripción |
| :--- | :--- | :--- |
| `id` | INT (PK) | Identificador único. |
| `nombre` | VARCHAR(100) | Nombre completo del usuario. |
| `email` | VARCHAR(100) | Correo único (Login). |
| `password` | VARCHAR(255) | Hash de la contraseña. |
| `rol` | ENUM | 'admin' o 'operador'. |
| `activo` | TINYINT | 1 = Activo, 0 = Baja lógica. |

#### `productos`
Catálogo maestro de artículos.
| Columna | Tipo | Descripción |
| :--- | :--- | :--- |
| `id` | INT (PK) | Identificador único. |
| `nombre` | VARCHAR(200) | Nombre del producto. |
| `precio_compra` | DECIMAL | Costo de adquisición. |
| `precio_venta` | DECIMAL | Precio al público. |
| `codigo_barras` | VARCHAR(50) | Código único para escáner. |
| `activo` | TINYINT | 1 = Disponible, 0 = Descontinuado. |

#### `existencias`
Control de stock actual. Relación 1:1 con Productos.
| Columna | Tipo | Descripción |
| :--- | :--- | :--- |
| `id` | INT (PK) | Identificador. |
| `producto_id` | INT (FK) | Referencia a `productos`. |
| `cantidad` | INT | Stock actual (se actualiza vía Triggers). |
| `fecha_actualizacion`| TIMESTAMP | Última vez que se movió el inventario. |

---

### B. Transaccionales (Ventas, Compras, Devoluciones)

> **Nota de Lógica:** Todas las transacciones funcionan con un modelo Maestro-Detalle.

#### 1. Módulo de Ventas
* **`ventas` (Maestro):** Guarda el `folio`, `usuario_id` (quién vendió), `total`, `iva` y `fecha`.
* **`ventas_detalle` (Detalle):** Guarda cada producto vendido en esa transacción.
    * *Relación:* Un `venta_id` puede tener múltiples filas aquí.
    * *Columnas clave:* `producto_id`, `cantidad`, `precio_unitario`, `subtotal`.

#### 2. Módulo de Compras (Reabastecimiento)
* **`compras` (Maestro):** Registro de entrada de mercancía.
* **`compras_detalle` (Detalle):** Desglose de productos adquiridos.

#### 3. Módulo de Devoluciones
* **`devoluciones` (Maestro):** Registro de retornos de cliente.
* **`devoluciones_detalle` (Detalle):** Productos devueltos al inventario.

---

## 3. Lógica de Negocio (Triggers)

El sistema utiliza disparadores (Triggers) para mantener la integridad del inventario automáticamente. **El Backend NO debe actualizar la tabla `existencias` manualmente.**

### `trg_venta_detalle_after_insert`
* **Evento:** Después de insertar en `ventas_detalle`.
* **Acción:** **RESTA** la cantidad vendida a la tabla `existencias`.
* **Lógica:** `Stock = Stock - New.cantidad`

### `trg_compra_detalle_after_insert`
* **Evento:** Después de insertar en `compras_detalle`.
* **Acción:** **SUMA** la cantidad comprada a la tabla `existencias`.
* **Lógica:** `Stock = Stock + New.cantidad`

### `trg_devolucion_detalle_after_insert`
* **Evento:** Después de insertar en `devoluciones_detalle`.
* **Acción:** **SUMA** (Regresa) la cantidad devuelta a la tabla `existencias`.
* **Lógica:** `Stock = Stock + New.cantidad`

---

## 4. Instrucciones de Instalación SQL

Para desplegar la base de datos correctamente, ejecutar los scripts en el siguiente orden estricto:

1.  **`schema.sql`**: Crea la estructura de tablas y relaciones.
2.  **`triggers.sql`**: Instala la automatización del inventario.
3.  **`seed.sql`**: Carga los datos de prueba (Usuario Admin/Operador y productos base).