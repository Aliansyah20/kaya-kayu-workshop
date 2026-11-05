@extends('layouts.app')

@section('title', $productModel->name)
@section('page-title', 'Detail Model Produk')
@section('page-subtitle', $productModel->code . ' · ' . ($productModel->category?->name ?? 'Tanpa kategori'))

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">{{ $productModel->name }}</h2>
            <p class="kk-page-sub"><code>{{ $productModel->code }}</code> · {{ $productModel->category?->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('product-models.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            <a href="{{ route('product-models.edit', $productModel) }}" class="btn btn-kk"><i class="bi bi-pencil me-1"></i> Edit Model</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="kk-card mb-4 overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-4">
                        @if($productModel->photo_path)
                            <img src="{{ Storage::url($productModel->photo_path) }}" alt="{{ $productModel->name }}" class="w-100 h-100" style="object-fit:cover;min-height:220px;">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100" style="min-height:220px;background:linear-gradient(135deg,#efe4d4,#e3d2bd);">
                                <i class="bi bi-tree" style="font-size:3.5rem;color:#b18a64;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <span class="badge-soft badge-soft-{{ $productModel->is_active ? 'success' : 'secondary' }}">{{ $productModel->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    <h3 class="h5 fw-bold mt-2 mb-1">{{ $productModel->name }}</h3>
                                    <div class="text-muted-2 small">{{ $productModel->description ?: 'Belum ada deskripsi.' }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted-2" style="font-size:.68rem;">Harga acuan</div>
                                    <div class="fw-bold text-brown fs-5">
                                        @if($productModel->pricing_type === 'per_m2')
                                            Rp {{ number_format($productModel->default_price_per_m2, 0, ',', '.') }}<span class="small text-muted-2">/m²</span>
                                        @else
                                            Rp {{ number_format($productModel->default_fixed_price, 0, ',', '.') }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-3 pt-3 border-top" style="border-color:#f0e8dd !important;">
                                <div class="col-6 col-md-3">
                                    <div class="text-muted-2" style="font-size:.66rem;">Tipe Harga</div>
                                    <div class="fw-semibold small">{{ $productModel->pricing_type === 'per_m2' ? 'Per m²' : 'Tetap' }}</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted-2" style="font-size:.66rem;">Ukuran Acuan</div>
                                    <div class="fw-semibold small">{{ ucwords(str_replace('_', ' ', $productModel->default_measurement_type)) }}</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted-2" style="font-size:.66rem;">Estimasi</div>
                                    <div class="fw-semibold small">{{ $productModel->estimated_days }} hari</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted-2" style="font-size:.66rem;">Dimensi</div>
                                    <div class="fw-semibold small">
                                        {{ $productModel->default_length_cm ? $productModel->default_length_cm . ' × ' : '' }}{{ $productModel->default_width_cm ? $productModel->default_width_cm . ' × ' : '' }}{{ $productModel->default_height_cm ?: '-' }} cm
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kk-card mb-4">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Bill of Materials (BOM)</h3>
                        <p class="kk-card-sub">Kebutuhan bahan per 1 unit model. Stok dipotong otomatis saat produksi dimulai.</p>
                    </div>
                    <span class="badge-soft badge-soft-secondary">{{ $productModel->bomItems->count() }} komponen</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Bahan</th>
                                <th>Dimensi</th>
                                <th class="text-end">Qty / Unit</th>
                                <th>Satuan</th>
                                <th class="text-end">Waste</th>
                                <th class="text-end">Stok Saat Ini</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productModel->bomItems as $bom)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-brown">{{ $bom->material?->name }}</div>
                                        <div class="text-muted-2" style="font-size:.68rem;">{{ $bom->material?->code }}</div>
                                    </td>
                                    <td class="text-muted-2">{{ $bom->material?->dimension_label ?? '-' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($bom->quantity, 4, ',', '.') }}</td>
                                    <td>{{ $bom->unit?->code ?? $bom->material?->unit?->code }}</td>
                                    <td class="text-end">{{ number_format($bom->waste_percent, 1, ',', '.') }}%</td>
                                    <td class="text-end">
                                        @if($bom->material?->isLowStock())
                                            <span class="text-danger fw-bold">{{ number_format($bom->material?->stock_qty, 2, ',', '.') }}</span>
                                        @else
                                            {{ number_format($bom->material?->stock_qty, 2, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('product-models.bom.destroy', [$productModel, $bom]) }}" onsubmit="return confirm('Hapus komponen BOM ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger btn-icon" type="submit"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7"><x-empty-state icon="bi-layers" title="BOM belum diisi" text="Tambahkan bahan di bawah agar stok bisa dihitung otomatis." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="kk-card-body border-top" style="border-color:#f0e8dd !important;">
                    <h4 class="h6 fw-bold mb-3">Tambah / Perbarui Komponen</h4>
                    <form method="POST" action="{{ route('product-models.bom.store', $productModel) }}">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Bahan</label>
                                <select name="material_id" class="form-select" required>
                                    <option value="">— Pilih Bahan —</option>
                                    @foreach($materials as $material)
                                        <option value="{{ $material->id }}">{{ $material->name }} ({{ $material->unit?->code }}) · stok {{ number_format($material->stock_qty, 2, ',', '.') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Qty</label>
                                <input type="number" name="quantity" class="form-control" step="0.0001" min="0.0001" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Satuan</label>
                                <select name="unit_id" class="form-select" required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Waste %</label>
                                <input type="number" name="waste_percent" class="form-control" step="0.01" min="0" max="100" value="5">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-kk w-100"><i class="bi bi-plus-lg"></i> Simpan</button>
                            </div>
                            <div class="col-12">
                                <input type="text" name="notes" class="form-control form-control-sm" placeholder="Catatan komponen (opsional)">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="kk-card mb-4">
                <div class="kk-card-header"><h3 class="kk-card-title">Faktor Default</h3></div>
                <div class="kk-card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Kesulitan</span>
                        <span class="fw-semibold small">{{ $productModel->defaultComplexity?->name ?? '-' }} @if($productModel->defaultComplexity) (×{{ $productModel->defaultComplexity->factor }}) @endif</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2 small">Jenis Kayu</span>
                        <span class="fw-semibold small">{{ $productModel->defaultWoodType?->name ?? '-' }} @if($productModel->defaultWoodType) (×{{ $productModel->defaultWoodType->price_factor }}) @endif</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted-2 small">Kualitas Kayu</span>
                        <span class="fw-semibold small">{{ $productModel->defaultWoodGrade?->name ?? '-' }} @if($productModel->defaultWoodGrade) (×{{ $productModel->defaultWoodGrade->price_factor }}) @endif</span>
                    </div>
                </div>
            </div>

            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Pesanan Terbaru</h3>
                        <p class="kk-card-sub">Item yang memakai model ini</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            @forelse($recentItems as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('orders.show', $item->order) }}" class="fw-semibold text-brown">{{ $item->order?->order_no }}</a>
                                        <div class="text-muted-2" style="font-size:.68rem;">{{ $item->order?->customer?->name }} · {{ $item->quantity }} unit</div>
                                    </td>
                                    <td class="text-end"><x-status-badge :status="$item->status" type="item" /></td>
                                </tr>
                            @empty
                                <tr><td><x-empty-state icon="bi-receipt" title="Belum dipakai" text="Model ini belum digunakan di pesanan." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
