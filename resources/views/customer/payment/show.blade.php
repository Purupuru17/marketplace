@extends('customer.layouts.app')
@section('title', 'Pembayaran')

@php
    $methods = \App\Services\Customer\PaymentService::METHODS;
    $isPickup = $order->fulfillment_type === 'pickup';

    $initials = collect(explode(' ', trim($order->store->store_name)))->filter()->take(2)
        ->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))->implode('');

    $addrRaw = $order->address_snapshot;
    $decoded = is_string($addrRaw) ? json_decode($addrRaw, true) : null;
    $addressLines = is_array($decoded)
        ? array_values(array_filter([$decoded['full_address'] ?? null, trim(($decoded['recipient_name'] ?? '') . ' · ' . ($decoded['phone'] ?? ''), ' ·')]))
        : ($addrRaw ? [$addrRaw] : []);
@endphp

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
    <a href="{{ route('customer.order.show', $order) }}" class="w-8 h-8 flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div class="min-w-0">
        <h1 class="font-bold text-[15px] text-gray-900">Pembayaran</h1>
        <p class="text-[11px] text-gray-500 truncate">{{ $order->order_no }}</p>
    </div>
</header>

<main class="px-4 pb-10">
    <section class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-[10px] shrink-0">{{ $initials }}</div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-gray-900 flex items-center gap-1.5">
                        {{ $order->store->store_name }}
                        @if($order->store->level && $order->store->level->name !== 'Free')
                            <span class="text-amber-500 text-[10px]">👑</span>
                        @endif
                        @if($isPickup)
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                Ambil Sendiri
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-semibold text-orange-700">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 19a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM2 5h12v10H2zM14 9h4l3 3v3h-7z"/></svg>
                                Diantar
                            </span>
                        @endif
                    </p>
            </div>
            <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700">Menunggu Bayar</span>
        </div>

        <div class="mt-3 pt-3 border-t border-gray-100 divide-y divide-gray-50">
            @foreach($order->items as $item)
                @php $img = $item->product?->images->first()?->path ? asset('storage/' . $item->product->images->first()->path) : null; @endphp
                <div class="py-2.5 first:pt-0 last:pb-0 flex gap-3">
                    @if($img)
                        <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                            <img src="{{ $img }}" alt="" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0"></div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-[12px] font-medium text-gray-900 leading-snug line-clamp-2">{{ $item->name_snapshot }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $item->variant_snapshot ? 'Kemasan: ' . $item->variant_snapshot . ' · x' . $item->qty : 'Tanpa varian · x' . $item->qty }}</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-900 shrink-0">Rp {{ number_format((float) $item->subtotal_snapshot, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
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
        <div class="mt-3 rounded-xl bg-emerald-50 border border-emerald-100 p-3 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-800">Total Tagihan</span>
            <span class="text-lg font-extrabold text-emerald-700">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
        </div>
    </section>

    <section class="mt-3 bg-white rounded-xl border border-gray-100 p-3.5">
        <p class="text-[13px] font-semibold text-gray-800 mb-2">Pembayaran — {{ $methods[$payment->payment_method] ?? $payment->payment_method }}</p>

        @if($payment->payment_method === 'bank_transfer')
            <div class="bg-gray-50 rounded-lg p-3 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-gray-500">Bank</span>
                    <span class="text-xs font-semibold text-gray-900">{{ $info['bank'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-gray-500">No. Rekening</span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-semibold text-gray-900 font-mono">{{ $info['account_number'] }}</span>
                        <button type="button"
                                @click="navigator.clipboard.writeText(@js($info['account_number'])); $store.toast.success('No. rekening disalin')">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="11" height="11" rx="1.5"/><path d="M5 15V5a2 2 0 012-2h10"/></svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-gray-500">Atas Nama</span>
                    <span class="text-xs font-semibold text-gray-900">{{ $info['account_name'] }}</span>
                </div>
            </div>
            <p class="mt-2.5 text-[11px] text-gray-500 leading-relaxed">{{ $info['instruction'] }}</p>

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
                <form method="POST" action="{{ route('customer.payment.proof', $payment->id) }}" enctype="multipart/form-data"
                      class="mt-3 border-2 border-dashed border-emerald-200 rounded-lg p-3">
                    @csrf
                    <label class="block w-full text-center cursor-pointer">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-700">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3-3m0 0l3 3m-3-3v8"/></svg>
                            Upload Bukti Transfer
                        </span>
                        <input type="file" name="proof" accept="image/*" required
                               class="mt-1.5 block w-full text-[11px] text-gray-600 file:mr-2 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-[11px] file:font-semibold file:text-emerald-700">
                    </label>
                    <button type="submit" class="mt-2.5 w-full text-xs font-semibold text-white bg-emerald-700 rounded-lg py-2.5">Kirim Bukti</button>
                    @error('proof')
                        <p class="mt-1.5 text-[11px] text-red-600">{{ $message }}</p>
                    @enderror
                </form>
            @endif
        @else
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-xs font-semibold text-gray-900">Cash</p>
                <p class="mt-1.5 text-[11px] text-gray-500 leading-relaxed">{{ $info['instruction'] }}</p>
            </div>
        @endif
    </section>

    <div class="mt-3 flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-[11px] text-amber-700">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Menunggu konfirmasi pembayaran dari toko.
    </div>
</main>
@endsection