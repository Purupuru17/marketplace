@extends('customer.layouts.app')
@section('title', 'Pesanan Dibuat')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">
            @svg('heroicon-o-check', 'h-8 w-8')
        </div>
        <h1 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Pesanan Berhasil Dibuat</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Invoice <span class="font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_no }}</span>
            sebesar <span class="font-semibold text-indigo-600 dark:text-indigo-400">Rp {{ number_format((float) $invoice->grand_total, 0, ',', '.') }}</span>
        </p>

        <div class="mt-6 space-y-3 text-left">
            @foreach($invoice->orders as $order)
                <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $order->order_no }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->store->store_name }} · {{ $order->items()->count() }} item</p>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
            <a href="{{ route('customer.order.index') }}"
               class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                Lihat Pesanan Saya
            </a>
            <a href="{{ route('storefront.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Belanja Lagi
            </a>
        </div>
    </div>
</div>
@endsection
