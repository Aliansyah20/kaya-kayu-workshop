@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas')
@section('page-subtitle', 'Jejak perubahan data dan aksi pengguna')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-clock-history me-2 text-brown"></i>Log Aktivitas</h2>
            <p class="kk-page-sub">Audit trail untuk memantau perubahan penting di sistem.</p>
        </div>
        <span class="badge-soft badge-soft-secondary align-self-center">{{ $logs->total() }} catatan</span>
    </div>

    <div class="kk-card">
        <div class="kk-card-header">
            <form class="d-flex gap-2 flex-wrap align-items-end" method="GET">
                <div class="input-group" style="min-width:220px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari deskripsi / aksi...">
                </div>
                <div>
                    <label class="form-label small mb-1">Modul</label>
                    <select name="module" class="form-select" style="min-width:140px;">
                        <option value="">Semua modul</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" @selected(request('module') === $module)>{{ ucfirst($module) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Pengguna</label>
                    <select name="user" class="form-select" style="min-width:160px;">
                        <option value="">Semua pengguna</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user') === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Dari</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div>
                    <label class="form-label small mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <button class="btn btn-outline-kk" type="submit">Filter</button>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Modul</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $actionColor = match ($log->action) {
                                'delete' => 'danger',
                                'create', 'store', 'upsert' => 'success',
                                'payment' => 'info',
                                'transfer' => 'warning',
                                'status', 'production_status' => 'dark',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td class="text-muted-2" style="white-space:nowrap;">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <div class="fw-semibold small">{{ $log->user?->name ?? 'Sistem' }}</div>
                                <div class="text-muted-2" style="font-size:.68rem;">{{ $log->ip_address ?: '-' }}</div>
                            </td>
                            <td><span class="badge-soft badge-soft-secondary">{{ ucfirst($log->module) }}</span></td>
                            <td><span class="badge-soft badge-soft-{{ $actionColor }}">{{ $log->action }}</span></td>
                            <td style="min-width:240px;">{{ $log->description ?? '-' }}</td>
                            <td style="min-width:150px;">
                                @if($log->old_values || $log->new_values)
                                    <details>
                                        <summary class="small text-muted-2" style="cursor:pointer;">Lihat detail</summary>
                                        <div class="row g-2 mt-1">
                                            @if($log->old_values)
                                                <div class="col-md-6">
                                                    <div class="small fw-semibold">Sebelum</div>
                                                    <pre class="small mb-0 p-2 rounded" style="background:#faf6f0;white-space:pre-wrap;word-break:break-word;max-height:180px;overflow:auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            @endif
                                            @if($log->new_values)
                                                <div class="col-md-6">
                                                    <div class="small fw-semibold">Sesudah</div>
                                                    <pre class="small mb-0 p-2 rounded" style="background:#faf6f0;white-space:pre-wrap;word-break:break-word;max-height:180px;overflow:auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </details>
                                @else
                                    <span class="text-muted-2 small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="bi-clock-history" title="Belum ada aktivitas" text="Aktivitas pengguna akan muncul di sini setelah ada perubahan data." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-3 border-top" style="border-color:#f0e8dd !important;">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
