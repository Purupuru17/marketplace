<?php

namespace App\Services\Catalog;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductVariantService
{
    public function __construct(protected StockService $stockService) {}

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

    public function create(array $data, ?UploadedFile $image = null): ProductVariant
    {
        $product = Product::findOrFail($data['product_id']);
        $data['store_id'] = $product->store_id;

        $initialStock = (int) ($data['stock'] ?? 0);
        unset($data['stock']);

        $variant = ProductVariant::create([...$data, 'stock' => 0]);

        if ($initialStock > 0) {
            $this->stockService->adjustTo($variant, $initialStock, 'Stok awal saat varian dibuat.');
        }

        $this->syncAttributeValues($variant, $data['attribute_value_ids'] ?? []);
        $this->syncVariantImage($variant, $image);

        return $variant->fresh();
    }

    public function update(ProductVariant $variant, array $data, ?UploadedFile $image = null): bool
    {
        if (isset($data['product_id']) && $data['product_id'] !== $variant->product_id) {
            $data['store_id'] = Product::findOrFail($data['product_id'])->store_id;
        }

        if (array_key_exists('stock', $data)) {
            $newStock = (int) $data['stock'];
            unset($data['stock']);
            $this->stockService->adjustTo($variant, $newStock, 'Koreksi stok manual oleh admin/toko.');
        }

        $updated = $variant->update($data);

        $this->syncAttributeValues($variant, $data['attribute_value_ids'] ?? []);
        $this->syncVariantImage($variant, $image);

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

    protected function syncVariantImage(ProductVariant $variant, ?UploadedFile $image): void
    {
        if (! $image) {
            return;
        }

        foreach ($variant->images()->get() as $existing) {
            Storage::disk('public')->delete($existing->path);
            $existing->delete();
        }

        ProductImage::create([
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'path' => Storage::disk('public')->putFile('products', $image),
            'position' => 0,
            'is_primary' => false,
        ]);
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
