<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('store_id', 'products_store_id_index');
            $table->dropUnique('products_store_id_slug_unique');
            $table->string('slug_unique')->storedAs("IF(deleted_at IS NULL, slug, CONCAT(slug, '#', id))")->after('slug');
            $table->unique(['store_id', 'slug_unique'], 'products_store_id_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_store_id_slug_unique');
            $table->dropColumn('slug_unique');
            $table->unique(['store_id', 'slug'], 'products_store_id_slug_unique');
            $table->dropIndex('products_store_id_index');
        });
    }
};
