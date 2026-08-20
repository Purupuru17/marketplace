<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $status = $request->query('status');

        $invoices = Invoice::query()
            ->where('customer_id', $customer->id)
            ->with(['orders.store', 'orders.items.product.images', 'orders.items.rating', 'orders.payments'])
            ->when($status, fn ($q) => $q->whereHas('orders', fn ($oq) => $oq->where('status', $status)))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('customer.order.index', compact('invoices', 'status'));
    }

    public function show(Order $order)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($order->customer_id === $customer->id, 403);

        $order->load(['items.product.images', 'items.rating', 'store.level', 'store.locationNode', 'payments', 'statusHistories', 'invoice.orders.store', 'invoice.orders.items']);

        return view('customer.order.show', compact('order'));
    }
}
