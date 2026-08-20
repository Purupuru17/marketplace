<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-200 font-jakarta" x-data x-init="$store.theme.init()">

<div class="max-w-[420px] mx-auto min-h-screen bg-gray-50 shadow-2xl relative pb-24">

    @if(session('success'))
        <div x-init="$store.toast.success(@js(session('success')))"></div>
    @endif
    @if(session('error'))
        <div x-init="$store.toast.error(@js(session('error')))"></div>
    @endif

    @yield('content')
</div>

@php
    $cartCount = auth('customer')->check()
        ? app(\App\Services\Customer\CartService::class)->count(auth('customer')->user())
        : 0;
@endphp

@if(! request()->routeIs('customer.auth.*'))
<nav class="fixed bottom-0 inset-x-0 max-w-[420px] mx-auto bg-white border-t border-gray-100 flex z-40">
    <a href="{{ route('storefront.index') }}"
       class="flex-1 flex flex-col items-center gap-0.5 py-2.5 {{ request()->routeIs('storefront.index') ? 'text-emerald-700' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span class="text-[10px] {{ request()->routeIs('storefront.index') ? 'font-semibold' : 'font-medium' }}">Beranda</span>
    </a>
    <a href="{{ route('storefront.search') }}"
       class="flex-1 flex flex-col items-center gap-0.5 py-2.5 {{ request()->routeIs('storefront.search') ? 'text-emerald-700' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
        <span class="text-[10px] {{ request()->routeIs('storefront.search') ? 'font-semibold' : 'font-medium' }}">Kategori</span>
    </a>
    <a href="{{ route('customer.cart.index') }}"
       class="flex-1 flex flex-col items-center gap-0.5 py-2.5 {{ request()->routeIs('customer.cart.*') ? 'text-emerald-700' : 'text-gray-400' }}">
        <span class="relative">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            @if($cartCount > 0)
                <span class="absolute -top-1 -right-1.5 bg-emerald-600 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ $cartCount }}</span>
            @endif
        </span>
        <span class="text-[10px] {{ request()->routeIs('customer.cart.*') ? 'font-semibold' : 'font-medium' }}">Keranjang</span>
    </a>
    <a href="{{ route('customer.chat.index') }}"
       class="flex-1 flex flex-col items-center gap-0.5 py-2.5 {{ request()->routeIs('customer.chat.*') ? 'text-emerald-700' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <span class="text-[10px] {{ request()->routeIs('customer.chat.*') ? 'font-semibold' : 'font-medium' }}">Chat</span>
    </a>
    <a href="{{ auth('customer')->check() ? route('customer.account') : route('customer.auth.login') }}"
       class="flex-1 flex flex-col items-center gap-0.5 py-2.5 {{ request()->routeIs('customer.account') ? 'text-emerald-700' : 'text-gray-400' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        <span class="text-[10px] {{ request()->routeIs('customer.account') ? 'font-semibold' : 'font-medium' }}">Akun</span>
    </a>
</nav>
@endif

<x-idcore::toast />

@stack('scripts')
</body>
</html>