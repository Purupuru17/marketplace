<?php

namespace App\Services\Catalog;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Set stok ke nilai absolut tertentu (dipakai saat create/koreksi manual).
     * Menulis product_variants.stock (cache) dan stock_movements (ledger) dalam satu transaction.
     */
    public function adjustTo(
        ProductVariant $variant,
        int $newStock,
        string $notes = 'Penyesuaian stok manual.',
        ?string $referenceType = null,
        ?string $referenceId = null
    ): ProductVariant {
        return DB::transaction(function () use ($variant, $newStock, $notes, $referenceType, $referenceId) {
            $locked = ProductVariant::whereKey($variant->id)->lockForUpdate()->firstOrFail();
            $before = (int) $locked->stock;
            $after = $newStock;
            $delta = $after - $before;

            if ($delta === 0) {
                return $locked;
            }

            $locked->update(['stock' => $after]);

            StockMovement::create([
                'product_variant_id' => $locked->id,
                'type' => $delta > 0 ? 'in' : 'adjustment',
                'qty' => abs($delta),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $notes,
            ]);

            return $locked->fresh();
        });
    }

    /**
     * Tambah stok (mis. restock dari supplier).
     */
    public function increment(
        ProductVariant $variant,
        int $qty,
        string $notes,
        ?string $referenceType = null,
        ?string $referenceId = null
    ): ProductVariant {
        return DB::transaction(function () use ($variant, $qty, $notes, $referenceType, $referenceId) {
            $locked = ProductVariant::whereKey($variant->id)->lockForUpdate()->firstOrFail();
            $before = (int) $locked->stock;
            $after = $before + $qty;

            $locked->update(['stock' => $after]);

            StockMovement::create([
                'product_variant_id' => $locked->id,
                'type' => 'in',
                'qty' => $qty,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $notes,
            ]);

            return $locked->fresh();
        });
    }
}
