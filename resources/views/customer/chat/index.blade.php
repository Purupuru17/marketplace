@extends('customer.layouts.app')
@section('title', 'Chat Saya')

@section('content')
<h1 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">Chat Saya</h1>

@if($conversations->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Belum ada percakapan.
        <a href="{{ route('storefront.index') }}" class="mt-4 block font-medium text-indigo-600 dark:text-indigo-400">Mulai Belanja</a>
    </div>
@else
    <div class="space-y-4">
        @foreach($conversations as $conversation)
            <a href="{{ route('customer.chat.show', $conversation->id) }}"
               class="block rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-700">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                            @svg('heroicon-o-building-storefront', 'h-6 w-6')
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="truncate font-semibold text-gray-900 dark:text-white">{{ $conversation->store->store_name }}</p>
                                @if(($conversation->unread_count ?? 0) > 0)
                                    <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1.5 text-xs font-semibold text-white">{{ $conversation->unread_count }}</span>
                                @endif
                            </div>
                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                @if($conversation->product)
                                    {{ $conversation->product->name }} ·
                                @endif
                                {{ $conversation->lastMessage?->message ?? 'Mulai percakapan' }}
                            </p>
                        </div>
                    </div>
                    @if($conversation->lastMessage)
                        <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $conversation->lastMessage->created_at->format('d M H:i') }}</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $conversations->links() }}
    </div>
@endif
@endsection
