@extends('layouts.app')

@section('title', 'Opname Baru')
@section('page-title', 'Opname Baru')
@section('page-subtitle', 'Masukkan jumlah fisik hasil hitung di gudang')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">Buat Stock Opname</h2>
            <p class="kk-page-sub">Kosongkan bahan yang tidak dihitung. Selisih akan otomatis menyesuaikan stok.</p>
        </div>
        <a href="{{ route('inventory.opname.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <form method="POST" action="{{ route('inventory.opname.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="kk-card">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Daftar Bahan</h3>
                            <p class="kk-card-sub">Stok sistem diambil saat form dibuka</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-kk" id="fillSystem"><i class="bi bi-magic me-1"></i> Samakan dengan sistem</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Bahan</th>
                                    <th class="text-end">Stok Sistem</th>
                                    <th style="width:150px;">Stok Fisik</th>
                                    <th>Alasan Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($materials as $index => $material)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold small">{{ $material->name }}</div>
                                            <div class="text-muted-2" style="font-size:.66rem;">{{ $material->code }} · {{ $material->unit?->code }}</div>
                                            <input type="hidden" name="items[{{ $index }}][material_id]" value="{{ $material->id }}">
                                        </td>
                                        <td class="text-end small">{{ number_format($material->stock_qty, 2, ',', '.') }}</td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][physical_qty]" class="form-control form-control-sm physical-input" value="{{ old("items.{$index}.physical_qty") }}" min="0" step="0.0001" data-system="{{ $material->stock_qty }}" placeholder="{{ number_format($material->stock_qty, 2, ',', '.') }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $index }}][reason]" class="form-control form-control-sm" value="{{ old("items.{$index}.reason") }}" placeholder="Contoh: sisa potongan / rusak">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="kk-card" style="position:sticky;top:92px;">
                    <div class="kk-card-header"><h3 class="kk-card-title">Informasi Opname</h3></div>
                    <div class="kk-card-body">
                        <div class="mb-3">
                            <label class="form-label" for="opname_date">Tanggal Opname <span class="text-danger">*</span></label>
                            <input type="date" name="opname_date" id="opname_date" class="form-control" value="{{ old('opname_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="notes">Catatan</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Kondisi gudang, petugas hitung...">{{ old('notes') }}</textarea>
                        </div>
                        <div class="p-3 rounded-3" style="background:#fbf6ee;border:1px dashed #e2d2be;font-size:.75rem;color:#6b5647;">
                            Setelah disimpan, stok sistem akan disesuaikan dengan stok fisik dan mutasi opname tercatat.
                        </div>
                    </div>
                    <div class="kk-card-body border-top" style="border-color:#f0e8dd !important;">
                        <button type="submit" class="btn btn-kk w-100 btn-lg"><i class="bi bi-check2-circle me-1"></i> Simpan Opname</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('fillSystem')?.addEventListener('click', function () {
        document.querySelectorAll('.physical-input').forEach(function (input) {
            input.value = input.dataset.system;
        });
    });
</script>
@endpush
