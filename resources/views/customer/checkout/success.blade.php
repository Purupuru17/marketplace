@extends('customer.layouts.app')
@section('title', 'Pesanan Dibuat')

@section('content')
<main class="px-4">
    <section class="mt-8 rounded-2xl border border-gray-100 bg-white p-6 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="mt-4 text-lg font-bold text-gray-900">Pesanan Berhasil Dibuat</h1>
        <p class="mt-2 text-xs text-gray-500">
            Invoice <span class="font-semibold text-gray-800">{{ $invoice->invoice_no }}</span>
            sebesar <span class="font-semibold text-emerald-700">Rp {{ number_format((float) $invoice->grand_total, 0, ',', '.') }}</span>
        </p>
    </section>

    @if($invoice->orders->isNotEmpty())
        <section class="mt-4 space-y-3">
            @foreach($invoice->orders as $order)
                @php $orderPayment = $order->payments->first(); @endphp
                <div class="rounded-xl border border-gray-100 bg-white p-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-gray-900">{{ $order->order_no }}</p>
                            <p class="text-[11px] text-gray-500">
                                {{ $order->store->store_name }} · {{ $order->items()->count() }} item
                                @if($orderPayment)
                                    · {{ \App\Services\Customer\PaymentService::METHODS[$orderPayment->payment_method] ?? $orderPayment->payment_method }}
                                @endif
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-semibold text-amber-600">{{ ucfirst($order->status) }}</span>
                    </div>
                    @if($orderPayment && $orderPayment->status === 'pending' && $orderPayment->payment_method === 'bank_transfer')
                        <div class="mt-3 flex items-center justify-between gap-3 border-t border-gray-50 pt-2.5">
                            <p class="text-[11px] text-gray-500">
                                @if($orderPayment->payment_proof_path)
                                    Bukti sudah dikirim. Menunggu konfirmasi toko.
                                @else
                                    Upload bukti transfer agar pesanan diproses.
                                @endif
                            </p>
                            <a href="{{ route('customer.payment.show', $orderPayment->id) }}"
                               class="shrink-0 rounded-lg bg-emerald-700 px-3 py-1.5 text-[11px] font-semibold text-white">
                                {{ $orderPayment->payment_proof_path ? 'Lihat Pembayaran' : 'Upload Bukti' }}
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    @endif

    <section class="mt-5 space-y-2">
        <a href="{{ route('customer.order.index') }}" class="block w-full text-center text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3">Lihat Pesanan Saya</a>
        <a href="{{ route('storefront.index') }}" class="block w-full text-center text-xs font-semibold text-emerald-700 border border-emerald-700 rounded-lg py-3">Belanja Lagi</a>
    </section>
</main>
@endsection