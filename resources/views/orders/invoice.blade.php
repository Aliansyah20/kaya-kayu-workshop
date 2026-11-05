<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_no }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #33251e; font-size: 11px; line-height: 1.45; }
        .header { border-bottom: 3px solid #5b3a29; padding-bottom: 14px; margin-bottom: 18px; width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .brand { font-size: 20px; font-weight: bold; color: #5b3a29; }
        .brand small { display: block; font-size: 9px; letter-spacing: 2px; color: #8a7668; font-weight: normal; margin-top: 3px; }
        .muted { color: #8a7668; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 4px 9px; border-radius: 99px; background: #f1e7da; color: #5b3a29; font-size: 9px; font-weight: bold; }
        .box { border: 1px solid #e7dccd; border-radius: 8px; padding: 10px 12px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { vertical-align: top; padding: 4px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.items th { background: #f6efe5; color: #5b3a29; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; text-align: left; padding: 8px 8px; border-bottom: 1px solid #e7dccd; }
        table.items td { padding: 8px 8px; border-bottom: 1px solid #f1e8dc; vertical-align: top; }
        table.items td.right, table.items th.right { text-align: right; }
        .totals { width: 300px; margin-left: auto; margin-top: 12px; }
        .totals td { padding: 5px 0; }
        .totals .grand { font-size: 14px; font-weight: bold; color: #5b3a29; border-top: 2px solid #5b3a29; padding-top: 8px; }
        .footer { margin-top: 28px; border-top: 1px solid #e7dccd; padding-top: 10px; font-size: 9px; color: #8a7668; }
        .signature { margin-top: 38px; width: 200px; text-align: center; float: right; }
        .signature .line { border-top: 1px solid #33251e; margin-top: 42px; padding-top: 4px; }
        .clear { clear: both; }
    </style>
</head>
<body>
@php
    $companyName = \App\Models\Setting::get('company_name', 'Kaya Kayu Workshop');
    $companyAddress = \App\Models\Setting::get('company_address', '');
    $companyPhone = \App\Models\Setting::get('company_phone', '');
    $companyEmail = \App\Models\Setting::get('company_email', '');
    $invoiceNotes = \App\Models\Setting::get('invoice_notes', 'Terima kasih telah mempercayakan furniture Anda kepada Kaya Kayu Workshop.');
@endphp

<table class="header">
    <tr>
        <td>
            <div class="brand">{{ $companyName }}<small>SISTEM MANAJEMEN WORKSHOP</small></div>
            <div class="muted" style="margin-top:8px;">
                {{ $companyAddress ?: 'Alamat workshop belum diatur' }}<br>
                @if($companyPhone) {{ $companyPhone }} @endif
                @if($companyEmail) · {{ $companyEmail }} @endif
            </div>
        </td>
        <td class="text-right">
            <div style="font-size:16px;font-weight:bold;color:#5b3a29;">INVOICE</div>
            <div class="muted">{{ $order->order_no }}</div>
            <div style="margin-top:8px;"><span class="badge">{{ $order->status_label }}</span></div>
            <div style="margin-top:6px;"><span class="badge">{{ $order->payment_status_label }}</span></div>
        </td>
    </tr>
</table>

<table class="grid">
    <tr>
        <td style="width:55%;">
            <div class="muted" style="font-size:9px;text-transform:uppercase;letter-spacing:1px;">Ditagihkan Kepada</div>
            <div style="font-size:13px;font-weight:bold;margin-top:4px;">{{ $order->customer?->name }}</div>
            <div class="muted">{{ $order->customer?->phone }}</div>
            <div class="muted">{{ $order->customer?->address }}</div>
        </td>
        <td style="width:45%;">
            <table class="grid">
                <tr><td class="muted">Tanggal Pesanan</td><td class="text-right">{{ $order->order_date?->format('d/m/Y') }}</td></tr>
                <tr><td class="muted">Tenggat</td><td class="text-right">{{ $order->due_date?->format('d/m/Y') }}</td></tr>
                <tr><td class="muted">Batas Pelunasan</td><td class="text-right">{{ $order->payment_deadline?->format('d/m/Y') ?? '-' }}</td></tr>
                <tr><td class="muted">Sumber</td><td class="text-right">{{ ucfirst($order->channel) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:38%;">Item</th>
            <th>Ukuran</th>
            <th class="right">Qty</th>
            <th class="right">Luas</th>
            <th class="right">Harga</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->item_name }}</strong><br>
                    <span class="muted">{{ $item->productModel?->name ?? 'Produk custom' }} · {{ $item->woodType?->name ?? '-' }} / {{ $item->woodGrade?->name ?? '-' }}</span>
                </td>
                <td class="muted">{{ $item->length_cm ?: '-' }} × {{ $item->width_cm ?: '-' }} × {{ $item->height_cm ?: '-' }} cm</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">{{ number_format($item->area_m2, 2, ',', '.') }} m²</td>
                <td class="right">Rp {{ number_format($item->final_price, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td class="muted">Subtotal</td><td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td></tr>
    <tr><td class="muted">DP {{ number_format($order->dp_percent, 0) }}%</td><td class="text-right">Rp {{ number_format($order->dp_amount, 0, ',', '.') }}</td></tr>
    <tr><td class="muted">Sudah Dibayar</td><td class="text-right">Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</td></tr>
    <tr><td class="grand">Sisa Tagihan</td><td class="grand text-right">Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</td></tr>
</table>

<div class="clear"></div>

@if($order->payments->isNotEmpty())
    <div style="margin-top:18px;">
        <div class="muted" style="font-size:9px;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Riwayat Pembayaran</div>
        <table class="items">
            <thead><tr><th>No.</th><th>Tanggal</th><th>Tipe</th><th>Metode</th><th class="right">Nominal</th></tr></thead>
            <tbody>
                @foreach($order->payments->sortBy('paid_at') as $payment)
                    <tr>
                        <td>{{ $payment->payment_no }}</td>
                        <td>{{ $payment->paid_at->format('d/m/Y') }}</td>
                        <td>{{ $payment->type_label }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div style="margin-top:18px;" class="muted">{{ $invoiceNotes }}</div>

<div class="signature">
    <div class="muted">Hormat kami,</div>
    <div class="line">{{ $companyName }}</div>
</div>
<div class="clear"></div>

<div class="footer">
    Invoice ini dibuat otomatis oleh Sistem Manajemen Kaya Kayu Workshop. Barang yang sudah dipesan tidak dapat dibatalkan sepihak.
    DP hangus jika pelanggan tidak melakukan pelunasan sampai batas yang disepakati.
</div>
</body>
</html>
