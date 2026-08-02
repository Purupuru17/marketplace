<?php

namespace App\Services\Store;

use App\Models\Product;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    public function paginate(User $user, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Promotion::query()
            ->with(['store', 'products'])
            ->when(! $user->hasRole('Administrator'), function ($query) use ($user) {
                $storeIds = $user->stores()->pluck('id');

                $query->where(function ($q) use ($storeIds) {
                    $q->where('source', 'store')->whereIn('store_id', $storeIds)
                        ->orWhere('source', 'platform');
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['search']), fn ($query) => $query->where('name', 'like', '%'.$filters['search'].'%'))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(User $user, array $data, array $productIds = []): Promotion
    {
        $data = $this->normalizeData($user, $data);

        $promotion = Promotion::create($data);
        $promotion->products()->sync($productIds);

        return $promotion->load('products');
    }

    public function update(User $user, Promotion $promotion, array $data, array $productIds = []): Promotion
    {
        $this->authorize($user, $promotion);

        $data = $this->normalizeData($user, $data, $promotion);

        $promotion->update($data);
        $promotion->products()->sync($productIds);

        return $promotion->refresh()->load('products');
    }

    public function delete(User $user, Promotion $promotion): ?bool
    {
        $this->authorize($user, $promotion);

        return $promotion->delete();
    }

    public function authorize(User $user, Promotion $promotion): void
    {
        $ownsStore = $promotion->source === 'store'
            && $user->stores()->whereKey($promotion->store_id)->exists();

        abort_unless($user->hasRole('Administrator') || $ownsStore, 403);
    }

    public function productOptions(User $user): array
    {
        return Product::query()
            ->with('variants')
            ->when(! $user->hasRole('Administrator'), function ($query) use ($user) {
                $query->whereIn('store_id', $user->stores()->pluck('id'));
            })
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Product $product) => [$product->id => "{$product->name}"])
            ->all();
    }

    public function storeOptions(User $user): array
    {
        $stores = $user->hasRole('Administrator')
            ? Store::orderBy('store_name')->get()
            : $user->stores()->orderBy('store_name')->get();

        return $stores->pluck('store_name', 'id')->all();
    }

    protected function normalizeData(User $user, array $data, ?Promotion $promotion = null): array
    {
        if (! $user->hasRole('Administrator')) {
            $storeId = $data['store_id'] ?? $user->stores()->value('id');
            abort_unless($user->stores()->whereKey($storeId)->exists(), 403);

            $data['source'] = 'store';
            $data['store_id'] = $storeId;
        } elseif (($data['source'] ?? 'store') === 'platform') {
            $data['store_id'] = null;
        } elseif (empty($data['store_id'])) {
            throw ValidationException::withMessages([
                'store_id' => 'Toko wajib dipilih untuk promo dari toko.',
            ]);
        }

        return $data;
    }
}
