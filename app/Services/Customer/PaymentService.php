<?php

namespace App\Services\Customer;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\User;
use App\Services\Store\StoreWalletService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(protected StoreWalletService $walletService) {}

    public const METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Transfer Bank Manual',
    ];

    public function methods(): array
    {
        return self::METHODS;
    }

    public function createPaymentForOrder(Order $order, string $method): Payment
    {
        $store = $order->store;
        $bank = $method === 'bank_transfer' && $store?->account_number
            ? [
                'bank_name' => $store->bank_name,
                'account_number' => $store->account_number,
                'account_name' => $store->account_name,
            ]
            : null;

        return Payment::create([
            'order_id' => $order->id,
            'provider' => $method === 'bank_transfer' ? 'manual' : 'cash',
            'payment_method' => $method,
            'bank_snapshot' => $bank,
            'amount' => $order->total,
            'status' => 'pending',
            'expired_at' => null,
        ]);
    }

    public function payableInfo(Payment $payment): array
    {
        if ($payment->payment_method === 'bank_transfer') {
            $bank = $payment->bank_snapshot ?? [];

            return [
                'bank' => $bank['bank_name'] ?? '-',
                'account_number' => $bank['account_number'] ?? '-',
                'account_name' => $bank['account_name'] ?? '-',
                'instruction' => 'Transfer ke rekening toko di atas, lalu upload bukti pembayaran. Pesanan diproses setelah toko mengonfirmasi.',
            ];
        }

        return [
            'instruction' => 'Bayar tunai langsung ke penjual (ambil sendiri atau diantar penjual).',
        ];
    }

    public function uploadProof(Payment $payment, UploadedFile $file): Payment
    {
        if ($payment->payment_method !== 'bank_transfer') {
            throw ValidationException::withMessages([
                'proof' => 'Bukti hanya untuk metode transfer bank.',
            ]);
        }

        if ($payment->status !== 'pending') {
            throw ValidationException::withMessages([
                'proof' => 'Pembayaran sudah tidak bisa diubah.',
            ]);
        }

        $path = $file->store('payment-proofs', 'public');

        $payment->update([
            'payment_proof_path' => $path,
        ]);

        return $payment->refresh();
    }

    /**
     * Validasi LUNAS oleh toko/administrator. Customer tidak bisa melunasi sendiri.
     */
    public function markPaidByStore(Payment $payment, User $user, ?string $notes = null): Payment
    {
        if ($payment->status === 'paid') {
            return $payment;
        }

        if ($payment->status === 'cancelled') {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran sudah dibatalkan.',
            ]);
        }

        return DB::transaction(function () use ($payment, $user, $notes) {
            $payment->load(['order.invoice']);
            $order = $payment->order;

            if (! $order) {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran tidak terhubung ke order.',
                ]);
            }

            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if ($order->status === 'pending') {
                $order->update(['status' => 'processing']);
            }

            $this->walletService->hold($order);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status_from' => 'pending',
                'status_to' => 'processing',
                'changed_by_type' => 'store',
                'changed_by_id' => $user->id,
                'notes' => $notes ?? 'Pembayaran dikonfirmasi toko.',
            ]);

            $this->syncInvoiceStatus($order->invoice);

            return $payment->refresh();
        });
    }

    protected function syncInvoiceStatus(Invoice $invoice): void
    {
        $allPaid = ! $invoice->orders()
            ->whereDoesntHave('payments', fn ($query) => $query->where('status', 'paid'))
            ->exists();

        $invoice->update(['status' => $allPaid ? 'paid' : 'pending']);
    }
}
