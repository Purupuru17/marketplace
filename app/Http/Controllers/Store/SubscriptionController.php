<?php

namespace App\Http\Controllers\Store;

use App\Models\Subscription;
use App\Services\Store\SubscriptionService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
use Illuminate\Http\Request;

class SubscriptionController extends BaseCoreController
{
    private $module = 'toko.subscription';

    protected $view = 'store.subscription';

    public function __construct(protected SubscriptionService $service) {}

    public function index(Request $request)
    {
        $columns = [
            ['key' => 'store', 'label' => 'Toko', 'html' => true, 'align' => 'left'],
            ['key' => 'level', 'label' => 'Level', 'html' => true, 'align' => 'center'],
            ['key' => 'periode', 'label' => 'Periode', 'align' => 'center'],
            ['key' => 'auto_renew', 'label' => 'Auto Renew', 'sortable' => true, 'html' => true, 'align' => 'center'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'title' => 'Subscription',
            'subtitle' => 'Data Subscription',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Subscription']],

            'columns' => $columns,
        ];

        return view($this->view.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,
            'storeOptions' => $this->service->storeOptions(),
            'levelOptions' => $this->service->levelOptions(),

            'title' => 'Tambah Subscription',
            'subtitle' => 'Atur langganan toko',

            'action' => route($this->module.'.store'),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Subscription', route($this->module.'.index')], ['Tambah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSubscription($request);

        $this->service->create($validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Subscription berhasil ditambahkan, invoice otomatis dibuat.');
    }

    public function edit(Subscription $subscription)
    {
        $subscription->load(['store', 'storeLevel']);

        $compact = [
            'formData' => $subscription,
            'storeOptions' => $this->service->storeOptions(),
            'levelOptions' => $this->service->levelOptions(),

            'title' => 'Edit Subscription',
            'subtitle' => 'Atur langganan toko',

            'action' => route($this->module.'.update', $subscription->id),
            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Subscription', route($this->module.'.index')], ['Ubah Data']],
        ];

        return view($this->view.'.form', $compact);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $this->validateSubscription($request);

        $this->service->update($subscription, $validated);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Subscription berhasil diperbarui.');
    }

    public function destroy(Subscription $subscription)
    {
        $this->service->delete($subscription);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Subscription berhasil dihapus.');
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
            Subscription::with(['store', 'storeLevel']),
            [
                fn ($query, $search) => $query->orWhereHas('store', fn ($q) => $q->where('store_name', 'like', '%'.$search.'%')),
            ],
            function ($query) use ($request) {
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }
            },
            function (Subscription $item) {
                $status = match ($item->status) {
                    'active' => Render::badge('success', 'Active'),
                    'expired' => Render::badge('warning', 'Expired'),
                    default => Render::badge('danger', 'Cancelled'),
                };

                return [
                    'id' => $item->id,
                    'store' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($item->store->store_name ?? '-').'</p><p class="text-xs text-gray-500 dark:text-gray-400">'.e($item->store->store_code ?? '').'</p>',
                    'name_plain' => $item->store->store_name ?? '-',
                    'level' => $item->storeLevel ? Render::badge('blue', $item->storeLevel->name) : '-',
                    'periode' => $item->starts_at->format('d M Y').' - '.$item->ends_at->format('d M Y'),
                    'auto_renew' => $item->auto_renew ? Render::badge('success', 'Ya') : Render::badge('gray', 'Tidak'),
                    'status' => $status,
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $item->id) : null,
                    'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $item->id) : null,
                ];
            },
            ['auto_renew', 'status', 'starts_at', 'ends_at', 'created_at']
        );
    }

    protected function validateSubscription(Request $request): array
    {
        return $request->validate([
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'store_level_id' => ['required', 'integer', 'exists:store_levels,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:active,expired,cancelled'],
            'auto_renew' => ['nullable', 'boolean'],
        ]);
    }
}
