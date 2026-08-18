<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    protected function owner(): User
    {
        return User::where('email', 'toko@gmail.com')->firstOrFail();
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
        $storeId = $this->cartStoreId();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'stores' => [$storeId => ['fulfillment_type' => 'delivery', 'payment_method' => $method]],
            ]);

        return Invoice::where('customer_id', $this->customer()->id)->latest('created_at')->firstOrFail();
    }

    protected function cartStoreId(): string
    {
        return ProductVariant::where('sku', 'NGS-REG')->firstOrFail()->store_id;
    }

    protected function markPaid(Invoice $invoice): void
    {
        $order = $invoice->orders()->firstOrFail();

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.order.paid', $order->id))
            ->assertRedirect();
    }

    public function test_checkout_requires_payment_method(): void
    {
        $this->addToCart('NGS-REG');
        $address = $this->seedAddress();
        $storeId = $this->cartStoreId();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'stores' => [$storeId => ['fulfillment_type' => 'delivery']],
            ])
            ->assertSessionHasErrors("stores.{$storeId}.payment_method");
    }

    public function test_checkout_rejects_unknown_payment_method(): void
    {
        $this->addToCart('NGS-REG');
        $address = $this->seedAddress();
        $storeId = $this->cartStoreId();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'stores' => [$storeId => ['fulfillment_type' => 'delivery', 'payment_method' => 'bitcoin']],
            ])
            ->assertSessionHasErrors("stores.{$storeId}.payment_method");
    }

    public function test_place_order_creates_bank_transfer_pending_payment(): void
    {
        $invoice = $this->placeOrder('bank_transfer');

        $this->assertEquals('pending', $invoice->status);

        $payment = $invoice->payments()->firstOrFail();
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('bank_transfer', $payment->payment_method);
        $this->assertEquals('manual', $payment->provider);
        $this->assertEquals($invoice->grand_total, $payment->amount);
        $this->assertNull($payment->expired_at);
    }

    public function test_place_order_creates_cash_payment(): void
    {
        $invoice = $this->placeOrder('cash');

        $payment = $invoice->payments()->firstOrFail();
        $this->assertEquals('cash', $payment->payment_method);
        $this->assertEquals('cash', $payment->provider);
        $this->assertEquals('pending', $payment->status);
        $this->assertNull($payment->expired_at);
        $this->assertEquals('pending', $invoice->status);
    }

    public function test_payment_page_shows_bank_instructions(): void
    {
        $invoice = $this->placeOrder('bank_transfer');
        $payment = $invoice->payments()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->get(route('customer.payment.show', $payment->id))
            ->assertOk()
            ->assertSee('No. Rekening')
            ->assertSee('Menunggu konfirmasi pembayaran dari toko');
    }

    public function test_store_marks_payment_paid_and_orders_processing(): void
    {
        $invoice = $this->placeOrder('bank_transfer');
        $payment = $invoice->payments()->firstOrFail();

        $this->markPaid($invoice);

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
                'changed_by_type' => 'store',
            ]);
        }
    }

    public function test_mark_paid_is_idempotent(): void
    {
        $invoice = $this->placeOrder('bank_transfer');
        $payment = $invoice->payments()->firstOrFail();

        $this->markPaid($invoice);
        $this->markPaid($invoice);

        $this->assertEquals('paid', $payment->refresh()->status);
        $this->assertEquals(1, $invoice->payments()->count());
    }

    public function test_cannot_view_payment_of_another_customer(): void
    {
        $invoice = $this->placeOrder('bank_transfer');
        $payment = $invoice->payments()->firstOrFail();

        $other = Customer::create([
            'name' => 'Budi',
            'email' => 'budi@gmail.com',
            'password' => Hash::make('12345'),
            'phone' => '089876543210',
            'status' => 'active',
        ]);

        $this->actingAs($other, 'customer')
            ->get(route('customer.payment.show', $payment->id))
            ->assertForbidden();
    }

    public function test_customer_uploads_bank_transfer_proof(): void
    {
        Storage::fake('public');

        $invoice = $this->placeOrder('bank_transfer');
        $payment = $invoice->payments()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.payment.proof', $payment->id), [
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect();

        $payment->refresh();

        $this->assertNotNull($payment->payment_proof_path);
        Storage::disk('public')->assertExists($payment->payment_proof_path);
    }

    public function test_proof_upload_rejected_for_cash_payment(): void
    {
        Storage::fake('public');

        $invoice = $this->placeOrder('cash');
        $payment = $invoice->payments()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.payment.proof', $payment->id), [
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertSessionHasErrors('proof');

        $this->assertNull($payment->refresh()->payment_proof_path);
    }
}
