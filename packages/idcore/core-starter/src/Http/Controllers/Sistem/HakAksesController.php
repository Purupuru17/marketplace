<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HakAksesController extends BaseCoreController
{
    private $module = 'sistem.hak-akses';

    protected static function resourceName(): string
    {
        return 'hak-akses';
    }

    public function index(Request $request)
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get();

        $rows = $roles->map(function (Role $role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => '<span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold tracking-wide text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">'.$role->permissions_count.' permission</span>',
                'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $role->id) : null,
                'delete_url' => null,
            ];
        })->values()->all();

        $columns = [
            ['key' => 'name', 'label' => 'Role', 'sortable' => true],
            ['key' => 'permissions_count', 'label' => 'Jumlah Permission', 'sortable' => true, 'html' => true, 'align' => 'center'],
        ];

        $compact = [
            'listData' => $roles,

            'title' => 'Hak Akses',
            'subtitle' => 'Daftar Role yang tersedia',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], [ucwords($this->resourceName())]],

            'columns' => $columns,
            'rows' => $rows,
        ];

        return view('idcore::'.$this->module.'.index', $compact);
    }

    public function edit(Role $role)
    {
        $permissionsGrouped = Permission::orderBy('name')->get()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0]);

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $compact = [
            'formData' => $role,
            'permissionsGrouped' => $permissionsGrouped,
            'rolePermissions' => $rolePermissions,

            'title' => 'Hak Akses',
            'subtitle' => 'Atur role untuk '.$role->name,
            'action' => route($this->module.'.update', $role->id),

            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')],
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Ubah Data']],
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
