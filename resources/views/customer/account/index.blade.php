@extends('customer.layouts.app')
@section('title', 'Akun Saya')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <h1 class="font-bold text-[15px] text-gray-900">Akun Saya</h1>
</header>

<main class="px-4 pb-6">

    <section class="mt-4 bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-3.5">
        <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-lg shrink-0">
            {{ strtoupper(substr($customer->name, 0, 1)) }}
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-[15px] font-bold text-gray-900 truncate">{{ $customer->name }}</p>
            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $customer->phone ?: $customer->email }}</p>
        </div>
        <a href="{{ route('customer.account.edit') }}"
                class="w-8 h-8 flex items-center justify-center text-gray-400 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </a>
    </section>

    <section class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[13px] font-semibold text-gray-800">Pesanan Saya</p>
            <a href="{{ route('customer.order.index') }}" class="text-[11px] font-medium text-emerald-700 flex items-center gap-0.5">
                Lihat Semua
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-4 gap-1">
            @php
                $orderStatusTabs = [
                    'pending' => ['label' => 'Bayar', 'icon' => 'M3 6l3 1m0 0l2.5 10.5A2 2 0 0010.44 19h6.12a2 2 0 001.94-1.5L21 8H6'],
                    'processing' => ['label' => 'Diproses', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    'shipped' => ['label' => 'Dikirim', 'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 17H7V4H3m4 13V7h11l3 6h-3m-4 4h4m-4-4v4'],
                    'completed' => ['label' => 'Selesai', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
            @endphp
            @foreach($orderStatusTabs as $status => $tab)
                <a href="{{ route('customer.order.index', ['status' => $status]) }}" class="flex flex-col items-center gap-1.5">
                    <div class="relative w-9 h-9 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/></svg>
                        @if($orderCounts[$status] > 0)
                            <span class="absolute -top-0.5 -right-0.5 bg-red-600 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $orderCounts[$status] }}</span>
                        @endif
                    </div>
                    <span class="text-[10px] text-gray-600">{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mt-3 bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
        <a href="{{ route('customer.address.index') }}" class="w-full flex items-center gap-3 px-3.5 py-3">
            <svg class="w-[18px] h-[18px] text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="flex-1 text-left text-[13px] text-gray-800">Alamat Tersimpan</span>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('customer.favorite.index') }}" class="w-full flex items-center gap-3 px-3.5 py-3">
            <svg class="w-[18px] h-[18px] text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <span class="flex-1 text-left text-[13px] text-gray-800">Produk Favorit</span>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <button type="button" @click="$store.toast.warning('Fitur riwayat belum tersedia')" class="w-full flex items-center gap-3 px-3.5 py-3">
            <svg class="w-[18px] h-[18px] text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="flex-1 text-left text-[13px] text-gray-800">Riwayat Baru Dilihat</span>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
    </section>

    <section class="mt-3 bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
        <button type="button" @click="$store.toast.warning('Fitur notifikasi belum tersedia')" class="w-full flex items-center gap-3 px-3.5 py-3">
            <svg class="w-[18px] h-[18px] text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span class="flex-1 text-left text-[13px] text-gray-800">Notifikasi</span>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <button type="button" @click="$store.toast.warning('Fitur keamanan akun belum tersedia')" class="w-full flex items-center gap-3 px-3.5 py-3">
            <svg class="w-[18px] h-[18px] text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span class="flex-1 text-left text-[13px] text-gray-800">Keamanan Akun</span>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <button type="button" @click="$store.toast.warning('Fitur pusat bantuan belum tersedia')" class="w-full flex items-center gap-3 px-3.5 py-3">
            <svg class="w-[18px] h-[18px] text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="flex-1 text-left text-[13px] text-gray-800">Pusat Bantuan</span>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <div class="w-full flex items-center gap-3 px-3.5 py-3">
            <svg class="w-[18px] h-[18px] text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="flex-1 text-left text-[13px] text-gray-800">Tentang Aplikasi</span>
            <span class="text-[11px] text-gray-400">v1.0.0</span>
        </div>
    </section>

    <section class="mt-3 bg-white rounded-xl border border-gray-100 divide-y divide-gray-50">
        <a href="{{ route('customer.point.index') }}" class="w-full flex items-center gap-3 px-3.5 py-3">
            <svg class="w-[18px] h-[18px] text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.075 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            <span class="flex-1 text-left text-[13px] text-gray-800">Poin Saya</span>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('customer.chat.index') }}" class="w-full flex items-center gap-3 px-3.5 py-3">
            <svg class="w-[18px] h-[18px] text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="flex-1 text-left text-[13px] text-gray-800">Chat</span>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    </section>

    <form method="POST" action="{{ route('customer.auth.logout') }}" id="logoutForm">
        @csrf
        <button type="button" @click="$customerConfirm({ title: 'Keluar Akun?', message: 'Kamu akan keluar dari akun ini. Yakin ingin melanjutkan?', confirmText: 'Ya, Keluar', variant: 'danger' }).then(ok => ok && document.getElementById('logoutForm').submit())"
                class="w-full text-xs font-semibold text-red-600 border border-red-200 rounded-lg py-3 flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Keluar Akun
        </button>
    </form>

</main>
@endsection