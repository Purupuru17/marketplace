<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\CatalogDataSeeder;
use Database\Seeders\CustomerDataSeeder;
use Database\Seeders\LocationDataSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\StoreDataSeeder;
use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CoreDatabaseSeeder::class);
        $this->seed(MasterDataSeeder::class);
        $this->seed(LocationDataSeeder::class);
        $this->seed(StoreDataSeeder::class);
        $this->seed(CatalogDataSeeder::class);
        $this->seed(CustomerDataSeeder::class);
    }

    protected function customer(): Customer
    {
        return Customer::where('email', 'dina@gmail.com')->firstOrFail();
    }

    protected function variant(string $sku): ProductVariant
    {
        return ProductVariant::where('sku', $sku)->firstOrFail();
    }

    public function test_add_to_cart_creates_and_upserts(): void
    {
        $customer = $this->customer();
        $variant = $this->variant('NGS-REG');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 2])
            ->assertRedirect(route('customer.cart.index'));

        $this->assertSame(2, $customer->carts()->first()->items()->where('variant_id', $variant->id)->value('qty'));

        $this->actingAs($customer, 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 3])
            ->assertRedirect(route('customer.cart.index'));

        $this->assertSame(5, $customer->carts()->first()->items()->where('variant_id', $variant->id)->value('qty'));
        $this->assertSame(1, $customer->carts()->first()->items()->count());
    }

    public function test_add_exceeding_stock_is_rejected(): void
    {
        $customer = $this->customer();
        $variant = $this->variant('NGS-REG');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 100])
            ->assertSessionHasErrors('qty');

        $this->assertSame(0, $customer->carts()->first()->items()->count());
    }

    public function test_add_inactive_variant_is_rejected(): void
    {
        $customer = $this->customer();
        $product = Product::where('slug', 'nasi-goreng-spesial')->firstOrFail();
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'store_id' => $product->store_id,
            'sku' => 'NGS-OFF',
            'price' => 1000,
            'stock' => 5,
            'weight_grams' => 300,
            'status' => 'inactive',
        ]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 1])
            ->assertSessionHasErrors('variant_id');
    }

    public function test_update_and_remove_item(): void
    {
        $customer = $this->customer();
        $variant = $this->variant('AG-REG');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 2]);

        $item = $customer->carts()->first()->items()->where('variant_id', $variant->id)->firstOrFail();

        $this->actingAs($customer, 'customer')
            ->put(route('customer.cart.update', $item->id), ['qty' => 1])
            ->assertRedirect(route('customer.cart.index'));

        $this->assertSame(1, $item->refresh()->qty);

        $this->actingAs($customer, 'customer')
            ->put(route('customer.cart.update', $item->id), ['qty' => 0])
            ->assertSessionHasErrors('qty');

        $this->actingAs($customer, 'customer')
            ->delete(route('customer.cart.destroy', $item->id))
            ->assertRedirect(route('customer.cart.index'));

        $this->assertNull(CartItem::find($item->id));
    }

    public function test_index_shows_items_with_attributes(): void
    {
        $customer = $this->customer();
        $variant = $this->variant('AG-REG');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 2]);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.cart.index'))
            ->assertOk()
            ->assertSee('Ayam Geprek', false)
            ->assertSee('Sedang', false)
            ->assertSee('Toko Berkah', false)
            ->assertSee('Rp 44.000', false);
    }

    public function test_cannot_modify_another_customers_item(): void
    {
        $dina = $this->customer();
        $variant = $this->variant('NGS-REG');

        $this->actingAs($dina, 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 2]);

        $item = $dina->carts()->first()->items()->firstOrFail();

        $budi = Customer::create([
            'name' => 'Budi',
            'email' => 'budi2@gmail.com',
            'password' => 'rahasia123',
            'status' => 'active',
        ]);

        $this->actingAs($budi, 'customer')
            ->put(route('customer.cart.update', $item->id), ['qty' => 1])
            ->assertForbidden();

        $this->actingAs($budi, 'customer')
            ->delete(route('customer.cart.destroy', $item->id))
            ->assertForbidden();
    }
}
