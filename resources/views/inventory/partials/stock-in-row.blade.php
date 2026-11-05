<div class="row g-2 align-items-end mb-2 stock-in-row">
    <div class="col-md-5">
        <label class="form-label">Bahan</label>
        <select name="items[{{ $index }}][material_id]" class="form-select material-select" required>
            <option value="">— Pilih Bahan —</option>
            @foreach($materials as $material)
                <option value="{{ $material->id }}" @selected(old("items.{$index}.material_id", $index === 0 ? request('material_id') : null) == $material->id)>{{ $material->name }} ({{ $material->unit?->code }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Qty</label>
        <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" value="{{ old("items.{$index}.quantity", 1) }}" min="0.0001" step="0.0001" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Harga Satuan</label>
        <div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="items[{{ $index }}][unit_price]" class="form-control price-input" value="{{ old("items.{$index}.unit_price", 0) }}" min="0" step="100" required></div>
    </div>
    <div class="col-md-2">
        <label class="form-label">Subtotal</label>
        <div class="form-control bg-light subtotal-display" style="border-color:#e7dccd;">Rp 0</div>
    </div>
    <div class="col-12 d-flex justify-content-end">
        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash me-1"></i>Hapus baris</button>
    </div>
</div>
