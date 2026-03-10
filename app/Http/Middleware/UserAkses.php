<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAkses
{
    // UBAH DISINI: Tambahkan '...' sebelum $roles untuk menangkap banyak role (admin,admin_biasa)
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = Auth::user();

        // UBAH DISINI: Hapus explode('|'), karena $roles sekarang sudah otomatis jadi array
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        return $this->redirectToRolePage($user);
    }

    protected function redirectToRolePage($user)
    {
        switch ($user->role) {
            case 'admin':
            case 'admin_biasa': // TAMBAHKAN INI (Stacking case)
                // Admin biasa diarahkan ke dashboard yang sama dengan admin
                return redirect()->route('admin.dashboard');
            
            case 'user_kawasan':
                return redirect()->route('user.dashboard');
            
            default:
                return redirect('/logout');
        }
    }
}