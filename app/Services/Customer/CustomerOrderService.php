<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\Store\StoreWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerOrderService
{
    public function __construct(
        protected StoreWalletService $walletService,
        protected LoyaltyService $loyaltyService
    ) {}

    public function confirmReceived(Customer $customer, Order $order): Order
    {
        abort_unless($order->invoice->customer_id === $customer->id, 403);

        if ($order->status !== 'ready') {
            throw ValidationException::withMessages([
                'status' => 'Pesanan belum siap diserahterimakan.',
            ]);
        }

        $order->loadMissing('payments');
        $unpaid = $order->payments->isEmpty() || $order->payments->contains(fn ($p) => $p->status !== 'paid');

        if ($unpaid) {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran belum dikonfirmasi toko. Selesaikan pembayaran terlebih dahulu.',
            ]);
        }

        return DB::transaction(function () use ($order, $customer) {
            $order->update(['status' => 'completed']);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status_from' => 'ready',
                'status_to' => 'completed',
                'changed_by_type' => 'customer',
                'changed_by_id' => $customer->id,
                'notes' => 'Pesanan dikonfirmasi diterima oleh customer.',
            ]);

            $this->walletService->settle($order);
            $this->loyaltyService->creditEarn($order);

            return $order->refresh()->load(['payments', 'items', 'store']);
        });
    }
}
