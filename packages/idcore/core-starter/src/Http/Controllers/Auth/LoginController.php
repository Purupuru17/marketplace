<?php

namespace IdCore\CoreStarter\Http\Controllers\Auth;

use IdCore\CoreStarter\Services\ActivityLogService;
use IdCore\CoreStarter\Support\ActiveRole;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

        $user = Auth::user();
        $activeRole = ActiveRole::get($user);

        return view('idcore::auth.dashboard', [
            'user' => $user,
            'activeRole' => $activeRole
        ]);
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|string',
        ];

        if ($this->recaptchaEnabled()) {
            $rules['g-recaptcha-response'] = 'required|string';
        }

        $request->validate($rules);

        if ($this->recaptchaEnabled() && ! $this->verifyRecaptcha($request->input('g-recaptcha-response'), $request->ip())) {
            return back()->withInput($request->only('email'))
                ->withErrors(['g-recaptcha-response' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.']);
        }

        $credentials = $request->only(['email', 'password']);

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

        ActivityLogService::login($user->id);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        ActivityLogService::logout($request->user()?->id);

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

    private function recaptchaEnabled(): bool
    {
        return filled(config('idcore.recaptcha.site_key')) && filled(config('idcore.recaptcha.secret_key'));
    }

    private function verifyRecaptcha(?string $response, ?string $ip): bool
    {
        if (blank($response)) {
            return false;
        }

        $result = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('idcore.recaptcha.secret_key'),
            'response' => $response,
            'remoteip' => $ip,
        ]);

        return $result->successful() && $result->json('success') === true;
    }
}
