@extends('customer.layouts.app')
@section('title', 'Chat dengan ' . $conversation->store->store_name)

@section('content')
<div class="flex flex-col h-[calc(100vh-56px)]">
    <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
        <a href="{{ route('customer.chat.index') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ $conversation->store->store_name }}</p>
            <p class="text-[11px] text-gray-500 truncate">
                @if($conversation->product)
                    Tanya tentang: {{ $conversation->product->name }}
                @else
                    Pertanyaan umum
                @endif
            </p>
        </div>
        <a href="{{ route('storefront.store', $conversation->store->slug) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-50 text-emerald-700 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </a>
    </header>

    <div class="flex-1 overflow-y-auto bg-gray-50 px-4 py-4 space-y-3"
         id="chat-messages" data-echo-channel="{{ $conversation->id }}">
        @foreach($conversation->messages as $message)
            @if($message->sender_type === 'customer')
                <div class="flex justify-end">
                    <div class="max-w-[80%] rounded-2xl rounded-br-sm bg-emerald-700 px-4 py-2 text-sm text-white">
                        <p>{{ $message->message }}</p>
                        <p class="mt-1 text-right text-[10px] text-emerald-200">{{ $message->created_at->format('H:i') }}</p>
                    </div>
                </div>
            @else
                <div class="flex justify-start">
                    <div class="max-w-[80%] rounded-2xl rounded-bl-sm border border-gray-200 bg-white px-4 py-2 text-sm text-gray-800">
                        <p>{{ $message->message }}</p>
                        <p class="mt-1 text-[10px] text-gray-400">{{ $message->created_at->format('H:i') }}</p>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <form method="POST" action="{{ route('customer.chat.store', $conversation->id) }}" class="sticky bottom-14 z-30 flex gap-2 border-t border-gray-100 bg-white px-4 py-2.5">
        @csrf
        <input type="text" name="message" required maxlength="2000" placeholder="Tulis pesan..."
               class="flex-1 rounded-full border-gray-200 bg-white px-4 py-2 text-sm focus:ring-0 focus:border-emerald-600">
        <button type="submit" class="shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-emerald-700 text-white">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
        </button>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const channelId = document.querySelector('#chat-messages')?.dataset.echoChannel;
    if (! window.Pusher || ! channelId) {
        return;
    }

    const pusher = new Pusher(window.Pusher.config.key, {
        cluster: window.Pusher.config.cluster || null,
        wsHost: window.Pusher.config.wsHost || undefined,
        wsPort: window.Pusher.config.wsPort || undefined,
        wssPort: window.Pusher.config.wssPort || undefined,
        forceTLS: window.Pusher.config.forceTLS ?? true,
        disableStats: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
        },
    });

    pusher.connection.bind('connected', function () {
        document.body.dataset.socketId = pusher.connection.socket_id;
    });

    const channel = pusher.subscribe('private-chat.' + channelId);
    channel.bind('App\\Events\\MessageSent', function (data) {
        appendMessage(data);
    });

    function appendMessage(data) {
        if (data.sender_type === 'customer') {
            return;
        }

        const box = document.getElementById('chat-messages');
        if (! box) {
            return;
        }

        const now = data.created_at ? new Date(data.created_at) : new Date();
        const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-start';

        const bubble = document.createElement('div');
        bubble.className = 'max-w-[80%] rounded-2xl rounded-bl-sm border border-gray-200 bg-white px-4 py-2 text-sm text-gray-800';
        bubble.innerHTML = '<p></p><p class="mt-1 text-[10px] text-gray-400">' + time + '</p>';
        bubble.firstElementChild.textContent = data.message;

        wrapper.appendChild(bubble);
        box.appendChild(wrapper);
        box.scrollTop = box.scrollHeight;
    }

    const box = document.getElementById('chat-messages');
    if (box) {
        box.scrollTop = box.scrollHeight;
    }
})();
</script>
@endpush
@endsection