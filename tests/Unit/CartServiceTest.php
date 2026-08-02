<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use App\Services\Customer\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushTestCache();
        $this->seedMarketplace();
    }

    protected function customer(): Customer
    {
        return Customer::where('email', 'dina@gmail.com')->firstOrFail();
    }

    protected function makeCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Unit Tester',
            'email' => 'unit-'.Str::random(8).'@example.com',
            'password' => 'secret123',
            'status' => 'active',
        ]);
    }

    protected function variant(string $sku): ProductVariant
    {
        return ProductVariant::where('sku', $sku)->firstOrFail();
    }

    protected function makeVariant(int $stock = 10): ProductVariant
    {
        $user = User::factory()->create();
        $store = Store::create([
            'user_id' => $user->id,
            'store_code' => 'STR-C-'.Str::upper(Str::random(4)),
            'store_name' => 'Toko Cart',
            'slug' => 'toko-cart-'.Str::random(6),
            'rate_per_km' => 2000,
            'min_free_distance_km' => 0,
            'status' => 'active',
        ]);
        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Produk Cart',
            'slug' => 'produk-cart-'.Str::random(6),
            'status' => 'active',
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'sku' => 'CART-'.Str::upper(Str::random(6)),
            'price' => 10000,
            'stock' => $stock,
            'status' => 'active',
        ]);
    }

    public function test_add_creates_a_cart_item_with_requested_quantity(): void
    {
        $service = app(CartService::class);
        $item = $service->add($this->customer(), $this->variant('NGS-REG'), 2);

        $this->assertSame(2, $item->qty);
        $this->assertSame(2, $service->count($this->customer()));
    }

    public function test_add_sums_quantity_for_existing_item(): void
    {
        $service = app(CartService::class);
        $variant = $this->variant('NGS-REG');

        $service->add($this->customer(), $variant, 2);
        $service->add($this->customer(), $variant, 3);

        $item = $this->customer()->carts()->where('status', 'active')->first()->items()->where('variant_id', $variant->id)->first();
        $this->assertSame(5, $item->qty);
        $this->assertSame(1, $this->customer()->carts()->where('status', 'active')->first()->items()->count());
    }

    public function test_add_rejects_quantity_below_one(): void
    {
        $service = app(CartService::class);

        $this->expectException(ValidationException::class);
        $service->add($this->customer(), $this->variant('NGS-REG'), 0);
    }

    public function test_add_rejects_quantity_above_stock(): void
    {
        $service = app(CartService::class);

        $this->expectException(ValidationException::class);
        $service->add($this->customer(), $this->variant('NGS-REG'), 51);
    }

    public function test_add_rejects_accumulated_quantity_above_stock(): void
    {
        $service = app(CartService::class);
        $variant = $this->variant('NGS-REG');

        $service->add($this->customer(), $variant, 40);

        $this->expectException(ValidationException::class);
        $service->add($this->customer(), $variant, 20);
    }

    public function test_add_rejects_inactive_variant(): void
    {
        $service = app(CartService::class);
        $variant = $this->makeVariant();
        $variant->update(['status' => 'inactive']);

        $this->expectException(ValidationException::class);
        $service->add($this->customer(), $variant, 1);
    }

    public function test_add_rejects_variant_of_inactive_store(): void
    {
        $service = app(CartService::class);
        $variant = $this->makeVariant();
        $variant->store->update(['status' => 'inactive']);

        $this->expectException(ValidationException::class);
        $service->add($this->customer(), $variant, 1);
    }

    public function test_update_qty_applies_new_quantity(): void
    {
        $service = app(CartService::class);
        $item = $service->add($this->customer(), $this->variant('NGS-REG'), 2);

        $service->updateQty($this->customer(), $item, 10);

        $this->assertSame(10, $item->fresh()->qty);
    }

    public function test_update_qty_rejects_below_one(): void
    {
        $service = app(CartService::class);
        $item = $service->add($this->customer(), $this->variant('NGS-REG'), 2);

        $this->expectException(ValidationException::class);
        $service->updateQty($this->customer(), $item, 0);
    }

    public function test_update_qty_rejects_above_stock(): void
    {
        $service = app(CartService::class);
        $item = $service->add($this->customer(), $this->variant('NGS-REG'), 2);

        $this->expectException(ValidationException::class);
        $service->updateQty($this->customer(), $item, 100);
    }

    public function test_update_qty_rejects_item_of_another_customer(): void
    {
        $service = app(CartService::class);
        $item = $service->add($this->customer(), $this->variant('NGS-REG'), 2);

        $this->expectException(HttpException::class);
        $service->updateQty($this->makeCustomer(), $item, 3);
    }

    public function test_remove_deletes_the_item(): void
    {
        $service = app(CartService::class);
        $item = $service->add($this->customer(), $this->variant('NGS-REG'), 2);

        $service->remove($this->customer(), $item);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
        $this->assertSame(0, $service->count($this->customer()));
    }

    public function test_remove_rejects_item_of_another_customer(): void
    {
        $service = app(CartService::class);
        $item = $service->add($this->customer(), $this->variant('NGS-REG'), 2);

        $this->expectException(HttpException::class);
        $service->remove($this->makeCustomer(), $item);
    }

    public function test_count_sums_quantities_of_all_items(): void
    {
        $service = app(CartService::class);

        $service->add($this->customer(), $this->variant('NGS-REG'), 2);
        $service->add($this->customer(), $this->variant('AG-REG'), 3);

        $this->assertSame(5, $service->count($this->customer()));
    }

    public function test_summary_applies_best_promotion_and_groups_by_store(): void
    {
        $service = app(CartService::class);
        $service->add($this->customer(), $this->variant('KSG-REG'), 1);

        $summary = $service->summary($this->customer());

        $this->assertSame(18000.0, $summary['items']->first()->unit_original_price);
        $this->assertSame(15000.0, $summary['items']->first()->unit_price);
        $this->assertSame(15000.0, $summary['total']);
        $this->assertSame(1, $summary['by_store']->count());
    }
}
