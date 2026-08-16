@include('errors.partials.page', [
    'code' => 403,
    'title' => 'Akses Ditolak',
    'message' => $exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.',
])
