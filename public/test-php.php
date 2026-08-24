<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico PHP - NovaStore</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f8fafc; padding: 40px 20px; color: #0f172a; }
        .card { max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .ver { font-size: 24px; font-weight: bold; padding: 8px 16px; border-radius: 8px; font-family: monospace; display: inline-block; }
        .bad { background: #fee2e2; color: #991b1b; }
        .good { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🔍 Versión de PHP que está usando tu Subdominio:</h2>
        <div style="margin: 20px 0;">
            <span class="ver <?= version_compare(PHP_VERSION, '8.2.0', '>=') ? 'good' : 'bad' ?>">
                PHP <?= PHP_VERSION ?>
            </span>
        </div>
        
        <?php if (version_compare(PHP_VERSION, '8.2.0', '<')): ?>
            <p style="color: #e11d48; font-weight: bold;">
                ❌ Tu servidor web está corriendo con una versión antigua (PHP <?= PHP_VERSION ?>). Por eso Symfony/Laravel 12 no puede iniciar.
            </p>
            <p>
                Debes ir a <strong>cPanel &rarr; MultiPHP Manager (o Select PHP Version)</strong> y cambiar <code>tienda.hamstersoftware.com</code> a <strong>PHP 8.2</strong> o <strong>PHP 8.3</strong>.
            </p>
        <?php else: ?>
            <p style="color: #16a34a; font-weight: bold;">
                ✅ ¡La versión de PHP es correcta (<?= PHP_VERSION ?>)!
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
