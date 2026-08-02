<?php

namespace App\Services\Master;

use App\Models\CustomerLevel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerLevelService
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return CustomerLevel::query()
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): CustomerLevel
    {
        return CustomerLevel::create($data);
    }

    public function update(CustomerLevel $customerLevel, array $data): bool
    {
        return $customerLevel->update($data);
    }

    public function delete(CustomerLevel $customerLevel): ?bool
    {
        return $customerLevel->delete();
    }
}
