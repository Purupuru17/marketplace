@extends('customer.layouts.app')
@section('title', 'Poin Saya')

@section('content')
<div class="mb-6 rounded-2xl bg-indigo-600 p-6 text-white shadow-sm">
    <p class="text-sm font-medium text-indigo-100">Saldo Poin</p>
    <p class="mt-1 text-4xl font-bold">{{ number_format($availablePoints) }}</p>
    <p class="mt-2 text-xs text-indigo-100">1 poin per Rp 5.000 belanja · 100 poin = Rp 1.000</p>
</div>

<div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
        <h2 class="font-semibold text-gray-900 dark:text-white">Riwayat Poin</h2>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($transactions as $tx)
            <div class="flex items-center justify-between gap-4 px-6 py-4">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $tx->description ?? ucfirst($tx->type) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $tx->created_at->format('d M Y H:i') }}</p>
                </div>
                <span class="font-semibold @if($tx->points > 0) text-green-600 dark:text-green-400 @else text-red-600 dark:text-red-400 @endif">
                    {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }}
                </span>
            </div>
        @empty
            <div class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                Belum ada riwayat poin.
            </div>
        @endforelse
    </div>
    <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
