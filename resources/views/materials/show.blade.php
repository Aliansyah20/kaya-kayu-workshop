@extends('layouts.app')

@section('title', $material->name)
@section('page-title', 'Detail Bahan')
@section('page-subtitle', $material->code . ' · ' . ($material->category?->name ?? 'Tanpa kategori'))

@section('content')
    <div class="kk-page-head">
        <div class="d-flex align-items-center gap-3">
            @if($material->photo_path)
                <img src="{{ Storage::url($material->photo_path) }}" alt="{{ $material->name }}" class="kk-avatar-lg" style="object-fit:cover;">
            @else
                <div class="kk-avatar-lg"><i class="bi bi-box-seam"></i></div>
            @endif
            <div>
                <h2 class="kk-page-title">{{ $material->name }}</h2>
                <p class="kk-page-sub"><code>{{ $material->code }}</code> · {{ $material->category?->name }} · {{ $material->unit?->name }}</p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('materials.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            <a href="{{ route('materials.edit', $material) }}" class="btn btn-outline-kk"><i class="bi bi-pencil me-1"></i> Edit</a>
            <a href="{{ route('inventory.stock-in.create', ['material_id' => $material->id]) }}" class="btn btn-kk"><i class="bi bi-box-arrow-in-down me-1"></i> Stok Masuk</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-card label="Stok Saat Ini" :value="number_format($material->stock_qty, 2, ',', '.') . ' ' . $material->unit?->code" icon="bi-box-seam" :color="$material->isLowStock() ? 'red' : 'green'" :hint="$material->isLowStock() ? 'Di bawah minimum' : 'Stok aman'" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Stok Minimum" :value="number_format($material->min_stock_qty, 2, ',', '.') . ' ' . $material->unit?->code" icon="bi-arrow-down-circle" color="amber" hint="Batas peringatan" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Nilai Stok" value="Rp {{ number_format($material->stock_qty * $material->purchase_price, 0, ',', '.') }}" icon="bi-cash-stack" color="blue" hint="Stok × harga beli" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Total Mutasi" :value="number_format($totalIn + $totalOut, 2, ',', '.')" icon="bi-arrow-left-right" color="brown" :hint="'Masuk ' . number_format($totalIn, 2, ',', '.') . ' · Keluar ' . number_format($totalOut, 2, ',', '.')" />
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="kk-card mb-4">
                <div class="kk-card-header"><h3 class="kk-card-title">Informasi Bahan</h3></div>
                <div class="kk-card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Tipe</span>
                        <span class="fw-semibold small">{{ ucfirst($material->material_type) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Dimensi</span>
                        <span class="fw-semibold small">{{ $material->dimension_label }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Jenis Kayu</span>
                        <span class="fw-semibold small">{{ $material->woodType?->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Kualitas</span>
                        <span class="fw-semibold small">{{ $material->woodGrade?->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Lokasi</span>
                        <span class="fw-semibold small">{{ $material->location ?: '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Harga Beli</span>
                        <span class="fw-semibold small">Rp {{ number_format($material->purchase_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted-2 small">Status</span>
                        <span class="badge-soft badge-soft-{{ $material->is_active ? 'success' : 'secondary' }}">{{ $material->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    @if($material->notes)
                        <div class="kk-divider"></div>
                        <div class="text-muted-2 small">{{ $material->notes }}</div>
                    @endif
                </div>
            </div>

            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Dipakai di Model</h3>
                        <p class="kk-card-sub">Komponen BOM yang memakai bahan ini</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            @forelse($material->bomItems()->with('productModel')->get() as $bom)
                                <tr>
                                    <td>
                                        <a href="{{ route('product-models.show', $bom->productModel) }}" class="fw-semibold text-brown">{{ $bom->productModel?->name }}</a>
                                        <div class="text-muted-2" style="font-size:.68rem;">{{ $bom->productModel?->code }}</div>
                                    </td>
                                    <td class="text-end small">{{ number_format($bom->quantity, 4, ',', '.') }} {{ $bom->unit?->code }}</td>
                                </tr>
                            @empty
                                <tr><td><x-empty-state icon="bi-layers" title="Belum dipakai" text="Bahan ini belum masuk BOM model produk." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Kartu Stok</h3>
                        <p class="kk-card-sub">Riwayat mutasi bahan ini</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.stock-in.create', ['material_id' => $material->id]) }}" class="btn btn-sm btn-outline-kk">Masuk</a>
                        <a href="{{ route('inventory.stock-out.create', ['material_id' => $material->id]) }}" class="btn btn-sm btn-outline-kk">Keluar</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th class="text-end">Qty</th>
                                <th>Referensi</th>
                                <th>Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                <tr>
                                    <td class="small">{{ $movement->movement_date->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge-soft badge-soft-{{ $movement->isIncoming() ? 'success' : 'danger' }}">{{ $movement->type_label }}</span>
                                    </td>
                                    <td class="text-end fw-bold {{ $movement->isIncoming() ? 'text-success' : 'text-danger' }}">
                                        {{ $movement->isIncoming() ? '+' : '−' }}{{ number_format($movement->quantity, 2, ',', '.') }}
                                    </td>
                                    <td class="small text-muted-2">
                                        @if($movement->order)
                                            <a href="{{ route('orders.show', $movement->order) }}">{{ $movement->order->order_no }}</a>
                                        @elseif($movement->purchase)
                                            {{ $movement->purchase->purchase_no }}
                                        @elseif($movement->stockOpname)
                                            {{ $movement->stockOpname->opname_no }}
                                        @else
                                            {{ $movement->reason ?: '-' }}
                                        @endif
                                    </td>
                                    <td class="small">{{ $movement->createdBy?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><x-empty-state icon="bi-arrow-left-right" title="Belum ada mutasi" text="Stok masuk atau keluar akan muncul di sini." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($movements->hasPages())
                    <div class="p-3 border-top" style="border-color:#f0e8dd !important;">{{ $movements->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
