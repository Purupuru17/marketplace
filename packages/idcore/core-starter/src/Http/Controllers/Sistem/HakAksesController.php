<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class HakAksesController extends BaseCoreController
{
    private $module = 'sistem.hak-akses';

    protected static function resourceName(): string
    {
        return 'hak-akses';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $listData = Role::when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->withCount('permissions')
            ->paginate($perPage)
            ->appends($request->only(['search', 'per_page']));

        $compact = [
            'listData'          => $listData,
            
            'title'             => 'Hak Akses',
            'subtitle'          => 'Daftar Role yang tersedia',
            
            'module'            => $this->module,
            'rolesName'         => $this->resourceName(),
            'breadcrumb'        => [[ 'Beranda', route('dashboard')], [ucwords($this->resourceName())]]
        ];
        return view('idcore::'.$this->module.'.index', $compact);
    }

    public function edit(Role $role)
    {
        $permissionsGrouped = Permission::orderBy('name')->get()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0]);

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $compact = [
            'formData'          => $role,
            'permissionsGrouped'=> $permissionsGrouped,
            'rolePermissions'   => $rolePermissions,
            
            'title'             => 'Hak Akses',
            'subtitle'          => 'Atur role untuk '.$role->name,
            'action'            => route($this->module.'.update', $role->id),

            'module'            => $this->module,
            'breadcrumb'        => [['Beranda', route('dashboard')], 
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Ubah Data']]
        ];
        return view('idcore::'.$this->module.'.form', $compact);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', "Hak akses untuk role \"{$role->name}\" berhasil diperbarui.");
    }
}
