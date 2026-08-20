@extends('customer.layouts.app')
@section('title', 'Alamat Saya')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center justify-between">
    <h1 class="font-bold text-[15px] text-gray-900">Alamat Saya</h1>
    <a href="{{ route('customer.address.create') }}" class="text-xs font-semibold text-white bg-emerald-700 rounded-full px-3.5 py-1.5">+ Tambah</a>
</header>

<main class="px-4">
    @if($addresses->isEmpty())
        <div class="flex flex-col items-center justify-center text-center px-8 py-24">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-800">Belum ada alamat</p>
            <p class="text-xs text-gray-500 mt-1.5">Tambahkan alamat pengiriman terlebih dahulu</p>
            <a href="{{ route('customer.address.create') }}" class="mt-4 text-xs font-semibold text-white bg-emerald-700 rounded-lg px-5 py-2.5">Tambah Alamat</a>
        </div>
    @else
        <div class="space-y-3 mt-4">
            @foreach($addresses as $address)
                <div class="bg-white rounded-xl border border-gray-100 p-3.5">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <p class="text-sm font-semibold text-gray-900">{{ $address->recipient_name }}</p>
                        @if($address->is_default)
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Utama</span>
                        @endif
                        @if($address->label)
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600">{{ $address->label }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $address->phone }}</p>
                    <p class="text-[13px] text-gray-700 mt-1.5">{{ $address->full_address }}</p>
                    @if($address->locationNode)
                        <p class="text-[11px] text-emerald-700 font-medium mt-1">{{ $address->locationNode->name }}</p>
                    @endif
                    <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center gap-3">
                        @if(! $address->is_default)
                            <form method="POST" action="{{ route('customer.address.default', $address->id) }}">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-emerald-700">Jadikan Utama</button>
                            </form>
                        @endif
                        <a href="{{ route('customer.address.edit', $address->id) }}" class="text-xs font-medium text-gray-600">Edit</a>
                        <form method="POST" action="{{ route('customer.address.destroy', $address->id) }}" onsubmit="return confirm('Hapus alamat ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-600">Hapus</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>
@endsection