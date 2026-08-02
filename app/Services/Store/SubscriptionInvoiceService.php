<?php

namespace App\Services\Store;

use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SubscriptionInvoiceService
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return SubscriptionInvoice::query()
            ->with(['subscription.store', 'subscription.storeLevel'])
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where('invoice_no', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('subscription.store', fn ($q) => $q->where('store_name', 'like', '%'.$filters['search'].'%'));
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): SubscriptionInvoice
    {
        $data['invoice_no'] = $this->uniqueInvoiceNumber();
        $data['paid_at'] = ($data['status'] ?? null) === 'paid' ? now() : null;

        return SubscriptionInvoice::create($data);
    }

    public function update(SubscriptionInvoice $subscriptionInvoice, array $data): bool
    {
        $data['paid_at'] = ($data['status'] ?? null) === 'paid' ? now() : null;

        return $subscriptionInvoice->update($data);
    }

    public function delete(SubscriptionInvoice $subscriptionInvoice): ?bool
    {
        return $subscriptionInvoice->delete();
    }

    public function uniqueInvoiceNumber(): string
    {
        do {
            $number = 'INV-SUB-'.date('Ymd').'-'.strtoupper(Str::random(4));
        } while (SubscriptionInvoice::where('invoice_no', $number)->exists());

        return $number;
    }

    /**
     * @return array<string, string> id => "nama toko - level"
     */
    public function subscriptionOptions(): array
    {
        return Subscription::query()
            ->with(['store', 'storeLevel'])
            ->orderByDesc('created_at')
            ->get()
            ->mapWithKeys(fn (Subscription $subscription) => [
                $subscription->id => $subscription->store->store_name.' - '.($subscription->storeLevel->name ?? '-'),
            ])
            ->all();
    }
}
