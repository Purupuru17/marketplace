<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('slug_unique')->storedAs("CASE WHEN deleted_at IS NULL THEN slug ELSE CONCAT(slug, '#', id) END");
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('store_id', 'products_store_id_index');
            $table->unique(['store_id', 'slug_unique'], 'products_store_id_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
