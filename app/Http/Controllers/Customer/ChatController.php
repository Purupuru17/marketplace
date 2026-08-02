<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\Store;
use App\Services\Customer\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(protected ChatService $service) {}

    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $conversations = $this->service->conversationsForCustomer($customer);

        return view('customer.chat.index', compact('conversations'));
    }

    public function show(ChatConversation $conversation)
    {
        $customer = Auth::guard('customer')->user();

        $this->service->authorizeCustomer($conversation, $customer);
        $this->service->markRead($conversation, 'customer');

        return view('customer.chat.show', [
            'conversation' => $conversation->load(['store', 'product', 'messages' => fn ($query) => $query->orderBy('created_at')]),
        ]);
    }

    public function store(Request $request, ChatConversation $conversation)
    {
        $customer = Auth::guard('customer')->user();

        $this->service->authorizeCustomer($conversation, $customer);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->service->send($conversation, 'customer', $customer->id, $data['message']);

        return back();
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
        ]);

        $store = Store::findOrFail($data['store_id']);

        abort_unless($store->status === 'active', 404);

        $conversation = $this->service->start(
            Auth::guard('customer')->user(),
            $store,
            $data['product_id'] ?? null
        );

        return redirect()->route('customer.chat.show', $conversation->id);
    }
}
