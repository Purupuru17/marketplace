<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class StoreOrderSmokeTest extends TestCase
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

    protected function owner(): User
    {
        return User::where('email', 'toko@gmail.com')->firstOrFail();
    }

    protected function admin(): User
    {
        return User::where('email', 'super@gmail.com')->firstOrFail();
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

    protected function addToCart(string $sku, int $qty = 1): void
    {
        $variant = ProductVariant::where('sku', $sku)->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => $qty]);
    }

    protected function placeOrder(string $method, string $sku = 'NGS-REG', int $qty = 1): Order
    {
        $this->addToCart($sku, $qty);
        $address = $this->seedAddress();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => $method,
            ]);

        $invoice = Invoice::where('customer_id', $this->customer()->id)->latest('created_at')->firstOrFail();

        return $invoice->orders()->firstOrFail();
    }

    protected function createOtherOwnerStore(): User
    {
        $budi = User::create([
            'name' => 'Budi',
            'email' => 'budi@gmail.com',
            'password' => 'rahasia123',
            'status' => 'active',
        ]);
        $budi->assignRole('Toko');

        Store::create([
            'user_id' => $budi->id,
            'store_code' => 'STR-DEMO2',
            'store_name' => 'Toko Maju',
            'slug' => 'toko-maju',
            'location_node_id' => $this->node('Kota Jakarta')->id,
            'rate_per_km' => 2000,
            'min_free_distance_km' => 3,
            'max_radius_km' => 25,
            'status' => 'active',
        ]);

        return $budi;
    }

    public function test_owner_sees_only_own_store_orders(): void
    {
        $order = $this->placeOrder('cod');

        $this->actingAs($this->owner(), 'web')
            ->get(route('toko.order.ajax', [
                'type' => 'table', 'source' => 'index',
                'search' => ['value' => $order->order_no],
                'start' => 0, 'length' => 10,
            ]))
            ->assertOk()
            ->assertSee($order->order_no, false);

        $budi = $this->createOtherOwnerStore();

        $this->actingAs($budi, 'web')
            ->get(route('toko.order.ajax', [
                'type' => 'table', 'source' => 'index',
                'search' => ['value' => $order->order_no],
                'start' => 0, 'length' => 10,
            ]))
            ->assertOk()
            ->assertDontSee($order->order_no, false);

        $this->actingAs($this->admin(), 'web')
            ->get(route('toko.order.ajax', [
                'type' => 'table', 'source' => 'index',
                'search' => ['value' => $order->order_no],
                'start' => 0, 'length' => 10,
            ]))
            ->assertOk()
            ->assertSee($order->order_no, false);
    }

    public function test_owner_accepts_pending_order(): void
    {
        $order = $this->placeOrder('cod');
        $this->assertSame('pending', $order->status);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'processing'])
            ->assertRedirect(route('toko.order.show', $order->id));

        $this->assertSame('processing', $order->refresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status_from' => 'pending',
            'status_to' => 'processing',
            'changed_by_type' => 'store',
            'changed_by_id' => $this->owner()->id,
        ]);
    }

    public function test_owner_ships_and_completes_order_settles_cod(): void
    {
        $order = $this->placeOrder('cod');
        $payment = $order->invoice->payments()->firstOrFail();

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'processing']);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'shipped']);

        $this->assertSame('shipped', $order->refresh()->status);
        $this->assertSame('pending', $order->invoice->status);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'completed']);

        $this->assertSame('completed', $order->refresh()->status);
        $this->assertSame('paid', $payment->refresh()->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame('paid', $order->invoice->refresh()->status);
    }

    public function test_owner_cancels_order_restores_stock_and_cancels_invoice(): void
    {
        $order = $this->placeOrder('cod', 'NGS-REG', 2);

        $variant = ProductVariant::where('sku', 'NGS-REG')->firstOrFail();
        $this->assertSame(48, (int) $variant->stock);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'cancelled']);

        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertSame('cancelled', $order->invoice->refresh()->status);
        $this->assertSame(50, (int) $variant->refresh()->stock);

        $movement = StockMovement::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->where('type', 'in')
            ->firstOrFail();
        $this->assertSame(2, $movement->qty);
        $this->assertSame(48, $movement->stock_before);
        $this->assertSame(50, $movement->stock_after);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $order = $this->placeOrder('cod');

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'shipped'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->refresh()->status);
    }

    public function test_cannot_manage_another_store_order(): void
    {
        $order = $this->placeOrder('cod');
        $budi = $this->createOtherOwnerStore();

        $this->actingAs($budi, 'web')
            ->get(route('toko.order.show', $order->id))
            ->assertForbidden();

        $this->actingAs($budi, 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'processing'])
            ->assertForbidden();

        $this->assertSame('pending', $order->refresh()->status);
    }

    public function test_order_detail_shows_items_and_customer(): void
    {
        $order = $this->placeOrder('cod');

        $this->actingAs($this->owner(), 'web')
            ->get(route('toko.order.show', $order->id))
            ->assertOk()
            ->assertSee($order->order_no, false)
            ->assertSee('Nasi Goreng Spesial', false)
            ->assertSee('Dina Puspita', false);
    }
}
