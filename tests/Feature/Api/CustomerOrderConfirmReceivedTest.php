<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Store\StoreWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class CustomerOrderConfirmReceivedTest extends TestCase
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

    protected function actAsCustomer(Customer $customer): void
    {
        $this->actingAs($customer, 'api-customer');
    }

    protected function placeOrder(): Order
    {
        $customer = $this->customer();
        $variant = ProductVariant::where('sku', 'NGS-REG')->firstOrFail();

        CustomerAddress::create([
            'customer_id' => $customer->id,
            'location_node_id' => LocationNode::where('name', 'Kota Jakarta')->firstOrFail()->id,
            'label' => 'Rumah',
            'recipient_name' => 'Dina Puspita',
            'phone' => '081234567891',
            'full_address' => 'Jl. Melati No. 1',
        ]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 1]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $customer->addresses()->firstOrFail()->id,
                'stores' => [$variant->store_id => ['fulfillment_type' => 'delivery', 'payment_method' => 'bank_transfer']],
            ]);

        $invoice = Invoice::where('customer_id', $customer->id)->latest('created_at')->firstOrFail();

        return $invoice->orders()->firstOrFail();
    }

    protected function readyPaidOrder(): Order
    {
        $order = $this->placeOrder();

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.paid', $order->id));

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'ready']);

        return $order->refresh();
    }

    public function test_customer_can_confirm_received_when_ready_and_paid(): void
    {
        $order = $this->readyPaidOrder();

        $this->actAsCustomer($this->customer());

        $this->postJson("/api/v1/customer/orders/{$order->id}/confirm-received")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertSame('completed', $order->refresh()->status);
        $this->assertSame('paid', $order->invoice->refresh()->status);

        $wallet = $order->store->wallet()->firstOrFail();
        $this->assertEquals(0, $wallet->held_balance);
        $this->assertEquals($order->total, $wallet->available_balance);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status_from' => 'ready',
            'status_to' => 'completed',
            'changed_by_type' => 'customer',
            'changed_by_id' => $this->customer()->id,
        ]);
    }

    public function test_confirm_received_rejected_when_not_ready(): void
    {
        $order = $this->placeOrder();

        $this->actAsCustomer($this->customer());

        $this->postJson("/api/v1/customer/orders/{$order->id}/confirm-received")
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->assertSame('pending', $order->refresh()->status);
    }

    public function test_confirm_received_rejected_when_payment_not_paid(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'processing']);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'ready']);

        $this->actAsCustomer($this->customer());

        $this->postJson("/api/v1/customer/orders/{$order->id}/confirm-received")
            ->assertStatus(422)
            ->assertJsonValidationErrors('payment');

        $this->assertSame('ready', $order->refresh()->status);
    }

    public function test_confirm_received_rejected_for_foreign_customer(): void
    {
        $order = $this->readyPaidOrder();

        $other = Customer::create([
            'name' => 'Orang Lain',
            'email' => 'lain@example.com',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $this->actAsCustomer($other);

        $this->postJson("/api/v1/customer/orders/{$order->id}/confirm-received")
            ->assertStatus(403);

        $this->assertSame('ready', $order->refresh()->status);
    }
}
