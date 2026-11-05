@extends('layouts.app')

@section('title', 'Stok Masuk')
@section('page-title', 'Stok Masuk')
@section('page-subtitle', 'Catat pembelian bahan dari supplier')

@section('content')
    @php
        $materialData = $materials->mapWithKeys(function ($material) {
            return [$material->id => [
                'price' => (float) $material->purchase_price,
                'unit' => $material->unit?->code,
            ]];
        });
        $oldRows = old('items');
        $oldRows = is_array($oldRows) && count($oldRows) > 0 ? array_values($oldRows) : [[]];
    @endphp

    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-box-arrow-in-down me-2 text-brown"></i>Stok Masuk</h2>
            <p class="kk-page-sub">Satu pembelian dapat berisi beberapa bahan.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <form method="POST" action="{{ route('inventory.stock-in.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="kk-card mb-4">
                    <div class="kk-card-header"><h3 class="kk-card-title">Informasi Pembelian</h3></div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="supplier_id">Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="form-select" required>
                                    <option value="">— Pilih Supplier —</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text"><a href="{{ route('master.create', 'suppliers') }}" target="_blank">+ Tambah supplier</a></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="purchase_date">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" id="purchase_date" class="form-control" value="{{ old('purchase_date', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="invoice_no">No. Invoice</label>
                                <input type="text" name="invoice_no" id="invoice_no" class="form-control" value="{{ old('invoice_no') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notes">Catatan</label>
                                <input type="text" name="notes" id="notes" class="form-control" value="{{ old('notes') }}" placeholder="Ongkir, termin, atau catatan pembelian...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kk-card">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Item Pembelian</h3>
                            <p class="kk-card-sub">Stok langsung bertambah saat disimpan</p>
                        </div>
                        <button type="button" class="btn btn-amber" id="addRow"><i class="bi bi-plus-lg me-1"></i> Tambah Baris</button>
                    </div>
                    <div class="kk-card-body">
                        <div id="itemsContainer">
                            @foreach($oldRows as $index => $oldRow)
                                @include('inventory.partials.stock-in-row', ['index' => $index, 'materials' => $materials])
                            @endforeach
                        </div>

                        <template id="rowTemplate">
                            @include('inventory.partials.stock-in-row', ['index' => '__INDEX__', 'materials' => $materials])
                        </template>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="kk-card" style="position:sticky;top:92px;">
                    <div class="kk-card-header"><h3 class="kk-card-title">Ringkasan Pembelian</h3></div>
                    <div class="kk-card-body">
                        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                            <span class="text-muted-2">Jumlah baris</span>
                            <span class="fw-semibold" id="rowCount">1</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted-2">Total pembelian</span>
                            <span class="fw-bold text-brown" id="grandTotal">Rp 0</span>
                        </div>
                        <div class="p-3 rounded-3 mt-3" style="background:#fbf6ee;border:1px dashed #e2d2be;font-size:.75rem;color:#6b5647;">
                            Harga satuan otomatis terisi dari harga beli terakhir. Anda bisa mengubahnya sesuai invoice supplier.
                        </div>
                    </div>
                    <div class="kk-card-body border-top" style="border-color:#f0e8dd !important;">
                        <button type="submit" class="btn btn-kk w-100 btn-lg"><i class="bi bi-check2-circle me-1"></i> Simpan Pembelian</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const container = document.getElementById('itemsContainer');
        const template = document.getElementById('rowTemplate');
        const addButton = document.getElementById('addRow');
        const materialData = @json($materialData);
        let rowIndex = {{ count($oldRows) }};

        const rupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value || 0));

        function calculateRow(row) {
            const quantity = parseFloat(row.querySelector('.quantity-input')?.value || 0) || 0;
            const price = parseFloat(row.querySelector('.price-input')?.value || 0) || 0;
            const subtotal = quantity * price;
            row.querySelector('.subtotal-display').textContent = rupiah(subtotal);
            row.dataset.subtotal = subtotal;
            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            let count = 0;
            document.querySelectorAll('.stock-in-row').forEach((row) => {
                total += parseFloat(row.dataset.subtotal || 0);
                count++;
            });
            document.getElementById('grandTotal').textContent = rupiah(total);
            document.getElementById('rowCount').textContent = count;
        }

        addButton?.addEventListener('click', function () {
            const html = template.innerHTML.replaceAll('__INDEX__', rowIndex);
            container.insertAdjacentHTML('beforeend', html);
            rowIndex++;
            calculateRow(container.lastElementChild);
        });

        container?.addEventListener('click', function (event) {
            const remove = event.target.closest('.remove-row');
            if (!remove) return;
            if (container.querySelectorAll('.stock-in-row').length <= 1) {
                alert('Minimal satu baris pembelian.');
                return;
            }
            remove.closest('.stock-in-row').remove();
            updateTotal();
        });

        container?.addEventListener('change', function (event) {
            if (!event.target.matches('.material-select')) return;
            const row = event.target.closest('.stock-in-row');
            const data = materialData[event.target.value];
            if (data) {
                row.querySelector('.price-input').value = data.price || 0;
            }
            calculateRow(row);
        });

        container?.addEventListener('input', function (event) {
            if (event.target.matches('.quantity-input, .price-input')) {
                calculateRow(event.target.closest('.stock-in-row'));
            }
        });

        document.querySelectorAll('.stock-in-row').forEach(calculateRow);
    })();
</script>
@endpush
