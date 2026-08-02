<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Database\Seeders\CatalogDataSeeder;
use Database\Seeders\CustomerDataSeeder;
use Database\Seeders\LocationDataSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\StoreDataSeeder;
use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreWalletSmokeTest extends TestCase
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

    protected function placeOrder(string $method): Order
    {
        $variant = \App\Models\ProductVariant::where('sku', 'NGS-REG')->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 1]);

        $address = $this->seedAddress();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => $method,
            ]);

        $invoice = Invoice::where('customer_id', $this->customer()->id)->latest('created_at')->firstOrFail();

        return $invoice->orders()->firstOrFail();
    }

    protected function confirmPayment(Invoice $invoice): void
    {
        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.payment.store', $invoice->id));
    }

    protected function transitionToCompleted(Order $order): void
    {
        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'processing']);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'shipped']);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'completed']);
    }

    public function test_online_payment_holds_funds_to_store_wallet(): void
    {
        $order = $this->placeOrder('bank_transfer');

        $this->assertNull($order->store->wallet);

        $this->confirmPayment($order->invoice);

        $wallet = $order->store->wallet()->firstOrFail();
        $this->assertEquals($order->total, $wallet->held_balance);
        $this->assertEquals(0, $wallet->available_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'hold',
            'amount' => $order->total,
            'reference_type' => Order::class,
            'reference_id' => $order->id,
        ]);

        $this->confirmPayment($order->invoice);
        $this->assertEquals($order->total, $wallet->refresh()->held_balance);
        $this->assertSame(1, $wallet->transactions()->where('type', 'hold')->count());
    }

    public function test_completing_online_order_releases_funds_to_available(): void
    {
        $order = $this->placeOrder('bank_transfer');
        $this->confirmPayment($order->invoice);

        $this->transitionToCompleted($order);

        $wallet = $order->store->wallet()->firstOrFail();
        $this->assertEquals(0, $wallet->held_balance);
        $this->assertEquals($order->total, $wallet->available_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'release',
            'amount' => $order->total,
            'reference_id' => $order->id,
        ]);
    }

    public function test_completing_cod_order_credits_funds_directly(): void
    {
        $order = $this->placeOrder('cod');
        $this->transitionToCompleted($order);

        $wallet = $order->store->wallet()->firstOrFail();
        $this->assertEquals($order->total, $wallet->available_balance);
        $this->assertEquals(0, $wallet->held_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $order->total,
            'reference_id' => $order->id,
        ]);
        $this->assertSame(0, $wallet->transactions()->where('type', 'hold')->count());
    }

    public function test_cancelling_paid_order_reverses_held_funds(): void
    {
        $order = $this->placeOrder('bank_transfer');
        $this->confirmPayment($order->invoice);

        $wallet = $order->store->wallet()->firstOrFail();
        $this->assertEquals($order->total, $wallet->held_balance);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.update', $order->id), ['status' => 'cancelled']);

        $this->assertEquals(0, $wallet->refresh()->held_balance);
        $this->assertEquals(0, $wallet->available_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => $order->total,
            'reference_id' => $order->id,
        ]);
    }

    public function test_withdrawal_request_is_created_and_validated(): void
    {
        $order = $this->placeOrder('cod');
        $this->transitionToCompleted($order);

        $wallet = $order->store->wallet()->firstOrFail();

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.wallet.store'), [
                'store_id' => $order->store_id,
                'amount' => 10000,
                'bank_name' => 'Bank Simulasi',
                'account_number' => '1234567890',
                'account_name' => 'Toko Berkah',
            ])
            ->assertRedirect(route('toko.wallet.index'));

        $this->assertDatabaseHas('withdrawal_requests', [
            'wallet_id' => $wallet->id,
            'amount' => 10000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.wallet.store'), [
                'store_id' => $order->store_id,
                'amount' => $order->total,
                'bank_name' => 'Bank Simulasi',
                'account_number' => '1234567890',
                'account_name' => 'Toko Berkah',
            ])
            ->assertSessionHasErrors('amount');
    }

    public function test_admin_approves_withdrawal_and_debits_balance(): void
    {
        $order = $this->placeOrder('cod');
        $this->transitionToCompleted($order);

        $wallet = $order->store->wallet()->firstOrFail();
        $withdrawal = WithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'store_id' => $order->store_id,
            'amount' => 10000,
            'bank_name' => 'Bank Simulasi',
            'account_number' => '1234567890',
            'account_name' => 'Toko Berkah',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin(), 'web')
            ->post(route('toko.wallet.process', [$withdrawal->id, 'approve']))
            ->assertRedirect(route('toko.wallet.index'));

        $this->assertEquals('approved', $withdrawal->refresh()->status);
        $this->assertEquals($this->admin()->id, $withdrawal->processed_by);
        $this->assertEquals($order->total - 10000, $wallet->refresh()->available_balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => 10000,
            'balance_before' => $order->total,
            'balance_after' => $order->total - 10000,
            'reference_type' => WithdrawalRequest::class,
            'reference_id' => $withdrawal->id,
        ]);
    }

    public function test_admin_rejects_withdrawal_without_balance_change(): void
    {
        $order = $this->placeOrder('cod');
        $this->transitionToCompleted($order);

        $wallet = $order->store->wallet()->firstOrFail();
        $withdrawal = WithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'store_id' => $order->store_id,
            'amount' => 10000,
            'bank_name' => 'Bank Simulasi',
            'account_number' => '1234567890',
            'account_name' => 'Toko Berkah',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin(), 'web')
            ->post(route('toko.wallet.process', [$withdrawal->id, 'reject']))
            ->assertRedirect(route('toko.wallet.index'));

        $this->assertEquals('rejected', $withdrawal->refresh()->status);
        $this->assertEquals($order->total, $wallet->refresh()->available_balance);
        $this->assertSame(0, $wallet->transactions()->where('type', 'debit')->count());
    }

    public function test_non_admin_cannot_process_withdrawal(): void
    {
        $order = $this->placeOrder('cod');
        $this->transitionToCompleted($order);

        $wallet = $order->store->wallet()->firstOrFail();
        $withdrawal = WithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'store_id' => $order->store_id,
            'amount' => 10000,
            'bank_name' => 'Bank Simulasi',
            'account_number' => '1234567890',
            'account_name' => 'Toko Berkah',
            'status' => 'pending',
        ]);

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.wallet.process', [$withdrawal->id, 'approve']))
            ->assertForbidden();

        $this->assertEquals('pending', $withdrawal->refresh()->status);
    }

    public function test_owner_cannot_request_withdrawal_for_another_store(): void
    {
        $budi = User::create([
            'name' => 'Budi',
            'email' => 'budi@gmail.com',
            'password' => 'rahasia123',
            'status' => 'active',
        ]);
        $budi->assignRole('Toko');

        $order = $this->placeOrder('cod');
        $this->transitionToCompleted($order);

        $this->actingAs($budi, 'web')
            ->post(route('toko.wallet.store'), [
                'store_id' => $order->store_id,
                'amount' => 10000,
                'bank_name' => 'Bank Simulasi',
                'account_number' => '1234567890',
                'account_name' => 'Toko Maju',
            ])
            ->assertForbidden();
    }

    public function test_wallet_index_shows_own_wallet_only(): void
    {
        $order = $this->placeOrder('cod');
        $this->transitionToCompleted($order);

        $this->actingAs($this->owner(), 'web')
            ->get(route('toko.wallet.index'))
            ->assertOk()
            ->assertSee('Saldo Toko', false)
            ->assertSee($order->store->store_name, false)
            ->assertSee('Transaksi Terbaru', false)
            ->assertSee('Permintaan Penarikan', false);
    }
}
