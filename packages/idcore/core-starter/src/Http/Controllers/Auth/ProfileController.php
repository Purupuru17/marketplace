<?php

namespace IdCore\CoreStarter\Http\Controllers\Auth;

use IdCore\CoreStarter\Support\ActiveRole;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $activeRole = ActiveRole::get($user);

        return view('idcore::auth.profile', [
            'user' => $user,
            'activeRole' => $activeRole,
            'title' => 'Profile',
            'subtitle' => 'Kelola informasi akun dan keamanan',
            'breadcrumb' => [['Beranda', route('dashboard')], ['Profile']],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update(['password' => $validated['password']]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function logoutAllDevices(Request $request)
    {
        Auth::logoutOtherDevices($request->user()->password);

        $request->session()->passwordConfirmed();

        return back()->with('success', 'Semua perangkat lain telah keluar.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($user->email === 'super@gmail.com') {
            return back()->with('error', 'Akun super admin tidak dapat dihapus.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('login')->with('success', 'Akun berhasil dihapus.');
    }
}
