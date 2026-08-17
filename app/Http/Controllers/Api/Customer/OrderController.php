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
        $invoices = Invoice::query()
            ->where('customer_id', $request->user('api-customer')->id)
            ->with(['orders.store', 'orders.items'])
            ->orderByDesc('created_at')
            ->paginate(10);

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

    public function show(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->customer_id === $request->user('api-customer')->id, 403);

        return response()->json([
            'data' => $this->payload($invoice->load(['orders.items.rating', 'orders.store', 'payments'])),
        ]);
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
                ])->values(),
            ])->values(),
        ];
    }
}
