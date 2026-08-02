<?php

namespace App\Services\Customer;

use App\Events\MessageSent;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function start(Customer $customer, Store $store, ?string $productId = null): ChatConversation
    {
        $product = null;

        if ($productId) {
            $product = Product::where('store_id', $store->id)->findOrFail($productId);
        }

        return ChatConversation::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'store_id' => $store->id,
                'product_id' => $product?->id,
            ]
        );
    }

    public function conversationsForCustomer(Customer $customer, int $perPage = 20): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->where('chat_conversations.customer_id', $customer->id)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function conversationsForStore(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->when(! $user->hasRole('Administrator'), function ($query) use ($user) {
                $query->whereIn('chat_conversations.store_id', $user->stores()->pluck('id'));
            })
            ->paginate($perPage)
            ->withQueryString();
    }

    public function send(ChatConversation $conversation, string $senderType, string $senderId, string $message): ChatMessage
    {
        $message = DB::transaction(function () use ($conversation, $senderType, $senderId, $message) {
            $conversation->lockForUpdate();

            return ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'message' => trim($message),
            ]);
        });

        try {
            broadcast(new MessageSent($message));
        } catch (BroadcastException|\Throwable $e) {
            Log::error('Chat broadcast failed', [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $message;
    }

    public function markRead(ChatConversation $conversation, string $readerType): void
    {
        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_type', '!=', $readerType)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function authorizeCustomer(ChatConversation $conversation, Customer $customer): void
    {
        abort_unless($conversation->customer_id === $customer->id, 403);
    }

    public function authorizeStore(ChatConversation $conversation, User $user): void
    {
        $ownsStore = $conversation->store->user_id === $user->id;

        abort_unless($user->hasRole('Administrator') || $ownsStore, 403);
    }

    protected function baseQuery()
    {
        $lastMessageAt = ChatMessage::select('created_at')
            ->whereColumn('chat_messages.conversation_id', 'chat_conversations.id')
            ->orderByDesc('created_at')
            ->limit(1);

        return ChatConversation::query()
            ->with(['store', 'product', 'customer', 'lastMessage'])
            ->withCount([
                'messages as unread_count' => fn ($query) => $query->whereNull('read_at'),
            ])
            ->orderByDesc($lastMessageAt)
            ->orderByDesc('chat_conversations.id');
    }
}
