<?php

namespace App\Services\Master;

use App\Models\LocationNode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LocationNodeService
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return LocationNode::query()
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): LocationNode
    {
        return LocationNode::create($data);
    }

    public function update(LocationNode $locationNode, array $data): bool
    {
        return $locationNode->update($data);
    }

    public function delete(LocationNode $locationNode): ?bool
    {
        return $locationNode->delete();
    }
}
