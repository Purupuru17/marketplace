<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Customer\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $service) {}

    public function show(Invoice $invoice)
    {
        $this->authorizeOwner($invoice);

        $payment = $this->service->latestPayable($invoice);

        if (! $payment) {
            return redirect()->route('customer.order.show', $invoice);
        }

        if ($payment->payment_method === 'cod') {
            return redirect()->route('customer.order.show', $invoice);
        }

        return view('customer.payment.show', [
            'invoice' => $invoice,
            'payment' => $payment,
            'info' => $this->service->payableInfo($payment),
            'expired' => $this->service->isExpired($payment),
        ]);
    }

    public function store(Request $request, Invoice $invoice)
    {
        $this->authorizeOwner($invoice);

        $payment = $this->service->latestPayable($invoice);

        if (! $payment || $payment->payment_method === 'cod') {
            return redirect()->route('customer.order.show', $invoice);
        }

        if ($payment->status === 'paid') {
            return redirect()->route('customer.payment.show', $invoice);
        }

        if ($this->service->isExpired($payment)) {
            $this->service->recreate($invoice);

            return redirect()->route('customer.payment.show', $invoice)
                ->with('success', 'Pembayaran baru dibuat. Selesaikan sebelum batas waktu.');
        }

        try {
            $this->service->confirmPaid($payment);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('customer.order.show', $invoice)
            ->with('success', 'Pembayaran berhasil. Pesanan sedang diproses.');
    }

    protected function authorizeOwner(Invoice $invoice): void
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($invoice->customer_id === $customer->id, 403);
    }
}
