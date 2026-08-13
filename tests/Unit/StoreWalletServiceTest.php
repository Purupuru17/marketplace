<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\Store\StoreWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class StoreWalletServiceTest extends TestCase
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

    protected function makeOrder(Store $store, int $total): Order
    {
        $customer = Customer::create([
            'name' => 'Unit Tester',
            'email' => 'unit-'.Str::random(8).'@example.com',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'invoice_no' => 'INV-UNIT-'.Str::random(8),
            'customer_id' => $customer->id,
            'subtotal' => $total,
            'grand_total' => $total,
            'status' => 'pending',
        ]);

        return Order::create([
            'order_no' => 'ORD-UNIT-'.Str::random(8),
            'invoice_id' => $invoice->id,
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'status' => 'pending',
            'subtotal' => $total,
            'total' => $total,
            'address_snapshot' => 'Jl. Unit Test No. 1',
        ]);
    }

    public function test_hold_moves_order_total_into_held_balance(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W1');
        $order = $this->makeOrder($store, 50000);

        $service->hold($order);

        $wallet = $store->wallet->refresh();
        $this->assertSame(0.0, (float) $wallet->available_balance);
        $this->assertSame(50000.0, (float) $wallet->held_balance);
        $this->assertSame(1, $wallet->transactions()->where('type', 'hold')->count());
    }

    public function test_hold_is_idempotent(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W2');
        $order = $this->makeOrder($store, 50000);

        $service->hold($order);
        $service->hold($order);

        $wallet = $store->wallet->refresh();
        $this->assertSame(50000.0, (float) $wallet->held_balance);
        $this->assertSame(1, $wallet->transactions()->count());
    }

    public function test_settle_after_hold_releases_funds_to_available(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W3');
        $order = $this->makeOrder($store, 50000);

        $service->hold($order);
        $service->settle($order);

        $wallet = $store->wallet->refresh();
        $this->assertSame(50000.0, (float) $wallet->available_balance);
        $this->assertSame(0.0, (float) $wallet->held_balance);

        $types = $wallet->transactions()->orderBy('created_at')->pluck('type')->all();
        $this->assertSame(['hold', 'release'], $types);
    }

    public function test_settle_without_hold_credits_available_directly(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W4');
        $order = $this->makeOrder($store, 30000);

        $service->settle($order);

        $wallet = $store->wallet->refresh();
        $this->assertSame(30000.0, (float) $wallet->available_balance);
        $this->assertSame(1, $wallet->transactions()->where('type', 'credit')->count());
    }

    public function test_settle_is_idempotent(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W5');
        $order = $this->makeOrder($store, 30000);

        $service->settle($order);
        $service->settle($order);

        $wallet = $store->wallet->refresh();
        $this->assertSame(30000.0, (float) $wallet->available_balance);
        $this->assertSame(1, $wallet->transactions()->count());
    }

    public function test_reverse_hold_returns_funds_without_crediting_available(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W6');
        $order = $this->makeOrder($store, 40000);

        $service->hold($order);
        $service->reverseHold($order);

        $wallet = $store->wallet->refresh();
        $this->assertSame(0.0, (float) $wallet->held_balance);
        $this->assertSame(0.0, (float) $wallet->available_balance);

        $types = $wallet->transactions()->orderBy('created_at')->pluck('type')->all();
        $this->assertSame(['hold', 'debit'], $types);
    }

    public function test_reverse_hold_without_hold_is_a_noop(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W7');
        $order = $this->makeOrder($store, 40000);

        $service->reverseHold($order);

        $this->assertSame(0, WalletTransaction::count());
    }

    public function test_ledger_records_balance_before_and_after(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W8');
        $order = $this->makeOrder($store, 50000);

        $service->hold($order);

        $transaction = WalletTransaction::where('type', 'hold')->firstOrFail();
        $this->assertSame(0.0, (float) $transaction->balance_before);
        $this->assertSame(50000.0, (float) $transaction->balance_after);
        $this->assertSame(Order::class, $transaction->reference_type);
        $this->assertSame($order->id, $transaction->reference_id);
    }

    public function test_withdrawable_only_reserves_pending_requests(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W9');
        $wallet = $service->walletFor($store);
        $wallet->update(['available_balance' => 100000]);

        // Approved lewat process() -> ini memotong available_balance secara langsung,
        // jadi TIDAK boleh direserve lagi oleh withdrawable().
        $toApprove = WithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'store_id' => $store->id,
            'amount' => 10000,
            'bank_name' => 'BCA',
            'account_number' => '12345',
            'account_name' => 'Pemilik',
            'status' => 'pending',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Administrator');
        $service->process($toApprove, 'approve', $admin);

        foreach (['pending' => 40000, 'rejected' => 20000, 'completed' => 5000] as $status => $amount) {
            WithdrawalRequest::create([
                'wallet_id' => $wallet->id,
                'store_id' => $store->id,
                'amount' => $amount,
                'bank_name' => 'BCA',
                'account_number' => '12345',
                'account_name' => 'Pemilik',
                'status' => $status,
            ]);
        }

        // available_balance: 100000 - 10000 (sudah dipotong saat approve) = 90000
        // reserved: hanya yang pending (40000)
        // withdrawable: 90000 - 40000 = 50000
        $this->assertSame(50000.0, $service->withdrawable($wallet->refresh()));
    }

    public function test_request_withdrawal_creates_pending_request(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W10');
        $wallet = $service->walletFor($store);
        $wallet->update(['available_balance' => 100000]);

        $withdrawal = $service->requestWithdrawal($wallet, [
            'amount' => 30000,
            'bank_name' => 'BCA',
            'account_number' => '12345',
            'account_name' => 'Pemilik',
        ]);

        $this->assertSame('pending', $withdrawal->status);
        $this->assertSame(70000.0, $service->withdrawable($wallet));
    }

    public function test_request_withdrawal_rejects_amount_over_withdrawable(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W11');
        $wallet = $service->walletFor($store);
        $wallet->update(['available_balance' => 100000]);

        $this->expectException(ValidationException::class);
        $service->requestWithdrawal($wallet, [
            'amount' => 150000,
            'bank_name' => 'BCA',
            'account_number' => '12345',
            'account_name' => 'Pemilik',
        ]);
    }

    public function test_request_withdrawal_rejects_non_positive_amount(): void
    {
        $service = app(StoreWalletService::class);
        $store = $this->makeStore('STR-W12');
        $wallet = $service->walletFor($store);
        $wallet->update(['available_balance' => 100000]);

        $this->expectException(ValidationException::class);
        $service->requestWithdrawal($wallet, [
            'amount' => 0,
            'bank_name' => 'BCA',
            'account_number' => '12345',
            'account_name' => 'Pemilik',
        ]);
    }
}
