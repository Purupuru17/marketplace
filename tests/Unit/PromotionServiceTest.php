<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\User;
use App\Services\Pricing\PromotionPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushTestCache();
        $this->seedMarketplace();
    }

    protected function makeStore(string $code): Store
    {
        $user = User::factory()->create();

        return Store::create([
            'user_id' => $user->id,
            'store_code' => $code,
            'store_name' => "Toko {$code}",
            'slug' => strtolower($code),
            'rate_per_km' => 2000,
            'min_free_distance_km' => 0,
            'status' => 'active',
        ]);
    }

    protected function makeVariant(Store $store, float $price, int $stock = 100, string $sku = 'VAR'): ProductVariant
    {
        $category = Category::first();
        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $category?->id,
            'name' => "Produk {$sku}",
            'slug' => strtolower($sku).'-'.uniqid(),
            'status' => 'active',
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'sku' => $sku.'-'.uniqid(),
            'price' => $price,
            'stock' => $stock,
            'status' => 'active',
        ]);
    }

    protected function makePromotion(Store $store, string $type, float $value, array $overrides = []): Promotion
    {
        return Promotion::create(array_merge([
            'store_id' => $store->id,
            'name' => 'Promo '.$type.'-'.uniqid(),
            'source' => 'store',
            'type' => $type,
            'value' => $value,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
        ], $overrides));
    }

    public function test_pricing_returns_original_when_no_promotion(): void
    {
        $store = $this->makeStore('STR-P1');
        $variant = $this->makeVariant($store, 100000);

        $pricing = app(PromotionPricingService::class)->pricing($variant);

        $this->assertSame(100000.0, $pricing['original']);
        $this->assertSame(100000.0, $pricing['effective']);
        $this->assertSame(0.0, $pricing['discount']);
        $this->assertNull($pricing['promotion']);
    }

    public function test_pricing_applies_percentage_promotion(): void
    {
        $store = $this->makeStore('STR-P2');
        $variant = $this->makeVariant($store, 100000);
        $promo = $this->makePromotion($store, 'percentage', 20);
        $promo->products()->attach($variant->product_id);

        $pricing = app(PromotionPricingService::class)->pricing($variant);

        $this->assertSame(80000.0, $pricing['effective']);
        $this->assertSame(20000.0, $pricing['discount']);
        $this->assertSame($promo->id, $pricing['promotion']->id);
    }

    public function test_pricing_applies_fixed_promotion(): void
    {
        $store = $this->makeStore('STR-P3');
        $variant = $this->makeVariant($store, 100000);
        $promo = $this->makePromotion($store, 'fixed', 30000);
        $promo->products()->attach($variant->product_id);

        $pricing = app(PromotionPricingService::class)->pricing($variant);

        $this->assertSame(70000.0, $pricing['effective']);
        $this->assertSame(30000.0, $pricing['discount']);
    }

    public function test_pricing_picks_the_lowest_effective_price(): void
    {
        $store = $this->makeStore('STR-P4');
        $variant = $this->makeVariant($store, 100000);
        $percentage = $this->makePromotion($store, 'percentage', 20);
        $fixed = $this->makePromotion($store, 'fixed', 5000);
        $percentage->products()->attach($variant->product_id);
        $fixed->products()->attach($variant->product_id);

        $pricing = app(PromotionPricingService::class)->pricing($variant);

        $this->assertSame(80000.0, $pricing['effective']);
        $this->assertSame(20000.0, $pricing['discount']);
        $this->assertSame($percentage->id, $pricing['promotion']->id);
    }

    public function test_pricing_ignores_inactive_and_expired_promotions(): void
    {
        $store = $this->makeStore('STR-P5');
        $variant = $this->makeVariant($store, 100000);

        $inactive = $this->makePromotion($store, 'fixed', 90000, ['status' => 'inactive']);
        $expired = $this->makePromotion($store, 'fixed', 90000, ['ends_at' => now()->subDay()]);
        $future = $this->makePromotion($store, 'fixed', 90000, ['starts_at' => now()->addDay()]);

        foreach ([$inactive, $expired, $future] as $promo) {
            $promo->products()->attach($variant->product_id);
        }

        $pricing = app(PromotionPricingService::class)->pricing($variant);

        $this->assertSame(100000.0, $pricing['effective']);
        $this->assertSame(0.0, $pricing['discount']);
        $this->assertNull($pricing['promotion']);
    }

    public function test_store_promotion_does_not_apply_to_other_stores_products(): void
    {
        $storeA = $this->makeStore('STR-P6');
        $storeB = $this->makeStore('STR-P7');
        $variantB = $this->makeVariant($storeB, 100000);

        $promoA = $this->makePromotion($storeA, 'fixed', 50000);
        $promoA->products()->attach($variantB->product_id);

        $pricing = app(PromotionPricingService::class)->pricing($variantB);

        $this->assertSame(100000.0, $pricing['effective']);
        $this->assertSame(0.0, $pricing['discount']);
    }

    public function test_platform_promotion_applies_to_any_store(): void
    {
        $store = $this->makeStore('STR-P8');
        $variant = $this->makeVariant($store, 100000);
        $promo = $this->makePromotion($store, 'percentage', 10, [
            'store_id' => null,
            'source' => 'platform',
        ]);
        $promo->products()->attach($variant->product_id);

        $pricing = app(PromotionPricingService::class)->pricing($variant);

        $this->assertSame(90000.0, $pricing['effective']);
    }

    public function test_fixed_promotion_never_pushes_price_below_zero(): void
    {
        $store = $this->makeStore('STR-P9');
        $variant = $this->makeVariant($store, 10000);
        $promo = $this->makePromotion($store, 'fixed', 999999);
        $promo->products()->attach($variant->product_id);

        $pricing = app(PromotionPricingService::class)->pricing($variant);

        $this->assertSame(0.0, $pricing['effective']);
        $this->assertSame(10000.0, $pricing['discount']);
    }
}
