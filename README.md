# Papelería Sigma - Sistema POS

Sistema de Punto de Venta moderno con diseño Dark Pro profesional para papelería.

## Características Principales

- **Diseño Dark Pro** - Interfaz moderna inspirada en GitHub Dark
- **POS Completo** - Carrito de ventas con escaneo de códigos de barras
- **Gestión de Productos** - CRUD completo con imágenes
- **Reportes** - Estadísticas y exportación a CSV
- **Sistema de Roles** - Admin y Operador con permisos diferenciados
- **Tickets 80×40mm** - Impresión térmica optimizada
- **Sin Dependencias** - No requiere Bootstrap, JavaScript vanilla puro

## Requisitos

- XAMPP (PHP 8.0+, MySQL 8.0+)
- Navegador web moderno (Chrome 90+, Firefox 88+, Safari 14+)
- Lector de código de barras USB (opcional)
- Impresora térmica 80×40mm (opcional)

## Instalación

### 1. Clonar repositorio
```bash
git clone <repo-url>
cd papeleria-pos
```

### 2. Configurar XAMPP
1. Copiar carpeta del proyecto a `C:\xampp\htdocs\papeleria-pos`
2. Iniciar Apache y MySQL desde XAMPP Control Panel

### 3. Crear base de datos
1. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Importar `sql/schema.sql`
3. Importar `sql/seed.sql` (datos de prueba)

### 4. Configurar conexión
Editar `includes/config.php` si es necesario:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'papeleria_db');
```

### 5. Acceder al sistema
- URL: `http://localhost/papeleria-pos`
- Usuario admin (de seed.sql):
  - Email: `admin@papeleria.com`
  - Password: `admin123`

## Estructura del Proyecto

```
papeleria-pos/
├── sql/                    # Scripts de BD
├── includes/               # Configuración y middlewares
├── actions/                # Endpoints PHP (APIs)
├── assets/                 # CSS, JS, imágenes
├── index.php              # POS principal
├── login.php              # Página de login
├── productos.php          # CRUD productos
├── ticket.php             # Vista de ticket 80×40
└── reportes.php           # Reportes y CSV
```

## Roles y Responsabilidades del Equipo

### 1. Líder de Proyecto
- Documentación final
- Endpoints críticos: `ventas_confirm.php`, `print_ticket.php`
- Revisión de PRs y merge

### 2. Frontend (UX-UI)
- `index.php` (POS)
- `productos.php` (admin)
- `ticket.php` (80×40mm)
- `reportes.php` (A4)

### 3. Backend
- Implementar todos los endpoints en `/actions`
- Validaciones y prepared statements

### 4. Base de Datos
- `schema.sql` y `seed.sql`
- Vistas, índices y consultas de reportes

### 5. Autenticación y Seguridad
- Login con `password_hash`
- Middlewares `auth_admin.php` y `auth_user.php`
- Protección contra SQLi, validación de uploads

### 6. Hardware
- Configurar impresora térmica 80×40mm
- Probar lector de código de barras USB
- Documentar instalación de drivers

## Flujo de Trabajo Git

### Branches
- `main`: producción
- `dev`: integración
- `feature/{rol}/{tarea}`: ramas de trabajo

### Ejemplo
```bash
git checkout dev
git pull origin dev
git checkout -b feature/backend/ventas-transac
# ... trabajar ...
git add .
git commit -m "feat: implementar confirmación de ventas"
git push origin feature/backend/ventas-transac
# Crear PR en GitHub hacia dev
```

## Endpoints Principales (a implementar)

### Autenticación
- `POST /actions/login.php` - Login
- `POST /actions/logout.php` - Logout

### Productos
- `POST /actions/productos_create.php` - Crear producto
- `PUT /actions/productos_update.php` - Actualizar producto
- `DELETE /actions/productos_delete.php` - Eliminar producto

### Ventas
- `POST /actions/ventas_add.php` - Agregar al carrito (sesión)
- `POST /actions/ventas_confirm.php` - Confirmar venta (transacción)

### Compras y Devoluciones
- `POST /actions/compras_confirm.php`
- `POST /actions/devoluciones_confirm.php`

### Reportes
- `GET /actions/export_csv.php` - Exportar a CSV

## ✅ Estado de Desarrollo

### Completado
- [x] Login funcional con roles admin/operador
- [x] CRUD productos con imagen BLOB
- [x] POS con input autofocus para lector
- [x] Confirmar venta con transacción (BEGIN/COMMIT/ROLLBACK)
- [x] Validar stock insuficiente
- [x] Devoluciones con límite de cantidad vendida
- [x] Ticket 80×40mm imprime correctamente
- [x] Reportes A4 con paginación
- [x] Export CSV con BOM UTF-8
- [x] **Diseño Dark Pro completo** ✨
- [x] **Sistema de alertas custom sin Bootstrap**
- [x] **Logo SVG profesional**
- [x] **Sidebar navigation**

### En Desarrollo
- [ ] Módulo de compras (ingreso de stock)
- [ ] Dashboard con gráficas
- [ ] PWA para uso offline

## Paleta de Colores Dark Pro

```
Background:     #0d1117
Cards/Sidebar:  #161b22
Borders:        #30363d
Primary Blue:   #58a6ff
Secondary Blue: #1f6feb
Success Green:  #2ea043
Danger Red:     #f85149
Text:           #c9d1d9
Muted:          #8b949e
```

## Documentación

- **Frontend:** `docs/FRONTEND-DOCUMENTACION.md` (642 líneas)
- **Backend:** `backend.md`
- **Base de Datos:** `sql/documentacion.md`

## Equipo

- **Líder:** Álvaro
- **Frontend:** Abraham
- **Backend:** Luisito, Arturo
- **Base de Datos:** Santi, Fer
- **Hardware:** Nolberto

## Soporte

Para dudas técnicas, abrir issue en GitHub o consultar con el líder del proyecto.

---

**Última actualización:** Diciembre 8, 2024  
**Versión:** 2.0.0 - Dark Pro Edition
