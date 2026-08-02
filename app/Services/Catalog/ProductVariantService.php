<?php

namespace App\Services\Catalog;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductVariantService
{
    public function paginate(array $filters = [], ?array $storeIds = null, int $perPage = 10): LengthAwarePaginator
    {
        return ProductVariant::query()
            ->with(['product', 'store', 'attributeValues.attribute'])
            ->when($storeIds !== null, fn ($query) => $query->whereIn('store_id', $storeIds))
            ->when(! empty($filters['store_id']), fn ($query) => $query->where('store_id', $filters['store_id']))
            ->when(! empty($filters['product_id']), fn ($query) => $query->where('product_id', $filters['product_id']))
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where('sku', 'like', '%'.$filters['search'].'%');
            })
            ->orderBy('sku')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): ProductVariant
    {
        $product = Product::findOrFail($data['product_id']);
        $data['store_id'] = $product->store_id;

        $variant = ProductVariant::create($data);

        $this->syncAttributeValues($variant, $data['attribute_value_ids'] ?? []);

        return $variant;
    }

    public function update(ProductVariant $variant, array $data): bool
    {
        if (isset($data['product_id']) && $data['product_id'] !== $variant->product_id) {
            $data['store_id'] = Product::findOrFail($data['product_id'])->store_id;
        }

        $updated = $variant->update($data);

        $this->syncAttributeValues($variant, $data['attribute_value_ids'] ?? []);

        return $updated;
    }

    public function delete(ProductVariant $variant): ?bool
    {
        return $variant->delete();
    }

    public function syncAttributeValues(ProductVariant $variant, array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids)));

        $variant->attributeValues()->sync($ids);
    }

    public function productOptions(?array $storeIds = null): array
    {
        return Product::query()
            ->when($storeIds !== null, fn ($query) => $query->whereIn('store_id', $storeIds))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function storeOptions(?array $storeIds = null): array
    {
        return Store::query()
            ->when($storeIds !== null, fn ($query) => $query->whereIn('id', $storeIds))
            ->orderBy('store_name')
            ->pluck('store_name', 'id')
            ->all();
    }

    public function attributeGroups(): array
    {
        $groups = [];

        foreach (Attribute::with('values')->orderBy('name')->get() as $attribute) {
            $groups[$attribute->name] = $attribute->values
                ->sortBy('value')
                ->pluck('value', 'id')
                ->all();
        }

        return $groups;
    }
}
