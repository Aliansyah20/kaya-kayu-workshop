<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $query = User::query();

        if ($search = request('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = request('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['user' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        ActivityLogger::log('create', 'user', $user, 'Menambah pengguna ' . $user->name . '.', [], $user->toArray());

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $willRemainActiveOwner = ($data['role'] ?? $user->role) === 'owner' && ! empty($data['is_active']);

        if ($user->role === 'owner' && $user->is_active && ! $willRemainActiveOwner
            && User::where('role', 'owner')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Minimal satu akun Pemilik harus tetap aktif.');
        }

        $old = $user->toArray();
        $user->update($data);
        ActivityLogger::log('update', 'user', $user, 'Mengubah pengguna ' . $user->name . '.', $old, $user->fresh()->toArray());

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->role === 'owner' && $user->is_active && User::where('role', 'owner')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Minimal satu akun Pemilik harus tetap aktif.');
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Pengguna tidak bisa dihapus karena sudah tercatat di transaksi atau riwayat.');
        }

        ActivityLogger::log('delete', 'user', $user, 'Menghapus pengguna ' . $user->name . '.');

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', 'in:owner,admin,employee'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
