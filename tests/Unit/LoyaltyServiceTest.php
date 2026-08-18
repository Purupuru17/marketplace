<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\Store;
use App\Services\Customer\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class LoyaltyServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushTestCache();
        $this->seedMarketplace();
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

    protected function makeOrder(Customer $customer, int $total): Order
    {
        $store = Store::where('store_code', 'STR-DEMO1')->firstOrFail();
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
            'status' => 'completed',
            'subtotal' => $total,
            'total' => $total,
            'address_snapshot' => 'Jl. Unit Test No. 1',
        ]);
    }

    protected function credit(Customer $customer, int $points): void
    {
        PointTransaction::create([
            'customer_id' => $customer->id,
            'type' => 'earn',
            'points' => $points,
            'description' => 'Saldo awal unit test.',
        ]);
    }

    public function test_points_for_order_uses_floor_of_total_per_earn_rate(): void
    {
        $service = app(LoyaltyService::class);
        $customer = $this->makeCustomer();

        $this->assertSame(5, $service->pointsForOrder($this->makeOrder($customer, 25000)));
        $this->assertSame(5, $service->pointsForOrder($this->makeOrder($customer, 29999)));
        $this->assertSame(1, $service->pointsForOrder($this->makeOrder($customer, 5000)));
        $this->assertSame(0, $service->pointsForOrder($this->makeOrder($customer, 4999)));
    }

    public function test_redeem_value_floors_to_redeem_unit(): void
    {
        $service = app(LoyaltyService::class);

        $this->assertSame(1000, $service->redeemValue(100));
        $this->assertSame(1000, $service->redeemValue(150));
        $this->assertSame(2000, $service->redeemValue(200));
        $this->assertSame(0, $service->redeemValue(99));
    }

    public function test_credit_earn_creates_a_single_transaction_per_order(): void
    {
        $service = app(LoyaltyService::class);
        $customer = $this->makeCustomer();
        $order = $this->makeOrder($customer, 25000);

        $service->creditEarn($order);
        $service->creditEarn($order);

        $this->assertSame(1, PointTransaction::where('customer_id', $customer->id)->where('type', 'earn')->count());
        $this->assertSame(5, PointTransaction::where('customer_id', $customer->id)->sum('points'));
        $this->assertSame(5, $service->availablePoints($customer));
    }

    public function test_credit_earn_skips_orders_below_minimum_total(): void
    {
        $service = app(LoyaltyService::class);
        $customer = $this->makeCustomer();
        $order = $this->makeOrder($customer, 4999);

        $service->creditEarn($order);

        $this->assertSame(0, PointTransaction::where('customer_id', $customer->id)->count());
    }

    public function test_available_points_is_net_of_earn_and_redeem(): void
    {
        $service = app(LoyaltyService::class);
        $customer = $this->makeCustomer();
        $this->credit($customer, 200);

        $service->redeem($customer, Invoice::create([
            'invoice_no' => 'INV-UNIT-'.Str::random(8),
            'customer_id' => $customer->id,
            'subtotal' => 25000,
            'grand_total' => 24000,
            'status' => 'pending',
        ]), 100);

        $this->assertSame(100, $service->availablePoints($customer));
    }

    public function test_assert_redeemable_passes_for_valid_multiple(): void
    {
        $service = app(LoyaltyService::class);
        $customer = $this->makeCustomer();
        $this->credit($customer, 200);

        $service->assertRedeemable($customer, 200);

        $this->assertTrue(true);
    }

    public function test_assert_redeemable_rejects_below_minimum(): void
    {
        $service = app(LoyaltyService::class);
        $customer = $this->makeCustomer();
        $this->credit($customer, 200);

        $this->expectException(ValidationException::class);
        $service->assertRedeemable($customer, 50);
    }

    public function test_assert_redeemable_rejects_non_multiple(): void
    {
        $service = app(LoyaltyService::class);
        $customer = $this->makeCustomer();
        $this->credit($customer, 200);

        $this->expectException(ValidationException::class);
        $service->assertRedeemable($customer, 150);
    }

    public function test_assert_redeemable_rejects_points_exceeding_balance(): void
    {
        $service = app(LoyaltyService::class);
        $customer = $this->makeCustomer();
        $this->credit($customer, 100);

        $this->expectException(ValidationException::class);
        $service->assertRedeemable($customer, 200);
    }

    public function test_redeem_persists_ledger(): void
    {
        $service = app(LoyaltyService::class);
        $customer = $this->makeCustomer();
        $this->credit($customer, 300);

        $invoice = Invoice::create([
            'invoice_no' => 'INV-UNIT-'.Str::random(8),
            'customer_id' => $customer->id,
            'subtotal' => 25000,
            'grand_total' => 24000,
            'status' => 'pending',
        ]);

        $service->redeem($customer, $invoice, 200);

        $this->assertSame(100, $service->availablePoints($customer));
        $this->assertDatabaseHas('point_transactions', [
            'customer_id' => $customer->id,
            'type' => 'redeem',
            'points' => -200,
        ]);
    }
}
