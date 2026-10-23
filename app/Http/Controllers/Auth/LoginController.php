<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return redirect($user->role === 'admin' ? route('admin.dashboard') : route('guru.dashboard'));
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Identitas login wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('login')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'login' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('login');
        }

        $loginInput = $request->input('login');
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Check if user exists and is active
        $user = User::where($field, $loginInput)->first();

        if (! $user || $user->status !== 'active') {
            RateLimiter::hit($throttleKey);

            return back()->withErrors([
                'login' => 'Kombinasi identitas dan kata sandi tidak cocok atau akun tidak aktif.',
            ])->onlyInput('login');
        }

        if (! Auth::attempt([$field => $loginInput, 'password' => $request->input('password')])) {
            RateLimiter::hit($throttleKey);

            return back()->withErrors([
                'login' => 'Kombinasi identitas dan kata sandi tidak cocok atau akun tidak aktif.',
            ])->onlyInput('login');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $dashboardRoute = $user->role === 'admin' ? route('admin.dashboard') : route('guru.dashboard');

        return redirect()->intended($dashboardRoute);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
