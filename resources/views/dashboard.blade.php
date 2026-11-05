@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan pesanan, produksi, dan stok hari ini')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">Selamat datang, {{ auth()->user()->name }} 👋</h2>
            <p class="kk-page-sub">Berikut kondisi workshop per {{ now()->translatedFormat('d F Y') }}.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('orders.create') }}" class="btn btn-kk"><i class="bi bi-plus-lg me-1"></i> Pesanan Baru</a>
                <a href="{{ route('inventory.stock-in.create') }}" class="btn btn-outline-kk"><i class="bi bi-box-arrow-in-down me-1"></i> Stok Masuk</a>
            @else
                <a href="{{ route('production.index') }}" class="btn btn-kk"><i class="bi bi-hammer me-1"></i> Buka Papan Produksi</a>
            @endif
        </div>
    </div>

    @if($stats['overdue_orders'] > 0 && auth()->user()->isAdmin())
        <div class="alert border-0 d-flex align-items-center gap-3 mb-4" style="border-radius:16px;background:#f7e4e1;color:#8d3d34;">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div class="flex-grow-1">
                <div class="fw-bold">Ada {{ $stats['overdue_orders'] }} pesanan melewati batas pelunasan.</div>
                <div class="small">Pesanan selesai produksi tetapi belum lunas. Pemilik dapat mengalihkan ke pesanan berikutnya atau stok jadi.</div>
            </div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('orders.index', ['overdue' => 1]) }}" class="btn btn-sm btn-outline-danger">Lihat</a>
            @endif
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <x-stat-card label="Pesanan Aktif" :value="$stats['active_orders']" icon="bi-receipt-cutoff" color="amber" hint="Belum selesai / diambil" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Item Dikerjakan" :value="$stats['in_progress_items']" icon="bi-hammer" color="blue" hint="Produksi, finishing, QC" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Stok Menipis" :value="$stats['low_stock']" icon="bi-exclamation-diamond" color="red" hint="Stok ≤ stok minimum" />
        </div>
        @if(auth()->user()->isAdmin())
            <div class="col-6 col-xl-3">
                <x-stat-card label="Penerimaan Bulan Ini" value="Rp {{ number_format($stats['revenue_month'], 0, ',', '.') }}" icon="bi-cash-stack" color="green" hint="Total pembayaran tercatat" />
            </div>
        @endif
    </div>

    <div class="row g-4">
        @if(auth()->user()->isAdmin())
            <div class="col-xl-8">
            <div class="kk-card mb-4">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Tren Penerimaan</h3>
                        <p class="kk-card-sub">Pembayaran 6 bulan terakhir</p>
                    </div>
                    <a href="{{ route('reports.index', ['type' => 'sales']) }}" class="btn btn-sm btn-outline-kk">Lihat laporan <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="kk-card-body">
                    <canvas id="salesChart" height="110"></canvas>
                </div>
            </div>

            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Pesanan Aktif</h3>
                        <p class="kk-card-sub">Diurutkan berdasarkan tenggat terdekat</p>
                    </div>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-kk">Semua pesanan</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Tenggat</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('orders.show', $order) }}" class="fw-bold text-brown">{{ $order->order_no }}</a>
                                        <div class="text-muted-2" style="font-size:.7rem;">{{ $order->items->count() }} item · {{ $order->channel === 'whatsapp' ? 'WhatsApp' : 'Walk-in' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $order->customer?->name }}</div>
                                        <div class="text-muted-2" style="font-size:.7rem;">{{ $order->customer?->phone }}</div>
                                    </td>
                                    <td>
                                        <div class="{{ $order->due_date?->isPast() ? 'text-danger fw-bold' : '' }}">{{ $order->due_date?->format('d/m/Y') }}</div>
                                        <div class="text-muted-2" style="font-size:.7rem;">Batas bayar {{ $order->payment_deadline?->format('d/m/Y') ?? '-' }}</div>
                                    </td>
                                    <td><x-status-badge :status="$order->status" /></td>
                                    <td class="text-end fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><x-empty-state icon="bi-receipt" title="Belum ada pesanan aktif" text="Buat pesanan baru untuk mulai mengisi antrian produksi." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        @endif

        <div class="{{ auth()->user()->isAdmin() ? 'col-xl-4' : 'col-xl-12' }}">
            <div class="kk-card mb-4">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Antrian Produksi</h3>
                        <p class="kk-card-sub">{{ $productionItems->count() }} item sedang dipantau</p>
                    </div>
                    <a href="{{ route('production.index') }}" class="btn btn-sm btn-outline-kk">Papan</a>
                </div>
                <div class="kk-card-body pt-3">
                    @forelse($productionItems as $item)
                        <div class="d-flex gap-3 pb-3 mb-3 border-bottom" style="border-color:#f2ebe1 !important;">
                            <div class="kk-avatar" style="border-radius:12px;background:#f3ece2;">{{ strtoupper(substr($item->item_name, 0, 1)) }}</div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between gap-2">
                                    <div class="fw-semibold text-truncate">{{ $item->item_name }}</div>
                                    <x-status-badge :status="$item->status" type="item" />
                                </div>
                                <div class="text-muted-2" style="font-size:.7rem;">{{ $item->order?->order_no }} · {{ $item->order?->customer?->name }}</div>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="kk-progress flex-grow-1"><div class="kk-progress-bar" style="width: {{ $item->progress_percent }}%"></div></div>
                                    <span class="text-muted-2" style="font-size:.65rem;">{{ $item->progress_percent }}%</span>
                                </div>
                                <div class="text-muted-2 mt-1" style="font-size:.66rem;"><i class="bi bi-person me-1"></i>{{ $item->assignedEmployee?->name ?? 'Belum ditugaskan' }} · target {{ $item->target_date?->format('d/m') ?? '-' }}</div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state icon="bi-hammer" title="Antrian kosong" text="Belum ada item produksi yang aktif." />
                    @endforelse
                </div>
            </div>

            <div class="kk-card mb-4">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Stok Menipis</h3>
                        <p class="kk-card-sub">Perlu segera dibeli</p>
                    </div>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-kk">Stok</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            @forelse($lowStock as $material)
                                <tr>
                                    <td>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('materials.show', $material) }}" class="fw-semibold text-brown">{{ $material->name }}</a>
                                        @else
                                            <span class="fw-semibold text-brown">{{ $material->name }}</span>
                                        @endif
                                        <div class="text-muted-2" style="font-size:.68rem;">{{ $material->category?->name }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold {{ $material->stock_qty <= 0 ? 'text-danger' : 'text-warning' }}">{{ number_format($material->stock_qty, 2, ',', '.') }} {{ $material->unit?->code }}</div>
                                        <div class="text-muted-2" style="font-size:.66rem;">min {{ number_format($material->min_stock_qty, 2, ',', '.') }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td><x-empty-state icon="bi-check2-circle" title="Stok aman" text="Tidak ada bahan di bawah batas minimum." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="kk-card">
                <div class="kk-card-header">
                    <div>
                        <h3 class="kk-card-title">Beban Pegawai</h3>
                        <p class="kk-card-sub">Item aktif per pegawai</p>
                    </div>
                </div>
                <div class="kk-card-body pt-3">
                    @forelse($employeeWorkload as $employee)
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="kk-avatar">{{ strtoupper(substr($employee->name, 0, 1)) }}</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">{{ $employee->name }}</div>
                                <div class="kk-progress mt-1"><div class="kk-progress-bar" style="width: {{ min(100, $employee->active_items_count * 20) }}%"></div></div>
                            </div>
                            <span class="badge-soft badge-soft-secondary">{{ $employee->active_items_count }} item</span>
                        </div>
                    @empty
                        <x-empty-state icon="bi-people" title="Belum ada pegawai" text="Tambahkan akun pegawai di menu Pengguna." />
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@if(auth()->user()->isAdmin())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const salesCanvas = document.getElementById('salesChart');
    if (salesCanvas) {
        const ctx = salesCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(201, 138, 75, .32)');
        gradient.addColorStop(1, 'rgba(201, 138, 75, .02)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($salesChart['labels']),
                datasets: [{
                    label: 'Penerimaan',
                    data: @json($salesChart['values']),
                    borderColor: '#a96732',
                    backgroundColor: gradient,
                    fill: true,
                    tension: .38,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#a96732',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y)
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#8a7668', font: { size: 10 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(231, 220, 205, .6)' },
                        ticks: {
                            color: '#8a7668',
                            font: { size: 10 },
                            callback: (value) => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value)
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
@endif
