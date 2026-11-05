@extends('layouts.app')

@section('title', $config['title'])
@section('page-title', $config['title'])
@section('page-subtitle', 'Kelola data master ' . strtolower($config['title']) . ' Kaya Kayu Workshop')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi {{ $config['icon'] }} me-2 text-brown"></i>{{ $config['title'] }}</h2>
            <p class="kk-page-sub">Data master yang dipakai di pesanan, produksi, dan inventori.</p>
        </div>
        <a href="{{ route('master.create', $resource) }}" class="btn btn-kk"><i class="bi bi-plus-lg me-1"></i> Tambah {{ $config['singular'] }}</a>
    </div>

    <div class="kk-card">
        <div class="kk-card-header">
            <form class="d-flex gap-2 flex-wrap" method="GET">
                <div class="input-group" style="min-width:260px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari {{ strtolower($config['title']) }}...">
                </div>
                <button class="btn btn-outline-kk" type="submit">Cari</button>
                @if(request('q'))
                    <a href="{{ route('master.index', $resource) }}" class="btn btn-outline-kk">Reset</a>
                @endif
            </form>
            <span class="badge-soft badge-soft-secondary">{{ $rows->total() }} data</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        @foreach($config['columns'] as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            @foreach($config['columns'] as $column)
                                @php
                                    $value = data_get($row, $column['name']);
                                    $format = $column['format'] ?? 'text';
                                @endphp
                                <td>
                                    @if($format === 'badge')
                                        @php $badgeLabel = $column['map'][$value] ?? ucfirst((string) $value); @endphp
                                        <span class="badge-soft badge-soft-info">{{ $badgeLabel }}</span>
                                    @elseif($format === 'boolean')
                                        <span class="badge-soft badge-soft-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'Aktif' : 'Nonaktif' }}</span>
                                    @elseif($format === 'decimal')
                                        {{ number_format((float) $value, 2, ',', '.') }}
                                    @elseif($format === 'currency')
                                        Rp {{ number_format((float) $value, 0, ',', '.') }}
                                    @else
                                        @if($column['name'] === 'name')
                                            <span class="fw-semibold text-brown">{{ $value }}</span>
                                        @elseif($column['name'] === 'description')
                                            {{ \Illuminate\Support\Str::limit($value, 70) ?: '-' }}
                                        @else
                                            {{ $value ?: '-' }}
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('master.edit', [$resource, $row->id]) }}" class="btn btn-sm btn-outline-kk btn-icon" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('master.destroy', [$resource, $row->id]) }}" onsubmit="return confirm('Hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger btn-icon" type="submit" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($config['columns']) + 1 }}">
                                <x-empty-state icon="{{ $config['icon'] }}" :title="'Belum ada ' . strtolower($config['title'])" text="Tambahkan data pertama untuk mulai menggunakan modul ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->hasPages())
            <div class="p-3 border-top" style="border-color:#f0e8dd !important;">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
@endsection
