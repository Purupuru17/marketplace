@extends('customer.layouts.app')
@section('title', 'Masuk')

@section('content')
<main class="px-4 pt-8">
    <div class="text-center">
        <span class="text-xl font-extrabold text-emerald-800">{{ config('app.name') }}</span>
        <h1 class="mt-4 text-lg font-bold text-gray-900">Masuk</h1>
        <p class="mt-1 text-xs text-gray-500">Selamat datang kembali, silakan masuk.</p>
    </div>

    <form method="POST" action="{{ route('customer.auth.login.store') }}" class="mt-6 bg-white rounded-xl border border-gray-100 p-4 space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-xs font-medium text-gray-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-gray-700">Kata Sandi</label>
            <input id="password" type="password" name="password" required
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center text-[13px] text-gray-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
            <span class="ml-2">Ingat saya</span>
        </label>

        <button type="submit" class="w-full text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3">Masuk</button>
    </form>

    <p class="mt-4 text-center text-xs text-gray-500">
        Belum punya akun?
        <a href="{{ route('customer.auth.register') }}" class="font-semibold text-emerald-700">Daftar</a>
    </p>
</main>
@endsection