@extends('layouts.app')

@section('title', $order->order_no)
@section('page-title', 'Detail Pesanan')
@section('page-subtitle', $order->order_no . ' · ' . ($order->customer?->name ?? 'Tanpa pelanggan'))

@section('content')
    <div class="kk-page-head">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h2 class="kk-page-title">{{ $order->order_no }}</h2>
                <x-status-badge :status="$order->status" />
                <x-status-badge :status="$order->payment_status" type="payment" />
            </div>
            <p class="kk-page-sub">{{ $order->customer?->name }} · {{ $order->order_date?->format('d/m/Y') }} · {{ $order->channel === 'whatsapp' ? 'WhatsApp' : ucfirst($order->channel) }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            <a href="{{ route('orders.wa', $order) }}" target="_blank" class="btn btn-outline-kk"><i class="bi bi-whatsapp me-1"></i> WhatsApp</a>
            <a href="{{ route('orders.invoice', $order) }}" class="btn btn-kk"><i class="bi bi-file-earmark-pdf me-1"></i> Invoice PDF</a>
        </div>
    </div>

    @if($order->status === 'pending_approval' && auth()->user()->isOwner())
        <div class="alert border-0 d-flex align-items-center gap-3 mb-4" style="border-radius:16px;background:#f8ecd7;color:#8b5e24;">
            <i class="bi bi-hourglass-split fs-5"></i>
            <div class="flex-grow-1">
                <div class="fw-bold">Pesanan menunggu approval Pemilik.</div>
                <div class="small">Setujui harga dan pesanan untuk memindahkannya ke antrian produksi.</div>
            </div>
            <form method="POST" action="{{ route('orders.approve', $order) }}">
                @csrf
                <button class="btn btn-kk" type="submit"><i class="bi bi-check2-circle me-1"></i> Setujui Pesanan</button>
            </form>
        </div>
    @endif

    @if($order->payment_status === 'overdue' || ($order->payment_deadline && $order->payment_deadline->copy()->endOfDay()->isPast() && $order->payment_status !== 'paid'))
        <div class="alert border-0 d-flex align-items-center gap-3 mb-4" style="border-radius:16px;background:#f7e4e1;color:#8d3d34;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div class="flex-grow-1">
                <div class="fw-bold">Batas pelunasan sudah lewat.</div>
                <div class="small">Pemilik dapat mengalihkan item ke pesanan berikutnya atau menjadikannya stok produk jadi. DP hangus sesuai kesepakatan.</div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="kk-card mb-4">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Item Pesanan</h3>
                        <p class="kk-card-sub">{{ $order->items->count() }} item · total Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="kk-card-body">
                    @forelse($order->items as $item)
                        <div class="p-3 rounded-4 mb-3" style="background:#fcf8f2;border:1px solid #eee3d5;">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <h4 class="h6 fw-bold mb-0">{{ $item->item_name }}</h4>
                                        <x-status-badge :status="$item->status" type="item" />
                                    </div>
                                    <div class="text-muted-2 small mt-1">
                                        {{ $item->productModel?->name ?? 'Produk custom' }}
                                        @if($item->assignedEmployee) · Pegawai: {{ $item->assignedEmployee->name }} @endif
                                        @if($item->target_date) · Target: {{ $item->target_date->format('d/m/Y') }} @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted-2" style="font-size:.66rem;">Harga item</div>
                                    <div class="fw-bold text-brown">Rp {{ number_format($item->final_price, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <div class="row g-2 mt-3">
                                <div class="col-6 col-md-2">
                                    <div class="text-muted-2" style="font-size:.64rem;">Qty</div>
                                    <div class="fw-semibold small">{{ $item->quantity }}</div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="text-muted-2" style="font-size:.64rem;">Luas</div>
                                    <div class="fw-semibold small">{{ number_format($item->area_m2, 2, ',', '.') }} m²</div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="text-muted-2" style="font-size:.64rem;">Ukuran</div>
                                    <div class="fw-semibold small">{{ $item->length_cm ?: '-' }} × {{ $item->width_cm ?: '-' }} × {{ $item->height_cm ?: '-' }} cm</div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="text-muted-2" style="font-size:.64rem;">Kayu</div>
                                    <div class="fw-semibold small">{{ $item->woodType?->name ?? '-' }} / {{ $item->woodGrade?->name ?? '-' }}</div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="text-muted-2" style="font-size:.64rem;">Kesulitan</div>
                                    <div class="fw-semibold small">{{ $item->complexity?->name ?? '-' }}</div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="text-muted-2" style="font-size:.64rem;">Faktor</div>
                                    <div class="fw-semibold small">{{ number_format($item->complexity_factor * $item->wood_type_factor * $item->wood_grade_factor, 2, ',', '.') }}×</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-3">
                                <div class="kk-progress flex-grow-1"><div class="kk-progress-bar" style="width: {{ $item->progress_percent }}%"></div></div>
                                <span class="text-muted-2" style="font-size:.68rem;">{{ $item->progress_percent }}%</span>
                            </div>

                            @if($item->custom_notes)
                                <div class="mt-2 small text-muted-2"><i class="bi bi-chat-left-text me-1"></i>{{ $item->custom_notes }}</div>
                            @endif

                            @if($item->materials->isNotEmpty())
                                <div class="mt-3 pt-3 border-top" style="border-color:#eee3d5 !important;">
                                    <div class="text-muted-2 fw-bold mb-2" style="font-size:.68rem;letter-spacing:.06em;text-transform:uppercase;">Kebutuhan Bahan (BOM Snapshot)</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Bahan</th>
                                                    <th class="text-end">Rencana</th>
                                                    <th class="text-end">Aktual</th>
                                                    <th>Satuan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($item->materials as $material)
                                                    <tr>
                                                        <td class="small">{{ $material->material?->name }}</td>
                                                        <td class="text-end small">{{ number_format($material->planned_qty, 4, ',', '.') }}</td>
                                                        <td class="text-end small">{{ number_format($material->actual_qty, 4, ',', '.') }}</td>
                                                        <td class="small">{{ $material->unit?->code }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <x-empty-state icon="bi-receipt" title="Tidak ada item" text="Pesanan ini belum memiliki item." />
                    @endforelse
                </div>
            </div>

            <div class="kk-card mb-4">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Riwayat Pembayaran</h3>
                        <p class="kk-card-sub">Total dibayar Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Metode</th>
                                <th class="text-end">Nominal</th>
                                <th>Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->payments->sortByDesc('paid_at') as $payment)
                                <tr>
                                    <td class="small"><code>{{ $payment->payment_no }}</code></td>
                                    <td class="small">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge-soft badge-soft-info">{{ $payment->type_label }}</span></td>
                                    <td class="small">{{ ucfirst($payment->payment_method) }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td class="small">{{ $payment->receivedBy?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><x-empty-state icon="bi-cash-stack" title="Belum ada pembayaran" text="DP dan pelunasan akan tercatat di sini." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Riwayat Status</h3>
                        <p class="kk-card-sub">Jejak perubahan status pesanan dan item</p>
                    </div>
                </div>
                <div class="kk-card-body">
                    <div class="kk-timeline">
                        @forelse($order->statusHistories->sortByDesc('created_at') as $history)
                            <div class="kk-timeline-item">
                                <div class="d-flex justify-content-between gap-2 flex-wrap">
                                    <div>
                                        <div class="fw-semibold small">
                                            {{ $history->status_from ? ucwords(str_replace('_', ' ', $history->status_from)) . ' → ' : '' }}{{ ucwords(str_replace('_', ' ', $history->status_to)) }}
                                        </div>
                                        <div class="text-muted-2" style="font-size:.7rem;">
                                            {{ $history->orderItem?->item_name ? 'Item: ' . $history->orderItem->item_name . ' · ' : '' }}
                                            oleh {{ $history->changedBy?->name ?? 'Sistem' }}
                                        </div>
                                        @if($history->notes)
                                            <div class="text-muted-2 small mt-1">{{ $history->notes }}</div>
                                        @endif
                                    </div>
                                    <div class="text-muted-2" style="font-size:.68rem;">{{ $history->created_at?->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted-2 small">Belum ada riwayat status.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="kk-card mb-4">
                <div class="kk-card-header"><h3 class="kk-card-title">Ringkasan Pembayaran</h3></div>
                <div class="kk-card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2">Total</span>
                        <span class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2">DP {{ number_format($order->dp_percent, 0) }}%</span>
                        <span class="fw-semibold">Rp {{ number_format($order->dp_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2">Sudah dibayar</span>
                        <span class="fw-semibold text-success">Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:#f2ebe1 !important;">
                        <span class="text-muted-2">Sisa</span>
                        <span class="fw-bold {{ $order->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted-2">Batas pelunasan</span>
                        <span class="fw-semibold {{ $order->payment_deadline?->copy()->endOfDay()->isPast() && $order->payment_status !== 'paid' ? 'text-danger' : '' }}">{{ $order->payment_deadline?->format('d/m/Y') ?? '-' }}</span>
                    </div>

                    @if($order->remaining_amount > 0 && !in_array($order->status, ['cancelled', 'transferred']))
                        <div class="kk-divider"></div>
                        <h4 class="h6 fw-bold mb-3">Catat Pembayaran</h4>
                        <form method="POST" action="{{ route('orders.payments.store', $order) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Nominal</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="amount" class="form-control" value="{{ $order->paid_amount == 0 ? (int) $order->dp_amount : (int) $order->remaining_amount }}" min="0.01" step="1" required></div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Tipe</label>
                                    <select name="payment_type" class="form-select">
                                        <option value="dp" @selected($order->paid_amount == 0)>DP</option>
                                        <option value="installment" @selected($order->paid_amount > 0)>Cicilan</option>
                                        <option value="settlement">Pelunasan</option>
                                        <option value="refund">Refund</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Metode</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="transfer">Transfer</option>
                                        <option value="cash">Tunai</option>
                                        <option value="qris">QRIS</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tanggal Bayar</label>
                                <input type="date" name="paid_at" class="form-control" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Bukti (opsional)</label>
                                <input type="file" name="proof" class="form-control" accept="image/*,.pdf">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="notes" class="form-control" placeholder="Transfer BCA a.n. ...">
                            </div>
                            <button type="submit" class="btn btn-kk w-100"><i class="bi bi-cash-coin me-1"></i> Simpan Pembayaran</button>
                        </form>
                    @endif
                </div>
            </div>

            @if($nextStatuses)
                <div class="kk-card mb-4">
                    <div class="kk-card-header"><h3 class="kk-card-title">Ubah Status Pesanan</h3></div>
                    <div class="kk-card-body">
                        <form method="POST" action="{{ route('orders.status', $order) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Status Berikutnya</label>
                                <select name="status" class="form-select" required>
                                    @foreach($nextStatuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="notes" class="form-control" placeholder="Alasan perubahan status...">
                            </div>
                            <button type="submit" class="btn btn-outline-kk w-100">Perbarui Status</button>
                        </form>
                    </div>
                </div>
            @endif

            @if($order->status === 'production_done' && $order->payment_status !== 'paid' && auth()->user()->isOwner() && $order->payment_deadline && $order->payment_deadline->copy()->endOfDay()->isFuture())
                <div class="alert border-0 d-flex align-items-center gap-3 mb-4" style="border-radius:16px;background:#f8ecd7;color:#8b5e24;">
                    <i class="bi bi-clock-history fs-5"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Masa tenggang pelunasan masih berjalan.</div>
                        <div class="small">Pengalihan item baru dapat dilakukan setelah {{ $order->payment_deadline->format('d/m/Y') }} jika pesanan belum lunas.</div>
                    </div>
                </div>
            @endif

            @if($order->status === 'production_done' && $order->payment_status !== 'paid' && auth()->user()->isOwner() && (! $order->payment_deadline || $order->payment_deadline->copy()->endOfDay()->isPast()))
                <div class="kk-card mb-4">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Pengalihan Pesanan</h3>
                            <p class="kk-card-sub">DP hangus, item dialihkan</p>
                        </div>
                    </div>
                    <div class="kk-card-body">
                        <form method="POST" action="{{ route('orders.transfer', $order) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Item</label>
                                <select name="order_item_id" class="form-select" required>
                                    @foreach($order->items->where('status', 'done') as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->quantity }} unit)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tipe Pengalihan</label>
                                <select name="transfer_type" class="form-select" required>
                                    <option value="ready_stock">Jadikan stok produk jadi</option>
                                    <option value="next_order">Tautkan ke pesanan berikutnya</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Pesanan Berikutnya</label>
                                <select name="new_order_id" class="form-select">
                                    <option value="">— Tidak ditautkan —</option>
                                    @foreach($transferOrders as $nextOrder)
                                        <option value="{{ $nextOrder->id }}">{{ $nextOrder->order_no }} · {{ $nextOrder->customer?->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Wajib dipilih jika tipe pengalihan adalah pesanan berikutnya.</div>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="dp_forfeited" value="1" id="dp_forfeited" checked>
                                <label class="form-check-label small" for="dp_forfeited">DP hangus</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="notes" class="form-control" placeholder="Kesepakatan dengan pelanggan...">
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Yakin mengalihkan item ini?')">Alirkan Item</button>
                        </form>
                    </div>
                </div>
            @endif

            @if($order->transfers->isNotEmpty())
                <div class="kk-card mb-4">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Riwayat Pengalihan</h3>
                            <p class="kk-card-sub">Item yang dialihkan dari pesanan ini</p>
                        </div>
                    </div>
                    <div class="kk-card-body">
                        @foreach($order->transfers as $transfer)
                            @php
                                $transferType = match ($transfer->transfer_type) {
                                    'ready_stock' => 'Stok produk jadi',
                                    'next_order' => 'Pesanan berikutnya',
                                    default => 'Lainnya',
                                };
                                $transferTarget = match ($transfer->transfer_type) {
                                    'ready_stock' => $transfer->product?->name,
                                    'next_order' => $transfer->newOrder?->order_no,
                                    default => null,
                                };
                            @endphp
                            <div class="py-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:#f2ebe1 !important;">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold small">{{ $transfer->originalOrderItem?->item_name ?? 'Item' }}</div>
                                        <div class="text-muted-2" style="font-size:.68rem;">
                                            {{ $transferType }}
                                            @if($transferTarget) · {{ $transferTarget }} @endif
                                            · oleh {{ $transfer->transferredBy?->name ?? '-' }}
                                            · {{ $transfer->transferred_at?->format('d/m/Y H:i') }}
                                        </div>
                                        @if($transfer->notes)
                                            <div class="text-muted-2 small mt-1">{{ $transfer->notes }}</div>
                                        @endif
                                    </div>
                                    <span class="badge-soft badge-soft-{{ $transfer->dp_forfeited ? 'danger' : 'secondary' }}">{{ $transfer->dp_forfeited ? 'DP hangus' : 'DP tidak hangus' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="kk-card mb-4">
                <div class="kk-card-header"><h3 class="kk-card-title">Pelanggan</h3></div>
                <div class="kk-card-body">
                    <div class="d-flex align-items-center gap-2">
                        <div class="kk-avatar">{{ strtoupper(substr($order->customer?->name ?? '?', 0, 1)) }}</div>
                        <div>
                            <div class="fw-bold">{{ $order->customer?->name }}</div>
                            <div class="text-muted-2 small">{{ $order->customer?->phone }}</div>
                        </div>
                    </div>
                    @if($order->customer?->address)
                        <div class="text-muted-2 small mt-2"><i class="bi bi-geo-alt me-1"></i>{{ $order->customer->address }}</div>
                    @endif
                    @if($order->notes)
                        <div class="kk-divider"></div>
                        <div class="text-muted-2 small"><i class="bi bi-sticky me-1"></i>{{ $order->notes }}</div>
                    @endif
                </div>
            </div>

            @if($order->approvals->isNotEmpty())
                <div class="kk-card">
                    <div class="kk-card-header"><h3 class="kk-card-title">Approval</h3></div>
                    <div class="kk-card-body">
                        @foreach($order->approvals as $approval)
                            <div class="d-flex justify-content-between align-items-start py-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:#f2ebe1 !important;">
                                <div>
                                    <div class="fw-semibold small">{{ ucfirst($approval->approval_type) }}</div>
                                    <div class="text-muted-2" style="font-size:.68rem;">Diminta {{ $approval->requestedBy?->name }} · {{ $approval->requested_at?->format('d/m/Y H:i') }}</div>
                                </div>
                                <span class="badge-soft badge-soft-{{ $approval->status === 'approved' ? 'success' : ($approval->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($approval->status) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
