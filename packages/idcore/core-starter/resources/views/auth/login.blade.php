<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data x-init="$store.theme.init()">
    <div class="relative bg-white p-6 dark:bg-gray-900 sm:p-0">
        <div class="relative flex h-screen w-full flex-col justify-center dark:bg-gray-900 lg:flex-row">
            <!-- Form -->
            <div class="flex w-full flex-1 flex-col">
                <div class="mx-auto w-full max-w-md pt-10">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        @svg('heroicon-o-arrow-left', 'h-4 w-4')
                        Back to dashboard
                    </a>
                </div>
                <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                    <div class="mb-5 sm:mb-8">
                        <h1 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90 sm:text-3xl">Sign In</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Enter your email and password to sign in!</p>
                    </div>

                    @if($errors->any())
                        <div class="mb-5 rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-500">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="space-y-5">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email <span class="text-error-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            <div x-data="{ showPassword: false }">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Password <span class="text-error-500">*</span></label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" name="password" required placeholder="Enter your password"
                                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <span @click="showPassword = !showPassword" class="absolute right-4 top-1/2 z-30 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                                        @svg('heroicon-o-eye', 'h-5 w-5', ['x-show' => '!showPassword'])
                                        @svg('heroicon-o-eye-slash', 'h-5 w-5', ['x-show' => 'showPassword'])
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <label x-data="{ checked: false }" class="flex cursor-pointer select-none items-center text-sm font-normal text-gray-700 dark:text-gray-400">
                                    <input type="checkbox" name="remember" value="1" class="sr-only" @change="checked = !checked">
                                    <span class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition" :class="checked ? 'border-brand-500 bg-brand-500' : 'border-gray-300 bg-transparent dark:border-gray-700'">
                                        <svg :class="checked ? 'opacity-100' : 'opacity-0'" width="14" height="14" viewBox="0 0 14 14" fill="none" class="transition" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    Keep me logged in
                                </label>
                                <a href="#" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">Forgot password?</a>
                            </div>

                            <button type="submit" class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                                Sign In
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Brand panel -->
            <div class="relative hidden h-full w-full items-center bg-brand-950 dark:bg-white/5 lg:grid lg:w-1/2">
                <div class="flex items-center justify-center">
                    <div class="flex flex-col items-center max-w-xs">
                        <a href="{{ route('dashboard') }}" class="mb-4 block">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-2xl font-bold text-white">
                                {{ substr(config('app.name'), 0, 1) }}
                            </div>
                        </a>
                        <p class="text-center text-base font-semibold text-white">{{ config('app.name') }}</p>
                        <p class="mt-2 text-center text-sm text-gray-400 dark:text-white/60">RBAC, menu dinamis, dan komponen admin reusable dalam satu package Laravel.</p>
                    </div>
                </div>
            </div>

            <!-- Dark toggler -->
            <div class="fixed bottom-6 right-6 z-50 hidden sm:block">
                <button type="button" @click="$store.theme.toggle()"
                        class="inline-flex size-14 items-center justify-center rounded-full bg-brand-500 text-white shadow-theme-lg transition-colors hover:bg-brand-600">
                    <span x-show="!$store.theme.dark">@svg('heroicon-o-moon', 'h-5 w-5')</span>
                    <span x-show="$store.theme.dark" x-cloak>@svg('heroicon-o-sun', 'h-5 w-5')</span>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
