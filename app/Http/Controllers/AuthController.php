<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'is_active' => 1,
        ], $remember)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah, atau akun tidak aktif.',
            ]);
        }

        $request->session()->regenerate();
        $request->user()->update(['last_login_at' => now()]);
        ActivityLogger::log('login', 'auth', $request->user(), 'Login ke sistem.');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('logout', 'auth', $request->user(), 'Logout dari sistem.');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
