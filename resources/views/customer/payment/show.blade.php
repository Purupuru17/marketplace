@extends('customer.layouts.app')
@section('title', 'Pembayaran')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
    <a href="{{ route('customer.order.show', $invoice) }}" class="w-8 h-8 flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="font-bold text-[15px] text-gray-900">Pembayaran</h1>
</header>

<main class="px-4">
    @if($payment->status === 'paid')
        <div class="mt-8 rounded-2xl border border-green-200 bg-green-50 p-8 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="mt-4 text-base font-bold text-gray-900">Pembayaran Lunas</h1>
            <p class="mt-1 text-[11px] text-gray-500">
                {{ $invoice->invoice_no }} · Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                · {{ $payment->paid_at?->format('d M Y H:i') }}
            </p>
        </div>
    @else
        <section class="mt-4 bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="px-3.5 py-3 border-b border-gray-100">
                <h1 class="text-base font-bold text-gray-900">Pembayaran {{ $invoice->invoice_no }}</h1>
                <p class="mt-0.5 text-[11px] text-gray-500">
                    Total: <span class="font-semibold text-gray-900">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
                </p>
            </div>

            <div class="p-3.5 space-y-3">
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    @if($payment->payment_method === 'cash')
                        <p class="text-xs font-semibold text-gray-900">Cash</p>
                        <p class="mt-2 text-[11px] text-gray-500">{{ $info['instruction'] }}</p>
                    @else
                        <div class="space-y-2 text-[11px]">
                            <div class="flex justify-between"><span class="text-gray-500">Bank</span><span class="font-semibold text-gray-900">{{ $info['bank'] }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">No. Rekening</span><span class="font-mono font-semibold text-gray-900">{{ $info['account_number'] }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Atas Nama</span><span class="font-semibold text-gray-900">{{ $info['account_name'] }}</span></div>
                        </div>
                        <p class="mt-3 text-[11px] text-gray-500">{{ $info['instruction'] }}</p>
                    @endif
                </div>

                @if($payment->payment_method === 'bank_transfer')
                    @if($payment->payment_proof_path)
                        <div class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 p-3.5 text-[11px] text-green-700">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Bukti pembayaran sudah terkirim. Menunggu konfirmasi toko.
                        </div>
                    @else
                        <form method="POST" action="{{ route('customer.payment.proof', $payment->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700" for="proof">Upload Bukti Transfer</label>
                                <input type="file" name="proof" id="proof" accept="image/*" required
                                       class="block w-full rounded-lg border border-gray-200 bg-white text-[11px] text-gray-700 file:mr-3 file:rounded-l-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-[11px] file:font-semibold file:text-emerald-700">
                                @error('proof') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="mt-3 w-full text-xs font-semibold text-white bg-emerald-700 rounded-lg py-2.5">Kirim Bukti</button>
                        </form>
                    @endif
                @endif

                <div class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-[11px] text-amber-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Menunggu konfirmasi pembayaran dari toko.
                </div>
            </div>
        </section>
    @endif
</main>
@endsection