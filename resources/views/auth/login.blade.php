@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<div class="kk-login">
    <section class="kk-login-art">
        <div>
            <div class="d-flex align-items-center gap-3 mb-5">
                <div class="kk-brand-mark" style="width:52px;height:52px;font-size:1.45rem;"><i class="bi bi-tree-fill"></i></div>
                <div>
                    <div class="fw-bold" style="font-size:1.3rem;color:#fff8ee;">Kaya Kayu Workshop</div>
                    <div style="font-size:.68rem;letter-spacing:.16em;text-transform:uppercase;color:#bfa994;">Workshop Manager</div>
                </div>
            </div>

            <div class="kk-login-badge mb-4"><i class="bi bi-stars"></i> Sistem Manajemen & Inventori</div>
            <h1 class="kk-login-quote brand-font">Rapikan pesanan, produksi, dan stok kayu dalam satu tempat.</h1>
            <p class="mt-3" style="max-width:430px;color:#c9b7a5;font-size:.9rem;line-height:1.7;">
                Dibuat untuk alur kerja Kaya Kayu: pesanan dari WhatsApp dan walk-in, antrian produksi,
                pemakaian bahan, pembayaran DP, hingga laporan pemilik.
            </p>
        </div>

        <div class="row g-3 mt-5" style="max-width:520px;">
            <div class="col-6">
                <div class="p-3 rounded-4" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);">
                    <i class="bi bi-receipt-cutoff fs-5" style="color:#e8c49b;"></i>
                    <div class="small fw-semibold mt-2">Pesanan & DP</div>
                    <div style="font-size:.7rem;color:#a98e79;">Catat item, harga, dan termin.</div>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 rounded-4" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);">
                    <i class="bi bi-hammer fs-5" style="color:#e8c49b;"></i>
                    <div class="small fw-semibold mt-2">Antrian Produksi</div>
                    <div style="font-size:.7rem;color:#a98e79;">Pantau progres tiap item.</div>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 rounded-4" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);">
                    <i class="bi bi-box-seam fs-5" style="color:#e8c49b;"></i>
                    <div class="small fw-semibold mt-2">Stok Bahan</div>
                    <div style="font-size:.7rem;color:#a98e79;">Mutasi otomatis saat produksi.</div>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 rounded-4" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);">
                    <i class="bi bi-graph-up-arrow fs-5" style="color:#e8c49b;"></i>
                    <div class="small fw-semibold mt-2">Laporan Pemilik</div>
                    <div style="font-size:.7rem;color:#a98e79;">PDF & Excel siap dicetak.</div>
                </div>
            </div>
        </div>

        <div class="mt-5" style="font-size:.7rem;color:#8f7765;">
            Kaya Kayu Workshop · Sistem lokal untuk operasional harian
        </div>
    </section>

    <section class="kk-login-panel">
        <div class="kk-login-card">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill mb-3" style="background:#efe4d4;color:#6b4a34;font-size:.72rem;font-weight:700;">
                    <i class="bi bi-shield-lock"></i> Area internal
                </div>
                <h2 class="brand-font fw-bold mb-1" style="font-size:2rem;">Selamat datang kembali</h2>
                <p class="text-muted-2 mb-0" style="font-size:.85rem;">Masuk untuk mengelola pesanan dan produksi workshop.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0" style="border-radius:12px;background:#f7e4e1;color:#8d3d34;font-size:.82rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" placeholder="contoh: owner" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label small text-muted-2" for="remember">Ingat saya</label>
                    </div>
                    <span class="small text-muted-2">Lupa password? Hubungi Pemilik.</span>
                </div>

                <button type="submit" class="btn btn-kk w-100 py-2">
                    Masuk ke Dashboard <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </form>

            <div class="kk-demo-account mt-4">
                <div class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i>Akun demo</div>
                <div class="d-grid gap-1">
                    <div>Pemilik: <code>owner</code> / <code>password</code></div>
                    <div>Admin: <code>admin</code> / <code>password</code></div>
                    <div>Pegawai: <code>pegawai</code> / <code>password</code></div>
                </div>
            </div>

            <div class="text-center mt-4 text-muted-2" style="font-size:.72rem;">
                &copy; {{ date('Y') }} Kaya Kayu Workshop
            </div>
        </div>
    </section>
</div>
@endsection
