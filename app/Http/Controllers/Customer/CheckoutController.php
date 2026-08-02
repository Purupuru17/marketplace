<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Customer\CartService;
use App\Services\Customer\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $service,
        protected CartService $cartService
    ) {}

    public function index()
    {
        $customer = Auth::guard('customer')->user();

        if ($this->cartService->items($customer)->isEmpty()) {
            return redirect()->route('customer.cart.index');
        }

        $addresses = $customer->addresses()->with('locationNode')->orderByDesc('is_default')->get();

        $selectedAddressId = request('address_id')
            ?? $addresses->firstWhere('is_default', true)?->id
            ?? $addresses->first()?->id;

        $summary = $this->service->getSummary($customer, $selectedAddressId);

        return view('customer.checkout.index', compact('addresses', 'summary'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'address_id' => ['required', 'uuid', 'exists:customer_addresses,id'],
        ]);

        $customer = Auth::guard('customer')->user();
        $invoice = $this->service->placeOrder($customer, $data['address_id']);

        return redirect()->route('customer.checkout.success', $invoice->id);
    }

    public function success(Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($invoice->customer_id === $customer->id, 403);

        return view('customer.checkout.success', [
            'invoice' => $invoice->load(['orders.items', 'orders.store']),
        ]);
    }
}
