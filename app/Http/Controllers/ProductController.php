<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $query = Product::with(['category', 'productModel', 'unit']);

        if ($search = request('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($category = request('category')) {
            $query->where('category_id', $category);
        }

        if (request('low_stock')) {
            $query->whereColumn('stock_qty', '<=', 'min_stock_qty');
        }

        $products = $query->orderBy('name')->paginate(12)->withQueryString();
        $categories = Category::where('type', 'product')->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('products.form', $this->formData(null));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['photo_path'] = $this->uploadPhoto($request);
        $data['stock_qty'] = 0;

        $product = Product::create($data);
        ActivityLogger::log('create', 'product', $product, 'Menambah produk stok jadi ' . $product->name . '.', [], $product->toArray());

        return redirect()->route('products.show', $product)->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'productModel', 'unit']);
        $movements = $product->stockMovements()->with('createdBy')->latest('created_at')->paginate(15);

        return view('products.show', compact('product', 'movements'));
    }

    public function edit(Product $product)
    {
        return view('products.form', $this->formData($product));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);

        if ($path = $this->uploadPhoto($request)) {
            if ($product->photo_path) {
                Storage::disk('public')->delete($product->photo_path);
            }
            $data['photo_path'] = $path;
        }

        $old = $product->toArray();
        $product->update($data);
        ActivityLogger::log('update', 'product', $product, 'Mengubah produk ' . $product->name . '.', $old, $product->fresh()->toArray());

        return redirect()->route('products.show', $product)->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists() || $product->stockMovements()->exists()) {
            return back()->with('error', 'Produk tidak bisa dihapus karena sudah dipakai di transaksi.');
        }

        try {
            $product->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Produk tidak bisa dihapus karena sudah dipakai di transaksi.');
        }

        ActivityLogger::log('delete', 'product', $product, 'Menghapus produk ' . $product->name . '.');

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function adjustStock(Request $request, Product $product, StockService $stockService)
    {
        $data = $request->validate([
            'direction' => ['required', 'in:in,out'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'notes' => ['nullable', 'string'],
        ]);

        $stockService->recordProduct(
            $product,
            $data['direction'] === 'in' ? 'adjustment_in' : 'adjustment_out',
            (float) $data['quantity'],
            ['notes' => $data['notes'] ?? null, 'reference_type' => 'manual_adjustment']
        );

        ActivityLogger::log('adjust_stock', 'product', $product, 'Menyesuaikan stok produk ' . $product->name . '.');

        return back()->with('success', 'Stok produk berhasil disesuaikan.');
    }

    private function formData(?Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::where('type', 'product')->where('is_active', true)->orderBy('name')->get(),
            'models' => ProductModel::where('is_active', true)->orderBy('name')->get(),
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('products', 'code')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'product_model_id' => ['nullable', 'exists:product_models,id'],
            'description' => ['nullable', 'string'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],
            'unit_id' => ['required', 'exists:units,id'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'min_stock_qty' => ['nullable', 'numeric', 'min:0'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sale_price'] = $data['sale_price'] ?? 0;
        $data['min_stock_qty'] = $data['min_stock_qty'] ?? 0;
        unset($data['photo']);

        return $data;
    }

    private function uploadPhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('products', 'public');
    }
}
