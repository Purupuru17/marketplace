@extends('customer.layouts.app')
@section('title', 'Chat Saya')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <h1 class="font-bold text-[15px] text-gray-900">Chat Saya</h1>
</header>

<main class="px-4">
    @if($conversations->isEmpty())
        <div class="flex flex-col items-center justify-center text-center px-8 py-24">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-800">Belum ada percakapan</p>
            <p class="text-xs text-gray-500 mt-1.5">Mulai chat dengan toko dari halaman produk atau toko</p>
            <a href="{{ route('storefront.index') }}" class="mt-4 text-xs font-semibold text-white bg-emerald-700 rounded-lg px-5 py-2.5">Mulai Belanja</a>
        </div>
    @else
        <div class="space-y-3 mt-4">
            @foreach($conversations as $conversation)
                <a href="{{ route('customer.chat.show', $conversation->id) }}" class="block bg-white rounded-xl border border-gray-100 p-3.5 flex items-center gap-3">
                    <div class="w-11 h-11 shrink-0 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr($conversation->store->store_name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-[13px] font-semibold text-gray-900">{{ $conversation->store->store_name }}</p>
                            @if($conversation->lastMessage)
                                <span class="shrink-0 text-[10px] text-gray-400">{{ $conversation->lastMessage->created_at->format('d M H:i') }}</span>
                            @endif
                        </div>
                        <p class="truncate text-[11px] text-gray-500 mt-0.5">
                            @if($conversation->product)
                                {{ $conversation->product->name }} ·
                            @endif
                            {{ $conversation->lastMessage?->message ?? 'Mulai percakapan' }}
                        </p>
                    </div>
                    @if(($conversation->unread_count ?? 0) > 0)
                        <span class="shrink-0 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-emerald-600 px-1.5 text-[10px] font-semibold text-white">{{ $conversation->unread_count }}</span>
                    @endif
                </a>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $conversations->links() }}
        </div>
    @endif
</main>
@endsection