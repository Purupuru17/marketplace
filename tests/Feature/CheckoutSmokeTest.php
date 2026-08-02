<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use App\Services\Shipping\ShippingService;
use Database\Seeders\CatalogDataSeeder;
use Database\Seeders\CustomerDataSeeder;
use Database\Seeders\LocationDataSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\StoreDataSeeder;
use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutSmokeTest extends TestCase
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

    protected function node(string $name): LocationNode
    {
        return LocationNode::where('name', $name)->firstOrFail();
    }

    protected function addToCart(string $sku, int $qty): void
    {
        $variant = ProductVariant::where('sku', $sku)->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => $qty]);
    }

    protected function createAddress(string $nodeName): CustomerAddress
    {
        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.address.store'), [
                'label' => 'Rumah',
                'recipient_name' => 'Dina Puspita',
                'phone' => '081234567891',
                'full_address' => 'Jl. Melati No. 1',
                'location_node_id' => $this->node($nodeName)->id,
            ]);

        return $this->customer()->addresses()->firstOrFail();
    }

    public function test_address_crud_and_default_toggle(): void
    {
        $customer = $this->customer();
        $jakarta = $this->node('Kota Jakarta');
        $bandung = $this->node('Kota Bandung');

        $this->actingAs($customer, 'customer')
            ->post(route('customer.address.store'), [
                'recipient_name' => 'Dina',
                'phone' => '081234567891',
                'full_address' => 'Jl. Melati No. 1',
                'location_node_id' => $jakarta->id,
            ])->assertRedirect(route('customer.address.index'));

        $first = $customer->addresses()->firstOrFail();
        $this->assertTrue($first->is_default);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.address.store'), [
                'recipient_name' => 'Dina',
                'phone' => '081234567891',
                'full_address' => 'Jl. Anggrek No. 2',
                'location_node_id' => $bandung->id,
                'is_default' => 1,
            ])->assertRedirect(route('customer.address.index'));

        $second = $customer->addresses()->where('full_address', 'Jl. Anggrek No. 2')->firstOrFail();
        $this->assertTrue($second->refresh()->is_default);
        $this->assertFalse($first->refresh()->is_default);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.address.default', $first->id))
            ->assertRedirect(route('customer.address.index'));

        $this->assertTrue($first->refresh()->is_default);
        $this->assertFalse($second->refresh()->is_default);

        $this->actingAs($customer, 'customer')
            ->put(route('customer.address.update', $second->id), [
                'recipient_name' => 'Dina Puspita',
                'phone' => '081234567891',
                'full_address' => 'Jl. Anggrek No. 5',
                'location_node_id' => $bandung->id,
            ])->assertRedirect(route('customer.address.index'));

        $this->assertSame('Jl. Anggrek No. 5', $second->refresh()->full_address);

        $this->actingAs($customer, 'customer')
            ->delete(route('customer.address.destroy', $second->id))
            ->assertRedirect(route('customer.address.index'));

        $this->assertNull(CustomerAddress::find($second->id));
    }

    public function test_shipping_service_formula(): void
    {
        $store = Store::where('store_code', 'STR-DEMO1')->firstOrFail();
        $service = app(ShippingService::class);

        $this->assertSame(14000.0, $service->cost($store, 10));
        $this->assertSame(0.0, $service->cost($store, 2));
        $this->assertSame(0.0, $service->cost($store, null));
        $this->assertTrue($service->isWithinRadius($store, 10));
        $this->assertFalse($service->isWithinRadius($store, 26));
        $this->assertTrue($service->isWithinRadius($store, null));
    }

    public function test_checkout_summary_renders(): void
    {
        $this->createAddress('Kota Jakarta');
        $this->addToCart('NGS-REG', 2);

        $this->actingAs($this->customer(), 'customer')
            ->get(route('customer.checkout.index'))
            ->assertOk()
            ->assertSee('Checkout', false)
            ->assertSee('Nasi Goreng Spesial', false)
            ->assertSee('Rp 50.000', false);
    }

    public function test_place_order_single_store(): void
    {
        $customer = $this->customer();
        $address = $this->createAddress('Kota Jakarta');
        $this->addToCart('NGS-REG', 2);

        $variant = ProductVariant::where('sku', 'NGS-REG')->firstOrFail();
        $this->assertSame(50, (int) $variant->stock);

        $response = $this->actingAs($customer, 'customer')
            ->post(route('customer.checkout.store'), ['address_id' => $address->id]);

        $invoice = Invoice::where('customer_id', $customer->id)->firstOrFail();

        $response->assertRedirect(route('customer.checkout.success', $invoice->id));
        $this->assertSame('pending', $invoice->status);
        $this->assertSame('50000.00', (string) $invoice->subtotal);
        $this->assertSame('0.00', (string) $invoice->total_shipping_cost);
        $this->assertSame('50000.00', (string) $invoice->grand_total);
        $this->assertSame(1, $invoice->orders()->count());

        $order = $invoice->orders()->firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertSame('50000.00', (string) $order->total);
        $this->assertSame('0.00', (string) $order->shipping_cost);
        $this->assertSame('0.00', (string) $order->distance_km_snapshot);
        $this->assertStringContainsString('Dina Puspita', $order->address_snapshot);

        $item = $order->items()->firstOrFail();
        $this->assertSame('Nasi Goreng Spesial', $item->name_snapshot);
        $this->assertSame('NGS-REG', $item->sku_snapshot);
        $this->assertSame(2, $item->qty);
        $this->assertSame('25000.00', (string) $item->final_price_snapshot);
        $this->assertSame('50000.00', (string) $item->subtotal_snapshot);

        $this->assertSame(48, (int) $variant->refresh()->stock);

        $movement = StockMovement::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->firstOrFail();
        $this->assertSame('out', $movement->type);
        $this->assertSame(2, $movement->qty);
        $this->assertSame(50, $movement->stock_before);
        $this->assertSame(48, $movement->stock_after);

        $this->assertSame(1, $order->statusHistories()->count());
        $this->assertSame('converted', $customer->carts()->first()->status);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.checkout.success', $invoice->id))
            ->assertOk()
            ->assertSee($invoice->invoice_no, false);
    }

    public function test_place_order_multi_store(): void
    {
        $customer = $this->customer();
        $address = $this->createAddress('Kota Jakarta');

        $store2 = Store::create([
            'user_id' => User::where('email', 'super@gmail.com')->value('id'),
            'store_code' => 'STR-DEMO2',
            'store_name' => 'Toko Maju',
            'slug' => 'toko-maju',
            'location_node_id' => $this->node('Kota Jakarta')->id,
            'rate_per_km' => 2000,
            'min_free_distance_km' => 3,
            'max_radius_km' => 25,
            'status' => 'active',
        ]);

        $product2 = Product::create([
            'store_id' => $store2->id,
            'name' => 'Ayam Bakar',
            'slug' => 'ayam-bakar',
            'status' => 'active',
        ]);

        ProductVariant::create([
            'product_id' => $product2->id,
            'store_id' => $store2->id,
            'sku' => 'AB-REG',
            'price' => 30000,
            'stock' => 20,
            'weight_grams' => 300,
            'status' => 'active',
        ]);

        $this->addToCart('NGS-REG', 2);
        $this->addToCart('AB-REG', 1);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertRedirect();

        $invoice = Invoice::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame('80000.00', (string) $invoice->grand_total);
        $this->assertSame(2, $invoice->orders()->count());
        $this->assertSame(['toko-berkah', 'toko-maju'], $invoice->orders()->with('store')->get()->pluck('store.slug')->sort()->values()->all());
    }

    public function test_place_order_out_of_radius_is_rejected(): void
    {
        $customer = $this->customer();
        $address = $this->createAddress('Kota Bandung');
        $this->addToCart('NGS-REG', 1);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, Invoice::where('customer_id', $customer->id)->count());
        $this->assertSame(0, Order::count());
        $this->assertSame(50, (int) ProductVariant::where('sku', 'NGS-REG')->value('stock'));
    }

    public function test_place_order_with_insufficient_stock_is_rejected(): void
    {
        $customer = $this->customer();
        $address = $this->createAddress('Kota Jakarta');
        $this->addToCart('NGS-REG', 2);

        ProductVariant::where('sku', 'NGS-REG')->update(['stock' => 1]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, Invoice::where('customer_id', $customer->id)->count());
        $this->assertSame(1, (int) ProductVariant::where('sku', 'NGS-REG')->value('stock'));
        $this->assertSame('active', $customer->carts()->first()->status);
    }

    public function test_checkout_rejects_address_of_another_customer(): void
    {
        $address = $this->createAddress('Kota Jakarta');

        $budi = Customer::create([
            'name' => 'Budi',
            'email' => 'budi3@gmail.com',
            'password' => 'rahasia123',
            'status' => 'active',
        ]);

        $this->actingAs($budi, 'customer')
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertForbidden();
    }

    public function test_order_listing_and_detail(): void
    {
        $customer = $this->customer();
        $address = $this->createAddress('Kota Jakarta');
        $this->addToCart('NGS-REG', 1);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.checkout.store'), ['address_id' => $address->id]);

        $invoice = Invoice::where('customer_id', $customer->id)->firstOrFail();
        $order = $invoice->orders()->firstOrFail();

        $this->actingAs($customer, 'customer')
            ->get(route('customer.order.index'))
            ->assertOk()
            ->assertSee($invoice->invoice_no, false);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.order.show', $invoice->id))
            ->assertOk()
            ->assertSee($order->order_no, false)
            ->assertSee('Nasi Goreng Spesial', false);
    }
}
