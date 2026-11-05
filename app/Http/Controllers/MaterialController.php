<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\WoodGrade;
use App\Models\WoodType;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MaterialController extends Controller
{
    public function index()
    {
        $query = Material::with(['category', 'unit', 'woodType', 'woodGrade']);

        if ($search = request('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($category = request('category')) {
            $query->where('category_id', $category);
        }

        if ($type = request('type')) {
            $query->where('material_type', $type);
        }

        if (request('low_stock')) {
            $query->whereColumn('stock_qty', '<=', 'min_stock_qty');
        }

        $materials = $query->orderBy('name')->paginate(12)->withQueryString();
        $categories = Category::where('type', 'material')->orderBy('name')->get();
        $lowStockCount = Material::whereColumn('stock_qty', '<=', 'min_stock_qty')->count();

        return view('materials.index', compact('materials', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        return view('materials.form', $this->formData(null));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['photo_path'] = $this->uploadPhoto($request);
        $data['stock_qty'] = 0;

        $material = Material::create($data);
        ActivityLogger::log('create', 'material', $material, 'Menambah bahan ' . $material->name . '.', [], $material->toArray());

        return redirect()->route('materials.show', $material)->with('success', 'Bahan berhasil ditambahkan. Stok awal 0, silakan lakukan stok masuk.');
    }

    public function show(Material $material)
    {
        $material->load(['category', 'unit', 'woodType', 'woodGrade']);
        $movements = $material->stockMovements()
            ->with(['createdBy', 'supplier', 'order'])
            ->latest('movement_date')
            ->paginate(15);

        $totalIn = $material->stockMovements()->whereIn('movement_type', StockMovement::INCOMING)->sum('quantity');
        $totalOut = $material->stockMovements()->whereIn('movement_type', StockMovement::OUTGOING)->sum('quantity');

        return view('materials.show', compact('material', 'movements', 'totalIn', 'totalOut'));
    }

    public function edit(Material $material)
    {
        return view('materials.form', $this->formData($material));
    }

    public function update(Request $request, Material $material)
    {
        $data = $this->validated($request, $material);

        if ($path = $this->uploadPhoto($request)) {
            if ($material->photo_path) {
                Storage::disk('public')->delete($material->photo_path);
            }
            $data['photo_path'] = $path;
        }

        $old = $material->toArray();
        $material->update($data);
        ActivityLogger::log('update', 'material', $material, 'Mengubah bahan ' . $material->name . '.', $old, $material->fresh()->toArray());

        return redirect()->route('materials.show', $material)->with('success', 'Bahan berhasil diperbarui.');
    }

    public function destroy(Material $material)
    {
        if ($material->bomItems()->exists()
            || $material->orderItemMaterials()->exists()
            || $material->stockMovements()->exists()
            || $material->purchaseItems()->exists()
            || $material->stockOpnameItems()->exists()) {
            return back()->with('error', 'Bahan tidak bisa dihapus karena sudah dipakai di BOM atau transaksi.');
        }

        try {
            $material->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Bahan tidak bisa dihapus karena sudah dipakai di BOM atau transaksi.');
        }

        ActivityLogger::log('delete', 'material', $material, 'Menghapus bahan ' . $material->name . '.');

        return redirect()->route('materials.index')->with('success', 'Bahan berhasil dihapus.');
    }

    private function formData(?Material $material): array
    {
        return [
            'material' => $material,
            'categories' => Category::where('type', 'material')->where('is_active', true)->orderBy('name')->get(),
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
            'woodTypes' => WoodType::where('is_active', true)->orderBy('name')->get(),
            'woodGrades' => WoodGrade::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Material $material = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('materials', 'code')->ignore($material?->id)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'material_type' => ['required', 'in:wood,finishing,hardware,consumable,other'],
            'wood_type_id' => ['nullable', 'exists:wood_types,id'],
            'wood_grade_id' => ['nullable', 'exists:wood_grades,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'thickness_mm' => ['nullable', 'numeric', 'min:0'],
            'width_mm' => ['nullable', 'numeric', 'min:0'],
            'length_mm' => ['nullable', 'numeric', 'min:0'],
            'min_stock_qty' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['min_stock_qty'] = $data['min_stock_qty'] ?? 0;
        $data['purchase_price'] = $data['purchase_price'] ?? 0;
        unset($data['photo']);

        return $data;
    }

    private function uploadPhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('materials', 'public');
    }
}
