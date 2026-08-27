@extends('idcore::layouts.backend')
@section('title', $order->order_no)

@section('content')
@php
    $badgeStyles = [
        'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'processing' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
        'ready' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'completed' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
        'cancelled' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
    ];

    $banner = [
        'pending' => ['Menunggu Diproses', 'amber', 'clock', 'Pesanan masuk dan menunggu untuk diproses.'],
        'processing' => ['Sedang Disiapkan', 'blue', 'package', 'Pesanan sedang disiapkan toko.'],
        'ready' => ['Siap Diserahkan', 'indigo', $order->fulfillment_type === 'pickup' ? 'shopping-bag' : 'truck', 'Pesanan siap '.($order->fulfillment_type === 'pickup' ? 'diambil pembeli' : 'diantar / diserahkan').'. Menunggu konfirmasi diterima oleh pembeli.'],
        'completed' => ['Selesai', 'emerald', 'check', 'Pesanan telah diselesaikan dan dikonfirmasi pembeli.'],
        'cancelled' => ['Dibatalkan', 'red', 'x', 'Pesanan dibatalkan. Stok telah dikembalikan otomatis.'],
    ];
    [$bannerTitle, $bannerTone, $bannerIcon, $bannerNote] = $banner[$order->status] ?? ['Pesanan', 'gray', 'info', ''];

    $actionLabels = [
        'processing' => 'Proses Pesanan',
        'ready' => 'Tandai Siap Serahterima',
        'cancelled' => 'Batalkan Pesanan',
    ];
    $allowed = $transitions[$order->status] ?? [];
    $payment = $order->payments->first();
    $payLabels = [
        'cash' => 'Cash',
        'bank_transfer' => 'Transfer',
    ];
    $methodLabel = $payment ? ($payLabels[$payment->payment_method] ?? $payment->payment_method) : '-';
    $isPickup = $order->fulfillment_type === 'pickup';
    $paid = $payment?->status === 'paid';

    $hist = $order->statusHistories->pluck('created_at', 'status_to');
    $steps = [
        ['key' => 'created', 'label' => 'Pesanan Dibuat', 'done' => true, 'active' => false, 'time' => $order->created_at],
        ['key' => 'payment', 'label' => 'Pembayaran', 'done' => in_array($order->status, ['processing', 'ready', 'completed']), 'active' => $order->status === 'pending', 'time' => $hist['processing'] ?? ($hist['ready'] ?? null), 'note' => 'Menunggu pembayaran / konfirmasi'],
        ['key' => 'processing', 'label' => 'Disiapkan', 'done' => in_array($order->status, ['ready', 'completed']), 'active' => $order->status === 'processing', 'time' => $hist['ready'] ?? $hist['completed'] ?? null, 'note' => 'Sedang disiapkan'],
        ['key' => 'ready', 'label' => $isPickup ? 'Siap Diambil' : 'Siap Diserahkan', 'done' => $order->status === 'completed', 'active' => $order->status === 'ready', 'time' => $hist['completed'] ?? null, 'note' => 'Menunggu konfirmasi pembeli'],
        ['key' => 'completed', 'label' => 'Selesai', 'done' => $order->status === 'completed', 'active' => $order->status === 'completed', 'time' => $hist['completed'] ?? null, 'note' => ''],
    ];

    $addrRaw = $order->address_snapshot;
    $decoded = is_string($addrRaw) ? json_decode($addrRaw, true) : null;
    $addressLines = is_array($decoded)
        ? array_values(array_filter([$decoded['full_address'] ?? null, trim(($decoded['recipient_name'] ?? '') . ' · ' . ($decoded['phone'] ?? ''), ' ·')]))
        : ($addrRaw ? [$addrRaw] : []);
    $stepsCount = count($steps);
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $order->order_no }}</h1>
            <x-idcore::breadcrumb :items="$breadcrumb" />
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $badgeStyles[$order->status] ?? 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                {{ \App\Services\Store\StoreOrderService::STATUS_LABELS[$order->status] ?? ucfirst($order->status) }}
            </span>
        </div>
    </div>

    {{-- Banner status --}}
    <section class="rounded-2xl border p-4 flex items-start gap-3
        @if($bannerTone === 'amber') bg-amber-50 border-amber-100 dark:bg-amber-500/10 dark:border-amber-500/20
        @elseif($bannerTone === 'blue') bg-blue-50 border-blue-100 dark:bg-blue-500/10 dark:border-blue-500/20
        @elseif($bannerTone === 'indigo') bg-indigo-50 border-indigo-100 dark:bg-indigo-500/10 dark:border-indigo-500/20
        @elseif($bannerTone === 'emerald') bg-emerald-50 border-emerald-100 dark:bg-emerald-500/10 dark:border-emerald-500/20
        @elseif($bannerTone === 'red') bg-red-50 border-red-100 dark:bg-red-500/10 dark:border-red-500/20
        @else bg-gray-50 border-gray-100 dark:bg-gray-800 dark:border-gray-700 @endif">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
            @if($bannerTone === 'amber') bg-amber-100 text-amber-600
            @elseif($bannerTone === 'blue') bg-blue-100 text-blue-600
            @elseif($bannerTone === 'indigo') bg-indigo-100 text-indigo-600
            @elseif($bannerTone === 'emerald') bg-emerald-100 text-emerald-700
            @elseif($bannerTone === 'red') bg-red-100 text-red-600
            @else bg-gray-100 text-gray-600 @endif">
            @if($bannerIcon === 'clock')
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($bannerIcon === 'package')
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            @elseif($bannerIcon === 'truck')
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 19a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM2 5h12v10H2zM14 9h4l3 3v3h-7z"/></svg>
            @elseif($bannerIcon === 'check')
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            @elseif($bannerIcon === 'x')
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            @endif
        </div>
        <div class="min-w-0">
            <p class="text-sm font-bold
                @if($bannerTone === 'amber') text-amber-800
                @elseif($bannerTone === 'blue') text-blue-800
                @elseif($bannerTone === 'indigo') text-indigo-800
                @elseif($bannerTone === 'emerald') text-emerald-800
                @elseif($bannerTone === 'red') text-red-700
                @else text-gray-800 @endif">{{ $bannerTitle }}</p>
            @if($bannerNote)
                <p class="text-xs mt-0.5
                    @if($bannerTone === 'amber') text-amber-700
                    @elseif($bannerTone === 'blue') text-blue-700
                    @elseif($bannerTone === 'indigo') text-indigo-700
                    @elseif($bannerTone === 'emerald') text-emerald-700
                    @elseif($bannerTone === 'red') text-red-600
                    @else text-gray-500 @endif">{{ $bannerNote }}</p>
            @endif
        </div>
    </section>

    {{-- Timeline langkah --}}
    <x-idcore::card title="Perkembangan Pesanan">
        <ol class="space-y-0">
            @foreach($steps as $idx => $step)
                @php $isLast = $idx === $stepsCount - 1; @endphp
                <li class="flex items-start">
                    <div class="flex flex-col items-center">
                        @if($step['done'])
                            <div class="w-5 h-5 rounded-full bg-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @elseif($step['active'])
                            <div class="w-5 h-5 rounded-full bg-indigo-500 ring-4 ring-indigo-100 dark:ring-indigo-500/20 flex items-center justify-center shrink-0">
                                <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                            </div>
                        @else
                            <div class="w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-700 shrink-0"></div>
                        @endif
                        @if(! $isLast)
                            <div class="w-0.5 flex-1 {{ $step['done'] ? 'bg-emerald-600' : 'bg-gray-200 dark:bg-gray-700' }} min-h-7"></div>
                        @endif
                    </div>
                    <div class="ml-3 {{ $isLast ? '' : 'pb-4' }}">
                        <p class="text-sm {{ $step['active'] || $step['done'] ? 'font-semibold text-gray-900 dark:text-white' : 'font-medium text-gray-400 dark:text-gray-500' }}">{{ $step['label'] }}</p>
                        @if(isset($step['note']) && $step['note'] && $step['active'])
                            <p class="text-xs text-gray-400 mt-0.5">{{ $step['note'] }}</p>
                        @elseif($step['time'])
                            <p class="text-xs text-gray-400 mt-0.5">{{ $step['time']->format('d M Y H:i') }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </x-idcore::card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-idcore::card title="Info Pesanan">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Invoice</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->invoice->invoice_no }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Toko</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->store->store_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Pengiriman</dt>
                    <dd><span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $isPickup ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' : 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400' }}">{{ $isPickup ? 'Ambil Sendiri' : 'Antar / Kirim' }}</span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Asal</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->origin_node_snapshot ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Dibuat</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->created_at->format('d M Y H:i') }}</dd></div>
            </dl>
        </x-idcore::card>

        <x-idcore::card title="Pembeli & Pengiriman">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Nama</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->customer?->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Telepon</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->customer?->phone ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="shrink-0 text-gray-500 dark:text-gray-400">Alamat</dt><dd class="text-right font-medium text-gray-900 dark:text-white">{{ $addressLines[0] ?? '—' }}</dd></div>
                @if(! $isPickup && $order->distance_km_snapshot !== null)
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Jarak</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->distance_km_snapshot.' km' }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Node Tujuan</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->destination_node_snapshot ?? '—' }}</dd></div>
                @if($isPickup && $order->store->locationNode)
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Lokasi Ambil</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $order->store->locationNode->name }}</dd></div>
                @endif
            </dl>
        </x-idcore::card>

        <x-idcore::card title="Pembayaran">
            <dl class="space-y-2 text-sm">
                <div class="flex items-center justify-between"><dt class="text-gray-500 dark:text-gray-400">Metode</dt>
                    <dd><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $methodLabel }}</span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Status Bayar</dt>
                    <dd><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $paid ? 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">{{ $paid ? 'LUNAS' : 'Menunggu' }}</span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Invoice</dt><dd class="font-medium text-gray-900 dark:text-white">{{ ucfirst($order->invoice->status) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Total</dt><dd class="font-semibold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</dd></div>
                @if(isset($payment->bank_snapshot) && $payment->bank_snapshot && $payment->payment_method === 'bank_transfer')
                    <div class="border-t border-gray-100 pt-2 dark:border-gray-800">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Rekening Tujuan (saat checkout)</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $payment->bank_snapshot['bank_name'] }} · {{ $payment->bank_snapshot['account_number'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">a.n. {{ $payment->bank_snapshot['account_name'] }}</p>
                    </div>
                @endif
                @if($payment?->payment_proof_path)
                    <div class="border-t border-gray-100 pt-2 dark:border-gray-800">
                        <p class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Bukti Transfer</p>
                        <img src="{{ asset('storage/'.$payment->payment_proof_path) }}" alt="Bukti transfer" class="w-32 rounded-lg border border-gray-200 object-cover dark:border-gray-700">
                    </div>
                @endif
            </dl>
            @if($payment && $payment->status === 'pending')
                <form method="POST" action="{{ route('toko.order.paid', $order->id) }}" class="mt-4">
                    @csrf
                    <x-idcore::button variant="success" type="submit" size="sm">
                        @svg('heroicon-o-check-badge', 'h-4 w-4') Tandai Lunas
                    </x-idcore::button>
                </form>
            @endif
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
                    @php
                        $img = $item->product?->images->first()?->path ? asset('storage/' . $item->product->images->first()->path) : null;
                    @endphp
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($img)
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0 overflow-hidden dark:bg-gray-800">
                                        <img src="{{ $img }}" alt="" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0 dark:bg-gray-800"></div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $item->name_snapshot }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->sku_snapshot }}@if($item->variant_snapshot) · {{ $item->variant_snapshot }} @endif</p>
                                </div>
                            </div>
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
        <div class="space-y-1.5 border-t border-gray-100 px-6 py-4 text-sm dark:border-gray-800">
            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                <span>Subtotal Produk</span>
                <span>Rp {{ number_format((float) ($order->subtotal + (float) $order->discount), 0, ',', '.') }}</span>
            </div>
            @if((float) $order->discount > 0)
                <div class="flex justify-between text-gray-600 dark:text-gray-300">
                    <span>Diskon Promo</span>
                    <span class="text-red-500">-Rp {{ number_format((float) $order->discount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                <span>Ongkir</span>
                @if((float) $order->shipping_cost === 0.0)
                    <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $isPickup ? 'Gratis (Ambil Sendiri)' : 'Gratis' }}</span>
                @else
                    <span>Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</span>
                @endif
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
                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $history->status_to === 'cancelled' ? 'bg-red-500' : 'bg-indigo-500' }}"></span>
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
