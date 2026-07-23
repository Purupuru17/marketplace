<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-600 bg-gray-50 dark:bg-gray-950 dark:text-gray-400" x-data x-init="$store.theme.init()">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="flex w-full max-w-5xl rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <!-- Brand Section -->
            <div class="hidden w-1/2 lg:flex flex-col items-center justify-center p-12 border-r border-gray-200 dark:border-gray-700">
                <a href="{{ route('login') }}" class="mb-6 inline-block">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-primary text-2xl font-bold text-white">
                            {{ substr(config('app.name'), 0, 1) }}
                        </div>
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ config('app.name') }}</span>
                    </div>
                </a>
                <p class="text-center text-gray-500 dark:text-gray-400 max-w-sm">
                    RBAC, menu dinamis, role switching, dan komponen admin reusable dalam satu package Laravel.
                </p>
            </div>

            <!-- Form Section -->
            <div class="w-full lg:w-1/2 p-8 sm:p-12">
                <p class="mb-1 text-sm font-medium">Mulai Kelola Sistem</p>
                <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">
                    Sign In ke {{ config('app.name') }}
                </h2>

                @if($errors->any())
                    <x-idcore::alert variant="error" class="mb-5">{{ $errors->first() }}</x-idcore::alert>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <x-idcore::input name="email" type="email" label="Email" required :value="old('email')" placeholder="nama@email.com" autofocus />
                    <x-idcore::input name="password" type="password" label="Password" required placeholder="Masukkan password" />

                    <div class="flex items-center justify-between">
                        <x-idcore::checkbox name="remember" label="Remember me" />
                        <a href="#" class="text-sm font-medium text-primary hover:underline">Forgot password?</a>
                    </div>

                    <x-idcore::button type="submit" variant="primary" block>Sign In</x-idcore::button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
