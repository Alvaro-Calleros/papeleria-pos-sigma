# Guía de Testing - Endpoints del Líder

## Prerrequisitos
1. XAMPP corriendo (Apache + MySQL)
2. Base de datos creada con `schema.sql` y `seed.sql`
3. Proyecto en `C:\xampp\htdocs\papeleria-pos`

## Herramientas de Testing
- **Navegador**: Para login y logout
- **Postman/Thunder Client**: Para probar endpoints de venta
- **Console del navegador**: Para ver respuestas JSON

---

## 1. Test Login

### Con navegador:
1. Ir a `http://localhost/papeleria-pos/login.php`
2. Credenciales de prueba:
   - **Admin**: `admin@papeleria.com` / `admin123`
   - **Operador**: `operador@papeleria.com` / `operador123`
3. Debe redirigir a `index.php`

### Con cURL:
```bash
curl -X POST http://localhost/papeleria-pos/actions/login.php \
  -d "email=operador@papeleria.com&password=operador123"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Login exitoso",
  "user": {
    "nombre": "Juan Operador",
    "rol": "operador"
  }
}
```

---

## 2. Test Agregar al Carrito

**Primero haz login** (paso anterior).

### Con Postman/Thunder Client:
```
POST http://localhost/papeleria-pos/actions/ventas_add.php
Content-Type: application/x-www-form-urlencoded

codigo_barras=7501234567890
```

### Con cURL (mantén la cookie de sesión):
```bash
curl -X POST http://localhost/papeleria-pos/actions/ventas_add.php \
  -b cookies.txt -c cookies.txt \
  -d "codigo_barras=7501234567890"
```

**Respuesta esperada (primera vez):**
```json
{
  "success": true,
  "message": "Producto agregado al carrito",
  "carrito": [
    {
      "producto_id": 1,
      "nombre": "Cuaderno profesional 100 hojas",
      "precio_unitario": 25.00,
      "cantidad": 1,
      "codigo_barras": "7501234567890"
    }
  ],
  "totales": {
    "items_count": 1,
    "subtotal": 25.00,
    "iva": 4.00,
    "total": 29.00
  }
}
```

**Repite la petición 2 veces más** para tener 3 cuadernos en carrito.

### Agregar más productos:
```bash
# Pluma (código: 7501234567891)
curl -X POST http://localhost/papeleria-pos/actions/ventas_add.php \
  -b cookies.txt -c cookies.txt \
  -d "codigo_barras=7501234567891"

# Lápiz (código: 7501234567892)
curl -X POST http://localhost/papeleria-pos/actions/ventas_add.php \
  -b cookies.txt -c cookies.txt \
  -d "codigo_barras=7501234567892"
```

---

## 3. Test Confirmar Venta (Transacción)

**Asegúrate de tener productos en el carrito** (paso anterior).

### Con Postman/Thunder Client:
```
POST http://localhost/papeleria-pos/actions/ventas_confirm.php
Content-Type: application/x-www-form-urlencoded
```

### Con cURL:
```bash
curl -X POST http://localhost/papeleria-pos/actions/ventas_confirm.php \
  -b cookies.txt -c cookies.txt
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Venta registrada exitosamente",
  "data": {
    "folio": "V-00002",
    "venta_id": 2,
    "subtotal": 86.00,
    "iva": 13.76,
    "total": 99.76
  }
}
```

### Verificar en BD:
```sql
-- Ver la venta creada
SELECT * FROM ventas WHERE folio = 'V-00002';

-- Ver el detalle
SELECT vd.*, p.nombre 
FROM ventas_detalle vd
INNER JOIN productos p ON vd.producto_id = p.id
WHERE vd.venta_id = 2;

-- Verificar que las existencias se redujeron
SELECT p.nombre, e.cantidad 
FROM productos p
INNER JOIN existencias e ON p.id = e.producto_id
WHERE p.id IN (1, 2, 3);
```

---

## 4. Test Datos para Ticket

