<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Customer\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $service) {}

    public function show(Payment $payment)
    {
        $this->authorizeOwner($payment);

        $order = $payment->order;

        if (! $order) {
            abort(404);
        }

        if ($payment->status === 'paid') {
            return redirect()->route('customer.order.show', $order);
        }

        $order->load(['store.level', 'store.locationNode', 'items.product.images']);

        return view('customer.payment.show', [
            'order' => $order,
            'payment' => $payment,
            'info' => $this->service->payableInfo($payment),
        ]);
    }

    public function upload(Request $request, Payment $payment)
    {
        $this->authorizeOwner($payment);

        $validated = $request->validate([
            'proof' => ['required', 'file', 'image', 'max:2048'],
        ]);

        try {
            $payment = $this->service->uploadProof($payment, $validated['proof']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('status', 'Bukti pembayaran terkirim. Menunggu konfirmasi toko.');
    }

    protected function authorizeOwner(Payment $payment): void
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($payment->order?->invoice?->customer_id === $customer->id, 403);
    }
}
