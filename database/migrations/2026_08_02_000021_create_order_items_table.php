<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignUuid('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

            // Snapshot fields — product/variant may change or be soft-disabled later.
            $table->string('name_snapshot');
            $table->string('sku_snapshot');
            $table->string('variant_snapshot')->nullable();
            $table->decimal('original_price_snapshot', 15, 2);
            $table->decimal('discount_snapshot', 15, 2)->default(0);
            $table->decimal('final_price_snapshot', 15, 2);
            $table->unsignedInteger('qty');
            $table->decimal('subtotal_snapshot', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
