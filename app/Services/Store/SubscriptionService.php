<?php

namespace App\Services\Store;

use App\Models\Store;
use App\Models\StoreLevel;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SubscriptionService
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Subscription::query()
            ->with(['store', 'storeLevel'])
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->whereHas('store', fn ($q) => $q->where('store_name', 'like', '%'.$filters['search'].'%'));
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Subscription
    {
        $level = StoreLevel::findOrFail($data['store_level_id']);

        $subscription = Subscription::create($data);

        SubscriptionInvoice::create([
            'subscription_id' => $subscription->id,
            'invoice_no' => $this->uniqueInvoiceNumber(),
            'amount' => $level->price,
            'status' => 'pending',
            'due_at' => $data['starts_at'],
        ]);

        return $subscription;
    }

    public function update(Subscription $subscription, array $data): bool
    {
        $levelChanged = ($data['store_level_id'] ?? null) !== $subscription->store_level_id;

        $updated = $subscription->update($data);

        if ($levelChanged) {
            $pendingInvoice = $subscription->invoices()->where('status', 'pending')->first();

            if ($pendingInvoice) {
                $level = StoreLevel::find($data['store_level_id']);

                if ($level) {
                    $pendingInvoice->update(['amount' => $level->price]);
                }
            }
        }

        return $updated;
    }

    public function delete(Subscription $subscription): ?bool
    {
        return $subscription->delete();
    }

    public function uniqueInvoiceNumber(): string
    {
        do {
            $number = 'INV-SUB-'.date('Ymd').'-'.strtoupper(Str::random(4));
        } while (SubscriptionInvoice::where('invoice_no', $number)->exists());

        return $number;
    }

    public function storeOptions(): array
    {
        return Store::query()->orderBy('store_name')->pluck('store_name', 'id')->all();
    }

    public function levelOptions(): array
    {
        return StoreLevel::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }
}
