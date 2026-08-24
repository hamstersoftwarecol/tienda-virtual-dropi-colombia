<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Auto-detect Laravel root directory (Local, Standard or cPanel folder structure)
$possibleRoots = [
    __DIR__ . '/..',
    __DIR__ . '/../tiendavirtual',
    __DIR__ . '/../novastore-colombia',
];

$baseDir = null;
foreach ($possibleRoots as $dir) {
    if (file_exists($dir . '/bootstrap/app.php') && file_exists($dir . '/vendor/autoload.php')) {
        $baseDir = realpath($dir);
        break;
    }
}

if (!$baseDir) {
    die('<h1>Error de Configuración</h1><p>No se pudo localizar el directorio principal de Laravel o la carpeta <code>vendor</code>. Asegúrate de haber subido la carpeta <code>vendor</code> a tu servidor.</p>');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $baseDir . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $baseDir . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $baseDir . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
