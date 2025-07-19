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
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('model')->unique();
            $table->float('price', 2)->default(0);
            $table->float('b2b_price', 2)->default(0);
            $table->float('sale_price', 2)->default(0);
            $table->foreignId('color_id')->nullable();
            $table->foreignId('team_id')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->jsonb('images')->nullable();
            $table->text('description')->nullable();
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
