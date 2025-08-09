<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function redirect()
    {
        $role = auth()->user()->role;

        // Kalau profil belum lengkap, langsung ke halaman lengkapi profil
        if (is_null(auth()->user()->nama) || is_null(auth()->user()->alamat)) {
            return redirect()->route('profile.complete');
        }

        // Kalau role = admin_bumdes → ke dashboard admin
        if ($role === 'admin_bumdes') {
            return redirect()->route('admin.dashboard');
        }

        // Default ke dashboard admin (atau ubah sesuai kebutuhan)
        return redirect()->route('admin.dashboard');
    }

    public function index()
    {
        return view('admin.dashboard');
    }
}
