<?php

namespace App\Http\Controllers;

use App\Models\ComplexityLevel;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductModel;
use App\Models\Setting;
use App\Models\User;
use App\Models\WoodGrade;
use App\Models\WoodType;
use App\Services\ActivityLogger;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    public function index()
    {
        $query = Order::with(['customer', 'items'])->withCount('items');

        if ($search = request('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($paymentStatus = request('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if (request('overdue')) {
            $query->overduePayment();
        }

        $orders = $query->latest('order_date')->latest('id')->paginate(10)->withQueryString();
        $statuses = $this->statusOptions();
        $paymentStatuses = $this->paymentStatusOptions();

        return view('orders.index', compact('orders', 'statuses', 'paymentStatuses'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $productModels = ProductModel::with(['category', 'defaultComplexity', 'defaultWoodType', 'defaultWoodGrade', 'bomItems'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $woodTypes = WoodType::where('is_active', true)->orderBy('name')->get();
        $woodGrades = WoodGrade::where('is_active', true)->orderBy('name')->get();
        $complexities = ComplexityLevel::where('is_active', true)->orderBy('name')->get();
        $employees = User::where('role', 'employee')->where('is_active', true)->orderBy('name')->get();
        $dpPercent = (float) Setting::get('dp_percent', 30);
        $graceDays = (int) Setting::get('grace_period_days', 3);

        return view('orders.create', compact(
            'customers',
            'productModels',
            'woodTypes',
            'woodGrades',
            'complexities',
            'employees',
            'dpPercent',
            'graceDays'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'channel' => ['required', 'in:whatsapp,walk_in,phone,other'],
            'order_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_model_id' => ['nullable', 'exists:product_models,id'],
            'items.*.item_name' => ['nullable', 'string', 'max:150'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.pricing_type' => ['required', 'in:per_m2,fixed'],
            'items.*.measurement_type' => ['required', 'in:front_area,top_area,surface_area,manual'],
            'items.*.wood_type_id' => ['nullable', 'exists:wood_types,id'],
            'items.*.wood_grade_id' => ['nullable', 'exists:wood_grades,id'],
            'items.*.complexity_level_id' => ['nullable', 'exists:complexity_levels,id'],
            'items.*.length_cm' => ['nullable', 'numeric', 'min:0'],
            'items.*.width_cm' => ['nullable', 'numeric', 'min:0'],
            'items.*.height_cm' => ['nullable', 'numeric', 'min:0'],
            'items.*.area_m2' => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.additional_fee' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.price_override_reason' => ['nullable', 'string'],
            'items.*.custom_notes' => ['nullable', 'string'],
            'items.*.assigned_employee_id' => ['nullable', 'exists:users,id'],
            'items.*.target_date' => ['nullable', 'date'],
        ]);

        $order = $this->orderService->createOrder($data, $request->user());

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan ' . $order->order_no . ' berhasil dibuat.');
    }

    public function show(Order $order)
    {
        $order->load([
            'customer',
            'createdBy',
            'approvedBy',
            'items.productModel',
            'items.woodType',
            'items.woodGrade',
            'items.complexity',
            'items.assignedEmployee',
            'items.materials.material.unit',
            'items.materials.unit',
            'payments.receivedBy',
            'approvals.requestedBy',
            'approvals.approvedBy',
            'statusHistories.changedBy',
            'statusHistories.orderItem',
            'transfers.originalOrderItem',
            'transfers.product',
            'transfers.newOrder',
            'transfers.transferredBy',
        ]);

        $nextStatuses = $this->nextStatuses($order);
        $transferOrders = Order::where('id', '!=', $order->id)
            ->whereIn('status', ['confirmed', 'queued', 'in_production'])
            ->orderBy('due_date')
            ->get();

        return view('orders.show', compact('order', 'nextStatuses', 'transferOrders'));
    }

    public function approve(Order $order)
    {
        $this->orderService->approve($order, request()->user());

        return back()->with('success', 'Pesanan berhasil disetujui dan masuk antrian produksi.');
    }

    public function recordPayment(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_type' => ['required', 'in:dp,installment,settlement,refund'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,transfer,qris,other'],
            'paid_at' => ['required', 'date'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['payment_type'] !== 'refund' && $data['amount'] > $order->remaining_amount + 0.01) {
            throw ValidationException::withMessages(['amount' => 'Nominal pembayaran melebihi sisa tagihan (Rp ' . number_format($order->remaining_amount, 0, ',', '.') . ').']);
        }

        if ($data['payment_type'] === 'refund' && $data['amount'] > $order->paid_amount + 0.01) {
            throw ValidationException::withMessages(['amount' => 'Nominal refund melebihi total pembayaran yang sudah diterima.']);
        }

        if ($request->hasFile('proof')) {
            $data['proof_path'] = $request->file('proof')->store('payment-proofs', 'public');
        }

        $this->orderService->recordPayment($order, $data, $request->user());

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->orderService->updateStatus($order, $data['status'], $request->user(), $data['notes'] ?? null);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function transfer(Request $request, Order $order)
    {
        $data = $request->validate([
            'order_item_id' => ['required', 'exists:order_items,id'],
            'transfer_type' => ['required', 'in:next_order,ready_stock,other'],
            'new_order_id' => ['nullable', 'exists:orders,id'],
            'dp_forfeited' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $item = OrderItem::where('order_id', $order->id)->findOrFail($data['order_item_id']);

        if ($data['transfer_type'] === 'next_order' && empty($data['new_order_id'])) {
            throw ValidationException::withMessages(['new_order_id' => 'Pilih pesanan berikutnya jika tipe pengalihan adalah pesanan berikutnya.']);
        }

        $data['dp_forfeited'] = $request->boolean('dp_forfeited');
        $this->orderService->transfer($order, $item, $data, $request->user());

        return back()->with('success', 'Item berhasil dialihkan.');
    }

    public function invoice(Order $order)
    {
        $order->load(['customer', 'items', 'payments', 'createdBy']);

        $pdf = Pdf::loadView('orders.invoice', compact('order'))
            ->setPaper('a4');

        return $pdf->download('Invoice-' . $order->order_no . '.pdf');
    }

    public function wa(Order $order)
    {
        return redirect()->away($this->orderService->waMessage($order));
    }

    private function statusOptions(): array
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

    private function paymentStatusOptions(): array
    {
        return [
            'unpaid' => 'Belum Bayar',
            'dp_paid' => 'DP Terbayar',
            'partially_paid' => 'Dibayar Sebagian',
            'paid' => 'Lunas',
            'overdue' => 'Jatuh Tempo',
        ];
    }

    private function nextStatuses(Order $order): array
    {
        $allowed = [
            'draft' => ['pending_approval', 'cancelled'],
            'pending_approval' => ['cancelled'],
            'confirmed' => ['queued', 'in_production', 'cancelled'],
            'queued' => ['in_production', 'cancelled'],
            'in_production' => ['finishing', 'cancelled'],
            'finishing' => ['production_done', 'cancelled'],
            'production_done' => ['delivered', 'cancelled'],
            'delivered' => [],
            'transferred' => [],
            'cancelled' => [],
        ];

        $labels = $this->statusOptions();
        $statuses = collect($allowed[$order->status] ?? [])
            ->mapWithKeys(fn ($status) => [$status => $labels[$status] ?? $status])
            ->all();

        if (! auth()->user()?->isOwner()) {
            unset($statuses['production_done']);
        }

        if ($order->payment_status !== 'paid') {
            unset($statuses['delivered']);
        }

        return $statuses;
    }
}
