@extends('customer.layouts.app')
@section('title', 'Pesanan Saya')

@section('content')
<h1 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">Pesanan Saya</h1>

@if($invoices->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-300 p-12 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
        Belum ada pesanan.
        <a href="{{ route('storefront.index') }}" class="mt-4 block font-medium text-indigo-600 dark:text-indigo-400">Mulai Belanja</a>
    </div>
@else
    <div class="space-y-4">
        @foreach($invoices as $invoice)
            <a href="{{ route('customer.order.show', $invoice->id) }}"
               class="block rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-700">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_no }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->orders()->count() }} toko</p>
                            <p class="font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $invoice->grand_total, 0, ',', '.') }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold @if($invoice->status === 'paid') bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400 @else bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 @endif">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $invoices->links() }}
    </div>
@endif
@endsection
