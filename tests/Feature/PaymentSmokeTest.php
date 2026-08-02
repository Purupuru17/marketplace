<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\PaymentWebhookLog;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class PaymentSmokeTest extends TestCase
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

    protected function placeOrder(string $method): Invoice
    {
        $this->addToCart('NGS-REG');
        $address = $this->seedAddress();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => $method,
            ]);

        return Invoice::where('customer_id', $this->customer()->id)->latest('created_at')->firstOrFail();
    }

    public function test_checkout_requires_payment_method(): void
    {
        $this->addToCart('NGS-REG');
        $address = $this->seedAddress();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertSessionHasErrors('payment_method');
    }

    public function test_checkout_rejects_unknown_payment_method(): void
    {
        $this->addToCart('NGS-REG');
        $address = $this->seedAddress();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => 'bitcoin',
            ])
            ->assertSessionHasErrors('payment_method');
    }

    public function test_place_order_creates_online_pending_payment(): void
    {
        $invoice = $this->placeOrder('bank_transfer');

        $this->assertEquals('pending', $invoice->status);

        $payment = $invoice->payments()->firstOrFail();
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('bank_transfer', $payment->payment_method);
        $this->assertEquals('simulated', $payment->provider);
        $this->assertEquals($invoice->grand_total, $payment->amount);
        $this->assertNotNull($payment->expired_at);
    }

    public function test_place_order_creates_cod_payment_without_expiry(): void
    {
        $invoice = $this->placeOrder('cod');

        $payment = $invoice->payments()->firstOrFail();
        $this->assertEquals('cod', $payment->payment_method);
        $this->assertEquals('cod', $payment->provider);
        $this->assertEquals('pending', $payment->status);
        $this->assertNull($payment->expired_at);
        $this->assertEquals('pending', $invoice->status);
    }

    public function test_payment_page_shows_instructions(): void
    {
        $invoice = $this->placeOrder('bank_transfer');
        $payment = $invoice->payments()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->get(route('customer.payment.show', $invoice->id))
            ->assertOk()
            ->assertSee('Virtual Account')
            ->assertSee(number_format((float) $payment->amount, 0, ',', '.'));
    }

    public function test_confirm_payment_marks_invoice_paid_and_orders_processing(): void
    {
        $invoice = $this->placeOrder('e_wallet');
        $payment = $invoice->payments()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.payment.store', $invoice->id))
            ->assertRedirect(route('customer.order.show', $invoice->id));

        $payment->refresh();
        $invoice->refresh();

        $this->assertEquals('paid', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('paid', $invoice->status);

        foreach ($invoice->orders as $order) {
            $this->assertEquals('processing', $order->status);
            $this->assertDatabaseHas('order_status_histories', [
                'order_id' => $order->id,
                'status_from' => 'pending',
                'status_to' => 'processing',
                'changed_by_type' => 'system',
            ]);
        }

        $this->assertDatabaseHas('payment_webhook_logs', [
            'payment_id' => $payment->id,
            'status' => 'success',
        ]);
    }

    public function test_confirm_payment_is_idempotent(): void
    {
        $invoice = $this->placeOrder('bank_transfer');
        $payment = $invoice->payments()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.payment.store', $invoice->id));

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.payment.store', $invoice->id));

        $this->assertEquals('paid', $payment->refresh()->status);
        $this->assertEquals(1, PaymentWebhookLog::where('payment_id', $payment->id)->count());
    }

    public function test_cannot_pay_invoice_of_another_customer(): void
    {
        $invoice = $this->placeOrder('bank_transfer');

        $other = Customer::create([
            'name' => 'Budi',
            'email' => 'budi@gmail.com',
            'password' => Hash::make('12345'),
            'phone' => '089876543210',
            'status' => 'active',
        ]);

        $this->actingAs($other, 'customer')
            ->get(route('customer.payment.show', $invoice->id))
            ->assertForbidden();

        $this->actingAs($other, 'customer')
            ->post(route('customer.payment.store', $invoice->id))
            ->assertForbidden();
    }

    public function test_expired_payment_triggers_recreate(): void
    {
        $invoice = $this->placeOrder('bank_transfer');
        $oldPayment = $invoice->payments()->firstOrFail();
        $oldPayment->update(['expired_at' => now()->subHour()]);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.payment.store', $invoice->id))
            ->assertRedirect(route('customer.payment.show', $invoice->id));

        $oldPayment->refresh();
        $this->assertEquals('failed', $oldPayment->status);

        $newPayment = $invoice->payments()->latest('created_at')->latest('id')->firstOrFail();
        $this->assertNotEquals($oldPayment->id, $newPayment->id);
        $this->assertEquals('pending', $newPayment->status);
        $this->assertEquals('pending', $invoice->refresh()->status);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.payment.store', $invoice->id));

        $this->assertEquals('paid', $newPayment->refresh()->status);
        $this->assertEquals('paid', $invoice->refresh()->status);
    }

    public function test_cod_has_no_payment_page(): void
    {
        $invoice = $this->placeOrder('cod');

        $this->actingAs($this->customer(), 'customer')
            ->get(route('customer.payment.show', $invoice->id))
            ->assertRedirect(route('customer.order.show', $invoice->id));
    }
}
