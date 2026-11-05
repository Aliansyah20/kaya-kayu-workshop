@extends('layouts.guest')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    <div class="d-flex align-items-center justify-content-center" style="min-height:100vh;background:var(--kk-cream);padding:2rem;">
        <div class="kk-card text-center" style="max-width:460px;width:100%;">
            <div class="kk-card-body p-5">
                <div class="kk-avatar-lg mx-auto mb-3"><i class="bi bi-compass fs-4"></i></div>
                <h1 class="brand-font fw-bold" style="font-size:2rem;">404</h1>
                <h2 class="h5 fw-bold">Halaman tidak ditemukan</h2>
                <p class="text-muted-2 small mt-2">Alamat yang Anda buka tidak tersedia atau data sudah dihapus.</p>
                <a href="{{ url()->previous() }}" class="btn btn-outline-kk me-1">Kembali</a>
                <a href="{{ route('dashboard') }}" class="btn btn-kk">Dashboard</a>
            </div>
        </div>
    </div>
@endsection
