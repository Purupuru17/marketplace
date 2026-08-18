@extends('customer.layouts.app')
@section('title', 'Pembayaran')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('customer.order.show', $invoice) }}"
       class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
        @svg('heroicon-o-arrow-left', 'h-4 w-4') Kembali ke Pesanan
    </a>

    @if($payment->status === 'paid')
        <div class="rounded-2xl border border-green-200 bg-green-50 p-8 text-center dark:border-green-500/30 dark:bg-green-500/10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400">
                @svg('heroicon-o-check', 'h-7 w-7')
            </div>
            <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">Pembayaran Lunas</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $invoice->invoice_no }} · Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                · {{ $payment->paid_at?->format('d M Y H:i') }}
            </p>
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pembayaran {{ $invoice->invoice_no }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Total: <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
                </p>
            </div>

            <div class="space-y-4 px-6 py-6">
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                    @if($payment->payment_method === 'cash')
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Cash</p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $info['instruction'] }}</p>
                    @else
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Bank</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $info['bank'] }}</p>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No. Rekening</p>
                            <p class="font-mono font-semibold text-gray-900 dark:text-white">{{ $info['account_number'] }}</p>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Atas Nama</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $info['account_name'] }}</p>
                        </div>
                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ $info['instruction'] }}</p>
                    @endif
                </div>

                @if($payment->payment_method === 'bank_transfer')
                    @if($payment->payment_proof_path)
                        <div class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">
                            @svg('heroicon-o-check-circle', 'h-5 w-5')
                            Bukti pembayaran sudah terkirim. Menunggu konfirmasi toko.
                        </div>
                    @else
                        <form method="POST" action="{{ route('customer.payment.proof', $payment->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300" for="proof">
                                    Upload Bukti Transfer
                                </label>
                                <input type="file" name="proof" id="proof" accept="image/*" required
                                       class="block w-full rounded-lg border border-gray-300 text-sm text-gray-700 file:mr-3 file:rounded-l-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-indigo-600 hover:file:bg-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:file:bg-indigo-500/10 dark:file:text-indigo-300">
                                @error('proof')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                    class="mt-3 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Kirim Bukti
                            </button>
                        </form>
                    @endif
                @endif

                <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                    @svg('heroicon-o-clock', 'h-5 w-5')
                    Menunggu konfirmasi pembayaran dari toko.
                </div>
            </div>
        </div>
    @endif
</div>
@endsection