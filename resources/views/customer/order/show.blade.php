@extends('customer.layouts.app')
@section('title', 'Detail Pesanan')

@php
    $methods = \App\Services\Customer\PaymentService::METHODS;
    $payment = $order->payments->first();
    $siblings = $order->invoice->orders->where('id', '!=', $order->id)->values();
    $isPickup = $order->fulfillment_type === 'pickup';

    $initials = collect(explode(' ', trim($order->store->store_name)))->filter()->take(2)
        ->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))->implode('');

    $statusBanner = [
        'pending' => ['Menunggu Pembayaran', 'amber', 'clock'],
        'processing' => ['Diproses Toko', 'blue', 'package'],
        'shipped' => ['Pesanan Dikirim', 'indigo', 'truck'],
        'completed' => ['Pesanan Selesai', 'emerald', 'check'],
        'cancelled' => ['Pesanan Dibatalkan', 'red', 'x'],
    ];
    [$bannerTitle, $bannerTone, $bannerIcon] = $statusBanner[$order->status] ?? ['Pesanan', 'gray', 'info'];

    $hist = $order->statusHistories->pluck('created_at', 'status_to');
    $paidAt = $payment?->paid_at;

    $steps = [
        ['key' => 'created', 'label' => 'Pesanan Dibuat', 'done' => true, 'active' => false, 'time' => $order->created_at, 'note' => null],
        ['key' => 'payment', 'label' => 'Menunggu Pembayaran', 'done' => in_array($order->status, ['processing', 'shipped', 'completed']), 'active' => $order->status === 'pending', 'time' => $paidAt ?? ($hist['processing'] ?? null), 'note' => 'Sedang berlangsung'],
        ['key' => 'processing', 'label' => 'Diproses Toko', 'done' => in_array($order->status, ['shipped', 'completed']), 'active' => $order->status === 'processing', 'time' => $hist['processing'] ?? ($hist['shipped'] ?? $hist['completed'] ?? null), 'note' => 'Sedang berlangsung'],
        ['key' => 'shipping', 'label' => 'Dikirim', 'done' => $order->status === 'completed', 'active' => $order->status === 'shipped', 'time' => $hist['shipped'] ?? $hist['completed'] ?? null, 'note' => 'Sedang berlangsung', 'hidden' => $isPickup],
        ['key' => 'completed', 'label' => 'Selesai', 'done' => $order->status === 'completed', 'active' => $order->status === 'completed', 'time' => $hist['completed'] ?? null, 'note' => null],
    ];
    $steps = collect($steps)->filter(fn ($s) => empty($s['hidden']))->values();

    $backHref = url()->previous() && url()->previous() !== url()->current()
        ? url()->previous()
        : route('customer.order.index');

    $addrRaw = $order->address_snapshot;
    $decoded = is_string($addrRaw) ? json_decode($addrRaw, true) : null;
    $addressLines = is_array($decoded)
        ? array_values(array_filter([$decoded['full_address'] ?? null, trim(($decoded['recipient_name'] ?? '') . ' · ' . ($decoded['phone'] ?? ''), ' ·')]))
        : ($addrRaw ? [$addrRaw] : []);
@endphp

