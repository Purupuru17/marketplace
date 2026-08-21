@extends('customer.layouts.app')
@section('title', 'Alamat Tersimpan')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <h1 class="font-bold text-[15px] text-gray-900">Alamat Tersimpan</h1>
</header>

<main class="px-4">
    @if($addresses->isEmpty())
        <div class="flex flex-col items-center justify-center text-center px-8 py-24">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-800">Belum ada alamat tersimpan</p>
            <p class="text-xs text-gray-500 mt-1.5">Tambahkan alamat supaya belanja lebih cepat</p>
            <a href="{{ route('customer.address.create') }}" class="mt-4 text-xs font-semibold text-white bg-emerald-700 rounded-lg px-5 py-2.5 flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Alamat
                </a>
        </div>
    @else
        <div class="space-y-3 mt-3">
            @foreach($addresses as $address)
                <section class="bg-white rounded-xl p-3.5 relative {{ $address->is_default ? 'border-2 border-emerald-600' : 'border border-gray-100' }}">
                    @if($address->is_default)
                        <span class="absolute -top-2.5 left-3.5 bg-emerald-700 text-white text-[10px] font-semibold rounded-full px-2 py-0.5">Alamat Utama</span>
                    @endif
                    <div class="flex items-start gap-3 {{ $address->is_default ? 'mt-1.5' : '' }}">
                        <svg class="w-5 h-5 {{ $address->is_default ? 'text-emerald-700' : 'text-gray-400' }} shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13px] font-bold text-gray-900">{{ $address->label ?: ($address->is_default ? 'Alamat Utama' : 'Alamat') }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $address->recipient_name }} · {{ $address->phone }}</p>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $address->full_address }}</p>
                            @if($address->locationNode)
                                <p class="text-[11px] font-medium text-emerald-700 mt-1">{{ $address->locationNode->name }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-3 pl-8">
                        <div class="flex gap-2">
                            <a href="{{ route('customer.address.edit', $address->id) }}" class="text-[11px] font-semibold text-gray-600 border border-gray-200 rounded-lg px-3 py-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Ubah
                            </a>
                            <form method="POST" action="{{ route('customer.address.destroy', $address->id) }}" id="delAddr{{ $address->id }}">
                                @csrf @method('DELETE')
                                <button type="button" @click="$customerConfirm({ title: 'Hapus Alamat?', message: 'Alamat ini akan dihapus permanen dari daftar alamatmu.', confirmText: 'Ya, Hapus', variant: 'danger' }).then(ok => ok && document.getElementById('delAddr{{ $address->id }}').submit())"
                                        class="text-[11px] font-semibold text-red-600 border border-red-200 rounded-lg px-3 py-1.5 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                        @if(! $address->is_default)
                            <form method="POST" action="{{ route('customer.address.default', $address->id) }}">
                                @csrf
                                <button type="submit" class="text-[11px] font-medium text-emerald-700 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Jadikan Utama
                                </button>
                            </form>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    @if($addresses->hasPages())
        <div class="mt-6">
            {{ $addresses->links() }}
        </div>
    @endif
</main>

<div class="fixed bottom-14 inset-x-0 max-w-[420px] mx-auto bg-white border-t border-gray-100 px-4 py-3 z-30">
    <a href="{{ route('customer.address.create') }}"
       class="w-full flex items-center justify-center gap-2 text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah Alamat Baru
    </a>
</div>
@endsection