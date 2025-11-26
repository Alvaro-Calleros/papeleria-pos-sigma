# Update del Proyecto - Papelería POS

Equipo, ya está el repo base con toda la estructura. Aquí están las instrucciones de lo que cada quien hace **HOY**.

## 📦 Repo ya tiene:
- Estructura completa de carpetas
- `schema.sql` y `seed.sql` (BD lista)
- Sistema de auth (login/logout)
- Endpoints críticos de ventas (carrito + confirmar + ticket)
- Middlewares de protección por rol

---

## 🔀 Flujo de Git (OBLIGATORIO)

```bash
# 1. Clonar el repo
git clone [URL_DEL_REPO]
cd papeleria-pos

# 2. Crear tu branch desde dev
git checkout dev
git pull origin dev
git checkout -b feature/[TU_ROL]/[TAREA]

# Ejemplos:
# git checkout -b feature/backend/crud-productos
# git checkout -b feature/frontend/pos-ui
# git checkout -b feature/db/views-reportes

# 3. Trabajar en tu branch
# ... hacer cambios ...
git add .
git commit -m "feat: descripción breve de lo que hiciste"

# 4. Subir tu branch
git push origin feature/[TU_ROL]/[TAREA]

# 5. Crear Pull Request en GitHub hacia 'dev'
# Asignarme como revisor (@alvaro)
```

**NO TRABAJES EN `main` O `dev` DIRECTAMENTE.**

---

## 👤 Base de Datos - @[NOMBRE]

### Tu chamba HOY:

**1. Revisar el schema**
- [ ] Importar `sql/schema.sql` en phpMyAdmin
- [ ] Importar `sql/seed.sql`
- [ ] Verificar que todas las tablas se crearon sin errores
- [ ] Probar las relaciones (foreign keys)

**2. Crear vistas para reportes**
Crear archivo `sql/views.sql` con:

```sql
-- Vista de productos con stock
CREATE VIEW v_productos_stock AS
SELECT p.id, p.nombre, p.codigo_barras, p.precio_venta, 
       e.cantidad as stock, p.activo
FROM productos p
INNER JOIN existencias e ON p.id = e.producto_id;

-- Vista de ventas con totales
CREATE VIEW v_ventas_resumen AS
SELECT v.id, v.folio, u.nombre as cajero, 
       v.total, v.fecha, 
       COUNT(vd.id) as items_vendidos
FROM ventas v
INNER JOIN usuarios u ON v.usuario_id = u.id
LEFT JOIN ventas_detalle vd ON v.id = vd.venta_id
GROUP BY v.id;

-- Vista de productos más vendidos
CREATE VIEW v_productos_mas_vendidos AS
SELECT p.nombre, p.codigo_barras,
       SUM(vd.cantidad) as total_vendido,
       SUM(vd.subtotal) as ingresos_generados
FROM ventas_detalle vd
INNER JOIN productos p ON vd.producto_id = p.id
GROUP BY p.id
ORDER BY total_vendido DESC;
```

**3. Crear consultas clave**
Archivo `sql/queries.sql` con las consultas que usarán los reportes:

```sql
-- Ventas por rango de fechas
SELECT v.folio, v.fecha, v.total, u.nombre as cajero
FROM ventas v
INNER JOIN usuarios u ON v.usuario_id = u.id
WHERE v.fecha BETWEEN ? AND ?
ORDER BY v.fecha DESC;

-- Productos con stock bajo (menos de 10)
SELECT * FROM v_productos_stock
WHERE stock < 10 AND activo = 1;

-- Total de ventas del día
SELECT COUNT(*) as total_ventas, 
       SUM(total) as ingresos
FROM ventas
WHERE DATE(fecha) = CURDATE();
```

**Branch:** `feature/db/views-reportes`

**Entregable:** 3 archivos SQL en carpeta `sql/` con comentarios.

---

## 💻 Backend - @[NOMBRE]

### Tu chamba HOY:

Implementar los endpoints que faltan. Usa como referencia `actions/ventas_confirm.php` (ya está hecho y tiene transacciones).

**1. CRUD Productos**

**`actions/productos_create.php`**
```php
// POST: crear producto
// Campos: nombre, descripcion, precio_compra, precio_venta, codigo_barras, imagen (FILE)
// - Validar que codigo_barras sea único
// - Validar imagen (tipo: jpg/png, max 5MB)
// - Guardar imagen como BLOB
// - Insertar en productos Y en existencias (cantidad inicial = 0)
// Usar prepared statements
```

