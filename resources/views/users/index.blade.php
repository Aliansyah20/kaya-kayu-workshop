@extends('layouts.app')

@section('title', 'Pengguna')
@section('page-title', 'Pengguna')
@section('page-subtitle', 'Kelola akses Pemilik, Admin/Sales, dan Pegawai')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-person-gear me-2 text-brown"></i>Pengguna</h2>
            <p class="kk-page-sub">Hanya Pemilik yang dapat menambah dan mengubah akun.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-kk"><i class="bi bi-plus-lg me-1"></i> Tambah Pengguna</a>
    </div>

    <div class="kk-card">
        <div class="kk-card-header">
            <form class="d-flex gap-2 flex-wrap" method="GET">
                <div class="input-group" style="min-width:240px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama / username...">
                </div>
                <select name="role" class="form-select" style="width:auto;">
                    <option value="">Semua role</option>
                    <option value="owner" @selected(request('role') === 'owner')>Pemilik</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin / Sales</option>
                    <option value="employee" @selected(request('role') === 'employee')>Pegawai</option>
                </select>
                <button class="btn btn-outline-kk" type="submit">Filter</button>
            </form>
            <span class="badge-soft badge-soft-secondary">{{ $users->total() }} akun</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="kk-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold text-brown">{{ $user->name }}</div>
                                        <div class="text-muted-2" style="font-size:.7rem;">{{ $user->email ?? 'Tanpa email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $user->username }}</code></td>
                            <td>
                                <span class="badge-soft badge-soft-{{ $user->role === 'owner' ? 'dark' : ($user->role === 'admin' ? 'info' : 'secondary') }}">
                                    {{ $user->role === 'owner' ? 'Pemilik' : ($user->role === 'admin' ? 'Admin / Sales' : 'Pegawai') }}
                                </span>
                            </td>
                            <td>{{ $user->phone ?: '-' }}</td>
                            <td><span class="badge-soft badge-soft-{{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-muted-2">{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Belum pernah' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-kk btn-icon"><i class="bi bi-pencil"></i></a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus akun ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger btn-icon" type="submit"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="bi-person-gear" title="Belum ada pengguna" text="Tambahkan akun untuk mengakses sistem." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-3 border-top" style="border-color:#f0e8dd !important;">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
