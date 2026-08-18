<?php

namespace App\Services\Customer;

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Store;
use App\Services\Pricing\PromotionPricingService;
use App\Services\Shipping\ShippingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected ShippingService $shippingService,
        protected PaymentService $paymentService,
        protected PromotionPricingService $promotionPricingService,
        protected LoyaltyService $loyaltyService
    ) {}

    public function getSummary(
        Customer $customer,
        ?string $addressId = null,
        array $fulfillmentByStore = [],
        array $paymentMethodByStore = []
    ): array {
        $items = $this->cartService->items($customer);
        $needsDelivery = $items->groupBy(fn ($item) => $item->variant->product->store_id)
            ->keys()
            ->contains(fn ($storeId) => ($fulfillmentByStore[$storeId] ?? 'delivery') === 'delivery');

        $address = ($needsDelivery && $addressId) ? $this->addressOf($customer, $addressId) : null;

        $byStore = $items->groupBy(fn ($item) => $item->variant->product->store_id)
            ->map(function ($group) use ($address, $fulfillmentByStore, $paymentMethodByStore) {
                $store = $group->first()->variant->product->store;
                $isPickup = ($fulfillmentByStore[$store->id] ?? 'delivery') === 'pickup';
                $subtotal = 0.0;
                $discount = 0.0;

                foreach ($group as $item) {
                    $pricing = $this->promotionPricingService->pricing($item->variant);
                    $item->setAttribute('unit_price', $pricing['effective']);
                    $item->setAttribute('unit_original_price', $pricing['original']);
                    $item->setAttribute('unit_discount', $pricing['discount']);
                    $item->setAttribute('promotion', $pricing['promotion']);
                    $subtotal += $pricing['effective'] * $item->qty;
                    $discount += $pricing['discount'] * $item->qty;
                }

                $shipping = $isPickup
                    ? $this->zeroShipping()
                    : $this->shippingService->estimate($store, $address?->locationNode);

                return [
                    'store' => $store,
                    'items' => $group,
                    'fulfillment_type' => $isPickup ? 'pickup' : 'delivery',
                    'payment_method' => $paymentMethodByStore[$store->id] ?? 'cash',
                    'subtotal' => round($subtotal, 2),
                    'discount' => round($discount, 2),
                    'shipping' => $shipping,
                    'total' => round($subtotal + $shipping['cost'], 2),
                ];
            })
            ->values();

        return [
            'address' => $address,
            'items' => $items,
            'by_store' => $byStore,
            'subtotal' => $byStore->sum('subtotal'),
            'discount' => $byStore->sum('discount'),
            'shipping_total' => $byStore->sum(fn ($group) => $group['shipping']['cost']),
            'grand_total' => $byStore->sum('total'),
        ];
    }

    public function placeOrder(
        Customer $customer,
        ?string $addressId,
        array $paymentMethodByStore,
        int $points = 0,
        array $fulfillmentByStore = []
    ): Invoice {
        return DB::transaction(function () use ($customer, $addressId, $paymentMethodByStore, $points, $fulfillmentByStore) {
            $address = $addressId ? $this->addressOf($customer, $addressId) : null;

            $items = $this->cartService->items($customer);

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang masih kosong.',
                ]);
            }

            $errors = [];
            $ordersPayload = [];

            foreach ($items->groupBy(fn ($item) => $item->variant->product->store_id) as $storeId => $group) {
                $store = $group->first()->variant->product->store;
                $isPickup = ($fulfillmentByStore[$store->id] ?? 'delivery') === 'pickup';
                $ordersPayload[] = $this->buildOrderPayload(
                    $store,
                    $group,
                    $isPickup ? null : $address,
                    $isPickup,
                    $paymentMethodByStore[$store->id] ?? 'cash',
                    $errors
                );
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            $promoDiscount = collect($ordersPayload)->sum('discount');
            $grandTotal = collect($ordersPayload)->sum('total');

            if ($points > 0) {
                $this->loyaltyService->assertRedeemable($customer, $points);
                $pointsValue = $this->loyaltyService->redeemValue($points);

                if ($pointsValue > $grandTotal) {
                    throw ValidationException::withMessages([
                        'points' => 'Nilai poin yang ditukar melebihi total belanja.',
                    ]);
                }
            } else {
                $pointsValue = 0;
            }

            $invoice = Invoice::create([
                'invoice_no' => $this->nextInvoiceNo(),
                'customer_id' => $customer->id,
                'subtotal' => collect($ordersPayload)->sum('subtotal'),
                'total_discount' => round($promoDiscount + $pointsValue, 2),
                'total_shipping_cost' => collect($ordersPayload)->sum('shipping_cost'),
                'grand_total' => round($grandTotal - $pointsValue, 2),
                'status' => 'pending',
            ]);

            if ($points > 0) {
                $this->loyaltyService->redeem($customer, $invoice, $points);
            }

            foreach ($ordersPayload as $payload) {
                $isPickup = $payload['fulfillment_type'] === 'pickup';
                $order = Order::create([
                    'order_no' => $this->nextOrderNo(),
                    'invoice_id' => $invoice->id,
                    'store_id' => $payload['store']->id,
                    'customer_id' => $customer->id,
                    'status' => 'pending',
                    'fulfillment_type' => $payload['fulfillment_type'],
                    'subtotal' => $payload['subtotal'],
                    'discount' => $payload['discount'],
                    'shipping_cost' => $payload['shipping_cost'],
                    'total' => $payload['total'],
                    'address_snapshot' => $isPickup || ! $payload['address']
                        ? null
                        : json_encode($payload['address']->only([
                            'recipient_name', 'phone', 'full_address', 'label',
                        ])),
                    'distance_km_snapshot' => $payload['distance_km'],
                    'origin_node_snapshot' => $payload['origin_node'],
                    'destination_node_snapshot' => $payload['destination_node'],
                    'rate_per_km_snapshot' => $payload['rate_per_km'],
                    'free_distance_snapshot' => $payload['free_distance'],
                ]);

                foreach ($payload['items'] as $item) {
                    $this->createOrderItem($order, $item, $payload['pricing'][$item->id] ?? null);
                    $this->deductStock($order, $item);
                }

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status_from' => null,
                    'status_to' => 'pending',
                    'changed_by_type' => 'system',
                    'notes' => 'Pesanan dibuat.',
                ]);

                $this->paymentService->createPaymentForOrder($order, $payload['payment_method']);
            }

            $this->cartService->getActiveCart($customer)->update(['status' => 'converted']);

            return $invoice->load('orders.items');
        });
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     * @return array{store: Store, items: Collection<int, CartItem>, address: CustomerAddress|null, fulfillment_type: string, payment_method: string, subtotal: float, discount: float, shipping_cost: float, total: float, distance_km: float|null, origin_node: string|null, destination_node: string|null, rate_per_km: float|null, free_distance: float|null, pricing: array<string, array{original: float, effective: float, discount: float}>}
     */
    protected function buildOrderPayload(Store $store, $group, ?CustomerAddress $address, bool $isPickup, string $paymentMethod, array &$errors): array
    {
        $subtotal = 0.0;
        $discount = 0.0;
        $pricing = [];

        foreach ($group as $item) {
            $variant = $item->variant;
            $variant->loadMissing(['product', 'store']);

            if ($variant->status !== 'active' || $variant->product->status !== 'active' || $variant->store->status !== 'active') {
                $errors['items'][] = "Produk {$variant->product->name} tidak tersedia.";
            }

            $freshVariant = ProductVariant::whereKey($variant->id)->lockForUpdate()->first();

            if ((int) $freshVariant->stock < $item->qty) {
                $errors['items'][] = "Stok {$variant->product->name} ({$variant->sku}) tidak mencukupi.";
            }

            $itemPricing = $this->promotionPricingService->pricing($variant);
            $pricing[$item->id] = $itemPricing;
            $subtotal += $itemPricing['effective'] * $item->qty;
            $discount += $itemPricing['discount'] * $item->qty;
        }

        $estimate = $isPickup
            ? $this->zeroShipping()
            : $this->shippingService->estimate($store, $address?->locationNode);

        if (! $isPickup && ! $estimate['within_radius']) {
            $errors['items'][] = "Toko {$store->store_name} di luar jangkauan pengiriman.";
        }

        $shippingCost = $estimate['cost'];

        return [
            'store' => $store,
            'items' => $group,
            'address' => $isPickup ? null : $address,
            'fulfillment_type' => $isPickup ? 'pickup' : 'delivery',
            'payment_method' => $paymentMethod,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'shipping_cost' => $shippingCost,
            'total' => round($subtotal + $shippingCost, 2),
            'distance_km' => $estimate['distance_km'],
            'origin_node' => $store->locationNode?->name,
            'destination_node' => $isPickup ? null : $address?->locationNode?->name,
            'rate_per_km' => $store->rate_per_km,
            'free_distance' => $store->min_free_distance_km,
            'pricing' => $pricing,
        ];
    }

    protected function zeroShipping(): array
    {
        return ['distance_km' => null, 'cost' => 0.0, 'within_radius' => true];
    }

    protected function createOrderItem(Order $order, $item, ?array $pricing = null): void
    {
        $variant = $item->variant;
        $original = $pricing['original'] ?? (float) $variant->price;
        $final = $pricing['effective'] ?? $original;
        $discount = $pricing['discount'] ?? 0;

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'name_snapshot' => $variant->product->name,
            'sku_snapshot' => $variant->sku,
            'variant_snapshot' => $variant->attributeValues->isNotEmpty()
                ? $variant->attributeValues->sortBy(fn ($value) => $value->attribute?->name)->pluck('value')->join(' · ')
                : null,
            'original_price_snapshot' => $original,
            'discount_snapshot' => $discount,
            'final_price_snapshot' => $final,
            'qty' => $item->qty,
            'subtotal_snapshot' => round($final * $item->qty, 2),
        ]);
    }

    protected function deductStock(Order $order, $item): void
    {
        $variant = ProductVariant::whereKey($item->variant_id)->lockForUpdate()->firstOrFail();
        $stockBefore = (int) $variant->stock;
        $stockAfter = $stockBefore - $item->qty;

        $variant->update(['stock' => $stockAfter]);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'type' => 'out',
            'qty' => $item->qty,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => "Pesanan {$order->order_no}",
        ]);
    }

    protected function addressOf(Customer $customer, string $addressId): CustomerAddress
    {
        $address = CustomerAddress::findOrFail($addressId);

        abort_unless($address->customer_id === $customer->id, 403);

        return $address;
    }

    protected function nextInvoiceNo(): string
    {
        return $this->nextNumbered('invoices', 'INV');
    }

    protected function nextOrderNo(): string
    {
        return $this->nextNumbered('orders', 'ORD');
    }

    protected function nextNumbered(string $table, string $prefix): string
    {
        $today = now()->format('Ymd');
        $key = "{$table}:{$today}";

        $number = DB::transaction(function () use ($key) {
            $row = DB::table('number_sequences')->where('key', $key)->lockForUpdate()->first();

            if ($row) {
                $next = $row->last_number + 1;
                DB::table('number_sequences')->where('key', $key)->update(['last_number' => $next]);
            } else {
                $next = 1;
                DB::table('number_sequences')->insert(['key' => $key, 'last_number' => $next]);
            }

            return $next;
        });

        return "{$prefix}-{$today}-".Str::padLeft((string) $number, 4, '0');
    }
}
