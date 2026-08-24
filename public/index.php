<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Auto-detect Laravel root directory
$possibleRoots = [
    __DIR__,
    __DIR__ . '/..',
    __DIR__ . '/../tiendavirtual',
    __DIR__ . '/../novastore-colombia',
    __DIR__ . '/../tienda',
    __DIR__ . '/../tienda.hamstersoftware.com',
];

// Scan parent directory folders dynamically if not in predefined list
if ($parentDirs = glob(__DIR__ . '/../*', GLOB_ONLYDIR)) {
    $possibleRoots = array_merge($possibleRoots, $parentDirs);
}

$baseDir = null;
foreach ($possibleRoots as $dir) {
    if (file_exists($dir . '/bootstrap/app.php') && file_exists($dir . '/vendor/autoload.php')) {
        $baseDir = realpath($dir);
        break;
    }
}

if (!$baseDir) {
    http_response_code(500);
    echo '<div style="font-family: system-ui, sans-serif; max-width: 650px; margin: 50px auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">';
    echo '<h2 style="color: #e11d48; margin-top: 0;">⚠️ No se encontró la carpeta base de Laravel</h2>';
    echo '<p style="color: #475569; font-size: 15px;">No se localizó el archivo <code>bootstrap/app.php</code> o la carpeta <code>vendor/autoload.php</code>.</p>';
    echo '<p style="color: #475569; font-size: 14px;"><strong>Ruta actual evaluada:</strong> <code>' . htmlspecialchars(__DIR__) . '</code></p>';
    echo '<div style="background: #f8fafc; padding: 14px; border-radius: 8px; border-left: 4px solid #3b82f6; font-size: 13px; color: #334155;">';
    echo '<strong>Solución:</strong> Asegúrate de que la carpeta <code>vendor</code> y el código fuente estén subidos en tu cuenta de cPanel.';
    echo '</div></div>';
    exit(1);
}

// Check .env existence
if (!file_exists($baseDir . '/.env') && file_exists($baseDir . '/.env.example')) {
    copy($baseDir . '/.env.example', $baseDir . '/.env');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $baseDir . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $baseDir . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
try {
    /** @var Application $app */
    $app = require_once $baseDir . '/bootstrap/app.php';
    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<div style="font-family: system-ui, sans-serif; max-width: 750px; margin: 40px auto; padding: 24px; border: 1px solid #fecdd3; border-radius: 12px; background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">';
    echo '<h2 style="color: #e11d48; margin-top: 0;">⚠️ Error 500 al Iniciar Laravel</h2>';
    echo '<p style="color: #334155; font-size: 15px; background: #fff1f2; padding: 12px; border-radius: 8px; font-weight: 600;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p style="color: #64748b; font-size: 13px;"><strong>Archivo:</strong> <code>' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</code></p>';
    echo '<hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">';
    echo '<h4 style="color: #0f172a; margin-bottom: 8px;">Posibles causas habituales en cPanel:</h4>';
    echo '<ol style="color: #475569; font-size: 14px; line-height: 1.6;">';
    echo '<li><strong>Versión de PHP:</strong> Laravel 12 requiere <strong>PHP >= 8.2</strong>. Ve a <em>MultiPHP Manager</em> en cPanel y selecciona PHP 8.2 u 8.3.</li>';
    echo '<li><strong>Base de datos no configurada:</strong> Edita el archivo <code>.env</code> con las credenciales de tu base de datos MySQL de cPanel.</li>';
    echo '<li><strong>Falta la clave de cifrado:</strong> Ejecuta <code>php artisan key:generate</code> o usa el <a href="cpanel-setup.php?token=SetupNovaStore2026&action=status">Asistente Web cPanel</a>.</li>';
    echo '<li><strong>Permisos de escritura:</strong> Asegúrate de que las carpetas <code>storage/</code> y <code>bootstrap/cache/</code> tengan permisos <strong>775</strong> o <strong>755</strong>.</li>';
    echo '</ol>';
    echo '</div>';
    exit(1);
}
