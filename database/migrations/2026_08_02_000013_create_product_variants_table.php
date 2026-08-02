<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            // Denormalized from products.store_id purely to allow a per-store unique SKU index below.
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('sku');
            $table->decimal('price', 15, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('weight_grams')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['store_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
