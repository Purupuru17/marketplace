<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\LocationNode;
use App\Models\Product;
use App\Models\Store;
use App\Services\Customer\StorefrontService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
                'items' => $stores->map(fn ($store) => $this->storeItemPayload($store))->values(),
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

    public function locationNodes()
    {
        return response()->json([
            'data' => [
                'items' => LocationNode::query()
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
        ]);
    }

    public function home()
    {
        $now = now();
        $discount = Product::query()
            ->where('status', 'active')
            ->whereHas('promotions', fn ($q) => $q->where('status', 'active')
                ->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>', $now)))
            ->with($this->eager())
            ->latest()
            ->limit(10)
            ->get();

        $bestIds = DB::table('product_variants')
            ->join('order_items', 'order_items.variant_id', '=', 'product_variants.id')
            ->selectRaw('product_variants.product_id, SUM(order_items.qty) as sold')
            ->groupBy('product_variants.product_id')
            ->orderByDesc('sold')
            ->limit(10)
            ->pluck('product_id')
            ->all();

        $bestSeller = empty($bestIds)
            ? collect()
            : Product::query()
                ->whereIn('id', $bestIds)
                ->with($this->eager())
                ->get()
                ->sortBy(fn (Product $p) => array_search($p->id, $bestIds))
                ->values();

        $stores = Store::query()
            ->where('status', 'active')
            ->withCount('products')
            ->with('locationNode')
            ->orderByDesc('products_count')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'discount_products' => $discount->map(fn (Product $p) => $this->productPayload($p))->values(),
                'best_seller_products' => $bestSeller->map(fn (Product $p) => $this->productPayload($p))->values(),
                'stores' => $stores->map(fn ($store) => $this->storeItemPayload($store))->values(),
            ],
        ]);
    }

    public function store(Request $request, string $store)
    {
        $customer = $request->user('api-customer');
        $data = $this->service->store($store, $customer);
        $model = $data['store'];
        $products = $this->service->storeProducts(
            $model,
            $request->input('search'),
            ['category_id' => $request->input('category_id'), 'sort' => $request->input('sort', 'default')]
        );

        return response()->json([
            'data' => [
                'id' => $model->id,
                'name' => $model->store_name,
                'slug' => $model->slug,
                'description' => $model->description,
                'phone' => $model->phone,
                'email' => $model->email,
                'status' => $model->status,
                'level' => $model->level?->name,
                'location_node' => $model->locationNode?->name,
                'logo_url' => $this->mediaUrl($model->logo),
                'banner_url' => $this->mediaUrl($model->banner),
                'bank' => [
                    'bank_name' => $model->bank_name,
                    'account_number' => $model->account_number,
                    'account_name' => $model->account_name,
                ],
                'operating_hours' => $model->operatingHours->map(fn ($hour) => [
                    'day' => $hour->day,
                    'opens_at' => $hour->opens_at,
                    'closes_at' => $hour->closes_at,
                ])->values(),
                'shipping' => $data['shipping'],
                'avg_rating' => $model->avg_rating,
                'rating_count' => $model->rating_count,
                'store_ratings' => $data['store_ratings']->map(fn ($r) => [
                    'id' => $r->id,
                    'rating' => $r->rating,
                    'review' => $r->review,
                    'customer' => $r->customer?->name,
                    'created_at' => $r->created_at?->toIso8601String(),
                ])->values(),
                'products' => [
                    'items' => $products->map(fn (Product $product) => $this->productPayload($product))->values(),
                    'pagination' => $this->pagination($products),
                ],
            ],
        ]);
    }

    public function product(Request $request, string $store, string $product)
    {
        $customer = $request->user('api-customer');
        $data = $this->service->product(
            $this->service->store($store, $customer)['store'],
            $product,
            $customer
        );

        $payload = $this->productDetailPayload($data['product']);
        $payload['store'] = array_merge($payload['store'] ?? [], [
            'level' => $data['store']->level?->name,
            'location_node' => $data['store']->locationNode?->name,
            'avg_rating' => $data['store']->avg_rating,
            'rating_count' => $data['store']->rating_count,
        ]);
        $payload['shipping'] = $data['shipping'];
        $payload['store_ratings'] = $data['store_ratings']->map(fn ($r) => [
            'id' => $r->id,
            'rating' => $r->rating,
            'review' => $r->review,
            'customer' => $r->customer?->name,
            'created_at' => $r->created_at?->toIso8601String(),
        ])->values();
        $payload['other_products'] = $data['other_products']->map(fn (Product $p) => $this->productPayload($p))->values();

        return response()->json([
            'data' => $payload,
        ]);
    }

    protected function eager(): array
    {
        return [
            'category',
            'store',
            'promotions',
            'images',
            'variants' => fn ($q) => $q->where('status', 'active')->with('attributeValues.attribute'),
        ];
    }

    protected function storeItemPayload($store): array
    {
        return [
            'id' => $store->id,
            'name' => $store->store_name,
            'slug' => $store->slug,
            'code' => $store->store_code,
            'rate_per_km' => (float) $store->rate_per_km,
            'min_free_distance_km' => (float) $store->min_free_distance_km,
            'products_count' => (int) $store->products_count,
            'location_node' => $store->locationNode?->name,
            'logo_url' => $this->mediaUrl($store->logo),
            'banner_url' => $this->mediaUrl($store->banner),
        ];
    }

    protected function mediaUrl(?string $path): ?string
    {
        return $path ? url(Storage::disk('public')->url($path)) : null;
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
            'images' => $product->images->whereNull('variant_id')->map(fn ($image) => [
                'id' => $image->id,
                'url' => $this->mediaUrl($image->path),
                'position' => $image->position,
            ])->values(),
            'variants' => $product->variants->map(fn ($variant) => $this->variantPayload($variant, $product->images))->values(),
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

    protected function variantPayload($variant, $images = null): array
    {
        $image = $images ? $images->firstWhere('variant_id', $variant->id) : null;

        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => (float) $variant->price,
            'stock' => (int) $variant->stock,
            'weight_grams' => (int) $variant->weight_grams,
            'status' => $variant->status,
            'image_url' => $image ? $this->mediaUrl($image->path) : null,
            'attributes' => $variant->attributeValues->map(fn ($value) => [
                'attribute' => $value->attribute?->name,
                'value' => $value->value,
            ])->values(),
        ];
    }

    public function search(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['uuid', 'exists:categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'radius' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:default,price_asc,price_desc,latest,sold'],
        ]);

        $customer = $request->user('api-customer');

        if (!empty($filters['radius']) && $customer) {
            $filters['store_ids'] = $this->filterStoresByRadius((float) $filters['radius'], $customer);
        }

        $products = $this->service->search($filters, $customer);

        return response()->json([
            'data' => [
                'items' => $products->map(fn (Product $product) => $this->productPayload($product))->values(),
                'pagination' => $this->pagination($products),
            ],
        ]);
    }

    protected function filterStoresByRadius(float $radius, $customer): array
    {
        $destination = $customer->addresses()
            ->with('locationNode')
            ->orderByDesc('is_default')
            ->first()
            ?->locationNode;

        if (!$destination) {
            return [];
        }

        return Store::query()
            ->where('status', 'active')
            ->whereNotNull('location_node_id')
            ->get()
            ->filter(function ($store) use ($destination, $radius) {
                $distance = app(\App\Services\Shipping\ShippingService::class)
                    ->estimate($store, $destination)['distance_km'];
                return $distance !== null && $distance <= $radius;
            })
            ->pluck('id')
            ->all();
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
