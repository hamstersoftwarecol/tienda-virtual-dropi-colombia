# 🛍️ NovaStore Colombia — Tienda Virtual en Laravel 12 + Breeze + Bootstrap 5

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)

Plataforma de comercio electrónico de alto rendimiento desarrollada en **Laravel 12**, interfaz moderna con **Bootstrap 5.3 + Bootstrap Icons**, adaptada completamente al mercado de **Colombia (Pesos COP)** con catálogo interactivo, carrito de compras, cupones, checkout optimizado y panel de administración completo.

---

## 🌟 Características Principales

### 🛒 Frontend Storefront (Experiencia de Cliente)
- **Inicio Dinámico**: Hero banner con llamados a la acción, categorías destacadas, productos en oferta con cálculo dinámico de ahorro, sección de novedades y más vendidos.
- **Catálogo Multifacético (`/shop`)**: Búsqueda en tiempo real por nombre/SKU, filtros por categoría, rango de precios en COP, disponibilidad en stock y ofertas.
- **Ficha de Producto (`/product/{slug}`)**: Galería de imágenes interactiva, selector de cantidades, contador de stock, cálculo de ahorro y sistema de valoraciones (estrellas y comentarios).
- **Carrito Offcanvas & Página Dedicada (`/cart`)**: Carrito lateral desplegable en cualquier pantalla con actualización asíncrona (AJAX) y cupones de descuento.
- **Checkout Optimizado para Colombia (`/checkout`)**:
  - Datos de contacto y **Cédula de Ciudadanía / DNI**.
  - Selección de Departamento y Ciudad colombiana.
  - Selección de transportadora preferida (*Coordinadora, Servientrega, Interrapidísimo, Envía*).
  - Medios de pago nacionales: **PSE**, **Nequi / Daviplata**, **Tarjeta de Crédito / Débito**, **Pago Contra Entrega en Efectivo** y **Transferencia Bancaria**.
- **Comprobante y Seguimiento (`/my-orders`)**: Timeline de estado (*Pendiente ➔ En Preparación ➔ En Tránsito ➔ Entregado*) e impresión de recibo.

---

### 🛠️ Panel de Administración (`/admin`)
- **Dashboard con KPIs & Gráficos**: Métricas de ventas en COP, gráfico de ingresos mensuales con Chart.js, pedidos recientes y alertas de stock.
- **Gestión de Productos (`/admin/products`)**: CRUD completo de productos con imágenes, SKU, precios en COP, stock y categorías.
- **Gestión de Categorías (`/admin/categories`)**: Organización de categorías de la tienda.
- **Gestión de Pedidos (`/admin/orders`)**: Control de órdenes, cambio de estados y detalle del comprador.
- **Generador de Pedidos Manual (`/admin/orders/create`)**: Para ventas de WhatsApp o llamadas con transportadora asignada.
- **Gestión de Clientes (`/admin/customers`)**: Base de datos de compradores con historial de pedidos.
- **Cupones de Descuento (`/admin/coupons`)**: Creación y gestión de cupones por porcentaje o monto fijo.

---

## 🚀 Instalación y Puesta en Marcha Local

### Prerrequisitos
- PHP >= 8.2 (Recomendado 8.3 / 8.4)
- Composer
- Node.js & NPM
- SQLite o MySQL

```bash
# 1. Clonar el repositorio
git clone https://github.com/soyalejandrolopez/novastore-colombia.git
cd novastore-colombia

# 2. Instalar dependencias PHP
composer install

# 3. Instalar y compilar dependencias frontend
npm install && npm run build

# 4. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 5. Ejecutar migraciones y semillero inicial
php artisan migrate --seed

# 6. Iniciar servidor local
php artisan serve
```

---

## 🧪 Pruebas Automatizadas

```bash
php artisan test
```
