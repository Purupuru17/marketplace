<?php

namespace App\Services\Customer;

use App\Models\Invoice;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentWebhookLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public const ONLINE_METHODS = ['bank_transfer', 'e_wallet'];

    public const METHODS = [
        'bank_transfer' => 'Transfer Bank',
        'e_wallet' => 'E-Wallet',
        'cod' => 'Cash on Delivery (COD)',
    ];

    public function methods(): array
    {
        return self::METHODS;
    }

    public function onlineMethods(): array
    {
        return array_intersect_key(self::METHODS, array_flip(self::ONLINE_METHODS));
    }

    public function isOnline(string $method): bool
    {
        return in_array($method, self::ONLINE_METHODS, true);
    }

    public function createPayment(Invoice $invoice, string $method): Payment
    {
        return Payment::create([
            'invoice_id' => $invoice->id,
            'provider' => $this->isOnline($method) ? 'simulated' : 'cod',
            'payment_method' => $method,
            'amount' => $invoice->grand_total,
            'status' => 'pending',
            'expired_at' => $this->isOnline($method) ? now()->addHours(24) : null,
        ]);
    }

    public function latestPayable(Invoice $invoice): ?Payment
    {
        return $invoice->payments()->latest('created_at')->latest('id')->first();
    }

    public function isExpired(Payment $payment): bool
    {
        return $payment->expired_at !== null && now()->gt($payment->expired_at);
    }

    public function payableInfo(Payment $payment): array
    {
        $seed = Str::after($payment->id, '-');

        if ($payment->payment_method === 'e_wallet') {
            return [
                'app' => 'SimPay',
                'account' => '0812'.substr($seed, 0, 9),
                'instruction' => 'Bayar lewat aplikasi SimPay ke nomor di atas, lalu konfirmasi pembayaran di sini.',
            ];
        }

        if ($payment->payment_method === 'cod') {
            return [
                'instruction' => 'Bayar tunai kepada kurir saat barang diterima.',
            ];
        }

        return [
            'bank' => 'Bank Simulasi',
            'virtual_account' => '880'.substr($seed, 0, 11),
            'instruction' => 'Transfer ke virtual account di atas, lalu konfirmasi pembayaran di sini.',
        ];
    }

    public function confirmPaid(Payment $payment): Payment
    {
        if ($payment->status === 'paid') {
            return $payment;
        }

        if ($payment->payment_method === 'cod') {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran COD diselesaikan saat barang diterima.',
            ]);
        }

        if ($payment->status === 'cancelled' || $this->isExpired($payment)) {
            $payment->update(['status' => 'expired']);

            throw ValidationException::withMessages([
                'payment' => 'Pembayaran sudah kedaluwarsa. Silakan buat pembayaran baru.',
            ]);
        }

        return DB::transaction(function () use ($payment) {
            $payment->load('invoice.orders');

            PaymentWebhookLog::create([
                'payment_id' => $payment->id,
                'provider' => 'simulated',
                'event_id' => 'payment.'.(string) Str::uuid(),
                'payload' => json_encode([
                    'method' => $payment->payment_method,
                    'amount' => $payment->amount,
                ]),
                'processed_at' => now(),
                'status' => 'success',
            ]);

            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $invoice = $payment->invoice;
            $invoice->update(['status' => 'paid']);

            foreach ($invoice->orders as $order) {
                $order->update(['status' => 'processing']);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status_from' => 'pending',
                    'status_to' => 'processing',
                    'changed_by_type' => 'system',
                    'notes' => 'Pembayaran diterima.',
                ]);
            }

            return $payment->refresh();
        });
    }

    public function recreate(Invoice $invoice): Payment
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->payments()
                ->where('status', 'pending')
                ->update(['status' => 'failed']);

            $method = $this->latestPayable($invoice)?->payment_method ?? 'bank_transfer';

            return $this->createPayment($invoice, $method);
        });
    }
}
