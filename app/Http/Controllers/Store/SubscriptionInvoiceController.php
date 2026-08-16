<?php

namespace App\Http\Controllers\Store;

use App\Models\SubscriptionInvoice;
use App\Services\Store\SubscriptionInvoiceService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;

class SubscriptionInvoiceController extends BaseCoreController
{
    private $module = 'toko.subscription-invoice';

    protected $view = 'store.subscription-invoice';

    public function __construct(protected SubscriptionInvoiceService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'invoice_no', 'label' => 'No Invoice', 'html' => true, 'align' => 'left'],
            ['key' => 'store', 'label' => 'Toko', 'align' => 'left'],
            ['key' => 'amount', 'label' => 'Jumlah', 'sortable' => true, 'html' => true, 'align' => 'right'],
            ['key' => 'due_at', 'label' => 'Jatuh Tempo', 'sortable' => true, 'align' => 'center'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'title' => 'Invoice Subscription',
            'subtitle' => 'Data Invoice Subscription',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Invoice Subscription']],

            'columns' => $columns,
        ];

        return view($this->view.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,
            'subscriptionOptions' => $this->service->subscriptionOptions(),

            'title' => 'Tambah Invoice Subscription',
            'subtitle' => 'Atur invoice langganan toko',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Invoice Subscription', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Invoice subscription berhasil ditambahkan.');
    }

    public function edit(SubscriptionInvoice $subscriptionInvoice)
    {
        $subscriptionInvoice->load(['subscription.store', 'subscription.storeLevel']);

        $compact = [
            'formData' => $subscriptionInvoice,
            'subscriptionOptions' => $this->service->subscriptionOptions(),

            'title' => 'Edit Invoice Subscription',
            'subtitle' => 'Atur invoice langganan toko',

            'action' => route($this->module.'.update', $subscriptionInvoice->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Invoice Subscription', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function update(Request $request, SubscriptionInvoice $subscriptionInvoice)
    {
        $validated = $this->validateInvoice($request);

        $this->service->update($subscriptionInvoice, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Invoice subscription berhasil diperbarui.');
    }

    public function destroy(SubscriptionInvoice $subscriptionInvoice)
    {
        $this->service->delete($subscriptionInvoice);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Invoice subscription berhasil dihapus.');
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
        return DataTableService::process(
            $request,
            SubscriptionInvoice::with(['subscription.store', 'subscription.storeLevel']),
            [
                fn ($query, $search) => $query->orWhere('invoice_no', 'like', '%'.$search.'%')
                    ->orWhereHas('subscription.store', fn ($q) => $q->where('store_name', 'like', '%'.$search.'%')),
            ],
            function ($query) use ($request) {
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }
            },
            function (SubscriptionInvoice $item) {
                $status = match ($item->status) {
                    'paid' => Render::badge('success', 'Paid'),
                    'overdue' => Render::badge('danger', 'Overdue'),
                    default => Render::badge('warning', 'Pending'),
                };

                return [
                    'id' => $item->id,
                    'invoice_no' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($item->invoice_no).'</p><p class="text-xs text-gray-500 dark:text-gray-400">'.e($item->subscription->storeLevel->name ?? '-').'</p>',
                    'name_plain' => $item->invoice_no,
                    'store' => e($item->subscription->store->store_name ?? '-'),
                    'amount' => '<span class="font-semibold text-gray-900 dark:text-white">Rp '.number_format($item->amount, 0, ',', '.').'</span>',
                    'due_at' => $item->due_at->format('d M Y'),
                    'status' => $status,
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['amount', 'due_at', 'status', 'created_at']
        );
    }

    protected function validateInvoice(Request $request): array
    {
        return $request->validate([
            'subscription_id' => ['required', 'uuid', 'exists:subscriptions,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_at' => ['required', 'date'],
            'status' => ['required', 'in:pending,paid,overdue'],
        ]);
    }
}