### Con GET:
```
GET http://localhost/papeleria-pos/actions/print_ticket.php?venta_id=2
```

### Con cURL:
```bash
curl http://localhost/papeleria-pos/actions/print_ticket.php?venta_id=2 \
  -b cookies.txt
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "venta": {
      "id": 2,
      "folio": "V-00002",
      "cajero": "Juan Operador",
      "subtotal": 86.00,
      "iva": 13.76,
      "total": 99.76,
      "fecha": "2024-11-26 15:30:00"
    },
    "detalle": [
      {
        "producto_nombre": "Cuaderno profesional 100 hojas",
        "cantidad": 3,
        "precio_unitario": 25.00,
        "subtotal": 75.00,
        "codigo_barras": "7501234567890"
      },
      {
        "producto_nombre": "Pluma azul BIC",
        "cantidad": 1,
        "precio_unitario": 7.00,
        "subtotal": 7.00,
        "codigo_barras": "7501234567891"
      },
      {
        "producto_nombre": "Lápiz HB #2",
        "cantidad": 1,
        "precio_unitario": 4.00,
        "subtotal": 4.00,
        "codigo_barras": "7501234567892"
      }
    ]
  }
}
```

---

## 5. Test Logout

### Con navegador:
Desde cualquier página logueada, llamar a:
```javascript
fetch('/papeleria-pos/actions/logout.php')
  .then(r => r.json())
  .then(console.log);
```

### Con cURL:
```bash
curl http://localhost/papeleria-pos/actions/logout.php \
  -b cookies.txt
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

---

## Casos de Error a Probar

### Stock insuficiente:
```bash
# Agregar 200 cuadernos (solo hay 50 en stock)
for i in {1..200}; do
  curl -X POST http://localhost/papeleria-pos/actions/ventas_add.php \
    -b cookies.txt -c cookies.txt \
    -d "codigo_barras=7501234567890"
done
```

**Debe retornar error cuando llegues al límite:**
```json
{
  "success": false,
  "message": "No hay más stock disponible",
  "stock": 50
}
```

### Venta sin carrito:
```bash
# Hacer logout y login de nuevo (limpia sesión)
# Luego intentar confirmar venta
curl -X POST http://localhost/papeleria-pos/actions/ventas_confirm.php \
  -b cookies.txt
```

**Debe retornar:**
```json
{
  "success": false,
  "message": "Carrito vacío"
}
```

### Producto inexistente:
```bash
curl -X POST http://localhost/papeleria-pos/actions/ventas_add.php \
  -b cookies.txt -c cookies.txt \
  -d "codigo_barras=9999999999999"
```

**Debe retornar:**
```json
{
  "success": false,
  "message": "Producto no encontrado"
}
```

---

## Checklist de Funcionalidades

- [ ] Login funcional (admin y operador)
- [ ] Logout limpia sesión
- [ ] Agregar producto al carrito por código de barras
- [ ] Incrementar cantidad si producto ya está en carrito
- [ ] Validar stock al agregar
- [ ] Confirmar venta con transacción (BEGIN/COMMIT)
- [ ] Generar folio único autoincremental (V-00001, V-00002...)
- [ ] Actualizar existencias en transacción
- [ ] ROLLBACK si falla alguna operación
- [ ] Limpiar carrito después de venta exitosa
- [ ] Obtener datos de venta para ticket
- [ ] Validar que no se venda con stock insuficiente
- [ ] Validar que no se confirme venta con carrito vacío

---

## Comandos Útiles

### Ver folios generados:
```sql
SELECT folio, total, fecha FROM ventas ORDER BY id DESC LIMIT 5;
```

### Resetear autoincrement de ventas:
```sql
ALTER TABLE ventas AUTO_INCREMENT = 1;
DELETE FROM ventas WHERE id > 1; -- Mantener solo venta de seed
```

### Ver carrito actual (desde PHP):
```php
<?php
session_start();
echo json_encode($_SESSION['carrito'] ?? []);
?>
```