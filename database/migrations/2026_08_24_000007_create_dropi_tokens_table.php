<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dropi_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('store')->default('Tienda Principal');
            $table->text('token');
            $table->string('sync', 50)->default('AUTOMÁTICAMENTE'); // AUTOMÁTICAMENTE / MANUALMENTE
            $table->boolean('create_prod_empr')->default(true);
            $table->string('api_url')->default('https://api.dropi.co/api/');
            $table->string('user_id_dropi')->nullable();
            $table->string('integration_type')->nullable();
            $table->string('integration_url')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dropi_tokens');
    }
};
