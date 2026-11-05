@extends('layouts.app')

@section('title', 'Pesanan')
@section('page-title', 'Pesanan')
@section('page-subtitle', 'Pesanan dari WhatsApp, walk-in, dan telepon')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-receipt-cutoff me-2 text-brown"></i>Pesanan</h2>
            <p class="kk-page-sub">Satu pesanan dapat berisi beberapa item dengan satu tenggat kesepakatan.</p>
        </div>
        <a href="{{ route('orders.create') }}" class="btn btn-kk"><i class="bi bi-plus-lg me-1"></i> Pesanan Baru</a>
    </div>

    @if(request('overdue'))
        <div class="alert border-0 d-flex align-items-center gap-2 mb-4" style="border-radius:14px;background:#f7e4e1;color:#8d3d34;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div class="small">Menampilkan pesanan yang melewati batas pelunasan.</div>
            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-danger ms-auto">Tampilkan semua</a>
        </div>
    @endif

    <div class="kk-card mb-4">
        <div class="kk-card-body py-3">
            <form class="d-flex gap-2 flex-wrap" method="GET">
                <div class="input-group" style="min-width:260px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari no. pesanan / pelanggan...">
                </div>
                <select name="status" class="form-select" style="width:auto;">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="payment_status" class="form-select" style="width:auto;">
                    <option value="">Semua pembayaran</option>
                    @foreach($paymentStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="btn btn-outline-kk d-flex align-items-center gap-2 mb-0">
                    <input type="checkbox" name="overdue" value="1" class="form-check-input mt-0" @checked(request('overdue'))>
                    Jatuh tempo
                </label>
                <button class="btn btn-outline-kk" type="submit">Filter</button>
                @if(request()->hasAny(['q', 'status', 'payment_status', 'overdue']))
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-kk">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="kk-card">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Tenggat</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('orders.show', $order) }}" class="fw-bold text-brown">{{ $order->order_no }}</a>
                                <div class="text-muted-2" style="font-size:.68rem;">{{ $order->items_count }} item · {{ $order->channel === 'whatsapp' ? 'WhatsApp' : ($order->channel === 'walk_in' ? 'Walk-in' : ucfirst($order->channel)) }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $order->customer?->name }}</div>
                                <div class="text-muted-2" style="font-size:.68rem;">{{ $order->customer?->phone }}</div>
                            </td>
                            <td class="small">{{ $order->order_date?->format('d/m/Y') }}</td>
                            <td>
                                <div class="small {{ $order->due_date?->isPast() && !in_array($order->status, ['delivered', 'cancelled', 'transferred']) ? 'text-danger fw-bold' : '' }}">{{ $order->due_date?->format('d/m/Y') }}</div>
                                <div class="text-muted-2" style="font-size:.66rem;">Batas bayar {{ $order->payment_deadline?->format('d/m/Y') ?? '-' }}</div>
                            </td>
                            <td><x-status-badge :status="$order->status" /></td>
                            <td><x-status-badge :status="$order->payment_status" type="payment" /></td>
                            <td class="text-end fw-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="text-end {{ $order->remaining_amount > 0 ? 'text-danger' : 'text-success' }} fw-semibold">Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-kk btn-icon" title="Detail"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('orders.invoice', $order) }}" class="btn btn-sm btn-outline-kk btn-icon" title="Invoice PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><x-empty-state icon="bi-receipt" title="Belum ada pesanan" text="Buat pesanan pertama untuk mulai mengelola produksi." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-3 border-top" style="border-color:#f0e8dd !important;">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
