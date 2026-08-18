<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Customer\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $service) {}

    public function show(Request $request, string $invoice)
    {
        $invoice = Invoice::query()->where('invoice_no', $invoice)->firstOrFail();
        abort_unless($invoice->customer_id === $request->user('api-customer')->id, 403);

        $payment = $this->service->latestPayable($invoice);

        if (! $payment || $payment->payment_method === 'cod') {
            return response()->json([
                'data' => ['invoice' => $invoice->only(['id', 'invoice_no', 'status', 'grand_total'])],
            ]);
        }

        return response()->json([
            'data' => [
                'invoice' => $invoice->only(['id', 'invoice_no', 'status', 'grand_total']),
                'payment' => $payment->only(['id', 'provider', 'payment_method', 'amount', 'status', 'expired_at']),
                'info' => $this->service->payableInfo($payment),
                'expired' => $this->service->isExpired($payment),
            ],
        ]);
    }

    public function confirm(Request $request, string $invoice)
    {
        $invoice = Invoice::query()->where('invoice_no', $invoice)->firstOrFail();
        abort_unless($invoice->customer_id === $request->user('api-customer')->id, 403);

        $payment = $this->service->latestPayable($invoice);

        if (! $payment || $payment->payment_method === 'cod') {
            throw ValidationException::withMessages(['payment' => 'Tidak ada pembayaran online yang menunggu.']);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'data' => ['message' => 'Pembayaran sudah lunas.', 'invoice' => $invoice->refresh()->only(['id', 'status'])],
            ]);
        }

        if ($this->service->isExpired($payment)) {
            $recreated = $this->service->recreate($invoice);

            return response()->json([
                'data' => ['message' => 'Pembayaran lama kedaluwarsa. Dibuat pembayaran baru.', 'payment_id' => $recreated->id],
            ], 201);
        }

        try {
            $this->service->confirmPaid($payment);
        } catch (ValidationException $e) {
            throw $e;
        }

        return response()->json([
            'data' => ['message' => 'Pembayaran berhasil. Pesanan sedang diproses.', 'invoice' => $invoice->refresh()->only(['id', 'status'])],
        ]);
    }
}
