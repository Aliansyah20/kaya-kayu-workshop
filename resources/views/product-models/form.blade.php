@extends('layouts.app')

@section('title', $productModel ? 'Edit Model Produk' : 'Tambah Model Produk')
@section('page-title', $productModel ? 'Edit Model Produk' : 'Tambah Model Produk')
@section('page-subtitle', 'Atur harga acuan, ukuran default, dan bahan default')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">{{ $productModel ? 'Edit' : 'Tambah' }} Model Produk</h2>
            <p class="kk-page-sub">Harga akhir tetap dihitung ulang saat pesanan dibuat.</p>
        </div>
        <a href="{{ route('product-models.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <form method="POST" action="{{ $productModel ? route('product-models.update', $productModel) : route('product-models.store') }}" enctype="multipart/form-data">
        @csrf
        @if($productModel) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="kk-card mb-4">
                    <div class="kk-card-header"><h3 class="kk-card-title">Identitas Model</h3></div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="code">Kode Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $productModel?->code) }}" placeholder="MJ-001" required>
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="name">Nama Model <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $productModel?->name) }}" placeholder="Meja Makan Jati" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="category_id">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="">— Pilih Kategori —</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $productModel?->category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="estimated_days">Estimasi Pengerjaan (hari)</label>
                                <input type="number" class="form-control @error('estimated_days') is-invalid @enderror" id="estimated_days" name="estimated_days" value="{{ old('estimated_days', $productModel?->estimated_days ?? 14) }}" min="0">
                                @error('estimated_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Deskripsi</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Bentuk, fungsi, dan catatan khusus model...">{{ old('description', $productModel?->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kk-card mb-4">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Harga & Ukuran Default</h3>
                            <p class="kk-card-sub">Rumus cepat: luas m² × Rp 1.000.000 × faktor.</p>
                        </div>
                    </div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="pricing_type">Tipe Harga <span class="text-danger">*</span></label>
                                <select class="form-select" id="pricing_type" name="pricing_type" required>
                                    <option value="per_m2" @selected(old('pricing_type', $productModel?->pricing_type ?? 'per_m2') === 'per_m2')>Per m²</option>
                                    <option value="fixed" @selected(old('pricing_type', $productModel?->pricing_type) === 'fixed')>Harga Tetap</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="default_measurement_type">Ukuran Acuan <span class="text-danger">*</span></label>
                                <select class="form-select" id="default_measurement_type" name="default_measurement_type" required>
                                    <option value="front_area" @selected(old('default_measurement_type', $productModel?->default_measurement_type ?? 'front_area') === 'front_area')>Luas Tampak Depan (W × H)</option>
                                    <option value="top_area" @selected(old('default_measurement_type', $productModel?->default_measurement_type) === 'top_area')>Luas Atas (L × W)</option>
                                    <option value="surface_area" @selected(old('default_measurement_type', $productModel?->default_measurement_type) === 'surface_area')>Luas Permukaan</option>
                                    <option value="manual" @selected(old('default_measurement_type', $productModel?->default_measurement_type) === 'manual')>Manual (m²)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="default_price_per_m2">Harga / m²</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="default_price_per_m2" name="default_price_per_m2" value="{{ old('default_price_per_m2', $productModel?->default_price_per_m2 ?? 1000000) }}" min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="default_fixed_price">Harga Tetap / Unit</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="default_fixed_price" name="default_fixed_price" value="{{ old('default_fixed_price', $productModel?->default_fixed_price) }}" min="0" step="1000">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Dimensi Default (cm)</label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <div class="input-group"><span class="input-group-text">L</span><input type="number" class="form-control" name="default_length_cm" value="{{ old('default_length_cm', $productModel?->default_length_cm) }}" min="0" step="0.01" placeholder="Panjang"></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-group"><span class="input-group-text">W</span><input type="number" class="form-control" name="default_width_cm" value="{{ old('default_width_cm', $productModel?->default_width_cm) }}" min="0" step="0.01" placeholder="Lebar"></div>
                                    </div>
                                    <div class="col-4">
                                        <div class="input-group"><span class="input-group-text">T</span><input type="number" class="form-control" name="default_height_cm" value="{{ old('default_height_cm', $productModel?->default_height_cm) }}" min="0" step="0.01" placeholder="Tinggi"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="kk-card mb-4">
                    <div class="kk-card-header"><h3 class="kk-card-title">Faktor Default</h3></div>
                    <div class="kk-card-body">
                        <div class="mb-3">
                            <label class="form-label" for="default_complexity_level_id">Tingkat Kesulitan</label>
                            <select class="form-select" id="default_complexity_level_id" name="default_complexity_level_id">
                                <option value="">— Tidak ditentukan —</option>
                                @foreach($complexities as $complexity)
                                    <option value="{{ $complexity->id }}" @selected(old('default_complexity_level_id', $productModel?->default_complexity_level_id) == $complexity->id)>{{ $complexity->name }} (×{{ $complexity->factor }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="default_wood_type_id">Jenis Kayu</label>
                            <select class="form-select" id="default_wood_type_id" name="default_wood_type_id">
                                <option value="">— Tidak ditentukan —</option>
                                @foreach($woodTypes as $woodType)
                                    <option value="{{ $woodType->id }}" @selected(old('default_wood_type_id', $productModel?->default_wood_type_id) == $woodType->id)>{{ $woodType->name }} (×{{ $woodType->price_factor }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="default_wood_grade_id">Kualitas Kayu</label>
                            <select class="form-select" id="default_wood_grade_id" name="default_wood_grade_id">
                                <option value="">— Tidak ditentukan —</option>
                                @foreach($woodGrades as $woodGrade)
                                    <option value="{{ $woodGrade->id }}" @selected(old('default_wood_grade_id', $productModel?->default_wood_grade_id) == $woodGrade->id)>{{ $woodGrade->name }} (×{{ $woodGrade->price_factor }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="kk-card mb-4">
                    <div class="kk-card-header"><h3 class="kk-card-title">Foto Model</h3></div>
                    <div class="kk-card-body">
                        @if($productModel?->photo_path)
                            <img src="{{ Storage::url($productModel->photo_path) }}" alt="{{ $productModel->name }}" class="kk-thumb-lg mb-3">
                        @endif
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                        <div class="form-text">JPG/PNG maksimal 2 MB. Foto opsional.</div>
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="kk-card mb-4">
                    <div class="kk-card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $productModel?->is_active ?? true))>
                            <label class="form-check-label form-label mb-0" for="is_active">Model aktif</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-kk btn-lg"><i class="bi bi-check2 me-1"></i> Simpan Model</button>
                    <a href="{{ route('product-models.index') }}" class="btn btn-outline-kk">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection
