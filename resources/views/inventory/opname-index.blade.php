@extends('layouts.app')

@section('title', 'Stock Opname')
@section('page-title', 'Stock Opname')
@section('page-subtitle', 'Penyesuaian stok fisik dengan catatan sistem')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-clipboard-check me-2 text-brown"></i>Stock Opname</h2>
            <p class="kk-page-sub">Dokumentasikan selisih stok fisik dan sistem.</p>
        </div>
        <a href="{{ route('inventory.opname.create') }}" class="btn btn-kk"><i class="bi bi-plus-lg me-1"></i> Opname Baru</a>
    </div>

    <div class="kk-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>No. Opname</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th class="text-end">Jumlah Bahan</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opnames as $opname)
                        <tr>
                            <td><a href="{{ route('inventory.opname.show', $opname) }}" class="fw-bold text-brown">{{ $opname->opname_no }}</a></td>
                            <td class="small">{{ $opname->opname_date?->format('d/m/Y') }}</td>
                            <td><span class="badge-soft badge-soft-{{ $opname->status === 'completed' || $opname->status === 'approved' ? 'success' : 'warning' }}">{{ ucfirst($opname->status) }}</span></td>
                            <td class="text-end fw-semibold">{{ $opname->items_count }}</td>
                            <td class="small">{{ $opname->createdBy?->name ?? '-' }}</td>
                            <td class="text-end"><a href="{{ route('inventory.opname.show', $opname) }}" class="btn btn-sm btn-outline-kk btn-icon"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="bi-clipboard-check" title="Belum ada opname" text="Buat opname untuk menyesuaikan stok fisik." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($opnames->hasPages())
            <div class="p-3 border-top" style="border-color:#f0e8dd !important;">{{ $opnames->links() }}</div>
        @endif
    </div>
@endsection
