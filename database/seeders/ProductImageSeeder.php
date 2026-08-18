<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::where('store_code', 'STR-DEMO1')->first();

        if ($store) {
            $store->update([
                'logo' => 'stores/logo-ramen.png',
                'banner' => 'stores/banner-food.png',
            ]);
        }

        $map = [
            'nasi-goreng-spesial' => ['products/delicious-thai.png', 'products/ramen-bowl.png'],
            'ayam-geprek' => ['products/fried-chicken.png', 'products/ramen-bowl.png'],
            'es-kopi-susu' => ['products/frappe-coffee.png'],
        ];

        foreach ($map as $slug => $paths) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                continue;
            }

            foreach ($paths as $i => $path) {
                ProductImage::firstOrCreate(
                    ['product_id' => $product->id, 'path' => $path],
                    ['position' => $i, 'is_primary' => $i === 0],
                );
            }
        }

        $spicy = ProductVariant::where('sku', 'AG-REG')->first();

        if ($spicy) {
            ProductImage::firstOrCreate(
                ['product_id' => $spicy->product_id, 'variant_id' => $spicy->id],
                ['path' => 'products/ramen-bowl.png', 'position' => 0, 'is_primary' => false],
            );
        }
    }
}