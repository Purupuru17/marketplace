<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\Store\StoreWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class AuthorizationSmokeTest extends TestCase
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

    protected function store(): Store
    {
        return Store::where('store_code', 'STR-DEMO1')->firstOrFail();
    }

    protected function makeCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Orang Lain',
            'email' => 'lain-'.Str::random(8).'@example.com',
            'password' => 'secret123',
            'status' => 'active',
        ]);
    }

    protected function createAddress(Customer $customer): CustomerAddress
    {
        return CustomerAddress::create([
            'customer_id' => $customer->id,
            'location_node_id' => LocationNode::where('name', 'Kota Jakarta')->firstOrFail()->id,
            'label' => 'Rumah',
            'recipient_name' => 'Dina Puspita',
            'phone' => '081234567891',
            'full_address' => 'Jl. Melati No. 1',
        ]);
    }

    protected function placeOrder(string $sku, int $qty = 1): Order
    {
        $customer = $this->customer();
        $variant = ProductVariant::where('sku', $sku)->firstOrFail();
        $address = $this->createAddress($customer);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => $qty]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'stores' => [$variant->store_id => ['fulfillment_type' => 'delivery', 'payment_method' => 'bank_transfer']],
            ]);

        $invoice = Invoice::where('customer_id', $customer->id)->latest('created_at')->firstOrFail();

        return $invoice->orders()->firstOrFail();
    }

    protected function completeOrder(Order $order): void
    {
        foreach (['processing', 'shipped', 'completed'] as $status) {
            $this->actingAs($this->owner(), 'web')
                ->post(route('toko.order.update', $order->id), ['status' => $status]);
        }
    }

    public function test_customer_cannot_view_another_customers_invoice(): void
    {
        $order = $this->placeOrder('NGS-REG');

        $this->actingAs($this->makeCustomer(), 'customer')
            ->get(route('customer.order.show', $order->id))
            ->assertForbidden();
    }

    public function test_customer_cannot_open_another_customers_payment(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $payment = $order->payments()->firstOrFail();

        $this->actingAs($this->makeCustomer(), 'customer')
            ->get(route('customer.payment.show', $payment->id))
            ->assertForbidden();
    }

    public function test_stranger_cannot_mark_order_paid(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $stranger = User::factory()->create();

        $this->actingAs($stranger, 'web')
            ->post(route('toko.order.paid', $order->id))
            ->assertForbidden();
    }

    public function test_owner_cannot_view_another_stores_order(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $stranger = User::factory()->create();

        $this->actingAs($stranger, 'web')
            ->get(route('toko.order.show', $order->id))
            ->assertForbidden();
    }

    public function test_owner_cannot_update_another_stores_order(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $stranger = User::factory()->create();

        $this->actingAs($stranger, 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'processing'])
            ->assertForbidden();
    }

    public function test_non_admin_cannot_process_withdrawal(): void
    {
        $store = $this->store();
        $wallet = app(StoreWalletService::class)->walletFor($store);
        $wallet->update(['available_balance' => 100000]);

        $withdrawal = WithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'store_id' => $store->id,
            'amount' => 50000,
            'bank_name' => 'BCA',
            'account_number' => '12345',
            'account_name' => 'Pemilik Toko',
            'status' => 'pending',
        ]);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.wallet.process', [$withdrawal->id, 'approve']))
            ->assertForbidden();

        $this->assertSame('pending', $withdrawal->refresh()->status);
        $this->assertSame(0, WalletTransaction::count());
    }

    public function test_customer_cannot_checkout_with_another_customers_address(): void
    {
        $otherCustomer = $this->makeCustomer();
        $address = $this->createAddress($otherCustomer);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => ProductVariant::where('sku', 'NGS-REG')->firstOrFail()->id, 'qty' => 1]);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'stores' => [ProductVariant::where('sku', 'NGS-REG')->firstOrFail()->store_id => ['fulfillment_type' => 'delivery', 'payment_method' => 'bank_transfer']],
            ])
            ->assertForbidden();
    }

    public function test_customer_cannot_rate_order_item_of_another_customer(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $this->completeOrder($order);

        $item = $order->items()->firstOrFail();

        $this->actingAs($this->makeCustomer(), 'customer')
            ->post(route('customer.rating.store'), [
                'order_item_id' => $item->id,
                'rating' => 5,
            ])
            ->assertSessionHasErrors('order_item_id');
    }

    public function test_customer_cannot_rate_own_non_completed_order(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $item = $order->items()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.rating.store'), [
                'order_item_id' => $item->id,
                'rating' => 5,
            ])
            ->assertSessionHasErrors('order_item_id');
    }

    public function test_owner_cannot_apply_illegal_status_transition(): void
    {
        $order = $this->placeOrder('NGS-REG');

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->refresh()->status);
    }
}
