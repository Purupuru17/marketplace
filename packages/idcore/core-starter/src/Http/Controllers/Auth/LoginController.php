<?php

namespace IdCore\CoreStarter\Http\Controllers\Auth;

use IdCore\CoreStarter\Support\ActiveRole;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('idcore::auth.login');
    }

    public function dashboard()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return view('idcore::auth.dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $defaultRole = $user->default_role_id
            ? Role::find($user->default_role_id)
            : $user->roles->first();

        session(['active_role' => $defaultRole?->name]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function switch(Request $request)
    {
        $validated = $request->validate(['role' => 'required|string']);

        if (! ActiveRole::set($request->user(), $validated['role'])) {
            return back()->with('error', 'Anda tidak memiliki role tersebut.');
        }

        return redirect()->route('dashboard')
            ->with('success', "Berhasil beralih ke role \"{$validated['role']}\".");
    }
}
