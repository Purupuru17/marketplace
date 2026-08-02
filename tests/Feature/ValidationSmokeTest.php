<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\LocationNode;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use App\Services\Customer\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class ValidationSmokeTest extends TestCase
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

    protected function variant(string $sku): ProductVariant
    {
        return ProductVariant::where('sku', $sku)->firstOrFail();
    }

    protected function createAddress(): CustomerAddress
    {
        return CustomerAddress::create([
            'customer_id' => $this->customer()->id,
            'location_node_id' => LocationNode::where('name', 'Kota Jakarta')->firstOrFail()->id,
            'label' => 'Rumah',
            'recipient_name' => 'Dina Puspita',
            'phone' => '081234567891',
            'full_address' => 'Jl. Melati No. 1',
        ]);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        $this->post(route('customer.auth.register.store'), [
            'name' => 'Dina Baru',
            'email' => $this->customer()->email,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors('email');
    }

    public function test_register_rejects_invalid_email_and_short_password(): void
    {
        $this->post(route('customer.auth.register.store'), [
            'name' => 'Dina Baru',
            'email' => 'bukan-email',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_register_requires_password_confirmation_match(): void
    {
        $this->post(route('customer.auth.register.store'), [
            'name' => 'Dina Baru',
            'email' => 'dina-baru@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'different123',
        ])->assertSessionHasErrors('password');
    }

    public function test_cart_requires_variant_id(): void
    {
        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['qty' => 1])
            ->assertSessionHasErrors('variant_id');
    }

    public function test_cart_rejects_quantity_above_stock(): void
    {
        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $this->variant('NGS-REG')->id, 'qty' => 100])
            ->assertSessionHasErrors('qty');
    }

    public function test_checkout_requires_address_id(): void
    {
        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), ['payment_method' => 'bank_transfer'])
            ->assertSessionHasErrors('address_id');
    }

    public function test_checkout_rejects_invalid_payment_method(): void
    {
        $address = $this->createAddress();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => 'cash_on_delivery_baru',
            ])
            ->assertSessionHasErrors('payment_method');
    }

    public function test_customer_chat_rejects_empty_message(): void
    {
        $conversation = app(ChatService::class)->start($this->customer(), $this->store());

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.chat.store', $conversation->id), ['message' => ''])
            ->assertSessionHasErrors('message');
    }

    public function test_customer_chat_rejects_overlong_message(): void
    {
        $conversation = app(ChatService::class)->start($this->customer(), $this->store());

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.chat.store', $conversation->id), ['message' => str_repeat('a', 2001)])
            ->assertSessionHasErrors('message');
    }

    public function test_promotion_requires_name(): void
    {
        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.promotion.store'), [
                'store_id' => $this->store()->id,
                'source' => 'store',
                'type' => 'percentage',
                'value' => 10,
                'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addMonth()->format('Y-m-d\TH:i'),
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_promotion_rejects_end_before_start(): void
    {
        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.promotion.store'), [
                'store_id' => $this->store()->id,
                'name' => 'Promo Salah Tanggal',
                'source' => 'store',
                'type' => 'percentage',
                'value' => 10,
                'starts_at' => now()->addMonth()->format('Y-m-d\TH:i'),
                'ends_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'status' => 'active',
            ])
            ->assertSessionHasErrors('ends_at');
    }

    public function test_promotion_rejects_value_below_minimum(): void
    {
        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.promotion.store'), [
                'store_id' => $this->store()->id,
                'name' => 'Promo Nol',
                'source' => 'store',
                'type' => 'percentage',
                'value' => 0,
                'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addMonth()->format('Y-m-d\TH:i'),
                'status' => 'active',
            ])
            ->assertSessionHasErrors('value');
    }
}
