@extends('layouts.app')

@php
    $oldItems = old('items');
    $oldItems = is_array($oldItems) && count($oldItems) > 0 ? array_values($oldItems) : [[]];
@endphp

@section('title', 'Pesanan Baru')
@section('page-title', 'Pesanan Baru')
@section('page-subtitle', 'Catat pesanan dari WhatsApp atau walk-in')

@section('content')
    @php
        $dueDateDefault = now()->addDays(14)->toDateString();
        $modelData = $productModels->mapWithKeys(function ($model) {
            return [$model->id => [
                'name' => $model->name,
                'code' => $model->code,
                'category' => $model->category?->name ?? '-',
                'pricing_type' => $model->pricing_type,
                'measurement_type' => $model->default_measurement_type,
                'price_per_m2' => (float) $model->default_price_per_m2,
                'fixed_price' => (float) ($model->default_fixed_price ?? 0),
                'length' => (float) ($model->default_length_cm ?? 0),
                'width' => (float) ($model->default_width_cm ?? 0),
                'height' => (float) ($model->default_height_cm ?? 0),
                'complexity_id' => $model->default_complexity_level_id,
                'wood_type_id' => $model->default_wood_type_id,
                'wood_grade_id' => $model->default_wood_grade_id,
            ]];
        });
        $woodTypeFactors = $woodTypes->pluck('price_factor', 'id');
        $woodGradeFactors = $woodGrades->pluck('price_factor', 'id');
        $complexityFactors = $complexities->pluck('factor', 'id');
    @endphp

    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">Buat Pesanan Baru</h2>
            <p class="kk-page-sub">Harga dihitung otomatis dari luas, faktor kayu, dan tingkat kesulitan.</p>
        </div>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    @if($customers->isEmpty())
        <div class="alert border-0 mb-4" style="border-radius:14px;background:#f8ecd7;color:#8b5e24;">
            <i class="bi bi-info-circle me-1"></i> Belum ada pelanggan. <a href="{{ route('master.create', 'customers') }}" class="fw-bold">Tambah pelanggan dulu</a>.
        </div>
    @endif

    @if($productModels->isEmpty())
        <div class="alert border-0 mb-4" style="border-radius:14px;background:#f8ecd7;color:#8b5e24;">
            <i class="bi bi-info-circle me-1"></i> Belum ada model produk. Anda tetap bisa membuat produk custom, tetapi BOM tidak akan otomatis dibuat.
        </div>
    @endif

    <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
        @csrf

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="kk-card mb-4">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Informasi Pesanan</h3>
                            <p class="kk-card-sub">Pelanggan dan tenggat kesepakatan</p>
                        </div>
                    </div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="customer_id">Pelanggan <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                    <option value="">— Pilih Pelanggan —</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} · {{ $customer->phone }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text"><a href="{{ route('master.create', 'customers') }}" target="_blank">+ Tambah pelanggan baru</a></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="channel">Sumber Pesanan <span class="text-danger">*</span></label>
                                <select class="form-select" id="channel" name="channel" required>
                                    <option value="whatsapp" @selected(old('channel', 'whatsapp') === 'whatsapp')>WhatsApp</option>
                                    <option value="walk_in" @selected(old('channel') === 'walk_in')>Walk-in</option>
                                    <option value="phone" @selected(old('channel') === 'phone')>Telepon</option>
                                    <option value="other" @selected(old('channel') === 'other')>Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="order_date">Tanggal Pesanan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="order_date" name="order_date" value="{{ old('order_date', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="due_date">Tenggat Kesepakatan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', $dueDateDefault) }}" required>
                                <div class="form-text">Batas pelunasan otomatis {{ $graceDays }} hari setelah tenggat.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="notes">Catatan Pesanan</label>
                                <input type="text" class="form-control" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Contoh: kirim setelah tanggal 20...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="h5 fw-bold mb-1">Item Pesanan</h3>
                        <div class="text-muted-2 small">Satu pesanan dapat berisi beberapa produk.</div>
                    </div>
                    <button type="button" class="btn btn-amber" id="addItem"><i class="bi bi-plus-lg me-1"></i> Tambah Item</button>
                </div>

                <div id="itemsContainer">
                    @foreach($oldItems as $index => $oldItem)
                        @include('orders.partials.item-fields', [
                            'index' => $index,
                            'productModels' => $productModels,
                            'woodTypes' => $woodTypes,
                            'woodGrades' => $woodGrades,
                            'complexities' => $complexities,
                            'employees' => $employees,
                            'dueDateDefault' => $dueDateDefault,
                        ])
                    @endforeach
                </div>

                <template id="itemTemplate">
                    @include('orders.partials.item-fields', [
                        'index' => '__INDEX__',
                        'productModels' => $productModels,
                        'woodTypes' => $woodTypes,
                        'woodGrades' => $woodGrades,
                        'complexities' => $complexities,
                        'employees' => $employees,
                        'dueDateDefault' => $dueDateDefault,
                    ])
                </template>
            </div>

            <div class="col-xl-4">
                <div class="kk-card mb-4" style="position:sticky;top:92px;">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Ringkasan</h3>
                            <p class="kk-card-sub">Estimasi sebelum disimpan</p>
                        </div>
                    </div>
                    <div class="kk-card-body">
                        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                            <span class="text-muted-2">Jumlah item</span>
                            <span class="fw-semibold" id="summaryCount">1</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                            <span class="text-muted-2">Total pesanan</span>
                            <span class="fw-bold text-brown" id="summaryTotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                            <span class="text-muted-2">DP {{ number_format($dpPercent, 0) }}%</span>
                            <span class="fw-semibold" id="summaryDp">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                            <span class="text-muted-2">Sisa setelah DP</span>
                            <span class="fw-semibold" id="summaryRemaining">Rp 0</span>
                        </div>
                        <div class="p-3 rounded-3 mt-3" style="background:#fbf6ee;border:1px dashed #e2d2be;font-size:.75rem;color:#6b5647;">
                            <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i>Ketentuan</div>
                            DP {{ number_format($dpPercent, 0) }}% saat pesanan dikonfirmasi. Pelunasan sebelum pengambilan. Jika lewat {{ $graceDays }} hari dari tenggat, pesanan dapat dialihkan ke pelanggan berikutnya dan DP hangus.
                        </div>
                    </div>
                    <div class="kk-card-body border-top" style="border-color:#f0e8dd !important;">
                        <button type="submit" class="btn btn-kk w-100 btn-lg"><i class="bi bi-check2-circle me-1"></i> Simpan Pesanan</button>
                        <div class="text-muted-2 text-center mt-2" style="font-size:.7rem;">Pesanan Admin akan menunggu approval Pemilik.</div>
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
        const template = document.getElementById('itemTemplate');
        const addButton = document.getElementById('addItem');
        const modelData = @json($modelData);
        const woodTypeFactors = @json($woodTypeFactors);
        const woodGradeFactors = @json($woodGradeFactors);
        const complexityFactors = @json($complexityFactors);
        const dpPercent = {{ (float) $dpPercent }};
        let itemIndex = {{ count($oldItems) }};

        const rupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value || 0));

        function getCard(el) {
            return el.closest('.item-card');
        }

        function calculateCard(card) {
            if (!card) return;

            const pricingType = card.querySelector('.pricing-type')?.value || 'per_m2';
            const measurement = card.querySelector('.measurement-type')?.value || 'front_area';
            const length = parseFloat(card.querySelector('.length-input')?.value || 0) || 0;
            const width = parseFloat(card.querySelector('.width-input')?.value || 0) || 0;
            const height = parseFloat(card.querySelector('.height-input')?.value || 0) || 0;
            const quantity = parseInt(card.querySelector('.quantity-input')?.value || 1, 10) || 1;
            const unitPrice = parseFloat(card.querySelector('.unit-price')?.value || 0) || 0;
            const additional = parseFloat(card.querySelector('.additional-fee')?.value || 0) || 0;
            const discount = parseFloat(card.querySelector('.discount-amount')?.value || 0) || 0;
            const areaInput = card.querySelector('.area-input');

            let area = 0;
            if (measurement === 'front_area') {
                area = (width * height) / 10000;
            } else if (measurement === 'top_area') {
                area = (length * width) / 10000;
            } else if (measurement === 'surface_area') {
                area = (2 * ((length * width) + (length * height) + (width * height))) / 10000;
            } else {
                area = parseFloat(areaInput?.value || 0) || 0;
            }

            if (measurement !== 'manual' && areaInput) {
                areaInput.value = area.toFixed(4);
            }

            const complexity = parseFloat(complexityFactors[card.querySelector('.complexity-select')?.value] || 1) || 1;
            const woodType = parseFloat(woodTypeFactors[card.querySelector('.wood-type-select')?.value] || 1) || 1;
            const woodGrade = parseFloat(woodGradeFactors[card.querySelector('.wood-grade-select')?.value] || 1) || 1;
            const factor = complexity * woodType * woodGrade;

            const calculated = pricingType === 'per_m2'
                ? area * unitPrice * factor * quantity
                : unitPrice * quantity;
            const final = Math.max(0, calculated + additional - discount);

            card.dataset.finalPrice = final;
            card.querySelector('.preview-area').textContent = area.toFixed(2);
            card.querySelector('.preview-factor').textContent = factor.toFixed(2);
            card.querySelector('.preview-price').textContent = rupiah(final);

            updateSummary();
        }

        function updateSummary() {
            let total = 0;
            let count = 0;
            document.querySelectorAll('.item-card').forEach((card) => {
                total += parseFloat(card.dataset.finalPrice || 0);
                count++;
            });

            const dp = total * dpPercent / 100;
            document.getElementById('summaryCount').textContent = count;
            document.getElementById('summaryTotal').textContent = rupiah(total);
            document.getElementById('summaryDp').textContent = rupiah(dp);
            document.getElementById('summaryRemaining').textContent = rupiah(total - dp);
        }

        function applyModel(card) {
            const modelId = card.querySelector('.model-select')?.value;
            const data = modelData[modelId];
            if (!data) {
                card.querySelector('.item-summary').textContent = 'Produk custom';
                calculateCard(card);
                return;
            }

            card.querySelector('.item-name').value = data.name;
            card.querySelector('.pricing-type').value = data.pricing_type;
            card.querySelector('.measurement-type').value = data.measurement_type;
            card.querySelector('.length-input').value = data.length || '';
            card.querySelector('.width-input').value = data.width || '';
            card.querySelector('.height-input').value = data.height || '';
            card.querySelector('.unit-price').value = data.pricing_type === 'fixed' ? data.fixed_price : data.price_per_m2;
            card.querySelector('.wood-type-select').value = data.wood_type_id || '';
            card.querySelector('.wood-grade-select').value = data.wood_grade_id || '';
            card.querySelector('.complexity-select').value = data.complexity_id || '';
            card.querySelector('.area-input').readOnly = data.measurement_type !== 'manual';
            card.querySelector('.item-summary').textContent = data.code + ' · ' + data.category;

            calculateCard(card);
        }

        addButton?.addEventListener('click', function () {
            const html = template.innerHTML.replaceAll('__INDEX__', itemIndex);
            container.insertAdjacentHTML('beforeend', html);
            const card = container.lastElementChild;
            card.querySelector('.item-number').textContent = itemIndex + 1;
            itemIndex++;
            calculateCard(card);
        });

        container?.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-item');
            if (!removeButton) return;

            if (container.querySelectorAll('.item-card').length <= 1) {
                alert('Minimal satu item dalam pesanan.');
                return;
            }

            getCard(removeButton)?.remove();
            updateSummary();
        });

        container?.addEventListener('change', function (event) {
            const card = getCard(event.target);
            if (!card) return;

            if (event.target.matches('.model-select')) {
                applyModel(card);
            } else if (event.target.matches('.measurement-type')) {
                const manual = event.target.value === 'manual';
                card.querySelector('.area-input').readOnly = !manual;
                calculateCard(card);
            } else if (event.target.matches('.pricing-type, .wood-type-select, .wood-grade-select, .complexity-select')) {
                calculateCard(card);
            }
        });

        container?.addEventListener('input', function (event) {
            const card = getCard(event.target);
            if (!card) return;
            if (event.target.matches('.length-input, .width-input, .height-input, .area-input, .quantity-input, .unit-price, .additional-fee, .discount-amount')) {
                calculateCard(card);
            }
        });

        document.querySelectorAll('.item-card').forEach((card) => {
            const areaInput = card.querySelector('.area-input');
            if (areaInput) {
                areaInput.readOnly = card.querySelector('.measurement-type')?.value !== 'manual';
            }
            calculateCard(card);
        });
    })();
</script>
@endpush
