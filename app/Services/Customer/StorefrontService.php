<?php

namespace App\Services\Customer;

use App\Models\Category;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\Store;
use App\Models\StoreOperatingHour;
use App\Services\Pricing\PromotionPricingService;
use App\Services\Shipping\ShippingService;
use Carbon\CarbonInterface;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

class StorefrontService
{
    public function __construct(
        protected PromotionPricingService $pricingService,
        protected ShippingService $shippingService
    ) {}

    public function home(?Customer $customer, int $perPage = 12): array
    {
        $destination = $this->destinationNode($customer);

        $nearby = Store::query()
            ->where('status', 'active')
            ->with(['locationNode', 'operatingHours'])
            ->withCount('products')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $discount = $this->activePromoProducts()
            ->limit(10)
            ->get();

        $top = $this->topProducts()
            ->limit(10)
            ->get();

        $newStores = Store::query()
            ->where('status', 'active')
            ->withCount('products')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $explore = $this->decorateProducts($this->products([], $perPage));

        return [
            'categories' => $this->categories(),
            'nearby_stores' => $this->decorateStores($nearby, $destination),
            'discount_products' => $this->decorateProducts($discount),
            'top_products' => $this->decorateProducts($top),
            'new_stores' => $newStores,
            'explore_products' => $explore,
            'destination' => $destination,
        ];
    }

    public function search(array $filters, ?Customer $customer, int $perPage = 12)
    {
        $query = Product::query()
            ->where('status', 'active')
            ->with([
                'category',
                'store.locationNode',
                'store.level',
                'images',
                'variants' => fn ($q) => $q->where('status', 'active'),
            ]);

        if ($q = $filters['q'] ?? null) {
            $query->where('name', 'like', "%{$q}%");
        }

        if ($categoryIds = $filters['category_ids'] ?? null) {
            $query->whereIn('category_id', (array) $categoryIds);
        }

        if ($minPrice = $filters['min_price'] ?? null) {
            $query->whereRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = ?) >= ?', ['active', (float) $minPrice]);
        }

