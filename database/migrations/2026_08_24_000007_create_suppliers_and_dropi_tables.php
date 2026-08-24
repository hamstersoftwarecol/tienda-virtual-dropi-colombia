<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city');
            $table->string('department')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->string('warehouse_address')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('total_products_count')->default(0);
            $table->timestamps();
        });

        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->string('dropi_id')->nullable()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('sku')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('wholesale_price', 12, 2); // Costo proveedor en COP
            $table->decimal('suggested_price', 12, 2); // Precio sugerido de venta en COP
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->string('category_name')->nullable();
            $table->boolean('is_imported')->default(false);
            $table->unsignedBigInteger('imported_product_id')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('category_id')->constrained('suppliers')->nullOnDelete();
            $table->string('dropi_id')->nullable()->after('sku');
            $table->decimal('wholesale_price', 12, 2)->nullable()->after('price');
            $table->decimal('profit_margin', 12, 2)->nullable()->after('wholesale_price');
            $table->boolean('is_dropshipping')->default(false)->after('is_active');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('recipient_dni')->nullable()->after('customer_phone');
            $table->string('shipping_department')->nullable()->after('shipping_city');
            $table->string('shipping_carrier')->nullable()->default('Coordinadora')->after('shipping_postal_code');
            $table->string('dropi_order_id')->nullable()->after('order_number');
            $table->string('dropi_status')->nullable()->default('unassigned')->after('status');
            $table->string('dropi_guide_number')->nullable()->after('dropi_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('dni')->nullable()->after('phone');
            $table->string('department')->nullable()->after('city');
        });

        Schema::create('woocommerce_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('description');
            $table->string('permissions')->default('read_write'); // read, write, read_write
            $table->string('consumer_key')->unique(); // ck_...
            $table->string('consumer_secret'); // cs_...
            $table->string('truncated_key', 10);
            $table->timestamp('last_access_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('dropi_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_url')->default('https://api.dropi.co/api/');
            $table->string('auth_token')->nullable();
            $table->string('email')->nullable();
            $table->boolean('auto_sync_orders')->default(true);
            $table->integer('default_markup_percent')->default(40);
            $table->string('default_carrier')->default('Coordinadora');
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dropi_settings');
        Schema::dropIfExists('woocommerce_api_keys');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dni', 'department']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_dni',
                'shipping_department',
                'shipping_carrier',
                'dropi_order_id',
                'dropi_status',
                'dropi_guide_number',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'supplier_id',
                'dropi_id',
                'wholesale_price',
                'profit_margin',
                'is_dropshipping',
            ]);
        });

        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('suppliers');
    }
};
