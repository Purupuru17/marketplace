<?php

namespace App\Http\Controllers\Store;

use App\Models\SubscriptionInvoice;
use App\Services\Store\SubscriptionInvoiceService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class SubscriptionInvoiceController extends BaseCoreController
{
    public function __construct(protected SubscriptionInvoiceService $service) {}

    public function index(Request $request)
    {
        $invoices = $this->service->paginate(
            $request->only(['search', 'status']),
            (int) $request->input('per_page', 10)
        );

        return view('store.subscription-invoice.index', compact('invoices'));
    }

    public function create()
    {
        return view('store.subscription-invoice.form', [
            'invoice' => null,
            'subscriptionOptions' => $this->service->subscriptionOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);

        $this->service->create($validated);

        return redirect()
            ->route('toko.subscription-invoice.index')
            ->with('success', 'Invoice subscription berhasil ditambahkan.');
    }

    public function edit(SubscriptionInvoice $subscriptionInvoice)
    {
        $subscriptionInvoice->load(['subscription.store', 'subscription.storeLevel']);

        return view('store.subscription-invoice.form', [
            'invoice' => $subscriptionInvoice,
            'subscriptionOptions' => $this->service->subscriptionOptions(),
        ]);
    }

    public function update(Request $request, SubscriptionInvoice $subscriptionInvoice)
    {
        $validated = $this->validateInvoice($request);

        $this->service->update($subscriptionInvoice, $validated);

        return redirect()
            ->route('toko.subscription-invoice.index')
            ->with('success', 'Invoice subscription berhasil diperbarui.');
    }

    public function destroy(SubscriptionInvoice $subscriptionInvoice)
    {
        $this->service->delete($subscriptionInvoice);

        return redirect()
            ->route('toko.subscription-invoice.index')
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
