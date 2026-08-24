# 🛍️ NovaStore Colombia — Tienda Virtual en Laravel 12 + Breeze + Bootstrap 5

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Dropi Integration](https://img.shields.io/badge/Dropi.co-Compatible-00C49F?style=for-the-badge)](https://dropi.co)
[![WooCommerce API](https://img.shields.io/badge/WooCommerce%20API-v3%20Emulated-96588A?style=for-the-badge&logo=woocommerce&logoColor=white)](https://woocommerce.com)

Plataforma de comercio electrónico de alto rendimiento desarrollada en **Laravel 12**, interfaz moderna con **Bootstrap 5.3 + Bootstrap Icons**, adaptada completamente al mercado de **Colombia (Pesos COP)**, con integración de **Proveedores / Bodegas**, importador de productos desde **Dropi.co** y **Emulación Nativa de la REST API de WooCommerce (WordPress v3)** para conexión automática con plataformas de dropshipping.

---

## 🌟 Características Principales

### 🛒 Frontend Storefront (Experiencia de Cliente)
- **Inicio Dinámico**: Hero banner con llamados a la acción, categorías destacadas, productos en oferta con cálculo dinámico de ahorro, sección de novedades y más vendidos.
- **Catálogo Multifacético (`/shop`)**: Búsqueda en tiempo real por nombre/SKU, filtros por categoría, rango de precios en COP, disponibilidad en stock y ofertas.
- **Ficha de Producto (`/product/{slug}`)**: Galería de imágenes interactiva, selector de cantidades, contador de stock, cálculo de ahorro y sistema de valoraciones (estrellas y comentarios).
- **Carrito Offcanvas & Página Dedicada (`/cart`)**: Carrito lateral desplegable en cualquier pantalla con actualización asíncrona (AJAX) y cupones de descuento (`DESCUENTO10`, `PROMO20`).
- **Checkout Optimizado para Colombia (`/checkout`)**:
  - Datos de contacto y **Cédula de Ciudadanía / DNI** (requerido para guías de transporte).
  - Selección de Departamento y Ciudad colombiana.
  - Selección de transportadora preferida (*Coordinadora, Servientrega, Interrapidísimo, Envía*).
  - Medios de pago nacionales: **PSE**, **Nequi / Daviplata**, **Tarjeta de Crédito / Débito**, **Pago Contra Entrega en Efectivo** y **Transferencia Bancaria**.
- **Comprobante y Seguimiento (`/my-orders`)**: Timeline de estado (*Pendiente ➔ En Preparación ➔ En Tránsito ➔ Entregado*) e impresión de recibo.

---

### 🛠️ Panel de Administración (`/admin`)
- **Dashboard con KPIs & Gráficos**: Métricas de ventas en COP, gráfico de ingresos mensuales con Chart.js, pedidos recientes y alertas de bajo stock.
- **Proveedores & Bodegas (`/admin/suppliers`)**: Directorio de bodegas aliadas en Medellín, Bogotá y Cali con catálogo mayorista listo para importar.
- **Importador de Catálogo Dropi (`/admin/dropi/catalog`)**:
  - Explorador mayorista con **Calculadora de Ganancia en Vivo** ($ Costo Proveedor ➔ $ Precio Sugerido ➔ Ganancia Neta y % Margen).
  - Importación en 1-clic y masiva con margen personalizado.
- **Generador de Pedidos Manual (`/admin/orders/create`)**: Para ventas de WhatsApp o llamadas; captura datos del comprador (Cédula, dirección, teléfono), genera la orden y la despacha automáticamente a Dropi.
- **Tablero de Despachos Dropi (`/admin/dropi/orders`)**: Control de números de guía (`COORD-XXXXX-CO`) y transportadoras con actualización de tracking.
- **Gestión de Clientes (`/admin/customers`)**: Base de datos de compradores con historial y monto total comprado.
- **Centro de Integraciones (`/admin/integrations`)**:
  - Generador de claves de WooCommerce (**Consumer Key `ck_...` y Consumer Secret `cs_...`**).
  - Configuración de API Dropi (`https://api.dropi.co/api/`).
  - Probador interactivo de endpoints integrado.

---

### 🔌 Emulación Nativa de WooCommerce REST API v3
Dropi (u otras plataformas) pueden conectarse a esta tienda exactamente como si fuera un sitio de WordPress con WooCommerce:

| Endpoint | Método | Descripción |
| :--- | :--- | :--- |
| `/wp-json` | `GET` | Índice WordPress con namespaces `['wp/v2', 'wc/v3', 'dropi/v1']` |
| `/wp-json/wc/v3/system_status` | `GET` | Reporte del sistema simulando WooCommerce 9.0+, WordPress 6.5+ y plugins activos |
| `/wp-json/wc/v3/products` | `GET`, `POST` | Listar y crear productos con esquema JSON estándar de WooCommerce |
| `/wp-json/wc/v3/products/{id}` | `GET`, `PUT`, `DELETE` | Consultar, actualizar stock o eliminar productos |
| `/wp-json/wc/v3/orders` | `GET`, `POST` | Listar y sincronizar pedidos con `billing`, `shipping`, `line_items` y metadatos Dropi |
| `/wp-json/wc/v3/customers` | `GET` | Directorio de clientes |

---

## 🚀 Instalación y Puesta en Marcha Local

### Prerrequisitos
- PHP >= 8.2 (Recomendado PHP 8.3 o 8.4)
- Composer
- Node.js & NPM
- Extensión PHP SQLite / MySQL y cURL habilitadas

### Pasos de Instalación

1. **Clonar el repositorio:**
   ```bash
   git clone <URL_DEL_REPOSITORIO>
   cd tiendavirtual
   ```

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Instalar dependencias de frontend y compilar estilos:**
   ```bash
   npm install
   npm run build
   ```

4. **Configurar el archivo de entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Ejecutar migraciones y poblar la base de datos (Seeder con datos de prueba de Colombia y Dropi):**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Crear enlace simbólico de almacenamiento:**
   ```bash
   php artisan storage:link
   ```

7. **Iniciar el servidor de desarrollo:**
   ```bash
   php artisan serve
   ```
   Abre tu navegador en: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔑 Cuentas de Acceso Preconfiguradas

| Rol | Correo Electrónico | Contraseña | Acceso |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@tienda.com` | `password` | Tienda completa y [Panel Administrativo (/admin)](http://127.0.0.1:8000/admin) |
| **Cliente de Prueba** | `cliente@tienda.com` | `password` | Catálogo, Carrito, Checkout y [Mis Pedidos](/my-orders) |

---

## 🔗 Cómo Conectar Dropi.co con esta Tienda

1. Inicia sesión como administrador y ve a **[Panel > Integraciones WooCommerce](/admin/integrations)**.
2. Haz clic en **Generar Nueva Clave** y copia:
   - **URL de la Tienda:** `https://tudominio.com` (o `http://127.0.0.1:8000` en pruebas locales).
   - **Consumer Key (CK):** `ck_...`
   - **Consumer Secret (CS):** `cs_...`
3. En tu cuenta de **Dropi.co**, ve a **Integraciones > WooCommerce**.
4. Pega la URL de la tienda, el Consumer Key y el Consumer Secret.
5. ¡Listo! Dropi se conectará y comenzará a sincronizar productos y despachos automáticamente.

---

## 🧪 Pruebas Automatizadas

La aplicación cuenta con una suite completa de pruebas unitarias y de integración:
```bash
php artisan test
```
*41 pruebas ejecutadas exitosamente (131 aserciones).*

---

## 📦 Despliegue en Servidores cPanel / Hosting Compartido

Para instrucciones detalladas paso a paso sobre cómo subir y desplegar este proyecto en un hosting con cPanel, consulta la guía dedicada:
👉 **[DEPLOY_CPANEL.md](DEPLOY_CPANEL.md)**

---

## 📄 Licencia
Este proyecto es de código abierto bajo la licencia [MIT](LICENSE).
