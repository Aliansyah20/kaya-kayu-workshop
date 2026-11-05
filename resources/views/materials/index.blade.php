@extends('layouts.app')

@section('title', 'Bahan Baku')
@section('page-title', 'Bahan Baku')
@section('page-subtitle', 'Stok, harga beli, dan mutasi bahan workshop')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-layers me-2 text-brown"></i>Bahan Baku</h2>
            <p class="kk-page-sub">Stok di bawah ini adalah saldo terkini dari ledger mutasi.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.stock-in.create') }}" class="btn btn-outline-kk"><i class="bi bi-box-arrow-in-down me-1"></i> Stok Masuk</a>
            <a href="{{ route('materials.create') }}" class="btn btn-kk"><i class="bi bi-plus-lg me-1"></i> Tambah Bahan</a>
        </div>
    </div>

    @if($lowStockCount > 0)
        <div class="alert border-0 d-flex align-items-center gap-2 mb-4" style="border-radius:14px;background:#f8ecd7;color:#8b5e24;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div class="small"><strong>{{ $lowStockCount }} bahan</strong> berada di bawah stok minimum. Segera lakukan pembelian.</div>
        </div>
    @endif

    <div class="kk-card mb-4">
        <div class="kk-card-body py-3">
            <form class="d-flex gap-2 flex-wrap" method="GET">
                <div class="input-group" style="min-width:240px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, kode, lokasi...">
                </div>
                <select name="category" class="form-select" style="width:auto;">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="type" class="form-select" style="width:auto;">
                    <option value="">Semua tipe</option>
                    <option value="wood" @selected(request('type') === 'wood')>Kayu</option>
                    <option value="finishing" @selected(request('type') === 'finishing')>Finishing</option>
                    <option value="hardware" @selected(request('type') === 'hardware')>Hardware</option>
                    <option value="consumable" @selected(request('type') === 'consumable')>Habis Pakai</option>
                    <option value="other" @selected(request('type') === 'other')>Lainnya</option>
                </select>
                <label class="btn btn-outline-kk d-flex align-items-center gap-2 mb-0">
                    <input type="checkbox" name="low_stock" value="1" class="form-check-input mt-0" @checked(request('low_stock'))>
                    Stok menipis
                </label>
                <button class="btn btn-outline-kk" type="submit">Filter</button>
                @if(request()->hasAny(['q', 'category', 'type', 'low_stock']))
                    <a href="{{ route('materials.index') }}" class="btn btn-outline-kk">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="kk-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Bahan</th>
                        <th>Kategori</th>
                        <th>Dimensi</th>
                        <th class="text-end">Stok</th>
                        <th class="text-end">Min.</th>
                        <th class="text-end">Harga Beli</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($material->photo_path)
                                        <img src="{{ Storage::url($material->photo_path) }}" class="kk-thumb" alt="{{ $material->name }}">
                                    @else
                                        <div class="kk-thumb d-grid place-items-center" style="display:grid;place-items:center;color:#b18a64;"><i class="bi bi-box"></i></div>
                                    @endif
                                    <div>
                                        <a href="{{ route('materials.show', $material) }}" class="fw-semibold text-brown">{{ $material->name }}</a>
                                        <div class="text-muted-2" style="font-size:.68rem;"><code>{{ $material->code }}</code> · {{ $material->location ?: 'Tanpa lokasi' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small">{{ $material->category?->name }}</div>
                                <div class="text-muted-2" style="font-size:.68rem;">{{ ucfirst($material->material_type) }}</div>
                            </td>
                            <td class="text-muted-2 small">{{ $material->dimension_label }}</td>
                            <td class="text-end fw-bold {{ $material->isLowStock() ? 'text-danger' : '' }}">{{ number_format($material->stock_qty, 2, ',', '.') }} <span class="text-muted-2 fw-normal">{{ $material->unit?->code }}</span></td>
                            <td class="text-end text-muted-2">{{ number_format($material->min_stock_qty, 2, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($material->purchase_price, 0, ',', '.') }}</td>
                            <td>
                                @if(!$material->is_active)
                                    <span class="badge-soft badge-soft-secondary">Nonaktif</span>
                                @elseif($material->isLowStock())
                                    <span class="badge-soft badge-soft-danger">Menipis</span>
                                @else
                                    <span class="badge-soft badge-soft-success">Aman</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('materials.show', $material) }}" class="btn btn-sm btn-outline-kk btn-icon" title="Detail"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('materials.edit', $material) }}" class="btn btn-sm btn-outline-kk btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state icon="bi-layers" title="Belum ada bahan" text="Tambahkan bahan baku untuk mulai menghitung stok dan BOM." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($materials->hasPages())
            <div class="p-3 border-top" style="border-color:#f0e8dd !important;">{{ $materials->links() }}</div>
        @endif
    </div>
@endsection
