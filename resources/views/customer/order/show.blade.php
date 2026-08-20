@extends('customer.layouts.app')
@section('title', $invoice->invoice_no)

@section('content')
@php
    $methods = \App\Services\Customer\PaymentService::METHODS;
@endphp
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
    <a href="{{ route('customer.order.index') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <h1 class="font-bold text-[15px] text-gray-900 truncate">{{ $invoice->invoice_no }}</h1>
        <p class="text-[11px] text-gray-500">{{ $invoice->created_at->format('d M Y H:i') }}</p>
    </div>
    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-semibold {{ $invoice->status === 'paid' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
        {{ ucfirst($invoice->status) }}
    </span>
</header>

<main class="px-4">
    @if($invoice->orders->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 mt-4">
            Invoice ini belum memiliki order.
        </div>
    @else
        <div class="space-y-3 mt-4">
            @foreach($invoice->orders as $order)
                @php $orderPayment = $order->payments->first(); @endphp
                <section class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="flex items-center justify-between px-3.5 py-3 border-b border-gray-100">
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-gray-900 truncate">{{ $order->order_no }}</p>
                            <p class="text-[11px] text-gray-500">{{ $order->store->store_name }} · {{ $order->fulfillment_type === 'pickup' ? 'Ambil Sendiri' : 'Kirim / Antar' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-semibold text-amber-600">{{ ucfirst($order->status) }}</span>
                    </div>

                    @if($orderPayment)
                        <div class="px-3.5 py-3 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[13px] font-semibold text-gray-900">{{ $methods[$orderPayment->payment_method] ?? $orderPayment->payment_method }}</p>
                                    <p class="text-[11px] text-gray-500 mt-0.5">
                                        @if($orderPayment->status === 'paid')
                                            Lunas pada {{ $orderPayment->paid_at?->format('d M Y H:i') }}
                                        @else
                                            Menunggu konfirmasi pembayaran dari toko.
                                        @endif
                                    </p>
                                </div>
                                @if($orderPayment->status === 'paid')
                                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-[10px] font-semibold text-green-600">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Lunas
                                    </span>
                                @endif
                            </div>

                            @if($orderPayment->payment_method === 'bank_transfer' && $orderPayment->bank_snapshot)
                                <dl class="mt-2.5 space-y-1.5 text-[11px]">
                                    <div class="flex justify-between"><dt class="text-gray-500">Bank</dt><dd class="font-medium text-gray-900">{{ $orderPayment->bank_snapshot['bank_name'] }}</dd></div>
                                    <div class="flex justify-between"><dt class="text-gray-500">No. Rekening</dt><dd class="font-mono font-medium text-gray-900">{{ $orderPayment->bank_snapshot['account_number'] }}</dd></div>
                                    <div class="flex justify-between"><dt class="text-gray-500">Atas Nama</dt><dd class="font-medium text-gray-900">{{ $orderPayment->bank_snapshot['account_name'] }}</dd></div>
                                </dl>
                            @endif

                            @if($orderPayment->payment_method === 'bank_transfer' && $orderPayment->status !== 'paid')
                                @if($orderPayment->payment_proof_path)
                                    <p class="mt-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-green-600">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Bukti sudah dikirim. Menunggu konfirmasi toko.
                                    </p>
                                @else
                                    <form method="POST" action="{{ route('customer.payment.proof', $orderPayment->id) }}" enctype="multipart/form-data" class="mt-2.5 flex gap-2">
                                        @csrf
                                        <input type="file" name="proof" id="proof-{{ $order->id }}" accept="image/*" required
                                               class="flex-1 min-w-0 text-[11px] border border-gray-200 rounded-lg bg-white text-gray-700 file:mr-2 file:rounded-l-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-[11px] file:font-semibold file:text-emerald-700">
                                        <button type="submit" class="shrink-0 text-[11px] font-semibold text-white bg-emerald-700 rounded-lg px-3.5 py-2">Upload Bukti</button>
                                    </form>
                                    @error('proof')
                                        <p class="mt-1.5 text-[11px] text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            @endif
                        </div>
                    @endif

                    <div class="divide-y divide-gray-50">
                        @foreach($order->items as $item)
                            <div class="px-3.5 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-medium text-gray-900 line-clamp-2">{{ $item->name_snapshot }}</p>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            {{ $item->sku_snapshot }}
                                            @if($item->variant_snapshot) · {{ $item->variant_snapshot }} @endif
                                            × {{ $item->qty }}
                                        </p>
                                    </div>
                                    <p class="shrink-0 text-[13px] font-semibold text-gray-900">Rp {{ number_format((float) $item->subtotal_snapshot, 0, ',', '.') }}</p>
                                </div>

                                @if($order->status === 'completed')
                                    <div class="mt-2.5 rounded-xl border border-gray-100 bg-gray-50/50 p-3">
                                        @if($item->rating)
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <div class="flex items-center gap-0.5 text-amber-500">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <svg class="w-3.5 h-3.5 {{ $i <= $item->rating->rating ? 'fill-amber-500' : 'fill-gray-200' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.075 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                                        @endfor
                                                    </div>
                                                    @if($item->rating->review)
                                                        <p class="mt-1 text-[11px] text-gray-600">{{ $item->rating->review }}</p>
                                                    @endif
                                                </div>
                                                <form method="POST" action="{{ route('customer.rating.destroy', $item->rating->id) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-[11px] font-medium text-red-500">Hapus</button>
                                                </form>
                                            </div>
                                        @else
                                            <form method="POST" action="{{ route('customer.rating.store') }}" class="space-y-2">
                                                @csrf
                                                <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                                                <div class="flex items-center gap-3">
                                                    <span class="text-[11px] font-medium text-gray-700">Nilai:</span>
                                                    <div class="flex items-center gap-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <label class="cursor-pointer">
                                                                <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" @checked($i === 5)>
                                                                <span class="text-gray-300 transition peer-checked:text-amber-500">@svg('heroicon-s-star', 'h-5 w-5')</span>
                                                            </label>
                                                        @endfor
                                                    </div>
                                                </div>
                                                <textarea name="review" rows="2" placeholder="Tulis ulasan (opsional)..."
                                                          class="w-full text-[11px] border border-gray-200 rounded-lg bg-white px-2.5 py-2 text-gray-700 focus:ring-0"></textarea>
                                                <button type="submit" class="text-[11px] font-semibold text-white bg-emerald-700 rounded-lg px-3 py-1.5">Kirim Penilaian</button>
                                                @error('order_item_id')
                                                    <p class="text-[11px] text-red-600">{{ $message }}</p>
                                                @enderror
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="px-3.5 py-3 border-t border-gray-100 space-y-1 text-[11px]">
                        <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between text-gray-500"><span>Ongkir {{ $order->distance_km_snapshot !== null ? '(' . $order->distance_km_snapshot . ' km)' : '' }}</span><span>Rp {{ number_format((float) $order->shipping_cost, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between font-bold text-gray-900"><span>Total</span><span>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span></div>
                    </div>
                </section>
            @endforeach
        </div>

        <section class="mt-3 mb-4 bg-white rounded-xl border border-gray-100 p-3.5 flex items-center justify-between">
            <p class="font-bold text-[15px] text-gray-900">Grand Total</p>
            <p class="text-lg font-extrabold text-emerald-700">Rp {{ number_format((float) $invoice->grand_total, 0, ',', '.') }}</p>
        </section>
    @endif
</main>
@endsection