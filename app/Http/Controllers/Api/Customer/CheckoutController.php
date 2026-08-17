<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\Customer\CheckoutService;
use App\Services\Customer\LoyaltyService;
use App\Services\Customer\PaymentService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $service,
        protected LoyaltyService $loyaltyService,
        protected PaymentService $paymentService,
    ) {}

    public function summary(Request $request)
    {
        $customer = $request->user('api-customer');
        $summary = $this->service->getSummary($customer, $request->input('address_id'));

        return response()->json([
            'data' => [
                'address' => $summary['address']?->only(['id', 'label', 'recipient_name', 'phone', 'full_address']),
                'by_store' => $summary['by_store']->map(function ($group) {
                    return [
                        'store' => [
                            'id' => $group['store']->id,
                            'name' => $group['store']->store_name,
                        ],
                        'subtotal' => (float) $group['subtotal'],
                        'discount' => (float) $group['discount'],
                        'shipping' => $group['shipping'],
                        'total' => (float) $group['total'],
                    ];
                })->values(),
                'subtotal' => (float) $summary['subtotal'],
                'discount' => (float) $summary['discount'],
                'shipping_total' => (float) $summary['shipping_total'],
                'grand_total' => (float) $summary['grand_total'],
                'payment_methods' => $this->paymentService->methods(),
                'available_points' => $this->loyaltyService->availablePoints($customer),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'address_id' => ['required', 'uuid', 'exists:customer_addresses,id'],
            'payment_method' => ['required', 'in:'.implode(',', array_keys(PaymentService::METHODS))],
            'points' => ['nullable', 'integer', 'min:0'],
        ]);

        $invoice = $this->service->placeOrder(
            $request->user('api-customer'),
            $validated['address_id'],
            $validated['payment_method'],
            (int) ($validated['points'] ?? 0),
        );

        return response()->json([
            'data' => $this->invoicePayload($invoice),
        ], 201);
    }

    protected function invoicePayload(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'grand_total' => (float) $invoice->grand_total,
            'status' => $invoice->status,
            'orders' => $invoice->orders->map(fn (Order $order) => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'store' => $order->store->store_name ?? null,
                'total' => (float) $order->total,
                'status' => $order->status,
            ])->values(),
        ];
    }
}
