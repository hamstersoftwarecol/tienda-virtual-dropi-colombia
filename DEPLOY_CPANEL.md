# 🚀 Guía de Despliegue en Servidores cPanel (Hosting Compartido)

Esta guía explica paso a paso cómo subir, configurar y poner en producción **NovaStore Colombia** en cualquier cuenta de **cPanel** (con o sin acceso a terminal SSH).

---

## 📁 Estructura Recomendada de Archivos en cPanel

En servidores compartidos de cPanel, la carpeta pública accesible por internet es `public_html/`. Por motivos de seguridad y arquitectura estándar de Laravel, los archivos del núcleo del framework **NUNCA** deben estar expuestos directamente en la raíz web.

### Estructura en el Servidor:
```text
/home/tu_usuario/
├── tiendavirtual/               <-- Código fuente de Laravel (Privado y Seguro)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env                    <-- Configuración de Producción
│   └── artisan
│
└── public_html/                <-- Contenido de la carpeta "public/" de Laravel
    ├── build/                  <-- Assets CSS y JS compilados por Vite
    ├── index.php               <-- Punto de entrada configurado
    ├── .htaccess               <-- Reglas de redirección y cabeceras de autorización
    ├── robots.txt
    └── storage/                <-- Enlace simbólico hacia ../tiendavirtual/storage/app/public
```

---

## 🛠️ Paso a Paso para el Despliegue

### Paso 1: Preparar el archivo ZIP del proyecto
En tu computadora local:
1. Asegúrate de tener los assets compilados para producción:
   ```bash
   npm run build
   ```
2. Comprime en un archivo `.zip` todos los archivos y carpetas del proyecto, **EXCLUYENDO** `node_modules` y `.git` (la carpeta `public/build` y `vendor` sí deben incluirse si tu cPanel no tiene Composer).

---

### Paso 2: Subir y extraer los archivos en cPanel
1. Inicia sesión en tu cuenta de **cPanel**.
2. Abre el **Administrador de Archivos (File Manager)**.
3. En la raíz de tu cuenta (`/home/tu_usuario/`), crea una carpeta llamada `tiendavirtual`.
4. Sube el archivo `.zip` dentro de `tiendavirtual/` y haz clic en **Extraer (Extract)**.
5. Mueve todo el contenido de la carpeta `tiendavirtual/public/` directamente dentro de tu carpeta `public_html/`.

---

### Paso 3: Ajustar las rutas en `public_html/index.php`
Abre para editar el archivo `public_html/index.php` y actualiza las rutas para que apunten a la carpeta `tiendavirtual`:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determinar si la aplicación está en modo de mantenimiento
if (file_exists($maintenance = __DIR__.'/../tiendavirtual/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Registrar el cargador automático de Composer
require __DIR__.'/../tiendavirtual/vendor/autoload.php';

// Inicializar la aplicación Laravel
(require_once __DIR__.'/../tiendavirtual/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

---

### Paso 4: Crear la Base de Datos MySQL en cPanel
1. En cPanel, dirígete a **Bases de Datos MySQL** o **Asistente de Bases de Datos MySQL**.
2. Crea una nueva base de datos (ejemplo: `usuario_tiendavirtual`).
3. Crea un usuario de base de datos con una contraseña segura (ejemplo: `usuario_admin`).
4. Asocia el usuario a la base de datos y concédele **Todos los Privilegios (ALL PRIVILEGES)**.

---

### Paso 5: Configurar el archivo `.env` de Producción
1. En `tiendavirtual/`, crea o edita el archivo `.env`:
```env
APP_NAME="NovaStore Colombia"
APP_ENV=production
APP_KEY=base64:TU_APP_KEY_AQUI
APP_DEBUG=false
APP_URL=https://tudominio.com

APP_TIMEZONE="America/Bogota"
APP_LOCALE=es

# Conexión a la base de datos MySQL de cPanel
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=usuario_tiendavirtual
DB_USERNAME=usuario_admin
DB_PASSWORD=TuPasswordSeguro123!

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

# Configuración de Correo SMTP cPanel
MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=465
MAIL_USERNAME=contacto@tudominio.com
MAIL_PASSWORD=TuPasswordDeCorreo
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="contacto@tudominio.com"
MAIL_FROM_NAME="${APP_NAME}"

# Configuración Dropi
DROPI_API_URL="https://api.dropi.co/api/"
DROPI_AUTH_TOKEN=
DROPI_DEFAULT_CARRIER="Coordinadora"
```

---

### Paso 6: Ejecutar Migraciones y Crear Enlace Simbólico de Storage

#### Opción A: Si tienes acceso a la Terminal de cPanel (Recomendado)
Abre la **Terminal** en cPanel y ejecuta:
```bash
cd /home/tu_usuario/tiendavirtual

# Generar clave si no existe
php artisan key:generate --force

# Ejecutar migraciones y poblar datos iniciales (Proveedores, Dropi y Usuarios)
php artisan migrate --force --seed

# Crear enlace simbólico para imágenes
php artisan storage:link

# Optimizar caché de rutas, vistas y configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Opción B: Si tu hosting NO tiene Terminal SSH
Puedes usar el script auxiliar incluido `public_html/cpanel-setup.php` ingresando a:
`https://tudominio.com/cpanel-setup.php?token=SetupNovaStore2026`
*(Una vez ejecutado, elimina o renombra este archivo por seguridad).*

---

### Paso 7: Configuración de `.htaccess` para WooCommerce REST API (Dropi)
Asegúrate de que `public_html/.htaccess` contenga las directivas de autorización para que Apache no bloquee las credenciales `ck_...` y `cs_...` enviadas por Dropi:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Habilitar cabeceras de autorización para WooCommerce REST API
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

    # Redirigir a HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Enviar peticiones al Front Controller de Laravel
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

### Paso 8: Configurar Tareas Programadas (Cron Jobs en cPanel)
Para que las órdenes automáticas y la sincronización en segundo plano funcionen:
1. En cPanel, abre **Tareas Cron (Cron Jobs)**.
2. Añade un nuevo trabajo cron configurado para ejecutarse **Cada minuto (`* * * * *`)**:
   ```bash
   /usr/local/bin/php /home/tu_usuario/tiendavirtual/artisan schedule:run >> /dev/null 2>&1
   ```
   *(Ajusta la ruta de PHP según la versión configurada en tu hosting, ej: `/usr/bin/php` o `/opt/cpanel/ea-php83/root/usr/bin/php`).*

---

## 🔒 Checklist de Seguridad en Producción
- [x] `APP_DEBUG=false` en el archivo `.env`.
- [x] Certificado SSL / HTTPS activo en cPanel.
- [x] Permisos de escritura configurados en `storage/` y `bootstrap/cache/` (`775` o `755`).
- [x] Contraseña del usuario administrador `admin@tienda.com` cambiada en producción.
