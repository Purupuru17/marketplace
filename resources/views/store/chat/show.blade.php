@extends('idcore::layouts.backend')
@section('title', 'Chat dengan ' . $conversation->customer->name)

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Chat</h1>
        <x-idcore::breadcrumb :items="$breadcrumb" />
    </div>
    <a href="{{ route($module.'.index') }}"
       class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
        @svg('heroicon-o-arrow-left', 'h-4 w-4') Semua percakapan
    </a>
</div>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
            @svg('heroicon-o-user-circle', 'h-5 w-5')
        </div>
        <div>
            <p class="font-semibold text-gray-900 dark:text-white">{{ $conversation->customer->name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ $conversation->customer->email }}
                @if($conversation->product)
                    · Tanya tentang: {{ $conversation->product->name }}
                @endif
            </p>
        </div>
    </div>

    <div class="flex max-h-[480px] min-h-[320px] flex-col gap-3 overflow-y-auto bg-gray-50 p-6 dark:bg-gray-950/50"
         id="chat-messages" data-echo-channel="{{ $conversation->id }}">
        @foreach($conversation->messages as $message)
            @if($message->sender_type === 'store')
                <div class="flex justify-end">
                    <div class="max-w-[80%] rounded-2xl rounded-br-sm bg-indigo-600 px-4 py-2 text-sm text-white">
                        <p>{{ $message->message }}</p>
                        <p class="mt-1 text-right text-[10px] text-indigo-200">{{ $message->created_at->format('H:i') }}</p>
                    </div>
                </div>
            @else
                <div class="flex justify-start">
                    <div class="max-w-[80%] rounded-2xl rounded-bl-sm border border-gray-200 bg-white px-4 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <p>{{ $message->message }}</p>
                        <p class="mt-1 text-[10px] text-gray-400">{{ $message->created_at->format('H:i') }}</p>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <form method="POST" action="{{ route('toko.chat.store', $conversation->id) }}" class="flex gap-2 border-t border-gray-100 px-4 py-3 dark:border-gray-800">
        @csrf
        <input type="text" name="message" required maxlength="2000" placeholder="Tulis balasan..."
               class="flex-1 rounded-lg border-gray-300 bg-white px-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            @svg('heroicon-o-paper-airplane', 'h-4 w-4') Kirim
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
        if (data.sender_type === 'store') {
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
        bubble.className = 'max-w-[80%] rounded-2xl rounded-bl-sm border border-gray-200 bg-white px-4 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
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
