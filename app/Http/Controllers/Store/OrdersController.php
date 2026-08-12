<?php

namespace App\Http\Controllers\Store;

use App\Models\Order;
use App\Services\Store\StoreOrderService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
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
        $orders = $this->service->paginate(
            Auth::user(),
            $request->only(['search', 'status', 'store_id']),
            (int) $request->input('per_page', 10)
        );

        $stores = Auth::user()->stores()->orderBy('store_name')->get();

        $compact = [
            'listData'      => $orders,
            'stores'        => $stores,
            'statusLabels'  => StoreOrderService::STATUS_LABELS,

            'title'         => 'Pesanan',
            'subtitle'      => 'Data Pesanan',

            'module'        => $this->module,
            'rolesName'     => $this->resourceName(),
            'breadcrumb'    => [['Beranda', route('dashboard')], ['Toko'], ['Pesanan']],
        ];

        return view($this->view.'.index', $compact);
    }

    public function show(Order $order)
    {
        abort_unless($this->service->authorize(Auth::user(), $order), 403);

        $order->load(['items', 'store', 'customer', 'invoice.payments', 'statusHistories']);

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
}
