<?php

namespace App\Http\Controllers\Store;

use App\Models\ChatConversation;
use App\Services\Customer\ChatService;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;

class ChatController extends BaseCoreController
{
    public function __construct(protected ChatService $service) {}

    public function index(Request $request)
    {
        $conversations = $this->service->conversationsForStore(
            auth()->user(),
            (int) $request->input('per_page', 15)
        );

        return view('store.chat.index', compact('conversations'));
    }

    public function show(ChatConversation $conversation)
    {
        $this->service->authorizeStore($conversation, auth()->user());
        $this->service->markRead($conversation, 'store');

        return view('store.chat.show', [
            'conversation' => $conversation->load(['customer', 'product', 'messages' => fn ($query) => $query->orderBy('created_at')]),
        ]);
    }

    public function store(Request $request, ChatConversation $conversation)
    {
        $this->service->authorizeStore($conversation, auth()->user());

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->service->send($conversation, 'store', auth()->id(), $data['message']);

        return back();
    }
}
