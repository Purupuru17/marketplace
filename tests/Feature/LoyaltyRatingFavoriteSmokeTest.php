<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\LocationNode;
use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Customer\LoyaltyService;
use Database\Seeders\CatalogDataSeeder;
use Database\Seeders\CustomerDataSeeder;
use Database\Seeders\LocationDataSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\StoreDataSeeder;
use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyRatingFavoriteSmokeTest extends TestCase
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

    protected function placeOrder(string $sku, int $points = 0): Order
    {
        $variant = ProductVariant::where('sku', $sku)->firstOrFail();
        $address = $this->seedAddress();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 1]);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => 'bank_transfer',
                'points' => $points,
            ]);

        $invoice = Invoice::where('customer_id', $this->customer()->id)->latest('created_at')->firstOrFail();

        return $invoice->orders()->firstOrFail();
    }

    protected function completeOrder(Order $order): void
    {
        foreach (['processing', 'shipped', 'completed'] as $status) {
            $this->actingAs($this->owner(), 'web')
                ->post(route('toko.order.update', $order->id), ['status' => $status]);
        }
    }

    public function test_points_are_earned_when_order_completed(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $expectedPoints = (int) floor((float) $order->total / 5000);

        $this->assertSame(0, PointTransaction::where('reference_id', $order->id)->count());

        $this->completeOrder($order);

        $this->assertSame(1, PointTransaction::where('reference_id', $order->id)->where('type', 'earn')->count());
        $this->assertSame($expectedPoints, PointTransaction::where('reference_id', $order->id)->value('points'));
        $this->assertSame($expectedPoints, PointTransaction::where('customer_id', $this->customer()->id)->sum('points'));
    }

    public function test_points_are_not_earned_twice_for_same_order(): void
    {
        $order = $this->placeOrder('NGS-REG');

        $this->completeOrder($order);

        $this->assertSame(1, PointTransaction::where('reference_id', $order->id)->where('type', 'earn')->count());

        app(LoyaltyService::class)->creditEarn($order);

        $this->assertSame(1, PointTransaction::where('reference_id', $order->id)->where('type', 'earn')->count());
    }

    public function test_redeem_points_reduces_grand_total(): void
    {
        PointTransaction::create([
            'customer_id' => $this->customer()->id,
            'type' => 'earn',
            'points' => 200,
            'reference_type' => null,
            'description' => 'Saldo awal',
        ]);

        $order = $this->placeOrder('NGS-REG', 100);

        $invoice = $order->invoice;

        $this->assertSame(100, $invoice->points_used);
        $this->assertSame('1000.00', (string) $invoice->total_discount);
        $this->assertSame('24000.00', (string) $invoice->grand_total);
        $this->assertSame('25000.00', (string) $order->total);

        $this->assertDatabaseHas('point_transactions', [
            'customer_id' => $this->customer()->id,
            'type' => 'redeem',
            'points' => -100,
        ]);

        $this->assertSame(100, PointTransaction::where('customer_id', $this->customer()->id)->sum('points'));
    }

    public function test_redeem_rejects_below_minimum_points(): void
    {
        $address = $this->seedAddress();
        $variant = ProductVariant::where('sku', 'NGS-REG')->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 1]);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => 'bank_transfer',
                'points' => 50,
            ])
            ->assertSessionHasErrors('points');

        $this->assertSame(0, Invoice::where('customer_id', $this->customer()->id)->count());
    }

    public function test_redeem_rejects_points_not_multiple_of_hundred(): void
    {
        $address = $this->seedAddress();
        $variant = ProductVariant::where('sku', 'NGS-REG')->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.cart.store'), ['variant_id' => $variant->id, 'qty' => 1]);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => 'bank_transfer',
                'points' => 150,
            ])
            ->assertSessionHasErrors('points');
    }

    public function test_customer_can_rate_completed_order_item(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $this->completeOrder($order);

        $item = $order->items()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.rating.store'), [
                'order_item_id' => $item->id,
                'rating' => 5,
                'review' => 'Enak banget!',
            ]);

        $rating = ProductRating::where('order_item_id', $item->id)->firstOrFail();

        $this->assertSame(5, $rating->rating);
        $this->assertSame('Enak banget!', $rating->review);
        $this->assertSame('active', $rating->status);
        $this->assertSame($item->product_id, $rating->product_id);
    }

    public function test_customer_cannot_rate_uncompleted_order(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $item = $order->items()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.rating.store'), [
                'order_item_id' => $item->id,
                'rating' => 4,
                'review' => 'Masih diproses.',
            ])
            ->assertSessionHasErrors('order_item_id');

        $this->assertSame(0, ProductRating::count());
    }

    public function test_duplicate_rating_is_rejected(): void
    {
        $order = $this->placeOrder('NGS-REG');
        $this->completeOrder($order);

        $item = $order->items()->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.rating.store'), [
                'order_item_id' => $item->id,
                'rating' => 5,
            ]);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.rating.store'), [
                'order_item_id' => $item->id,
                'rating' => 3,
            ])
            ->assertSessionHasErrors('order_item_id');

        $this->assertSame(1, ProductRating::where('order_item_id', $item->id)->count());
    }

    public function test_favorite_toggle_adds_and_removes(): void
    {
        $product = Product::where('slug', 'nasi-goreng-spesial')->firstOrFail();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.favorite.toggle'), ['product_id' => $product->id]);

        $this->assertDatabaseHas('favorite_products', [
            'customer_id' => $this->customer()->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($this->customer(), 'customer')
            ->get(route('customer.favorite.index'))
            ->assertOk()
            ->assertSee('Nasi Goreng Spesial');

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.favorite.toggle'), ['product_id' => $product->id]);

        $this->assertDatabaseMissing('favorite_products', [
            'customer_id' => $this->customer()->id,
            'product_id' => $product->id,
        ]);
    }
}
