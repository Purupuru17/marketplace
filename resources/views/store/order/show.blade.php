@extends('idcore::layouts.backend')
@section('title', $order->order_no)

@section('content')
@php
    $badgeStyles = [
        'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'processing' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
        'shipped' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'completed' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
        'cancelled' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
    ];
    $actionLabels = [
        'processing' => 'Proses Pesanan',
        'shipped' => 'Tandai Dikirim',
        'completed' => 'Tandai Selesai',
        'cancelled' => 'Batalkan Pesanan',
    ];
    $allowed = $transitions[$order->status] ?? [];
    $payment = $order->invoice->payments->first();
    $methodLabel = $payment ? (\App\Services\Customer\PaymentService::METHODS[$payment->payment_method] ?? $payment->payment_method) : '-';
@endphp

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $order->order_no }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Toko'], ['label' => 'Pesanan', 'url' => route('toko.order.index')], ['label' => $order->order_no]]" />
    </div>
    <div class="flex items-center gap-2">
        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $badgeStyles[$order->status] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
            {{ \App\Services\Store\StoreOrderService::STATUS_LABELS[$order->status] ?? ucfirst($order->status) }}
        </span>
    </div>
</div>

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-idcore::card title="Info Pesanan">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Invoice</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->invoice->invoice_no }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Toko</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->store->store_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Dibuat</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->created_at->format('d M Y H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Jarak</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->distance_km_snapshot !== null ? $order->distance_km_snapshot.' km' : '—' }}</dd></div>
            </dl>
        </x-idcore::card>

        <x-idcore::card title="Pembeli & Pengiriman">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Nama</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->customer?->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Telepon</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->customer?->phone ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="shrink-0 text-gray-500 dark:text-gray-400">Alamat</dt><dd class="text-right font-medium text-gray-900 dark:text-white">{{ \Illuminate\Support\Arr::get(json_decode($order->address_snapshot, true) ?? [], 'full_address', '-') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Node Tujuan</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->destination_node_snapshot ?? '—' }}</dd></div>
            </dl>
        </x-idcore::card>

        <x-idcore::card title="Pembayaran">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Metode</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $methodLabel }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Status Bayar</dt><dd class="font-medium text-gray-900 dark:text-white">{{ ucfirst($payment?->status ?? '-') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Invoice</dt><dd class="font-medium text-gray-900 dark:text-white">{{ ucfirst($order->invoice->status) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Total</dt><dd class="font-semibold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</dd></div>
            </dl>
        </x-idcore::card>
    </div>

    <x-idcore::card title="Item Pesanan" :padding="false">
        <x-idcore::table>
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Produk</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Qty</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($order->items as $item)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $item->name_snapshot }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->sku_snapshot }}@if($item->variant_snapshot) · {{ $item->variant_snapshot }} @endif</p>
                        </td>
                        <td class="px-6 py-4 text-center text-gray-700 dark:text-gray-300">{{ $item->qty }}</td>
                        <td class="px-6 py-4 text-right text-gray-700 dark:text-gray-300">Rp {{ number_format((float) $item->final_price_snapshot, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $item->subtotal_snapshot, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <x-idcore::table-empty colspan="4" message="Tidak ada item." />
                @endforelse
            </tbody>
        </x-idcore::table>
        <div class="space-y-1 border-t border-gray-100 px-6 py-4 text-sm dark:border-gray-800">
            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                <span>Subtotal</span>
                <span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                <span>Ongkir</span>
                <span>Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-semibold text-gray-900 dark:text-white">
                <span>Total</span>
                <span>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
            </div>
        </div>
    </x-idcore::card>

    @if($order->statusHistories->isNotEmpty())
        <x-idcore::card title="Riwayat Status">
            <ol class="space-y-4">
                @foreach($order->statusHistories as $history)
                    <li class="flex gap-3">
                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-indigo-500"></span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $history->status_from ? ucfirst($history->status_from).' → ' : '' }}{{ ucfirst($history->status_to) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $history->created_at->format('d M Y H:i') }} · {{ ucfirst($history->changed_by_type) }}
                                @if($history->notes) · {{ $history->notes }} @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-idcore::card>
    @endif

    @can('orders.edit')
        @if($allowed !== [])
            <x-idcore::card title="Perbarui Status">
                @if($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form method="POST" action="{{ route('toko.order.update', $order->id) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-idcore::input name="notes" label="Catatan (opsional)" type="text" placeholder="Contoh: no resi, keterangan pengiriman" />
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($allowed as $target)
                            @if($target === 'cancelled')
                                <button type="submit" name="status" value="cancelled"
                                        x-data
                                        @click.prevent="$confirm({
                                            title: 'Batalkan Pesanan?',
                                            message: 'Stok akan dikembalikan otomatis.',
                                            confirmText: 'Ya, Batalkan',
                                            variant: 'danger'
                                        }).then(ok => { if (ok) $el.closest('form').submit(); })"
                                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">
                                    {{ $actionLabels['cancelled'] }}
                                </button>
                            @else
                                <button type="submit" name="status" value="{{ $target }}"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                    {{ $actionLabels[$target] }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </form>
            </x-idcore::card>
        @endif
    @endcan
</div>
@endsection
