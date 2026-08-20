<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\Customer\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(protected CartService $service) {}

    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $summary = $this->service->summary($customer);

        return view('customer.cart.index', $summary);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'uuid', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::findOrFail($data['variant_id']);

        $this->service->add(Auth::guard('customer')->user(), $variant, (int) $data['qty']);

        if ($request->boolean('checkout')) {
            return redirect()->route('customer.checkout.index')
                ->with('success', 'Item ditambahkan ke keranjang.');
        }

        return redirect()->route('customer.cart.index')
            ->with('success', 'Item ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $item)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $this->service->updateQty(Auth::guard('customer')->user(), $item, (int) $data['qty']);

        return redirect()->route('customer.cart.index')
            ->with('success', 'Kuantitas diperbarui.');
    }

    public function destroy(CartItem $item)
    {
        $this->service->remove(Auth::guard('customer')->user(), $item);

        return redirect()->route('customer.cart.index')
            ->with('success', 'Item dihapus dari keranjang.');
    }
}
