@extends('layouts.app')

@section('title', 'Stok Keluar')
@section('page-title', 'Stok Keluar')
@section('page-subtitle', 'Catat pemakaian non-produksi, kerusakan, atau koreksi')

@section('content')
    @php
        $materialData = $materials->mapWithKeys(function ($material) {
            return [$material->id => [
                'stock' => (float) $material->stock_qty,
                'unit' => $material->unit?->code,
            ]];
        });
    @endphp

    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-box-arrow-up me-2 text-brown"></i>Stok Keluar</h2>
            <p class="kk-page-sub">Untuk pemakaian produksi, gunakan papan produksi agar stok terpotong otomatis.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="kk-card">
                <div class="kk-card-header"><h3 class="kk-card-title">Form Stok Keluar</h3></div>
                <div class="kk-card-body">
                    <form method="POST" action="{{ route('inventory.stock-out.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="material_id">Bahan <span class="text-danger">*</span></label>
                                <select name="material_id" id="material_id" class="form-select" required>
                                    <option value="">— Pilih Bahan —</option>
                                    @foreach($materials as $material)
                                        <option value="{{ $material->id }}" @selected(old('material_id', request('material_id')) == $material->id)>{{ $material->name }} ({{ $material->unit?->code }}) · stok {{ number_format($material->stock_qty, 2, ',', '.') }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text" id="stockInfo">Pilih bahan untuk melihat stok saat ini.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="movement_type">Tipe Keluar <span class="text-danger">*</span></label>
                                <select name="movement_type" id="movement_type" class="form-select" required>
                                    <option value="adjustment_out" @selected(old('movement_type') === 'adjustment_out')>Koreksi / Pemakaian</option>
                                    <option value="damage_out" @selected(old('movement_type') === 'damage_out')>Rusak / Hilang</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="quantity">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity') }}" min="0.0001" step="0.0001" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="movement_date">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="movement_date" id="movement_date" class="form-control" value="{{ old('movement_date', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="reason">Alasan <span class="text-danger">*</span></label>
                                <input type="text" name="reason" id="reason" class="form-control" value="{{ old('reason') }}" placeholder="Contoh: papan pecah saat pemotongan" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notes">Catatan</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="kk-divider"></div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-kk">Batal</a>
                            <button type="submit" class="btn btn-kk"><i class="bi bi-check2 me-1"></i> Simpan Stok Keluar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="kk-card">
                <div class="kk-card-header"><h3 class="kk-card-title">Catatan</h3></div>
                <div class="kk-card-body">
                    <div class="d-flex gap-2 mb-3">
                        <i class="bi bi-info-circle text-brown fs-5"></i>
                        <div class="small text-muted-2">Stok keluar manual tidak mengurangi kebutuhan produksi. Gunakan papan produksi untuk pemakaian bahan pesanan.</div>
                    </div>
                    <div class="p-3 rounded-3" style="background:#fbf6ee;border:1px dashed #e2d2be;font-size:.78rem;color:#6b5647;">
                        Setiap stok keluar tercatat di kartu stok dan laporan mutasi. Stok tidak boleh menjadi negatif.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const materialData = @json($materialData);
    const select = document.getElementById('material_id');
    const info = document.getElementById('stockInfo');

    select?.addEventListener('change', function () {
        const data = materialData[this.value];
        info.textContent = data
            ? 'Stok saat ini: ' + new Intl.NumberFormat('id-ID').format(data.stock) + ' ' + data.unit
            : 'Pilih bahan untuk melihat stok saat ini.';
    });
</script>
@endpush
