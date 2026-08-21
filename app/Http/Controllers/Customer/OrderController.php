<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\Customer\RatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    public function review(Order $order)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($order->customer_id === $customer->id, 403);

        $order->load(['store', 'items.product.images', 'items.rating']);

        return view('customer.order.review', compact('order'));
    }

    public function submitReview(Request $request, Order $order, RatingService $service)
    {
        $customer = Auth::guard('customer')->user();

        abort_unless($order->customer_id === $customer->id, 403);

        $validated = $request->validate([
            'ratings' => ['required', 'array', 'min:1'],
            'ratings.*.rating' => ['required', 'integer', 'between:1,5'],
            'ratings.*.review' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $service->createMany($customer, $order, $validated['ratings']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('customer.order.review', $order)
            ->with('success', 'Terima kasih atas penilaian Anda.');
    }
}
