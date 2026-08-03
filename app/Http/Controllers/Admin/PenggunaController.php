<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\UserWilayahScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $hasRoleColumn = Schema::hasColumn('users', 'role');
        $hasStatusColumn = Schema::hasColumn('users', 'status');
        $hasScopesTable = UserWilayahScope::ensureTableExists();

        $query = User::query();

        if ($hasScopesTable) {
            $query->with(['wilayahScopes.provinsi', 'wilayahScopes.kabupaten']);
        }

        $query->latest();

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));

            $query->where(function ($q) use ($search, $hasRoleColumn, $hasStatusColumn) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);

                if ($hasRoleColumn) {
                    $q->orWhereRaw('LOWER(role) LIKE ?', ["%{$search}%"]);
                }

                if ($hasStatusColumn) {
                    $q->orWhereRaw('LOWER(status) LIKE ?', ["%{$search}%"]);
                }
            });
        }

        if ($hasRoleColumn && $request->filled('role')) {
            $role = strtolower(trim($request->role));

            $query->whereRaw('LOWER(TRIM(role)) = ?', [$role]);
        }

        if ($hasStatusColumn && $request->filled('status')) {
            $status = strtolower(trim($request->status));

            $query->whereRaw('LOWER(TRIM(status)) = ?', [$status]);
        }

        $pengguna = $query
            ->paginate(7)
            ->withQueryString();

        $stats = [
            [
                'label' => 'Total Pengguna',
                'value' => User::count(),
                'color' => 'green',
                'icon' => 'fa-users',
            ],
            [
                'label' => 'Admin',
                'value' => $hasRoleColumn
                    ? User::whereRaw('LOWER(TRIM(role)) = ?', ['admin'])->count()
                    : 0,
                'color' => 'yellow',
                'icon' => 'fa-user-shield',
            ],
            [
                'label' => 'Operator',
                'value' => $hasRoleColumn
                    ? User::whereRaw('LOWER(TRIM(role)) = ?', ['operator'])->count()
                    : 0,
                'color' => 'blue',
                'icon' => 'fa-user-gear',
            ],
            [
                'label' => 'Non-Aktif / Pending',
                'value' => $hasStatusColumn
                    ? User::whereIn('status', ['pending', 'rejected', 'nonactive', 'suspend'])->count()
                    : 0,
                'color' => 'red',
                'icon' => 'fa-user-slash',
            ],
        ];

        $mode = $request->query('mode');
        $editData = null;

        if ($request->filled('edit')) {
            $editQuery = User::query();
            if ($hasScopesTable) {
                $editQuery->with(['wilayahScopes.provinsi', 'wilayahScopes.kabupaten']);
            }
            $editData = $editQuery->findOrFail($request->edit);
            $mode = 'edit';
        }

        $provinsis = Provinsi::orderBy('nama_provinsi')->get();
        $kabupatens = Kabupaten::orderBy('nama_kabupaten')->get();

        return view('admin.pengguna', compact(
            'pengguna',
            'stats',
            'mode',
            'editData',
            'hasRoleColumn',
            'hasStatusColumn',
            'hasScopesTable',
            'provinsis',
            'kabupatens'
        ));
    }

    public function store(Request $request)
    {
        $hasRoleColumn = Schema::hasColumn('users', 'role');
        $hasStatusColumn = Schema::hasColumn('users', 'status');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'scope_type' => ['nullable', 'in:provinsi,kabupaten,none'],
            'provinsi_id' => ['nullable', 'exists:provinsi,provinsi_id'],
            'kabupaten_id' => ['nullable', 'exists:kabupaten,kab_id'],
        ];

        if ($hasRoleColumn) {
            $rules['role'] = ['required', 'in:admin,operator'];
        }

        if ($hasStatusColumn) {
            $rules['status'] = ['required', 'in:pending,approved,rejected,nonactive,Aktif,Suspend'];
        }

        $data = $request->validate($rules);

        $statusValue = 'approved';
        if ($hasStatusColumn && isset($data['status'])) {
            $statusValue = match(strtolower($data['status'])) {
                'approved', 'aktif' => 'approved',
                'pending' => 'pending',
                'rejected' => 'rejected',
                'nonactive', 'suspend' => 'nonactive',
                default => 'approved',
            };
        }

        $createData = [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ];

        if ($hasRoleColumn) {
            $createData['role'] = $data['role'];
        }

        if ($hasStatusColumn) {
            $createData['status'] = $statusValue;
        }

        $user = User::create($createData);

        if (($data['role'] ?? 'operator') === 'operator' && !empty($data['scope_type'])) {
            $this->syncWilayahScope($user, $data);
        }

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $pengguna)
    {
        $hasRoleColumn = Schema::hasColumn('users', 'role');
        $hasStatusColumn = Schema::hasColumn('users', 'status');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($pengguna->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'scope_type' => ['nullable', 'in:provinsi,kabupaten,none'],
            'provinsi_id' => ['nullable', 'exists:provinsi,provinsi_id'],
            'kabupaten_id' => ['nullable', 'exists:kabupaten,kab_id'],
        ];

        if ($hasRoleColumn) {
            $rules['role'] = ['required', 'in:admin,operator'];
        }

        if ($hasStatusColumn) {
            $rules['status'] = ['required', 'in:pending,approved,rejected,nonactive,Aktif,Suspend'];
        }

        $data = $request->validate($rules);

        $statusValue = 'approved';
        if ($hasStatusColumn && isset($data['status'])) {
            $statusValue = match(strtolower($data['status'])) {
                'approved', 'aktif' => 'approved',
                'pending' => 'pending',
                'rejected' => 'rejected',
                'nonactive', 'suspend' => 'nonactive',
                default => 'approved',
            };
        }

        if (
            Auth::check()
            && Auth::id() === $pengguna->id
            && $hasRoleColumn
            && $data['role'] !== 'admin'
        ) {
            return redirect()
                ->route('admin.pengguna.index', ['edit' => $pengguna->id])
                ->with('error', 'Role akun yang sedang digunakan tidak boleh diubah dari admin.');
        }

        if (
            Auth::check()
            && Auth::id() === $pengguna->id
            && $hasStatusColumn
            && in_array($statusValue, ['nonactive', 'rejected', 'pending'])
        ) {
            return redirect()
                ->route('admin.pengguna.index', ['edit' => $pengguna->id])
                ->with('error', 'Akun yang sedang digunakan tidak boleh dinonaktifkan.');
        }

        $updateData = [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
        ];

        if ($hasRoleColumn) {
            $updateData['role'] = $data['role'];
        }

        if ($hasStatusColumn) {
            $updateData['status'] = $statusValue;
        }

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $pengguna->update($updateData);

        if (Schema::hasTable('user_wilayah_scopes')) {
            if (($updateData['role'] ?? $pengguna->role) === 'operator') {
                $this->syncWilayahScope($pengguna, $data);
            } else {
                $pengguna->wilayahScopes()->delete();
            }
        }

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', 'Pengguna dan penyesuaian wilayah kerja berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        if (Auth::check() && Auth::id() === $pengguna->id) {
            return redirect()
                ->route('admin.pengguna.index')
                ->with('error', 'Akun yang sedang digunakan tidak bisa dihapus.');
        }

        $pengguna->delete();

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    private function syncWilayahScope(User $user, array $data): void
    {
        if (!Schema::hasTable('user_wilayah_scopes')) {
            return;
        }

        $user->wilayahScopes()->delete();

        $scopeType = $data['scope_type'] ?? null;

        if ($scopeType === 'provinsi' && !empty($data['provinsi_id'])) {
            UserWilayahScope::create([
                'user_id' => $user->id,
                'provinsi_id' => $data['provinsi_id'],
                'kabupaten_id' => null,
            ]);
        } elseif ($scopeType === 'kabupaten' && !empty($data['kabupaten_id'])) {
            $kabupaten = Kabupaten::find($data['kabupaten_id']);
            if ($kabupaten) {
                UserWilayahScope::create([
                    'user_id' => $user->id,
                    'provinsi_id' => $kabupaten->provinsi_id,
                    'kabupaten_id' => $kabupaten->kab_id,
                ]);
            }
        }
    }
}