@extends('layouts.app')

@section('title', $product ? 'Edit Produk' : 'Tambah Produk')
@section('page-title', $product ? 'Edit Produk' : 'Tambah Produk')
@section('page-subtitle', 'Data produk jadi dan harga jual')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">{{ $product ? 'Edit' : 'Tambah' }} Produk Jadi</h2>
            <p class="kk-page-sub">Stok awal 0. Gunakan penyesuaian stok di halaman detail.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <form method="POST" action="{{ $product ? route('products.update', $product) : route('products.store') }}" enctype="multipart/form-data">
        @csrf
        @if($product) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="kk-card mb-4">
                    <div class="kk-card-header"><h3 class="kk-card-title">Identitas Produk</h3></div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="code">Kode Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $product?->code) }}" placeholder="PRD-001" required>
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="name">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product?->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="category_id">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">— Pilih Kategori —</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="product_model_id">Model Produk</label>
                                <select class="form-select" id="product_model_id" name="product_model_id">
                                    <option value="">— Produk custom —</option>
                                    @foreach($models as $model)
                                        <option value="{{ $model->id }}" @selected(old('product_model_id', $product?->product_model_id) == $model->id)>{{ $model->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $product?->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kk-card">
                    <div class="kk-card-header"><h3 class="kk-card-title">Dimensi & Harga</h3></div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label" for="length_cm">Panjang (cm)</label>
                                <input type="number" class="form-control" id="length_cm" name="length_cm" value="{{ old('length_cm', $product?->length_cm) }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="width_cm">Lebar (cm)</label>
                                <input type="number" class="form-control" id="width_cm" name="width_cm" value="{{ old('width_cm', $product?->width_cm) }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="height_cm">Tinggi (cm)</label>
                                <input type="number" class="form-control" id="height_cm" name="height_cm" value="{{ old('height_cm', $product?->height_cm) }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="unit_id">Satuan <span class="text-danger">*</span></label>
                                <select class="form-select" id="unit_id" name="unit_id" required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" @selected(old('unit_id', $product?->unit_id) == $unit->id)>{{ $unit->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="sale_price">Harga Jual</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input type="number" class="form-control" id="sale_price" name="sale_price" value="{{ old('sale_price', $product?->sale_price ?? 0) }}" min="0" step="1000"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="min_stock_qty">Stok Minimum</label>
                                <input type="number" class="form-control" id="min_stock_qty" name="min_stock_qty" value="{{ old('min_stock_qty', $product?->min_stock_qty ?? 0) }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="kk-card mb-4">
                    <div class="kk-card-header"><h3 class="kk-card-title">Foto Produk</h3></div>
                    <div class="kk-card-body">
                        @if($product?->photo_path)
                            <img src="{{ Storage::url($product->photo_path) }}" alt="{{ $product->name }}" class="kk-thumb-lg mb-3">
                        @endif
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        <div class="form-text">JPG/PNG maksimal 2 MB. Opsional.</div>
                    </div>
                </div>

                <div class="kk-card mb-4">
                    <div class="kk-card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))>
                            <label class="form-check-label form-label mb-0" for="is_active">Produk aktif</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-kk btn-lg"><i class="bi bi-check2 me-1"></i> Simpan Produk</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-kk">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection
