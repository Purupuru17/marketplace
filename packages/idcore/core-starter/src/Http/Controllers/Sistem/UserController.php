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
    protected static function resourceName(): string
    {
        return 'user';
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $users = User::with('roles')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->only(['search', 'per_page']));

        return view('idcore::sistem.user.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('idcore::sistem.user.form', ['user' => null, 'roles' => $roles]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => ['required', Password::min(8)],
            'roles'        => 'required|array|min:1',
            'roles.*'      => 'exists:roles,name',
            'default_role' => ['required', 'exists:roles,name', Rule::in($request->input('roles', []))]
        ], [
            'default_role.required' => 'Silakan pilih salah satu role sebagai default.',
            'default_role.in'       => 'Role default harus salah satu dari role yang dicentang.',
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
            ->route('sistem.user.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        return view('idcore::sistem.user.form', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'     => ['nullable', Password::min(8)],
            'roles'        => 'required|array|min:1',
            'roles.*'      => 'exists:roles,name',
            'default_role' => ['required', 'exists:roles,name', Rule::in($request->input('roles', []))]
        ], [
            'default_role.required' => 'Silakan pilih salah satu role sebagai default.',
            'default_role.in'       => 'Role default harus salah satu dari role yang dicentang.',
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
            ->route('sistem.user.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('sistem.user.index')
                ->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('sistem.user.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function show(User $user)
    {
        return view('idcore::sistem.user.show', compact('user'));
    }

    public function ajax(Request $request)
    {
        $type = $request->input('type');

        switch ($type) {

            default:
                return response()->json(['status' => 'error', 'message' => 'Aksi tidak valid.'], 400);
        }
    }
}
