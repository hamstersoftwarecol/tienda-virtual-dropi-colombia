<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DropiProductController as AdminDropiProductController;
use App\Http\Controllers\Admin\DropiSettingController as AdminDropiSettingController;
use App\Http\Controllers\Admin\ManualOrderController as AdminManualOrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
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

    // Coupons Management
    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->name('coupons.store');
    Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');

    // Dropi Products & Importer
    Route::get('/dropi/products', [AdminDropiProductController::class, 'index'])->name('dropi.products.index');
    Route::post('/dropi/products/import', [AdminDropiProductController::class, 'import'])->name('dropi.products.import');

    // Dropi Settings & Token Validation
    Route::get('/dropi/settings', [AdminDropiSettingController::class, 'index'])->name('dropi.settings');
    Route::post('/dropi/settings', [AdminDropiSettingController::class, 'store'])->name('dropi.settings.store');
    Route::post('/dropi/settings/validate', [AdminDropiSettingController::class, 'validateToken'])->name('dropi.settings.validate');
    Route::delete('/dropi/settings/{dropiToken}', [AdminDropiSettingController::class, 'destroy'])->name('dropi.settings.destroy');
});

// Breeze Auth Routes
require __DIR__.'/auth.php';
