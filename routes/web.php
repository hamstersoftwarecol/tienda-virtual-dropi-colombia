<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DropiCatalogController as AdminDropiCatalogController;
use App\Http\Controllers\Admin\DropiOrderController as AdminDropiOrderController;
use App\Http\Controllers\Admin\IntegrationController as AdminIntegrationController;
use App\Http\Controllers\Admin\ManualOrderController as AdminManualOrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Api\WooCommerce\CustomerController as WcCustomerController;
use App\Http\Controllers\Api\WooCommerce\OrderController as WcOrderController;
use App\Http\Controllers\Api\WooCommerce\ProductController as WcProductController;
use App\Http\Controllers\Api\WooCommerce\SystemController as WcSystemController;
use App\Http\Controllers\Api\WooCommerce\WebhookController as WcWebhookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Middleware\WooCommerceAuthMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Tienda Virtual
|--------------------------------------------------------------------------
*/

// Public Storefront Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/product/{slug}', [ShopController::class, 'show'])->name('shop.show');

// Shopping Cart Routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/coupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::get('/mini', [CartController::class, 'getMiniCart'])->name('mini');
});

// Checkout Routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    Route::get('/success/{orderNumber}', [CheckoutController::class, 'success'])->name('success');
});

// Authenticated Customer Routes
Route::middleware('auth')->group(function () {
    // Orders
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');

    // Reviews
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Panel Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Products Management
    Route::resource('products', AdminProductController::class)->names('products');

    // Categories Management
    Route::resource('categories', AdminCategoryController::class)->except(['create', 'show', 'edit'])->names('categories');

    // Orders Management & Manual Order Generator
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [AdminManualOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders/create', [AdminManualOrderController::class, 'store'])->name('orders.store.manual');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    // Customers Management
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [AdminCustomerController::class, 'store'])->name('customers.store');

    // Dropi Catalog Importer & Dropi Orders
    Route::get('/dropi/catalog', [AdminDropiCatalogController::class, 'index'])->name('dropi.catalog');
    Route::post('/dropi/catalog/sync-api', [AdminDropiCatalogController::class, 'syncApi'])->name('dropi.catalog.sync_api');
    Route::post('/dropi/catalog/{supplierProduct}/sync', [AdminDropiCatalogController::class, 'syncProduct'])->name('dropi.catalog.sync_product');
    Route::post('/dropi/catalog/import/{id}', [AdminDropiCatalogController::class, 'import'])->name('dropi.catalog.import');
    Route::post('/dropi/catalog/import-custom', [AdminDropiCatalogController::class, 'importCustom'])->name('dropi.catalog.import_custom');
    Route::post('/dropi/catalog/bulk-import', [AdminDropiCatalogController::class, 'bulkImport'])->name('dropi.catalog.bulk');
    Route::post('/dropi/catalog/import-all', [AdminDropiCatalogController::class, 'importAll'])->name('dropi.catalog.import_all');
    Route::get('/dropi/orders', [AdminDropiOrderController::class, 'index'])->name('dropi.orders');
    Route::post('/dropi/orders/{order}/dispatch', [AdminDropiOrderController::class, 'dispatch'])->name('dropi.orders.dispatch');
    Route::post('/dropi/orders/{order}/sync', [AdminDropiOrderController::class, 'syncTracking'])->name('dropi.orders.sync');

    // Integrations & WooCommerce API Keys
    Route::get('/integrations', [AdminIntegrationController::class, 'index'])->name('integrations.index');
    Route::post('/integrations/woocommerce/keys', [AdminIntegrationController::class, 'generateWooCommerceKey'])->name('integrations.wc.keys.generate');
    Route::delete('/integrations/woocommerce/keys/{key}', [AdminIntegrationController::class, 'revokeWooCommerceKey'])->name('integrations.wc.keys.revoke');
    Route::post('/integrations/dropi/settings', [AdminIntegrationController::class, 'updateDropiSettings'])->name('integrations.dropi.settings');
    Route::post('/integrations/dropi/test-connection', [AdminIntegrationController::class, 'testDropiConnection'])->name('integrations.dropi.test');

    // Coupons Management
    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->name('coupons.store');
    Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');
});

/*
|--------------------------------------------------------------------------
| WooCommerce REST API Emulation Engine (/wp-json/wc/v3/...)
| Enables Dropi (and any other tool) to connect seamlessly to NovaStore
|--------------------------------------------------------------------------
*/
Route::middleware([WooCommerceAuthMiddleware::class])->group(function () {
    // WordPress Index
    Route::get('/wp-json', [WcSystemController::class, 'index']);
    Route::get('/wp-json/wc/v3', [WcSystemController::class, 'wcIndex']);
    Route::get('/wp-json/wc/v3/system_status', [WcSystemController::class, 'systemStatus']);

    // WooCommerce Products
    Route::get('/wp-json/wc/v3/products', [WcProductController::class, 'index']);
    Route::get('/wp-json/wc/v3/products/{id}', [WcProductController::class, 'show']);
    Route::post('/wp-json/wc/v3/products', [WcProductController::class, 'store']);
    Route::put('/wp-json/wc/v3/products/{id}', [WcProductController::class, 'update']);
    Route::patch('/wp-json/wc/v3/products/{id}', [WcProductController::class, 'update']);
    Route::delete('/wp-json/wc/v3/products/{id}', [WcProductController::class, 'destroy']);

    // WooCommerce Orders
    Route::get('/wp-json/wc/v3/orders', [WcOrderController::class, 'index']);
    Route::get('/wp-json/wc/v3/orders/{id}', [WcOrderController::class, 'show']);
    Route::post('/wp-json/wc/v3/orders', [WcOrderController::class, 'store']);
    Route::put('/wp-json/wc/v3/orders/{id}', [WcOrderController::class, 'update']);
    Route::patch('/wp-json/wc/v3/orders/{id}', [WcOrderController::class, 'update']);

    // WooCommerce Customers
    Route::get('/wp-json/wc/v3/customers', [WcCustomerController::class, 'index']);

    // WooCommerce Webhooks
    Route::get('/wp-json/wc/v3/webhooks', [WcWebhookController::class, 'index']);
    Route::post('/wp-json/wc/v3/webhooks', [WcWebhookController::class, 'store']);
});

// Breeze Auth Routes
require __DIR__.'/auth.php';
