<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreOrderService
{
    public function __construct(protected StoreWalletService $walletService) {}

    public const TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public function query(User $user)
    {
        return Order::query()
            ->with(['store', 'customer', 'invoice.payments', 'items'])
            ->when(! $user->hasRole('Administrator'), function ($query) use ($user) {
                $query->whereIn('store_id', $user->stores()->pluck('id'));
            });
    }

    public function paginate(User $user, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->query($user)
            ->when(! empty($filters['status']), fn ($query) => $query->where('orders.status', $filters['status']))
            ->when(! empty($filters['store_id']), fn ($query) => $query->where('orders.store_id', $filters['store_id']))
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('orders.order_no', 'like', '%'.$filters['search'].'%')
                        ->orWhere('orders.invoice_id', 'like', '%'.$filters['search'].'%');
                });
            })
            ->orderByDesc('orders.created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function authorize(User $user, Order $order): bool
    {
        return $user->hasRole('Administrator') || $order->store->user_id === $user->id;
    }

    public function transition(User $user, Order $order, string $to, ?string $notes = null): Order
    {
        abort_unless($this->authorize($user, $order), 403);

        $from = $order->status;

        if (! in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Transisi status {$from} ke {$to} tidak diizinkan.",
            ]);
        }

        return DB::transaction(function () use ($order, $from, $to, $user, $notes) {
            if ($to === 'cancelled') {
                $this->restoreStock($order);
                $this->walletService->reverseHold($order);
            }

            if ($to === 'completed') {
                $this->settleCodIfApplicable($order);
                $this->walletService->settle($order);
            }

            $order->update(['status' => $to]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status_from' => $from,
                'status_to' => $to,
                'changed_by_type' => 'store',
                'changed_by_id' => $user->id,
                'notes' => $notes,
            ]);

            if ($to === 'cancelled') {
                $this->syncInvoiceStatus($order);
            }

            return $order->refresh()->load(['invoice.payments', 'items', 'store']);
        });
    }

    protected function restoreStock(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            $variant = ProductVariant::whereKey($item->variant_id)->lockForUpdate()->firstOrFail();
            $stockBefore = (int) $variant->stock;
            $stockAfter = $stockBefore + $item->qty;

            $variant->update(['stock' => $stockAfter]);

            StockMovement::create([
                'product_variant_id' => $variant->id,
                'type' => 'in',
                'qty' => $item->qty,
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => "Pembatalan pesanan {$order->order_no}",
            ]);
        }
    }

    protected function settleCodIfApplicable(Order $order): void
    {
        $invoice = $order->invoice;
        $payment = $invoice->payments()->latest('created_at')->latest('id')->first();

        if ($payment && $payment->payment_method === 'cod' && $payment->status === 'pending' && $invoice->status !== 'paid') {
            $payment->update(['status' => 'paid', 'paid_at' => now()]);
            $invoice->update(['status' => 'paid']);
        }
    }

    protected function syncInvoiceStatus(Order $order): void
    {
        $invoice = $order->invoice;

        $hasActive = $invoice->orders()
            ->whereIn('status', ['pending', 'processing', 'shipped', 'completed'])
            ->exists();

        if (! $hasActive && $invoice->status !== 'cancelled') {
            $invoice->update(['status' => 'cancelled']);
        }
    }
}
