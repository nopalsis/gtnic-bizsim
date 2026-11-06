<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login OptimaBiz.
     */
    public function create(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    /**
     * Proses autentikasi login pengguna.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // 1. Cek status keaktifan akun sebelum autentikasi
        $user = User::where('email', $credentials['email'])->first();
        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan oleh administrator workshop. Silakan hubungi pemilik bengkel.',
            ]);
        }

        // 2. Lakukan upaya login
        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan tidak sesuai.',
            ]);
        }

        $request->session()->regenerate();

        $loggedInUser = Auth::user();

        // 3. Arahkan pengguna ke landing module yang relevan berdasarkan perannya
        $defaultRoute = match ($loggedInUser->role) {
            'sales' => route('sales.index'),
            'craftsman' => route('inventory.index'),
            default => route('dashboard'),
        };

        return redirect()->intended($defaultRoute);
    }

    /**
     * Proses logout pengguna dan pembersihan sesi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sesi sistem.');
    }
}

