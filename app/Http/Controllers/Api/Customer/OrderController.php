<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $invoices = Invoice::query()
            ->where('customer_id', $request->user('api-customer')->id)
            ->with(['orders.store', 'orders.items.product.images', 'orders.items.rating', 'orders.payments'])
            ->when($status, fn ($q) => $q->whereHas('orders', fn ($oq) => $oq->where('status', $status)))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'data' => [
                'items' => $invoices->map(fn (Invoice $invoice) => $this->payload($invoice))->values(),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->invoice->customer_id === $request->user('api-customer')->id, 403);

        $order->load([
            'items.product.images',
            'items.rating',
            'store.level',
            'store.locationNode',
            'payments',
            'statusHistories',
            'invoice.orders.store',
            'invoice.orders.items',
        ]);

        return response()->json([
            'data' => $this->orderPayload($order),
        ]);
    }

    protected function orderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'status' => $order->status,
            'fulfillment_type' => $order->fulfillment_type,
            'total' => (float) $order->total,
            'store' => [
                'id' => $order->store?->id,
                'name' => $order->store?->store_name,
                'level' => $order->store?->level?->name,
                'location_node' => $order->store?->locationNode?->name,
            ],
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name_snapshot,
                'sku' => $item->sku_snapshot,
                'qty' => $item->qty,
                'final_price' => (float) ($item->final_price_snapshot ?? $item->subtotal_snapshot),
                'image_url' => $item->product?->images?->sortBy('position')->first()
                    ? url(\Illuminate\Support\Facades\Storage::disk('public')->url($item->product->images->sortBy('position')->first()->path))
                    : null,
                'rating' => $item->rating ? [
                    'id' => $item->rating->id,
                    'rating' => $item->rating->rating,
                    'review' => $item->rating->review,
                ] : null,
            ])->values(),
            'payments' => $order->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'provider' => $payment->provider,
                'payment_method' => $payment->payment_method,
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
            ])->values(),
            'status_history' => $order->statusHistories->sortBy('created_at')->map(fn ($history) => [
                'status_to' => $history->status_to,
                'notes' => $history->notes,
                'created_at' => $history->created_at?->toIso8601String(),
            ])->values(),
            'invoice' => [
                'id' => $order->invoice?->id,
                'invoice_no' => $order->invoice?->invoice_no,
                'orders' => $order->invoice?->orders?->map(fn ($o) => [
                    'id' => $o->id,
                    'order_no' => $o->order_no,
                    'store' => $o->store?->store_name,
                    'status' => $o->status,
                ])->values(),
            ],
        ];
    }

    protected function payload(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'subtotal' => (float) $invoice->subtotal,
            'total_discount' => (float) $invoice->total_discount,
            'total_shipping_cost' => (float) $invoice->total_shipping_cost,
            'grand_total' => (float) $invoice->grand_total,
            'status' => $invoice->status,
            'created_at' => $invoice->created_at?->toIso8601String(),
            'orders' => $invoice->orders->map(fn (Order $order) => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'store' => [
                    'id' => $order->store?->id,
                    'name' => $order->store?->store_name,
                ],
                'status' => $order->status,
                'total' => (float) $order->total,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name_snapshot,
                    'sku' => $item->sku_snapshot,
                    'qty' => $item->qty,
                    'final_price' => (float) ($item->final_price_snapshot ?? $item->subtotal_snapshot),
                    'image_url' => $item->product?->images?->sortBy('position')->first()
                        ? url(\Illuminate\Support\Facades\Storage::disk('public')->url($item->product->images->sortBy('position')->first()->path))
                        : null,
                    'rating' => $item->rating ? [
                        'id' => $item->rating->id,
                        'rating' => $item->rating->rating,
                        'review' => $item->rating->review,
                    ] : null,
                ])->values(),
                'status_history' => $order->statusHistories->sortBy('created_at')->map(fn ($history) => [
                    'status_to' => $history->status_to,
                    'notes' => $history->notes,
                    'created_at' => $history->created_at?->toIso8601String(),
                ])->values(),
            ])->values(),
        ];
    }
}
