<?php

namespace Tests\Unit;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\Customer\ChatService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Concerns\SeedsMarketplace;
use Tests\TestCase;

class ChatServiceTest extends TestCase
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

    protected function administrator(): User
    {
        return User::where('email', 'super@gmail.com')->firstOrFail();
    }

    protected function store(): Store
    {
        return Store::where('store_code', 'STR-DEMO1')->firstOrFail();
    }

    protected function product(): Product
    {
        return $this->store()->products()->where('slug', 'es-kopi-susu')->firstOrFail();
    }

    protected function makeStore(): Store
    {
        $user = User::factory()->create();

        return Store::create([
            'user_id' => $user->id,
            'store_code' => 'STR-CH-'.Str::upper(Str::random(4)),
            'store_name' => 'Toko Chat Lain',
            'slug' => 'toko-chat-'.Str::random(6),
            'rate_per_km' => 2000,
            'min_free_distance_km' => 0,
            'status' => 'active',
        ]);
    }

    public function test_start_reuses_existing_conversation_for_same_keys(): void
    {
        $service = app(ChatService::class);

        $first = $service->start($this->customer(), $this->store());
        $second = $service->start($this->customer(), $this->store());

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, ChatConversation::count());
    }

    public function test_start_scopes_conversation_by_product(): void
    {
        $service = app(ChatService::class);
        $product = $this->product();

        $withoutProduct = $service->start($this->customer(), $this->store());
        $withProduct = $service->start($this->customer(), $this->store(), $product->id);

        $this->assertNotSame($withoutProduct->id, $withProduct->id);
        $this->assertNull($withoutProduct->product_id);
        $this->assertSame($product->id, $withProduct->product_id);
    }

    public function test_start_rejects_product_of_another_store(): void
    {
        $service = app(ChatService::class);
        $otherStore = $this->makeStore();
        $otherProduct = Product::create([
            'store_id' => $otherStore->id,
            'name' => 'Produk Lain',
            'slug' => 'produk-lain-'.Str::random(6),
            'status' => 'active',
        ]);

        $this->expectException(ModelNotFoundException::class);
        $service->start($this->customer(), $this->store(), $otherProduct->id);
    }

    public function test_send_persists_and_trims_the_message(): void
    {
        $service = app(ChatService::class);
        $conversation = $service->start($this->customer(), $this->store());

        $message = $service->send($conversation, 'customer', $this->customer()->id, '  Halo toko, stok masih ada?  ');

        $this->assertSame('Halo toko, stok masih ada?', $message->message);
        $this->assertSame(1, ChatMessage::where('conversation_id', $conversation->id)->count());
    }

    public function test_mark_read_only_updates_counterpart_messages(): void
    {
        $service = app(ChatService::class);
        $conversation = $service->start($this->customer(), $this->store());

        $service->send($conversation, 'customer', $this->customer()->id, 'Halo');
        $service->send($conversation, 'store', $this->owner()->id, 'Ada');
        $service->send($conversation, 'customer', $this->customer()->id, 'Terima kasih');

        $service->markRead($conversation, 'store');

        $this->assertSame(2, ChatMessage::where('conversation_id', $conversation->id)->where('sender_type', 'customer')->whereNotNull('read_at')->count());
        $this->assertSame(0, ChatMessage::where('conversation_id', $conversation->id)->where('sender_type', 'store')->whereNotNull('read_at')->count());
    }

    public function test_authorize_customer_allows_participant(): void
    {
        $service = app(ChatService::class);
        $conversation = $service->start($this->customer(), $this->store());

        $service->authorizeCustomer($conversation, $this->customer());

        $this->assertTrue(true);
    }

    public function test_authorize_customer_rejects_other_customer(): void
    {
        $service = app(ChatService::class);
        $conversation = $service->start($this->customer(), $this->store());
        $other = Customer::create([
            'name' => 'Orang Lain',
            'email' => 'other-'.Str::random(8).'@example.com',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $this->expectException(HttpException::class);
        $service->authorizeCustomer($conversation, $other);
    }

    public function test_authorize_store_allows_owner(): void
    {
        $service = app(ChatService::class);
        $conversation = $service->start($this->customer(), $this->store());

        $service->authorizeStore($conversation, $this->owner());

        $this->assertTrue(true);
    }

    public function test_authorize_store_allows_administrator(): void
    {
        $service = app(ChatService::class);
        $conversation = $service->start($this->customer(), $this->store());

        $service->authorizeStore($conversation, $this->administrator());

        $this->assertTrue(true);
    }

    public function test_authorize_store_rejects_unrelated_user(): void
    {
        $service = app(ChatService::class);
        $conversation = $service->start($this->customer(), $this->store());
        $stranger = User::factory()->create();

        $this->expectException(HttpException::class);
        $service->authorizeStore($conversation, $stranger);
    }

    public function test_conversations_for_store_scopes_non_admin_to_owned_stores(): void
    {
        $service = app(ChatService::class);
        $otherStore = $this->makeStore();

        $owned = $service->start($this->customer(), $this->store());
        $service->start($this->customer(), $otherStore);

        $result = $service->conversationsForStore($this->owner());

        $this->assertSame(1, $result->total());
        $this->assertSame($owned->id, $result->first()->id);
    }
}
