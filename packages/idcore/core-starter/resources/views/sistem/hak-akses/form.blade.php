@extends('idcore::layouts.backend')
@section('title', 'Atur Hak Akses - ' . $role->name)

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Atur Hak Akses</h1>
        <x-idcore::breadcrumb :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Hak Akses', 'url' => route('sistem.hak-akses.index')], ['label' => $role->name]]" />
    </div>
    <div class="flex items-center gap-2">
        <x-idcore::button variant="outline" :href="route('sistem.hak-akses.index')">Kembali</x-idcore::button>
    </div>
</div>

<x-idcore::card title="Hak Akses: {{ $role->name }}" subtitle="Pilih permission yang dapat diakses oleh role ini." class="max-w-5xl">
    <div class="grid gap-4 md:grid-cols-3 mb-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $role->name }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Guard</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $role->guard_name }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Permission</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $role->permissions->count() }} dipilih</p>
        </div>
    </div>

    <form action="{{ route('sistem.hak-akses.update', $role->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <x-idcore::tabs default="{{ $permissionsGrouped->keys()->first() }}">
            <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-5 dark:border-gray-800">
                @foreach($permissionsGrouped as $module => $permissions)
                    @php
                        $moduleLabel = \Illuminate\Support\Str::headline(str_replace('-', ' ', $module));
                    @endphp
                    <x-idcore::tab-button value="{{ $module }}">{{ $moduleLabel }}</x-idcore::tab-button>
                @endforeach
            </div>

            @foreach($permissionsGrouped as $module => $permissions)
                @php
                    $moduleLabel = \Illuminate\Support\Str::headline(str_replace('-', ' ', $module));
                @endphp
                <x-idcore::tab-panel value="{{ $module }}">
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950"
                         x-data="{ allSelected: false }"
                         data-module-group>
                        <div class="flex flex-col gap-4 border-b border-gray-100 px-4 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $moduleLabel }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $permissions->count() }} permission tersedia</p>
                            </div>
                            <button type="button"
                                    @click="
                                        allSelected = !allSelected;
                                        $el.closest('[data-module-group]').querySelectorAll('input[type=checkbox]').forEach(cb => {
                                            cb.checked = allSelected;
                                            cb.dispatchEvent(new Event('change', { bubbles: true }));
                                        });
                                        $el.textContent = allSelected ? 'Batalkan Semua' : 'Pilih Semua';
                                    "
                                    class="text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                Pilih Semua
                            </button>
                        </div>

                        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($permissions as $permission)
                                @php
                                    $label = \Illuminate\Support\Str::headline(explode('.', $permission->name)[1] ?? $permission->name);
                                @endphp
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                                    <x-idcore::checkbox
                                        name="permissions[]"
                                        :value="$permission->name"
                                        :label="$label"
                                        :checked="in_array($permission->name, $rolePermissions)"
                                    />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-idcore::tab-panel>
            @endforeach
        </x-idcore::tabs>

        <div class="flex flex-col gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="outline" :href="route('sistem.hak-akses.index')">Batal</x-idcore::button>
            <x-idcore::button type="submit" variant="primary">Simpan Akses</x-idcore::button>
        </div>
    </form>
</x-idcore::card>

@endsection
