<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class GroupController extends BaseCoreController
{
    private $module = 'sistem.group';

    protected static function resourceName(): string
    {
        return 'group';
    }

    public function index(Request $request)
    {
        $roles = Role::orderBy('name')->get();

        $rows = $roles->map(fn (Role $role) => [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'edit_url' => auth()->user()->can($this->resourceName().'.edit') ? route($this->module.'.edit', $role->id) : null,
            'delete_url' => auth()->user()->can($this->resourceName().'.delete') ? route($this->module.'.destroy', $role->id) : null,
        ])->values()->all();

        $columns = [
            ['key' => 'name', 'label' => 'Nama Grup', 'sortable' => true],
            ['key' => 'guard_name', 'label' => 'Guard', 'sortable' => true],
        ];

        $compact = [
            'listData' => $roles,

            'title' => 'Group',
            'subtitle' => 'Daftar Role yang tersedia',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], [ucwords($this->resourceName())]],

            'columns' => $columns,
            'rows' => $rows,
        ];

        return view('idcore::'.$this->module.'.index', $compact);
    }

    public function create()
    {
        $compact = [
            'formData' => null,

            'title' => 'Group',
            'subtitle' => 'Atur Roles',
            'action' => route($this->module.'.store'),

            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')],
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Tambah Data']],

        ];

        return view('idcore::'.$this->module.'.form', $compact);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        Role::create(['name' => $validated['name']]);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Role $group)
    {
        $compact = [
            'formData' => $group,

            'title' => 'Group',
            'subtitle' => 'Atur Roles',
            'action' => route($this->module.'.update', $group->id),

            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')],
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Ubah Data']],

        ];

        return view('idcore::'.$this->module.'.form', $compact);
    }

    public function update(Request $request, Role $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$group->id,
        ]);

        $group->update(['name' => $validated['name']]);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Role $group)
    {
        $group->delete();

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Data berhasil dihapus.');
    }
}
