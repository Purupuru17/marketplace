@extends('idcore::layouts.backend')
@section('title', $user ? 'Edit User' : 'Tambah User')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user ? 'Edit User' : 'Tambah User' }}</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'User', 'url' => route('sistem.user.index')], ['label' => $user ? 'Edit' : 'Create']]" />
    </div>
</div>

<x-idcore::card title="{{ $user ? 'Edit User' : 'Tambah User' }}" subtitle="Atur identitas, password, role, dan default role user." class="max-w-4xl">
    <form action="{{ $user ? route('sistem.user.update', $user->id) : route('sistem.user.store') }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($user) @method('PUT') @endif

        <div class="grid gap-5 md:grid-cols-2">
            <x-idcore::input name="name" label="Nama Lengkap" required :value="$user->name ?? null" placeholder="Contoh: Budi Santoso" />
            <x-idcore::input name="email" type="email" label="Email" required :value="$user->email ?? null" placeholder="nama@email.com" />
        </div>

        <x-idcore::input name="password" type="password" label="Password" :required="!$user" :hint="$user ? 'Kosongkan jika tidak ingin mengubah password.' : 'Minimal 8 karakter.'" />

        <div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pilih satu atau lebih role, lalu tentukan role default saat login.</p>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach($roles as $role)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 transition hover:border-brand-200 hover:bg-brand-50/30 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-brand-500/10">
                        <div class="flex items-start justify-between gap-4">
                            <x-idcore::checkbox name="roles[]" :value="$role->name" :label="$role->name" :checked="$user?->hasRole($role->name)" />
                            <x-idcore::radio name="default_role" :value="$role->name" label="Default" :selected="$user?->default_role_id === $role->id ? $role->name : null" />
                        </div>
                    </div>
                @endforeach
            </div>
            @error('roles')<p class="mt-2 text-xs text-danger-600 dark:text-danger-500">{{ $message }}</p>@enderror
            @error('default_role')<p class="mt-2 text-xs text-danger-600 dark:text-danger-500">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Perubahan Belum Disimpan',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route('sistem.user.index') }}'; });
                } else {
                    window.location.href = '{{ route('sistem.user.index') }}';
                }
            ">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="primary">Simpan</x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection
