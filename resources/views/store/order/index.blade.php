@extends('idcore::layouts.backend')
@section('title', 'Pesanan Toko')

@section('content')
@php
    $badgeStyles = [
        'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'processing' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
        'shipped' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'completed' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
        'cancelled' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
    ];
@endphp

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pesanan Toko</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Toko'], ['label' => 'Pesanan']]" />
    </div>
</div>

<x-idcore::card title="Data Pesanan" subtitle="Kelola pesanan masuk dari pelanggan" :padding="false">
    <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>Show</span>
            <x-idcore::select name="per_page" :options="[10 => '10', 25 => '25', 50 => '50']" :selected="request('per_page', 10)" placeholder="" onchange="this.form.submit()" />
            <span>entries</span>
            @if($stores->isNotEmpty())
                <x-idcore::select name="store_id" :options="$stores->pluck('store_name', 'id')->all()" :selected="request('store_id')" placeholder="Semua Toko" onchange="this.form.submit()" />
            @endif
            <x-idcore::select name="status" :options="$statusLabels" :selected="request('status')" placeholder="Semua Status" onchange="this.form.submit()" />
        </div>
        <div class="relative w-full md:max-w-xs">
            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400">@svg('heroicon-o-magnifying-glass', 'h-4 w-4')</span>
            <x-idcore::input name="search" type="search" value="{{ request('search') }}" placeholder="Search..." />
        </div>
    </form>

    <x-idcore::table>
        <thead class="bg-gray-50 dark:bg-gray-800/50">
            <tr>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pesanan</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pembeli</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Item</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pembayaran</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($orders as $order)
                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $orders->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $order->order_no }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->store->store_name }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-gray-700 dark:text-gray-300">{{ $order->customer?->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->customer?->phone ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">{{ $order->items->count() }}</td>
                    <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">
                        Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">
                        {{ \App\Services\Customer\PaymentService::METHODS[$order->invoice->payments->first()?->payment_method] ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeStyles[$order->status] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d M Y H:i') }}</td>
                    <td class="px-6 py-4 text-right">
                        <x-idcore::button variant="outline-primary" size="xs" circle tooltip="Detail" :href="route('toko.order.show', $order->id)">
                            @svg('heroicon-o-eye', 'h-3.5 w-3.5')
                        </x-idcore::button>
                    </td>
                </tr>
            @empty
                <x-idcore::table-empty colspan="9" message="Belum ada pesanan." />
            @endforelse
        </tbody>
    </x-idcore::table>

    <x-idcore::pagination :paginator="$orders" />
</x-idcore::card>
@endsection
