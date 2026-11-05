@props([
    'label' => 'Statistik',
    'value' => '0',
    'icon' => 'bi-graph-up-arrow',
    'color' => 'amber',
    'hint' => null,
])

<div class="kk-stat">
    <div class="kk-stat-icon kk-icon-{{ $color }}"><i class="bi {{ $icon }}"></i></div>
    <div class="kk-stat-label">{{ $label }}</div>
    <div class="kk-stat-value">{{ $value }}</div>
    @if($hint)
        <div class="kk-stat-hint">{{ $hint }}</div>
    @endif
</div>
