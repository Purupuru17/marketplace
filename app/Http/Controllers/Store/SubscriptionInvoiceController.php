<?php

namespace App\Http\Controllers\Store;

use App\Models\SubscriptionInvoice;
use App\Services\Store\SubscriptionInvoiceService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class SubscriptionInvoiceController extends BaseCoreController
{
    private $module = 'toko.subscription-invoice';

    protected $view = 'store.subscription-invoice';

    public function __construct(protected SubscriptionInvoiceService $service) {}

    public function index(Request $request)
    {
        $invoices = $this->service->paginate(
            $request->only(['search', 'status']),
            (int) $request->input('per_page', 10)
        );

        $compact = [
            'listData'   => $invoices,

            'title'      => 'Invoice Subscription',
            'subtitle'   => 'Data Invoice Subscription',

            'module'     => $this->module,
            'rolesName'  => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], ['Toko'], ['Invoice Subscription']],
        ];

        return view($this->view.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData'              => null,
            'subscriptionOptions'   => $this->service->subscriptionOptions(),

            'title'                 => 'Tambah Invoice Subscription',
            'subtitle'              => 'Atur invoice langganan toko',

            'action'                => route($this->module.'.store'),
            'module'                => $this->module,
            'breadcrumb'            => [['Beranda', route('dashboard')], ['Toko'], ['Invoice Subscription', route($this->module.'.index')], ['Tambah Data']],
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
            'formData'              => $subscriptionInvoice,
            'subscriptionOptions'   => $this->service->subscriptionOptions(),

            'title'                 => 'Edit Invoice Subscription',
            'subtitle'              => 'Atur invoice langganan toko',

            'action'                => route($this->module.'.update', $subscriptionInvoice->id),
            'module'                => $this->module,
            'breadcrumb'            => [['Beranda', route('dashboard')], ['Toko'], ['Invoice Subscription', route($this->module.'.index')], ['Ubah Data']],
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
