@extends('customer.layouts.app')
@section('title', 'Edit Profil')

@section('content')
<header class="sticky top-0 z-30 bg-white border-b border-gray-100 px-4 py-3 flex items-center gap-3">
    <a href="{{ route('customer.account') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="font-bold text-[15px] text-gray-900">Edit Profil</h1>
</header>

<main class="px-4 pb-6">
    <div class="flex flex-col items-center py-5">
        <div class="relative">
            <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-2xl">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <button type="button" class="absolute bottom-0 right-0 w-7 h-7 bg-emerald-700 rounded-full flex items-center justify-center border-2 border-white pointer-events-none opacity-50">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('customer.account.update') }}" class="space-y-3.5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-[11px] font-medium text-gray-500">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name', $customer->name) }}" required
                   class="w-full mt-1 text-sm text-gray-800 border border-gray-200 rounded-lg px-3 py-2.5 focus:border-emerald-600 focus:ring-0">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-[11px] font-medium text-gray-500">Nomor HP</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                   class="w-full mt-1 text-sm text-gray-800 border border-gray-200 rounded-lg px-3 py-2.5 focus:border-emerald-600 focus:ring-0">
            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-[11px] font-medium text-gray-500">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $customer->email) }}" required
                   class="w-full mt-1 text-sm text-gray-800 border border-gray-200 rounded-lg px-3 py-2.5 focus:border-emerald-600 focus:ring-0">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full mt-6 text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3">Simpan Perubahan</button>
    </form>
</main>
@endsection