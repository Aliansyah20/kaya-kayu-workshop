<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    public function index(string $resource)
    {
        $config = $this->resolveConfig($resource);
        $model = new $config['model'];
        $query = $model::query();

        if ($search = request('q')) {
            $query->where(function ($q) use ($config, $search) {
                foreach ($config['search'] as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $orderColumn = in_array('name', $model->getFillable(), true) || $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'name')
            ? 'name'
            : 'id';

        $rows = $query->orderBy($orderColumn)->paginate(10)->withQueryString();

        return view('master.index', compact('config', 'rows', 'resource'));
    }

    public function create(string $resource)
    {
        $config = $this->resolveConfig($resource);
        $record = null;

        return view('master.form', compact('config', 'record', 'resource'));
    }

    public function store(Request $request, string $resource)
    {
        $config = $this->resolveConfig($resource);
        $data = $this->validatedData($request, $config, null);
        $model = new $config['model'];
        $record = $model::create($data);

        ActivityLogger::log('create', $resource, $record, "Menambah data {$config['singular']}.", [], $record->toArray());

        return redirect()->route('master.index', $resource)->with('success', "{$config['singular']} berhasil ditambahkan.");
    }

    public function edit(string $resource, int $id)
    {
        $config = $this->resolveConfig($resource);
        $model = new $config['model'];
        $record = $model::findOrFail($id);

        return view('master.form', compact('config', 'record', 'resource'));
    }

    public function update(Request $request, string $resource, int $id)
    {
        $config = $this->resolveConfig($resource);
        $model = new $config['model'];
        $record = $model::findOrFail($id);
        $old = $record->toArray();
        $data = $this->validatedData($request, $config, $record);
        $record->update($data);

        ActivityLogger::log('update', $resource, $record, "Mengubah data {$config['singular']}.", $old, $record->fresh()->toArray());

        return redirect()->route('master.index', $resource)->with('success', "{$config['singular']} berhasil diperbarui.");
    }

    public function destroy(string $resource, int $id)
    {
        $config = $this->resolveConfig($resource);
        $model = new $config['model'];
        $record = $model::findOrFail($id);

        if ($this->isUsed($resource, $record)) {
            return back()->with('error', 'Data tidak bisa dihapus karena sudah dipakai di transaksi.');
        }

        try {
            $record->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Data tidak bisa dihapus karena sudah dipakai di transaksi.');
        }

        ActivityLogger::log('delete', $resource, $record, "Menghapus data {$config['singular']}.");

        return redirect()->route('master.index', $resource)->with('success', "{$config['singular']} berhasil dihapus.");
    }

    private function resolveConfig(string $resource): array
    {
        $config = config("master.{$resource}");
        abort_if(! $config, 404, 'Master data tidak ditemukan.');

        return $config;
    }

    private function isUsed(string $resource, $record): bool
    {
        return match ($resource) {
            'categories' => $record->productModels()->exists()
                || $record->materials()->exists()
                || $record->products()->exists(),
            'suppliers' => $record->purchases()->exists()
                || $record->stockMovements()->exists(),
            'customers' => $record->orders()->exists(),
            default => false,
        };
    }

    private function validatedData(Request $request, array $config, $record): array
    {
        $rules = $this->buildRules($config['rules'], $record);
        $data = $request->validate($rules);

        foreach ($config['fields'] as $field) {
            if (($field['type'] ?? null) === 'checkbox') {
                $data[$field['name']] = $request->boolean($field['name']);
            }
        }

        return $data;
    }

    private function buildRules(array $rules, $record): array
    {
        foreach ($rules as $field => $fieldRules) {
            $rules[$field] = array_map(function ($rule) use ($record) {
                if (is_string($rule) && str_starts_with($rule, 'unique:')) {
                    [$table, $column] = explode(',', substr($rule, 7));
                    return Rule::unique($table, $column)->ignore($record?->id);
                }

                return $rule;
            }, $fieldRules);
        }

        return $rules;
    }
}
