@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')
@section('page-subtitle', 'Stok, mutasi, pesanan, pembayaran, dan produksi')

@section('content')
    @php
        $tabs = [
            'stock' => ['label' => 'Stok Bahan', 'icon' => 'bi-box-seam'],
            'movements' => ['label' => 'Mutasi Stok', 'icon' => 'bi-arrow-left-right'],
            'orders' => ['label' => 'Pesanan', 'icon' => 'bi-receipt-cutoff'],
            'sales' => ['label' => 'Pembayaran', 'icon' => 'bi-cash-stack'],
            'production' => ['label' => 'Produksi', 'icon' => 'bi-hammer'],
        ];
    @endphp

    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-file-earmark-bar-graph me-2 text-brown"></i>Laporan</h2>
            <p class="kk-page-sub">Unduh laporan PDF atau Excel untuk kebutuhan evaluasi dan arsip.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('reports.pdf', request()->query()) }}" class="btn btn-outline-kk"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</a>
            <a href="{{ route('reports.excel', request()->query()) }}" class="btn btn-kk"><i class="bi bi-file-earmark-excel me-1"></i> Excel</a>
        </div>
    </div>

    <div class="kk-card mb-4">
        <div class="kk-card-body py-3">
            <ul class="nav kk-tabs gap-1 flex-wrap">
                @foreach($tabs as $value => $tab)
                    <li class="nav-item">
                        <a class="nav-link {{ $type === $value ? 'active' : '' }}"
                           href="{{ route('reports.index', array_merge(request()->query(), ['type' => $value])) }}">
                            <i class="bi {{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="kk-card mb-4">
        <div class="kk-card-body py-3">
            <form class="d-flex gap-2 flex-wrap align-items-end" method="GET">
                <input type="hidden" name="type" value="{{ $type }}">
                <div>
                    <label class="form-label mb-1">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control">
                </div>
                <div>
                    <label class="form-label mb-1">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control">
                </div>
                @if(in_array($type, ['movements', 'production']))
                    <div>
                        <label class="form-label mb-1">Status / Bahan</label>
                        @if($type === 'movements')
                            <select name="material_id" class="form-select" style="min-width:180px;">
                                <option value="">Semua bahan</option>
                                @foreach($materials as $material)
                                    <option value="{{ $material->id }}" @selected(request('material_id') == $material->id)>{{ $material->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <select name="status" class="form-select" style="min-width:180px;">
                                <option value="">Semua status</option>
                                <option value="queued" @selected(request('status') === 'queued')>Antrian</option>
                                <option value="in_progress" @selected(request('status') === 'in_progress')>Dikerjakan</option>
                                <option value="finishing" @selected(request('status') === 'finishing')>Finishing</option>
                                <option value="qc" @selected(request('status') === 'qc')>QC</option>
                                <option value="done" @selected(request('status') === 'done')>Selesai</option>
                            </select>
                        @endif
                    </div>
                @endif
                @if($type === 'orders')
                    <div>
                        <label class="form-label mb-1">Status Pesanan</label>
                        <select name="status" class="form-select" style="min-width:180px;">
                            <option value="">Semua status</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button class="btn btn-outline-kk" type="submit"><i class="bi bi-funnel me-1"></i> Terapkan</button>
                <a href="{{ route('reports.index', ['type' => $type]) }}" class="btn btn-outline-kk">Reset</a>
            </form>
            @if($type === 'stock')
                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Laporan stok menampilkan saldo terkini, bukan berdasarkan rentang tanggal.</div>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($summary as $label => $value)
            <div class="col-6 col-xl-3">
                <div class="kk-stat">
                    <div class="kk-stat-label">{{ $label }}</div>
                    <div class="kk-stat-value" style="font-size:1.25rem;">{{ $value }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="kk-card">
        <div class="kk-card-header">
            <div>
                <h3 class="kk-card-title">{{ $title }}</h3>
                <p class="kk-card-sub">
                    @if($type === 'stock')
                        Data per {{ now()->format('d/m/Y H:i') }}
                    @else
                        Periode {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}
                    @endif
                </p>
            </div>
            <span class="badge-soft badge-soft-secondary">{{ count($rows) }} baris</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td class="small">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($headers) }}"><x-empty-state icon="bi-file-earmark-bar-graph" title="Tidak ada data" text="Tidak ada data untuk filter dan periode yang dipilih." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
