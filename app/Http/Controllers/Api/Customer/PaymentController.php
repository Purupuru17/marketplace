<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Customer\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $service) {}

    public function show(Request $request, Payment $payment)
    {
        $this->authorizeOwner($payment);

        $invoice = $payment->order?->invoice;

        return response()->json([
            'data' => [
                'invoice' => $invoice->only(['id', 'invoice_no', 'status', 'grand_total']),
                'order' => [
                    'id' => $payment->order?->id,
                    'order_no' => $payment->order?->order_no,
                    'store' => $payment->order?->store?->store_name,
                    'status' => $payment->order?->status,
                ],
                'payment' => $payment->only(['id', 'provider', 'payment_method', 'amount', 'status', 'paid_at', 'payment_proof_path']),
                'payment_proof_url' => $payment->payment_proof_path
                    ? asset('storage/'.$payment->payment_proof_path)
                    : null,
                'info' => $this->service->payableInfo($payment),
                'message' => $payment->status === 'paid'
                    ? null
                    : 'Menunggu konfirmasi pembayaran dari toko.',
            ],
        ]);
    }

    public function upload(Request $request, Payment $payment)
    {
        $this->authorizeOwner($payment);

        $validated = $request->validate([
            'proof' => ['required', 'image', 'max:2048'],
        ]);

        $payment = $this->service->uploadProof($payment, $validated['proof']);

        return response()->json([
            'data' => [
                'payment' => $payment->only(['id', 'status', 'payment_proof_path']),
                'payment_proof_url' => $payment->payment_proof_path
                    ? asset('storage/'.$payment->payment_proof_path)
                    : null,
            ],
        ]);
    }

    protected function authorizeOwner(Payment $payment): void
    {
        abort_unless($payment->order?->invoice?->customer_id === request()->user('api-customer')->id, 403);
    }
}