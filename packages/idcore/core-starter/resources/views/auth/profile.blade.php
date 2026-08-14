@extends('idcore::layouts.backend')
@section('title', $title)

@section('content')
<x-idcore::page-header :title="$title" :subtitle="$subtitle" :breadcrumb="$breadcrumb" />

<div class="space-y-6">
    {{-- ===== HEADER PROFILE ===== --}}
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="h-28 bg-gradient-to-r from-brand-500 to-blue-light-500 dark:from-brand-600 dark:to-blue-light-600"></div>
        <div class="px-6 pb-6">
            <div class="-mt-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl border-4 border-white bg-brand-50 text-3xl font-bold text-brand-600 shadow-theme-sm dark:border-gray-900 dark:bg-gray-800 dark:text-brand-400">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="pb-1">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                        @if($activeRole)
                            <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold tracking-wide text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                @svg('heroicon-o-shield-check', 'h-3.5 w-3.5') {{ $activeRole->name }}
                            </span>
                        @endif
                    </div>
                </div>
                <a href="#personal-info" class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">
                    @svg('heroicon-o-pencil-square', 'h-4 w-4') Edit
                </a>
            </div>
        </div>
    </div>

    {{-- ===== INFO RINGKAS ===== --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    @svg('heroicon-o-identification', 'h-5 w-5')
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">ID Akun</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->id }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                    @svg('heroicon-o-check-badge', 'h-5 w-5')
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</p>
                    <x-idcore::status-badge :status="$user->status ?? 'active'" />
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                    @svg('heroicon-o-calendar', 'h-5 w-5')
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Bergabung</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->created_at?->format('d M Y') ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PERSONAL INFORMATION ===== --}}
    <div id="personal-info">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf @method('PUT')
            <x-idcore::form-section title="Informasi Pribadi" subtitle="Perbarui nama dan email akun Anda." :columns="2">
                <x-idcore::field name="name" label="Nama Lengkap" required>
                    <x-idcore::input name="name" type="text" :value="$user->name" required placeholder="Nama lengkap Anda" />
                </x-idcore::field>

                <x-idcore::field name="email" label="Email" required>
                    <x-idcore::input name="email" type="email" :value="$user->email" required placeholder="nama@email.com" />
                </x-idcore::field>

                <x-slot:footer>
                    <div class="flex items-center justify-end gap-2">
                        <x-idcore::button variant="outline" type="reset">Reset</x-idcore::button>
                        <x-idcore::button type="submit">
                            @svg('heroicon-o-check-circle', 'h-4 w-4') Simpan Perubahan
                        </x-idcore::button>
                    </div>
                </x-slot:footer>
            </x-idcore::form-section>
        </form>
    </div>

    {{-- ===== SECURITY ===== --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Security</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola kata sandi dan sesi akun Anda.</p>
        </div>

        <div class="grid gap-6 p-6 lg:grid-cols-2">
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf @method('PUT')
                <x-idcore::form-section title="Change Password" subtitle="Gunakan minimal 8 karakter." :columns="1">
                    <x-idcore::field name="current_password" label="Password Saat Ini" required>
                        <x-idcore::input name="current_password" type="password" required placeholder="Masukkan password saat ini" />
                    </x-idcore::field>
                    <x-idcore::field name="password" label="Password Baru" required>
                        <x-idcore::input name="password" type="password" required placeholder="Minimal 8 karakter" />
                    </x-idcore::field>
                    <x-idcore::field name="password_confirmation" label="Konfirmasi Password" required>
                        <x-idcore::input name="password_confirmation" type="password" required placeholder="Ulangi password baru" />
                    </x-idcore::field>
                    <x-slot:footer>
                        <div class="flex items-center justify-end gap-2">
                            <x-idcore::button type="submit" variant="warning">
                                @svg('heroicon-o-key', 'h-4 w-4') Ubah Password
                            </x-idcore::button>
                        </div>
                    </x-slot:footer>
                </x-idcore::form-section>
            </form>

            <div class="rounded-2xl border border-gray-200 p-6 dark:border-gray-800">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400">
                            @svg('heroicon-o-exclamation-triangle', 'h-5 w-5')
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white">Danger Zone</h4>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tindakan di bawah ini tidak dapat dibatalkan.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    <form action="{{ route('profile.logout-all') }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">Logout semua device</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Keluar dari semua perangkat lain.</p>
                            </div>
                            <x-idcore::button variant="outline" size="sm" type="submit">
                                @svg('heroicon-o-arrow-right-on-rectangle', 'h-4 w-4') Logout All
                            </x-idcore::button>
                        </div>
                    </form>

                    <form action="{{ route('profile.destroy') }}" method="POST">
                        @csrf @method('DELETE')
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-error-200 p-4 dark:border-error-500/30">
                            <div>
                                <p class="text-sm font-medium text-error-700 dark:text-error-400">Delete account</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Hapus akun dan semua datanya secara permanen.</p>
                            </div>
                            <x-idcore::button variant="danger" size="sm" type="button"
                                @click.prevent="$confirm({
                                    title: 'Hapus Akun?',
                                    message: 'Akun akan dihapus permanen dan tidak dapat dikembalikan.',
                                    confirmText: 'Ya, Hapus',
                                    variant: 'danger'
                                }).then(ok => { if (ok) $el.closest('form').submit(); });">
                                @svg('heroicon-o-trash', 'h-4 w-4') Delete account
                            </x-idcore::button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection