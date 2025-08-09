<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   // app/Http/Middleware/CheckProfileCompletion.php (Kode baru)

public function handle(Request $request, Closure $next)
{
    $user = Auth::user();

    // Cek jika user sudah login dan profil belum lengkap
    if ($user && !$user->is_profile_complete) {
        // Cek jika user sudah berada di halaman profil atau halaman logout
        // Ini untuk menghindari redirect loop
        if ($request->routeIs('profile.edit') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Redirect ke halaman profil
        return redirect()->route('profile.edit')->with('warning', 'Silakan lengkapi data profil Anda untuk mengakses fitur lain.');
    }

    return $next($request);
}
}
