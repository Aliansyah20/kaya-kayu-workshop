@extends('layouts.app')

@section('title', 'Detail Produk')
@section('page-title', 'Detail Produk Jadi')
@section('page-subtitle', 'Informasi produk, stok, dan riwayat pergerakan')

@section('content')
    <div class="kk-page-head">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('products.index') }}" class="text-muted-2 small"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
                <span class="text-muted-2">/</span>
                <span class="small text-muted-2">{{ $product->code }}</span>
            </div>
            <h2 class="kk-page-title">{{ $product->name }}</h2>
            <p class="kk-page-sub">{{ $product->category?->name }} · {{ $product->productModel?->name ?? 'Produk custom' }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-kk"><i class="bi bi-pencil me-1"></i> Edit</a>
            <a href="{{ route('products.index') }}" class="btn btn-kk"><i class="bi bi-grid me-1"></i> Daftar Produk</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="kk-card mb-4">
                <div class="kk-card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if($product->photo_path)
                            <img src="{{ Storage::url($product->photo_path) }}" class="kk-thumb" alt="{{ $product->name }}" style="width:80px;height:80px;">
                        @else
                            <div class="kk-thumb d-grid" style="width:80px;height:80px;display:grid;place-items:center;color:#b18a64;"><i class="bi bi-box2-heart fs-2"></i></div>
                        @endif
                        <div>
                            <div class="text-muted-2" style="font-size:.7rem;">Status stok</div>
                            <span class="badge-soft badge-soft-{{ $product->stock_qty <= 0 ? 'danger' : ($product->isLowStock() ? 'warning' : 'success') }}">
                                {{ $product->stock_qty <= 0 ? 'Habis' : ($product->isLowStock() ? 'Menipis' : 'Tersedia') }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 mb-3" style="background:#fbf6ee;">
                        <div class="text-muted-2" style="font-size:.68rem;">Stok tersedia</div>
                        <div class="brand-font fw-bold" style="font-size:1.8rem;color:#5b3a29;">{{ number_format($product->stock_qty, 2, ',', '.') }} <span class="h6 text-muted-2">{{ $product->unit?->code }}</span></div>
                        <div class="text-muted-2" style="font-size:.7rem;">Minimum: {{ number_format($product->min_stock_qty, 2, ',', '.') }} {{ $product->unit?->code }}</div>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f0e8dd !important;">
                        <span class="text-muted-2 small">Kode</span>
                        <code class="small">{{ $product->code }}</code>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f0e8dd !important;">
                        <span class="text-muted-2 small">Harga jual</span>
                        <span class="fw-semibold small">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f0e8dd !important;">
                        <span class="text-muted-2 small">Dimensi</span>
                        <span class="small">{{ $product->length_cm ?: '-' }} × {{ $product->width_cm ?: '-' }} × {{ $product->height_cm ?: '-' }} cm</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted-2 small">Status</span>
                        <span class="badge-soft badge-soft-{{ $product->is_active ? 'success' : 'secondary' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>

                    @if($product->description)
                        <div class="mt-3 pt-3 border-top" style="border-color:#f0e8dd !important;">
                            <div class="text-muted-2 small mb-1">Deskripsi</div>
                            <div class="small">{{ $product->description }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="kk-card">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Sesuaikan Stok</h3>
                            <p class="kk-card-sub">Catat koreksi stok manual</p>
                        </div>
                    </div>
                    <div class="kk-card-body">
                        <form method="POST" action="{{ route('products.adjust-stock', $product) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="direction">Arah</label>
                                <select name="direction" id="direction" class="form-select" required>
                                    <option value="in">Stok masuk (+)</option>
                                    <option value="out">Stok keluar (−)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="quantity">Jumlah</label>
                                <input type="number" step="0.01" min="0.01" name="quantity" id="quantity" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="notes">Catatan</label>
                                <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="Contoh: koreksi stok opname"></textarea>
                            </div>
                            <button class="btn btn-kk w-100" type="submit"><i class="bi bi-check2-circle me-1"></i> Simpan Penyesuaian</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Riwayat Pergerakan Stok</h3>
                        <p class="kk-card-sub">Produk jadi masuk dan keluar</p>
                    </div>
                    <span class="badge-soft badge-soft-secondary">{{ $movements->total() }} catatan</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Jenis</th>
                                <th class="text-end">Jumlah</th>
                                <th>Referensi</th>
                                <th>Oleh</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                @php
                                    $typeLabels = [
                                        'production_in' => ['Masuk Produksi', 'success'],
                                        'sale_out' => ['Terjual', 'danger'],
                                        'transfer_in' => ['Transfer Masuk', 'info'],
                                        'transfer_out' => ['Transfer Keluar', 'warning'],
                                        'adjustment_in' => ['Penyesuaian Masuk', 'success'],
                                        'adjustment_out' => ['Penyesuaian Keluar', 'warning'],
                                    ];
                                    [$typeLabel, $typeColor] = $typeLabels[$movement->movement_type] ?? [ucfirst($movement->movement_type), 'secondary'];
                                @endphp
                                <tr>
                                    <td class="small">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge-soft badge-soft-{{ $typeColor }}">{{ $typeLabel }}</span></td>
                                    <td class="text-end fw-semibold small">{{ number_format($movement->quantity, 2, ',', '.') }}</td>
                                    <td class="small text-muted-2">{{ $movement->reference_type ?? '-' }} @if($movement->reference_id)#{{ $movement->reference_id }}@endif</td>
                                    <td class="small">{{ $movement->createdBy?->name ?? '-' }}</td>
                                    <td class="small text-muted-2">{{ $movement->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><x-empty-state icon="bi-arrow-left-right" title="Belum ada pergerakan" text="Riwayat stok produk akan muncul di sini." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($movements->hasPages())
                    <div class="kk-card-body pt-0">{{ $movements->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
