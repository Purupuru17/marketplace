<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\Customer\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $service) {}

    public function index(Request $request)
    {
        $customer = $request->user('api-customer');
        $summary = $this->service->summary($customer);

        return response()->json([
            'data' => [
                'items' => $summary['items']->map(fn (CartItem $item) => $this->itemPayload($item))->values(),
                'by_store' => $summary['by_store']->map(fn ($group) => [
                    'store' => [
                        'id' => $group['store']->id,
                        'name' => $group['store']->store_name,
                        'slug' => $group['store']->slug,
                    ],
                    'items' => $group['items']->map(fn (CartItem $item) => $this->itemPayload($item))->values(),
                    'subtotal' => (float) $group['subtotal'],
                    'discount' => (float) $group['discount'],
                ])->values(),
                'subtotal' => (float) $summary['subtotal'],
                'discount' => (float) $summary['discount'],
                'total' => (float) $summary['total'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'uuid', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);
        $item = $this->service->add(
            $request->user('api-customer'),
            $variant,
            (int) $validated['qty'],
        );

        return response()->json([
            'data' => [
                'message' => 'Item ditambahkan ke keranjang.',
                'count' => $this->service->count($request->user('api-customer')),
                'cart_item_id' => $item->id,
            ],
        ], 201);
    }

    public function update(Request $request, CartItem $item)
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $item = $this->service->updateQty(
            $request->user('api-customer'),
            $item,
            (int) $validated['qty'],
        );

        return response()->json([
            'data' => [
                'message' => 'Jumlah item diperbarui.',
                'qty' => $item->qty,
            ],
        ]);
    }

    public function destroy(Request $request, CartItem $item)
    {
        $this->service->remove($request->user('api-customer'), $item);

        return response()->json([
            'data' => ['message' => 'Item dihapus dari keranjang.'],
        ]);
    }

    protected function itemPayload(CartItem $item): array
    {
        $variant = $item->variant;

        return [
            'id' => $item->id,
            'qty' => $item->qty,
            'unit_price' => (float) ($item->unit_price ?? $variant?->price),
            'unit_original_price' => (float) ($item->unit_original_price ?? $variant?->price),
            'unit_discount' => (float) ($item->unit_discount ?? 0),
            'variant' => [
                'id' => $variant?->id,
                'sku' => $variant?->sku,
                'stock' => $variant?->stock,
            ],
            'product' => [
                'id' => $variant?->product?->id,
                'name' => $variant?->product?->name,
                'slug' => $variant?->product?->slug,
            ],
            'store' => [
                'id' => $variant?->product?->store?->id,
                'name' => $variant?->product?->store?->store_name,
            ],
        ];
    }
}
