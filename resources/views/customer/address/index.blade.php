@extends('customer.layouts.app')
@section('title', 'Alamat Saya')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Alamat Saya</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola alamat pengiriman kamu.</p>
    </div>
    <a href="{{ route('customer.address.create') }}"
       class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
        + Tambah Alamat
    </a>
</div>

@if($addresses->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Belum ada alamat. Tambahkan alamat pengiriman terlebih dahulu.
    </div>
@else
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @foreach($addresses as $address)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $address->recipient_name }}</p>
                            @if($address->is_default)
                                <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">Utama</span>
                            @endif
                            @if($address->label)
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $address->label }}</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $address->phone }}</p>
                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $address->full_address }}</p>
                        @if($address->locationNode)
                            <p class="mt-2 inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                @svg('heroicon-o-map-pin', 'h-3.5 w-3.5') {{ $address->locationNode->name }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                    @if(! $address->is_default)
                        <form method="POST" action="{{ route('customer.address.default', $address->id) }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10">
                                Jadikan Utama
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('customer.address.edit', $address->id) }}"
                       class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('customer.address.destroy', $address->id) }}"
                          onsubmit="return confirm('Hapus alamat ini?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
