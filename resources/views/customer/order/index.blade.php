@extends('customer.layouts.app')
@section('title', 'Pesanan Saya')

@php
    $tabs = [
        '' => 'Semua',
        'pending' => 'Menunggu Bayar',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    $statusLabels = [
        'pending' => 'Menunggu Bayar',
        'processing' => 'Diproses Toko',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    $statusColors = [
        'pending' => 'bg-amber-50 text-amber-700',
        'processing' => 'bg-blue-50 text-blue-700',
        'shipped' => 'bg-indigo-50 text-indigo-700',
        'completed' => 'bg-emerald-50 text-emerald-700',
        'cancelled' => 'bg-red-50 text-red-600',
    ];

    $initials = fn ($name) => collect(explode(' ', trim($name)))->filter()->take(2)->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100">
    <div class="px-4 py-3 flex items-center justify-between">
        <h1 class="font-bold text-[15px] text-gray-900">Pesanan Saya</h1>
    </div>
    <div class="flex overflow-x-auto no-scrollbar px-4 gap-5 border-b border-gray-100">
        @foreach($tabs as $value => $label)
            <a href="{{ $value ? route('customer.order.index', ['status' => $value]) : route('customer.order.index') }}"
               class="shrink-0 text-xs py-2.5 {{ ($status ?? '') === $value ? 'font-semibold text-emerald-700 border-b-2 border-emerald-700' : 'font-medium text-gray-500' }}">{{ $label }}</a>
        @endforeach
    </div>
</header>

<main class="px-4">
    @php
        $visibleInvoices = $invoices->filter(fn ($invoice) => ! $status || $invoice->orders->where('status', $status)->isNotEmpty());
    @endphp

    @if($invoices->isEmpty() || $visibleInvoices->isEmpty())
        <div class="flex flex-col items-center justify-center text-center px-8 py-24">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-800">
                @if($status) Belum ada pesanan berstatus "{{ $tabs[$status] ?? $status }}" @else Belum ada pesanan @endif
            </p>
            <p class="text-xs text-gray-500 mt-1.5">Pesananmu akan muncul di sini</p>
        </div>
    @else
        <div class="space-y-4 mt-4">
            @foreach($visibleInvoices as $invoice)
                @php
                    $orders = $status ? $invoice->orders->where('status', $status) : $invoice->orders;
                @endphp
                <section>
                    <div class="flex items-center justify-between mb-2 px-0.5">
                        <p class="text-[11px] text-gray-500 font-medium">{{ $invoice->created_at->format('d M Y') }} · <span class="text-gray-700">{{ $invoice->invoice_no }}</span></p>
                        @if($invoice->orders->count() > 1)
                            <span class="text-[11px] font-medium text-gray-400">{{ $invoice->orders->count() }} toko</span>
                        @endif
                    </div>

                    <div class="{{ $invoice->orders->count() > 1 ? 'border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100' : '' }}">
                        @foreach($orders as $order)
                            @php
                                $payment = $order->payments->first();
                                $firstItem = $order->items->first();
                                $itemNames = $order->items->take(2)->pluck('name_snapshot')->implode(', ');
                                $img = $firstItem?->product?->images->first()?->path ? asset('storage/' . $firstItem->product->images->first()->path) : null;
                            @endphp
                            <div class="{{ $invoice->orders->count() > 1 ? 'bg-white p-3.5' : 'bg-white border border-gray-200 rounded-xl p-3.5' }} {{ $order->status === 'cancelled' ? 'opacity-70' : '' }}">
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-[9px] shrink-0">{{ $initials($order->store->store_name) }}</div>
                                        <p class="text-xs font-semibold text-gray-900">{{ $order->store->store_name }}</p>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        @if($order->fulfillment_type === 'pickup')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-1 text-[9px] font-semibold text-blue-700">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                                Ambil Sendiri
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-1 text-[9px] font-semibold text-orange-700">
                                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 19a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM2 5h12v10H2zM14 9h4l3 3v3h-7z"/></svg>
                                                Antar/Kirim
                                            </span>
                                        @endif
                                        <span class="text-[10px] font-semibold rounded-full px-2 py-1 {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span>
                                    </div>
                                </div>

                                <a href="{{ route('customer.order.show', $order) }}" class="flex gap-3">
                                    @if($img)
                                        <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0 overflow-hidden">
                                            <img src="{{ $img }}" alt="" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gray-100 shrink-0"></div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[12px] text-gray-800 leading-snug line-clamp-2">{{ $itemNames }}</p>
                                        <p class="text-[11px] text-gray-500 mt-1">
                                            {{ $order->items->count() }} produk · Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                                            @if($order->status === 'cancelled' && $payment?->expired_at && $payment->expired_at->isPast())
                                                · Batas bayar terlewat
                                            @endif
                                        </p>
                                    </div>
                                </a>

                                <div class="flex gap-2 mt-3">
                                    @if($order->status === 'pending')
                                        <a href="{{ route('customer.order.show', $order) }}"
                                           class="flex-1 text-[11px] font-semibold text-gray-600 border border-gray-200 rounded-lg py-2 flex items-center justify-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                            Lihat Detail
                                        </a>
                                        @if($payment && $payment->status !== 'paid')
                                            <a href="{{ route('customer.payment.show', $payment) }}"
                                               class="flex-1 text-[11px] font-semibold text-white bg-emerald-700 rounded-lg py-2 flex items-center justify-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                Bayar Sekarang
                                            </a>
                                        @endif
                                    @elseif($order->status === 'completed')
                                        <a href="{{ route('storefront.store', $order->store->slug) }}"
                                           class="flex-1 text-[11px] font-semibold text-emerald-700 border border-emerald-700 rounded-lg py-2 flex items-center justify-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v8h4a1 1 0 001-1V7a1 1 0 00-1-1H4zm12 2a4 4 0 11-8 0v9a2 2 0 002 2h4a2 2 0 002-2v-1h4V6a2 2 0 00-2-2h-2zm4 7v6m-15 0v-3H4"/></svg>
                                            Beli Lagi
                                        </a>
                                        @if($order->hasBeenReviewed())
                                            <a href="{{ route('customer.order.review', $order) }}"
                                               class="flex-1 text-[11px] font-semibold text-gray-600 border border-gray-200 rounded-lg py-2 flex items-center justify-center gap-1">
                                                <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                Lihat Ulasan
                                            </a>
                                        @else
                                            <a href="{{ route('customer.order.review', $order) }}"
                                               class="flex-1 text-[11px] font-semibold text-white bg-emerald-700 rounded-lg py-2 flex items-center justify-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                Beri Ulasan
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('customer.order.show', $order) }}"
                                           class="w-full text-[11px] font-semibold text-gray-600 border border-gray-200 rounded-lg py-2 flex items-center justify-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                            Lihat Detail
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $invoices->links() }}
        </div>
    @endif
</main>
@endsection