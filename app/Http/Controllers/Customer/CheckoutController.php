<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Customer\CartService;
use App\Services\Customer\CheckoutService;
use App\Services\Customer\LoyaltyService;
use App\Services\Customer\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $service,
        protected CartService $cartService,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        if ($this->cartService->items($customer)->isEmpty()) {
            return redirect()->route('customer.cart.index');
        }

        $addresses = $customer->addresses()->with('locationNode')->orderByDesc('is_default')->get();

        $payment_methods = $this->paymentService->methods();

        $storeIds = $this->cartService->items($customer)
            ->groupBy(fn ($item) => $item->variant->product->store_id)
            ->keys()
            ->values()
            ->all();

        $fulfillmentByStore = collect($storeIds)->mapWithKeys(fn ($id) => [$id => 'delivery'])->all();
        $paymentMethodByStore = collect($storeIds)->mapWithKeys(fn ($id) => [$id => 'cash'])->all();

        $selectedAddressId = request('address_id')
            ?? $addresses->firstWhere('is_default', true)?->id
            ?? $addresses->first()?->id;

        $summary = $this->service->getSummary(
            $customer,
            $selectedAddressId,
            $fulfillmentByStore,
            $paymentMethodByStore
        );

        return view('customer.checkout.index', [
            'addresses' => $addresses,
            'summary' => $summary,
            'store_ids' => $storeIds,
            'selected_address_id' => $selectedAddressId,
            'payment_methods' => $payment_methods,
            'available_points' => app(LoyaltyService::class)->availablePoints($customer),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'stores' => ['required', 'array', 'min:1'],
            'stores.*.fulfillment_type' => ['required', Rule::in(['pickup', 'delivery'])],
            'stores.*.payment_method' => ['required', 'string', Rule::in(array_keys(PaymentService::METHODS))],
            'address_id' => ['nullable', 'uuid', 'exists:customer_addresses,id'],
            'points' => ['nullable', 'integer', 'min:0'],
        ]);

        $fulfillmentByStore = [];
        $paymentMethodByStore = [];

        foreach ($data['stores'] as $storeId => $options) {
            $fulfillmentByStore[$storeId] = $options['fulfillment_type'];
            $paymentMethodByStore[$storeId] = $options['payment_method'];
        }

        if (in_array('delivery', $fulfillmentByStore, true) && empty($data['address_id'])) {
            throw ValidationException::withMessages([
                'address_id' => 'Alamat pengiriman wajib dipilih.',
            ]);
        }

        $customer = Auth::guard('customer')->user();
        $invoice = $this->service->placeOrder(
            $customer,
            $data['address_id'] ?? null,
            $paymentMethodByStore,
            (int) ($data['points'] ?? 0),
            $fulfillmentByStore
        );

        return redirect()->route('customer.checkout.success', $invoice->id);
    }

    public function success(Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($invoice->customer_id === $customer->id, 403);

        return view('customer.checkout.success', [
            'invoice' => $invoice->load(['orders.items', 'orders.store', 'orders.payments', 'payments']),
        ]);
    }
}
