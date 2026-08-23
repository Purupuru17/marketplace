@extends('customer.layouts.app')
@section('title', 'Masuk')

@section('content')
<main class="px-6 pt-2" x-data="{ show: false }">
    <a href="{{ route('storefront.index') }}" class="inline-flex w-8 h-8 items-center justify-center -ml-2 mt-1">
        <svg class="w-5 h-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>

    <h1 class="mt-3 text-xl font-extrabold text-gray-900">Selamat Datang Kembali</h1>
    <p class="text-sm text-gray-500 mt-1.5">Masuk untuk lanjut belanja di toko sekitarmu.</p>

    <button type="button" class="w-full mt-6 flex items-center justify-center gap-2.5 text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg py-3">
        <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Lanjutkan dengan Google
    </button>

    <div class="flex items-center gap-3 my-5">
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-[11px] text-gray-400 font-medium">atau masuk dengan email</span>
        <div class="flex-1 h-px bg-gray-200"></div>
    </div>

    <form method="POST" action="{{ route('customer.auth.login.store') }}" class="space-y-3.5">
        @csrf
        <div>
            <label for="email" class="block text-[11px] font-medium text-gray-500">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full mt-1 text-sm text-gray-800 border border-gray-200 rounded-lg px-3 py-2.5 focus:border-emerald-600 focus:ring-0">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-[11px] font-medium text-gray-500">Kata Sandi</label>
            <div class="relative mt-1">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required
                       class="w-full text-sm text-gray-800 border border-gray-200 rounded-lg px-3 py-2.5 pr-10 focus:border-emerald-600 focus:ring-0">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.94 3.432a5 5 0 00-1.94-.432c-4.638 0-8.573 3.007-9.963 7.178a1.012 1.012 0 000 .639c.326 1.02.77 1.96 1.31 2.81M9.53 9.53a3 3 0 104.24 4.24M3 3l18 18"/></svg>
                </button>
            </div>
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center text-[13px] text-gray-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
                <span class="ml-2 text-xs">Ingat saya</span>
            </label>
            <a href="#" class="text-xs font-semibold text-emerald-700">Lupa kata sandi?</a>
        </div>

        <button type="submit" class="w-full mt-1 text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3.5">Masuk</button>
    </form>

    <p class="text-xs text-gray-500 text-center mt-5">Belum punya akun? <a href="{{ route('customer.auth.register') }}" class="text-emerald-700 font-semibold">Daftar</a></p>
</main>
@endsection
