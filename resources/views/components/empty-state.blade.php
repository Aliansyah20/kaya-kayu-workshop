@props([
    'icon' => 'bi-inbox',
    'title' => 'Belum ada data',
    'text' => 'Data akan muncul di sini setelah ditambahkan.',
])

<div class="kk-empty">
    <i class="bi {{ $icon }}"></i>
    <div class="fw-bold text-brown">{{ $title }}</div>
    <div class="small mt-1">{{ $text }}</div>
</div>
