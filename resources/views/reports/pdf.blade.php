<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 26px 28px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #33251e; font-size: 10px; line-height: 1.4; }
        .header { border-bottom: 3px solid #5b3a29; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { font-size: 18px; font-weight: bold; color: #5b3a29; }
        .brand small { display: block; font-size: 8px; letter-spacing: 2px; color: #8a7668; font-weight: normal; margin-top: 2px; }
        .muted { color: #8a7668; }
        .text-right { text-align: right; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .summary td { background: #f8f2e9; border: 1px solid #e7dccd; padding: 7px 9px; width: 25%; }
        .summary .label { font-size: 8px; text-transform: uppercase; letter-spacing: .6px; color: #8a7668; }
        .summary .value { font-size: 12px; font-weight: bold; color: #5b3a29; margin-top: 2px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #5b3a29; color: #fff8ee; font-size: 8px; text-transform: uppercase; letter-spacing: .4px; text-align: left; padding: 7px 6px; }
        table.data td { border-bottom: 1px solid #f0e6da; padding: 6px 6px; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #fcf8f2; }
        .footer { margin-top: 16px; border-top: 1px solid #e7dccd; padding-top: 8px; font-size: 8px; color: #8a7668; }
    </style>
</head>
<body>
@php
    $companyName = \App\Models\Setting::get('company_name', 'Kaya Kayu Workshop');
    $companyAddress = \App\Models\Setting::get('company_address', '');
    $companyPhone = \App\Models\Setting::get('company_phone', '');
@endphp

<div class="header">
    <table style="width:100%;">
        <tr>
            <td>
                <div class="brand">{{ $companyName }}<small>SISTEM MANAJEMEN WORKSHOP</small></div>
                <div class="muted" style="margin-top:6px;">{{ $companyAddress }} @if($companyPhone) · {{ $companyPhone }} @endif</div>
            </td>
            <td class="text-right">
                <div style="font-size:14px;font-weight:bold;color:#5b3a29;">{{ $title }}</div>
                <div class="muted">
                    @if(isset($from) && isset($to))
                        Periode {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}
                    @else
                        Dicetak {{ now()->format('d/m/Y H:i') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

<table class="summary">
    <tr>
        @foreach($summary as $label => $value)
            <td>
                <div class="label">{{ $label }}</div>
                <div class="value">{{ $value }}</div>
            </td>
        @endforeach
    </tr>
</table>

<table class="data">
    <thead>
        <tr>
            @foreach($headers as $header)
                <th>{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ count($headers) }}" class="muted">Tidak ada data untuk periode ini.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Dicetak otomatis oleh Sistem Manajemen Kaya Kayu Workshop pada {{ now()->format('d/m/Y H:i') }}.
</div>
</body>
</html>
