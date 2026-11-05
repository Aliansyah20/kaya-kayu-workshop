@extends('layouts.app')

@section('title', $user ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('page-title', $user ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('page-subtitle', 'Atur identitas dan hak akses akun')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title">{{ $user ? 'Edit' : 'Tambah' }} Pengguna</h2>
            <p class="kk-page-sub">Password minimal 6 karakter. Kosongkan saat edit jika tidak ingin mengganti.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-kk"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <div class="kk-card">
        <div class="kk-card-body">
            <form method="POST" action="{{ $user ? route('users.update', $user) : route('users.store') }}">
                @csrf
                @if($user) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user?->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user?->username) }}" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user?->email) }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Nomor Telepon</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user?->phone) }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="owner" @selected(old('role', $user?->role) === 'owner')>Pemilik</option>
                            <option value="admin" @selected(old('role', $user?->role) === 'admin')>Admin / Sales</option>
                            <option value="employee" @selected(old('role', $user?->role) === 'employee')>Pegawai</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password {{ $user ? '' : '*' }}</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="{{ $user ? 'Kosongkan jika tidak diganti' : 'Minimal 6 karakter' }}" {{ $user ? '' : 'required' }}>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $user?->is_active ?? true))>
                            <label class="form-check-label form-label mb-0" for="is_active">Akun aktif</label>
                        </div>
                    </div>
                </div>

                <div class="kk-divider"></div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-kk">Batal</a>
                    <button type="submit" class="btn btn-kk"><i class="bi bi-check2 me-1"></i> Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
@endsection
