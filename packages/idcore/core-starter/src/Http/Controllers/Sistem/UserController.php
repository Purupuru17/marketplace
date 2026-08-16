<?php

namespace IdCore\CoreStarter\Http\Controllers\Sistem;

use App\Models\User;
use IdCore\CoreStarter\Http\Controllers\Base\BaseCoreController;
use IdCore\CoreStarter\Services\DataTableService;
use IdCore\CoreStarter\Support\Render;
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
        $columns = [
            ['key' => 'name', 'label' => 'Nama', 'sortable' => true, 'searchable' => true, 'html' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true, 'searchable' => true],
            ['key' => 'roles', 'label' => 'Role'],
            ['key' => 'status', 'label' => 'Status'],
        ];
        $roles = Role::select('id', 'name')->orderBy('name')->get();

        $compact = [
            'columns' => $columns,
            'roles' => $roles,

            'title' => 'Users',
            'subtitle' => 'Daftar Akun dan Role yang tersedia',

            'module' => $this->module,
            'rolesName' => $this->resourceName(),
            'breadcrumb' => [['Beranda', route('dashboard')], [ucwords($this->resourceName())]],
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

    public function show(User $user)
    {
        $user->load('roles');

        $compact = [
            'detail' => $user,

            'title' => 'Users',
            'subtitle' => 'Detail informasi user',
            'action' => null,

            'module' => $this->module,
            'breadcrumb' => [['Beranda', route('dashboard')],
                [ucwords($this->resourceName()), route($this->module.'.index')], ['Detail Data']],
        ];

        return view('idcore::'.$this->module.'.show', $compact);

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
            User::with('roles'),
            ['name', 'email'],
            function ($query) use ($request) {
                if ($request->filled('role_id')) {
                    $query->whereHas('roles', function ($q) use ($request) {
                        $q->where('roles.id', $request->input('role_id'));
                    });
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }
            },
            function (User $user) {
                $rolesBadges = $user->roles->count() > 0
                    ? $user->roles->map(fn ($role) => Render::badge('brand', $role->name))->implode(' ')
                    : Render::badge('gray', 'Tanpa role');

                $statusBadge = Render::badge(
                    $user->status === 'active' ? 'success' : 'danger',
                    $user->status === 'active' ? 'Aktif' : 'Tidak Aktif'
                );

                return [
                    'id' => $user->id,
                    'name' => '<p class="font-semibold text-gray-900 dark:text-white">'.e($user->name).'</p><p class="text-xs text-gray-500 dark:text-gray-400">ID : '.$user->id.'</p>',
                    'name_plain' => '<strong>'.$user->name.'</strong>',
                    'email' => $user->email,
                    'roles' => $rolesBadges,
                    'status' => $statusBadge,

                    'detail_url' => Auth::user()->can('user.detail') ? route($this->module.'.show', $user->id) : null,
                    'edit_url' => Auth::user()->can('user.edit') ? route($this->module.'.edit', $user->id) : null,
                    'delete_url' => Auth::user()->can('user.delete') ? route($this->module.'.destroy', $user->id) : null,
                ];
            }
        );
    }
}
