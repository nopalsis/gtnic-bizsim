<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 1. Pastikan pengguna telah terautentikasi
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        // 2. Pastikan akun dalam status aktif
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda dinonaktifkan oleh Administrator Workshop. Silakan hubungi pemilik bengkel.',
            ]);
        }

        // 3. Jika tidak ada parameter role spesifik, loloskan
        if (empty($roles)) {
            return $next($request);
        }

        // 4. Verifikasi peran pengguna
        if (! in_array($user->role, $roles, true)) {
            $roleNames = [
                'admin' => 'Administrator',
                'sales' => 'Sales Force',
                'craftsman' => 'Pengrajin (Craftsman)',
            ];

            $requiredRoleLabels = array_map(fn ($r) => $roleNames[$r] ?? $r, $roles);
            $message = 'Akses ditolak: Modul ini hanya dapat diakses oleh peran: '.implode(' / ', $requiredRoleLabels).'.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('dashboard')->with('error', $message);
        }

        return $next($request);
    }
}

