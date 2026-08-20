@extends('customer.layouts.app')
@section('title', 'Pesanan Saya')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3">
    <h1 class="font-bold text-[15px] text-gray-900">Pesanan Saya</h1>
</header>

<main class="px-4">
    @if($invoices->isEmpty())
        <div class="flex flex-col items-center justify-center text-center px-8 py-24">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-800">Belum ada pesanan</p>
            <p class="text-xs text-gray-500 mt-1.5">Yuk mulai belanja dari toko-toko sekitarmu</p>
            <a href="{{ route('storefront.index') }}" class="mt-4 text-xs font-semibold text-white bg-emerald-700 rounded-lg px-5 py-2.5">Mulai Belanja</a>
        </div>
    @else
        <div class="space-y-3 mt-4">
            @foreach($invoices as $invoice)
                <a href="{{ route('customer.order.show', $invoice->id) }}" class="block bg-white rounded-xl border border-gray-100 p-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-900">{{ $invoice->invoice_no }}</p>
                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold {{ $invoice->status === 'paid' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $invoice->created_at->format('d M Y H:i') }} · {{ $invoice->orders()->count() }} toko</p>
                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-50">
                        <span class="text-lg font-extrabold text-emerald-700">Rp {{ number_format((float) $invoice->grand_total, 0, ',', '.') }}</span>
                        <span class="text-[11px] font-semibold text-emerald-700 flex items-center gap-1">Detail
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $invoices->links() }}
        </div>
    @endif
</main>
@endsection