<?php

namespace App\Services\Store;

use App\Models\LocationNode;
use App\Models\Store;
use App\Models\StoreLevel;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class StoreService
{
    public const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Store::query()
            ->with(['owner', 'level', 'locationNode'])
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('store_name', 'like', '%'.$filters['search'].'%')
                        ->orWhere('store_code', 'like', '%'.$filters['search'].'%');
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('store_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, array $operatingHours = []): Store
    {
        $data['store_code'] = $this->uniqueCode();
        $data['slug'] = $this->uniqueSlug($data['store_name']);

        $store = Store::create($data);

        $this->syncOperatingHours($store, $operatingHours);

        return $store;
    }

    public function update(Store $store, array $data, array $operatingHours = []): bool
    {
        if (($data['store_name'] ?? null) !== $store->store_name) {
            $data['slug'] = $this->uniqueSlug($data['store_name'], $store->id);
        }

        $updated = $store->update($data);

        $this->syncOperatingHours($store, $operatingHours);

        return $updated;
    }

    public function delete(Store $store): ?bool
    {
        return $store->delete();
    }

    public function syncOperatingHours(Store $store, array $operatingHours): void
    {
        foreach (self::DAYS as $day) {
            $value = $operatingHours[$day] ?? [];

            $store->operatingHours()->updateOrCreate(
                ['store_id' => $store->id, 'day' => $day],
                [
                    'is_open' => (bool) ($value['is_open'] ?? false),
                    'opens_at' => ! empty($value['opens_at']) ? $value['opens_at'] : null,
                    'closes_at' => ! empty($value['closes_at']) ? $value['closes_at'] : null,
                ]
            );
        }
    }

    public function uniqueCode(): string
    {
        do {
            $code = 'STR-'.strtoupper(Str::random(6));
        } while (Store::where('store_code', $code)->exists());

        return $code;
    }

    public function uniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $base = $slug;
        $counter = 2;

        while (Store::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public function userOptions(): array
    {
        return User::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    public function levelOptions(): array
    {
        return StoreLevel::query()->orderBy('sort_order')->pluck('name', 'id')->all();
    }

    public function nodeOptions(): array
    {
        return LocationNode::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
