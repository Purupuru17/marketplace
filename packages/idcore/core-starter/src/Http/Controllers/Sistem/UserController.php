<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use App\Models\User;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends BaseCoreController
{
    private $module = 'sistem.user';

    protected static function resourceName(): string
    {
        return 'user';
    }

    public function index(Request $request)
    {
        $users = User::with('roles')->orderBy('name')->get();

        $rows = $users->map(function (User $user) {
            $rolesBadges = $user->roles->count() > 0
                ? $user->roles->map(fn ($role) => '<span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold tracking-wide text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">'.e($role->name).'</span>')->implode(' ')
                : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold tracking-wide text-gray-700 dark:bg-white/5 dark:text-gray-400">Tanpa role</span>';

            return [
                'id' => $user->id,
                'name' => '<div class="flex items-center gap-3"><div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-sm font-bold text-brand-500 dark:bg-gray-800 dark:text-brand-400">'.e(strtoupper(substr($user->name, 0, 1))).'</div><div><p class="font-semibold text-gray-900 dark:text-white">'.e($user->name).'</p><p class="text-xs text-gray-500 dark:text-gray-400">ID : '.$user->id.'</p></div></div>',
                'email' => $user->email,
                'roles' => $rolesBadges,
                'edit_url' => auth()->user()->can('user.edit') ? route($this->module.'.edit', $user->id) : null,
                'delete_url' => auth()->user()->can('user.delete') ? route($this->module.'.destroy', $user->id) : null,
            ];
        })->values()->all();

        $columns = [
            ['key' => 'name', 'label' => 'Name', 'sortable' => true, 'html' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'roles', 'label' => 'Role', 'sortable' => false, 'html' => true],
        ];

        $compact = [
            'listData' => $users,

            'title' => 'Users',
            'subtitle' => 'Daftar Akun dan Role yang tersedia',

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
        $roles = Role::orderBy('name')->get();

        $compact = [
            'formData' => null,
            'roles' => $roles,

            'title' => 'Users',
            'subtitle' => 'Atur identitas, password, role, dan default role user',
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
            'default_role' => ['required', 'exists:roles,name', Rule::in($request->input('roles', []))],
        ], [
            'default_role.required' => 'Silakan pilih salah satu role sebagai default.',
            'default_role.in' => 'Role default harus salah satu dari role yang dicentang.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $user->assignRole($validated['roles']);

        $defaultRole = Role::where('name', $validated['default_role'])->first();
        $user->update(['default_role_id' => $defaultRole?->id]);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        $compact = [
            'formData' => $user,
            'roles' => $roles,

            'title' => 'Users',
            'subtitle' => 'Atur identitas, password, role, dan default role user',
            'action' => route($this->module.'.update', $user->id),

            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')],
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Ubah Data']],
        ];

        return view('idcore::'.$this->module.'.form', $compact);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', Password::min(8)],
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
            'default_role' => ['required', 'exists:roles,name', Rule::in($request->input('roles', []))],
        ], [
            'default_role.required' => 'Silakan pilih salah satu role sebagai default.',
            'default_role.in' => 'Role default harus salah satu dari role yang dicentang.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? bcrypt($validated['password']) : $user->password,
        ]);

        $user->syncRoles([$validated['roles']]);

        $defaultRole = Role::where('name', $validated['default_role'])->first();
        $user->update(['default_role_id' => $defaultRole?->id]);

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route($this->module.'.index')
                ->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()
            ->route($this->module.'.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    public function show(User $user) {}

    public function ajax(Request $request)
    {
        $type = $request->input('type');

        switch ($type) {

            default:
                return response()->json(['status' => 'error', 'message' => 'Aksi tidak valid.'], 400);
        }
    }
}