**`actions/productos_update.php`**
```php
// PUT o POST: actualizar producto
// Campos: id, nombre, descripcion, precio_compra, precio_venta, codigo_barras, imagen (opcional)
// - Solo admin puede hacer esto (require auth_admin.php)
// - Si hay nueva imagen, reemplazar BLOB
// Usar prepared statements
```

**`actions/productos_delete.php`**
```php
// DELETE o POST: soft delete (activo = 0)
// Solo admin
// No eliminar físicamente, solo marcar activo = 0
```

**`actions/productos_list.php`**
```php
// GET: listar productos con paginación
// Params: page (default 1), limit (default 10), search (opcional)
// - Si hay 'search', buscar por nombre o codigo_barras (LIKE)
// - Retornar: productos[], total_items, total_pages
```

**2. Compras**

**`actions/compras_confirm.php`**
```php
// POST: confirmar compra
// Similar a ventas_confirm.php pero:
// - Incrementa existencias (en vez de decrementar)
// - Genera folio C-XXXXX
// - No calcula IVA (solo total)
// Usa transacción BEGIN/COMMIT/ROLLBACK
```

**3. Devoluciones**

**`actions/devoluciones_confirm.php`**
```php
// POST: confirmar devolución
// Body: venta_id, productos[{producto_id, cantidad}]
// - Validar que la venta existe
// - Validar que no se devuelva más de lo comprado
// - Incrementar existencias
// - Genera folio D-XXXXX
// Transacción BEGIN/COMMIT/ROLLBACK
```

**Branch:** `feature/backend/crud-endpoints`

**Entregable:** 7 archivos PHP en `actions/` + breve comentario en cada uno explicando params y response.

**Testing:** Usa Postman/Thunder Client. Valida que funcionen los casos de error.

---

## 🎨 Frontend (UX-UI) - @[NOMBRE]

### Tu chamba HOY:

Maquetar las 4 páginas principales. **NO necesitas conectar los endpoints aún**, solo el HTML/CSS/JS básico.

**1. POS Principal - `index.php`**

```php
<?php
require_once 'includes/config.php';
require_once 'includes/auth_user.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>POS - Papelería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">Papelería POS</span>
            <div>
                <span class="text-white me-3">Cajero: <?= $_SESSION['nombre'] ?></span>
                <button class="btn btn-outline-light btn-sm" onclick="logout()">Salir</button>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <div class="row">
            <!-- Panel izquierdo: Input y carrito -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <label for="barcodeInput" class="form-label">Escanear código de barras</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="barcodeInput" 
                               placeholder="Código de barras" 
                               autofocus>
                    </div>
                </div>

                <!-- Tabla de carrito -->
                <div class="card">
                    <div class="card-header">
                        <h5>Carrito de Venta</h5>
                    </div>
                    <div class="card-body">
                        <table class="table" id="carritoTable">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel derecho: Totales y confirmar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3>Totales</h3>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="subtotalDisplay">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>IVA (16%):</span>
                            <strong id="ivaDisplay">$0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h4">Total:</span>
                            <strong class="h4 text-success" id="totalDisplay">$0.00</strong>
                        </div>
                        <button class="btn btn-success btn-lg w-100" 
                                id="confirmarBtn" 
                                disabled>
                            Confirmar Venta
                        </button>
                        <button class="btn btn-outline-danger btn-sm w-100 mt-2" 
                                id="limpiarBtn">
                            Limpiar Carrito
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/pos.js"></script>
</body>
</html>
```

**Archivo `assets/js/pos.js`** (básico, sin conectar endpoints aún):
```javascript
let carrito = [];

document.getElementById('barcodeInput').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        const codigo = e.target.value.trim();
        if (codigo) {
            // TODO: llamar a ventas_add.php
            console.log('Agregar:', codigo);
            e.target.value = '';
        }
    }
});

function actualizarCarrito() {
    // TODO: renderizar tabla y totales
}

function logout() {
    fetch('actions/logout.php')
        .then(() => window.location.href = 'login.php');
}
```

**2. Admin Productos - `productos.php`**

Requiere `auth_admin.php`. Maquetar:
- Tabla con productos (nombre, código, precio, stock, imagen thumbnail)
- Botón "Nuevo Producto" → modal con form
- Botón "Editar" por producto → modal con form prellenado
- Botón "Eliminar" (soft delete)
- Paginación y búsqueda

Usa Bootstrap modals. **NO conectes endpoints aún**, solo deja los eventos `console.log('Crear producto')`.

**3. Ticket - `ticket.php`**

