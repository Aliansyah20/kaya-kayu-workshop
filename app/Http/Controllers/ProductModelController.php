<?php

namespace App\Http\Controllers;

use App\Models\BomItem;
use App\Models\Category;
use App\Models\ComplexityLevel;
use App\Models\Material;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\WoodGrade;
use App\Models\WoodType;
use App\Services\ActivityLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductModelController extends Controller
{
    public function index()
    {
        $query = ProductModel::with(['category', 'defaultWoodType', 'defaultWoodGrade'])
            ->withCount('bomItems');

        if ($search = request('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($category = request('category')) {
            $query->where('category_id', $category);
        }

        $models = $query->orderBy('name')->paginate(9)->withQueryString();
        $categories = Category::where('type', 'product')->orderBy('name')->get();

        return view('product-models.index', compact('models', 'categories'));
    }

    public function create()
    {
        return view('product-models.form', $this->formData(null));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['photo_path'] = $this->uploadPhoto($request);

        $model = ProductModel::create($data);
        ActivityLogger::log('create', 'product_model', $model, 'Menambah model produk ' . $model->name . '.', [], $model->toArray());

        return redirect()->route('product-models.show', $model)->with('success', 'Model produk berhasil ditambahkan. Lengkapi BOM di bawah.');
    }

    public function show(ProductModel $productModel)
    {
        $productModel->load([
            'category',
            'defaultComplexity',
            'defaultWoodType',
            'defaultWoodGrade',
            'bomItems.material.unit',
            'bomItems.unit',
            'products',
        ]);

        $materials = Material::with('unit')->where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $recentItems = $productModel->orderItems()->with('order.customer')->latest()->limit(8)->get();

        return view('product-models.show', compact('productModel', 'materials', 'units', 'recentItems'));
    }

    public function edit(ProductModel $productModel)
    {
        return view('product-models.form', $this->formData($productModel));
    }

    public function update(Request $request, ProductModel $productModel)
    {
        $data = $this->validated($request, $productModel);

        if ($path = $this->uploadPhoto($request)) {
            if ($productModel->photo_path) {
                Storage::disk('public')->delete($productModel->photo_path);
            }
            $data['photo_path'] = $path;
        }

        $old = $productModel->toArray();
        $productModel->update($data);
        ActivityLogger::log('update', 'product_model', $productModel, 'Mengubah model produk ' . $productModel->name . '.', $old, $productModel->fresh()->toArray());

        return redirect()->route('product-models.show', $productModel)->with('success', 'Model produk berhasil diperbarui.');
    }

    public function destroy(ProductModel $productModel)
    {
        if ($productModel->orderItems()->exists() || $productModel->products()->exists()) {
            return back()->with('error', 'Model produk tidak bisa dihapus karena sudah dipakai di pesanan atau produk jadi.');
        }

        try {
            $productModel->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Model produk tidak bisa dihapus karena sudah dipakai di pesanan atau produk jadi.');
        }

        ActivityLogger::log('delete', 'product_model', $productModel, 'Menghapus model produk ' . $productModel->name . '.');

        return redirect()->route('product-models.index')->with('success', 'Model produk berhasil dihapus.');
    }

    public function storeBom(Request $request, ProductModel $productModel)
    {
        $data = $request->validate([
            'material_id' => ['required', 'exists:materials,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'waste_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $bom = BomItem::updateOrCreate(
            [
                'product_model_id' => $productModel->id,
                'material_id' => $data['material_id'],
                'unit_id' => $data['unit_id'],
            ],
            [
                'quantity' => $data['quantity'],
                'waste_percent' => $data['waste_percent'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]
        );

        ActivityLogger::log('upsert', 'bom', $bom, 'Mengatur BOM ' . $productModel->name . '.');

        return back()->with('success', 'Komponen BOM berhasil disimpan.');
    }

    public function destroyBom(ProductModel $productModel, BomItem $bom)
    {
        abort_if($bom->product_model_id !== $productModel->id, 404);
        $bom->delete();
        ActivityLogger::log('delete', 'bom', $bom, 'Menghapus komponen BOM ' . $productModel->name . '.');

        return back()->with('success', 'Komponen BOM berhasil dihapus.');
    }

    private function formData(?ProductModel $productModel): array
    {
        return [
            'productModel' => $productModel,
            'categories' => Category::where('type', 'product')->where('is_active', true)->orderBy('name')->get(),
            'complexities' => ComplexityLevel::where('is_active', true)->orderBy('name')->get(),
            'woodTypes' => WoodType::where('is_active', true)->orderBy('name')->get(),
            'woodGrades' => WoodGrade::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?ProductModel $productModel = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('product_models', 'code')->ignore($productModel?->id)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'pricing_type' => ['required', 'in:per_m2,fixed'],
            'default_measurement_type' => ['required', 'in:front_area,top_area,surface_area,manual'],
            'default_price_per_m2' => ['nullable', 'numeric', 'min:0'],
            'default_fixed_price' => ['nullable', 'numeric', 'min:0'],
            'default_complexity_level_id' => ['nullable', 'exists:complexity_levels,id'],
            'default_wood_type_id' => ['nullable', 'exists:wood_types,id'],
            'default_wood_grade_id' => ['nullable', 'exists:wood_grades,id'],
            'default_length_cm' => ['nullable', 'numeric', 'min:0'],
            'default_width_cm' => ['nullable', 'numeric', 'min:0'],
            'default_height_cm' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['nullable', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['estimated_days'] = $data['estimated_days'] ?? 0;

        if ($data['pricing_type'] === 'per_m2' && empty($data['default_price_per_m2'])) {
            $data['default_price_per_m2'] = 1000000;
        }

        unset($data['photo']);

        return $data;
    }

    private function uploadPhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('product-models', 'public');
    }
}
