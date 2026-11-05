@extends('layouts.app')

@section('title', $opname->opname_no)
@section('page-title', 'Detail Stock Opname')
@section('page-subtitle', $opname->opname_no . ' · ' . $opname->opname_date?->format('d/m/Y'))

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">{{ $opname->opname_no }}</h2>
            <p class="kk-page-sub">{{ $opname->opname_date?->format('d/m/Y') }} · dibuat oleh {{ $opname->createdBy?->name }}</p>
        </div>
        <a href="{{ route('inventory.opname.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Hasil Hitung Fisik</h3>
                        <p class="kk-card-sub">Selisih sudah disesuaikan ke stok sistem</p>
                    </div>
                    <span class="badge-soft badge-soft-{{ $opname->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($opname->status) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Bahan</th>
                                <th class="text-end">Sistem</th>
                                <th class="text-end">Fisik</th>
                                <th class="text-end">Selisih</th>
                                <th>Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($opname->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $item->material?->name }}</div>
                                        <div class="text-muted-2" style="font-size:.66rem;">{{ $item->material?->unit?->code }}</div>
                                    </td>
                                    <td class="text-end small">{{ number_format($item->system_qty, 2, ',', '.') }}</td>
                                    <td class="text-end small">{{ number_format($item->physical_qty, 2, ',', '.') }}</td>
                                    <td class="text-end fw-bold {{ $item->difference_qty > 0 ? 'text-success' : ($item->difference_qty < 0 ? 'text-danger' : 'text-muted-2') }}">
                                        {{ $item->difference_qty > 0 ? '+' : '' }}{{ number_format($item->difference_qty, 2, ',', '.') }}
                                    </td>
                                    <td class="small text-muted-2">{{ $item->reason ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><x-empty-state icon="bi-clipboard-check" title="Tidak ada item" text="Opname ini tidak memiliki item." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="kk-card mb-4">
                <div class="kk-card-header"><h3 class="kk-card-title">Informasi</h3></div>
                <div class="kk-card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Tanggal</span>
                        <span class="fw-semibold small">{{ $opname->opname_date?->format('d/m/Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Jumlah Item</span>
                        <span class="fw-semibold small">{{ $opname->items->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted-2 small">Dibuat Oleh</span>
                        <span class="fw-semibold small">{{ $opname->createdBy?->name }}</span>
                    </div>
                    @if($opname->notes)
                        <div class="kk-divider"></div>
                        <div class="small text-muted-2">{{ $opname->notes }}</div>
                    @endif
                </div>
            </div>

            <div class="kk-card">
                <div class="kk-card-header"><h3 class="kk-card-title">Mutasi Terkait</h3></div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            @forelse($opname->stockMovements as $movement)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $movement->material?->name }}</div>
                                        <div class="text-muted-2" style="font-size:.66rem;">{{ $movement->type_label }}</div>
                                    </td>
                                    <td class="text-end small {{ $movement->isIncoming() ? 'text-success' : 'text-danger' }}">{{ $movement->isIncoming() ? '+' : '−' }}{{ number_format($movement->quantity, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td class="text-muted-2 small">Tidak ada mutasi (selisih 0).</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
