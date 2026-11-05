@extends('layouts.app')

@section('title', 'Stok & Mutasi')
@section('page-title', 'Stok & Mutasi')
@section('page-subtitle', 'Ledger pergerakan bahan dan pembelian')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-box-seam me-2 text-brown"></i>Stok & Mutasi</h2>
            <p class="kk-page-sub">Semua perubahan stok tercatat sebagai ledger yang bisa diaudit.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('inventory.opname.create') }}" class="btn btn-outline-kk"><i class="bi bi-clipboard-check me-1"></i> Opname</a>
            <a href="{{ route('inventory.stock-out.create') }}" class="btn btn-outline-kk"><i class="bi bi-box-arrow-up me-1"></i> Stok Keluar</a>
            <a href="{{ route('inventory.stock-in.create') }}" class="btn btn-kk"><i class="bi bi-box-arrow-in-down me-1"></i> Stok Masuk</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-card label="Total Bahan" :value="$stats['materials']" icon="bi-layers" color="brown" hint="Bahan aktif & nonaktif" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Stok Menipis" :value="$stats['low_stock']" icon="bi-exclamation-diamond" color="red" hint="Perlu pembelian" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Nilai Stok" value="Rp {{ number_format($stats['stock_value'], 0, ',', '.') }}" icon="bi-cash-stack" color="green" hint="Stok × harga beli" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Mutasi Bulan Ini" :value="$stats['movements_month']" icon="bi-arrow-left-right" color="blue" hint="Semua tipe mutasi" />
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Riwayat Mutasi</h3>
                        <p class="kk-card-sub">Pembelian, produksi, opname, dan koreksi</p>
                    </div>
                </div>
                <div class="kk-card-body py-3 border-bottom" style="border-color:#f0e8dd !important;">
                    <form class="d-flex gap-2 flex-wrap" method="GET">
                        <select name="material" class="form-select" style="width:auto;">
                            <option value="">Semua bahan</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}" @selected(request('material') == $material->id)>{{ $material->name }}</option>
                            @endforeach
                        </select>
                        <select name="type" class="form-select" style="width:auto;">
                            <option value="">Semua tipe</option>
                            <option value="purchase_in" @selected(request('type') === 'purchase_in')>Pembelian</option>
                            <option value="production_out" @selected(request('type') === 'production_out')>Produksi</option>
                            <option value="opname_in" @selected(request('type') === 'opname_in')>Opname Masuk</option>
                            <option value="opname_out" @selected(request('type') === 'opname_out')>Opname Keluar</option>
                            <option value="adjustment_out" @selected(request('type') === 'adjustment_out')>Koreksi Keluar</option>
                            <option value="damage_out" @selected(request('type') === 'damage_out')>Rusak</option>
                        </select>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control" style="width:auto;">
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control" style="width:auto;">
                        <button class="btn btn-outline-kk" type="submit">Filter</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Bahan</th>
                                <th>Tipe</th>
                                <th class="text-end">Qty</th>
                                <th>Referensi</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                <tr>
                                    <td class="small">{{ $movement->movement_date->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('materials.show', $movement->material) }}" class="fw-semibold text-brown">{{ $movement->material?->name }}</a>
                                        <div class="text-muted-2" style="font-size:.66rem;">{{ $movement->material?->code }}</div>
                                    </td>
                                    <td><span class="badge-soft badge-soft-{{ $movement->isIncoming() ? 'success' : 'danger' }}">{{ $movement->type_label }}</span></td>
                                    <td class="text-end fw-bold {{ $movement->isIncoming() ? 'text-success' : 'text-danger' }}">{{ $movement->isIncoming() ? '+' : '−' }}{{ number_format($movement->quantity, 2, ',', '.') }}</td>
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
                                <tr><td colspan="6"><x-empty-state icon="bi-arrow-left-right" title="Belum ada mutasi" text="Stok masuk dan keluar akan tercatat di sini." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($movements->hasPages())
                    <div class="p-3 border-top" style="border-color:#f0e8dd !important;">{{ $movements->links() }}</div>
                @endif
            </div>
        </div>

        <div class="col-xl-4">
            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Pembelian Terbaru</h3>
                        <p class="kk-card-sub">Stok masuk dari supplier</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            @forelse($purchases as $purchase)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $purchase->purchase_no }}</div>
                                        <div class="text-muted-2" style="font-size:.68rem;">{{ $purchase->supplier?->name }} · {{ $purchase->purchase_date?->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold small">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</div>
                                        <div class="text-muted-2" style="font-size:.66rem;">{{ $purchase->createdBy?->name }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td><x-empty-state icon="bi-truck" title="Belum ada pembelian" text="Catat stok masuk untuk melihat riwayat pembelian." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
