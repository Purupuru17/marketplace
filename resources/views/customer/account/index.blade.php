@extends('customer.layouts.app')
@section('title', 'Akun')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <h1 class="font-bold text-[15px] text-gray-900">Akun</h1>
</header>

<main class="px-4">

    <section class="mt-4 bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm shrink-0">
            {{ strtoupper(substr($customer->name, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ $customer->name }}</p>
            <p class="text-[11px] text-gray-500 truncate">{{ $customer->email }}</p>
        </div>
    </section>

    <section class="mt-4 bg-white rounded-xl border border-gray-100 overflow-hidden divide-y divide-gray-50">
        @foreach([
            ['label' => 'Pesanan Saya', 'desc' => 'Status pesanan & riwayat belanja', 'route' => 'customer.order.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['label' => 'Poin Saya', 'desc' => 'Saldo poin & riwayat transaksi poin', 'route' => 'customer.point.index', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.075 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
            ['label' => 'Favorit', 'desc' => 'Produk yang kamu simpan', 'route' => 'customer.favorite.index', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ['label' => 'Alamat Saya', 'desc' => 'Kelola alamat pengiriman', 'route' => 'customer.address.index', 'icon' => 'M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
            ['label' => 'Chat', 'desc' => 'Percakapan dengan toko', 'route' => 'customer.chat.index', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        ] as $item)
            <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-4 py-3.5 active:bg-gray-50">
                <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-900">{{ $item['label'] }}</p>
                    <p class="text-[11px] text-gray-500">{{ $item['desc'] }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endforeach
    </section>

    <form method="POST" action="{{ route('customer.auth.logout') }}" class="mt-4">
        @csrf
        <button type="submit"
                class="w-full text-sm font-semibold text-red-600 bg-white border border-red-200 rounded-lg py-3">Keluar</button>
    </form>

</main>
@endsection