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
        $addresses = $customer->addresses()->with('locationNode')->orderByDesc('is_default')->get();
        $selectedAddressId = $request->input('address_id')
            ?? $addresses->firstWhere('is_default', true)?->id
            ?? $addresses->first()?->id;

        $fulfillmentByStore = $this->storesMap($request, 'fulfillment_type', 'delivery');
        $paymentMethodByStore = $this->storesMap($request, 'payment_method', 'cash');

        $summary = $this->service->getSummary(
            $customer,
            $selectedAddressId,
            $fulfillmentByStore,
            $paymentMethodByStore,
        );

        return response()->json([
            'data' => [
                'addresses' => $addresses->map(fn ($a) => [
                    'id' => $a->id,
                    'label' => $a->label,
                    'recipient_name' => $a->recipient_name,
                    'phone' => $a->phone,
                    'full_address' => $a->full_address,
                    'location_node' => $a->locationNode?->name,
                    'is_default' => (bool) $a->is_default,
                ])->values(),
                'selected_address_id' => $selectedAddressId,
                'address' => $summary['address']?->only(['id', 'label', 'recipient_name', 'phone', 'full_address']),
                'by_store' => $summary['by_store']->map(function ($group) {
                    return [
                        'store' => [
                            'id' => $group['store']->id,
                            'name' => $group['store']->store_name,
                        ],
                        'fulfillment_type' => $group['fulfillment_type'],
                        'payment_method' => $group['payment_method'],
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
            'stores' => ['required', 'array', 'min:1'],
            'stores.*.fulfillment_type' => ['required', 'in:pickup,delivery'],
            'stores.*.payment_method' => ['required', 'in:'.implode(',', array_keys(PaymentService::METHODS))],
            'address_id' => ['nullable', 'uuid', 'exists:customer_addresses,id'],
            'points' => ['nullable', 'integer', 'min:0'],
        ]);

        $fulfillmentByStore = [];
        $paymentMethodByStore = [];

        foreach ($validated['stores'] as $storeId => $options) {
            $fulfillmentByStore[$storeId] = $options['fulfillment_type'];
            $paymentMethodByStore[$storeId] = $options['payment_method'];
        }

        $invoice = $this->service->placeOrder(
            $request->user('api-customer'),
            $validated['address_id'] ?? null,
            $paymentMethodByStore,
            (int) ($validated['points'] ?? 0),
            $fulfillmentByStore,
        );

        return response()->json([
            'data' => $this->invoicePayload($invoice),
        ], 201);
    }

    protected function storesMap(Request $request, string $key, string $default): array
    {
        $stores = $request->input('stores', []);

        if (! is_array($stores) || $stores === []) {
            return [];
        }

        return collect($stores)->mapWithKeys(fn ($options, $storeId) => [
            $storeId => is_array($options) ? ($options[$key] ?? $default) : $default,
        ])->all();
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
                'fulfillment_type' => $order->fulfillment_type,
                'total' => (float) $order->total,
                'status' => $order->status,
            ])->values(),
        ];
    }
}
