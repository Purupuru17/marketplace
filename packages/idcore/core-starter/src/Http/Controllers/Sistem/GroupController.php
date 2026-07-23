<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class GroupController extends BaseCoreController
{
    protected static function resourceName(): string
    {
        return 'group';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $groups = Role::when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->only(['search', 'per_page']));

        return view('idcore::sistem.group.index', compact('groups'));
    }

    public function create()
    {
        return view('idcore::sistem.group.form', ['group' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        Role::create(['name' => $validated['name']]);

        return redirect()
            ->route('sistem.group.index')
            ->with('success', 'Grup berhasil ditambahkan.');
    }

    public function edit(Role $group)
    {
        return view('idcore::sistem.group.form', ['group' => $group]);
    }

    public function update(Request $request, Role $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $group->id,
        ]);

        $group->update(['name' => $validated['name']]);

        return redirect()
            ->route('sistem.group.index')
            ->with('success', 'Grup berhasil diperbarui.');
    }

    public function destroy(Role $group)
    {
        $group->delete();

        return redirect()
            ->route('sistem.group.index')
            ->with('success', 'Grup berhasil dihapus.');
    }
}
