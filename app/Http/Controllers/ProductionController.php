<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\ProductionLog;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StockService $stockService
    ) {
    }

    public function index()
    {
        $query = OrderItem::with([
            'order.customer',
            'productModel',
            'assignedEmployee',
            'materials.material.unit',
        ])->whereIn('status', ['queued', 'in_progress', 'finishing', 'qc']);

        if ($employee = request('employee')) {
            $query->where('assigned_employee_id', $employee);
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($search = request('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($o) => $o->where('order_no', 'like', "%{$search}%"))
                    ->orWhereHas('order.customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $items = $query->orderBy('target_date')->orderBy('sort_order')->get();
        $employees = User::where('role', 'employee')->where('is_active', true)->orderBy('name')->get();

        $columns = [
            'queued' => ['label' => 'Antrian', 'color' => 'secondary', 'icon' => 'bi-hourglass-split'],
            'in_progress' => ['label' => 'Dikerjakan', 'color' => 'primary', 'icon' => 'bi-hammer'],
            'finishing' => ['label' => 'Finishing', 'color' => 'warning', 'icon' => 'bi-brush'],
            'qc' => ['label' => 'Quality Control', 'color' => 'info', 'icon' => 'bi-patch-check'],
        ];

        $grouped = collect($columns)->mapWithKeys(fn ($meta, $status) => [
            $status => $items->where('status', $status)->values(),
        ]);

        $completedToday = OrderItem::where('status', 'done')
            ->whereDate('finished_at', now()->toDateString())
            ->count();

        return view('production.index', compact('grouped', 'columns', 'employees', 'completedToday'));
    }

    public function updateStatus(Request $request, OrderItem $orderItem)
    {
        $data = $request->validate([
            'status' => ['required', 'in:queued,in_progress,finishing,qc,done,cancelled'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $user = $request->user();

        if ($user->isEmployee() && $orderItem->assigned_employee_id !== $user->id) {
            throw ValidationException::withMessages(['status' => 'Anda hanya dapat memperbarui item yang ditugaskan kepada Anda.']);
        }

        $allowed = [
            'pending' => ['queued', 'cancelled'],
            'queued' => ['in_progress', 'cancelled'],
            'in_progress' => ['finishing', 'cancelled'],
            'finishing' => ['qc', 'cancelled'],
            'qc' => ['done', 'cancelled'],
            'done' => [],
            'transferred' => [],
            'cancelled' => [],
        ];

        if (! in_array($data['status'], $allowed[$orderItem->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Status tidak bisa diubah dari {$orderItem->status_label} ke {$data['status']}.",
            ]);
        }

        if ($data['status'] === 'done' && ! $user->isOwner()) {
            throw ValidationException::withMessages(['status' => 'Status selesai harus dikonfirmasi oleh Pemilik.']);
        }

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('production-photos', 'public');
        }

        DB::transaction(function () use ($orderItem, $data, $user) {
            $oldStatus = $orderItem->status;

            if ($data['status'] === 'in_progress' && $oldStatus !== 'in_progress') {
                $this->deductMaterials($orderItem, $user);
            }

            $update = [
                'status' => $data['status'],
                'assigned_employee_id' => $orderItem->assigned_employee_id ?? $user->id,
            ];

            if ($data['status'] === 'in_progress' && ! $orderItem->started_at) {
                $update['started_at'] = now();
            }
            if ($data['status'] === 'finishing') {
                $update['finished_at'] = null;
            }
            if ($data['status'] === 'qc') {
                $update['qc_at'] = now();
            }
            if ($data['status'] === 'done') {
                $update['finished_at'] = now();
                $update['qc_at'] = $orderItem->qc_at ?? now();
                $update['qc_by'] = $user->id;
            }

            $orderItem->update($update);

            ProductionLog::create([
                'order_item_id' => $orderItem->id,
                'employee_id' => $user->id,
                'status_from' => $oldStatus,
                'status_to' => $data['status'],
                'progress_percent' => $data['progress_percent'] ?? $orderItem->progress_percent,
                'notes' => $data['notes'] ?? null,
                'photo_path' => $data['photo_path'] ?? null,
            ]);

            $orderItem->order->statusHistories()->create([
                'order_item_id' => $orderItem->id,
                'status_from' => $oldStatus,
                'status_to' => $data['status'],
                'changed_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->orderService->syncOrderStatus($orderItem->order);

            ActivityLogger::log('production_status', 'production', $orderItem, 'Memperbarui status produksi ' . $orderItem->item_name . '.', ['status' => $oldStatus], ['status' => $data['status']]);
        });

        return back()->with('success', 'Status produksi berhasil diperbarui.');
    }

    private function deductMaterials(OrderItem $item, User $user): void
    {
        $item->loadMissing('materials.material.unit');

        foreach ($item->materials as $planned) {
            $alreadyDeducted = StockMovement::where('order_item_material_id', $planned->id)
                ->where('movement_type', 'production_out')
                ->exists();

            if ($alreadyDeducted) {
                continue;
            }

            $this->stockService->recordMaterial(
                $planned->material,
                'production_out',
                (float) $planned->planned_qty,
                [
                    'reference_type' => OrderItem::class,
                    'reference_id' => $item->id,
                    'order_id' => $item->order_id,
                    'order_item_id' => $item->id,
                    'order_item_material_id' => $planned->id,
                    'reason' => 'Pemakaian bahan produksi ' . $item->item_name,
                    'notes' => 'Dipotong otomatis saat produksi dimulai.',
                ]
            );

            $planned->update(['actual_qty' => $planned->planned_qty]);
        }
    }
}
