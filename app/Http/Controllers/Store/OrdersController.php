<?php

namespace App\Http\Controllers\Store;

use App\Models\Order;
use App\Services\Customer\PaymentService;
use App\Services\Store\StoreOrderService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrdersController extends BaseCoreController
{
    private $module = 'toko.order';

    protected $view = 'store.order';

    public function __construct(protected StoreOrderService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'order_no', 'label' => 'Pesanan', 'html' => true, 'align' => 'left'],
            ['key' => 'customer', 'label' => 'Pembeli', 'align' => 'left'],
            ['key' => 'items', 'label' => 'Item', 'align' => 'center'],
            ['key' => 'total', 'label' => 'Total', 'sortable' => true, 'html' => true, 'align' => 'right'],
            ['key' => 'payment', 'label' => 'Pembayaran', 'align' => 'center'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
            ['key' => 'created_at', 'label' => 'Tanggal', 'sortable' => true, 'align' => 'center'],
        ];

        $stores = Auth::user()->stores()->orderBy('store_name')->get();

        $compact = [
            'stores' => $stores,
            'statusLabels' => StoreOrderService::STATUS_LABELS,

            'title' => 'Pesanan',
            'subtitle' => 'Data Pesanan',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Pesanan']],

            'columns' => $columns,
        ];

        return view($this->view.'.index', $compact);
    }

    public function show(Order $order)
    {
        abort_unless($this->service->authorize(Auth::user(), $order), 403);

        $order->load(['items', 'store', 'customer', 'payments', 'statusHistories']);

        return view($this->view.'.show', [
            'order' => $order,
            'title' => 'Detail Pesanan',
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Pesanan', route($this->module.'.index')], [$order->order_no]],
            'transitions' => StoreOrderService::TRANSITIONS,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->service->transition(
                Auth::user(),
                $order,
                $validated['status'],
                $validated['notes'] ?? null
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route($this->module.'.show', $order->id)
            ->with('success', 'Status pesanan diperbarui.');
    }

    public function markPaid(Request $request, Order $order)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->service->markPaymentPaid(
                Auth::user(),
                $order,
                $validated['notes'] ?? null
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route($this->module.'.show', $order->id)
            ->with('success', 'Pembayaran dikonfirmasi lunas.');
    }

    public function ajax(Request $request)
    {
        $type = $request->input('type');
        $source = $request->input('source');

        return match ($type) {
            'table' => match ($source) {
                'index' => $this->tableIndex($request),
                default => response()->json(['status' => 'error', 'message' => 'Sumber data tidak valid.'], 400),
            },
            default => response()->json(['status' => 'error', 'message' => 'Aksi tidak valid.'], 400),
        };
    }

    private function tableIndex(Request $request)
    {
        $statusLabels = StoreOrderService::STATUS_LABELS;
        $badgeStyles = [
            'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            'processing' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
            'shipped' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
            'completed' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
            'cancelled' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
        ];

        return DataTableService::process(
            $request,
            $this->service->query(Auth::user()),
            ['orders.order_no', 'orders.invoice_id'],
            function ($query) use ($request) {
                if ($request->filled('status')) {
                    $query->where('orders.status', $request->input('status'));
                }
                if ($request->filled('store_id')) {
                    $query->where('orders.store_id', $request->input('store_id'));
                }
            },
            function (Order $order) use ($statusLabels, $badgeStyles) {
                $method = $order->payments->first()?->payment_method;

                return [
                    'id' => $order->id,
                    'order_no' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($order->order_no).'</p><p class="text-xs text-gray-500 dark:text-gray-400">'.e($order->store->store_name ?? '-').'</p>',
                    'name_plain' => $order->order_no,
                    'customer' => '<p class="text-gray-700 dark:text-gray-300">'.e($order->customer?->name ?? '-').'</p><p class="text-xs text-gray-500 dark:text-gray-400">'.e($order->customer?->phone ?? '').'</p>',
                    'items' => $order->items->count(),
                    'total' => '<span class="font-semibold text-gray-900 dark:text-white">Rp '.number_format((float) $order->total, 0, ',', '.').'</span>',
                    'payment' => PaymentService::METHODS[$method] ?? '-',
                    'status' => '<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold '.($badgeStyles[$order->status] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300').'">'.($statusLabels[$order->status] ?? ucfirst($order->status)).'</span>',
                    'created_at' => $order->created_at->format('d M Y H:i'),
                    'detail_url' => auth()->user()->can($this->resourceName().'.detail') ? route($this->module.'.show', $order->id) : null,
                    'edit_url' => null,
                    'delete_url' => null,
                ];
            },
            ['orders.order_no', 'orders.total', 'orders.status', 'orders.created_at', 'orders.store_id']
        );
    }
}
