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
    <div class="grid gap-4 md:grid-cols-3 mb-6">
        <x-idcore::card>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $formData->name }}</p>
        </x-idcore::card>
        <x-idcore::card>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Guard</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $formData->guard_name }}</p>
        </x-idcore::card>
        <x-idcore::card>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Permission</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $formData->permissions->count() }} dipilih</p>
        </x-idcore::card>
    </div>

    <form action="{{ $action }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <x-idcore::tabs default="{{ $permissionsGrouped->keys()->first() }}">
            <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-5 dark:border-gray-800">
                @foreach($permissionsGrouped as $itemModule => $permissions)
                    @php
                        $moduleLabel = \Illuminate\Support\Str::headline(str_replace('-', ' ', $itemModule));
                    @endphp
                    <x-idcore::tab-button value="{{ $itemModule }}">{{ $moduleLabel }}</x-idcore::tab-button>
                @endforeach
            </div>

            @foreach($permissionsGrouped as $itemModule => $permissions)
                @php
                    $moduleLabel = \Illuminate\Support\Str::headline(str_replace('-', ' ', $itemModule));
                @endphp
                <x-idcore::tab-panel value="{{ $itemModule }}">
                    <x-idcore::card :padding="false"
                         x-data="{ allSelected: false }"
                         data-module-group>
                        <div class="flex flex-col gap-4 border-b border-gray-100 px-4 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $moduleLabel }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $permissions->count() }} permission tersedia</p>
                            </div>
                            <x-idcore::button variant="outline-warning" size="sm"
                                    @click="
                                        allSelected = !allSelected;
                                        $el.closest('[data-module-group]').querySelectorAll('input[type=checkbox]').forEach(cb => {
                                            cb.checked = allSelected;
                                            cb.dispatchEvent(new Event('change', { bubbles: true }));
                                        });
                                        $el.textContent = allSelected ? 'Batalkan Semua' : 'Pilih Semua';
                                    ">
                                Pilih Semua
                            </x-idcore::button>
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
                    </x-idcore::card>
                </x-idcore::tab-panel>
            @endforeach
        </x-idcore::tabs>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:justify-end">
            <x-idcore::button variant="light" :href="route($module.'.index')">
                @svg('heroicon-o-arrow-path', 'h-4 w-4') Batal
            </x-idcore::button>
            <x-idcore::button type="submit" variant="{{ $formData ? 'warning' : 'success' }}">
                @svg('heroicon-o-paper-airplane', 'h-4 w-4') Simpan
            </x-idcore::button>
        </div>
    </form>
</x-idcore::card>

@endsection
