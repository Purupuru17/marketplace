<?php

namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use App\Services\Master\CategoryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function create(array $data, array|UploadedFile|null $uploads = null): Product
    {
        $data['slug'] = $this->uniqueSlug($data['store_id'], $data['name']);

        $product = Product::create($data);
        $this->syncUploads($product, $this->normalizeUploads($uploads));

        return $product;
    }

    public function update(Product $product, array $data, array|UploadedFile|null $uploads = null, ?string $primaryId = null, array $removeIds = []): bool
    {
        if (($data['name'] ?? null) !== $product->name) {
            $data['slug'] = $this->uniqueSlug($product->store_id, $data['name'], $product->id);
        }

        $updated = $product->update($data);

        $this->syncUploads($product, $this->normalizeUploads($uploads));
        $this->applyPrimary($product, $primaryId);
        $this->removeImages($product, $removeIds);

        return $updated;
    }

    public function delete(Product $product): ?bool
    {
        $product->images()->each(fn (ProductImage $image) => $this->deleteUpload($image));

        return $product->delete();
    }

    protected function normalizeUploads(array|UploadedFile|null $uploads): array
    {
        if ($uploads instanceof UploadedFile) {
            return [$uploads];
        }

        return array_values(array_filter($uploads ?? []));
    }

    protected function syncUploads(Product $product, array $files): void
    {
        foreach ($files as $file) {
            $path = Storage::disk('public')->putFile('products', $file);

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'position' => (int) $product->images()->max('position') + 1,
                'is_primary' => ! $product->images()->where('is_primary', true)->exists(),
            ]);
        }
    }

    protected function applyPrimary(Product $product, ?string $primaryId): void
    {
        $target = $primaryId ? $product->images()->whereNull('variant_id')->find($primaryId) : null;

        if (! $target) {
            return;
        }

        $product->images()->whereNull('variant_id')->update(['is_primary' => false]);
        $target->update(['is_primary' => true]);
    }

    protected function removeImages(Product $product, array $removeIds): void
    {
        foreach ($product->images()->whereNull('variant_id')->whereIn('id', $removeIds)->get() as $image) {
            $this->deleteUpload($image);
        }

        if (! $product->images()->whereNull('variant_id')->where('is_primary', true)->exists()) {
            $product->images()->whereNull('variant_id')->orderBy('position')->first()?->update(['is_primary' => true]);
        }
    }

    protected function deleteUpload(ProductImage $image): void
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
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
