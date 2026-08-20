@extends('customer.layouts.app')
@section('title', 'Daftar')

@section('content')
<main class="px-4 pt-8">
    <div class="text-center">
        <span class="text-xl font-extrabold text-emerald-800">{{ config('app.name') }}</span>
        <h1 class="mt-4 text-lg font-bold text-gray-900">Daftar Akun</h1>
        <p class="mt-1 text-xs text-gray-500">Buat akun untuk mulai berbelanja.</p>
    </div>

    <form method="POST" action="{{ route('customer.auth.register.store') }}" class="mt-6 bg-white rounded-xl border border-gray-100 p-4 space-y-4">
        @csrf
        <div>
            <label for="name" class="block text-xs font-medium text-gray-700">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-xs font-medium text-gray-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="block text-xs font-medium text-gray-700">No. HP <span class="text-gray-400">(opsional)</span></label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-gray-700">Kata Sandi</label>
            <input id="password" type="password" name="password" required
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-medium text-gray-700">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="mt-1 w-full rounded-lg border-gray-200 bg-white px-3 py-2.5 text-sm focus:ring-0 focus:border-emerald-600">
        </div>

        <button type="submit" class="w-full text-sm font-semibold text-white bg-emerald-700 rounded-lg py-3">Daftar</button>
    </form>

    <p class="mt-4 text-center text-xs text-gray-500">
        Sudah punya akun?
        <a href="{{ route('customer.auth.login') }}" class="font-semibold text-emerald-700">Masuk</a>
    </p>
</main>
@endsection