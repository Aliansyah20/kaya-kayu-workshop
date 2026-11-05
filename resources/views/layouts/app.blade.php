<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Kaya Kayu Workshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700;9..144,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kaya-kayu.css') }}">
    @stack('styles')
</head>
<body>
@php
    $user = auth()->user();
    $roleLabel = match ($user->role) {
        'owner' => 'Pemilik',
        'admin' => 'Admin / Sales',
        'employee' => 'Pegawai',
        default => ucfirst($user->role),
    };
@endphp

<div class="kk-shell">
    @include('layouts.partials.sidebar')

    <div class="kk-main">
        <header class="kk-topbar">
            <button class="btn btn-outline-kk btn-icon d-lg-none" type="button" id="kkSidebarToggle" aria-label="Buka menu">
                <i class="bi bi-list"></i>
            </button>

            <div class="flex-grow-1 min-w-0">
                <h1 class="kk-topbar-title text-truncate">@yield('page-title', 'Dashboard')</h1>
                <p class="kk-topbar-sub text-truncate">@yield('page-subtitle', 'Ringkasan operasional Kaya Kayu Workshop')</p>
            </div>

            <div class="d-none d-md-flex align-items-center gap-2 text-muted-2 small">
                <i class="bi bi-calendar3"></i>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>

            <div class="dropdown">
                <button class="kk-user-chip border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="kk-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="d-none d-sm-block text-start">
                        <span class="d-block fw-bold" style="font-size:.78rem; line-height:1.1;">{{ $user->name }}</span>
                        <span class="d-block text-muted-2" style="font-size:.66rem;">{{ $roleLabel }}</span>
                    </span>
                    <i class="bi bi-chevron-down ms-1 small text-muted-2"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="border-radius:14px;">
                    <li class="px-3 py-2">
                        <div class="fw-bold small">{{ $user->name }}</div>
                        <div class="text-muted-2" style="font-size:.72rem;">{{ $user->username }} · {{ $roleLabel }}</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    @if($user->isOwner())
                        <li><a class="dropdown-item small" href="{{ route('settings.edit') }}"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item small text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="kk-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius:14px; background:#e6f0e4; color:#365b3a;">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius:14px; background:#f7e4e1; color:#8d3d34;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius:14px; background:#f7e4e1; color:#8d3d34;">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Periksa kembali data berikut:</div>
                    <ul class="mb-0 ps-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="kk-footer d-flex justify-content-between flex-wrap gap-2">
            <span>&copy; {{ date('Y') }} Kaya Kayu Workshop · Sistem Manajemen & Inventori</span>
            <span>Dibangun dengan Laravel & Bootstrap 5</span>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const sidebar = document.getElementById('kkSidebar');
        const backdrop = document.getElementById('kkSidebarBackdrop');
        const toggle = document.getElementById('kkSidebarToggle');

        function closeSidebar() {
            sidebar?.classList.remove('show');
            backdrop?.classList.remove('show');
        }

        toggle?.addEventListener('click', function () {
            sidebar?.classList.toggle('show');
            backdrop?.classList.toggle('show');
        });

        backdrop?.addEventListener('click', closeSidebar);
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) closeSidebar();
        });
    })();
</script>
@stack('scripts')
</body>
</html>
