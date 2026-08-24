<?php

namespace App\Http\Controllers\Api\WooCommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    /**
     * Emulates standard WordPress REST API index at /wp-json/
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name', 'NovaStore Colombia'),
            'description' => 'Tienda Virtual WooCommerce en Colombia',
            'url' => url('/'),
            'home' => url('/'),
            'gmt_offset' => -5,
            'timezone_string' => 'America/Bogota',
            'namespaces' => [
                'wp/v2',
                'wc/v3',
                'wc/v2',
                'wc/v1',
                'dropi/v1',
            ],
            'authentication' => [
                'oauth1' => [
                    'request' => url('/oauth1/request'),
                    'authorize' => url('/oauth1/authorize'),
                    'access' => url('/oauth1/access'),
                ],
            ],
            'routes' => [
                '/wp-json' => [
                    'namespace' => '',
                    'methods' => ['GET'],
                ],
                '/wp-json/wc/v3' => [
                    'namespace' => 'wc/v3',
                    'methods' => ['GET'],
                ],
                '/wp-json/wc/v3/system_status' => [
                    'namespace' => 'wc/v3',
                    'methods' => ['GET'],
                ],
                '/wp-json/wc/v3/products' => [
                    'namespace' => 'wc/v3',
                    'methods' => ['GET', 'POST'],
                ],
                '/wp-json/wc/v3/orders' => [
                    'namespace' => 'wc/v3',
                    'methods' => ['GET', 'POST'],
                ],
                '/wp-json/wc/v3/customers' => [
                    'namespace' => 'wc/v3',
                    'methods' => ['GET', 'POST'],
                ],
                '/wp-json/wc/v3/webhooks' => [
                    'namespace' => 'wc/v3',
                    'methods' => ['GET', 'POST'],
                ],
            ],
            '_links' => [
                'help' => [
                    ['href' => 'https://developer.woocommerce.com/docs/rest-api/'],
                ],
            ],
        ]);
    }

    /**
     * Emulates WooCommerce v3 namespace index at /wp-json/wc/v3
     */
    public function wcIndex(): JsonResponse
    {
        return response()->json([
            'namespace' => 'wc/v3',
            'routes' => [
                '/wp-json/wc/v3' => ['methods' => ['GET']],
                '/wp-json/wc/v3/products' => ['methods' => ['GET', 'POST']],
                '/wp-json/wc/v3/products/(?P<id>[\\d]+)' => ['methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']],
                '/wp-json/wc/v3/orders' => ['methods' => ['GET', 'POST']],
                '/wp-json/wc/v3/orders/(?P<id>[\\d]+)' => ['methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']],
                '/wp-json/wc/v3/customers' => ['methods' => ['GET', 'POST']],
                '/wp-json/wc/v3/system_status' => ['methods' => ['GET']],
            ],
        ]);
    }

    /**
     * Emulates WooCommerce System Status report at /wp-json/wc/v3/system_status
     */
    public function systemStatus(): JsonResponse
    {
        return response()->json([
            'environment' => [
                'home_url' => url('/'),
                'site_url' => url('/'),
                'version' => '9.0.2',
                'log_directory' => storage_path('logs'),
                'log_directory_writable' => true,
                'wp_version' => '6.5.4',
                'wp_multisite' => false,
                'wp_memory_limit' => 268435456,
                'wp_debug_mode' => false,
                'wp_cron' => true,
                'language' => 'es_CO',
                'server_info' => 'Laravel ' . app()->version() . ' (WooCommerce Compatibility Engine)',
                'php_version' => PHP_VERSION,
                'php_post_max_size' => 67108864,
                'php_max_execution_time' => 300,
                'php_max_input_vars' => 10000,
                'curl_version' => '7.88.1',
                'suhosin_installed' => false,
                'max_upload_size' => 67108864,
                'mysql_version' => '8.0.36',
                'default_timezone' => 'America/Bogota',
                'fsockopen_or_curl_enabled' => true,
                'soapclient_enabled' => true,
                'domdocument_enabled' => true,
                'gzip_compression_enabled' => true,
                'mbstring_enabled' => true,
            ],
            'database' => [
                'wc_database_version' => '9.0.2',
                'database_prefix' => 'wp_',
                'maxmind_geoip_database' => '',
            ],
            'active_plugins' => [
                [
                    'plugin' => 'woocommerce/woocommerce.php',
                    'name' => 'WooCommerce',
                    'version' => '9.0.2',
                    'url' => 'https://woocommerce.com/',
                    'author_name' => 'Automattic',
                    'author_url' => 'https://woocommerce.com',
                ],
                [
                    'plugin' => 'dropi-woocommerce/dropi.php',
                    'name' => 'Dropi Dropshipping Colombia',
                    'version' => '2.1.0',
                    'url' => 'https://dropi.co',
                    'author_name' => 'Dropi Latam',
                    'author_url' => 'https://dropi.co',
                ],
            ],
            'theme' => [
                'name' => 'NovaStore Bootstrap 5 Theme',
                'version' => '1.0.0',
                'author_url' => url('/'),
                'is_child_theme' => false,
                'has_woocommerce_support' => true,
            ],
            'settings' => [
                'api_enabled' => true,
                'force_ssl' => false,
                'currency' => 'COP',
                'currency_symbol' => '$',
                'currency_position' => 'left_space',
                'thousand_separator' => '.',
                'decimal_separator' => ',',
                'number_of_decimals' => 0,
                'geolocation_enabled' => true,
                'taxonomies' => [
                    'product_cat' => 'Categorías de Producto',
                ],
                'product_visibility_terms' => [],
            ],
            'security' => [
                'secure_connection' => true,
                'hide_errors' => true,
            ],
            'pages' => [
                'shop' => url('/shop'),
                'cart' => url('/cart'),
                'checkout' => url('/checkout'),
                'myaccount' => url('/orders'),
            ],
        ]);
    }
}
