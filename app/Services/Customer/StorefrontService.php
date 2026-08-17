<?php

namespace App\Services\Customer;

use App\Models\Category;
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

    public function products(array $filters = [], int $perPage = 12)
    {
        return Product::query()
            ->where('status', 'active')
            ->with([
                'category',
                'store',
                'promotions',
                'variants' => fn ($q) => $q->where('status', 'active')->with('attributeValues.attribute'),
            ])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['store_id'] ?? null, fn ($q, $id) => $q->where('store_id', $id))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function categories()
    {
        return Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
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
                'promotions',
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
                'promotions',
                'ratings' => fn ($q) => $q->where('status', 'active')->with('customer'),
                'variants' => fn ($q) => $q->where('status', 'active')->with('attributeValues.attribute'),
            ])
            ->firstOrFail();
    }
}
