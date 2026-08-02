<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class CustomerAuthSmokeTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushTestCache();
        $this->seedMarketplace();
    }

    public function test_register_and_login_pages_render(): void
    {
        $this->get(route('customer.auth.register'))->assertOk()->assertSee('Daftar Akun', false);
        $this->get(route('customer.auth.login'))->assertOk()->assertSee('Masuk', false);
    }

    public function test_customer_can_register(): void
    {
        $this->post(route('customer.auth.register.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@gmail.com',
            'phone' => '081234567892',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('storefront.index'));

        $customer = Customer::where('email', 'budi@gmail.com')->firstOrFail();

        $this->assertSame('active', $customer->status);
        $this->assertSame(0, $customer->points);
        $this->assertNotNull($customer->customer_level_id);
        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertSame($customer->id, Auth::guard('customer')->id());
    }

    public function test_register_validates_unique_email(): void
    {
        $this->post(route('customer.auth.register.store'), [
            'name' => 'Dina Lagi',
            'email' => 'dina@gmail.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');
    }

    public function test_customer_can_login_and_logout(): void
    {
        $this->post(route('customer.auth.login.store'), [
            'email' => 'dina@gmail.com',
            'password' => '12345',
        ])->assertRedirect(route('storefront.index'));

        $this->assertTrue(Auth::guard('customer')->check());

        $this->post(route('customer.auth.logout'))->assertRedirect(route('storefront.index'));

        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_login_rejects_wrong_credentials(): void
    {
        $this->post(route('customer.auth.login.store'), [
            'email' => 'dina@gmail.com',
            'password' => 'salah123',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_inactive_customer_is_blocked(): void
    {
        $customer = Customer::where('email', 'dina@gmail.com')->firstOrFail();
        $customer->update(['status' => 'inactive']);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.cart.index'))
            ->assertForbidden();

        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_guest_cannot_access_cart(): void
    {
        $this->get(route('customer.cart.index'))
            ->assertRedirect(route('login'));
    }
}