@section('content')
<div x-data="{ siblingSheet: false }" @keydown.escape.window="siblingSheet = false">

    <header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
        <a href="{{ $backHref }}" class="w-8 h-8 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="min-w-0">
            <h1 class="font-bold text-[15px] text-gray-900">Detail Pesanan</h1>
            <p class="text-[11px] text-gray-500 truncate">{{ $order->order_no }}</p>
        </div>
    </header>

    <main class="px-4 pb-56">
        @if($siblings->isNotEmpty())
            <button type="button" @click="siblingSheet = true"
                    class="mt-3 w-full flex items-center justify-between bg-white border border-gray-100 rounded-lg px-3.5 py-2.5">
                <span class="text-[11px] text-gray-500">Invoice <span class="font-semibold text-gray-700">{{ $order->invoice->invoice_no }}</span> · Lihat {{ $siblings->count() }} pesanan lain</span>
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        @endif

        <section class="mt-3 rounded-xl border p-3.5 flex items-start gap-3
            @if($bannerTone === 'amber') bg-amber-50 border-amber-100
            @elseif($bannerTone === 'blue') bg-blue-50 border-blue-100
            @elseif($bannerTone === 'indigo') bg-indigo-50 border-indigo-100
            @elseif($bannerTone === 'emerald') bg-emerald-50 border-emerald-100
            @else bg-red-50 border-red-100 @endif">
            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                @if($bannerTone === 'amber') bg-amber-100 text-amber-600
                @elseif($bannerTone === 'blue') bg-blue-100 text-blue-600
                @elseif($bannerTone === 'indigo') bg-indigo-100 text-indigo-600
                @elseif($bannerTone === 'emerald') bg-emerald-100 text-emerald-700
                @else bg-red-100 text-red-600 @endif">
                @if($bannerIcon === 'clock')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif($bannerIcon === 'package')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                @elseif($bannerIcon === 'truck')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 19a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM2 5h12v10H2zM14 9h4l3 3v3h-7z"/></svg>
                @elseif($bannerIcon === 'check')
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                @else
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-sm font-bold
                    @if($bannerTone === 'amber') text-amber-800
                    @elseif($bannerTone === 'blue') text-blue-800
                    @elseif($bannerTone === 'indigo') text-indigo-800
                    @elseif($bannerTone === 'emerald') text-emerald-800
                    @else text-red-700 @endif">{{ $bannerTitle }}</p>
                @if($order->status === 'pending' && $payment?->expired_at)
                    <p class="text-[11px] mt-0.5
                        @if($bannerTone === 'amber') text-amber-700 @endif">Selesaikan pembayaran sebelum <span class="font-semibold">{{ $payment->expired_at->format('d M Y, H.i') }}</span> agar pesanan tidak dibatalkan otomatis.</p>
                @elseif($order->status === 'completed')
                    <p class="text-[11px] mt-0.5
                        @if($bannerTone === 'emerald') text-emerald-700 @endif">Diselesaikan pada {{ $order->updated_at->format('d M Y, H.i') }}. Terima kasih sudah berbelanja!</p>
                @endif
            </div>
        </section>

        <section class="mt-4 bg-white rounded-xl border border-gray-100 p-4">
            @foreach($steps as $idx => $step)
                @php $isLast = $idx === $steps->count() - 1; @endphp
                <div class="flex items-start">
                    <div class="flex flex-col items-center">
                        @if($step['done'])
                            <div class="w-5 h-5 rounded-full bg-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @elseif($step['active'])
                            <div class="w-5 h-5 rounded-full {{ $bannerTone === 'amber' ? 'bg-amber-500 ring-4 ring-amber-100' : ($bannerTone === 'red' ? 'bg-red-400 ring-4 ring-red-100' : 'bg-blue-500 ring-4 ring-blue-100') }} flex items-center justify-center shrink-0">
                                <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                            </div>
                        @else
                            <div class="w-5 h-5 rounded-full bg-gray-200 shrink-0"></div>
                        @endif
                        @if(! $isLast)
                            <div class="w-0.5 flex-1 {{ $step['done'] ? 'bg-emerald-700' : 'bg-gray-200' }} min-h-[28px]"></div>
                        @endif
                    </div>
                    <div class="ml-3 {{ $isLast ? '' : 'pb-4' }}">
                        <p class="text-xs {{ $step['active'] || $step['done'] ? 'font-semibold text-gray-900' : 'font-medium text-gray-400' }}">{{ $step['label'] }}</p>
                        @if($step['active'] && $step['note'])
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $step['note'] }}</p>
                        @elseif($step['time'])
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $step['time']->format('d M Y, H.i') }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>

        @if($payment)
            <section class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5">
                <p class="text-[13px] font-semibold text-gray-800 mb-2">Pembayaran — {{ $methods[$payment->payment_method] ?? $payment->payment_method }}</p>

                @if($payment->payment_method === 'bank_transfer')
                    @if($payment->bank_snapshot)
                        <div class="bg-gray-50 rounded-lg p-3 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-500">Bank</span>
                                <span class="text-xs font-semibold text-gray-900">{{ $payment->bank_snapshot['bank_name'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-500">No. Rekening</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-semibold text-gray-900 font-mono">{{ $payment->bank_snapshot['account_number'] }}</span>
                                    <button type="button"
                                            @click="navigator.clipboard.writeText(@js($payment->bank_snapshot['account_number'])); $store.toast.success('No. rekening disalin')">
                                        <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="11" height="11" rx="1.5"/><path d="M5 15V5a2 2 0 012-2h10"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-500">Atas Nama</span>
                                <span class="text-xs font-semibold text-gray-900">{{ $payment->bank_snapshot['account_name'] }}</span>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-[11px] text-amber-800">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            <p class="leading-relaxed">Toko belum melengkapi data rekening. Hubungi toko untuk info pembayaran.</p>
                        </div>
                    @endif

                    @if($payment->status !== 'paid')
                        @if($payment->payment_proof_path)
                            <div class="mt-2.5 flex items-center gap-1 text-[11px] font-medium text-green-600">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Bukti sudah dikirim. Menunggu konfirmasi toko.
                            </div>
                            <button type="button"
                                    @click="$customerViewer({ src: @js(asset('storage/' . $payment->payment_proof_path)), alt: 'Bukti transfer {{ $order->order_no }}', status: 'Menunggu konfirmasi toko', downloadUrl: @js(asset('storage/' . $payment->payment_proof_path)) })"
                                    class="mt-2.5 block w-full rounded-xl overflow-hidden border border-gray-100">
                                <img src="{{ asset('storage/' . $payment->payment_proof_path) }}" alt="Bukti transfer"
                                     class="w-full max-h-52 object-cover">
                                <span class="flex items-center justify-center gap-1 bg-gray-50 py-2 text-[11px] font-semibold text-emerald-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                    Ketuk untuk melihat bukti
                                </span>
                            </button>
                        @else
                            <a href="{{ route('customer.payment.show', $payment) }}"
                               class="mt-3 w-full flex items-center justify-center gap-2 text-xs font-semibold text-emerald-700 border-2 border-dashed border-emerald-200 rounded-lg py-3">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3-3m0 0l3 3m-3-3v8"/></svg>
                                Upload Bukti Transfer
                            </a>
                        @endif
                    @endif
                @else
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs font-semibold text-gray-900">Cash</p>
                        <p class="mt-1.5 text-[11px] text-gray-500 leading-relaxed">Siapkan uang tunai sejumlah total tagihan. Pembayaran dikonfirmasi toko setelah pesanan selesai.</p>
                    </div>
                    @if($payment->status !== 'paid')
                        <p class="mt-2.5 flex items-center gap-1 text-[11px] text-gray-500">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            Menunggu konfirmasi pembayaran dari toko.
                        </p>
                    @endif
                @endif

                @if($payment->status === 'paid')
                    <div class="mt-3 rounded-xl bg-emerald-600 text-white p-3.5 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold">Pembayaran Lunas</p>
                            <p class="text-[11px] text-emerald-100 mt-0.5">Dibayar pada {{ $paidAt?->format('d M Y, H.i') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-semibold">LUNAS</span>
                    </div>
                @endif
            </section>
        @endif

        <section class="mt-3 rounded-xl p-3.5 flex items-start gap-3 {{ $isPickup ? 'bg-primary-500 text-white' : 'bg-warning-500 text-white' }}">
            @if($isPickup)
                <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
            @else
                <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 19a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM2 5h12v10H2zM14 9h4l3 3v3h-7z"/></svg>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold {{ $isPickup ? 'text-white' : 'text-white' }}">{{ $isPickup ? 'Ambil Sendiri' : 'Diantar' }}</p>
                @if($isPickup)
                    <p class="text-[11px] mt-1 opacity-90">Ambil di <span class="font-semibold">{{ $order->store->locationNode?->name ?? 'toko' }}</span>
                        @if($order->store->phone) · {{ $order->store->phone }} @endif
                    </p>
                    <p class="text-[11px] mt-1 opacity-90">Tanpa ongkos kirim</p>
                @else
                    <p class="text-[11px] mt-1 opacity-90 leading-relaxed">{{ $addressLines[0] ?? 'Alamat pelanggan' }}</p>
                    @if(isset($addressLines[1]) && $addressLines[1])
                        <p class="text-[11px] mt-0.5 opacity-90">{{ $addressLines[1] }}</p>
                    @endif
                    @if($order->distance_km_snapshot !== null)
                        <p class="text-[11px] mt-1 opacity-90">{{ (float) $order->distance_km_snapshot }} km dari toko</p>
                    @endif
                @endif
            </div>
        </section>

        <section class="mt-3 bg-white rounded-xl border border-gray-100 overflow-hidden">
            <a href="{{ route('storefront.store', $order->store->slug) }}"
               class="flex items-center gap-2.5 px-3.5 py-3 border-b border-gray-100">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-[10px] shrink-0">{{ $initials }}</div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-gray-900 flex items-center gap-1">
                        {{ $order->store->store_name }}
                        @if($order->store->level && $order->store->level->name !== 'Free')
                            <span class="text-amber-500 text-[10px]">👑</span>
                        @endif
                    </p>
                    @if($order->store->locationNode)
                        <p class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1">
                            <svg class="w-3 h-3 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $order->store->locationNode->name }}
                        </p>
                    @endif
                </div>
                <span class="shrink-0">
                    <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
            </a>

            <div class="divide-y divide-gray-50">
                @foreach($order->items as $item)
                    @php
                        $img = $item->product?->images->first()?->path ? asset('storage/' . $item->product->images->first()->path) : null;
                        $itemHref = $item->product ? route('storefront.product', [$order->store->slug, $item->product->slug]) : null;
                    @endphp
                    <div class="py-3 px-3.5 {{ $itemHref ? 'cursor-pointer' : '' }}" @if($itemHref) @click="window.location.href = @js($itemHref)" @endif>
                        <div class="flex gap-3">
                            @if($img)
                                <div class="w-14 h-14 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                                    <img src="{{ $img }}" alt="" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-14 h-14 rounded-lg bg-gray-100 shrink-0"></div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-medium text-gray-900 leading-snug">{{ $item->name_snapshot }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $item->variant_snapshot ? 'Kemasan: ' . $item->variant_snapshot . ' · x' . $item->qty : 'Tanpa varian · x' . $item->qty }}</p>
                                @if($order->status === 'completed')
                                    @if($item->rating)
                                        <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                                            <span class="text-amber-500 text-xs">
                                                @for($i = 1; $i <= 5; $i++){{ $i <= $item->rating->rating ? '★' : '☆' }}@endfor
                                            </span>
                                            @if($item->rating->review)
                                                <p class="text-[11px] text-gray-600">{{ $item->rating->review }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-[11px] text-gray-400 mt-1.5">Belum dinilai</p>
                                    @endif
                                @endif
                            </div>
                            <span class="text-xs font-semibold text-gray-900 shrink-0">Rp {{ number_format((float) $item->subtotal_snapshot, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($order->status === 'completed' && ! $order->hasBeenReviewed())
                <div class="px-3.5 py-3 border-t border-gray-100 flex justify-end">
                    <a href="{{ route('customer.order.review', $order) }}"
                       class="text-[11px] font-semibold text-white bg-emerald-700 rounded-lg px-3 py-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Beri Ulasan
                    </a>
                </div>
            @endif
        </section>

        <section class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5">
            <p class="text-[13px] font-semibold text-gray-800 mb-2.5">Rincian Pembayaran</p>
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <span>Subtotal Produk</span><span>Rp {{ number_format((float) ($order->subtotal + $order->discount), 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <span>Ongkos Kirim</span>
                    @if((float) $order->shipping_cost === 0.0)
                        <span class="text-emerald-700 font-medium">{{ $isPickup ? 'Gratis (Ambil Sendiri)' : 'Gratis' }}</span>
                    @else
                        <span>Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</span>
                    @endif
                </div>
                @if((float) $order->discount > 0)
                    <div class="flex items-center justify-between text-xs text-gray-600">
                        <span>Diskon Promo</span><span class="text-red-500">-Rp {{ number_format((float) $order->discount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
            <div class="border-t border-gray-100 mt-2.5 pt-2.5 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-800">Total Pembayaran</span>
                <span class="text-sm font-extrabold text-emerald-700">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
            </div>
        </section>
    </main>

    @if($order->status === 'pending' || $order->status === 'completed')
        <div class="fixed bottom-14 inset-x-0 max-w-[420px] mx-auto bg-white border-t border-gray-100 px-4 py-3 flex items-center gap-2.5 z-40">
            @if($order->status === 'pending')
                <button type="button" @click="$store.toast.warning('Fitur pembatalan belum tersedia')"
                        class="flex-1 text-xs font-semibold text-red-600 border border-red-200 rounded-lg py-3 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batalkan Pesanan
                    </button>
                <button type="button" @click="$store.toast.warning('Fitur chat belum tersedia')"
                        class="flex-1 text-xs font-semibold text-white bg-emerald-700 rounded-lg py-3 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Chat Toko
                    </button>
            @else
                <a href="{{ route('storefront.store', $order->store->slug) }}"
                   class="flex-1 text-xs font-semibold text-emerald-700 border border-emerald-700 rounded-lg py-3 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v8h4a1 1 0 001-1V7a1 1 0 00-1-1H4zm12 2a4 4 0 11-8 0v9a2 2 0 002 2h4a2 2 0 002-2v-1h4V6a2 2 0 00-2-2h-2zm4 7v6m-15 0v-3H4"/></svg>
                    Beli Lagi
                </a>
                @if($order->hasBeenReviewed())
                    <a href="{{ route('customer.order.review', $order) }}"
                       class="flex-1 text-xs font-semibold text-gray-600 border border-gray-200 rounded-lg py-3 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Lihat Ulasan
                    </a>
                @else
                    <a href="{{ route('customer.order.review', $order) }}"
                       class="flex-1 text-xs font-semibold text-white bg-emerald-700 rounded-lg py-3 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Beri Ulasan
                    </a>
                @endif
            @endif
        </div>
    @endif

    <div x-cloak x-show="siblingSheet" class="fixed inset-0 z-50" @keydown.escape.window="siblingSheet = false">
        <div class="absolute inset-0 bg-black/40" @click="siblingSheet = false"></div>
        <div class="absolute inset-x-0 bottom-0 max-w-[420px] mx-auto bg-white rounded-t-2xl"
             x-show="siblingSheet"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">
            <div class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-gray-100">
                <div>
                    <h2 class="font-bold text-[15px] text-gray-900">Pesanan dalam Invoice Ini</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $order->invoice->invoice_no }} · Total Rp {{ number_format((float) $order->invoice->grand_total, 0, ',', '.') }}</p>
                </div>
                <button type="button" @click="siblingSheet = false" class="w-7 h-7 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-4 py-3 divide-y divide-gray-100 max-h-[60vh] overflow-y-auto">
                @foreach($order->invoice->orders as $sibling)
                    @php
                        $isCurrent = $sibling->id === $order->id;
                    @endphp
                    @if($isCurrent)
                        <div class="py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-[10px] shrink-0">
                                {{ collect(explode(' ', trim($sibling->store->store_name)))->filter()->take(2)->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))->implode('') }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-gray-900">{{ $sibling->store->store_name }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $sibling->items->count() }} produk · Rp {{ number_format((float) $sibling->total, 0, ',', '.') }} · sedang dilihat</p>
                            </div>
                            <span class="text-[10px] font-semibold rounded-full px-2 py-1 shrink-0
                                {{ $sibling->status === 'pending' ? 'bg-amber-50 text-amber-700' : ($sibling->status === 'processing' ? 'bg-blue-50 text-blue-700' : ($sibling->status === 'shipped' ? 'bg-indigo-50 text-indigo-700' : ($sibling->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'))) }}">
                                {{ $sibling->status === 'pending' ? 'Menunggu Bayar' : ($sibling->status === 'processing' ? 'Diproses Toko' : ($sibling->status === 'shipped' ? 'Dikirim' : ($sibling->status === 'completed' ? 'Selesai' : 'Dibatalkan'))) }}
                            </span>
                        </div>
                    @else
                        <a href="{{ route('customer.order.show', $sibling) }}" @click="siblingSheet = false"
                           class="block py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-[10px] shrink-0">
                                {{ collect(explode(' ', trim($sibling->store->store_name)))->filter()->take(2)->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))->implode('') }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-gray-900">{{ $sibling->store->store_name }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $sibling->items->count() }} produk · Rp {{ number_format((float) $sibling->total, 0, ',', '.') }}</p>
                            </div>
                            <span class="text-[10px] font-semibold rounded-full px-2 py-1 shrink-0
                                {{ $sibling->status === 'pending' ? 'bg-amber-50 text-amber-700' : ($sibling->status === 'processing' ? 'bg-blue-50 text-blue-700' : ($sibling->status === 'shipped' ? 'bg-indigo-50 text-indigo-700' : ($sibling->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'))) }}">
                                {{ $sibling->status === 'pending' ? 'Menunggu Bayar' : ($sibling->status === 'processing' ? 'Diproses Toko' : ($sibling->status === 'shipped' ? 'Dikirim' : ($sibling->status === 'completed' ? 'Selesai' : 'Dibatalkan'))) }}
                            </span>
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="px-4 pb-4 pt-1">
                <p class="text-[10px] text-gray-400 leading-relaxed">Setiap toko memproses dan menagih pembayarannya masing-masing, meskipun berasal dari satu checkout.</p>
            </div>
        </div>
    </div>

</div>
@endsection