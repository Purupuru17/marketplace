<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductRating;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RatingService
{
    public function create(Customer $customer, string $orderItemId, int $rating, ?string $review): ProductRating
    {
        $item = OrderItem::query()->with('order')->findOrFail($orderItemId);

        if ($item->order->customer_id !== $customer->id) {
            throw ValidationException::withMessages([
                'order_item_id' => 'Pesanan tidak ditemukan.',
            ]);
        }

        if ($item->order->status !== 'completed') {
            throw ValidationException::withMessages([
                'order_item_id' => 'Penilaian hanya bisa diberikan setelah pesanan selesai.',
            ]);
        }

        if (ProductRating::where('order_item_id', $item->id)->exists()) {
            throw ValidationException::withMessages([
                'order_item_id' => 'Produk ini sudah pernah dinilai.',
            ]);
        }

        return DB::transaction(fn () => ProductRating::create([
            'order_item_id' => $item->id,
            'product_id' => $item->product_id,
            'customer_id' => $customer->id,
            'rating' => $rating,
            'review' => $review,
            'status' => 'active',
        ]));
    }

    public function createMany(Customer $customer, Order $order, array $ratings): void
    {
        if ($order->customer_id !== $customer->id) {
            throw ValidationException::withMessages([
                'order_item_id' => 'Pesanan tidak ditemukan.',
            ]);
        }

        if ($order->status !== 'completed') {
            throw ValidationException::withMessages([
                'order_item_id' => 'Penilaian hanya bisa diberikan setelah pesanan selesai.',
            ]);
        }

        $items = $order->items()->with(['order'])->whereIn('id', array_keys($ratings))->get();
        $missing = array_diff(array_keys($ratings), $items->pluck('id')->all());
        if ($missing) {
            throw ValidationException::withMessages([
                'order_item_id' => 'Produk tidak ditemukan pada pesanan ini.',
            ]);
        }

        foreach ($items as $item) {
            $this->create($customer, $item->id, $ratings[$item->id]['rating'], $ratings[$item->id]['review'] ?? null);
        }
    }
}
