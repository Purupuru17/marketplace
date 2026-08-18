<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user('api-customer');

        $products = Product::query()
            ->whereHas('favoritedBy', fn ($q) => $q->whereKey($customer->id))
            ->with(['store', 'variants', 'promotions'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => [
                'items' => $products->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'store' => [
                        'id' => $product->store?->id,
                        'name' => $product->store?->store_name,
                        'slug' => $product->store?->slug,
                    ],
                ])->values(),
            ],
        ]);
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
        ]);

        $customer = $request->user('api-customer');
        $product = Product::findOrFail($validated['product_id']);

        $exists = $customer->favoriteProducts()->whereKey($product->id)->exists();

        if ($exists) {
            $customer->favoriteProducts()->detach($product->id);
            $favorited = false;
        } else {
            $customer->favoriteProducts()->attach($product->id);
            $favorited = true;
        }

        return response()->json([
            'data' => [
                'favorited' => $favorited,
                'message' => $favorited ? 'Ditambahkan ke favorit.' : 'Dihapus dari favorit.',
            ],
        ]);
    }
}
