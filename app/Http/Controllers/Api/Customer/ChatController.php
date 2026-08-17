<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\Store;
use App\Services\Customer\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(protected ChatService $service) {}

    public function index(Request $request)
    {
        $conversations = $this->service->conversationsForCustomer(
            $request->user('api-customer'),
        );

        return response()->json([
            'data' => [
                'items' => $conversations->map(fn (ChatConversation $conversation) => $this->payload($conversation))->values(),
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'last_page' => $conversations->lastPage(),
                    'total' => $conversations->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, ChatConversation $conversation)
    {
        $customer = $request->user('api-customer');

        $this->service->authorizeCustomer($conversation, $customer);
        $this->service->markRead($conversation, 'customer');

        $conversation->load(['store', 'product', 'messages' => fn ($query) => $query->orderBy('created_at')]);

        return response()->json([
            'data' => $this->payload($conversation, true),
        ]);
    }

    public function store(Request $request, ChatConversation $conversation)
    {
        $customer = $request->user('api-customer');

        $this->service->authorizeCustomer($conversation, $customer);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = $this->service->send($conversation, 'customer', $customer->id, $validated['message']);

        return response()->json([
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender_type' => $message->sender_type,
                'created_at' => $message->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
        ]);

        $store = Store::findOrFail($validated['store_id']);
        abort_unless($store->status === 'active', 404);

        $conversation = $this->service->start(
            $request->user('api-customer'),
            $store,
            $validated['product_id'] ?? null,
        );

        return response()->json([
            'data' => $this->payload($conversation),
        ], 201);
    }

    protected function payload(ChatConversation $conversation, bool $withMessages = false): array
    {
        $data = [
            'id' => $conversation->id,
            'store' => [
                'id' => $conversation->store?->id,
                'name' => $conversation->store?->store_name,
            ],
            'product' => $conversation->product
                ? ['id' => $conversation->product->id, 'name' => $conversation->product->name]
                : null,
            'last_message' => $conversation->lastMessage?->message,
            'updated_at' => $conversation->updated_at?->toIso8601String(),
        ];

        if ($withMessages) {
            $data['messages'] = $conversation->messages->map(fn ($message) => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_id' => $message->sender_id,
                'message' => $message->message,
                'read_at' => $message->read_at?->toIso8601String(),
                'created_at' => $message->created_at?->toIso8601String(),
            ])->values();
        }

        return $data;
    }
}
