<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HakAksesController extends BaseCoreController
{
    protected static function resourceName(): string
    {
        return 'hak-akses';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $roles = Role::when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->withCount('permissions')
            ->paginate($perPage)
            ->appends($request->only(['search', 'per_page']));

        return view('idcore::sistem.hak-akses.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $permissionsGrouped = Permission::orderBy('name')->get()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0]);

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('idcore::sistem.hak-akses.form', compact('role', 'permissionsGrouped', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('sistem.hak-akses.index')
            ->with('success', "Hak akses untuk role \"{$role->name}\" berhasil diperbarui.");
    }
}
