<div class="kk-card mb-3 item-card">
    <div class="kk-card-header">
        <div class="d-flex align-items-center gap-2">
            <span class="badge-soft badge-soft-secondary">Item <span class="item-number">{{ is_numeric($index) ? $index + 1 : 'Baru' }}</span></span>
            <span class="text-muted-2 small item-summary">Pilih model atau isi produk custom</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger btn-icon remove-item" title="Hapus item"><i class="bi bi-trash"></i></button>
    </div>
    <div class="kk-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Model Produk</label>
                <select name="items[{{ $index }}][product_model_id]" class="form-select model-select">
                    <option value="">— Produk custom —</option>
                    @foreach($productModels as $model)
                        <option value="{{ $model->id }}" @selected(old("items.{$index}.product_model_id") == $model->id)>{{ $model->name }} ({{ $model->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nama Item <span class="text-danger">*</span></label>
                <input type="text" name="items[{{ $index }}][item_name]" class="form-control item-name" value="{{ old("items.{$index}.item_name") }}" placeholder="Contoh: Meja Makan Jati 180 cm" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tipe Harga <span class="text-danger">*</span></label>
                <select name="items[{{ $index }}][pricing_type]" class="form-select pricing-type" required>
                    <option value="per_m2" @selected(old("items.{$index}.pricing_type", 'per_m2') === 'per_m2')>Per m²</option>
                    <option value="fixed" @selected(old("items.{$index}.pricing_type") === 'fixed')>Harga Tetap</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ukuran Acuan <span class="text-danger">*</span></label>
                <select name="items[{{ $index }}][measurement_type]" class="form-select measurement-type" required>
                    <option value="front_area" @selected(old("items.{$index}.measurement_type", 'front_area') === 'front_area')>Luas Tampak Depan (W × H)</option>
                    <option value="top_area" @selected(old("items.{$index}.measurement_type") === 'top_area')>Luas Atas (L × W)</option>
                    <option value="surface_area" @selected(old("items.{$index}.measurement_type") === 'surface_area')>Luas Permukaan</option>
                    <option value="manual" @selected(old("items.{$index}.measurement_type") === 'manual')>Manual (m²)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" value="{{ old("items.{$index}.quantity", 1) }}" min="1" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Jenis Kayu</label>
                <select name="items[{{ $index }}][wood_type_id]" class="form-select wood-type-select">
                    <option value="">— Ikuti model —</option>
                    @foreach($woodTypes as $woodType)
                        <option value="{{ $woodType->id }}" @selected(old("items.{$index}.wood_type_id") == $woodType->id)>{{ $woodType->name }} (×{{ $woodType->price_factor }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kualitas Kayu</label>
                <select name="items[{{ $index }}][wood_grade_id]" class="form-select wood-grade-select">
                    <option value="">— Ikuti model —</option>
                    @foreach($woodGrades as $woodGrade)
                        <option value="{{ $woodGrade->id }}" @selected(old("items.{$index}.wood_grade_id") == $woodGrade->id)>{{ $woodGrade->name }} (×{{ $woodGrade->price_factor }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tingkat Kesulitan</label>
                <select name="items[{{ $index }}][complexity_level_id]" class="form-select complexity-select">
                    <option value="">— Ikuti model —</option>
                    @foreach($complexities as $complexity)
                        <option value="{{ $complexity->id }}" @selected(old("items.{$index}.complexity_level_id") == $complexity->id)>{{ $complexity->name }} (×{{ $complexity->factor }})</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Panjang (cm)</label>
                <input type="number" name="items[{{ $index }}][length_cm]" class="form-control length-input" value="{{ old("items.{$index}.length_cm") }}" min="0" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Lebar (cm)</label>
                <input type="number" name="items[{{ $index }}][width_cm]" class="form-control width-input" value="{{ old("items.{$index}.width_cm") }}" min="0" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tinggi (cm)</label>
                <input type="number" name="items[{{ $index }}][height_cm]" class="form-control height-input" value="{{ old("items.{$index}.height_cm") }}" min="0" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Luas (m²)</label>
                <input type="number" name="items[{{ $index }}][area_m2]" class="form-control area-input" value="{{ old("items.{$index}.area_m2") }}" min="0" step="0.0001" readonly>
            </div>

            <div class="col-md-4">
                <label class="form-label">Harga Satuan</label>
                <div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price" value="{{ old("items.{$index}.unit_price") }}" min="0" step="1000" placeholder="Otomatis dari model"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Biaya Tambahan</label>
                <div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="items[{{ $index }}][additional_fee]" class="form-control additional-fee" value="{{ old("items.{$index}.additional_fee", 0) }}" min="0" step="1000"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Diskon Item</label>
                <div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="items[{{ $index }}][discount_amount]" class="form-control discount-amount" value="{{ old("items.{$index}.discount_amount", 0) }}" min="0" step="1000"></div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Pegawai Produksi</label>
                <select name="items[{{ $index }}][assigned_employee_id]" class="form-select">
                    <option value="">— Belum ditugaskan —</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old("items.{$index}.assigned_employee_id") == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Target Selesai Item</label>
                <input type="date" name="items[{{ $index }}][target_date]" class="form-control target-date" value="{{ old("items.{$index}.target_date", $dueDateDefault ?? now()->addDays(14)->toDateString()) }}">
            </div>

            <div class="col-12">
                <label class="form-label">Catatan Item</label>
                <input type="text" name="items[{{ $index }}][custom_notes]" class="form-control" value="{{ old("items.{$index}.custom_notes") }}" placeholder="Finishing warna, ukiran, atau permintaan khusus...">
            </div>

            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 rounded-3" style="background:#fbf6ee;border:1px dashed #e2d2be;">
                    <div class="small text-muted-2">
                        <i class="bi bi-calculator me-1"></i>
                        Luas: <span class="preview-area fw-semibold">0</span> m² ·
                        Faktor: <span class="preview-factor fw-semibold">1.00</span>
                    </div>
                    <div class="text-end">
                        <div class="text-muted-2" style="font-size:.66rem;">Estimasi harga item</div>
                        <div class="fw-bold text-brown preview-price">Rp 0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
