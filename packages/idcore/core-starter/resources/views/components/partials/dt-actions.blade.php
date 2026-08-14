@props([
    'module',
    'rolesName',
])

@can($rolesName.'.edit')
    <a :href="row.edit_url" x-cloak x-show="row.edit_url"
       class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-brand-400"
       title="Ubah Data">
        @svg('heroicon-o-pencil-square', 'h-4 w-4')
    </a>
@endcan

@can($rolesName.'.delete')
    <button type="button" x-cloak x-show="row.delete_url"
            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-error-50 hover:text-error-600 dark:text-gray-400 dark:hover:bg-error-500/10 dark:hover:text-error-400"
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