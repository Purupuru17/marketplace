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
    <form id="menuForm" action="{{ $action }}" method="POST" class="space-y-6"
          x-data="{ originalActions: @js($menu->actions ?? []), formDirty: false }"
          @input.debounce.500ms="formDirty = true"
          @change="formDirty = true"
          @submit="formDirty = false"
          x-init="$watch('formDirty', val => { window.onbeforeunload = val ? () => true : null; })"
          @submit.prevent="
            const current = Array.from(document.querySelectorAll('#menuForm input[name=&quot;actions[]&quot;]:checked')).map(item => item.value);
            const removed = originalActions.filter(action => !current.includes(action));
            if (removed.length > 0) {
                $confirm({
                    title: 'Hapus Permission?',
                    message: 'Action berikut akan dihapus permission-nya: ' + removed.join(', ') + '.',
                    confirmText: 'Ya, Lanjutkan',
                    variant: 'danger'
                }).then(ok => { if (ok) document.getElementById('menuForm').submit(); });
            } else {
                document.getElementById('menuForm').submit();
            }
          ">
        @csrf
        @if($menu->exists) @method('PUT') @endif

        <div>
            <h4 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Informasi Menu</h4>
            <div class="grid gap-5 md:grid-cols-2">
                <x-idcore::input name="name" label="Nama Menu" required :value="$menu->name" placeholder="Contoh: User" />
                <x-idcore::input name="url" label="URL" :value="$menu->url" hint="Isi # atau kosongkan jika hanya kategori/grup." placeholder="sistem/user" />
                <x-idcore::input name="icon" label="Icon (Heroicons)" :value="$menu->icon" hint="Contoh: heroicon-o-users" placeholder="heroicon-o-users" />
                <x-idcore::input name="sort_by" type="number" label="Urutan" :value="$menu->sort_by ?? 0" />
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <x-idcore::select name="parent_id" label="Parent Menu" :options="collect($parentTree)->pluck('label', 'id')" :selected="$menu->parent_id" placeholder="Tidak ada parent" />
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                <x-idcore::toggle name="is_active" label="Menu aktif" :checked="$menu->is_active ?? true" />
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Menu nonaktif tidak tampil di sidebar walaupun permission tersedia.</p>
            </div>
        </div>

        <div>
            <div class="mb-3">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white">Actions / Permission</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Centang action yang perlu dibuat sebagai permission untuk menu link ini.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach(config('idcore.menu_actions') as $key => $label)
                    <div class="rounded-xl border border-gray-200/80 bg-white p-4 transition hover:border-brand-200 hover:bg-brand-50/40 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-brand-500/10">
                        <x-idcore::checkbox name="actions[]" :value="$key" :label="$label" :checked="in_array($key, $menu->actions ?? [])" />
                    </div>
                @endforeach
            </div>
            @error('actions')<p class="mt-2 text-xs text-error-600 dark:text-error-500">{{ $message }}</p>@enderror
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
            <x-idcore::button type="submit" variant="{{ $menu->exists ? 'warning' : 'success' }}">
                @svg('heroicon-o-paper-airplane', 'h-4 w-4') Simpan
            </x-idcore::button>
        </div>
    </form>
</x-idcore::card>
@endsection
