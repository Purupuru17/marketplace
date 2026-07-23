<?php

return [
    'route_prefix' => 'sistem',
    'menu_cache_ttl' => 60,
    'menu_actions' => [
        'index'  => 'Lihat',
        'create' => 'Tambah',
        'edit'   => 'Ubah',
        'delete' => 'Hapus',
        'detail' => 'Detail',
        'print'  => 'Cetak',
        'export' => 'Export',
        'import' => 'Import',
        'ajax'   => 'Ajax',
    ],
    'permission_map' => [
        'index'   => 'index',
        'show'    => 'detail',
        'create'  => 'create',
        'store'   => 'create',
        'edit'    => 'edit',
        'update'  => 'edit',
        'destroy' => 'delete',
        'print'   => 'print',
        'export'  => 'export',
        'import'  => 'import',
        'ajax'    => 'ajax',
    ],

];
