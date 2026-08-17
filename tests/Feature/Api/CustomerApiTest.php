<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushTestCache();
        $this->seedMarketplace();
    }

    public function test_register_returns_token_and_customer(): void
    {
        $response = $this->postJson('/api/v1/customer/register', [
            'name' => 'Budi Baru',
            'email' => 'budi.baru@gmail.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['customer', 'token']]);

        $this->assertSame('Budi Baru', $response->json('data.customer.name'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_register_rejects_duplicate_email(): void
    {
        $this->postJson('/api/v1/customer/register', [
            'name' => 'Dina Dupe',
            'email' => 'dina@gmail.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertStatus(422);
    }

    public function test_login_returns_token(): void
    {
        $this->postJson('/api/v1/customer/login', [
            'email' => 'dina@gmail.com',
            'password' => '12345',
        ])->assertOk()->assertJsonStructure(['data' => ['customer', 'token']]);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $this->postJson('/api/v1/customer/login', [
            'email' => 'dina@gmail.com',
            'password' => 'salah',
        ])->assertStatus(422);
    }

    public function test_me_requires_auth(): void
    {
        $this->getJson('/api/v1/customer/me')->assertStatus(401);
    }

    public function test_me_with_token(): void
    {
        $token = $this->tokenFor('dina@gmail.com');

        $this->withToken($token)->getJson('/api/v1/customer/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'dina@gmail.com');
    }

    public function test_storefront_lists_stores(): void
    {
        $this->getJson('/api/v1/storefront/stores')
            ->assertOk()
            ->assertJsonStructure(['data' => ['items', 'pagination']]);
    }

    public function test_storefront_lists_categories(): void
    {
        $this->getJson('/api/v1/storefront/categories')
            ->assertOk()
            ->assertJsonStructure(['data' => ['items']]);
    }

    public function test_storefront_search_products(): void
    {
        $this->getJson('/api/v1/storefront/products?search=Nasi')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonStructure(['data' => ['items' => [['id', 'name', 'variants']]]]);
    }

    public function test_storefront_product_detail(): void
    {
        $response = $this->getJson('/api/v1/storefront/stores/toko-berkah/products/nasi-goreng-spesial');

        $response->assertOk()
            ->assertJsonPath('data.name', 'Nasi Goreng Spesial')
            ->assertJsonStructure(['data' => ['id', 'name', 'slug', 'variants', 'promotions', 'rating', 'ratings', 'category', 'store']]);
    }

    public function test_storefront_product_detail_unknown_store(): void
    {
        $this->getJson('/api/v1/storefront/stores/tidak-ada/products/nasi-goreng-spesial')->assertStatus(404);
    }

    public function test_cart_add_with_token(): void
    {
        $variant = ProductVariant::where('sku', 'NGS-REG')->firstOrFail();
        $token = $this->tokenFor('dina@gmail.com');

        $this->withToken($token)->postJson('/api/v1/customer/cart', [
            'variant_id' => $variant->id,
            'qty' => 2,
        ])->assertStatus(201)
            ->assertJsonPath('data.count', 2);
    }

    public function test_cart_requires_auth(): void
    {
        $this->getJson('/api/v1/customer/cart')->assertStatus(401);
    }

    protected function tokenFor(string $email): string
    {
        $customer = Customer::where('email', $email)->firstOrFail();

        return $customer->createToken('test')->plainTextToken;
    }
}
