@props(['module' => '#', 'id' => ''])

<x-idcore::button variant="outline-warning" size="xs" circle tooltip="Ubah Data" :href="route($module.'.edit', $id)">
    @svg('heroicon-o-pencil-square', 'h-4 w-4')
</x-idcore::button>
