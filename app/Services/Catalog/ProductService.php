<?php

namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\Store;
use App\Services\Master\CategoryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(protected CategoryService $categoryService) {}

    public function paginate(array $filters = [], ?array $storeIds = null, int $perPage = 10): LengthAwarePaginator
    {
        return Product::query()
            ->with(['store', 'category'])
            ->when($storeIds !== null, fn ($query) => $query->whereIn('store_id', $storeIds))
            ->when(! empty($filters['store_id']), fn ($query) => $query->where('store_id', $filters['store_id']))
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%'.$filters['search'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['search'].'%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Product
    {
        $data['slug'] = $this->uniqueSlug($data['store_id'], $data['name']);

        return Product::create($data);
    }

    public function update(Product $product, array $data): bool
    {
        if (($data['name'] ?? null) !== $product->name) {
            $data['slug'] = $this->uniqueSlug($product->store_id, $data['name'], $product->id);
        }

        return $product->update($data);
    }

    public function delete(Product $product): ?bool
    {
        return $product->delete();
    }

    public function uniqueSlug(string $storeId, string $name, ?string $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $base = $slug;
        $counter = 2;

        while (Product::query()
            ->where('store_id', $storeId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public function storeOptions(?array $storeIds = null): array
    {
        return Store::query()
            ->when($storeIds !== null, fn ($query) => $query->whereIn('id', $storeIds))
            ->orderBy('store_name')
            ->pluck('store_name', 'id')
            ->all();
    }

    public function categoryOptions(): array
    {
        return $this->categoryService->options();
    }
}
