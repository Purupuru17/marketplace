@extends('idcore::layouts.backend')
@section('title', $title)

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        <x-idcore::breadcrumb :items="$breadcrumb" />
    </div>
</div>

<x-idcore::card title="{{ $subtitle }}" subtitle="{{ $title }}" class="max-w-4xl">
    <form action="{{ $action }}" method="POST" class="space-y-6"
          x-data="{ formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })">
        @csrf
        @if($formData) @method('PUT') @endif

        <div class="grid gap-5 md:grid-cols-2">
            <x-idcore::input name="name" label="Nama Lengkap" required :value="$formData->name ?? null" placeholder="Contoh: Budi Santoso" />
            <x-idcore::input name="email" type="email" label="Email" required :value="$formData->email ?? null" placeholder="nama@email.com" />
        </div>

        <x-idcore::input name="password" type="password" label="Password" :required="!$formData" :hint="$formData ? 'Kosongkan jika tidak ingin mengubah password.' : 'Minimal 8 karakter.'" />

        <div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pilih satu atau lebih role, lalu tentukan role default saat login.</p>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach($roles as $role)
                    <div class="rounded-xl border border-gray-200/80 bg-white p-4 transition hover:border-primary-200 hover:bg-primary-50/40 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-primary-500/10">
                        <div class="flex items-start justify-between gap-4">
                            <x-idcore::checkbox name="roles[]" :value="$role->name" :label="$role->name" :checked="$formData?->hasRole($role->name)" />
                            <x-idcore::radio name="default_role" :value="$role->name" label="Default" :selected="$formData?->default_role_id === $role->id ? $role->name : null" />
                        </div>
                    </div>
                @endforeach
            </div>
            @error('roles')<p class="mt-2 text-xs text-danger-600 dark:text-danger-500">{{ $message }}</p>@enderror
            @error('default_role')<p class="mt-2 text-xs text-danger-600 dark:text-danger-500">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="light" @click.prevent="
                if (formDirty) {
                    $confirm({
                        title: 'Konfirmasi',
                        message: 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini ?',
                        confirmText: 'Ya, Tinggalkan',
                        cancelText: 'Batal',
                        variant: 'warning'
                    }).then(ok => { if (ok) window.location.href = '{{ route($module.'.index') }}'; });
                } else {
                    window.location.href = '{{ route($module.'.index') }}';
                }
            ">@svg('heroicon-o-arrow-path', 'h-4 w-4') Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $formData ? 'warning' : 'success' }}">
                @svg('heroicon-o-paper-airplane', 'h-4 w-4') Simpan
            </x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection
