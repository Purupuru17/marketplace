<?php

namespace App\Services\Customer;

use App\Models\Product;
use App\Models\Store;

class StorefrontService
{
    public function stores(array $filters = [], int $perPage = 12)
    {
        return Store::query()
            ->where('status', 'active')
            ->withCount('products')
            ->with('locationNode')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('store_name', 'like', "%{$search}%"))
            ->orderBy('store_name')
            ->paginate($perPage);
    }

    public function store(string $slug): Store
    {
        return Store::where('slug', $slug)
            ->where('status', 'active')
            ->with(['locationNode', 'operatingHours'])
            ->firstOrFail();
    }

    public function storeProducts(Store $store, ?string $search = null)
    {
        return $store->products()
            ->where('status', 'active')
            ->when($search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->with([
                'category',
                'variants' => fn ($q) => $q->where('status', 'active')->with('attributeValues.attribute'),
            ])
            ->orderBy('name')
            ->paginate(12);
    }

    public function product(Store $store, string $slug): Product
    {
        return $store->products()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->with([
                'category',
                'variants' => fn ($q) => $q->where('status', 'active')->with('attributeValues.attribute'),
            ])
            ->firstOrFail();
    }
}
