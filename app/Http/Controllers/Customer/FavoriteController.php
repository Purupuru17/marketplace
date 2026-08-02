<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $products = Product::query()
            ->whereHas('favoritedBy', fn ($q) => $q->whereKey($customer->id))
            ->with(['store', 'variants', 'promotions'])
            ->orderBy('name')
            ->get();

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
