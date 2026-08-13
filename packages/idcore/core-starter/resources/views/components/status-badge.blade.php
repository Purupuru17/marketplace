@props([
    'status' => null,
    'label' => null,
])

@php
    $status = strtolower((string) $status);
    $label = $label ?: $status;

    $variantMap = [
        'success'  => ['active', 'aktif', 'success', 'paid', 'completed', 'selesai', 'approved', 'verified', 'confirmed', 'open', 'published', 'publish', 'lunas', 'active1'],
        'danger'   => ['error', 'failed', 'gagal', 'rejected', 'cancelled', 'cancel', 'batal', 'expired', 'blocked', 'banned', 'void', 'inactive', 'nonaktif', 'closed', 'disabled'],
        'warning'  => ['pending', 'waiting', 'menunggu', 'processing', 'diproses', 'draft', 'unpaid', 'belum bayar', 'review', 'shipped', 'dikirim', 'on progress', 'partial'],
        'info'     => ['info', 'new', 'baru', 'processed', 'delivered', 'terkirim', 'sent'],
        'orange'   => ['refunded', 'refund', 'dikembalikan', 'retur'],
    ];

    $variant = 'gray';
    foreach ($variantMap as $tone => $values) {
        if (in_array($status, $values, true)) { $variant = $tone; break; }
    }
@endphp

<x-idcore::badge :variant="$variant">{{ $label }}</x-idcore::badge>