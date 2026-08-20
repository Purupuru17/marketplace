<?php

namespace App\Services\Customer;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Services\Pricing\PromotionPricingService;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(protected PromotionPricingService $promotionPricingService) {}

    public function getActiveCart(Customer $customer): Cart
    {
        return Cart::firstOrCreate([
            'customer_id' => $customer->id,
            'status' => 'active',
        ]);
    }

    public function count(Customer $customer): int
    {
        return $this->getActiveCart($customer)->items()->sum('qty');
    }

    public function add(Customer $customer, ProductVariant $variant, int $qty): CartItem
    {
        if ($qty < 1) {
            throw ValidationException::withMessages(['qty' => 'Kuantitas minimal 1.']);
        }

        $this->ensureSellable($variant);

        $cart = $this->getActiveCart($customer);
        $existing = $cart->items()->where('variant_id', $variant->id)->first();

        $totalQty = ($existing?->qty ?? 0) + $qty;

        $this->ensureStockAvailable($variant, $totalQty);

        if ($existing) {
            $existing->update(['qty' => $totalQty]);

            return $existing;
        }

        return $cart->items()->create([
            'variant_id' => $variant->id,
            'qty' => $qty,
        ]);
    }

    public function updateQty(Customer $customer, CartItem $item, int $qty): CartItem
    {
        $this->assertOwnsItem($customer, $item);

        if ($qty < 1) {
            throw ValidationException::withMessages(['qty' => 'Kuantitas minimal 1.']);
        }

        $this->ensureStockAvailable($item->variant, $qty);

        $item->update(['qty' => $qty]);

        return $item;
    }

    public function remove(Customer $customer, CartItem $item): void
    {
        $this->assertOwnsItem($customer, $item);
        $item->delete();
    }

    public function items(Customer $customer)
    {
        return $this->getActiveCart($customer)
            ->items()
            ->with([
                'variant' => fn ($q) => $q->with(['product.store', 'product.promotions', 'product.images', 'attributeValues.attribute']),
            ])
            ->get();
    }

    public function summary(Customer $customer): array
    {
        $items = $this->items($customer);

        $byStore = $items->groupBy(fn ($item) => $item->variant->product->store_id)
            ->map(function ($group) {
                $store = $group->first()->variant->product->store;

                foreach ($group as $item) {
                    $pricing = $this->promotionPricingService->pricing($item->variant);
                    $item->setAttribute('unit_price', $pricing['effective']);
                    $item->setAttribute('unit_original_price', $pricing['original']);
                    $item->setAttribute('unit_discount', $pricing['discount']);
                    $item->setAttribute('promotion', $pricing['promotion']);
                }

                return [
                    'store' => $store,
                    'items' => $group,
                    'subtotal' => $group->sum(fn ($item) => (float) $item->unit_price * $item->qty),
                    'discount' => $group->sum(fn ($item) => (float) $item->unit_discount * $item->qty),
                ];
            })
            ->values();

        return [
            'items' => $items,
            'by_store' => $byStore,
            'subtotal' => $byStore->sum('subtotal'),
            'discount' => $byStore->sum('discount'),
            'total' => $byStore->sum('subtotal'),
        ];
    }

    protected function ensureSellable(ProductVariant $variant): void
    {
        $variant->loadMissing(['product', 'store']);

        if ($variant->status !== 'active' || $variant->product->status !== 'active' || $variant->store->status !== 'active') {
            throw ValidationException::withMessages([
                'variant_id' => 'Produk tidak tersedia.',
            ]);
        }
    }

    protected function ensureStockAvailable(ProductVariant $variant, int $qty): void
    {
        if ($qty > (int) $variant->stock) {
            throw ValidationException::withMessages([
                'qty' => "Stok tidak mencukupi (sisa {$variant->stock}).",
            ]);
        }
    }

    protected function assertOwnsItem(Customer $customer, CartItem $item): void
    {
        abort_unless($item->cart_id === $this->getActiveCart($customer)->id, 403);
    }
}
