<?php

namespace App\Http\Controllers\Store;

use App\Models\Subscription;
use App\Services\Store\SubscriptionService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class SubscriptionController extends BaseCoreController
{
    private $module = 'toko.subscription';

    protected $view = 'store.subscription';

    public function __construct(protected SubscriptionService $service) {}

    public function index(Request $request)
    {
        $subscriptions = $this->service->paginate(
            $request->only(['search', 'status']),
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData'   => $subscriptions,

            'title'      => 'Subscription',
            'subtitle'   => 'Data Subscription',

            'module'     => $this->module,
            'rolesName'  => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Subscription']],
        ];

        return view($this->view.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData'      => null,
            'storeOptions'  => $this->service->storeOptions(),
            'levelOptions'  => $this->service->levelOptions(),

            'title'         => 'Tambah Subscription',
            'subtitle'      => 'Atur langganan toko',

            'action'        => route($this->module.'.store'),
            'module'        => $this->module,
            'breadcrumb'    => [['Beranda', route('dashboard')], ['Toko'], ['Subscription', route($this->module.'.index')], ['Tambah Data']],
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
            'formData'      => $subscription,
            'storeOptions'  => $this->service->storeOptions(),
            'levelOptions'  => $this->service->levelOptions(),
            
            'title'         => 'Edit Subscription',
            'subtitle'      => 'Atur langganan toko',

            'action'        => route($this->module.'.update', $subscription->id),
            'module'        => $this->module,
            'breadcrumb'    => [['Beranda', route('dashboard')], ['Toko'], ['Subscription', route($this->module.'.index')], ['Ubah Data']],
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
