<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->decimal('profit_margin', 10, 2)->nullable();
            $table->decimal('suggested_price', 10, 2)->nullable();
            $table->integer('stock')->default(10);
            $table->string('image')->nullable();
            $table->json('images')->nullable();
            $table->string('badge')->nullable(); // e.g., "Nuevo", "Oferta", "Más Vendido"
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('reviews_count')->default(0);
            $table->integer('sales_count')->default(0);
            $table->string('dropi_id')->nullable();
            $table->string('dropi_store')->nullable();
            $table->boolean('is_dropi_product')->default(false);
            $table->boolean('is_dropshipping')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
