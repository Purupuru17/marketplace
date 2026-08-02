<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $invoices = Invoice::query()
            ->where('customer_id', $customer->id)
            ->with(['orders.store', 'orders.items'])
            ->when($request->filled('search'), fn ($q, $search) => $q->where('invoice_no', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('customer.order.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($invoice->customer_id === $customer->id, 403);

        return view('customer.order.show', [
            'invoice' => $invoice->load(['orders.items', 'orders.store']),
        ]);
    }
}
