<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
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
        $columns = [
            ['key' => 'name', 'label' => 'Role', 'sortable' => true],
            ['key' => 'permissions_count', 'label' => 'Jumlah Permission', 'sortable' => true],
        ];

        $compact = [
            'title' => 'Hak Akses',
            'subtitle' => 'Daftar Role yang tersedia',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], [ucwords($this->resourceName())]],

            'columns' => $columns,
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

    public function ajax(Request $request)
    {
        $type = $request->input('type');
        $source = $request->input('source');

        return match ($type) {
            'table' => match ($source) {

                'index' => $this->tableIndex($request),

                default => response()->json(['status' => 'error', 'message' => 'Sumber data tidak valid.'], 400),
            },
            default => response()->json(['status' => 'error', 'message' => 'Aksi tidak valid.'], 400),
        };
    }

    private function tableIndex(Request $request)
    {
        return DataTableService::process(
            $request,
            Role::withCount('permissions'),
            ['name'],
            null,
            function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'name_plain' => $role->name,
                    'permissions_count' => Render::badge('brand', $role->permissions_count.' permission'),
                    'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $role->id) : null,
                    'delete_url' => null,
                ];
            },
            ['name', 'permissions_count']
        );
    }
}
