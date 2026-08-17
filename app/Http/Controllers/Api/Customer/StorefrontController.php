<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Customer\StorefrontService;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function __construct(protected StorefrontService $service) {}

    public function stores(Request $request)
    {
        $stores = $this->service->stores(
            $request->only(['search']),
            (int) $request->input('per_page', 12),
        );

        return response()->json([
            'data' => [
                'items' => $stores->map(fn ($store) => [
                    'id' => $store->id,
                    'name' => $store->store_name,
                    'slug' => $store->slug,
                    'code' => $store->store_code,
                    'rate_per_km' => (float) $store->rate_per_km,
                    'min_free_distance_km' => (float) $store->min_free_distance_km,
                    'products_count' => (int) $store->products_count,
                    'location_node' => $store->locationNode?->name,
                ])->values(),
                'pagination' => $this->pagination($stores),
            ],
        ]);
    }

    public function products(Request $request)
    {
        $products = $this->service->products(
            $request->only(['search', 'category_id', 'store_id']),
            (int) $request->input('per_page', 12),
        );

        return response()->json([
            'data' => [
                'items' => $products->map(fn (Product $product) => $this->productPayload($product))->values(),
                'pagination' => $this->pagination($products),
            ],
        ]);
    }

    public function categories()
    {
        return response()->json([
            'data' => [
                'items' => $this->service->categories(),
            ],
        ]);
    }

    public function store(Request $request, string $store)
    {
        $model = $this->service->store($store);
        $products = $this->service->storeProducts($model, $request->input('search'));

        return response()->json([
            'data' => [
                'id' => $model->id,
                'name' => $model->store_name,
                'slug' => $model->slug,
                'description' => $model->description,
                'phone' => $model->phone,
                'email' => $model->email,
                'status' => $model->status,
                'location_node' => $model->locationNode?->name,
                'operating_hours' => $model->operatingHours->map(fn ($hour) => [
                    'day' => $hour->day,
                    'opens_at' => $hour->opens_at,
                    'closes_at' => $hour->closes_at,
                ])->values(),
                'products' => [
                    'items' => $products->map(fn (Product $product) => $this->productPayload($product))->values(),
                    'pagination' => $this->pagination($products),
                ],
            ],
        ]);
    }

    public function product(string $store, string $product)
    {
        $model = $this->service->store($store);

        return response()->json([
            'data' => $this->productDetailPayload($this->service->product($model, $product)),
        ]);
    }

    protected function productPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'status' => $product->status,
            'store' => [
                'id' => $product->store?->id,
                'name' => $product->store?->store_name,
                'slug' => $product->store?->slug,
            ],
            'category' => [
                'id' => $product->category?->id,
                'name' => $product->category?->name,
                'slug' => $product->category?->slug,
            ],
            'variants' => $product->variants->map(fn ($variant) => $this->variantPayload($variant))->values(),
            'promotions' => $product->promotions->map(fn ($promo) => [
                'id' => $promo->id,
                'name' => $promo->name,
                'type' => $promo->type,
                'value' => (float) $promo->value,
                'source' => $promo->source,
                'starts_at' => $promo->starts_at?->toIso8601String(),
                'ends_at' => $promo->ends_at?->toIso8601String(),
            ])->values(),
        ];
    }

    protected function productDetailPayload(Product $product): array
    {
        $payload = $this->productPayload($product);
        $ratings = $product->ratings;

        $payload['rating'] = [
            'average' => $ratings->isEmpty() ? null : round((float) $ratings->avg('rating'), 1),
            'count' => $ratings->count(),
        ];

        $payload['ratings'] = $ratings->map(fn ($rating) => [
            'id' => $rating->id,
            'rating' => $rating->rating,
            'review' => $rating->review,
            'customer' => $rating->customer?->name,
            'created_at' => $rating->created_at?->toIso8601String(),
        ])->values();

        return $payload;
    }

    protected function variantPayload($variant): array
    {
        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => (float) $variant->price,
            'stock' => (int) $variant->stock,
            'weight_grams' => (int) $variant->weight_grams,
            'status' => $variant->status,
            'attributes' => $variant->attributeValues->map(fn ($value) => [
                'attribute' => $value->attribute?->name,
                'value' => $value->value,
            ])->values(),
        ];
    }

    protected function pagination($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
