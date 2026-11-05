<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $this->resolveType($request->get('type'));
        $from = $request->filled('from') ? Carbon::parse($request->get('from')) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->get('to')) : now();
        $report = $this->buildReport($type, $request, $from, $to);

        return view('reports.index', array_merge($report, [
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'materials' => Material::orderBy('name')->get(),
            'statuses' => $this->orderStatuses(),
        ]));
    }

    public function exportPdf(Request $request)
    {
        $type = $this->resolveType($request->get('type'));
        $from = $request->filled('from') ? Carbon::parse($request->get('from')) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->get('to')) : now();
        $report = $this->buildReport($type, $request, $from, $to);

        $pdf = Pdf::loadView('reports.pdf', array_merge($report, [
            'from' => $from,
            'to' => $to,
        ]))->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-' . $type . '-' . now()->format('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $type = $this->resolveType($request->get('type'));
        $from = $request->filled('from') ? Carbon::parse($request->get('from')) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->get('to')) : now();
        $report = $this->buildReport($type, $request, $from, $to);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($report['title'], 0, 31));

        $sheet->fromArray($report['headers'], null, 'A1');
        $row = 2;
        foreach ($report['rows'] as $dataRow) {
            $sheet->fromArray(array_values($dataRow), null, 'A' . $row);
            $row++;
        }

        foreach (range(1, count($report['headers'])) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        $filename = 'Laporan-' . $type . '-' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildReport(string $type, Request $request, Carbon $from, Carbon $to): array
    {
        return match ($type) {
            'movements' => $this->movementReport($request, $from, $to),
            'orders' => $this->orderReport($request, $from, $to),
            'sales' => $this->salesReport($from, $to),
            'production' => $this->productionReport($request, $from, $to),
            default => $this->stockReport(),
        };
    }

    private function stockReport(): array
    {
        $materials = Material::with(['category', 'unit'])->orderBy('name')->get();

        $rows = $materials->map(fn ($material) => [
            $material->code,
            $material->name,
            $material->category?->name ?? '-',
            $material->unit?->code ?? '-',
            number_format((float) $material->stock_qty, 2, ',', '.'),
            number_format((float) $material->min_stock_qty, 2, ',', '.'),
            'Rp ' . number_format((float) $material->purchase_price, 0, ',', '.'),
            'Rp ' . number_format((float) $material->stock_qty * (float) $material->purchase_price, 0, ',', '.'),
        ])->all();

        return [
            'title' => 'Laporan Stok Bahan',
            'headers' => ['Kode', 'Nama Bahan', 'Kategori', 'Satuan', 'Stok', 'Stok Minimum', 'Harga Beli', 'Nilai Stok'],
            'rows' => $rows,
            'summary' => [
                'Total Bahan' => $materials->count(),
                'Stok Menipis' => $materials->filter(fn ($m) => $m->isLowStock())->count(),
                'Nilai Stok' => 'Rp ' . number_format($materials->sum(fn ($m) => (float) $m->stock_qty * (float) $m->purchase_price), 0, ',', '.'),
            ],
        ];
    }

    private function movementReport(Request $request, Carbon $from, Carbon $to): array
    {
        $query = StockMovement::with(['material.unit', 'createdBy'])
            ->whereBetween('movement_date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        if ($material = $request->get('material_id')) {
            $query->where('material_id', $material);
        }

        $movements = $query->latest('movement_date')->get();

        $rows = $movements->map(fn ($movement) => [
            $movement->movement_date->format('d/m/Y H:i'),
            $movement->material?->name ?? '-',
            $movement->type_label,
            number_format((float) $movement->quantity, 2, ',', '.'),
            $movement->material?->unit?->code ?? '-',
            $movement->reference_type ? class_basename($movement->reference_type) . ' #' . $movement->reference_id : '-',
            $movement->createdBy?->name ?? '-',
        ])->all();

        $in = $movements->filter(fn ($m) => $m->isIncoming())->sum('quantity');
        $out = $movements->filter(fn ($m) => ! $m->isIncoming())->sum('quantity');

        return [
            'title' => 'Laporan Mutasi Stok',
            'headers' => ['Tanggal', 'Bahan', 'Tipe', 'Qty', 'Satuan', 'Referensi', 'Dicatat Oleh'],
            'rows' => $rows,
            'summary' => [
                'Total Mutasi' => $movements->count(),
                'Total Masuk' => number_format((float) $in, 2, ',', '.'),
                'Total Keluar' => number_format((float) $out, 2, ',', '.'),
            ],
        ];
    }

    private function orderReport(Request $request, Carbon $from, Carbon $to): array
    {
        $query = Order::with('customer')
            ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()]);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $orders = $query->latest('order_date')->get();

        $rows = $orders->map(fn ($order) => [
            $order->order_no,
            $order->order_date->format('d/m/Y'),
            $order->customer?->name ?? '-',
            $order->status_label,
            $order->payment_status_label,
            'Rp ' . number_format((float) $order->total_amount, 0, ',', '.'),
            'Rp ' . number_format((float) $order->dp_amount, 0, ',', '.'),
            'Rp ' . number_format((float) $order->paid_amount, 0, ',', '.'),
            'Rp ' . number_format($order->remaining_amount, 0, ',', '.'),
            $order->payment_deadline?->format('d/m/Y') ?? '-',
        ])->all();

        return [
            'title' => 'Laporan Pesanan',
            'headers' => ['No. Pesanan', 'Tanggal', 'Pelanggan', 'Status', 'Pembayaran', 'Total', 'DP', 'Dibayar', 'Sisa', 'Batas Pelunasan'],
            'rows' => $rows,
            'summary' => [
                'Jumlah Pesanan' => $orders->count(),
                'Nilai Pesanan' => 'Rp ' . number_format($orders->sum('total_amount'), 0, ',', '.'),
                'Sudah Dibayar' => 'Rp ' . number_format($orders->sum('paid_amount'), 0, ',', '.'),
                'Sisa Tagihan' => 'Rp ' . number_format($orders->sum(fn ($o) => $o->remaining_amount), 0, ',', '.'),
            ],
        ];
    }

    private function salesReport(Carbon $from, Carbon $to): array
    {
        $payments = Payment::with(['order.customer', 'receivedBy'])
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest('paid_at')
            ->get();

        $rows = $payments->map(fn ($payment) => [
            $payment->payment_no,
            $payment->paid_at->format('d/m/Y H:i'),
            $payment->order?->order_no ?? '-',
            $payment->order?->customer?->name ?? '-',
            $payment->type_label,
            ucfirst($payment->payment_method),
            'Rp ' . number_format((float) $payment->amount, 0, ',', '.'),
            $payment->receivedBy?->name ?? '-',
        ])->all();

        $incoming = $payments->where('payment_type', '!=', 'refund')->sum('amount');
        $refunds = $payments->where('payment_type', 'refund')->sum('amount');

        return [
            'title' => 'Laporan Pembayaran',
            'headers' => ['No. Pembayaran', 'Tanggal', 'Pesanan', 'Pelanggan', 'Tipe', 'Metode', 'Nominal', 'Diterima Oleh'],
            'rows' => $rows,
            'summary' => [
                'Jumlah Transaksi' => $payments->count(),
                'Penerimaan Bersih' => 'Rp ' . number_format((float) $incoming - (float) $refunds, 0, ',', '.'),
                'Total Refund' => 'Rp ' . number_format((float) $refunds, 0, ',', '.'),
            ],
        ];
    }

    private function productionReport(Request $request, Carbon $from, Carbon $to): array
    {
        $query = OrderItem::with(['order.customer', 'assignedEmployee'])
            ->whereHas('order', fn ($q) => $q->whereBetween('order_date', [$from->toDateString(), $to->toDateString()]));

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $items = $query->latest('target_date')->get();

        $rows = $items->map(fn ($item) => [
            $item->order?->order_no ?? '-',
            $item->item_name,
            $item->order?->customer?->name ?? '-',
            $item->status_label,
            $item->assignedEmployee?->name ?? '-',
            $item->target_date?->format('d/m/Y') ?? '-',
            $item->started_at?->format('d/m/Y H:i') ?? '-',
            $item->finished_at?->format('d/m/Y H:i') ?? '-',
        ])->all();

        return [
            'title' => 'Laporan Produksi',
            'headers' => ['Pesanan', 'Item', 'Pelanggan', 'Status', 'Pegawai', 'Target', 'Mulai', 'Selesai'],
            'rows' => $rows,
            'summary' => [
                'Jumlah Item' => $items->count(),
                'Selesai' => $items->where('status', 'done')->count(),
                'Sedang Dikerjakan' => $items->whereIn('status', ['in_progress', 'finishing', 'qc'])->count(),
            ],
        ];
    }

    private function resolveType(?string $type): string
    {
        return in_array($type, ['stock', 'movements', 'orders', 'sales', 'production'], true) ? $type : 'stock';
    }

    private function orderStatuses(): array
    {
        return [
            'draft' => 'Draft',
            'pending_approval' => 'Menunggu Approval',
            'confirmed' => 'Dikonfirmasi',
            'queued' => 'Antrian',
            'in_production' => 'Dikerjakan',
            'finishing' => 'Finishing',
            'production_done' => 'Selesai Produksi',
            'delivered' => 'Diambil/Dikirim',
            'transferred' => 'Dialihkan',
            'cancelled' => 'Dibatalkan',
        ];
    }
}
