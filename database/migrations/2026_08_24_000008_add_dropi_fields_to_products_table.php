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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'dropi_id')) {
                $table->string('dropi_id')->nullable();
            }
            if (!Schema::hasColumn('products', 'dropi_store')) {
                $table->string('dropi_store')->nullable();
            }
            if (!Schema::hasColumn('products', 'is_dropi_product')) {
                $table->boolean('is_dropi_product')->default(false);
            }
            if (!Schema::hasColumn('products', 'is_dropshipping')) {
                $table->boolean('is_dropshipping')->default(false);
            }
            if (!Schema::hasColumn('products', 'wholesale_price')) {
                $table->decimal('wholesale_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'profit_margin')) {
                $table->decimal('profit_margin', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'suggested_price')) {
                $table->decimal('suggested_price', 10, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = ['dropi_id', 'dropi_store', 'is_dropi_product', 'is_dropshipping', 'wholesale_price', 'profit_margin', 'suggested_price'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
