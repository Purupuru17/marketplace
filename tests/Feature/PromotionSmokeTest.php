<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\User;
use App\Services\Pricing\PromotionPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class PromotionSmokeTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushTestCache();
        $this->seedMarketplace();
    }

    protected function owner(): User
    {
        return User::where('email', 'toko@gmail.com')->firstOrFail();
    }

    protected function admin(): User
    {
        return User::where('email', 'super@gmail.com')->firstOrFail();
    }

    protected function customer(): Customer
    {
        return Customer::where('email', 'dina@gmail.com')->firstOrFail();
    }

    protected function store(): Store
    {
        return Store::where('store_code', 'STR-DEMO1')->firstOrFail();
    }

    protected function node(string $name): LocationNode
    {
        return LocationNode::where('name', $name)->firstOrFail();
    }

    protected function seedAddress(): CustomerAddress
    {
        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.address.store'), [
                'label' => 'Rumah',
                'recipient_name' => 'Dina Puspita',
                'phone' => '081234567891',
                'full_address' => 'Jl. Melati No. 1',
                'location_node_id' => $this->node('Kota Jakarta')->id,
            ]);

        return $this->customer()->addresses()->firstOrFail();
    }

    public function test_owner_can_list_promotions(): void
    {
        $this->actingAs($this->owner(), 'web')
            ->get(route('toko.promotion.ajax', [
                'type' => 'table', 'source' => 'index',
                'start' => 0, 'length' => 25,
            ]))
            ->assertOk()
            ->assertSee('Promo Agustus');
    }

    public function test_owner_creates_store_promotion_scoped_to_own_store(): void
    {
        $product = $this->store()->products()->where('slug', 'es-kopi-susu')->firstOrFail();

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.promotion.store'), [
                'name' => 'Promo Owner',
                'source' => 'store',
                'store_id' => $this->store()->id,
                'type' => 'percentage',
                'value' => 15,
                'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addMonth()->format('Y-m-d\TH:i'),
                'stackable' => 0,
                'status' => 'active',
                'products' => [$product->id],
            ])
            ->assertRedirect(route('toko.promotion.index'));

        $promotion = Promotion::where('name', 'Promo Owner')->firstOrFail();

        $this->assertSame('store', $promotion->source);
        $this->assertSame($this->store()->id, $promotion->store_id);
        $this->assertTrue($promotion->products()->whereKey($product->id)->exists());
    }

    public function test_owner_cannot_force_a_platform_promotion(): void
    {
        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.promotion.store'), [
                'name' => 'Coba Platform',
                'source' => 'platform',
                'type' => 'fixed',
                'value' => 1000,
                'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addMonth()->format('Y-m-d\TH:i'),
                'status' => 'active',
            ])
            ->assertRedirect(route('toko.promotion.index'));

        $promotion = Promotion::where('name', 'Coba Platform')->firstOrFail();

        $this->assertSame('store', $promotion->source);
        $this->assertSame($this->store()->id, $promotion->store_id);
    }

    public function test_admin_can_create_platform_promotion(): void
    {
        $this->actingAs($this->admin(), 'web')
            ->post(route('toko.promotion.store'), [
                'name' => 'Promo Platform Admin',
                'source' => 'platform',
                'type' => 'percentage',
                'value' => 20,
                'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addMonth()->format('Y-m-d\TH:i'),
                'status' => 'active',
            ])
            ->assertRedirect(route('toko.promotion.index'));

        $promotion = Promotion::where('name', 'Promo Platform Admin')->firstOrFail();

        $this->assertSame('platform', $promotion->source);
        $this->assertNull($promotion->store_id);
    }

    public function test_owner_cannot_edit_another_stores_promotion(): void
    {
        $otherUser = User::create([
            'name' => 'Pemilik Lain',
            'email' => 'lain@gmail.com',
            'password' => '12345',
        ]);

        $storeB = Store::create([
            'user_id' => $otherUser->id,
            'store_name' => 'Toko Lain',
            'store_code' => 'STR-TEST-B',
            'slug' => 'toko-lain',
            'status' => 'active',
        ]);

        $promotion = Promotion::create([
            'store_id' => $storeB->id,
            'name' => 'Promo Toko Lain',
            'source' => 'store',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
        ]);

        $this->actingAs($this->owner(), 'web')
            ->get(route('toko.promotion.edit', $promotion->id))
            ->assertForbidden();

        $this->actingAs($this->owner(), 'web')
            ->delete(route('toko.promotion.destroy', $promotion->id))
            ->assertForbidden();
    }

    public function test_pricing_applies_best_single_discount(): void
    {
        $variant = ProductVariant::where('sku', 'NGS-REG')->firstOrFail();
        $product = $variant->product;

        Promotion::create([
            'store_id' => $this->store()->id,
            'name' => 'Persen 10',
            'source' => 'store',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
        ])->products()->attach($product->id);

        Promotion::create([
            'store_id' => $this->store()->id,
            'name' => 'Potong 1000',
            'source' => 'store',
            'type' => 'fixed',
            'value' => 1000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
        ])->products()->attach($product->id);

        $pricing = app(PromotionPricingService::class)->pricing($variant);

        $this->assertSame(25000.0, $pricing['original']);
        $this->assertSame(22500.0, $pricing['effective']);
        $this->assertSame(2500.0, $pricing['discount']);
        $this->assertSame('Persen 10', $pricing['promotion']->name);
    }

    public function test_inactive_and_expired_promotions_are_ignored(): void
    {
        $variant = ProductVariant::where('sku', 'NGS-REG')->firstOrFail();
        $product = $variant->product;

        Promotion::create([
            'store_id' => $this->store()->id,
            'name' => 'Nonaktif',
            'source' => 'store',
            'type' => 'percentage',
            'value' => 50,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'inactive',
        ])->products()->attach($product->id);

        Promotion::create([
            'store_id' => $this->store()->id,
            'name' => 'Kedaluwarsa',
            'source' => 'store',
            'type' => 'fixed',
            'value' => 20000,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
            'status' => 'active',
        ])->products()->attach($product->id);

        $pricing = app(PromotionPricingService::class)->pricing($variant);

        $this->assertSame(25000.0, $pricing['effective']);
        $this->assertNull($pricing['promotion']);
    }

    public function test_checkout_applies_promotion_to_order_item_snapshot(): void
    {
        $variant = ProductVariant::where('sku', 'KSG-REG')->firstOrFail();
        $address = $this->seedAddress();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 1]);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => 'bank_transfer',
            ])
            ->assertRedirect();

        $invoice = Invoice::where('customer_id', $this->customer()->id)->latest('created_at')->firstOrFail();
        $item = $invoice->orders()->firstOrFail()->items()->firstOrFail();

        $this->assertSame('18000.00', (string) $item->original_price_snapshot);
        $this->assertSame('3000.00', (string) $item->discount_snapshot);
        $this->assertSame('15000.00', (string) $item->final_price_snapshot);
        $this->assertSame(0.0, (float) $invoice->total_shipping_cost);
        $this->assertSame('15000.00', (string) $invoice->grand_total);
    }

    public function test_checkout_does_not_apply_promo_to_unpromoted_product(): void
    {
        $variant = ProductVariant::where('sku', 'NGS-REG')->firstOrFail();
        $address = $this->seedAddress();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 1]);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => 'bank_transfer',
            ])
            ->assertRedirect();

        $invoice = Invoice::where('customer_id', $this->customer()->id)->latest('created_at')->firstOrFail();
        $item = $invoice->orders()->firstOrFail()->items()->firstOrFail();

        $this->assertSame('25000.00', (string) $item->final_price_snapshot);
        $this->assertSame('0.00', (string) $item->discount_snapshot);
    }
}
