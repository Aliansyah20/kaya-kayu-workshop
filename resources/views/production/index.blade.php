@extends('layouts.app')

@section('title', 'Papan Produksi')
@section('page-title', 'Papan Produksi')
@section('page-subtitle', 'Pantau antrian dan progres pengerjaan item')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-hammer me-2 text-brown"></i>Papan Produksi</h2>
            <p class="kk-page-sub">Status item diperbarui oleh Pegawai, konfirmasi selesai oleh Pemilik.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge-soft badge-soft-success align-self-center"><i class="bi bi-check2-circle"></i> {{ $completedToday }} selesai hari ini</span>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('orders.index') }}" class="btn btn-outline-kk">Lihat Pesanan</a>
            @endif
        </div>
    </div>

    <div class="kk-card mb-4">
        <div class="kk-card-body py-3">
            <form class="d-flex gap-2 flex-wrap" method="GET">
                <div class="input-group" style="min-width:240px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari pesanan / item / pelanggan...">
                </div>
                <select name="employee" class="form-select" style="width:auto;">
                    <option value="">Semua pegawai</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(request('employee') == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select" style="width:auto;">
                    <option value="">Semua kolom</option>
                    @foreach($columns as $status => $meta)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline-kk" type="submit">Filter</button>
                @if(request()->hasAny(['q', 'employee', 'status']))
                    <a href="{{ route('production.index') }}" class="btn btn-outline-kk">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="kk-kanban">
        @foreach($columns as $status => $meta)
            <div class="kk-kanban-col">
                <div class="kk-kanban-head">
                    <span><i class="bi {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}</span>
                    <span class="badge-soft badge-soft-{{ $meta['color'] }}">{{ $grouped[$status]->count() }}</span>
                </div>

                @forelse($grouped[$status] as $item)
                    @php
                        $nextMap = [
                            'queued' => ['in_progress' => 'Mulai Produksi', 'cancelled' => 'Batalkan'],
                            'in_progress' => ['finishing' => 'Mulai Finishing', 'cancelled' => 'Batalkan'],
                            'finishing' => ['qc' => 'Ajukan QC', 'cancelled' => 'Batalkan'],
                            'qc' => ['done' => 'Selesai (Pemilik)', 'cancelled' => 'Batalkan'],
                        ];
                        $options = $nextMap[$item->status] ?? [];
                        if (! auth()->user()->isOwner()) {
                            unset($options['done']);
                        }
                        $canUpdate = !auth()->user()->isEmployee() || $item->assigned_employee_id === auth()->id();
                    @endphp

                    <div class="kk-kanban-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-w-0">
                                <div class="fw-bold small text-truncate">{{ $item->item_name }}</div>
                                <div class="text-muted-2" style="font-size:.68rem;">
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('orders.show', $item->order) }}" class="text-muted-2">{{ $item->order?->order_no }}</a>
                                    @else
                                        <span>{{ $item->order?->order_no }}</span>
                                    @endif
                                    · {{ $item->order?->customer?->name }}
                                </div>
                            </div>
                            <span class="badge-soft badge-soft-{{ $meta['color'] }}" style="font-size:.6rem;">{{ $item->quantity }} unit</span>
                        </div>

                        <div class="d-flex align-items-center gap-2 mt-2">
                            <div class="kk-progress flex-grow-1"><div class="kk-progress-bar" style="width: {{ $item->progress_percent }}%"></div></div>
                            <span class="text-muted-2" style="font-size:.62rem;">{{ $item->progress_percent }}%</span>
                        </div>

                        <div class="d-flex justify-content-between text-muted-2 mt-2" style="font-size:.66rem;">
                            <span><i class="bi bi-person me-1"></i>{{ $item->assignedEmployee?->name ?? 'Belum ditugaskan' }}</span>
                            <span><i class="bi bi-calendar3 me-1"></i>{{ $item->target_date?->format('d/m') ?? '-' }}</span>
                        </div>

                        @if($item->materials->isNotEmpty())
                            <div class="mt-2 pt-2 border-top" style="border-color:#f0e8dd !important;">
                                @foreach($item->materials as $material)
                                    <div class="d-flex justify-content-between" style="font-size:.62rem;">
                                        <span class="text-muted-2 text-truncate">{{ $material->material?->name }}</span>
                                        <span class="{{ $material->material?->isLowStock() ? 'text-danger fw-bold' : 'text-muted-2' }}">{{ number_format($material->planned_qty, 2, ',', '.') }} {{ $material->unit?->code }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($canUpdate && $options)
                            <form method="POST" action="{{ route('production.status', $item) }}" class="mt-2">
                                @csrf
                                <div class="d-flex gap-1">
                                    <select name="status" class="form-select form-select-sm" style="font-size:.7rem;">
                                        @foreach($options as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-kk" title="Perbarui status"><i class="bi bi-arrow-right"></i></button>
                                </div>
                                <input type="text" name="notes" class="form-control form-control-sm mt-1" style="font-size:.68rem;" placeholder="Catatan progres (opsional)">
                            </form>
                        @elseif(!$canUpdate)
                            <div class="mt-2 text-muted-2" style="font-size:.64rem;"><i class="bi bi-lock me-1"></i>Ditugaskan ke pegawai lain</div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted-2 py-4" style="font-size:.72rem;">
                        <i class="bi bi-inbox d-block fs-4 mb-1"></i>Tidak ada item
                    </div>
                @endforelse
            </div>
        @endforeach
    </div>
@endsection
