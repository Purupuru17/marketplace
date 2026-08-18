<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoyaltyService
{
    public const EARN_PER = 5000;

    public const REDEEM_POINTS = 100;

    public const REDEEM_VALUE = 1000;

    public const MIN_REDEEM_POINTS = 100;

    public function availablePoints(Customer $customer): int
    {
        return (int) PointTransaction::where('customer_id', $customer->id)->sum('points');
    }

    public function pointsForOrder(Order $order): int
    {
        return (int) floor((float) $order->total / self::EARN_PER);
    }

    public function creditEarn(Order $order): void
    {
        $points = $this->pointsForOrder($order);

        if ($points <= 0) {
            return;
        }

        $exists = PointTransaction::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('type', 'earn')
            ->exists();

        if ($exists) {
            return;
        }

        PointTransaction::create([
            'customer_id' => $order->customer_id,
            'type' => 'earn',
            'points' => $points,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
            'description' => "Poin dari pesanan {$order->order_no}",
        ]);
    }

    public function redeemValue(int $points): int
    {
        return (int) floor($points / self::REDEEM_POINTS) * self::REDEEM_VALUE;
    }

    public function assertRedeemable(Customer $customer, int $points): void
    {
        if ($points < self::MIN_REDEEM_POINTS || $points % self::REDEEM_POINTS !== 0) {
            throw ValidationException::withMessages([
                'points' => 'Poin yang ditukar minimal '.self::MIN_REDEEM_POINTS.' dan kelipatan '.self::REDEEM_POINTS.'.',
            ]);
        }

        if ($points > $this->availablePoints($customer)) {
            throw ValidationException::withMessages([
                'points' => 'Poin yang Anda miliki tidak mencukupi.',
            ]);
        }
    }

    public function redeem(Customer $customer, Invoice $invoice, int $points): void
    {
        DB::transaction(function () use ($customer, $invoice, $points) {
            PointTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'redeem',
                'points' => -$points,
                'reference_type' => Invoice::class,
                'reference_id' => $invoice->id,
                'description' => "Tukar poin pada invoice {$invoice->invoice_no}",
            ]);
        });
    }
}
