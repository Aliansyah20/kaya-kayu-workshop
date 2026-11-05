<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $stats = [
            'active_orders' => Order::active()->count(),
            'queued_items' => OrderItem::whereIn('status', ['queued', 'pending'])->count(),
            'in_progress_items' => OrderItem::whereIn('status', ['in_progress', 'finishing', 'qc'])->count(),
            'low_stock' => Material::whereColumn('stock_qty', '<=', 'min_stock_qty')->count(),
            'revenue_month' => Payment::whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->where('payment_type', '!=', 'refund')
                ->sum('amount')
                - Payment::whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->where('payment_type', 'refund')
                    ->sum('amount'),
            'overdue_orders' => Order::overduePayment()->count(),
            'customers' => \App\Models\Customer::count(),
            'models' => ProductModel::count(),
        ];

        $lowStock = Material::with(['unit', 'category'])
            ->whereColumn('stock_qty', '<=', 'min_stock_qty')
            ->orderBy('stock_qty')
            ->limit(8)
            ->get();

        $activeOrders = Order::with(['customer', 'items'])
            ->active()
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $productionItems = OrderItem::with(['order.customer', 'assignedEmployee', 'productModel'])
            ->whereIn('status', ['queued', 'in_progress', 'finishing', 'qc'])
            ->orderByRaw("FIELD(status, 'in_progress', 'finishing', 'qc', 'queued')")
            ->orderBy('target_date')
            ->limit(8)
            ->get();

        $employeeWorkload = User::where('role', 'employee')
            ->withCount(['assignedItems as active_items_count' => function ($query) {
                $query->whereIn('status', ['queued', 'in_progress', 'finishing', 'qc']);
            }])
            ->orderByDesc('active_items_count')
            ->get();

        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));
        $salesRaw = Payment::selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as total")
            ->where('paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month');

        $salesChart = [
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M Y'))->values(),
            'values' => $months->map(fn ($m) => (float) ($salesRaw[$m->format('Y-m')] ?? 0))->values(),
        ];

        $statusCounts = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard', compact(
            'stats',
            'lowStock',
            'activeOrders',
            'productionItems',
            'employeeWorkload',
            'salesChart',
            'statusCounts'
        ));
    }
}
