@extends('layouts.app')

@section('title', 'Model Produk')
@section('page-title', 'Model Produk')
@section('page-subtitle', 'Katalog model yang dilihat pelanggan dari buku fisik')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-book me-2 text-brown"></i>Model Produk</h2>
            <p class="kk-page-sub">Setiap model menyimpan harga acuan, ukuran default, dan komponen BOM.</p>
        </div>
        <a href="{{ route('product-models.create') }}" class="btn btn-kk"><i class="bi bi-plus-lg me-1"></i> Tambah Model</a>
    </div>

    <div class="kk-card mb-4">
        <div class="kk-card-body py-3">
            <form class="d-flex gap-2 flex-wrap" method="GET">
                <div class="input-group" style="min-width:260px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari kode atau nama model...">
                </div>
                <select name="category" class="form-select" style="width:auto;">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline-kk" type="submit">Filter</button>
                @if(request()->hasAny(['q', 'category']))
                    <a href="{{ route('product-models.index') }}" class="btn btn-outline-kk">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse($models as $model)
            <div class="col-md-6 col-xl-4">
                <div class="kk-card h-100 overflow-hidden">
                    @if($model->photo_path)
                        <img src="{{ Storage::url($model->photo_path) }}" alt="{{ $model->name }}" class="w-100" style="height:190px;object-fit:cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center" style="height:190px;background:linear-gradient(135deg,#efe4d4,#e3d2bd);">
                            <i class="bi bi-tree" style="font-size:3rem;color:#b18a64;"></i>
                        </div>
                    @endif
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="text-muted-2" style="font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;">{{ $model->category?->name }}</div>
                                <h3 class="h6 fw-bold mb-1 mt-1">{{ $model->name }}</h3>
                                <div class="text-muted-2" style="font-size:.72rem;"><code>{{ $model->code }}</code> · {{ $model->bom_items_count }} komponen BOM</div>
                            </div>
                            <span class="badge-soft badge-soft-{{ $model->is_active ? 'success' : 'secondary' }}">{{ $model->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-end mt-3 pt-3 border-top" style="border-color:#f0e8dd !important;">
                            <div>
                                <div class="text-muted-2" style="font-size:.66rem;">Harga acuan</div>
                                <div class="fw-bold text-brown">
                                    @if($model->pricing_type === 'per_m2')
                                        Rp {{ number_format($model->default_price_per_m2, 0, ',', '.') }} / m²
                                    @else
                                        Rp {{ number_format($model->default_fixed_price, 0, ',', '.') }} / unit
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('product-models.show', $model) }}" class="btn btn-sm btn-outline-kk">Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="kk-card"><x-empty-state icon="bi-book" title="Belum ada model produk" text="Tambahkan model pertama agar bisa dipakai saat membuat pesanan." /></div>
            </div>
        @endforelse
    </div>

    @if($models->hasPages())
        <div class="mt-4">{{ $models->links() }}</div>
    @endif
@endsection
