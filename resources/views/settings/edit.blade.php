@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')
@section('page-subtitle', 'Identitas workshop dan ketentuan transaksi')

@section('content')
    <div class="kk-page-head">
        <div>
            <h2 class="kk-page-title"><i class="bi bi-gear me-2 text-brown"></i>Pengaturan Sistem</h2>
            <p class="kk-page-sub">Perubahan berlaku untuk pesanan dan invoice berikutnya.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="kk-card mb-4">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Identitas Workshop</h3>
                            <p class="kk-card-sub">Tampil di invoice dan laporan PDF</p>
                        </div>
                    </div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="company_name">Nama Workshop <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" required>
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="company_phone">Telepon</label>
                                <input type="text" class="form-control @error('company_phone') is-invalid @enderror" id="company_phone" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}">
                                @error('company_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="company_address">Alamat</label>
                                <textarea class="form-control" id="company_address" name="company_address" rows="2">{{ old('company_address', $settings['company_address']) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="company_email">Email</label>
                                <input type="email" class="form-control @error('company_email') is-invalid @enderror" id="company_email" name="company_email" value="{{ old('company_email', $settings['company_email']) }}">
                                @error('company_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="invoice_notes">Catatan Invoice</label>
                                <textarea class="form-control" id="invoice_notes" name="invoice_notes" rows="3">{{ old('invoice_notes', $settings['invoice_notes']) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="kk-card mb-4">
                    <div class="kk-card-header">
                        <div>
                            <h3 class="kk-card-title">Ketentuan Transaksi</h3>
                            <p class="kk-card-sub">Dipakai saat membuat pesanan baru</p>
                        </div>
                    </div>
                    <div class="kk-card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label" for="dp_percent">DP (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('dp_percent') is-invalid @enderror" id="dp_percent" name="dp_percent" value="{{ old('dp_percent', $settings['dp_percent']) }}" min="0" max="100" step="0.01" required>
                                @error('dp_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="grace_period_days">Tenggang (hari) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('grace_period_days') is-invalid @enderror" id="grace_period_days" name="grace_period_days" value="{{ old('grace_period_days', $settings['grace_period_days']) }}" min="0" max="30" required>
                                @error('grace_period_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="quick_price_per_m2">Harga Cepat / m² <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('quick_price_per_m2') is-invalid @enderror" id="quick_price_per_m2" name="quick_price_per_m2" value="{{ old('quick_price_per_m2', $settings['quick_price_per_m2']) }}" min="0" step="1000" required>
                                </div>
                                @error('quick_price_per_m2')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Rumus cepat: luas m² × harga ini × faktor kayu × faktor kesulitan.</div>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 mt-3" style="background:#fbf6ee;border:1px dashed #e2d2be;font-size:.75rem;color:#6b5647;">
                            <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i>Alur pembayaran</div>
                            DP {{ number_format($settings['dp_percent'], 0) }}% saat konfirmasi, pelunasan sebelum pengambilan.
                            Jika lewat {{ $settings['grace_period_days'] }} hari dari tenggat, pesanan dapat dialihkan dan DP hangus.
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-kk btn-lg"><i class="bi bi-check2-circle me-1"></i> Simpan Pengaturan</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-kk">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </form>
@endsection
