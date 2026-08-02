<?php

namespace App\Services\Master;

use App\Models\StoreLevel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StoreLevelService
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return StoreLevel::query()
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): StoreLevel
    {
        return StoreLevel::create($data);
    }

    public function update(StoreLevel $storeLevel, array $data): bool
    {
        return $storeLevel->update($data);
    }

    public function delete(StoreLevel $storeLevel): ?bool
    {
        return $storeLevel->delete();
    }
}