        if ($maxPrice = $filters['max_price'] ?? null) {
            $query->whereRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = ?) <= ?', ['active', (float) $maxPrice]);
        }

        if ($storeIds = $filters['store_ids'] ?? null) {
            $query->whereIn('store_id', $storeIds);
        }

        $sort = $filters['sort'] ?? 'default';
        if ($sort === 'price_asc') {
            $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = ?) ASC', ['active']);
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = ?) DESC', ['active']);
        } elseif ($sort === 'sold') {
            $query->orderByDesc(OrderItem::query()
                ->whereColumn('order_items.product_id', 'products.id')
                ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                ->selectRaw('COALESCE(SUM(order_items.qty), 0)'));
        } elseif ($sort === 'latest') {
            $query->orderByDesc('created_at');
        } else {
            $query->orderBy('name');
        }

        $products = $query->paginate($perPage);

        return $this->decorateProducts($products);
    }

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
                'store.locationNode',
                'store.level',
                'images',
                'variants' => fn ($q) => $q->where('status', 'active'),
            ])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['store_id'] ?? null, fn ($q, $id) => $q->where('store_id', $id))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function categories()
    {
        return Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    public function store(string $slug, ?Customer $customer = null): array
    {
        $store = Store::where('slug', $slug)
            ->where('status', 'active')
            ->with(['locationNode', 'operatingHours', 'level'])
            ->firstOrFail();

        $destination = $this->destinationNode($customer);
        $shipping = $this->shippingService->estimate($store, $destination);

        $store->shipping = $shipping;
        $store->open_status = $this->openStatus($store->operatingHours);

        $rating = $this->storeRating($store);
        $store->avg_rating = $rating['avg_rating'];
        $store->rating_count = $rating['rating_count'];

        $store_ratings = ProductRating::query()
            ->where('status', 'active')
            ->whereIn('product_id', $store->products()->where('status', 'active')->pluck('products.id'))
            ->with('customer')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return [
            'store' => $store,
            'shipping' => $shipping,
            'store_ratings' => $store_ratings,
        ];
    }

    public function storeProducts(Store $store, ?string $search = null, array $filters = [])
    {
        $query = $store->products()
            ->where('status', 'active')
            ->with([
                'category',
                'images',
                'variants' => fn ($q) => $q->where('status', 'active'),
            ]);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId = $filters['category_id'] ?? null) {
            $query->where('category_id', $categoryId);
        }

        $sort = $filters['sort'] ?? 'default';
        if ($sort === 'price_asc') {
            $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = ?) ASC', ['active']);
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id AND product_variants.status = ?) DESC', ['active']);
        } else {
            $query->orderBy('name');
        }

        return $this->decorateProducts($query->paginate(12));
    }

    public function product(Store $store, string $slug, ?Customer $customer = null): array
    {
        $product = $store->products()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->with([
                'category',
                'images',
                'ratings' => fn ($q) => $q->where('status', 'active')->with('customer'),
                'variants' => fn ($q) => $q->where('status', 'active')->with(['attributeValues.attribute', 'images']),
            ])
            ->firstOrFail();

        $destination = $this->destinationNode($customer);
        $shipping = $this->shippingService->estimate($store, $destination);

        $store->load('operatingHours');
        $store->shipping = $shipping;
        $store->open_status = $this->openStatus($store->operatingHours);

        $rating = $this->storeRating($store);
        $store->avg_rating = $rating['avg_rating'];
        $store->rating_count = $rating['rating_count'];

        $store_ratings = ProductRating::query()
            ->where('status', 'active')
            ->whereIn('product_id', $store->products()->where('status', 'active')->pluck('products.id'))
            ->with('customer')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $others = $store->products()
            ->where('status', 'active')
            ->whereKeyNot($product->id)
            ->with(['images', 'variants' => fn ($q) => $q->where('status', 'active')])
            ->limit(10)
            ->get();

        return [
            'store' => $store,
            'product' => $this->decorateProducts(collect([$product]))->first(),
            'shipping' => $shipping,
            'store_ratings' => $store_ratings,
            'other_products' => $this->decorateProducts($others),
        ];
    }

    protected function storeRating(Store $store): array
    {
        $row = ProductRating::query()
            ->where('status', 'active')
            ->whereIn('product_id', $store->products()->where('status', 'active')->pluck('products.id'))
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as rating_count')
            ->first();

        return [
            'avg_rating' => $row?->avg_rating ? round((float) $row->avg_rating, 1) : null,
            'rating_count' => (int) ($row?->rating_count ?? 0),
        ];
    }

    protected function destinationNode(?Customer $customer)
    {
        if (! $customer) {
            return null;
        }

        $address = $customer->addresses()
            ->with('locationNode')
            ->orderByDesc('is_default')
            ->first();

        return $address?->locationNode;
    }

    protected function activePromoProducts()
    {
        return Product::query()
            ->where('status', 'active')
            ->with(['store.locationNode', 'store.level', 'images', 'variants' => fn ($q) => $q->where('status', 'active')])
            ->whereHas('promotions', fn ($q) => $q
                ->where('status', 'active')
                ->where(fn ($q2) => $q2->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q2) => $q2->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->where(fn ($q2) => $q2->whereNull('promotions.store_id')->orWhereColumn('promotions.store_id', 'products.store_id')))
            ->orderByDesc('created_at');
    }

    protected function topProducts()
    {
        return Product::query()
            ->where('status', 'active')
            ->with(['store.locationNode', 'store.level', 'images', 'variants' => fn ($q) => $q->where('status', 'active')])
            ->orderByDesc(OrderItem::query()
                ->whereColumn('order_items.product_id', 'products.id')
                ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
                ->selectRaw('COALESCE(SUM(order_items.qty), 0)'))
            ->orderByDesc('created_at');
    }

    protected function decorateStores(Collection $stores, $destination): Collection
    {
        return $stores->map(function (Store $store) use ($destination) {
            $estimate = $this->shippingService->estimate($store, $destination);
            $store->distance_km = $estimate['distance_km'];
            $store->cost_estimate = $estimate['cost'];
            $store->open_status = $this->openStatus($store->operatingHours);

            return $store;
        });
    }

    public function decorateProducts($products)
    {
        $isPaginator = $products instanceof AbstractPaginator;
        $items = $isPaginator ? $products->getCollection() : $products;

        if ($items->isEmpty()) {
            return $products;
        }

        $ids = $items->pluck('id')->all();

        $ratings = ProductRating::query()
            ->where('status', 'active')
            ->whereIn('product_id', $ids)
            ->selectRaw('product_id, AVG(rating) as avg_rating, COUNT(*) as rating_count')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $sold = OrderItem::query()
            ->whereIn('product_id', $ids)
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->selectRaw('product_id, SUM(qty) as sold_count')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $decorated = $items->map(function (Product $product) use ($ratings, $sold) {
            $prices = $product->variants->map(fn ($v) => $this->pricingService->pricing($v));

            $product->min_price = $prices->min('effective');
            $product->min_original_price = $prices->min('original');

            $discounting = $prices
                ->filter(fn ($p) => (float) $p['discount'] > 0)
                ->map(fn ($p) => $p['original'] > 0 ? round(((float) $p['original'] - (float) $p['effective']) / (float) $p['original'] * 100) : 0);

            $product->discount_percent = $discounting->isNotEmpty() ? (int) $discounting->max() : 0;
            $product->has_promo = $discounting->isNotEmpty();
            $product->primary_image = $product->images->first()?->path;
            $product->avg_rating = $ratings->has($product->id) ? round((float) $ratings[$product->id]->avg_rating, 1) : null;
            $product->rating_count = (int) ($ratings[$product->id]->rating_count ?? 0);
            $product->sold_count = (int) ($sold[$product->id]->sold_count ?? 0);

            return $product;
        });

        if ($isPaginator) {
            $products->setCollection($decorated);
        }

        return $products;
    }

    /**
     * Status buka/tutup toko hari ini berdasarkan jam operasional.
     *
     * @return array{open: bool, label: string|null}
     */
    protected function openStatus(Collection $hours, ?CarbonInterface $now = null): array
    {
        $now = $now ?? now();
        $day = strtolower($now->format('l'));

        $hour = $hours->firstWhere('day', $day);
        if (! $hour || ! $hour->is_open) {
            return ['open' => false, 'label' => null];
        }

        $time = $now->format('H:i:s');
        $opens = $hour->opens_at;
        $closes = $hour->closes_at;

        $open = $time >= substr((string) $opens, 0, 8) && $time <= substr((string) $closes, 0, 8);

        return [
            'open' => $open,
            'label' => $hour->is_open ? $this->timeLabel($hour) : null,
        ];
    }

    protected function timeLabel(StoreOperatingHour $hour): string
    {
        $trim = fn ($v) => substr((string) $v, 0, 5);

        return $trim($hour->opens_at).'–'.$trim($hour->closes_at);
    }
}
