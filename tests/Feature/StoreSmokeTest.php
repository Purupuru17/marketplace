<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\StoreLevel;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class StoreSmokeTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMarketplace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushTestCache();
        $this->seedCore();
        $this->seedMaster();
        $this->seedLocations();
        $this->seedStores();
    }

    public function test_index_and_create_pages_render(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();

        $pages = [
            ['toko.store.index', null, 'Data Toko'],
            ['toko.store.create', null, 'Tambah Toko'],
            ['toko.subscription.index', null, 'Data Subscription'],
            ['toko.subscription.create', null, 'Tambah Subscription'],
            ['toko.subscription-invoice.index', null, 'Data Invoice Subscription'],
            ['toko.subscription-invoice.create', null, 'Tambah Invoice Subscription'],
        ];

        foreach ($pages as [$name, $param, $needle]) {
            $url = $param ? route($name, $param) : route($name);

            $this->actingAs($user)->get($url)
                ->assertOk()
                ->assertSee($needle, false);
        }
    }

    public function test_crud_store_with_operating_hours(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $owner = User::where('email', 'toko@gmail.com')->firstOrFail();
        $level = StoreLevel::where('name', 'Basic')->firstOrFail();

        $this->actingAs($user)
            ->post(route('toko.store.store'), [
                'user_id' => $owner->id,
                'store_level_id' => $level->id,
                'store_name' => 'Toko Sejahtera',
                'rate_per_km' => 2500,
                'min_free_distance_km' => 2,
                'max_radius_km' => 20,
                'status' => 'active',
                'hours' => [
                    'monday' => ['is_open' => '1', 'opens_at' => '08:00', 'closes_at' => '21:00'],
                    'sunday' => ['is_open' => '0', 'opens_at' => '', 'closes_at' => ''],
                ],
            ])
            ->assertRedirect(route('toko.store.index'));

        $store = Store::where('store_name', 'Toko Sejahtera')->firstOrFail();

        $this->assertNotNull($store->store_code);
        $this->assertSame('toko-sejahtera', $store->slug);
        $this->assertSame(1, $store->operatingHours()->where('day', 'monday')->where('is_open', 1)->count());
        $this->assertSame(1, $store->operatingHours()->where('day', 'sunday')->where('is_open', 0)->count());
    }

    public function test_subscription_create_auto_generates_invoice(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $store = Store::where('store_code', 'STR-DEMO1')->firstOrFail();
        $premium = StoreLevel::where('name', 'Premium')->firstOrFail();

        $this->actingAs($user)
            ->post(route('toko.subscription.store'), [
                'store_id' => $store->id,
                'store_level_id' => $premium->id,
                'starts_at' => '2026-09-01',
                'ends_at' => '2026-09-30',
                'status' => 'active',
                'auto_renew' => 1,
            ])
            ->assertRedirect(route('toko.subscription.index'));

        $subscription = Subscription::where('store_id', $store->id)
            ->where('store_level_id', $premium->id)
            ->latest('created_at')
            ->firstOrFail();

        $invoice = $subscription->invoices()->firstOrFail();

        $this->assertSame('pending', $invoice->status);
        $this->assertSame((float) $premium->price, (float) $invoice->amount);
        $this->assertMatchesRegularExpression('/^INV-SUB-/', $invoice->invoice_no);
    }

    public function test_invoice_can_be_marked_paid(): void
    {
        $user = User::where('email', 'super@gmail.com')->firstOrFail();
        $invoice = SubscriptionInvoice::where('invoice_no', 'INV-DEMO-001')->firstOrFail();

        $this->actingAs($user)
            ->put(route('toko.subscription-invoice.update', $invoice->id), [
                'subscription_id' => $invoice->subscription_id,
                'amount' => $invoice->amount,
                'due_at' => $invoice->due_at->toDateString(),
                'status' => 'paid',
            ])
            ->assertRedirect(route('toko.subscription-invoice.index'));

        $this->assertNotNull($invoice->refresh()->paid_at);
    }
}
