@extends('layouts.app')

@section('title', 'Stok Produk Jadi')
@section('page-title', 'Stok Produk Jadi')
@section('page-subtitle', 'Produk selesai yang siap dijual atau dikirim')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-box2-heart me-2 text-brown"></i>Produk Jadi</h2>
            <p class="kk-page-sub">Produk bisa berasal dari pengalihan pesanan atau produksi stok.</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-kk"><i class="bi bi-plus-lg me-1"></i> Tambah Produk</a>
    </div>

    <div class="kk-card mb-4">
        <div class="kk-card-body py-3">
            <form class="d-flex gap-2 flex-wrap" method="GET">
                <div class="input-group" style="min-width:260px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari kode atau nama produk...">
                </div>
                <select name="category" class="form-select" style="width:auto;">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <label class="btn btn-outline-kk d-flex align-items-center gap-2 mb-0">
                    <input type="checkbox" name="low_stock" value="1" class="form-check-input mt-0" @checked(request('low_stock'))>
                    Stok menipis
                </label>
                <button class="btn btn-outline-kk" type="submit">Filter</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-md-6 col-xl-4">
                <div class="kk-card h-100">
                    <div class="p-3">
                        <div class="d-flex align-items-start gap-3">
                            @if($product->photo_path)
                                <img src="{{ Storage::url($product->photo_path) }}" class="kk-thumb" alt="{{ $product->name }}" style="width:70px;height:70px;">
                            @else
                                <div class="kk-thumb d-grid" style="width:70px;height:70px;display:grid;place-items:center;color:#b18a64;"><i class="bi bi-box2-heart fs-4"></i></div>
                            @endif
                            <div class="flex-grow-1 min-w-0">
                                <div class="text-muted-2" style="font-size:.66rem;">{{ $product->category?->name }}</div>
                                <h3 class="h6 fw-bold mb-1 text-truncate">{{ $product->name }}</h3>
                                <div class="text-muted-2" style="font-size:.7rem;"><code>{{ $product->code }}</code> · {{ $product->productModel?->name ?? 'Produk custom' }}</div>
                            </div>
                            <span class="badge-soft badge-soft-{{ $product->stock_qty <= 0 ? 'danger' : ($product->isLowStock() ? 'warning' : 'success') }}">
                                {{ $product->stock_qty <= 0 ? 'Habis' : ($product->isLowStock() ? 'Menipis' : 'Tersedia') }}
                            </span>
                        </div>

                        <div class="row g-2 mt-3">
                            <div class="col-6">
                                <div class="p-2 rounded-3" style="background:#fbf6ee;">
                                    <div class="text-muted-2" style="font-size:.64rem;">Stok</div>
                                    <div class="fw-bold">{{ number_format($product->stock_qty, 2, ',', '.') }} {{ $product->unit?->code }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded-3" style="background:#fbf6ee;">
                                    <div class="text-muted-2" style="font-size:.64rem;">Harga Jual</div>
                                    <div class="fw-bold">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top" style="border-color:#f0e8dd !important;">
                            <div class="text-muted-2" style="font-size:.7rem;">
                                {{ $product->length_cm ? $product->length_cm . ' × ' : '' }}{{ $product->width_cm ? $product->width_cm . ' × ' : '' }}{{ $product->height_cm ?: '-' }} cm
                            </div>
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-kk">Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="kk-card"><x-empty-state icon="bi-box2-heart" title="Belum ada produk jadi" text="Produk jadi akan muncul dari pengalihan pesanan atau produksi stok." /></div></div>
        @endforelse
    </div>

    @if($products->hasPages())
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
@endsection
