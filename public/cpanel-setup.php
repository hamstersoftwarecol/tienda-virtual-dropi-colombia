<?php
/**
 * Script de Asistencia para Despliegue en cPanel sin Acceso SSH
 * 
 * Uso: https://tudominio.com/cpanel-setup.php?token=SetupNovaStore2026&action=migrate_seed
 * 
 * Acciones disponibles:
 * - action=status         (Verifica conexión a base de datos y versión de PHP)
 * - action=migrate        (Ejecuta migraciones)
 * - action=migrate_seed   (Ejecuta migraciones y puebla datos iniciales)
 * - action=storage_link   (Crea el enlace simbólico de almacenamiento)
 * - action=optimize       (Limpia y optimiza la caché de Laravel)
 * 
 * ¡IMPORTANTE! Elimina o renombra este archivo después de completar la instalación inicial.
 */

// Token de seguridad requerido en la URL (?token=...)
$securityToken = 'SetupNovaStore2026';

if (!isset($_GET['token']) || $_GET['token'] !== $securityToken) {
    http_response_code(403);
    die('<h1>403 Acceso Denegado</h1><p>El token de seguridad proporcionado es inválido.</p>');
}

// Cargar el entorno de Laravel dinámicamente
$possibleRoots = [
    __DIR__,
    __DIR__ . '/..',
    __DIR__ . '/../tiendavirtual',
    __DIR__ . '/../novastore-colombia',
    __DIR__ . '/../tienda',
    __DIR__ . '/../tienda.hamstersoftware.com',
];

if ($parentDirs = glob(__DIR__ . '/../*', GLOB_ONLYDIR)) {
    $possibleRoots = array_merge($possibleRoots, $parentDirs);
}

$app = null;
$baseDir = null;
foreach ($possibleRoots as $dir) {
    if (file_exists($dir . '/bootstrap/app.php') && file_exists($dir . '/vendor/autoload.php')) {
        $baseDir = realpath($dir);
        require_once $baseDir . '/vendor/autoload.php';
        $app = require_once $baseDir . '/bootstrap/app.php';
        break;
    }
}

if (!$app) {
    die('<h1>Error</h1><p>No se pudo localizar el archivo bootstrap/app.php o vendor/autoload.php de Laravel. Verifica que la carpeta vendor esté subida.</p>');
}

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$action = $_GET['action'] ?? 'status';
$output = '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asistente de Configuración cPanel - NovaStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width: 750px;">
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h3 class="fw-bold text-dark mb-1">🛠️ Asistente de Configuración cPanel</h3>
        <p class="text-muted small mb-4">NovaStore Colombia — Utilidad para hosting compartido sin acceso a terminal SSH.</p>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="?token=<?= htmlspecialchars($securityToken) ?>&action=status" class="btn btn-outline-secondary btn-sm rounded-pill">Ver Estado</a>
            <a href="?token=<?= htmlspecialchars($securityToken) ?>&action=migrate" class="btn btn-outline-primary btn-sm rounded-pill">Ejecutar Migraciones</a>
            <a href="?token=<?= htmlspecialchars($securityToken) ?>&action=migrate_seed" class="btn btn-primary btn-sm rounded-pill">Migrar y Poblar Datos (Seed)</a>
            <a href="?token=<?= htmlspecialchars($securityToken) ?>&action=storage_link" class="btn btn-outline-info btn-sm rounded-pill">Crear Storage Link</a>
            <a href="?token=<?= htmlspecialchars($securityToken) ?>&action=optimize" class="btn btn-outline-success btn-sm rounded-pill">Optimizar Caché</a>
        </div>

        <div class="bg-dark text-light p-3 rounded-3 font-monospace small" style="min-height: 120px;">
            <?php
            try {
                switch ($action) {
                    case 'migrate':
                        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                        echo nl2br(\Illuminate\Support\Facades\Artisan::output());
                        break;

                    case 'migrate_seed':
                        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
                        echo nl2br(\Illuminate\Support\Facades\Artisan::output());
                        break;

                    case 'storage_link':
                        \Illuminate\Support\Facades\Artisan::call('storage:link');
                        echo nl2br(\Illuminate\Support\Facades\Artisan::output());
                        break;

                    case 'optimize':
                        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
                        echo nl2br(\Illuminate\Support\Facades\Artisan::output());
                        \Illuminate\Support\Facades\Artisan::call('config:cache');
                        echo nl2br(\Illuminate\Support\Facades\Artisan::output());
                        \Illuminate\Support\Facades\Artisan::call('route:cache');
                        echo nl2br(\Illuminate\Support\Facades\Artisan::output());
                        \Illuminate\Support\Facades\Artisan::call('view:cache');
                        echo nl2br(\Illuminate\Support\Facades\Artisan::output());
                        break;

                    case 'status':
                    default:
                        echo "PHP Version: " . phpversion() . "\n";
                        echo "Laravel Version: " . app()->version() . "\n";
                        echo "Environment: " . app()->environment() . "\n";
                        try {
                            \Illuminate\Support\Facades\DB::connection()->getPdo();
                            echo "Database Status: Conexión Exitosa (" . \Illuminate\Support\Facades\DB::connection()->getDatabaseName() . ")\n";
                        } catch (\Exception $dbEx) {
                            echo "Database Status: Error de conexión (" . $dbEx->getMessage() . ")\n";
                        }
                        break;
                }
            } catch (\Exception $e) {
                echo "<span class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
            }
            ?>
        </div>

        <div class="alert alert-warning mt-4 mb-0 rounded-3 small">
            <strong>⚠️ Recordatorio de Seguridad:</strong> Una vez hayas completado la instalación y configuración inicial, elimina este archivo (<code>cpanel-setup.php</code>) de tu servidor.
        </div>
    </div>
</div>
</body>
</html>
