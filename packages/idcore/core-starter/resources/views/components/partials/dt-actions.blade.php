@props([
    'module',
    'rolesName',
])

@can($rolesName.'.edit')
    <a :href="row.edit_url" x-cloak x-show="row.edit_url"
       class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-warning-200 text-warning-700 transition hover:bg-warning-50 dark:border-warning-500/40 dark:text-warning-300 dark:hover:bg-warning-500/10"
       title="Ubah Data">
        @svg('heroicon-o-pencil-square', 'h-4 w-4')
    </a>
@endcan

@can($rolesName.'.delete')
    <button type="button" x-cloak x-show="row.delete_url"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-error-200 text-error-700 transition hover:bg-error-50 dark:border-error-500/40 dark:text-error-300 dark:hover:bg-error-500/10"
            @click.prevent="$confirm({
                title: 'Peringatan !',
                message: 'Apakah anda yakin akan menghapus data ' + (row.name_plain ?? row.name ?? 'ini') + ' ?',
                confirmText: 'Ya, Hapus',
                variant: 'danger'
            }).then(ok => { if (ok) document.getElementById('dt-del-' + row.id).submit(); });">
        @svg('heroicon-o-trash', 'h-4 w-4')
    </button>

    <form :id="'dt-del-' + row.id" :action="row.delete_url" method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>
@endcan