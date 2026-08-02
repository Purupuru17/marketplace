<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\Customer\ChatService;
use App\Support\ChatChannel;
use Database\Seeders\CatalogDataSeeder;
use Database\Seeders\CustomerDataSeeder;
use Database\Seeders\LocationDataSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\StoreDataSeeder;
use IdCore\CoreStarter\Database\Seeders\CoreDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class ChatSmokeTest extends TestCase
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

    protected function owner(): User
    {
        return User::where('email', 'toko@gmail.com')->firstOrFail();
    }

    protected function customer(): Customer
    {
        return Customer::where('email', 'dina@gmail.com')->firstOrFail();
    }

    protected function store(): Store
    {
        return Store::where('store_code', 'STR-DEMO1')->firstOrFail();
    }

    protected function product(): Product
    {
        return $this->store()->products()->where('slug', 'es-kopi-susu')->firstOrFail();
    }

    protected function enablePusherBroadcast(): void
    {
        Broadcast::forgetDrivers();
        config()->set('broadcasting.default', 'pusher');
        config()->set('broadcasting.connections.pusher.key', 'test-key');
        config()->set('broadcasting.connections.pusher.secret', 'test-secret');
        config()->set('broadcasting.connections.pusher.app_id', 'test-app');
        Broadcast::channel('chat.{conversation}', ChatChannel::class);
    }

    public function test_customer_starts_conversation_from_product(): void
    {
        $product = $this->product();

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.chat.start'), [
                'store_id' => $this->store()->id,
                'product_id' => $product->id,
            ])
            ->assertRedirect();

        $conversation = ChatConversation::where('customer_id', $this->customer()->id)
            ->where('store_id', $this->store()->id)
            ->firstOrFail();

        $this->assertSame($product->id, $conversation->product_id);
    }

    public function test_customer_cannot_start_conversation_with_inactive_store(): void
    {
        $otherStore = Store::create([
            'user_id' => $this->owner()->id,
            'store_name' => 'Toko Nonaktif',
            'store_code' => 'STR-TEST-NA',
            'slug' => 'toko-nonaktif',
            'status' => 'inactive',
        ]);

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.chat.start'), [
                'store_id' => $otherStore->id,
            ])
            ->assertNotFound();
    }

    public function test_customer_sends_message_and_it_is_persisted(): void
    {
        $conversation = app(ChatService::class)->start(
            $this->customer(),
            $this->store(),
            $this->product()->id
        );

        $this->actingAs($this->customer(), 'customer')
            ->post(route('customer.chat.store', $conversation->id), [
                'message' => 'Halo, apakah produk ini ready?',
            ])
            ->assertRedirect();

        $message = $conversation->messages()->firstOrFail();

        $this->assertSame('customer', $message->sender_type);
        $this->assertSame($this->customer()->id, $message->sender_id);
        $this->assertSame('Halo, apakah produk ini ready?', $message->message);
        $this->assertNull($message->read_at);
    }

    public function test_owner_sees_customer_conversation_and_replies(): void
    {
        $conversation = app(ChatService::class)->start(
            $this->customer(),
            $this->store(),
            $this->product()->id
        );

        app(ChatService::class)->send(
            $conversation,
            'customer',
            $this->customer()->id,
            'Stok tersedia?'
        );

        $this->actingAs($this->owner(), 'web')
            ->get(route('toko.chat.index'))
            ->assertOk()
            ->assertSee('Stok tersedia?');

        $this->actingAs($this->owner(), 'web')
            ->post(route('toko.chat.store', $conversation->id), [
                'message' => 'Ya, tersedia.',
            ])
            ->assertRedirect();

        $reply = $conversation->messages()->where('sender_type', 'store')->firstOrFail();

        $this->assertSame('store', $reply->sender_type);
        $this->assertSame($this->owner()->id, $reply->sender_id);
        $this->assertSame('Ya, tersedia.', $reply->message);
    }

    public function test_owner_cannot_open_another_stores_conversation(): void
    {
        $otherUser = User::create([
            'name' => 'Pemilik Lain',
            'email' => 'lain@gmail.com',
            'password' => '12345',
        ]);

        $storeB = Store::create([
            'user_id' => $otherUser->id,
            'store_name' => 'Toko Lain',
            'store_code' => 'STR-TEST-B',
            'slug' => 'toko-lain',
            'status' => 'active',
        ]);

        $conversation = app(ChatService::class)->start(
            $this->customer(),
            $storeB,
            null
        );

        $this->actingAs($this->owner(), 'web')
            ->get(route('toko.chat.show', $conversation->id))
            ->assertForbidden();
    }

    public function test_customer_cannot_open_another_customers_conversation(): void
    {
        $otherCustomer = Customer::create([
            'name' => 'Budi',
            'email' => 'budi@gmail.com',
            'phone' => '081222333444',
            'password' => '12345',
            'is_active' => true,
        ]);

        $conversation = app(ChatService::class)->start(
            $otherCustomer,
            $this->store(),
            null
        );

        $this->actingAs($this->customer(), 'customer')
            ->get(route('customer.chat.show', $conversation->id))
            ->assertForbidden();
    }

    public function test_reading_conversation_marks_incoming_messages_as_read(): void
    {
        $conversation = app(ChatService::class)->start(
            $this->customer(),
            $this->store(),
            null
        );

        app(ChatService::class)->send(
            $conversation,
            'store',
            $this->owner()->id,
            'Halo, ada yang bisa dibantu?'
        );

        $this->actingAs($this->customer(), 'customer')
            ->get(route('customer.chat.show', $conversation->id))
            ->assertOk();

        $this->assertNotNull(
            $conversation->messages()->where('sender_type', 'store')->firstOrFail()->read_at
        );
    }

    public function test_customer_can_authenticate_private_channel(): void
    {
        $conversation = app(ChatService::class)->start(
            $this->customer(),
            $this->store(),
            null
        );

        $this->enablePusherBroadcast();

        $this->actingAs($this->customer(), 'customer')
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-chat.'.$conversation->id,
                'socket_id' => '123.456',
            ])
            ->assertOk()
            ->assertJsonPath('auth', fn ($auth) => str_starts_with($auth, 'test-key:'));
    }

    public function test_store_owner_can_authenticate_private_channel(): void
    {
        $conversation = app(ChatService::class)->start(
            $this->customer(),
            $this->store(),
            null
        );

        $this->enablePusherBroadcast();

        $this->actingAs($this->owner(), 'web')
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-chat.'.$conversation->id,
                'socket_id' => '123.456',
            ])
            ->assertOk();
    }

    public function test_foreign_customer_cannot_authenticate_private_channel(): void
    {
        $conversation = app(ChatService::class)->start(
            $this->customer(),
            $this->store(),
            null
        );

        $otherCustomer = Customer::create([
            'name' => 'Budi',
            'email' => 'budi@gmail.com',
            'phone' => '081222333444',
            'password' => '12345',
            'is_active' => true,
        ]);

        $this->enablePusherBroadcast();

        $this->actingAs($otherCustomer, 'customer')
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-chat.'.$conversation->id,
                'socket_id' => '123.456',
            ])
            ->assertForbidden();
    }
}
