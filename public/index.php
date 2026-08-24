<?php

define('LARAVEL_START', microtime(true));

// Strict PHP Version Pre-Check (Before loading any PHP 8.2+ class syntax)
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Versión de PHP Incompatible - NovaStore</title>
        <style>
            body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; padding: 40px 20px; color: #1e293b; }
            .card { max-width: 650px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 30px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
            h2 { color: #e11d48; margin-top: 0; }
            .badge { display: inline-block; padding: 4px 10px; background: #fee2e2; color: #991b1b; border-radius: 6px; font-weight: bold; font-family: monospace; }
            .badge-success { background: #dcfce7; color: #166534; }
            ol { line-height: 1.8; padding-left: 20px; }
            .box { background: #f1f5f9; padding: 15px; border-radius: 8px; font-size: 14px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>⚠️ Versión de PHP Incompatible en cPanel</h2>
            <p>Tu subdominio está ejecutándose actualmente con <span class="badge">PHP <?= PHP_VERSION ?></span>.</p>
            <p><strong>Laravel 12</strong> requiere como mínimo <span class="badge badge-success">PHP 8.2</span> o <span class="badge badge-success">PHP 8.3</span>.</p>
            
            <h4>Cómo cambiar la versión en cPanel (en 30 segundos):</h4>
            <ol>
                <li>Entra a tu <strong>cPanel</strong>.</li>
                <li>Busca y abre <strong>Administrador MultiPHP (MultiPHP Manager)</strong>.</li>
                <li>Marca la casilla al lado de <strong>tienda.hamstersoftware.com</strong>.</li>
                <li>En el menú desplegable superior derecho selecciona <strong>ea-php83</strong> (o <code>ea-php82</code>) y haz clic en <strong>Aplicar</strong>.</li>
            </ol>
            <div class="box">
                <em>Si tu servidor usa CloudLinux en vez de MultiPHP, busca <strong>Select PHP Version (Seleccionar Versión de PHP)</strong> y cámbialo a <strong>8.2</strong> u <strong>8.3</strong>.</em>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit(1);
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Auto-detect Laravel root directory
$possibleRoots = [
    __DIR__,
    __DIR__ . '/..',
    __DIR__ . '/resource',
    __DIR__ . '/../resource',
    __DIR__ . '/../tiendavirtual',
    __DIR__ . '/../novastore-colombia',
    __DIR__ . '/../tienda',
    __DIR__ . '/../tienda.hamstersoftware.com',
];

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
    echo '<div style="font-family: system-ui, sans-serif; max-width: 650px; margin: 50px auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff;">';
    echo '<h2 style="color: #e11d48; margin-top: 0;">⚠️ No se encontró el código de Laravel</h2>';
    echo '<p>No se localizó <code>bootstrap/app.php</code> o la carpeta <code>vendor</code>.</p>';
    echo '</div>';
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
    echo '<div style="font-family: system-ui, sans-serif; max-width: 750px; margin: 40px auto; padding: 24px; border: 1px solid #fecdd3; border-radius: 12px; background: #fff;">';
    echo '<h2 style="color: #e11d48; margin-top: 0;">⚠️ Error 500 al Iniciar Laravel</h2>';
    echo '<p style="color: #334155; font-size: 15px; background: #fff1f2; padding: 12px; border-radius: 8px; font-weight: 600;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p style="color: #64748b; font-size: 13px;"><strong>Archivo:</strong> <code>' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</code></p>';
    echo '</div>';
    exit(1);
}
