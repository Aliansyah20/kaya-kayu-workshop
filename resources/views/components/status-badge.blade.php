@props(['status', 'type' => 'order'])

@php
    $maps = [
        'order' => [
            'labels' => [
                'draft' => 'Draft',
                'pending_approval' => 'Menunggu Approval',
                'confirmed' => 'Dikonfirmasi',
                'queued' => 'Antrian',
                'in_production' => 'Dikerjakan',
                'finishing' => 'Finishing',
                'production_done' => 'Selesai Produksi',
                'delivered' => 'Diambil/Dikirim',
                'transferred' => 'Dialihkan',
                'cancelled' => 'Dibatalkan',
            ],
            'classes' => [
                'draft' => 'secondary',
                'pending_approval' => 'warning',
                'confirmed' => 'info',
                'queued' => 'secondary',
                'in_production' => 'primary',
                'finishing' => 'warning',
                'production_done' => 'success',
                'delivered' => 'dark',
                'transferred' => 'dark',
                'cancelled' => 'danger',
            ],
        ],
        'payment' => [
            'labels' => [
                'unpaid' => 'Belum Bayar',
                'dp_paid' => 'DP Terbayar',
                'partially_paid' => 'Dibayar Sebagian',
                'paid' => 'Lunas',
                'overdue' => 'Jatuh Tempo',
            ],
            'classes' => [
                'unpaid' => 'secondary',
                'dp_paid' => 'info',
                'partially_paid' => 'warning',
                'paid' => 'success',
                'overdue' => 'danger',
            ],
        ],
        'item' => [
            'labels' => [
                'pending' => 'Menunggu',
                'queued' => 'Antrian',
                'in_progress' => 'Dikerjakan',
                'finishing' => 'Finishing',
                'qc' => 'Quality Control',
                'done' => 'Selesai',
                'transferred' => 'Dialihkan',
                'cancelled' => 'Dibatalkan',
            ],
            'classes' => [
                'pending' => 'secondary',
                'queued' => 'secondary',
                'in_progress' => 'primary',
                'finishing' => 'warning',
                'qc' => 'info',
                'done' => 'success',
                'transferred' => 'dark',
                'cancelled' => 'danger',
            ],
        ],
    ];

    $map = $maps[$type] ?? $maps['order'];
    $label = $map['labels'][$status] ?? ucfirst(str_replace('_', ' ', $status));
    $class = $map['classes'][$status] ?? 'secondary';
@endphp

<span class="badge-soft badge-soft-{{ $class }}">{{ $label }}</span>
