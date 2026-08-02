@extends('idcore::layouts.backend')
@section('title', 'Chat')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Chat</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Chat']]" />
    </div>
</div>

<x-idcore::card title="Percakapan" subtitle="Pertanyaan pelanggan tentang produk" :padding="false">
    <x-idcore::table>
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pelanggan</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pesan Terakhir</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($conversations as $conversation)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $conversations->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $conversation->customer->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $conversation->customer->email }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($conversation->product)
                            <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ $conversation->product->name }}</p>
                        @endif
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $conversation->lastMessage?->message ?? 'Mulai percakapan' }}</p>
                        @if($conversation->lastMessage)
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $conversation->lastMessage->created_at->format('d M H:i') }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if(($conversation->unread_count ?? 0) > 0)
                            <x-idcore::badge variant="red">{{ $conversation->unread_count }} belum dibaca</x-idcore::badge>
                        @else
                            <x-idcore::badge variant="green">Dibaca</x-idcore::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <x-idcore::button variant="outline-primary" size="xs" circle tooltip="Buka"
                            :href="route('toko.chat.show', $conversation->id)">
                            @svg('heroicon-o-chat-bubble-left-right', 'h-3.5 w-3.5')
                        </x-idcore::button>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="5" message="Belum ada percakapan." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$conversations" />
</x-idcore::card>
@endsection
