<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_distances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('origin_node_id')->constrained('location_nodes')->cascadeOnDelete();
            $table->foreignUuid('destination_node_id')->constrained('location_nodes')->cascadeOnDelete();
            $table->decimal('distance_km', 8, 2);
            $table->timestamps();

            // Undirected graph: one row per pair, service builds the reverse edge in memory.
            $table->unique(['origin_node_id', 'destination_node_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_distances');
    }
};