```php
<?php
require_once 'includes/config.php';
require_once 'includes/auth_user.php';

$venta_id = $_GET['venta_id'] ?? null;
// TODO: fetch data usando print_ticket.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        @page {
            size: 80mm 40mm;
            margin: 0;
        }
        body {
            width: 80mm;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            padding: 2mm;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        hr { border: none; border-top: 1px dashed #000; }
        table { width: 100%; }
    </style>
</head>
<body>
    <div class="center">
        <strong>PAPELERÍA XYZ</strong><br>
        Calle Ejemplo #123<br>
        Tel: 123-456-7890
    </div>
    <hr>
    <div>
        <strong>Folio:</strong> V-00001<br>
        <strong>Fecha:</strong> 26/11/2024 15:30<br>
        <strong>Cajero:</strong> Juan Operador
    </div>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Cant</th>
                <th>Producto</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>2</td>
                <td>Cuaderno 100h</td>
                <td class="right">$50.00</td>
            </tr>
        </tbody>
    </table>
    <hr>
    <div class="right">
        <strong>Subtotal:</strong> $50.00<br>
        <strong>IVA (16%):</strong> $8.00<br>
        <strong>TOTAL:</strong> $58.00
    </div>
    <hr>
    <div class="center">
        ¡Gracias por su compra!
    </div>
    
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Imprimir</button>
        <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
    </div>
</body>
</html>
```

**4. Reportes - `reportes.php`**

Maqueta solo la estructura:
- Filtros: fecha inicio, fecha fin, tipo de reporte (dropdown)
- Botón "Generar"
- Tabla con resultados (vacía por ahora)
- Botón "Exportar CSV"

Usa Bootstrap. Haz que se vea profesional para impresión A4.

**Branch:** `feature/frontend/ui-pages`

**Entregable:** 4 páginas HTML/PHP con CSS responsive y estructura lista para conectar endpoints.

---

## 🔒 Seguridad - @[NOMBRE]

### Tu chamba (MAÑANA o cuando haya endpoints para testear):

Por ahora revisa el código existente:

**Checklist de revisión:**
- [ ] Todos los endpoints usan `auth_user.php` o `auth_admin.php`
- [ ] Todas las queries usan prepared statements (no hay SQL directo con variables)
- [ ] El login usa `password_verify()`
- [ ] Hay `session_regenerate_id(true)` después del login
- [ ] Los uploads de imagen validan tipo y tamaño

**Cuando backend termine, implementarás:**
1. **CSRF tokens** en forms de productos/compras/devoluciones
2. **Rate limiting** en login (máximo 5 intentos por minuto)
3. **Sanitización** de inputs en búsquedas (evitar XSS)
4. **Headers de seguridad** (X-Frame-Options, etc.)

Crea un archivo `docs/seguridad.md` documentando las protecciones implementadas.

---

## 🖨️ Hardware - @[NOMBRE]

### Tu chamba (ÚLTIMA SEMANA):

Por ahora solo prepara:

**Investigación HOY:**
- [ ] Buscar el modelo exacto de la impresora térmica que usará el profe
- [ ] Descargar drivers del fabricante (Bixolon, Epson, Star, etc.)
- [ ] Leer manual de instalación
- [ ] Crear documento `docs/hardware_setup.md` con:
  - Modelo de impresora
  - Link a drivers
  - Pasos de instalación (Windows/Linux)
  - Configuración de tamaño de papel (80×40mm)

**Cuando tengas la impresora física:**
- Probar `ticket.php` imprime correctamente
- Ajustar CSS si hay márgenes raros
- Tomar fotos/capturas para evidencia
- Probar lector de código de barras (debe actuar como teclado)

---

## 📋 Resumen de Entregables HOY

| Rol | Entregable | Branch |
|-----|-----------|--------|
| **BD** | `views.sql` + `queries.sql` | `feature/db/views-reportes` |
| **Backend** | 7 endpoints en `/actions` | `feature/backend/crud-endpoints` |
| **Frontend** | 4 páginas maquetadas | `feature/frontend/ui-pages` |
| **Seguridad** | Revisión de código + `docs/seguridad.md` | `feature/security/review` |
| **Hardware** | `docs/hardware_setup.md` | `feature/hardware/docs` |

---

## ⏰ Deadline

**HOY antes de las 11:59 PM:**
- Push de tu branch
- Pull Request hacia `dev` con descripción clara

**Mañana:**
- Revisaré PRs y haré merge
- Frontend conectará endpoints
- Testing de integración

---

## 🆘 Si tienes dudas

1. Revisa el código que ya está en el repo (es tu referencia)
2. Pregunta en el grupo
3. Si algo no jala, documenta el error y avísame

Manos a la obra. 🚀