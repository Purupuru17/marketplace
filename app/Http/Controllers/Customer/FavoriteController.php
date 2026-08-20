<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Customer\StorefrontService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function __construct(protected StorefrontService $storefrontService) {}

    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $products = $this->storefrontService->decorateProducts(
            Product::query()
                ->whereHas('favoritedBy', fn ($q) => $q->whereKey($customer->id))
                ->with(['store.locationNode', 'store.level', 'images', 'variants' => fn ($q) => $q->where('status', 'active')])
                ->orderBy('name')
                ->get()
        );

        return view('customer.favorite.index', compact('products'));
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'uuid', 'exists:products,id'],
        ]);

        $customer = Auth::guard('customer')->user();
        $product = Product::findOrFail($validated['product_id']);

        $exists = $customer->favoriteProducts()->whereKey($product->id)->exists();

        if ($exists) {
            $customer->favoriteProducts()->detach($product->id);
            $message = 'Dihapus dari favorit.';
        } else {
            $customer->favoriteProducts()->attach($product->id);
            $message = 'Ditambahkan ke favorit.';
        }

        return back()->with('success', $message);
    }
}
