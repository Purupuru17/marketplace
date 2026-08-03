@props(['module' => '#', 'id' => '', 'name' => ''])

<x-idcore::button variant="outline-danger" size="xs" circle tooltip="Hapus Data"
    x-data
    @click.prevent="
        $confirm({
            title: 'Peringatan !',
            message: 'Apakah anda yakin akan menghapus data {{ $name }} ?',
            confirmText: 'Ya, Hapus',
            variant: 'danger'
        }).then(ok => { if (ok) $refs['deleteForm' + '{{ $id }}'].submit(); });
    ">
    @svg('heroicon-o-trash', 'h-4 w-4')
</x-idcore::button>
<form x-ref="deleteForm{{ $id }}" action="{{ route($module.'.destroy', $id) }}" method="POST" class="hidden">
    @csrf @method('DELETE')
</form>