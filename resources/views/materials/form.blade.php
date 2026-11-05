@extends('layouts.app')

@section('title', $material ? 'Edit Bahan' : 'Tambah Bahan')
@section('page-title', $material ? 'Edit Bahan' : 'Tambah Bahan')
@section('page-subtitle', 'Data bahan, dimensi, dan batas stok minimum')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">{{ $material ? 'Edit' : 'Tambah' }} Bahan Baku</h2>
            <p class="kk-page-sub">Stok awal selalu 0. Gunakan menu Stok Masuk untuk menambah saldo.</p>
        </div>
        <a href="{{ route('materials.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <form method="POST" action="{{ $material ? route('materials.update', $material) : route('materials.store') }}" enctype="multipart/form-data">
        @csrf
        @if($material) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="kk-card mb-4">
                    <div class="kk-card-header"><h3 class="kk-card-title">Identitas Bahan</h3></div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="code">Kode Bahan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $material?->code) }}" placeholder="KAYU-JTI-01" required>
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="name">Nama Bahan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $material?->name) }}" placeholder="Papan Jati 2 cm" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="category_id">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="">— Pilih Kategori —</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $material?->category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="material_type">Tipe Bahan <span class="text-danger">*</span></label>
                                <select class="form-select" id="material_type" name="material_type" required>
                                    <option value="wood" @selected(old('material_type', $material?->material_type ?? 'wood') === 'wood')>Kayu</option>
                                    <option value="finishing" @selected(old('material_type', $material?->material_type) === 'finishing')>Finishing</option>
                                    <option value="hardware" @selected(old('material_type', $material?->material_type) === 'hardware')>Hardware</option>
                                    <option value="consumable" @selected(old('material_type', $material?->material_type) === 'consumable')>Habis Pakai</option>
                                    <option value="other" @selected(old('material_type', $material?->material_type) === 'other')>Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="unit_id">Satuan <span class="text-danger">*</span></label>
                                <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required>
                                    <option value="">— Pilih Satuan —</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" @selected(old('unit_id', $material?->unit_id) == $unit->id)>{{ $unit->name }} ({{ $unit->code }})</option>
                                    @endforeach
                                </select>
                                @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="wood_type_id">Jenis Kayu</label>
                                <select class="form-select" id="wood_type_id" name="wood_type_id">
                                    <option value="">— Tidak berlaku —</option>
                                    @foreach($woodTypes as $woodType)
                                        <option value="{{ $woodType->id }}" @selected(old('wood_type_id', $material?->wood_type_id) == $woodType->id)>{{ $woodType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="wood_grade_id">Kualitas Kayu</label>
                                <select class="form-select" id="wood_grade_id" name="wood_grade_id">
                                    <option value="">— Tidak berlaku —</option>
                                    @foreach($woodGrades as $woodGrade)
                                        <option value="{{ $woodGrade->id }}" @selected(old('wood_grade_id', $material?->wood_grade_id) == $woodGrade->id)>{{ $woodGrade->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notes">Catatan</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $material?->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kk-card mb-4">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Dimensi & Stok</h3>
                            <p class="kk-card-sub">Isi dimensi untuk bahan kayu/lembaran. Kosongkan jika tidak relevan.</p>
                        </div>
                    </div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="thickness_mm">Tebal (mm)</label>
                                <input type="number" class="form-control" id="thickness_mm" name="thickness_mm" value="{{ old('thickness_mm', $material?->thickness_mm) }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="width_mm">Lebar (mm)</label>
                                <input type="number" class="form-control" id="width_mm" name="width_mm" value="{{ old('width_mm', $material?->width_mm) }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="length_mm">Panjang (mm)</label>
                                <input type="number" class="form-control" id="length_mm" name="length_mm" value="{{ old('length_mm', $material?->length_mm) }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="min_stock_qty">Stok Minimum</label>
                                <input type="number" class="form-control" id="min_stock_qty" name="min_stock_qty" value="{{ old('min_stock_qty', $material?->min_stock_qty ?? 0) }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="purchase_price">Harga Beli Terakhir</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input type="number" class="form-control" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $material?->purchase_price ?? 0) }}" min="0" step="100"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="location">Lokasi Simpan</label>
                                <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $material?->location) }}" placeholder="Rak A / Gudang belakang">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="kk-card mb-4">
                    <div class="kk-card-header"><h3 class="kk-card-title">Foto Bahan</h3></div>
                    <div class="kk-card-body">
                        @if($material?->photo_path)
                            <img src="{{ Storage::url($material->photo_path) }}" alt="{{ $material->name }}" class="kk-thumb-lg mb-3">
                        @endif
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                        <div class="form-text">JPG/PNG maksimal 2 MB. Opsional.</div>
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="kk-card mb-4">
                    <div class="kk-card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $material?->is_active ?? true))>
                            <label class="form-check-label form-label mb-0" for="is_active">Bahan aktif</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-kk btn-lg"><i class="bi bi-check2 me-1"></i> Simpan Bahan</button>
                    <a href="{{ route('materials.index') }}" class="btn btn-outline-kk">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection
