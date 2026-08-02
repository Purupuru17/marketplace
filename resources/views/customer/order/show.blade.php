@extends('customer.layouts.app')
@section('title', $invoice->invoice_no)

@section('content')
<a href="{{ route('customer.order.index') }}"
   class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
    @svg('heroicon-o-arrow-left', 'h-4 w-4') Pesanan Saya
</a>

<div class="mb-6 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $invoice->invoice_no }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->created_at->format('d M Y H:i') }}</p>
    </div>
    <span class="rounded-full px-3 py-1 text-sm font-semibold @if($invoice->status === 'paid') bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400 @else bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 @endif">
        {{ ucfirst($invoice->status) }}
    </span>
</div>

@php
    $payment = $invoice->payments->first();
@endphp
@if($payment)
    <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-900">
        <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ \App\Services\Customer\PaymentService::METHODS[$payment->payment_method] ?? $payment->payment_method }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                @if($payment->status === 'paid')
                    Lunas pada {{ $payment->paid_at?->format('d M Y H:i') }}
                @elseif($payment->payment_method === 'cod')
                    Pembayaran tunai dilakukan saat barang diterima.
                @elseif($payment->status === 'pending' && $payment->expired_at && now()->gt($payment->expired_at))
                    Pembayaran kedaluwarsa — buat pembayaran baru.
                @else
                    Menunggu pembayaran, batas {{ $payment->expired_at?->format('d M Y H:i') }}
                @endif
            </p>
        </div>
        <div>
            @if($payment->status === 'paid')
                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-600 dark:bg-green-500/10 dark:text-green-400">
                    @svg('heroicon-o-check-circle', 'h-4 w-4') Lunas
                </span>
            @elseif($payment->payment_method === 'cod')
                <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">COD</span>
            @elseif($payment->status === 'pending' && $payment->expired_at && now()->gt($payment->expired_at))
                <form method="POST" action="{{ route('customer.payment.store', $invoice->id) }}">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Bayar Ulang
                    </button>
                </form>
            @else
                <a href="{{ route('customer.payment.show', $invoice->id) }}"
                   class="inline-block rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
                    Bayar Sekarang
                </a>
            @endif
        </div>
    </div>
@endif

@if($invoice->orders->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Invoice ini belum memiliki order.
    </div>
@else
    <div class="space-y-6">
        @foreach($invoice->orders as $order)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $order->order_no }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->store->store_name }}</p>
                    </div>
                    <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($order->items as $item)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $item->name_snapshot }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $item->sku_snapshot }}
                                        @if($item->variant_snapshot) · {{ $item->variant_snapshot }} @endif
                                        × {{ $item->qty }}
                                    </p>
                                </div>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format((float) $item->subtotal_snapshot, 0, ',', '.') }}
                                </p>
                            </div>

                            @if($order->status === 'completed')
                                <div class="mt-3 rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30">
                                    @if($item->rating)
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <div class="flex items-center gap-0.5 text-amber-500">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @svg($i <= $item->rating->rating ? 'heroicon-s-star' : 'heroicon-o-star', 'h-4 w-4')
                                                    @endfor
                                                </div>
                                                @if($item->rating->review)
                                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $item->rating->review }}</p>
                                                @endif
                                            </div>
                                            <form method="POST" action="{{ route('customer.rating.destroy', $item->rating->id) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-600">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('customer.rating.store') }}" class="space-y-2">
                                            @csrf
                                            <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Nilai:</span>
                                                <div class="flex items-center gap-2">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <label class="cursor-pointer">
                                                            <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" @checked($i === 5)>
                                                            <span class="text-gray-300 transition peer-checked:text-amber-500 peer-hover:text-amber-400">@svg('heroicon-s-star', 'h-5 w-5')</span>
                                                        </label>
                                                    @endfor
                                                </div>
                                            </div>
                                            <textarea name="review" rows="2" placeholder="Tulis ulasan (opsional)..."
                                                      class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                                            @error('order_item_id')
                                                <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                            <button type="submit"
                                                    class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                                                Kirim Penilaian
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="space-y-1 border-t border-gray-100 px-6 py-4 text-sm dark:border-gray-800">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Ongkir ({{ $order->distance_km_snapshot !== null ? $order->distance_km_snapshot . ' km' : '—' }})</span>
                        <span>Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-gray-900 dark:text-white">
                        <span>Total</span>
                        <span>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between">
            <p class="text-lg font-bold text-gray-900 dark:text-white">Grand Total</p>
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $invoice->grand_total, 0, ',', '.') }}</p>
        </div>
    </div>
@endif
@endsection
