<aside class="kk-sidebar" id="kkSidebar">
    <div class="kk-brand">
        <div class="kk-brand-mark"><i class="bi bi-tree-fill"></i></div>
        <div>
            <div class="kk-brand-name">Kaya Kayu</div>
            <div class="kk-brand-sub">Workshop Manager</div>
        </div>
    </div>

    <nav>
        <div class="kk-nav-label">Ringkasan</div>
        <a href="{{ route('dashboard') }}" class="kk-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>

        @if(auth()->user()->isAdmin())
            <div class="kk-nav-label">Transaksi</div>
            <a href="{{ route('orders.index') }}" class="kk-nav-link {{ request()->routeIs('orders.index', 'orders.show') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i> Pesanan
            </a>
            <a href="{{ route('orders.create') }}" class="kk-nav-link {{ request()->routeIs('orders.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Pesanan Baru
            </a>
        @endif

        <div class="kk-nav-label">Produksi</div>
        <a href="{{ route('production.index') }}" class="kk-nav-link {{ request()->routeIs('production.*') ? 'active' : '' }}">
            <i class="bi bi-hammer"></i> Papan Produksi
        </a>

        @if(auth()->user()->isAdmin())
            <div class="kk-nav-label">Persediaan</div>
            <a href="{{ route('inventory.index') }}" class="kk-nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Stok & Mutasi
            </a>
            <a href="{{ route('inventory.stock-in.create') }}" class="kk-nav-link {{ request()->routeIs('inventory.stock-in.*') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-in-down"></i> Stok Masuk
            </a>
            <a href="{{ route('inventory.stock-out.create') }}" class="kk-nav-link {{ request()->routeIs('inventory.stock-out.*') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-up"></i> Stok Keluar
            </a>
            <a href="{{ route('inventory.opname.index') }}" class="kk-nav-link {{ request()->routeIs('inventory.opname.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i> Stock Opname
            </a>

            <div class="kk-nav-label">Katalog</div>
            <a href="{{ route('product-models.index') }}" class="kk-nav-link {{ request()->routeIs('product-models.*') ? 'active' : '' }}">
                <i class="bi bi-book"></i> Model Produk
            </a>
            <a href="{{ route('materials.index') }}" class="kk-nav-link {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                <i class="bi bi-layers"></i> Bahan Baku
            </a>
            <a href="{{ route('products.index') }}" class="kk-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-box2-heart"></i> Stok Produk Jadi
            </a>

            <div class="kk-nav-label">Master Data</div>
            <a href="{{ route('master.index', 'categories') }}" class="kk-nav-link {{ request()->routeIs('master.*') && request()->route('resource') === 'categories' ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Kategori
            </a>
            <a href="{{ route('master.index', 'units') }}" class="kk-nav-link {{ request()->routeIs('master.*') && request()->route('resource') === 'units' ? 'active' : '' }}">
                <i class="bi bi-rulers"></i> Satuan
            </a>
            <a href="{{ route('master.index', 'wood_types') }}" class="kk-nav-link {{ request()->routeIs('master.*') && request()->route('resource') === 'wood_types' ? 'active' : '' }}">
                <i class="bi bi-tree"></i> Jenis Kayu
            </a>
            <a href="{{ route('master.index', 'wood_grades') }}" class="kk-nav-link {{ request()->routeIs('master.*') && request()->route('resource') === 'wood_grades' ? 'active' : '' }}">
                <i class="bi bi-star"></i> Kualitas Kayu
            </a>
            <a href="{{ route('master.index', 'complexity_levels') }}" class="kk-nav-link {{ request()->routeIs('master.*') && request()->route('resource') === 'complexity_levels' ? 'active' : '' }}">
                <i class="bi bi-sliders"></i> Tingkat Kesulitan
            </a>
            <a href="{{ route('master.index', 'suppliers') }}" class="kk-nav-link {{ request()->routeIs('master.*') && request()->route('resource') === 'suppliers' ? 'active' : '' }}">
                <i class="bi bi-truck"></i> Supplier
            </a>
            <a href="{{ route('master.index', 'customers') }}" class="kk-nav-link {{ request()->routeIs('master.*') && request()->route('resource') === 'customers' ? 'active' : '' }}">
                <i class="bi bi-people"></i> Pelanggan
            </a>

            <div class="kk-nav-label">Laporan</div>
            <a href="{{ route('reports.index') }}" class="kk-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Laporan
            </a>
        @endif

        @if(auth()->user()->isOwner())
            <div class="kk-nav-label">Sistem</div>
            <a href="{{ route('users.index') }}" class="kk-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i> Pengguna
            </a>
            <a href="{{ route('activity-logs.index') }}" class="kk-nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Log Aktivitas
            </a>
            <a href="{{ route('settings.edit') }}" class="kk-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Pengaturan
            </a>
        @endif
    </nav>

    <div class="mt-4 p-3 rounded-4" style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08);">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-shield-check" style="color:#e8c49b;"></i>
            <span class="small fw-semibold" style="color:#f1dfca;">Workshop lokal</span>
        </div>
        <div style="font-size:.7rem; color:#a98e79; line-height:1.5;">Data tersimpan di server lokal Laragon. Cocok untuk operasional harian.</div>
    </div>
</aside>
<div class="kk-sidebar-backdrop" id="kkSidebarBackdrop"></div>
