@props([
    'module',
    'rolesName',
])

@can($rolesName.'.detail')
    <a :href="row.detail_url" x-cloak x-show="row.detail_url"
    class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-brand-500 transition bg-brand-50 hover:bg-brand-200 hover:text-brand-600 dark:bg-brand-500/15 dark:text-gray-400 dark:hover:bg-brand-500/30 dark:hover:text-brand-400"
    title="Lihat Data">
        @svg('heroicon-o-magnifying-glass-plus', 'h-5 w-5')
    </a>
@endcan

@can($rolesName.'.edit')
    <a :href="row.edit_url" x-cloak x-show="row.edit_url"
       class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-warning-500 transition bg-warning-50 hover:bg-warning-200 hover:text-warning-600  dark:bg-warning-500/15 dark:text-gray-400 dark:hover:bg-warning-500/30 dark:hover:text-warning-400"
       title="Ubah Data">
        @svg('heroicon-o-pencil-square', 'h-5 w-5')
    </a>
@endcan

@can($rolesName.'.delete')
    <button type="button" x-cloak x-show="row.delete_url"
            class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-error-500 transition bg-error-50 hover:bg-error-200 hover:text-error-600 dark:bg-error-500/15 dark:text-gray-400 dark:hover:bg-error-500/30 dark:hover:text-error-400"
            @click.prevent="$confirm({
                title: 'Peringatan !',
                message: 'Apakah anda yakin akan menghapus data ' + (row.name_plain ?? row.name ?? 'ini') + ' ?',
                confirmText: 'Ya, Hapus',
                variant: 'danger'
            }).then(ok => { if (ok) document.getElementById('dt-del-' + row.id).submit(); });"
            title="Hapus Data">
        @svg('heroicon-o-trash', 'h-4 w-4')
    </button>

    <form :id="'dt-del-' + row.id" :action="row.delete_url" method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>
@endcan